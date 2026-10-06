<?php
require_once __DIR__ . '/includes/functions.php';
global $pdo;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    $cart = get_cart();

    if ($action === 'update') {
        $product_id = (int)($_POST['product_id'] ?? 0);
        $qty = max(1, (int)($_POST['qty'] ?? 1));

        $p = get_product($pdo, $product_id);
        if ($p) {
            if ($qty > $p['stock']) {
                $qty = $p['stock'];
                set_flash('info', "Stok \"{$p['name']}\" hanya {$p['stock']} unit. Qty disesuaikan.");
            }
            $cart[$product_id] = $qty;
        }
        set_cart($cart);
    } elseif ($action === 'remove') {
        $product_id = (int)($_POST['product_id'] ?? 0);
        unset($cart[$product_id]);
        set_cart($cart);
        set_flash('success', 'Item dihapus dari keranjang.');
    } elseif ($action === 'clear') {
        set_cart([]);
        set_flash('success', 'Keranjang dikosongkan.');
    }
    header('Location: ' . url('cart.php'));
    exit;
}

$items = cart_items($pdo);
$subtotal = cart_total($pdo);

$page_title = 'Keranjang Belanja';
include __DIR__ . '/includes/header.php';
$cart_total_qty = cart_count();
$checkout_url = url('checkout.php');
$login_url = url('login.php?redirect=' . urlencode($checkout_url));
?>

<!-- ============ STEPPER ============ -->
<div class="shop-steps">
    <div class="shop-step active"><span class="ss-num">1</span><span class="ss-label">Keranjang</span></div>
    <div class="shop-step-line"></div>
    <div class="shop-step"><span class="ss-num">2</span><span class="ss-label">Checkout</span></div>
    <div class="shop-step-line"></div>
    <div class="shop-step"><span class="ss-num">3</span><span class="ss-label">Selesai</span></div>
</div>

<div class="shop-title">
    <h1><i class="fas fa-shopping-bag"></i> Keranjang Belanja</h1>
    <?php if (!empty($items)): ?>
        <span class="count-pill"><?= $cart_total_qty ?> item</span>
    <?php endif; ?>
</div>

<section class="cart-wrap">
    <div>
        <?php if (empty($items)): ?>
            <div class="cart-empty">
                <div class="ce-icon"><i class="fas fa-shopping-bag"></i></div>
                <h3>Keranjang kamu masih kosong</h3>
                <p>Yuk jelajahi produk hobby terbaik di ANOHOBBY!</p>
                <a href="<?= url('products.php') ?>" class="btn btn-primary btn-lg"><i class="fas fa-store"></i> Mulai Belanja</a>
            </div>
        <?php else: ?>
            <div class="cp-list">
                <?php foreach ($items as $it):
                    $oos = $it['stock'] <= 0;
                    $low = !$oos && $it['stock'] <= 5;
                ?>
                    <div class="cp-item <?= $oos ? 'oos' : '' ?>">
                        <a href="<?= url('product_detail.php?id=' . $it['product_id']) ?>" class="cp-thumb">
                            <img src="<?= e($it['image']) ?>" alt="<?= e($it['name']) ?>" loading="lazy">
                        </a>

                        <div class="cp-info">
                            <a href="<?= url('product_detail.php?id=' . $it['product_id']) ?>" class="cp-name"><?= e($it['name']) ?></a>
                            <div class="cp-price-row">
                                <span class="cp-price"><?= rupiah($it['price']) ?></span>
                                <?php if (!empty($it['is_flash_sale'])): ?>
                                    <span class="cp-price-strike"><?= rupiah($it['original_price']) ?></span>
                                    <span class="cp-fs-badge"><i class="fas fa-bolt"></i> -<?= (int)$it['flash_sale_discount'] ?>%</span>
                                <?php endif; ?>
                            </div>
                            <div class="cp-stock <?= $oos ? 'out' : ($low ? 'low' : '') ?>">
                                <?php if ($oos): ?>
                                    <i class="fas fa-times-circle"></i> Stok habis
                                <?php elseif ($low): ?>
                                    <i class="fas fa-exclamation-circle"></i> Sisa <?= $it['stock'] ?> unit lagi
                                <?php else: ?>
                                    <i class="fas fa-check-circle"></i> Stok tersedia (<?= $it['stock'] ?>)
                                <?php endif; ?>
                            </div>
                            <form method="post" action="" data-auto-submit class="cp-qty-form">
                                <input type="hidden" name="action" value="update">
                                <input type="hidden" name="product_id" value="<?= $it['product_id'] ?>">
                                <span class="cp-qty" data-qty-group>
                                    <button type="button" data-qty-dec aria-label="Kurangi">&minus;</button>
                                    <input type="number" name="qty" value="<?= $it['qty'] ?>" min="1" max="<?= max(1, $it['stock']) ?>" aria-label="Jumlah">
                                    <button type="button" data-qty-inc aria-label="Tambah">+</button>
                                </span>
                            </form>
                        </div>

                        <div class="cp-side">
                            <div>
                                <div class="cp-subtotal-label">Subtotal</div>
                                <div class="cp-subtotal"><?= rupiah($it['subtotal']) ?></div>
                            </div>
                            <form method="post" action="" onsubmit="return confirm('Hapus item ini dari keranjang?')">
                                <input type="hidden" name="action" value="remove">
                                <input type="hidden" name="product_id" value="<?= $it['product_id'] ?>">
                                <button type="submit" class="cp-remove"><i class="fas fa-trash-alt"></i> Hapus</button>
                            </form>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>

            <div class="cp-footer-actions">
                <a href="<?= url('products.php') ?>" class="btn btn-outline"><i class="fas fa-arrow-left"></i> Lanjut Belanja</a>
                <form method="post" action="" onsubmit="return confirm('Kosongkan keranjang?')">
                    <input type="hidden" name="action" value="clear">
                    <button type="submit" class="cp-clear"><i class="fas fa-trash"></i> Kosongkan keranjang</button>
                </form>
            </div>
        <?php endif; ?>
    </div>

    <?php if (!empty($items)): ?>
        <aside class="cart-summary">
            <h3>Ringkasan Belanja</h3>
            <div class="summary-row">
                <span>Subtotal (<?= $cart_total_qty ?> item)</span>
                <span><?= rupiah($subtotal) ?></span>
            </div>
            <div class="summary-row muted">
                <span>Ongkos kirim</span>
                <span>Dihitung di checkout</span>
            </div>
            <div class="summary-row total">
                <span>Total</span>
                <span class="accent"><?= rupiah($subtotal) ?></span>
            </div>

            <?php if (!is_logged_in()): ?>
                <div class="cart-login-note">
                    <i class="fas fa-info-circle"></i>
                    <span>Kamu perlu <a href="<?= $login_url ?>"><strong>login</strong></a> dulu untuk checkout.</span>
                </div>
                <a href="<?= $login_url ?>" class="btn btn-primary btn-block btn-lg"><i class="fas fa-sign-in-alt"></i> Login untuk Checkout</a>
            <?php else: ?>
                <a href="<?= $checkout_url ?>" class="btn btn-primary btn-block btn-lg" style="margin-top:16px;"><i class="fas fa-lock"></i> Lanjut ke Checkout</a>
            <?php endif; ?>

            <div class="cs-trust">
                <div><i class="fas fa-shield-alt"></i> Transaksi aman &amp; terpercaya</div>
                <div><i class="fas fa-ticket-alt"></i> Pakai voucher &amp; koin di checkout</div>
                <div><i class="fas fa-truck"></i> Pilihan pengiriman fleksibel</div>
            </div>
        </aside>
    <?php endif; ?>
</section>

<?php include __DIR__ . '/includes/footer.php'; ?>
