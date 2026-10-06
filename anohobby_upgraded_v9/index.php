<?php
require_once __DIR__ . '/includes/functions.php';
global $pdo;

$categories = get_categories($pdo);
$latest_products = get_latest_products($pdo, 10);
$flash_products = $pdo->query("SELECT p.*, c.name AS category_name, c.slug AS category_slug
                                FROM products p JOIN categories c ON p.category_id = c.id
                                WHERE p.badge IN ('HOT','PRE-ORDER')
                                ORDER BY p.id LIMIT 5")->fetchAll();
$new_stock_products = get_new_stock_products($pdo, 8);

// Produk terbaru untuk banner "New Series Drop" (1 produk paling baru)
$latest_single = get_latest_product($pdo);
// Flash sale aktif untuk section homepage (defensive - mungkin tabel belum di-migrate)
try {
    $flashsale_products = get_flash_sale_products($pdo, 8);
} catch (Throwable $e) {
    $flashsale_products = [];
}
// New arrival aktif untuk section homepage
try {
    $newarrival_products = get_new_arrival_products($pdo, 8);
} catch (Throwable $e) {
    $newarrival_products = [];
}


$page_title = 'Beranda';
include __DIR__ . '/includes/header.php';
?>

<!-- ============ HERO BANNER GRID (6 banners: 3 wide + 3 square) ============ -->
<section class="hero-section">
    <div class="hero-grid">
        <a href="https://maps.app.goo.gl/2neK6BU54siQwgTt5" class="hero-banner banner-360 hb-wide">
            <div class="hb-pattern"></div>
            <div class="hb-thumb">
                <img src="https://encrypted-tbn0.gstatic.com/images?q=tbn:ANd9GcT-A2XVp3aExliUjTv8gV62t5sSH5LbyItKVjV9x-2P9lN-ce-7XV9GRsI&s=10" alt="Store Interior">
            </div>
            <div class="hb-content">
                <span class="hb-badge"><i class="fas fa-vr-cardboard"></i> FEATURE</span>
                <h3 class="hb-title">360&deg; View Available!</h3>
                <p class="hb-subtitle">Come and step into AnoHobby's world, right from your screen!</p>
                <span class="hb-cta"><i class="fas fa-store"></i> Explore Physical Store</span>
            </div>
        </a>

        <!-- Banner 2: Nebuta Sale -->
        <a href="<?= url('flash_sale.php') ?>" class="hero-banner banner-nebuta hb-wide">
            <div class="hb-bg"></div>
            <div class="hb-pattern"></div>
            <div class="hb-overlay"></div>
            <div class="hb-content">
                <span class="hb-badge yellow"><i class="fas fa-percent"></i> SALE UP TO 40%</span>
                <h3 class="hb-title hb-title-lg">NEBUTA SALE</h3>
                <p class="hb-subtitle">Festival diskon tahunan untuk modelkit, figure, dan TCG pilihan!</p>
                <span class="hb-cta"><i class="fas fa-bolt"></i> 24 JUL - 7 AGU 2026</span>
            </div>
        </a>

        <!-- Bottom Row - 3 Square Banners -->

        <!-- Banner 4: New Arrival - Bandai -->
        <a href="<?= url('category.php?slug=model-kit') ?>" class="hero-banner banner-arrival hb-square">
            <div class="hb-left">
                <img src="https://i.pinimg.com/1200x/60/65/2b/60652b1ef248c72b81e10761b171a5e5.jpg" alt="">
                <div class="hb-ribbon"><span>NEW ARRIVAL!</span></div>
            </div>
            <div class="hb-right">
                <div class="label">MANUFACTURER:</div>
                <div class="name">BANDAI</div>
            </div>
        </a>

        <!-- Banner 5: New Arrival - PVC Figure -->
        <a href="<?= url('category.php?slug=pvc-figure') ?>" class="hero-banner banner-arrival hb-square">
            <div class="hb-left">
                <img src="https://i.pinimg.com/1200x/e4/4e/0b/e44e0b0d811b15a8bbd1874bcb6eb8c8.jpg" alt="">
                <div class="hb-ribbon"><span>NEW ARRIVAL!</span></div>
            </div>
            <div class="hb-right">
                <div class="label">CATEGORY:</div>
                <div class="name">PVC FIGURE</div>
            </div>
        </a>

        <!-- Banner 6: New Series Drop - GAMBAR DARI PRODUK TERBARU -->
        <?php if ($latest_single): ?>
        <a href="<?= url('new_arrivals.php') ?>" class="hero-banner banner-wipeout hb-square banner-newseries">
            <div class="hb-bg"></div>
            <div class="hb-pattern"></div>
            <div class="hb-newseries-image" style="background-image: url('<?= e($latest_single['image']) ?>');"></div>
            <div class="hb-overlay"></div>
            <div class="hb-content">
                <span class="hb-badge-pop"><i class="fas fa-bolt"></i> NEW SERIES DROP!</span>
                <h3 class="hb-title"><?= e(mb_strimwidth($latest_single['name'], 0, 40, '...')) ?></h3>
                <p class="hb-subtitle">Produk terbaru baru saja drop! Klik untuk lihat semua koleksi terbaru.</p>
                <span class="hb-cta"><i class="fas fa-arrow-right"></i> Lihat New Arrivals</span>
            </div>
        </a>
        <?php else: ?>
        <a href="<?= url('new_arrivals.php') ?>" class="hero-banner banner-wipeout hb-square">
            <div class="hb-bg"></div>
            <div class="hb-pattern"></div>
            <div class="hb-overlay"></div>
            <div class="hb-content">
                <span class="hb-badge-pop">WIPEOUT!</span>
                <h3 class="hb-title">New Series Drop</h3>
                <p class="hb-subtitle">Koleksi figure seri anime populer terbaru telah tiba.</p>
                <span class="hb-cta"><i class="fas fa-arrow-right"></i> Belanja Sekarang</span>
            </div>
        </a>
        <?php endif; ?>
    </div>
</section>

<!-- ============ FEATURE STRIP (3 cards - dark/orange/maroon) ============ -->
<div class="feature-strip">
    <div class="feature-card dark">
        <i class="fas fa-store feature-icon"></i>
        <div>
            <h3>360&deg; View Store</h3>
            <p>Lihat toko fisik kami tanpa keluar rumah. Online tour tersedia 24/7.</p>
            <a href="https://maps.app.goo.gl/fjLRprrZ22FmAqbF7" class="btn btn-primary btn-sm"><i class="fas fa-vr-cardboard"></i> Explore Store</a>
        </div>
    </div>
    <div class="feature-card orange">
        <i class="fas fa-bolt feature-icon"></i>
        <div>
            <h3>Nebuta Sale</h3>
            <p>Diskon up to <strong>40%</strong> untuk modelkit & figure pilihan.</p>
            <a href="<?= url('flash_sale.php') ?>" class="btn btn-dark btn-sm">Belanja Sekarang</a>
        </div>
    </div>
</div>

<!-- ============ NEW STOCK SECTION ============ -->
<section class="newstock-section">
    <div style="max-width: var(--container); margin: 0 auto; padding: 0 24px;">
        <div class="section-header">
            <h2><span class="accent">NEW</span> STOCK</h2>
            <a href="<?= url('products.php') ?>" class="view-all">Lihat Semua <i class="fas fa-arrow-right"></i></a>
        </div>
        <div class="newstock-grid">
            <?php foreach ($new_stock_products as $p):
                $brand = $brand_map[$p['id']] ?? 'ANOHOBBY';
                $is_oos = $p['stock'] <= 0;
                $is_preorder = $p['badge'] === 'PRE-ORDER';
                $ribbon_class = $is_oos ? 'out-of-stock' : ($is_preorder ? 'pre-order' : '');
                $ribbon_text = $is_oos ? 'OUT OF STOCK' : ($is_preorder ? 'PRE-ORDER' : 'READY STOCK');
            ?>
                <div class="ns-card">
                    <a href="<?= url('product_detail.php?id=' . $p['id']) ?>" class="ns-image">
                        <span class="ns-ribbon <?= $ribbon_class ?>"><?= $ribbon_text ?></span>
                        <?php if (!$is_oos && $p['badge'] === 'HOT'): ?>
                            <span class="ns-brand-tag">HOT</span>
                        <?php endif; ?>
                        <img src="<?= e($p['image']) ?>" alt="<?= e($p['name']) ?>" loading="lazy">
                        <span class="ns-brand <?= $p['id']%2===0?'alt':'' ?>"><?= e($brand) ?></span>
                    </a>
                    <div class="ns-body">
                        <a href="<?= url('product_detail.php?id=' . $p['id']) ?>" class="ns-title"><?= e($p['name']) ?></a>
                        <div class="ns-divider"></div>
                        <?php if ($is_oos): ?>
                            <div class="ns-price strike"><?= rupiah($p['price']) ?></div>
                            <div class="ns-stock-status"><i class="fas fa-times-circle"></i> Out of Stock</div>
                            <button class="ns-cart-btn" disabled><i class="fas fa-ban"></i> Habis</button>
                        <?php else: ?>
                            <div class="ns-price">IDR <?= number_format($p['price'], 0, '.', '.') ?></div>
                            <form action="<?= url('cart_add.php') ?>" method="post">
                                <input type="hidden" name="product_id" value="<?= $p['id'] ?>">
                                <input type="hidden" name="qty" value="1">
                                <button type="submit" class="ns-cart-btn"><i class="fas fa-shopping-bag"></i> Add to Cart</button>
                            </form>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<!-- ============ CATEGORIES (Slider: 2 cols x 4 rows = 8 cards per page) ============ -->
<section class="section">
    <div class="section-header">
        <h2><i class="fas fa-th-large accent"></i> Kategori Hobby</h2>
        <a href="<?= url('all_categories.php') ?>" class="view-all">Semua Kategori <i class="fas fa-arrow-right"></i></a>
    </div>

    <?php
    // Bagi kategori menjadi halaman-halaman, masing-masing 8 kartu (2 cols x 4 rows)
    $cat_pages = array_chunk($categories, 8);
    $total_pages = count($cat_pages);
    ?>

    <div class="cat-slider-wrap" id="catSlider">
        <?php if ($total_pages > 1): ?>
            <button type="button" class="cat-slider-nav prev" aria-label="Halaman sebelumnya" disabled><i class="fas fa-chevron-left"></i></button>
        <?php endif; ?>

        <div class="cat-slider-viewport">
            <div class="cat-slider-track">
                <?php foreach ($cat_pages as $page_idx => $page_cats): ?>
                    <div class="cat-slider-page" data-page="<?= $page_idx ?>">
                        <?php foreach ($page_cats as $c): ?>
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
                <?php endforeach; ?>
            </div>
        </div>

        <?php if ($total_pages > 1): ?>
            <button type="button" class="cat-slider-nav next" aria-label="Halaman berikutnya"><i class="fas fa-chevron-right"></i></button>
            <div class="cat-slider-dots">
                <?php for ($i = 0; $i < $total_pages; $i++): ?>
                    <button type="button" class="dot <?= $i===0?'active':'' ?>" data-page="<?= $i ?>" aria-label="Ke halaman <?= $i+1 ?>"></button>
                <?php endfor; ?>
            </div>
        <?php endif; ?>
    </div>
</section>

<!-- ============ FLASH SALE SECTION (jika ada produk flash sale aktif) ============ -->
<?php if (!empty($flashsale_products)): ?>
<section class="section section-flashsale">
    <div class="section-header">
        <h2><i class="fas fa-bolt accent"></i> ⚡ FLASH SALE ⚡</h2>
        <a href="<?= url('flash_sale.php') ?>" class="view-all">Lihat Semua <i class="fas fa-arrow-right"></i></a>
    </div>
    <div class="product-grid">
        <?php foreach ($flashsale_products as $p):
            $remaining = flash_sale_remaining_seconds($p);
            $has_disc = has_flash_sale_discount($p);
            $sale_price = $has_disc ? flash_sale_price($p) : (float)$p['price'];
        ?>
            <div class="product-card product-card-flashsale">
                <a href="<?= url('product_detail.php?id=' . $p['id']) ?>" class="pc-image">
                    <span class="pc-badge badge-flashsale"><i class="fas fa-bolt"></i> FLASH SALE</span>
                    <?php if ($has_disc): ?>
                        <span class="pc-badge badge-flashsale-disc">-<?= (int)$p['flash_sale_discount'] ?>%</span>
                    <?php endif; ?>
                    <span class="pc-cat"><?= e($p['category_name']) ?></span>
                    <img src="<?= e($p['image']) ?>" alt="<?= e($p['name']) ?>" loading="lazy">
                </a>
                <div class="pc-body">
                    <a href="<?= url('product_detail.php?id=' . $p['id']) ?>" class="pc-title"><?= e($p['name']) ?></a>
                    <div class="pc-release"><?= e($p['release_date'] ?: 'Ready Stock') ?></div>
                    <div class="pc-price">
                        <?php if ($has_disc): ?>
                            <span class="pc-price-strike"><?= rupiah($p['price']) ?></span>
                            <span class="pc-price-sale"><?= rupiah($sale_price) ?></span>
                        <?php else: ?>
                            <?= rupiah($p['price']) ?>
                        <?php endif; ?>
                    </div>
                    <div class="fs-mini-countdown" data-ends-at="<?= e($p['flash_sale_ends_at']) ?>" data-remaining="<?= $remaining ?>">
                        <i class="fas fa-clock"></i> <span class="fs-mini-text"><?= format_countdown_short($remaining) ?></span>
                    </div>
                    <div class="pc-actions">
                        <a href="<?= url('product_detail.php?id=' . $p['id']) ?>" class="btn btn-outline btn-sm"><i class="fas fa-eye"></i> Detail</a>
                        <form action="<?= url('cart_add.php') ?>" method="post" style="flex:1;">
                            <input type="hidden" name="product_id" value="<?= $p['id'] ?>">
                            <input type="hidden" name="qty" value="1">
                            <button type="submit" class="btn btn-primary btn-sm" style="width:100%;" <?= $p['stock']<=0?'disabled':'' ?>><i class="fas fa-bolt"></i> Beli</button>
                        </form>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
    <script>
    // Mini countdown untuk flash sale di homepage
    (function() {
        var timers = document.querySelectorAll('.section-flashsale .fs-mini-countdown');
        function pad(n){return n<10?'0'+n:''+n;}
        function update(el, remaining) {
            var txt = el.querySelector('.fs-mini-text');
            if (!txt) return;
            if (remaining <= 0) { txt.textContent = '00:00:00'; el.classList.add('expired'); return; }
            var days  = Math.floor(remaining / 86400);
            var hours = Math.floor((remaining % 86400) / 3600);
            var mins  = Math.floor((remaining % 3600) / 60);
            var secs  = remaining % 60;
            txt.textContent = days > 0
                ? days + 'd ' + pad(hours) + ':' + pad(mins) + ':' + pad(secs)
                : pad(hours) + ':' + pad(mins) + ':' + pad(secs);
        }
        var state = [];
        timers.forEach(function(el, i){
            state[i] = parseInt(el.getAttribute('data-remaining'), 10);
            update(el, state[i]);
        });
        setInterval(function(){
            state.forEach(function(r, i){
                state[i] = Math.max(0, r - 1);
                update(timers[i], state[i]);
            });
        }, 1000);
    })();
    </script>
</section>
<?php endif; ?>

<!-- ============ NEW ARRIVALS SECTION (jika ada produk NEW aktif) ============ -->
<?php if (!empty($newarrival_products)): ?>
<section class="section">
    <div class="section-header">
        <h2><i class="fas fa-sparkles accent"></i> NEW ARRIVALS</h2>
        <a href="<?= url('new_arrivals.php') ?>" class="view-all">Lihat Semua <i class="fas fa-arrow-right"></i></a>
    </div>
    <div class="product-grid">
        <?php foreach ($newarrival_products as $p): ?>
            <div class="product-card product-card-new">
                <a href="<?= url('product_detail.php?id=' . $p['id']) ?>" class="pc-image">
                    <span class="pc-badge badge-new"><i class="fas fa-bolt"></i> NEW</span>
                    <span class="pc-cat"><?= e($p['category_name']) ?></span>
                    <img src="<?= e($p['image']) ?>" alt="<?= e($p['name']) ?>" loading="lazy">
                </a>
                <div class="pc-body">
                    <a href="<?= url('product_detail.php?id=' . $p['id']) ?>" class="pc-title"><?= e($p['name']) ?></a>
                    <div class="pc-release"><?= e($p['release_date'] ?: 'Ready Stock') ?></div>
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
</section>
<?php endif; ?>

<!-- ============ FLASH PRODUCTS (Hot Items) ============ -->
<section class="section">
    <div class="section-header">
        <h2><i class="fas fa-bolt accent"></i> Flash Pre-Order & Hot Items</h2>
        <a href="<?= url('products.php') ?>" class="view-all">Lihat Semua <i class="fas fa-arrow-right"></i></a>
    </div>
    <div class="product-grid">
        <?php foreach ($flash_products as $p): ?>
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
                    <div class="pc-release"><?= e($p['release_date'] ?: 'Ready Stock') ?></div>
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
</section>

<!-- ============ LATEST PRODUCTS ============ -->
<section class="section">
    <div class="section-header">
        <h2><i class="fas fa-clock accent"></i> Produk Terbaru</h2>
        <a href="<?= url('products.php') ?>" class="view-all">Lihat Semua <i class="fas fa-arrow-right"></i></a>
    </div>
    <div class="product-grid">
        <?php foreach ($latest_products as $p): ?>
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
                    <div class="pc-release"><?= e($p['release_date'] ?: 'Ready Stock') ?></div>
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
</section>

<?php include __DIR__ . '/includes/footer.php'; ?>
