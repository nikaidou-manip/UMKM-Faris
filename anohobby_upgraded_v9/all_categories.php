<?php
require_once __DIR__ . '/includes/functions.php';
global $pdo;

$categories = get_categories($pdo);

// Ambil jumlah produk per kategori untuk ditampilkan sebagai badge
$counts = [];
foreach ($categories as $c) {
    $stmt = $pdo->prepare("SELECT COUNT(*) AS cnt, COALESCE(SUM(stock),0) AS stk FROM products WHERE category_id = ?");
    $stmt->execute([$c['id']]);
    $row = $stmt->fetch();
    $counts[$c['id']] = [
        'products' => (int)$row['cnt'],
        'stock'    => (int)$row['stk'],
    ];
}

$page_title = 'Semua Kategori';
include __DIR__ . '/includes/header.php';
?>

<!-- ============ PAGE HERO BANNER ============ -->
<section class="cat-hero" style="background-image: url('https://images.unsplash.com/photo-1503602642458-232111445657?w=1400&q=80');">
    <div class="cat-hero-inner">
        <div class="cat-breadcrumb">
            <a href="<?= url('index.php') ?>"><i class="fas fa-home"></i> Beranda</a>
            <span class="sep">/</span>
            <span>Semua Kategori</span>
        </div>
        <h1>Semua Kategori Hobby</h1>
        <p class="cat-desc">Jelajahi <?= count($categories) ?> kategori produk hobby pilihan kami &mdash; dari Model Kit, TCG Cards, PVC Figure, Tools, Paint, Resin Kit, Display, hingga Apparel. Klik kartu untuk masuk ke halaman kategori dan lihat produk lengkapnya.</p>
        <div class="cat-stats">
            <span class="stat-pill"><i class="fas fa-th-large"></i> <strong><?= count($categories) ?></strong> Kategori</span>
            <span class="stat-pill"><i class="fas fa-box"></i> <strong><?= array_sum(array_column($counts, 'products')) ?></strong> Total Produk</span>
            <span class="stat-pill"><i class="fas fa-warehouse"></i> <strong><?= array_sum(array_column($counts, 'stock')) ?></strong> Unit Stok</span>
        </div>
    </div>
</section>

<!-- ============ ALL CATEGORIES GRID ============ -->
<section class="section">
    <div class="section-header">
        <h2><i class="fas fa-th-large accent"></i> Pilih Kategori</h2>
        <a href="<?= url('products.php') ?>" class="view-all">Lihat Semua Produk <i class="fas fa-arrow-right"></i></a>
    </div>
    <div class="cat-image-grid all-grid">
        <?php foreach ($categories as $c): 
            $cnt = $counts[$c['id']]['products'];
            $stk = $counts[$c['id']]['stock'];
        ?>
            <a href="<?= url('category.php?slug=' . $c['slug']) ?>" class="cat-image-card">
                <div class="cic-bg" style="background-image: url('<?= e($c['image']) ?>');"></div>
                <div class="cic-overlay"></div>
                <div class="cic-arrow"><i class="fas fa-arrow-right"></i></div>
                <div class="cic-content">
                    <span class="cic-tag"><?= e($c['name']) ?></span>
                    <div class="cic-name"><?= e($c['name']) ?></div>
                    <div class="cic-desc"><?= e($c['description']) ?></div>
                    <div class="cic-meta">
                        <span><i class="fas fa-box"></i> <?= $cnt ?> produk</span>
                        <span><i class="fas fa-warehouse"></i> <?= $stk ?> stok</span>
                    </div>
                </div>
            </a>
        <?php endforeach; ?>
    </div>
</section>

<?php include __DIR__ . '/includes/footer.php'; ?>
