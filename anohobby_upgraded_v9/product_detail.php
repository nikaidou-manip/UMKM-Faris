<?php
require_once __DIR__ . '/includes/functions.php';
global $pdo;

$id = (int)($_GET['id'] ?? 0);
if (!$id) { header('Location: ' . url('products.php')); exit; }

$p = get_product($pdo, $id);
if (!$p) {
    set_flash('error', 'Produk tidak ditemukan.');
    header('Location: ' . url('products.php'));
    exit;
}

// Related products (same category)
$stmt = $pdo->prepare("SELECT p.*, c.name AS category_name FROM products p
                       JOIN categories c ON p.category_id = c.id
                       WHERE p.category_id = ? AND p.id <> ? LIMIT 4");
$stmt->execute([$p['category_id'], $id]);
$related = $stmt->fetchAll();

$page_title = $p['name'];
include __DIR__ . '/includes/header.php';
?>
<div class="container" style="margin-top:16px;">
    <div class="breadcrumb">
        <a href="<?= url('index.php') ?>">Beranda</a>
        <span class="sep">/</span>
        <a href="<?= url('products.php') ?>">Produk</a>
        <span class="sep">/</span>
        <a href="<?= url('products.php?cat=' . $p['category_slug']) ?>"><?= e($p['category_name']) ?></a>
        <span class="sep">/</span>
        <span><?= e($p['name']) ?></span>
    </div>
</div>

<section class="product-detail">
    <div class="pd-gallery">
        <?php
        // Kumpulkan semua gambar produk (maksimal 5: 1 cover + 4 tambahan).
        // Jumlah foto yang tampil menyesuaikan jumlah yang diisi admin - bisa kurang dari 5
        // tergantung barang, kolom yang kosong otomatis dilewati.
        $images = [];
        if (!empty($p['image']))      $images[] = $p['image'];
        for ($i = 2; $i <= 5; $i++) {
            $col = 'image_' . $i;
            if (!empty($p[$col])) $images[] = $p[$col];
        }
        // Fallback kalau tidak ada gambar sama sekali
        if (empty($images)) $images[] = 'https://via.placeholder.com/600x600?text=No+Image';
        $total_imgs = count($images);
        ?>
        <div class="pd-main-image-wrap">
            <img id="pdMainImage" src="<?= e($images[0]) ?>" alt="<?= e($p['name']) ?>" class="pd-main-image">
            <?php if ($total_imgs > 1): ?>
                <span class="pd-img-counter"><i class="fas fa-images"></i> 1 / <?= $total_imgs ?></span>
                <button type="button" class="pd-nav-btn prev" onclick="pdGalleryNav(-1)" aria-label="Sebelumnya"><i class="fas fa-chevron-left"></i></button>
                <button type="button" class="pd-nav-btn next" onclick="pdGalleryNav(1)"  aria-label="Berikutnya"><i class="fas fa-chevron-right"></i></button>
            <?php endif; ?>
        </div>

        <?php if ($total_imgs > 1): ?>
        <div class="pd-thumbs" id="pdThumbs">
            <?php foreach ($images as $idx => $img): ?>
                <button type="button"
                        class="pd-thumb <?= $idx===0?'active':'' ?>"
                        data-src="<?= e($img) ?>"
                        data-idx="<?= $idx ?>"
                        onclick="pdGallerySelect(<?= $idx ?>)">
                    <img src="<?= e($img) ?>" alt="Foto <?= $idx + 1 ?>" loading="lazy">
                </button>
            <?php endforeach; ?>
        </div>
        <?php endif; ?>
    </div>
    <div class="pd-info">
        <span class="pd-cat-tag"><i class="fas fa-tag"></i> <?= e($p['category_name']) ?></span>
        <h1><?= e($p['name']) ?></h1>

        <?php if ($p['badge']): ?>
            <span class="pc-badge badge-<?= strtolower(str_replace([' ','-'],'',$p['badge'])) ?>" style="position:static;display:inline-block;margin-bottom:10px;"><?= e($p['badge']) ?></span>
        <?php endif; ?>

        <?php if (is_flash_sale_active($p)):
            $fs_remaining = flash_sale_remaining_seconds($p);
        ?>
            <!-- Flash sale countdown di product detail -->
            <div class="fs-mini-countdown" data-ends-at="<?= e($p['flash_sale_ends_at']) ?>" data-remaining="<?= $fs_remaining ?>" style="margin-bottom:12px;padding:10px 14px;font-size:13px;">
                <i class="fas fa-fire"></i>
                <span>Flash Sale berakhir dalam:</span>
                <strong class="fs-mini-text" style="margin-left:6px;"><?= format_countdown_short($fs_remaining) ?></strong>
            </div>
            <script>
            (function() {
                var el = document.querySelector('.pd-info .fs-mini-countdown');
                if (!el) return;
                var remaining = parseInt(el.getAttribute('data-remaining'), 10);
                var txt = el.querySelector('.fs-mini-text');
                function pad(n){return n<10?'0'+n:''+n;}
                function tick() {
                    if (remaining <= 0) { txt.textContent = '00:00:00'; el.classList.add('expired'); return; }
                    var days  = Math.floor(remaining / 86400);
                    var hours = Math.floor((remaining % 86400) / 3600);
                    var mins  = Math.floor((remaining % 3600) / 60);
                    var secs  = remaining % 60;
                    txt.textContent = days > 0
                        ? days + 'd ' + pad(hours) + ':' + pad(mins) + ':' + pad(secs)
                        : pad(hours) + ':' + pad(mins) + ':' + pad(secs);
                    remaining = Math.max(0, remaining - 1);
                }
                tick();
                setInterval(tick, 1000);
            })();
            </script>
        <?php endif; ?>

        <div class="pd-price">
            <?php
            $has_fs_disc = has_flash_sale_discount($p);
            if ($has_fs_disc):
                $fs_price = flash_sale_price($p);
            ?>
                <div class="pd-price-flashsale">
                    <span class="pd-price-strike"><?= rupiah($p['price']) ?></span>
                    <span class="pd-price-sale"><?= rupiah($fs_price) ?></span>
                    <span class="pd-price-disc">-<?= (int)$p['flash_sale_discount'] ?>%</span>
                </div>
            <?php else: ?>
                <?= rupiah($p['price']) ?>
            <?php endif; ?>
        </div>

        <?php
        $brand_clean = trim((string)($p['brand'] ?? ''));
        // Brand hanya ditampilkan untuk kategori Model Kit
        $show_brand  = ($p['category_slug'] ?? '') === 'model-kit';
        ?>
        <div class="pd-meta">
            <div>
                <strong>Ketersediaan</strong>
                <?= $p['stock'] > 0 ? "<span class='text-success'>In Stock ({$p['stock']} unit)</span>" : "<span class='text-danger'>Out of Stock</span>" ?>
            </div>
            <div>
                <strong>Status</strong>
                <?= e($p['release_date'] ?: 'Ready Stock') ?>
            </div>
            <div>
                <strong>Kategori</strong>
                <a href="<?= url('products.php?cat=' . $p['category_slug']) ?>"><?= e($p['category_name']) ?></a>
            </div>
            <?php if ($show_brand): ?>
            <div>
                <strong>Brand</strong>
                <?= $brand_clean !== '' ? e(ucwords($brand_clean)) : '<span class="text-muted">Generic / Tidak bermerek</span>' ?>
            </div>
            <?php endif; ?>
            <div>
                <strong>SKU</strong>
                HBV-<?= str_pad($p['id'], 5, '0', STR_PAD_LEFT) ?>
            </div>
        </div>

        <?php if ($p['stock'] > 0): ?>
            <form action="<?= url('cart_add.php') ?>" method="post" class="pd-qty-row">
                <input type="hidden" name="product_id" value="<?= $p['id'] ?>">
                <label style="font-weight:600;font-size:14px;">Jumlah:</label>
                <div class="pd-qty" data-qty-group>
                    <button type="button" data-qty-dec>&minus;</button>
                    <input type="number" name="qty" value="1" min="1" max="<?= $p['stock'] ?>">
                    <button type="button" data-qty-inc>+</button>
                </div>
                <button type="submit" class="btn btn-primary btn-lg"><i class="fas fa-shopping-bag"></i> Tambah ke Keranjang</button>
            </form>

            <!-- ====== Tombol Checkout Sekarang (beli langsung tanpa add to cart manual) ====== -->
            <form action="<?= url('cart_add.php') ?>" method="post" class="pd-checkout-row" style="margin-top:10px;">
                <input type="hidden" name="product_id" value="<?= $p['id'] ?>">
                <!-- Qty di-mirror dari input pd-qty-row via JS di bawah -->
                <input type="hidden" name="qty" id="pd_checkout_qty" value="1">
                <input type="hidden" name="redirect" value="<?= e(url('checkout.php')) ?>">
                <button type="submit" class="btn btn-dark btn-lg btn-block"><i class="fas fa-bolt"></i> Checkout Sekarang</button>
            </form>
        <?php else: ?>
            <div class="flash flash-error"><i class="fas fa-times-circle"></i> Maaf, produk ini sedang habis. Pantau terus untuk update stok!</div>
        <?php endif; ?>

        <!-- ====== Tab Deskripsi & Spesifikasi (info produk lebih detail) ====== -->
        <div class="pd-tabs">
            <div class="pd-tab-nav">
                <button type="button" class="pd-tab-btn active" data-tab="desc" onclick="pdTabSwitch('desc')"><i class="fas fa-align-left"></i> Deskripsi</button>
                <button type="button" class="pd-tab-btn" data-tab="spec" onclick="pdTabSwitch('spec')"><i class="fas fa-list-ul"></i> Spesifikasi</button>
            </div>
            <div class="pd-tab-panel active" data-tab-panel="desc">
                <div class="pd-desc">
                    <?= $p['description'] ? nl2br(e($p['description'])) : '<span class="text-muted">Belum ada deskripsi detail untuk produk ini.</span>' ?>
                </div>
            </div>
            <div class="pd-tab-panel" data-tab-panel="spec">
                <table class="pd-spec-table">
                    <tbody>
                        <tr><th>Nama Produk</th><td><?= e($p['name']) ?></td></tr>
                        <tr><th>Kategori</th><td><?= e($p['category_name']) ?></td></tr>
                        <?php if ($show_brand): ?>
                        <tr><th>Brand</th><td><?= $brand_clean !== '' ? e(ucwords($brand_clean)) : 'Generic / Tidak bermerek' ?></td></tr>
                        <?php endif; ?>
                        <tr><th>Kode Produk (SKU)</th><td>HBV-<?= str_pad($p['id'], 5, '0', STR_PAD_LEFT) ?></td></tr>
                        <tr><th>Status Rilis</th><td><?= e($p['release_date'] ?: 'Ready Stock') ?></td></tr>
                        <tr><th>Stok Tersedia</th><td><?= $p['stock'] ?> unit</td></tr>
                        <tr><th>Harga</th><td>
                            <?php if ($has_fs_disc): ?>
                                <span style="text-decoration:line-through;color:var(--text-muted);"><?= rupiah($p['price']) ?></span>
                                &nbsp;<strong class="accent"><?= rupiah($fs_price) ?></strong>
                                <span class="status-badge status-pending" style="margin-left:6px;">-<?= (int)$p['flash_sale_discount'] ?>% FLASH SALE</span>
                            <?php else: ?>
                                <?= rupiah($p['price']) ?>
                            <?php endif; ?>
                        </td></tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</section>

<?php if (!empty($related)): ?>
<section class="section">
    <div class="section-header">
        <h2><i class="fas fa-th accent"></i> Produk Serupa</h2>
        <a href="<?= url('products.php?cat=' . $p['category_slug']) ?>" class="view-all">Lihat Semua <i class="fas fa-arrow-right"></i></a>
    </div>
    <div class="product-grid">
        <?php foreach ($related as $r): ?>
            <div class="product-card">
                <a href="<?= url('product_detail.php?id=' . $r['id']) ?>" class="pc-image">
                    <span class="pc-cat"><?= e($r['category_name']) ?></span>
                    <img src="<?= e($r['image']) ?>" alt="<?= e($r['name']) ?>" loading="lazy">
                </a>
                <div class="pc-body">
                    <a href="<?= url('product_detail.php?id=' . $r['id']) ?>" class="pc-title"><?= e($r['name']) ?></a>
                    <div class="pc-release"><?= e($r['release_date'] ?: 'Ready Stock') ?></div>
                    <div class="pc-price"><?= rupiah($r['price']) ?></div>
                    <div class="pc-actions">
                        <a href="<?= url('product_detail.php?id=' . $r['id']) ?>" class="btn btn-outline btn-sm">Detail</a>
                        <form action="<?= url('cart_add.php') ?>" method="post" style="flex:1;">
                            <input type="hidden" name="product_id" value="<?= $r['id'] ?>">
                            <input type="hidden" name="qty" value="1">
                            <button type="submit" class="btn btn-primary btn-sm" style="width:100%;" <?= $r['stock']<=0?'disabled':'' ?>>+ Cart</button>
                        </form>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
</section>
<?php endif; ?>

<script>
// ====== Tab Deskripsi / Spesifikasi ======
window.pdTabSwitch = function (tab) {
    document.querySelectorAll('.pd-tab-btn').forEach(function (btn) {
        btn.classList.toggle('active', btn.getAttribute('data-tab') === tab);
    });
    document.querySelectorAll('.pd-tab-panel').forEach(function (panel) {
        panel.classList.toggle('active', panel.getAttribute('data-tab-panel') === tab);
    });
};

// ====== Sync qty dari input utama ke hidden checkout qty ======
(function() {
    var mainQty = document.querySelector('.pd-qty-row input[name="qty"]');
    var checkoutQty = document.getElementById('pd_checkout_qty');
    if (mainQty && checkoutQty) {
        var sync = function() { checkoutQty.value = mainQty.value; };
        mainQty.addEventListener('input', sync);
        mainQty.addEventListener('change', sync);
        sync();
    }
})();
</script>

<?php if ($total_imgs > 1): ?>
<script>
// ====== Product Detail Gallery (up to 5 photos, sesuai jumlah yang diisi) ======
(function () {
    var images = <?php echo json_encode($images); ?>;
    var currentIdx = 0;
    var mainImg    = document.getElementById('pdMainImage');
    var counter    = document.querySelector('.pd-img-counter');
    var thumbs     = document.querySelectorAll('#pdThumbs .pd-thumb');

    window.pdGallerySelect = function (idx) {
        if (idx < 0 || idx >= images.length) return;
        currentIdx = idx;
        mainImg.src = images[idx];
        if (counter) counter.innerHTML = '<i class="fas fa-images"></i> ' + (idx + 1) + ' / ' + images.length;
        thumbs.forEach(function (t, i) {
            t.classList.toggle('active', i === idx);
        });
    };

    window.pdGalleryNav = function (dir) {
        var next = currentIdx + dir;
        if (next < 0) next = images.length - 1;
        if (next >= images.length) next = 0;
        pdGallerySelect(next);
    };

    // Keyboard arrow nav
    document.addEventListener('keydown', function (e) {
        if (e.key === 'ArrowLeft')  pdGalleryNav(-1);
        if (e.key === 'ArrowRight') pdGalleryNav(1);
    });
})();
</script>
<?php endif; ?>

<?php include __DIR__ . '/includes/footer.php'; ?>
