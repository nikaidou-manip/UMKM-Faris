<?php
require_once __DIR__ . '/includes/functions.php';
global $pdo;

// Auto-cleanup sudah dijalankan di header.php
$sort = $_GET['sort'] ?? 'ending_soon';

// Ambil semua produk flash sale aktif (limit besar) - defensive
try {
    $products = get_flash_sale_products($pdo, 60);
} catch (Throwable $e) {
    $products = [];
    set_flash('error', 'Tabel belum di-migrate. Jalankan database/migration_upgrade.sql terlebih dahulu.');
}

// Apply sort
if ($sort === 'price_asc') {
    usort($products, fn($a, $b) => $a['price'] <=> $b['price']);
} elseif ($sort === 'price_desc') {
    usort($products, fn($a, $b) => $b['price'] <=> $a['price']);
} elseif ($sort === 'newest') {
    usort($products, fn($a, $b) => strtotime($b['created_at']) <=> strtotime($a['created_at']));
} else {
    // ending_soon: sort by flash_sale_ends_at asc (paling cepat berakhir di atas)
    usort($products, fn($a, $b) => strtotime($a['flash_sale_ends_at']) <=> strtotime($b['flash_sale_ends_at']));
}

// Cari flash sale paling cepat berakhir untuk hero countdown
$soonest = null;
$soonest_seconds = 0;
if (!empty($products)) {
    $soonest = $products[0];
    $soonest_seconds = flash_sale_remaining_seconds($soonest);
}

$page_title = 'Flash Sale';
include __DIR__ . '/includes/header.php';
?>

<div class="container" style="margin-top:16px;">
    <div class="breadcrumb">
        <a href="<?= url('index.php') ?>">Beranda</a>
        <span class="sep">/</span>
        <span>Flash Sale</span>
    </div>
</div>

<!-- ============ FLASH SALE HERO ============ -->
<section class="fs-hero">
    <div class="fs-hero-bg"></div>
    <div class="fs-hero-pattern"></div>
    <div class="fs-hero-content">
        <span class="hb-badge"><i class="fas fa-bolt"></i> LIMITED TIME</span>
        <h1 class="hb-title-lg">⚡ FLASH SALE ⚡</h1>
        <p class="hb-subtitle">Produk dengan diskon eksklusif & stok terbatas. Buruan sebelum kehabisan!</p>
        <?php if ($soonest): ?>
            <div class="fs-countdown-wrap">
                <div class="fs-countdown-label">Berakhir dalam:</div>
                <div class="fs-countdown-timer" data-ends-at="<?= e($soonest['flash_sale_ends_at']) ?>" data-remaining="<?= $soonest_seconds ?>">
                    <div class="fs-cd-unit"><span class="fs-cd-num" data-cd-days>00</span><span class="fs-cd-cap">Hari</span></div>
                    <div class="fs-cd-sep">:</div>
                    <div class="fs-cd-unit"><span class="fs-cd-num" data-cd-hours>00</span><span class="fs-cd-cap">Jam</span></div>
                    <div class="fs-cd-sep">:</div>
                    <div class="fs-cd-unit"><span class="fs-cd-num" data-cd-mins>00</span><span class="fs-cd-cap">Menit</span></div>
                    <div class="fs-cd-sep">:</div>
                    <div class="fs-cd-unit"><span class="fs-cd-num" data-cd-secs>00</span><span class="fs-cd-cap">Detik</span></div>
                </div>
            </div>
        <?php endif; ?>
    </div>
</section>

<!-- ============ PRODUCTS GRID ============ -->
<section class="section">
    <div class="section-header">
        <h2><i class="fas fa-bolt accent"></i> Produk Flash Sale <span class="muted" style="font-size:14px;font-weight:400;">(<?= count($products) ?> produk)</span></h2>
        <form method="get" action="" style="display:flex;gap:8px;align-items:center;">
            <select name="sort" onchange="this.form.submit()" class="filter-bar-input" style="padding:8px 12px;border:1.5px solid var(--border);border-radius:8px;font-size:13px;">
                <option value="ending_soon" <?= $sort==='ending_soon'?'selected':'' ?>>Paling Seger Berakhir</option>
                <option value="newest" <?= $sort==='newest'?'selected':'' ?>>Terbaru</option>
                <option value="price_asc" <?= $sort==='price_asc'?'selected':'' ?>>Harga Termurah</option>
                <option value="price_desc" <?= $sort==='price_desc'?'selected':'' ?>>Harga Termahal</option>
            </select>
        </form>
    </div>

    <?php if (empty($products)): ?>
        <div class="empty-state">
            <i class="fas fa-bolt"></i>
            <h3>Tidak ada flash sale aktif saat ini</h3>
            <p>Pantau terus halaman ini untuk flash sale berikutnya!</p>
            <a href="<?= url('products.php') ?>" class="btn btn-primary mt-2"><i class="fas fa-th"></i> Lihat Semua Produk</a>
        </div>
    <?php else: ?>
        <div class="product-grid">
            <?php foreach ($products as $p):
                $remaining = flash_sale_remaining_seconds($p);
                $has_disc = has_flash_sale_discount($p);
                $sale_price = $has_disc ? flash_sale_price($p) : (float)$p['price'];
            ?>
                <div class="product-card product-card-flashsale">
                    <a href="<?= url('product_detail.php?id=' . $p['id']) ?>" class="pc-image">
                        <span class="pc-badge badge-flashsale">
                            <i class="fas fa-bolt"></i> FLASH SALE
                        </span>
                        <?php if ($has_disc): ?>
                            <span class="pc-badge badge-flashsale-disc">-<?= (int)$p['flash_sale_discount'] ?>%</span>
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
    <?php endif; ?>
</section>

<script>
// ====== Live countdown untuk semua timer ======
(function() {
    var heroTimer  = document.querySelector('.fs-countdown-timer');
    var miniTimers = document.querySelectorAll('.fs-mini-countdown');

    function pad(n) { return n < 10 ? '0' + n : '' + n; }

    function updateHero(remaining) {
        if (!heroTimer) return;
        if (remaining <= 0) {
            // Reload page ketika timer hero habis
            location.reload();
            return;
        }
        var days  = Math.floor(remaining / 86400);
        var hours = Math.floor((remaining % 86400) / 3600);
        var mins  = Math.floor((remaining % 3600) / 60);
        var secs  = remaining % 60;
        heroTimer.querySelector('[data-cd-days]').textContent  = pad(days);
        heroTimer.querySelector('[data-cd-hours]').textContent = pad(hours);
        heroTimer.querySelector('[data-cd-mins]').textContent   = pad(mins);
        heroTimer.querySelector('[data-cd-secs]').textContent    = pad(secs);
    }

    function updateMini(el, remaining) {
        if (!el) return;
        var txt = el.querySelector('.fs-mini-text');
        if (!txt) return;
        if (remaining <= 0) {
            txt.textContent = '00:00:00';
            el.classList.add('expired');
            return;
        }
        var days  = Math.floor(remaining / 86400);
        var hours = Math.floor((remaining % 86400) / 3600);
        var mins  = Math.floor((remaining % 3600) / 60);
        var secs  = remaining % 60;
        var label;
        if (days > 0) {
            label = days + 'd ' + pad(hours) + ':' + pad(mins) + ':' + pad(secs);
        } else {
            label = pad(hours) + ':' + pad(mins) + ':' + pad(secs);
        }
        txt.textContent = label;
    }

    // Init hero
    var heroRemaining = heroTimer ? parseInt(heroTimer.getAttribute('data-remaining'), 10) : 0;

    // Init mini timers
    var miniState = [];
    miniTimers.forEach(function(el, i) {
        miniState[i] = parseInt(el.getAttribute('data-remaining'), 10);
    });

    function tick() {
        if (heroTimer) {
            updateHero(heroRemaining);
            heroRemaining = Math.max(0, heroRemaining - 1);
        }
        miniState.forEach(function(r, i) {
            updateMini(miniTimers[i], r);
            miniState[i] = Math.max(0, r - 1);
        });
    }

    tick();
    setInterval(tick, 1000);
})();
</script>

<?php include __DIR__ . '/includes/footer.php'; ?>
