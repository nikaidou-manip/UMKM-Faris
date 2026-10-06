<?php
require_once __DIR__ . '/includes/functions.php';
global $pdo;

$slug = $_GET['slug'] ?? '';
if (!$slug) {
    set_flash('error', 'Kategori tidak ditemukan.');
    header('Location: ' . url('index.php'));
    exit;
}

$category = get_category_by_slug($pdo, $slug);
if (!$category) {
    set_flash('error', 'Kategori tidak ditemukan.');
    header('Location: ' . url('index.php'));
    exit;
}

$sort = $_GET['sort'] ?? 'newest';
$products = get_products_by_category_slug($pdo, $slug, $sort);
$stats = get_category_stats($pdo, $category['id']);
$categories = get_categories($pdo);
// Ambil filter dari GET
$filter_brand = trim($_GET['filter_brand'] ?? '');
$filters = [];
if ($filter_brand) {
    $filters['brand'] = $filter_brand;
}

// Ambil produk dengan filter
$products = get_products_by_category_slug($pdo, $slug, $sort, $filters);

// Produk terkait dari kategori lain (untuk "Lihat juga")
$stmt = $pdo->prepare("SELECT p.*, c.name AS category_name, c.slug AS category_slug
                       FROM products p JOIN categories c ON p.category_id = c.id
                       WHERE c.slug <> ? ORDER BY RAND() LIMIT 4");
$stmt->execute([$slug]);
$other_products = $stmt->fetchAll();

$page_title = $category['name'];
include __DIR__ . '/includes/header.php';
?>

<!-- ============ CATEGORY HERO BANNER ============ -->
<section class="cat-hero" style="background-image: url('<?= e($category['image']) ?>');">
    <div class="cat-hero-inner">
        <div class="cat-breadcrumb">
            <a href="<?= url('index.php') ?>"><i class="fas fa-home"></i> Beranda</a>
            <span class="sep">/</span>
            <a href="<?= url('all_categories.php') ?>">Semua Kategori</a>
            <span class="sep">/</span>
            <span><?= e($category['name']) ?></span>
        </div>
        <h1><?= e($category['name']) ?></h1>
        <p class="cat-desc"><?= e($category['description']) ?></p>
        <div class="cat-stats">
            <span class="stat-pill"><i class="fas fa-box"></i> <strong><?= (int)$stats['total_products'] ?></strong> Produk</span>
            <span class="stat-pill"><i class="fas fa-warehouse"></i> <strong><?= (int)$stats['total_stock'] ?></strong> Unit Stok</span>
            <span class="stat-pill"><i class="fas fa-tag"></i> Kategori: <strong><?= e($category['name']) ?></strong></span>
        </div>
    </div>
</section>

<?php
$available_filters = get_category_filters($slug);
if (!empty($available_filters)): ?>
<div class="category-filters" style="margin-bottom:20px;display:flex;gap:16px;flex-wrap:wrap;align-items:center;background:white;padding:12px 18px;border-radius:var(--radius-md);border:1px solid var(--border-light);">
    <form method="get" action="" style="display:flex;gap:16px;flex-wrap:wrap;align-items:center;">
        <input type="hidden" name="slug" value="<?= e($slug) ?>">
        <input type="hidden" name="sort" value="<?= e($sort) ?>">
        <?php foreach ($available_filters as $filter_key => $filter_data): ?>
            <div>
                <span style="font-weight:600;font-size:13px;margin-right:8px;"><?= e($filter_data['label']) ?>:</span>
                <select name="filter_<?= e($filter_key) ?>" onchange="this.form.submit()" style="padding:6px 12px;border:1px solid var(--border);border-radius:6px;font-size:13px;">
                    <option value="">Semua</option>
                    <?php foreach ($filter_data['options'] as $opt): ?>
                        <option value="<?= e($opt) ?>" <?= (($_GET['filter_'.$filter_key] ?? '') === $opt) ? 'selected' : '' ?>><?= e($opt) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
        <?php endforeach; ?>
    </form>
    <?php if (!empty($_GET['filter_brand']) || !empty($_GET['filter_series'])): ?>
        <a href="<?= url('category.php?slug=' . $slug) ?>" class="btn btn-outline btn-sm"><i class="fas fa-times"></i> Reset</a>
    <?php endif; ?>
</div>
<?php endif; ?>

<!-- ============ PRODUCTS ============ -->
<section class="section">
    <div class="section-header">
        <h2><i class="fas fa-th accent"></i> Produk <?= e($category['name']) ?> <span class="muted" style="font-size:14px;font-weight:400;">(<?= count($products) ?> produk)</span></h2>
        <form method="get" action="" style="display:flex;gap:8px;align-items:center;">
            <input type="hidden" name="slug" value="<?= e($slug) ?>">
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
            <h3>Belum ada produk di kategori ini</h3>
            <p>Kami akan segera menambahkan produk <?= e($category['name']) ?>. Pantau terus!</p>
            <a href="<?= url('all_categories.php') ?>" class="btn btn-primary mt-2"><i class="fas fa-arrow-left"></i> Lihat Semua Kategori</a>
        </div>
    <?php else: ?>
        <div class="product-grid">
            <?php foreach ($products as $p): ?>
                <div class="product-card">
                    <a href="<?= url('product_detail.php?id=' . $p['id']) ?>" class="pc-image">
                        <?php if ($p['badge']): ?>
                            <span class="pc-badge badge-<?= strtolower(str_replace([' ','-'], '', $p['badge'])) ?>"><?= e($p['badge']) ?></span>
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

<!-- ============ OTHER CATEGORIES (Quick Switch) ============ -->
<section class="section" style="background: white; padding: 28px 0; margin: 0;">
    <div style="max-width: var(--container); margin: 0 auto; padding: 0 24px;">
        <div class="section-header">
            <h2><i class="fas fa-th-large accent"></i> Kategori Lainnya</h2>
            <a href="<?= url('all_categories.php') ?>" class="view-all">Semua Kategori <i class="fas fa-arrow-right"></i></a>
        </div>
        <div class="cat-image-grid">
            <?php foreach ($categories as $c): ?>
                <?php if ($c['slug'] === $slug) continue; ?>
                <a href="<?= url('category.php?slug=' . $c['slug']) ?>" class="cat-image-card">
                    <div class="cic-bg" style="background-image: url('<?= e($c['image']) ?>');"></div>
                    <div class="cic-overlay"></div>
                    <div class="cic-arrow"><i class="fas fa-arrow-right"></i></div>
                    <div class="cic-content">
                        <span class="cic-tag"><?= e($c['name']) ?></span>
                        <div class="cic-name"><?= e($c['name']) ?></div>
                        <div class="cic-desc"><?= e($c['description']) ?></div>
                    </div>
                </a>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<?php include __DIR__ . '/includes/footer.php'; ?>
