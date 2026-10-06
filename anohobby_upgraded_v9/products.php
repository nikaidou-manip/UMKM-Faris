<?php
require_once __DIR__ . '/includes/functions.php';
global $pdo;

$categories = get_categories($pdo);
$cat_slug = $_GET['cat'] ?? '';
$search = trim($_GET['q'] ?? '');
$sort = $_GET['sort'] ?? 'newest';

// Query dasar
$sql = "SELECT p.*, c.name AS category_name, c.slug AS category_slug
        FROM products p JOIN categories c ON p.category_id = c.id WHERE 1=1";
$params = [];

if ($cat_slug) {
    $sql .= " AND c.slug = ?";
    $params[] = $cat_slug;
}
if ($search) {
    $sql .= " AND (p.name LIKE ? OR p.description LIKE ?)";
    $params[] = "%$search%";
    $params[] = "%$search%";
}
switch ($sort) {
    case 'price_asc':  $sql .= " ORDER BY p.price ASC"; break;
    case 'price_desc': $sql .= " ORDER BY p.price DESC"; break;
    case 'name':       $sql .= " ORDER BY p.name ASC"; break;
    default:           $sql .= " ORDER BY p.created_at DESC";
}
$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$products = $stmt->fetchAll();

// Judul halaman
if ($cat_slug) {
    $stmt = $pdo->prepare("SELECT name FROM categories WHERE slug = ?");
    $stmt->execute([$cat_slug]);
    $cat_name = $stmt->fetchColumn();
    $page_title = $cat_name ?: 'Produk';
} elseif ($search) {
    $page_title = "Cari: \"$search\"";
} else {
    $page_title = 'Semua Produk';
}

include __DIR__ . '/includes/header.php';
?>
<div class="container" style="margin-top:16px;">
    <div class="breadcrumb">
        <a href="<?= url('index.php') ?>">Beranda</a>
        <span class="sep">/</span>
        <a href="<?= url('products.php') ?>">Produk</a>
        <?php if ($cat_slug): ?>
            <span class="sep">/</span>
            <span><?= e($cat_name) ?></span>
        <?php elseif ($search): ?>
            <span class="sep">/</span>
            <span>Hasil pencarian</span>
        <?php endif; ?>
    </div>
</div>

<section class="section">
    <div class="section-header">
        <h2><?= e($page_title) ?> <span class="muted" style="font-size:14px;font-weight:400;">(<?= count($products) ?> produk)</span></h2>
    </div>

    <!-- FILTER BAR -->
    <div class="filter-bar">
        <span class="filter-label"><i class="fas fa-filter"></i> Filter:</span>
        <form method="get" action="" style="display:flex;gap:10px;flex-wrap:wrap;align-items:center;">
            <?php if ($search): ?><input type="hidden" name="q" value="<?= e($search) ?>"><?php endif; ?>
            <select name="cat" onchange="this.form.submit()">
                <option value="">Semua Kategori</option>
                <?php foreach ($categories as $c): ?>
                    <option value="<?= e($c['slug']) ?>" <?= $cat_slug===$c['slug']?'selected':'' ?>><?= e($c['name']) ?></option>
                <?php endforeach; ?>
            </select>
            <select name="sort" onchange="this.form.submit()">
                <option value="newest" <?= $sort==='newest'?'selected':'' ?>>Terbaru</option>
                <option value="price_asc" <?= $sort==='price_asc'?'selected':'' ?>>Harga Termurah</option>
                <option value="price_desc" <?= $sort==='price_desc'?'selected':'' ?>>Harga Termahal</option>
                <option value="name" <?= $sort==='name'?'selected':'' ?>>Nama A-Z</option>
            </select>
        </form>
    </div>

    <?php if (empty($products)): ?>
        <div class="empty-state">
            <i class="fas fa-search"></i>
            <h3>Tidak ada produk ditemukan</h3>
            <p>Coba ubah filter atau kata kunci pencarian Anda.</p>
            <a href="<?= url('products.php') ?>" class="btn btn-primary mt-2"><i class="fas fa-redo"></i> Reset Filter</a>
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

<?php include __DIR__ . '/includes/footer.php'; ?>
