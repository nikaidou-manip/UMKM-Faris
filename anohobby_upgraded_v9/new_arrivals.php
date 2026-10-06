<?php
require_once __DIR__ . '/includes/functions.php';
global $pdo;

// Auto-cleanup sudah dijalankan di header.php
$sort = $_GET['sort'] ?? 'newest';

// Ambil semua produk NEW arrival (limit besar untuk page khusus)
try {
    $products = get_new_arrival_products($pdo, 60);
} catch (Throwable $e) {
    $products = [];
}

// Apply sort
if ($sort === 'price_asc') {
    usort($products, fn($a, $b) => $a['price'] <=> $b['price']);
} elseif ($sort === 'price_desc') {
    usort($products, fn($a, $b) => $b['price'] <=> $a['price']);
} elseif ($sort === 'name') {
    usort($products, fn($a, $b) => strcasecmp($a['name'], $b['name']));
} else {
    // newest: sort by created_at desc (sudah default dari query)
    usort($products, fn($a, $b) => strtotime($b['created_at']) <=> strtotime($a['created_at']));
}

$page_title = 'New Arrivals';
include __DIR__ . '/includes/header.php';
?>

<div class="container" style="margin-top:16px;">
    <div class="breadcrumb">
        <a href="<?= url('index.php') ?>">Beranda</a>
        <span class="sep">/</span>
        <span>New Arrivals</span>
    </div>
</div>

<!-- ============ NEW ARRIVALS HERO ============ -->
<section class="na-hero">
    <div class="na-hero-bg"></div>
    <div class="na-hero-pattern"></div>
    <div class="na-hero-content">
        <span class="hb-badge yellow"><i class="fas fa-sparkles"></i> FRESH DROP</span>
        <h1 class="hb-title-lg">NEW ARRIVALS</h1>
        <p class="hb-subtitle">Produk-produk terbaru yang baru saja ditambahkan ke toko. Tag <strong>NEW</strong> otomatis hilang setelah 7 hari real-time.</p>
        <div class="na-hero-stats">
            <span><i class="fas fa-box"></i> <strong><?= count($products) ?></strong> produk baru</span>
            <span><i class="fas fa-clock"></i> Auto-expire <strong>7 hari</strong></span>
        </div>
    </div>
</section>

<!-- ============ PRODUCTS GRID ============ -->
<section class="section">
    <div class="section-header">
        <h2><i class="fas fa-sparkles accent"></i> Produk Terbaru <span class="muted" style="font-size:14px;font-weight:400;">(<?= count($products) ?> produk)</span></h2>
        <form method="get" action="" style="display:flex;gap:8px;align-items:center;">
            <select name="sort" onchange="this.form.submit()" class="filter-bar-input" style="padding:8px 12px;border:1.5px solid var(--border);border-radius:8px;font-size:13px;">
                <option value="newest" <?= $sort==='newest'?'selected':'' ?>>Terbaru</option>
                <option value="price_asc" <?= $sort==='price_asc'?'selected':'' ?>>Harga Termurah</option>
                <option value="price_desc" <?= $sort==='price_desc'?'selected':'' ?>>Harga Termahal</option>
                <option value="name" <?= $sort==='name'?'selected':'' ?>>Nama A-Z</option>
            </select>
        </form>
    </div>

    <?php if (empty($products)): ?>
        <div class="empty-state">
            <i class="fas fa-box-open"></i>
            <h3>Belum ada produk NEW saat ini</h3>
            <p>Produk yang baru ditambahkan akan muncul di sini selama 7 hari. Pantau terus!</p>
            <a href="<?= url('products.php') ?>" class="btn btn-primary mt-2"><i class="fas fa-th"></i> Lihat Semua Produk</a>
        </div>
    <?php else: ?>
        <div class="product-grid">
            <?php foreach ($products as $p):
                // Hitung sisa hari NEW
                $age_days = floor((time() - strtotime($p['created_at'])) / 86400);
                $days_left = max(0, 7 - $age_days);
            ?>
                <div class="product-card product-card-new">
                    <a href="<?= url('product_detail.php?id=' . $p['id']) ?>" class="pc-image">
                        <span class="pc-badge badge-new">
                            <i class="fas fa-bolt"></i> NEW
                        </span>
                        <?php if ($days_left <= 2): ?>
                            <span class="pc-countdown-warning">Sisa <?= $days_left ?> hari!</span>
                        <?php endif; ?>
                        <span class="pc-cat"><?= e($p['category_name']) ?></span>
                        <img src="<?= e($p['image']) ?>" alt="<?= e($p['name']) ?>" loading="lazy">
                    </a>
                    <div class="pc-body">
                        <a href="<?= url('product_detail.php?id=' . $p['id']) ?>" class="pc-title"><?= e($p['name']) ?></a>
                        <div class="pc-release">
                            <?= e($p['release_date'] ?: 'Ready Stock') ?> &middot;
                            <span class="<?= $p['stock']>0?'text-success':'text-danger' ?>"><?= $p['stock']>0?'Stok: '.$p['stock']:'Habis' ?></span>
                        </div>
                        <div class="pc-price"><?= rupiah($p['price']) ?></div>
                        <div class="pc-actions">
                            <a href="<?= url('product_detail.php?id=' . $p['id']) ?>" class="btn btn-outline btn-sm"><i class="fas fa-eye"></i> Detail</a>
                            <form action="<?= url('cart_add.php') ?>" method="post" style="flex:1;">
                                <input type="hidden" name="product_id" value="<?= $p['id'] ?>">
                                <input type="hidden" name="qty" value="1">
                                <button type="submit" class="btn btn-primary btn-sm" style="width:100%;" <?= $p['stock']<=0?'disabled':'' ?>><i class="fas fa-plus"></i> Cart</button>
                            </form>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</section>

<?php include __DIR__ . '/includes/footer.php'; ?>
