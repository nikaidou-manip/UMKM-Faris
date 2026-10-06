<?php
// ============================================================
// HEADER - dipakai di semua halaman publik (non-admin)
// ============================================================
require_once __DIR__ . '/functions.php';
global $pdo;

// Auto-cleanup: hapus tag NEW yang sudah lebih dari 7 hari & flash sale yang sudah ended
// Ringan - dijalankan sekali per request di halaman publik
try {
    cleanup_expired_new_badges($pdo);
    cleanup_expired_flash_sales($pdo);
} catch (Throwable $e) {
    // suppress cleanup errors (e.g., jika tabel belum di-migrate)
}

$cart_count = cart_count();
$current_page = basename($_SERVER['SCRIPT_NAME'], '.php');
$categories = get_categories($pdo);
$flash = get_flash();

// Hitung badge angka untuk menu navbar (jika tabel voucher/flashsale sudah ada)
try {
    $new_arrivals_count = count_new_arrivals($pdo);
    $flash_sale_count   = count_flash_sale($pdo);
} catch (Throwable $e) {
    $new_arrivals_count = 0;
    $flash_sale_count   = 0;
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= isset($page_title) ? e($page_title) . ' - ' : '' ?><?= STORE_NAME ?> - Toko Hobby Online</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800;900&family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="<?= asset('css/style.css') ?>?v=<?= @filemtime(__DIR__ . '/../assets/css/style.css') ?>">
    <link rel="stylesheet" href="<?= asset('css/shop-ui.css') ?>?v=<?= @filemtime(__DIR__ . '/../assets/css/shop-ui.css') ?>">
    <?php if (isset($extra_css)): ?><style><?= $extra_css ?></style><?php endif; ?>
</head>
<body>
<!-- ============ TOP BAR ============ -->
<div class="topbar">
    <div class="container topbar-inner">
        <span><i class="fas fa-truck"></i> Gratis ongkir min. belanja Rp 500.000</span>
        <span><i class="fas fa-phone"></i> <?= STORE_PHONE ?></span>
    </div>
</div>

<!-- ============ HEADER / NAVBAR ============ -->
<header class="site-header">
    <div class="container header-inner">
        <!-- LOGO -->
        <a href="<?= url('index.php') ?>" class="logo">
            <img src="<?= asset('img/logo.png') ?>" alt="<?= STORE_NAME ?>" class="logo-img">
        </a>

        <!-- SEARCH BAR -->
        <form class="searchbar" action="<?= url('products.php') ?>" method="get">
            <input type="text" name="q" placeholder="Cari modelkit, TCG, PVC figure, tools rakit..." value="<?= e($_GET['q'] ?? '') ?>">
            <span class="kbd">Ctrl K</span>
            <button type="submit" aria-label="Cari"><i class="fas fa-search"></i></button>
        </form>

        <!-- ACTIONS -->
        <nav class="header-actions">
            <a href="<?= url('cart.php') ?>" class="action-icon" title="Keranjang">
                <i class="fas fa-shopping-bag"></i>
                <?php if ($cart_count > 0): ?><span class="badge"><?= $cart_count ?></span><?php endif; ?>
            </a>
            <?php if (is_logged_in()): ?>
                <div class="dropdown">
                    <button class="action-icon action-icon-avatar" title="Akun">
                        <?php $hdr_avatar = user_avatar_url($_SESSION['avatar'] ?? null); ?>
                        <?php if ($hdr_avatar): ?>
                            <img src="<?= e($hdr_avatar) ?>" alt="Avatar" class="header-avatar-img">
                        <?php else: ?>
                            <i class="fas fa-user"></i>
                        <?php endif; ?>
                    </button>
                    <div class="dropdown-menu">
                        <div class="dropdown-header">
                            Hi, <strong><?= e($_SESSION['full_name']) ?></strong><br>
                            <small><?= e($_SESSION['role'] === 'admin' ? 'Administrator' : 'Pelanggan') ?></small>
                        </div>
                        <?php if (is_admin()): ?>
                            <a href="<?= url('admin/dashboard.php') ?>"><i class="fas fa-tachometer-alt"></i> Dashboard Admin</a>
                        <?php endif; ?>
                        <a href="<?= url('profile.php') ?>"><i class="fas fa-id-card"></i> Profile Saya</a>
                        <a href="<?= url('my_vouchers.php') ?>"><i class="fas fa-ticket-alt"></i> Voucher Saya</a>
                        <a href="<?= url('transaction_history.php') ?>"><i class="fas fa-receipt"></i> Riwayat Transaksi</a>
                        <a href="<?= url('cart.php') ?>"><i class="fas fa-shopping-bag"></i> Keranjang</a>
                        <hr>
                        <a href="<?= url('logout.php') ?>" class="text-danger"><i class="fas fa-sign-out-alt"></i> Logout</a>
                    </div>
                </div>
            <?php else: ?>
                <a href="<?= url('login.php') ?>" class="signin-btn">Sign in</a>
                <a href="<?= url('register.php') ?>" class="btn btn-primary btn-sm">Register</a>
            <?php endif; ?>
        </nav>
    </div>

    <!-- KATEGORI NAV -->
    <nav class="catnav">
        <div class="container catnav-inner">
            <a href="<?= url('index.php') ?>" class="catnav-item <?= $current_page==='index'?'active':'' ?>"><i class="fas fa-home"></i> Beranda</a>
            <?php if ($flash_sale_count > 0): ?>
                <a href="<?= url('flash_sale.php') ?>" class="catnav-item catnav-flashsale <?= $current_page==='flash_sale'?'active':'' ?>">
                    <i class="fas fa-bolt"></i> Flash Sale
                    <span class="catnav-badge"><?= $flash_sale_count ?></span>
                </a>
            <?php else: ?>
                <a href="<?= url('flash_sale.php') ?>" class="catnav-item <?= $current_page==='flash_sale'?'active':'' ?>"><i class="fas fa-bolt"></i> Flash Sale</a>
            <?php endif; ?>
            <?php if ($new_arrivals_count > 0): ?>
                <a href="<?= url('new_arrivals.php') ?>" class="catnav-item catnav-new <?= $current_page==='new_arrivals'?'active':'' ?>">
                    <i class="fas fa-sparkles"></i> New Arrivals
                    <span class="catnav-badge new"><?= $new_arrivals_count ?></span>
                </a>
            <?php else: ?>
                <a href="<?= url('new_arrivals.php') ?>" class="catnav-item <?= $current_page==='new_arrivals'?'active':'' ?>"><i class="fas fa-sparkles"></i> New Arrivals</a>
            <?php endif; ?>
            <a href="<?= url('all_categories.php') ?>" class="catnav-item <?= $current_page==='all_categories'?'active':'' ?>"><i class="fas fa-th-large"></i> Semua Kategori</a>
            <a href="<?= url('products.php') ?>" class="catnav-item <?= $current_page==='products'?'active':'' ?>"><i class="fas fa-th"></i> Semua Produk</a>
            <?php foreach ($categories as $c): ?>
                <a href="<?= url('category.php?slug=' . $c['slug']) ?>" class="catnav-item <?= (basename($_SERVER['SCRIPT_NAME'])==='category.php' && ($_GET['slug']??'')===$c['slug'])?'active':'' ?>"><?= e($c['name']) ?></a>
            <?php endforeach; ?>
        </div>
    </nav>
</header>

<!-- ============ FLASH MESSAGES ============ -->
<?php if (!empty($flash)): ?>
<div class="flash-container">
    <?php foreach ($flash as $f): ?>
        <div class="flash flash-<?= e($f['type']) ?>">
            <i class="fas fa-<?= $f['type']==='success'?'check-circle':($f['type']==='error'?'exclamation-circle':'info-circle') ?>"></i>
            <?= e($f['msg']) ?>
        </div>
    <?php endforeach; ?>
</div>
<?php endif; ?>

<main>
