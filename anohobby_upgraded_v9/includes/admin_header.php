<?php
// ============================================================
// ADMIN HEADER - dipakai di semua halaman /admin/*.php
// ============================================================
require_once __DIR__ . '/../includes/functions.php';
require_admin();
global $pdo;
$admin_page = basename($_SERVER['SCRIPT_NAME'], '.php');
$flash = get_flash();
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= isset($page_title) ? e($page_title) . ' - ' : '' ?>Admin Panel - <?= STORE_NAME ?></title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="<?= asset('css/admin.css') ?>?v=<?= @filemtime(__DIR__ . '/../assets/css/admin.css') ?>">
    <?php if (isset($extra_css)): ?><style><?= $extra_css ?></style><?php endif; ?>
</head>
<body>
<div class="admin-shell">
    <!-- ===== SIDEBAR ===== -->
    <aside class="admin-sidebar">
        <a href="<?= url('admin/dashboard.php') ?>" class="admin-logo">
            <img src="<?= asset('img/logo.png') ?>" alt="<?= STORE_NAME ?>" class="admin-logo-img">
            <small>Admin Panel</small>
        </a>
        <nav class="admin-nav">
            <a href="<?= url('admin/dashboard.php') ?>" class="<?= $admin_page==='dashboard'?'active':'' ?>"><i class="fas fa-tachometer-alt"></i> Dashboard</a>
            <a href="<?= url('admin/products.php') ?>" class="<?= $admin_page==='products'?'active':'' ?>"><i class="fas fa-box"></i> Data Produk</a>
            <a href="<?= url('admin/flash_sale.php') ?>" class="<?= $admin_page==='flash_sale'?'active':'' ?>"><i class="fas fa-bolt"></i> Flash Sale</a>
            <a href="<?= url('admin/vouchers.php') ?>" class="<?= $admin_page==='vouchers'?'active':'' ?>"><i class="fas fa-ticket-alt"></i> Data Voucher</a>
            <a href="<?= url('admin/transactions.php') ?>" class="<?= $admin_page==='transactions'||$admin_page==='transaction_detail'?'active':'' ?>"><i class="fas fa-receipt"></i> Data Transaksi</a>
            <a href="<?= url('admin/customers.php') ?>" class="<?= $admin_page==='customers'?'active':'' ?>"><i class="fas fa-users"></i> Data Pelanggan</a>
            <hr>
            <a href="<?= url('index.php') ?>" target="_blank"><i class="fas fa-external-link-alt"></i> Lihat Toko</a>
            <a href="<?= url('profile.php') ?>"><i class="fas fa-id-card"></i> Profile</a>
            <a href="<?= url('logout.php') ?>" class="text-danger"><i class="fas fa-sign-out-alt"></i> Logout</a>
        </nav>
    </aside>

    <!-- ===== MAIN ===== -->
    <div class="admin-main">
        <header class="admin-topbar">
            <button class="sidebar-toggle" id="sidebarToggle"><i class="fas fa-bars"></i></button>
            <h1><?= e($page_title ?? 'Dashboard') ?></h1>
            <div class="admin-user">
                <i class="fas fa-user-circle"></i>
                <span><?= e($_SESSION['full_name']) ?></span>
            </div>
        </header>
        <div class="admin-content">

        <?php if (!empty($flash)): ?>
            <?php foreach ($flash as $f): ?>
                <div class="flash flash-<?= e($f['type']) ?>">
                    <i class="fas fa-<?= $f['type']==='success'?'check-circle':($f['type']==='error'?'exclamation-circle':'info-circle') ?>"></i>
                    <?= e($f['msg']) ?>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
