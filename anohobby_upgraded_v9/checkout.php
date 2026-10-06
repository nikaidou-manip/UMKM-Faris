<?php
require_once __DIR__ . '/includes/functions.php';
require_login();
global $pdo;

$items = cart_items($pdo);
$subtotal = cart_total($pdo);

// Cart kosong -> balik ke produk
if (empty($items)) {
    set_flash('info', 'Keranjang kamu kosong. Tambahkan produk dulu sebelum checkout.');
    header('Location: ' . url('products.php'));
    exit;
}

$user_id = $_SESSION['user_id'];
$stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
$stmt->execute([$user_id]);
$user = $stmt->fetch();

// ===== CHECK: apakah kolom user_voucher_id sudah ada? =====
$needs_migration = false;
try {
    $check = $pdo->query("SHOW COLUMNS FROM transactions LIKE 'user_voucher_id'")->fetch();
    if (empty($check)) $needs_migration = true;
} catch (Throwable $e) {
    $needs_migration = true;
}
if ($needs_migration) {
    set_flash('error', 'Database belum lengkap. Jalankan SQL: <code>ALTER TABLE transactions ADD COLUMN user_voucher_id INT NULL DEFAULT NULL AFTER voucher_discount;</code> di phpMyAdmin. Atau import file database/patch_add_user_voucher_id.sql.');
}

// ===== SESSION STATE: voucher item & koin toggle =====
if (!isset($_SESSION['applied_user_voucher_id'])) $_SESSION['applied_user_voucher_id'] = null;
if (!isset($_SESSION['use_points'])) $_SESSION['use_points'] = false;

// Auto-cleanup expired user vouchers
try { cleanup_expired_user_vouchers($pdo); } catch (Throwable $e) {}

// ===== HANDLE VOUCHER ITEM PICKER & POINTS TOGGLE =====
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['voucher_action'] ?? '') !== '') {
    $voucher_action = $_POST['voucher_action'];

    if ($voucher_action === 'apply_voucher') {
        $uv_id = (int)($_POST['user_voucher_id'] ?? 0);
        if ($uv_id === 0) {
            $_SESSION['applied_user_voucher_id'] = null;
            set_flash('info', 'Voucher dibatalkan.');
        } else {
            $uv = get_user_voucher($pdo, $uv_id, $user_id);
            if ($uv && $uv['status'] === 'available' && (float)$uv['min_purchase'] <= $subtotal) {
                $_SESSION['applied_user_voucher_id'] = $uv_id;
                $disc = calculate_user_voucher_discount($uv, $subtotal);
                set_flash('success', "Voucher \"{$uv['voucher_code_snapshot']}\" diterapkan! Kamu hemat " . rupiah($disc) . ".");
            } else {
                set_flash('error', 'Voucher tidak valid atau tidak memenuhi syarat minimal belanja.');
            }
        }
        header('Location: ' . url('checkout.php'));
        exit;
    } elseif ($voucher_action === 'toggle_points') {
        $_SESSION['use_points'] = !$_SESSION['use_points'];
        header('Location: ' . url('checkout.php'));
        exit;
    }
}

// ===== HITUNG VOUCHER ITEM DISCOUNT SAAT INI =====
$applied_uv = null;
$voucher_discount = 0;
if (!empty($_SESSION['applied_user_voucher_id'])) {
    $applied_uv = get_user_voucher($pdo, $_SESSION['applied_user_voucher_id'], $user_id);
    if ($applied_uv && $applied_uv['status'] === 'available' && (float)$applied_uv['min_purchase'] <= $subtotal) {
        $voucher_discount = calculate_user_voucher_discount($applied_uv, $subtotal);
    } else {
        $_SESSION['applied_user_voucher_id'] = null;
    }
}

$after_voucher = $subtotal - $voucher_discount;

// ===== HITUNG POINTS DISCOUNT (Koin) =====
$user_points_balance = get_user_points($pdo, $user_id);
$max_usable_pts = max_usable_points($pdo, $user_id, $after_voucher);
$use_points = $_SESSION['use_points'] && $max_usable_pts > 0;
$points_used = $use_points ? $max_usable_pts : 0;
$points_discount = (float)$points_used;  // 1 koin = Rp 1

$after_points = $after_voucher - $points_discount;

// ===== Available user vouchers untuk picker (yang memenuhi syarat min_purchase) =====
$available_uvs = get_user_available_vouchers($pdo, $user_id, $subtotal);

$errors = [];
$form = [
    'recipient_name' => $user['full_name'],
    'recipient_phone'=> $user['phone'],
    'shipping_address' => $user['address'],
    'payment_method' => '',
    'shipping_method' => '',
];

// ===== HANDLE CHECKOUT SUBMIT =====
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['voucher_action'] ?? '') === '') {
    $form['recipient_name']  = trim($_POST['recipient_name'] ?? '');
    $form['recipient_phone'] = trim($_POST['recipient_phone'] ?? '');
    $form['shipping_address']= trim($_POST['shipping_address'] ?? '');
    $form['payment_method']  = $_POST['payment_method'] ?? '';
    $form['shipping_method'] = $_POST['shipping_method'] ?? '';

    if ($form['recipient_name']==='') $errors[] = 'Nama penerima wajib diisi.';
    if ($form['recipient_phone']==='') $errors[] = 'No. HP penerima wajib diisi.';
    if ($form['shipping_address']==='') $errors[] = 'Alamat pengiriman wajib diisi.';
    if (!$form['payment_method']) $errors[] = 'Pilih metode pembayaran.';
    if (!$form['shipping_method']) $errors[] = 'Pilih metode pengiriman.';

    $ship_opts = shipping_options();
    $ship_cost = $ship_opts[$form['shipping_method']]['cost'] ?? 0;
    $grand = $after_points + $ship_cost;

    if (empty($errors)) {
        foreach ($items as $it) {
            if ($it['qty'] > $it['stock']) {
                $errors[] = "Stok \"{$it['name']}\" tidak cukup. Tersisa {$it['stock']} unit.";
            }
        }
    }

    if (empty($errors)) {
        $invoice_no = generateInvoiceNo($pdo);
        try {
            $pdo->beginTransaction();

            $uv_id_db = $applied_uv['id'] ?? null;
            $uv_code_db = $applied_uv['voucher_code_snapshot'] ?? null;
            $voucher_id_db = $applied_uv['voucher_id'] ?? null;

            // Hitung koin yang akan didapat (1% dari grand_total)
            $earned_pts = calculate_earned_points($grand);

            // Cek apakah kolom user_voucher_id ada di tabel transactions (defensive)
            $has_uv_id_col = false;
            try {
                $cols = $pdo->query("SHOW COLUMNS FROM transactions LIKE 'user_voucher_id'")->fetch();
                $has_uv_id_col = !empty($cols);
            } catch (Throwable $e) {}

            if ($has_uv_id_col) {
                $stmt = $pdo->prepare("INSERT INTO transactions
                    (user_id, invoice_no, total, shipping_cost, voucher_id, voucher_code, voucher_discount, user_voucher_id, points_used, points_discount, points_earned, grand_total, payment_method, shipping_method, status, recipient_name, recipient_phone, shipping_address)
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'pending', ?, ?, ?)");
                $stmt->execute([
                    $user_id, $invoice_no, $subtotal, $ship_cost,
                    $voucher_id_db, $uv_code_db, $voucher_discount,
                    $uv_id_db, $points_used, $points_discount, $earned_pts, $grand,
                    $form['payment_method'], $form['shipping_method'],
                    $form['recipient_name'], $form['recipient_phone'], $form['shipping_address']
                ]);
            } else {
                // Fallback: tanpa user_voucher_id (jika belum di-migrate)
                $stmt = $pdo->prepare("INSERT INTO transactions
                    (user_id, invoice_no, total, shipping_cost, voucher_id, voucher_code, voucher_discount, points_used, points_discount, points_earned, grand_total, payment_method, shipping_method, status, recipient_name, recipient_phone, shipping_address)
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'pending', ?, ?, ?)");
                $stmt->execute([
                    $user_id, $invoice_no, $subtotal, $ship_cost,
                    $voucher_id_db, $uv_code_db, $voucher_discount,
                    $points_used, $points_discount, $earned_pts, $grand,
                    $form['payment_method'], $form['shipping_method'],
                    $form['recipient_name'], $form['recipient_phone'], $form['shipping_address']
                ]);
            }
            $trx_id = $pdo->lastInsertId();

            // Insert items + decrement stock
            $insItem = $pdo->prepare("INSERT INTO transaction_items (transaction_id, product_id, product_name, quantity, price, subtotal) VALUES (?, ?, ?, ?, ?, ?)");
            $updStock = $pdo->prepare("UPDATE products SET stock = stock - ? WHERE id = ?");
            foreach ($items as $it) {
                $insItem->execute([$trx_id, $it['product_id'], $it['name'], $it['qty'], $it['price'], $it['subtotal']]);
                $updStock->execute([$it['qty'], $it['product_id']]);
            }

            // Mark user_voucher sebagai used
            if ($uv_id_db) {
                mark_user_voucher_used($pdo, $uv_id_db, $trx_id);
                // Increment used_count pada master voucher (untuk statistik)
                if ($voucher_id_db) {
                    $pdo->prepare("UPDATE vouchers SET used_count = used_count + 1 WHERE id = ?")->execute([$voucher_id_db]);
                }
                // Log ke voucher_redemptions
                $pdo->prepare("INSERT INTO voucher_redemptions (voucher_id, transaction_id, user_id, code, discount_amount) VALUES (?, ?, ?, ?, ?)")
                    ->execute([$voucher_id_db, $trx_id, $user_id, $uv_code_db, $voucher_discount]);
            }

            // Update saldo koin user: kurangi yang dipakai + tambah yang didapat
            $new_points = $user_points_balance - $points_used + $earned_pts;
            $pdo->prepare("UPDATE users SET points = ? WHERE id = ?")->execute([$new_points, $user_id]);

            $pdo->commit();

            // Bersihkan session
            set_cart([]);
            $_SESSION['applied_user_voucher_id'] = null;
            $_SESSION['use_points'] = false;

            $points_msg = $earned_pts > 0 ? " Kamu mendapat {$earned_pts} koin baru!" : '';
            set_flash('success', 'Transaksi berhasil dibuat!' . $points_msg);
            header('Location: ' . url('invoice.php?id=' . $trx_id));
            exit;
        } catch (Exception $e) {
            $pdo->rollBack();
            $errors[] = 'Terjadi kesalahan saat menyimpan transaksi: ' . $e->getMessage();
        }
    }
}

$page_title = 'Checkout';
include __DIR__ . '/includes/header.php';
?>
<!-- ============ STEPPER ============ -->
<div class="shop-steps">
    <a href="<?= url('cart.php') ?>" class="shop-step done"><span class="ss-num"><i class="fas fa-check"></i></span><span class="ss-label">Keranjang</span></a>
    <div class="shop-step-line done"></div>
    <div class="shop-step active"><span class="ss-num">2</span><span class="ss-label">Checkout</span></div>
    <div class="shop-step-line"></div>
    <div class="shop-step"><span class="ss-num">3</span><span class="ss-label">Selesai</span></div>
</div>

<div class="shop-title">
    <h1><i class="fas fa-lock"></i> Checkout</h1>
    <span class="count-pill"><?= cart_count() ?> item</span>
</div>

<section class="checkout-shopee">
    <div class="cs-main">
        <?php if (!empty($errors)): foreach ($errors as $er): ?>
            <div class="flash flash-error"><i class="fas fa-exclamation-circle"></i> <?= e($er) ?></div>
        <?php endforeach; endif; ?>

        <!-- ============ SHIPPING ADDRESS (Shopee-style) ============ -->
        <div class="cs-section">
            <div class="cs-section-header">
                <i class="fas fa-map-marker-alt accent"></i>
                <h3>Alamat Pengiriman</h3>
            </div>
            <form method="post" action="" id="checkoutForm">
                <div class="cs-address-form">
                    <div class="form-row">
                        <div class="form-group">
                            <label class="form-label">Nama Penerima <span class="req">*</span></label>
                            <input type="text" name="recipient_name" class="form-control" value="<?= e($form['recipient_name']) ?>" required>
                        </div>
                        <div class="form-group">
                            <label class="form-label">No. HP Penerima <span class="req">*</span></label>
                            <input type="text" name="recipient_phone" class="form-control" value="<?= e($form['recipient_phone']) ?>" required>
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Alamat Lengkap Pengiriman <span class="req">*</span></label>
                        <textarea name="shipping_address" class="form-control" rows="2" required><?= e($form['shipping_address']) ?></textarea>
                    </div>
                </div>
            </form>
        </div>

        <!-- ============ PRODUCT ORDER TABLE (Shopee-style) ============ -->
        <div class="cs-section">
            <div class="cs-section-header">
                <i class="fas fa-box accent"></i>
                <h3>Produk Dipesan</h3>
            </div>
            <table class="cs-order-table">
                <thead>
                    <tr>
                        <th class="cs-col-product">Produk</th>
                        <th class="cs-col-price">Harga Satuan</th>
                        <th class="cs-col-qty">Jumlah</th>
                        <th class="cs-col-subtotal">Subtotal</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($items as $it): ?>
                        <tr>
                            <td class="cs-col-product">
                                <div class="cs-product-cell">
                                    <img src="<?= e($it['image']) ?>" alt="">
                                    <div>
                                        <div class="cs-prod-name"><?= e($it['name']) ?></div>
                                        <?php if (!empty($it['is_flash_sale'])): ?>
                                            <span class="cs-prod-badge badge-flashsale">
                                                <i class="fas fa-bolt"></i> FLASH SALE -<?= (int)$it['flash_sale_discount'] ?>%
                                            </span>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </td>
                            <td class="cs-col-price">
                                <?php if (!empty($it['is_flash_sale'])): ?>
                                    <div class="cs-price-strike"><?= rupiah($it['original_price']) ?></div>
                                    <div class="cs-price-sale"><?= rupiah($it['price']) ?></div>
                                <?php else: ?>
                                    <div class="cs-price-regular"><?= rupiah($it['price']) ?></div>
                                <?php endif; ?>
                            </td>
                            <td class="cs-col-qty"><?= $it['qty'] ?></td>
                            <td class="cs-col-subtotal"><?= rupiah($it['subtotal']) ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>

        <!-- ============ VOUCHER ITEM (Shopee-style) ============ -->
        <div class="cs-section">
            <div class="cs-section-header">
                <i class="fas fa-ticket-alt accent"></i>
                <h3>Voucher AnoHobby</h3>
                <?php if (count($available_uvs) > 0): ?>
                    <button type="button" class="btn btn-outline btn-sm cs-pick-voucher-btn" onclick="document.getElementById('voucherPickerModal').style.display='flex'">
                        <?= $applied_uv ? 'Ganti Voucher' : 'Pilih Voucher' ?> <i class="fas fa-chevron-right"></i>
                    </button>
                <?php else: ?>
                    <a href="<?= url('my_vouchers.php') ?>" class="btn btn-outline btn-sm">Klaim Voucher <i class="fas fa-plus"></i></a>
                <?php endif; ?>
            </div>
            <?php if ($applied_uv): ?>
                <div class="cs-applied-voucher">
                    <div class="cav-left">
                        <i class="fas fa-ticket-alt"></i>
                        <strong><?= e($applied_uv['voucher_code_snapshot']) ?></strong>
                    </div>
                    <div class="cav-right">
                        <div class="cav-desc"><?= e($applied_uv['description_snapshot'] ?: user_voucher_discount_label($applied_uv)) ?></div>
                        <div class="cav-disc">Hemat <strong class="accent"><?= rupiah($voucher_discount) ?></strong></div>
                    </div>
                    <form method="post" action="">
                        <input type="hidden" name="voucher_action" value="apply_voucher">
                        <input type="hidden" name="user_voucher_id" value="0">
                        <button type="submit" class="cav-remove" title="Hapus"><i class="fas fa-times"></i></button>
                    </form>
                </div>
            <?php else: ?>
                <div class="cs-no-voucher">
                    <?php if (count($available_uvs) > 0): ?>
                        Kamu punya <strong><?= count($available_uvs) ?></strong> voucher yang bisa dipakai. Klik "Pilih Voucher" di atas.
                    <?php else: ?>
                        Belum ada voucher aktif. <a href="<?= url('my_vouchers.php') ?>">Klaim voucher gratis di sini →</a>
                    <?php endif; ?>
                </div>
            <?php endif; ?>
        </div>

        <!-- ============ SHIPPING OPTIONS ============ -->
        <div class="cs-section">
            <div class="cs-section-header">
                <i class="fas fa-truck accent"></i>
                <h3>Opsi Pengiriman</h3>
            </div>
            <div class="cs-shipping-list">
                <?php foreach (shipping_options() as $key => $opt): ?>
                    <label class="cs-shipping-item">
                        <input type="radio" name="shipping_method" value="<?= e($key) ?>" <?= $form['shipping_method']===$key?'checked':'' ?> required form="checkoutForm">
                        <div class="csi-info">
                            <strong><?= e($opt['label']) ?></strong>
                        </div>
                        <span class="csi-cost"><?= rupiah($opt['cost']) ?></span>
                    </label>
                <?php endforeach; ?>
            </div>
        </div>

        <!-- ============ KOIN ANOHOBBY (Shopee Coins-style) ============ -->
        <div class="cs-section">
            <div class="cs-section-header">
                <i class="fas fa-coins accent" style="color:#FFD700"></i>
                <h3>Koin AnoHobby</h3>
            </div>
            <div class="cs-coins-row">
                <div class="cs-coins-info">
                    <div class="cs-coins-balance">
                        Saldo: <strong><?= number_format($user_points_balance) ?> koin</strong>
                    </div>
                    <div class="cs-coins-max">
                        <?php if ($max_usable_pts > 0): ?>
                            Maks bisa dipakai: <strong><?= number_format($max_usable_pts) ?> koin</strong> (≈ <?= rupiah($max_usable_pts) ?>) <br>
                            <small class="muted">Maksimal 25% dari subtotal setelah voucher</small>
                        <?php else: ?>
                            <small class="muted">Koin tidak bisa dipakai (saldo kosong atau subtotal terlalu kecil)</small>
                        <?php endif; ?>
                    </div>
                </div>
                <form method="post" action="">
                    <input type="hidden" name="voucher_action" value="toggle_points">
                    <label class="cs-toggle-switch" <?= $max_usable_pts==0?'data-disabled="1"':'' ?>>
                        <input type="checkbox" name="use_points" value="1" <?= $use_points?'checked':'' ?> <?= $max_usable_pts==0?'disabled':'' ?> onchange="this.form.submit()">
                        <span class="cs-toggle-slider"></span>
                    </label>
                </form>
            </div>
            <?php if ($use_points && $points_used > 0): ?>
                <div class="cs-coins-applied">
                    <i class="fas fa-check-circle"></i> Memakai <strong><?= number_format($points_used) ?> koin</strong> = hemat <strong class="accent"><?= rupiah($points_discount) ?></strong>
                </div>
            <?php endif; ?>
        </div>

        <!-- ============ PAYMENT METHOD ============ -->
        <div class="cs-section">
            <div class="cs-section-header">
                <i class="fas fa-credit-card accent"></i>
                <h3>Metode Pembayaran</h3>
            </div>
            <div class="cs-payment-list">
                <?php foreach (payment_options() as $key => $label): ?>
                    <label class="cs-payment-item">
                        <input type="radio" name="payment_method" value="<?= e($key) ?>" <?= $form['payment_method']===$key?'checked':'' ?> required form="checkoutForm">
                        <div class="cpi-info">
                            <strong><?= e($key) ?></strong><br>
                            <small class="muted"><?= e($label) ?></small>
                        </div>
                    </label>
                <?php endforeach; ?>
            </div>
        </div>

        <!-- ============ SUBMIT BUTTON (mobile-fixed bottom) ============ -->
        <button type="submit" form="checkoutForm" class="btn btn-primary cs-submit-btn">
            <i class="fas fa-check-circle"></i> Buat Pesanan & Bayar
        </button>
    </div>

    <!-- ============ PRICE SUMMARY SIDEBAR ============ -->
    <aside class="cs-summary">
        <h3>Ringkasan Pembayaran</h3>

        <div class="summary-row">
            <span>Subtotal Pesanan</span>
            <span><?= rupiah($subtotal) ?></span>
        </div>

        <?php if ($voucher_discount > 0): ?>
            <div class="summary-row summary-row-discount">
                <span><i class="fas fa-ticket-alt"></i> Voucher <?= e($applied_uv['voucher_code_snapshot'] ?? '') ?></span>
                <span>&minus; <?= rupiah($voucher_discount) ?></span>
            </div>
        <?php endif; ?>

        <?php if ($points_discount > 0): ?>
            <div class="summary-row summary-row-discount">
                <span><i class="fas fa-coins" style="color:#FFD700"></i> Koin (<?= number_format($points_used) ?>)</span>
                <span>&minus; <?= rupiah($points_discount) ?></span>
            </div>
        <?php endif; ?>

        <div class="summary-row">
            <span>Ongkir (estimasi)</span>
            <span class="muted">pilih di kiri</span>
        </div>

        <div class="summary-row total">
            <span>Total Bayar</span>
            <span class="accent" id="grandTotalDisplay"><?= rupiah($after_points) ?></span>
        </div>

        <div class="cs-earned-points-info">
            <i class="fas fa-gift" style="color:#FFD700"></i>
            <span>Kamu akan dapat <strong id="earnedPointsDisplay"><?= calculate_earned_points($after_points) ?></strong> koin setelah checkout!</span>
        </div>

        <a href="<?= url('cart.php') ?>" class="btn btn-outline btn-block" style="margin-top:14px;"><i class="fas fa-arrow-left"></i> Kembali ke Keranjang</a>
        <div class="cs-secure-note"><i class="fas fa-shield-alt"></i> Data kamu aman &amp; terenkripsi</div>
    </aside>
</section>

<!-- ============ VOUCHER PICKER MODAL ============ -->
<div class="voucher-picker-modal" id="voucherPickerModal">
    <div class="vpm-content">
        <div class="vpm-header">
            <h3><i class="fas fa-ticket-alt"></i> Pilih Voucher</h3>
            <button type="button" class="vpm-close" onclick="document.getElementById('voucherPickerModal').style.display='none'"><i class="fas fa-times"></i></button>
        </div>
        <div class="vpm-body">
            <?php if (empty($available_uvs)): ?>
                <div class="empty-state">
                    <i class="fas fa-ticket-alt"></i>
                    <h3>Tidak ada voucher tersedia</h3>
                    <p>Klaim voucher dulu di halaman "Voucher Saya"</p>
                    <a href="<?= url('my_vouchers.php') ?>" class="btn btn-primary mt-2">Klaim Voucher</a>
                </div>
            <?php else: ?>
                <?php foreach ($available_uvs as $uv):
                    $disc_test = calculate_user_voucher_discount($uv, $subtotal);
                    $is_current = $applied_uv && $applied_uv['id'] == $uv['id'];
                ?>
                    <form method="post" action="" class="vpm-item <?= $is_current?'selected':'' ?>">
                        <input type="hidden" name="voucher_action" value="apply_voucher">
                        <input type="hidden" name="user_voucher_id" value="<?= $uv['id'] ?>">
                        <div class="vpm-item-left">
                            <div class="vpm-amount">
                                <?php if ($uv['discount_type'] === 'percent'): ?>
                                    <span class="vpm-pct"><?= (float)$uv['discount_value'] ?>%</span>
                                    <small>OFF</small>
                                <?php else: ?>
                                    <span class="vpm-rp"><?= rupiah($uv['discount_value']) ?></span>
                                    <small>OFF</small>
                                <?php endif; ?>
                            </div>
                        </div>
                        <div class="vpm-item-right">
                            <div class="vpm-code"><?= e($uv['voucher_code_snapshot']) ?></div>
                            <div class="vpm-desc"><?= e($uv['description_snapshot'] ?: user_voucher_discount_label($uv)) ?></div>
                            <div class="vpm-meta">
                                <span>Min. <?= rupiah($uv['min_purchase']) ?></span>
                                <?php if ($disc_test > 0): ?>
                                    <span class="vpm-test-disc">Hemat <?= rupiah($disc_test) ?></span>
                                <?php endif; ?>
                            </div>
                            <button type="submit" class="btn btn-<?= $is_current?'outline':'primary' ?> btn-sm">
                                <?= $is_current ? 'Dipakai' : 'Pakai' ?>
                            </button>
                        </div>
                    </form>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>
</div>

<script>
// Update grand total + earned points saat shipping dipilih
(function() {
    const shipCosts = <?= json_encode(array_map(fn($o) => $o['cost'], shipping_options())) ?>;
    const afterPoints = <?= $after_points ?>;
    const radios = document.querySelectorAll('input[name="shipping_method"]');
    const display = document.getElementById('grandTotalDisplay');
    const earnedDisplay = document.getElementById('earnedPointsDisplay');
    const fmt = n => 'Rp ' + n.toLocaleString('id-ID');
    radios.forEach(r => r.addEventListener('change', () => {
        const cost = shipCosts[r.value] || 0;
        const grand = afterPoints + cost;
        display.textContent = fmt(grand);
        if (earnedDisplay) {
            earnedDisplay.textContent = Math.floor(grand * 0.01);
        }
    }));
})();

// Close voucher modal saat click backdrop
document.getElementById('voucherPickerModal').addEventListener('click', function(e) {
    if (e.target === this) this.style.display = 'none';
});
</script>

<?php include __DIR__ . '/includes/footer.php'; ?>
