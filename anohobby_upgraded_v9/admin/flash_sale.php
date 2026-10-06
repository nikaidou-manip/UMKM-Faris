<?php
require_once __DIR__ . '/../includes/functions.php';
require_admin();
global $pdo;

// ============================================================
// HANDLE ACTIONS (sebelum admin_header untuk redirect works)
// ============================================================

// ===== ADD PRODUCT TO FLASH SALE =====
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'add_to_flashsale') {
    $product_id = (int)($_POST['product_id'] ?? 0);
    $ends_at_input = trim($_POST['ends_at'] ?? '');
    $custom_discount = trim($_POST['custom_discount'] ?? '');

    if ($product_id <= 0) {
        set_flash('error', 'Produk tidak valid.');
    } else {
        $p = get_product($pdo, $product_id);
        if (!$p) {
            set_flash('error', 'Produk tidak ditemukan.');
        } else {
            // Hitung ends_at
            $ends_at = $ends_at_input ? date('Y-m-d H:i:s', strtotime($ends_at_input)) : date('Y-m-d H:i:s', strtotime('+3 days'));
            if (strtotime($ends_at) <= time()) {
                set_flash('error', 'Tanggal berakhir harus di masa depan.');
            } else {
                // Hitung discount
                if ($custom_discount !== '') {
                    $disc = (int)$custom_discount;
                    if ($disc < 25 || $disc > 90) {
                        set_flash('error', 'Diskon harus antara 25% s.d 90%.');
                        header('Location: ' . url('admin/flash_sale.php'));
                        exit;
                    }
                } else {
                    $disc = generate_weighted_flash_sale_discount();
                }

                $stmt = $pdo->prepare("UPDATE products SET badge='FLASH SALE', flash_sale_ends_at=?, flash_sale_discount=? WHERE id=?");
                $stmt->execute([$ends_at, $disc, $product_id]);
                set_flash('success', "Produk \"{$p['name']}\" ditambahkan ke Flash Sale dengan diskon {$disc}%. Berakhir " . tgl_indo($ends_at) . ".");
            }
        }
    }
    header('Location: ' . url('admin/flash_sale.php'));
    exit;
}

// ===== EDIT FLASH SALE (discount + ends_at) =====
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'edit_flashsale') {
    $product_id = (int)($_POST['product_id'] ?? 0);
    $ends_at_input = trim($_POST['ends_at'] ?? '');
    $discount = (int)($_POST['discount'] ?? 0);

    if ($product_id <= 0) {
        set_flash('error', 'Produk tidak valid.');
    } elseif ($discount < 25 || $discount > 90) {
        set_flash('error', 'Diskon harus antara 25% s.d 90%.');
    } else {
        $ends_at = $ends_at_input ? date('Y-m-d H:i:s', strtotime($ends_at_input)) : null;
        if ($ends_at && strtotime($ends_at) <= time()) {
            set_flash('error', 'Tanggal berakhir harus di masa depan.');
        } else {
            $stmt = $pdo->prepare("UPDATE products SET flash_sale_ends_at=?, flash_sale_discount=? WHERE id=? AND badge='FLASH SALE'");
            $stmt->execute([$ends_at, $discount, $product_id]);
            $p = get_product($pdo, $product_id);
            $name = $p ? $p['name'] : "ID #$product_id";
            set_flash('success', "Flash sale \"{$name}\" diperbarui: diskon {$discount}%, berakhir " . ($ends_at ? tgl_indo($ends_at) : '(tanpa batas)') . ".");
        }
    }
    header('Location: ' . url('admin/flash_sale.php'));
    exit;
}

// ===== REGENERATE RANDOM DISCOUNT (single) =====
if (isset($_GET['regenerate'])) {
    $product_id = (int)$_GET['regenerate'];
    $p = get_product($pdo, $product_id);
    if ($p && $p['badge'] === 'FLASH SALE') {
        $new_disc = generate_weighted_flash_sale_discount();
        $pdo->prepare("UPDATE products SET flash_sale_discount=? WHERE id=?")->execute([$new_disc, $product_id]);
        set_flash('success', "Diskon flash sale \"{$p['name']}\" di-generate ulang: {$new_disc}%.");
    } else {
        set_flash('error', 'Produk tidak valid atau bukan flash sale.');
    }
    header('Location: ' . url('admin/flash_sale.php'));
    exit;
}

// ===== END FLASH SALE (remove badge + clear ends_at + discount) =====
if (isset($_GET['end'])) {
    $product_id = (int)$_GET['end'];
    $p = get_product($pdo, $product_id);
    if ($p && $p['badge'] === 'FLASH SALE') {
        $pdo->prepare("UPDATE products SET badge=NULL, flash_sale_ends_at=NULL, flash_sale_discount=0 WHERE id=?")->execute([$product_id]);
        set_flash('success', "Flash sale dihentikan untuk \"{$p['name']}\".");
    }
    header('Location: ' . url('admin/flash_sale.php'));
    exit;
}

// ===== BULK ACTIONS =====
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'bulk_extend') {
    $days = (int)($_POST['extend_days'] ?? 0);
    if ($days <= 0 || $days > 365) {
        set_flash('error', 'Jumlah hari tidak valid (1-365).');
    } else {
        // Untuk yang sudah ended atau masih aktif, extend by X days from NOW
        $new_end = date('Y-m-d H:i:s', strtotime("+{$days} days"));
        $stmt = $pdo->prepare("UPDATE products SET flash_sale_ends_at=? WHERE badge='FLASH SALE'");
        $stmt->execute([$new_end]);
        $count = $stmt->rowCount();
        set_flash('success', "Flash sale {$count} produk di-extend {$days} hari (sampai " . tgl_indo($new_end) . ").");
    }
    header('Location: ' . url('admin/flash_sale.php'));
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'bulk_regenerate') {
    $products = $pdo->query("SELECT id, name FROM products WHERE badge='FLASH SALE'")->fetchAll();
    $count = 0;
    $stmt = $pdo->prepare("UPDATE products SET flash_sale_discount=? WHERE id=?");
    foreach ($products as $p) {
        $new_disc = generate_weighted_flash_sale_discount();
        $stmt->execute([$new_disc, $p['id']]);
        $count++;
    }
    set_flash('success', "Diskon {$count} produk flash sale di-generate ulang dengan weighted random.");
    header('Location: ' . url('admin/flash_sale.php'));
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'bulk_end_all') {
    $pdo->exec("UPDATE products SET badge=NULL, flash_sale_ends_at=NULL, flash_sale_discount=0 WHERE badge='FLASH SALE'");
    $count = $pdo->query("SELECT ROW_COUNT()")->fetchColumn();
    set_flash('success', "Semua flash sale dihentikan ({$count} produk).");
    header('Location: ' . url('admin/flash_sale.php'));
    exit;
}

// ============================================================
// LOAD DATA
// ============================================================
require_once __DIR__ . '/../includes/admin_header.php';

// ===== STATS =====
try {
    $stats = $pdo->query("
        SELECT
            COUNT(*) AS total_fs,
            SUM(CASE WHEN flash_sale_ends_at > NOW() THEN 1 ELSE 0 END) AS active_fs,
            SUM(CASE WHEN flash_sale_ends_at <= NOW() THEN 1 ELSE 0 END) AS expired_fs,
            AVG(flash_sale_discount) AS avg_disc,
            MAX(flash_sale_discount) AS max_disc,
            MIN(flash_sale_discount) AS min_disc
        FROM products WHERE badge='FLASH SALE'
    ")->fetch();
    if (!$stats) $stats = ['total_fs'=>0,'active_fs'=>0,'expired_fs'=>0,'avg_disc'=>0,'max_disc'=>0,'min_disc'=>0];
} catch (Throwable $e) {
    $stats = ['total_fs'=>0,'active_fs'=>0,'expired_fs'=>0,'avg_disc'=>0,'max_disc'=>0,'min_disc'=>0];
    set_flash('error', 'Kolom flash_sale belum ada di database. Jalankan database/migration_upgrade.sql lalu database/migration_upgrade_v2.sql.');
}

// Distribusi diskon
try {
    $distribution = $pdo->query("
        SELECT
            SUM(CASE WHEN flash_sale_discount BETWEEN 25 AND 35 THEN 1 ELSE 0 END) AS bucket_25_35,
            SUM(CASE WHEN flash_sale_discount BETWEEN 36 AND 50 THEN 1 ELSE 0 END) AS bucket_36_50,
            SUM(CASE WHEN flash_sale_discount BETWEEN 51 AND 65 THEN 1 ELSE 0 END) AS bucket_51_65,
            SUM(CASE WHEN flash_sale_discount BETWEEN 66 AND 75 THEN 1 ELSE 0 END) AS bucket_66_75,
            SUM(CASE WHEN flash_sale_discount BETWEEN 76 AND 85 THEN 1 ELSE 0 END) AS bucket_76_85,
            SUM(CASE WHEN flash_sale_discount BETWEEN 86 AND 90 THEN 1 ELSE 0 END) AS bucket_86_90
        FROM products WHERE badge='FLASH SALE'
    ")->fetch();
    if (!$distribution) {
        $distribution = ['bucket_25_35'=>0,'bucket_36_50'=>0,'bucket_51_65'=>0,'bucket_66_75'=>0,'bucket_76_85'=>0,'bucket_86_90'=>0];
    }
} catch (Throwable $e) {
    $distribution = ['bucket_25_35'=>0,'bucket_36_50'=>0,'bucket_51_65'=>0,'bucket_66_75'=>0,'bucket_76_85'=>0,'bucket_86_90'=>0];
}

// ===== FLASH SALE PRODUCTS =====
try {
    $fs_products = $pdo->query("
        SELECT p.*, c.name AS category_name
        FROM products p JOIN categories c ON p.category_id = c.id
        WHERE p.badge='FLASH SALE'
        ORDER BY (p.flash_sale_ends_at > NOW()) DESC, p.flash_sale_ends_at ASC
    ")->fetchAll();
} catch (Throwable $e) {
    $fs_products = [];
}

// ===== SEARCH PRODUCTS (untuk add to flash sale) =====
$search_q = trim($_GET['q'] ?? '');
$search_results = [];
if ($search_q !== '') {
    $stmt = $pdo->prepare("
        SELECT p.*, c.name AS category_name
        FROM products p JOIN categories c ON p.category_id = c.id
        WHERE p.badge <> 'FLASH SALE' AND (p.name LIKE ? OR p.brand LIKE ?)
        ORDER BY p.created_at DESC LIMIT 20
    ");
    $stmt->execute(["%$search_q%", "%$search_q%"]);
    $search_results = $stmt->fetchAll();
}

$page_title = 'Flash Sale Manager';
?>

<!-- ============ STATS CARDS ============ -->
<div class="stat-grid">
    <div class="stat-card">
        <div class="stat-info">
            <div class="stat-label">Total Flash Sale</div>
            <div class="stat-value"><?= (int)$stats['total_fs'] ?></div>
            <small class="text-muted">produk terdaftar</small>
        </div>
        <div class="stat-icon orange"><i class="fas fa-bolt"></i></div>
    </div>
    <div class="stat-card">
        <div class="stat-info">
            <div class="stat-label">Aktif Sekarang</div>
            <div class="stat-value text-success"><?= (int)$stats['active_fs'] ?></div>
            <small class="text-muted">masih berjalan</small>
        </div>
        <div class="stat-icon green"><i class="fas fa-play-circle"></i></div>
    </div>
    <div class="stat-card">
        <div class="stat-info">
            <div class="stat-label">Sudah Berakhir</div>
            <div class="stat-value text-danger"><?= (int)$stats['expired_fs'] ?></div>
            <small class="text-muted">perlu diperpanjang</small>
        </div>
        <div class="stat-icon purple"><i class="fas fa-clock"></i></div>
    </div>
    <div class="stat-card">
        <div class="stat-info">
            <div class="stat-label">Rata-rata Diskon</div>
            <div class="stat-value"><?= !empty($stats['avg_disc']) ? round((float)$stats['avg_disc'], 1) . '%' : '0%' ?></div>
            <small class="text-muted">min <?= (int)($stats['min_disc'] ?? 0) ?>% · max <?= (int)($stats['max_disc'] ?? 0) ?>%</small>
        </div>
        <div class="stat-icon" style="background:#FFF3E0;color:#FF8F00;"><i class="fas fa-percentage"></i></div>
    </div>
</div>

<!-- ============ DISTRIBUTION CHART ============ -->
<?php if ((int)$stats['total_fs'] > 0): ?>
<div class="panel" style="margin-bottom:20px;">
    <div class="panel-header">
        <h2><i class="fas fa-chart-bar"></i> Distribusi Diskon</h2>
    </div>
    <div class="fs-dist-chart">
        <?php
        $buckets = [
            ['label' => '25-35%', 'val' => (int)$distribution['bucket_25_35'], 'color' => '#4CAF50', 'expected' => 30],
            ['label' => '36-50%', 'val' => (int)$distribution['bucket_36_50'], 'color' => '#8BC34A', 'expected' => 30],
            ['label' => '51-65%', 'val' => (int)$distribution['bucket_51_65'], 'color' => '#FFC107', 'expected' => 20],
            ['label' => '66-75%', 'val' => (int)$distribution['bucket_66_75'], 'color' => '#FF9800', 'expected' => 12],
            ['label' => '76-85%', 'val' => (int)$distribution['bucket_76_85'], 'color' => '#FF5722', 'expected' => 6],
            ['label' => '86-90%', 'val' => (int)$distribution['bucket_86_90'], 'color' => '#F44336', 'expected' => 2],
        ];
        $total = (int)$stats['total_fs'];
        $vals = array_column($buckets, 'val');
        $max_val = max(1, ...$vals);
        foreach ($buckets as $b):
            $pct_of_total = $total > 0 ? round(($b['val'] / $total) * 100, 1) : 0;
            $bar_width = $max_val > 0 ? ($b['val'] / $max_val) * 100 : 0;
        ?>
            <div class="fs-dist-row">
                <div class="fs-dist-label"><?= $b['label'] ?></div>
                <div class="fs-dist-bar-wrap">
                    <div class="fs-dist-bar" style="width: <?= $bar_width ?>%; background: <?= $b['color'] ?>;">
                        <span class="fs-dist-count"><?= $b['val'] ?> (<?= $pct_of_total ?>%)</span>
                    </div>
                </div>
                <div class="fs-dist-expected">target: <?= $b['expected'] ?>%</div>
            </div>
        <?php endforeach; ?>
    </div>
</div>
<?php endif; ?>

<!-- ============ BULK ACTIONS ============ -->
<div class="panel" style="margin-bottom:20px;">
    <div class="panel-header">
        <h2><i class="fas fa-bolt"></i> Aksi Massal</h2>
    </div>
    <div class="fs-bulk-actions">
        <form method="post" action="" class="fs-bulk-form" onsubmit="return confirm('Extend semua flash sale yang aktif?')">
            <input type="hidden" name="action" value="bulk_extend">
            <label>Perpanjang semua:</label>
            <input type="number" name="extend_days" min="1" max="365" value="3" required style="width:80px;">
            <span>hari dari sekarang</span>
            <button type="submit" class="btn btn-primary btn-sm"><i class="fas fa-plus"></i> Extend Semua</button>
        </form>
        <form method="post" action="" class="fs-bulk-form" onsubmit="return confirm('Generate ulang diskon semua flash sale? Distribusi baru akan random.')">
            <input type="hidden" name="action" value="bulk_regenerate">
            <button type="submit" class="btn btn-outline btn-sm"><i class="fas fa-dice"></i> Regenerate Semua Diskon</button>
        </form>
        <form method="post" action="" class="fs-bulk-form" onsubmit="return confirm('HENTIKAN semua flash sale? Tidak bisa diundo.')">
            <input type="hidden" name="action" value="bulk_end_all">
            <button type="submit" class="btn btn-danger btn-sm"><i class="fas fa-stop"></i> Hentikan Semua Flash Sale</button>
        </form>
    </div>
</div>

<!-- ============ ADD PRODUCT TO FLASH SALE ============ -->
<div class="panel" style="margin-bottom:20px;">
    <div class="panel-header">
        <h2><i class="fas fa-plus-circle"></i> Tambah Produk ke Flash Sale</h2>
    </div>
    <form method="get" action="" style="margin-bottom:14px;">
        <input type="text" name="q" value="<?= e($search_q) ?>" placeholder="Cari nama produk atau brand..." class="form-control" style="max-width:400px;display:inline-block;">
        <button type="submit" class="btn btn-primary btn-sm"><i class="fas fa-search"></i> Cari</button>
        <?php if ($search_q): ?>
            <a href="<?= url('admin/flash_sale.php') ?>" class="btn btn-outline btn-sm"><i class="fas fa-times"></i> Reset</a>
        <?php endif; ?>
    </form>

    <?php if ($search_q !== ''): ?>
        <?php if (empty($search_results)): ?>
            <div class="empty-state" style="padding:20px;">
                <i class="fas fa-search"></i>
                <h3>Tidak ada produk ditemukan</h3>
                <p>Coba kata kunci lain.</p>
            </div>
        <?php else: ?>
            <div style="overflow-x:auto;">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>Produk</th>
                            <th>Kategori</th>
                            <th>Harga Asli</th>
                            <th>Stok</th>
                            <th>Set Diskon</th>
                            <th>Berakhir Pada</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($search_results as $p): ?>
                            <tr>
                                <td>
                                    <img src="<?= e($p['image']) ?>" alt="" class="thumb" style="display:inline-block;vertical-align:middle;margin-right:8px;">
                                    <strong><?= e($p['name']) ?></strong>
                                </td>
                                <td><?= e($p['category_name']) ?></td>
                                <td class="text-primary" style="font-weight:700;"><?= rupiah($p['price']) ?></td>
                                <td><?= $p['stock'] > 0 ? "<span class='text-success'>{$p['stock']}</span>" : "<span class='text-danger'>Habis</span>" ?></td>
                                <form method="post" action="">
                                    <td>
                                        <input type="hidden" name="action" value="add_to_flashsale">
                                        <input type="hidden" name="product_id" value="<?= $p['id'] ?>">
                                        <select name="custom_discount" class="form-control" style="width:140px;display:inline-block;font-size:12px;">
                                            <option value="">Auto Random (25-90%)</option>
                                            <option value="25">25%</option>
                                            <option value="30">30%</option>
                                            <option value="35">35%</option>
                                            <option value="40">40%</option>
                                            <option value="45">45%</option>
                                            <option value="50">50%</option>
                                            <option value="55">55%</option>
                                            <option value="60">60%</option>
                                            <option value="65">65%</option>
                                            <option value="70">70%</option>
                                            <option value="75">75%</option>
                                            <option value="80">80%</option>
                                            <option value="85">85%</option>
                                            <option value="90">90%</option>
                                        </select>
                                    </td>
                                    <td>
                                        <input type="datetime-local" name="ends_at" class="form-control" style="width:200px;display:inline-block;font-size:12px;" value="<?= date('Y-m-d\TH:i', strtotime('+3 days')) ?>">
                                    </td>
                                    <td>
                                        <button type="submit" class="btn btn-primary btn-sm"><i class="fas fa-bolt"></i> Jadikan Flash Sale</button>
                                    </td>
                                </form>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    <?php else: ?>
        <div class="fs-add-hint">
            <i class="fas fa-info-circle"></i>
            Cari produk di atas untuk ditambahkan ke flash sale. Diskon akan otomatis di-generate weighted random (25-90%), atau pilih manual.
        </div>
    <?php endif; ?>
</div>

<!-- ============ FLASH SALE PRODUCTS LIST ============ -->
<div class="panel">
    <div class="panel-header">
        <h2><i class="fas fa-list"></i> Daftar Produk Flash Sale (<?= count($fs_products) ?>)</h2>
    </div>

    <?php if (empty($fs_products)): ?>
        <div class="empty-state">
            <i class="fas fa-bolt"></i>
            <h3>Belum ada produk flash sale</h3>
            <p>Gunakan form "Tambah Produk ke Flash Sale" di atas untuk memulai.</p>
        </div>
    <?php else: ?>
        <div style="overflow-x:auto;">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Produk</th>
                        <th>Harga Asli</th>
                        <th>Diskon</th>
                        <th>Harga Sale</th>
                        <th>Stok</th>
                        <th>Berakhir Pada</th>
                        <th>Sisa Waktu</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($fs_products as $p):
                        $sale_price = flash_sale_price($p);
                        $remaining = flash_sale_remaining_seconds($p);
                        $is_active = $remaining > 0;
                        $remaining_days = floor($remaining / 86400);
                        $remaining_hours = floor(($remaining % 86400) / 3600);
                    ?>
                        <tr class="<?= $is_active ? '' : 'fs-row-expired' ?>">
                            <td>
                                <img src="<?= e($p['image']) ?>" alt="" class="thumb" style="display:inline-block;vertical-align:middle;margin-right:8px;">
                                <div style="display:inline-block;vertical-align:middle;">
                                    <strong><?= e($p['name']) ?></strong>
                                    <br>
                                    <small class="text-muted"><?= e($p['category_name']) ?> · #<?= $p['id'] ?></small>
                                </div>
                            </td>
                            <td class="text-muted" style="text-decoration:line-through;"><?= rupiah($p['price']) ?></td>
                            <td>
                                <span class="fs-disc-badge" style="background: <?= $p['flash_sale_discount'] >= 76 ? '#F44336' : ($p['flash_sale_discount'] >= 51 ? '#FF9800' : '#4CAF50') ?>;">
                                    <?= (int)$p['flash_sale_discount'] ?>%
                                </span>
                            </td>
                            <td class="text-primary" style="font-weight:700;"><?= rupiah($sale_price) ?></td>
                            <td>
                                <?php if ($p['stock'] > 0): ?>
                                    <span class="text-success"><?= $p['stock'] ?></span>
                                <?php else: ?>
                                    <span class="text-danger">Habis</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <?php if ($p['flash_sale_ends_at']): ?>
                                    <?= date('d M Y H:i', strtotime($p['flash_sale_ends_at'])) ?>
                                <?php else: ?>
                                    <span class="text-muted">tanpa batas</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <?php if ($is_active): ?>
                                    <span class="text-success">
                                        <?php if ($remaining_days > 0): ?>
                                            <?= $remaining_days ?>h <?= $remaining_hours ?>j
                                        <?php else: ?>
                                            <?= floor($remaining / 3600) ?>j <?= floor(($remaining % 3600) / 60) ?>m
                                        <?php endif; ?>
                                    </span>
                                <?php else: ?>
                                    <span class="text-danger">expired</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <div class="table-actions">
                                    <a href="<?= url('admin/flash_sale.php?regenerate=' . $p['id']) ?>" title="Regenerate Diskon" onclick="return confirm('Generate ulang diskon random untuk produk ini?')"><i class="fas fa-dice"></i></a>
                                    <a href="#" onclick="openEditModal(<?= $p['id'] ?>, <?= (int)$p['flash_sale_discount'] ?>, '<?= e($p['flash_sale_ends_at'] ? date('Y-m-d\TH:i', strtotime($p['flash_sale_ends_at'])) : '') ?>'); return false;" title="Edit"><i class="fas fa-edit"></i></a>
                                    <a href="<?= url('product_detail.php?id=' . $p['id']) ?>" target="_blank" title="Lihat"><i class="fas fa-eye"></i></a>
                                    <a href="<?= url('admin/flash_sale.php?end=' . $p['id']) ?>" class="danger" title="Hentikan Flash Sale" onclick="return confirm('Hentikan flash sale untuk produk ini?')"><i class="fas fa-stop"></i></a>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php endif; ?>
</div>

<!-- ============ EDIT MODAL ============ -->
<div class="fs-edit-modal" id="fsEditModal">
    <div class="fsem-content">
        <div class="fsem-header">
            <h3><i class="fas fa-edit"></i> Edit Flash Sale</h3>
            <button type="button" class="fsem-close" onclick="document.getElementById('fsEditModal').style.display='none'"><i class="fas fa-times"></i></button>
        </div>
        <form method="post" action="">
            <input type="hidden" name="action" value="edit_flashsale">
            <input type="hidden" name="product_id" id="fsem_product_id">
            <div class="fsem-body">
                <div class="form-group">
                    <label class="form-label">Diskon (%)</label>
                    <input type="number" name="discount" id="fsem_discount" min="25" max="90" required class="form-control">
                    <div class="form-hint">Range: 25% s.d 90%. <a href="#" onclick="document.forms[0].discount.value = <?= json_encode(generate_weighted_flash_sale_discount()) ?>; return false;">Generate random</a></div>
                </div>
                <div class="form-group">
                    <label class="form-label">Berakhir Pada</label>
                    <input type="datetime-local" name="ends_at" id="fsem_ends_at" class="form-control">
                    <div class="form-hint">Kosongkan jika ingin tanpa batas waktu.</div>
                </div>
            </div>
            <div class="fsem-footer">
                <button type="button" class="btn btn-outline" onclick="document.getElementById('fsEditModal').style.display='none'">Batal</button>
                <button type="submit" class="btn btn-primary"><i class="fas fa-save"></i> Simpan</button>
            </div>
        </form>
    </div>
</div>

<script>
function openEditModal(productId, currentDiscount, currentEndsAt) {
    document.getElementById('fsem_product_id').value = productId;
    document.getElementById('fsem_discount').value = currentDiscount;
    document.getElementById('fsem_ends_at').value = currentEndsAt;
    document.getElementById('fsEditModal').style.display = 'flex';
}
document.getElementById('fsEditModal').addEventListener('click', function(e) {
    if (e.target === this) this.style.display = 'none';
});
</script>

<?php require_once __DIR__ . '/../includes/admin_footer.php'; ?>
