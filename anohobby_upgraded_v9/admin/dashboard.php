<?php
require_once __DIR__ . '/../includes/admin_header.php';
global $pdo;

// Statistik utama
$total_products = $pdo->query("SELECT COUNT(*) FROM products")->fetchColumn();
$total_customers = $pdo->query("SELECT COUNT(*) FROM users WHERE role='customer'")->fetchColumn();
$total_transactions = $pdo->query("SELECT COUNT(*) FROM transactions")->fetchColumn();

// Revenue: hitung SEMUA transaksi yang BUKAN cancelled (pending + paid + shipped + completed)
// Sebelumnya cuma hitung 'paid','shipped','completed' - jadi Rp 0 kalau semua masih pending
$total_revenue = $pdo->query("SELECT COALESCE(SUM(grand_total),0) FROM transactions WHERE status <> 'cancelled'")->fetchColumn();
$paid_revenue = $pdo->query("SELECT COALESCE(SUM(grand_total),0) FROM transactions WHERE status IN ('paid','shipped','completed')")->fetchColumn();
$pending_count = $pdo->query("SELECT COUNT(*) FROM transactions WHERE status='pending'")->fetchColumn();

// Transaksi terbaru
$recent_trx = $pdo->query("SELECT t.*, u.full_name FROM transactions t JOIN users u ON t.user_id = u.id ORDER BY t.created_at DESC LIMIT 6")->fetchAll();

// Produk terlaris
$top_products = $pdo->query("SELECT p.name, p.image, p.price, p.stock, COALESCE(SUM(ti.quantity),0) AS sold
                             FROM products p
                             LEFT JOIN transaction_items ti ON ti.product_id = p.id
                             GROUP BY p.id
                             ORDER BY sold DESC, p.id
                             LIMIT 5")->fetchAll();

$page_title = 'Dashboard';
?>
<div class="stat-grid">
    <div class="stat-card">
        <div class="stat-info">
            <div class="stat-label">Total Produk</div>
            <div class="stat-value"><?= $total_products ?></div>
        </div>
        <div class="stat-icon orange"><i class="fas fa-box"></i></div>
    </div>
    <div class="stat-card">
        <div class="stat-info">
            <div class="stat-label">Pelanggan</div>
            <div class="stat-value"><?= $total_customers ?></div>
        </div>
        <div class="stat-icon blue"><i class="fas fa-users"></i></div>
    </div>
    <div class="stat-card">
        <div class="stat-info">
            <div class="stat-label">Transaksi</div>
            <div class="stat-value"><?= $total_transactions ?></div>
        </div>
        <div class="stat-icon green"><i class="fas fa-receipt"></i></div>
    </div>
    <div class="stat-card">
        <div class="stat-info">
            <div class="stat-label">Pendapatan</div>
            <div class="stat-value" style="font-size:20px;"><?= rupiah($total_revenue) ?></div>
            <small class="text-muted">Lunas: <?= rupiah($paid_revenue) ?> &middot; <?= (int)$pending_count ?> pending</small>
        </div>
        <div class="stat-icon purple"><i class="fas fa-money-bill-wave"></i></div>
    </div>
</div>

<div style="display:grid;grid-template-columns:1.5fr 1fr;gap:20px;">
    <!-- Transaksi Terbaru -->
    <div class="panel">
        <div class="panel-header">
            <h2><i class="fas fa-clock"></i> Transaksi Terbaru</h2>
            <a href="<?= url('admin/transactions.php') ?>" class="btn btn-outline btn-sm">Lihat Semua</a>
        </div>
        <?php if (empty($recent_trx)): ?>
            <div class="empty-state"><i class="fas fa-receipt"></i><p>Belum ada transaksi.</p></div>
        <?php else: ?>
            <table class="data-table">
                <thead><tr><th>Invoice</th><th>Pelanggan</th><th>Total</th><th>Status</th><th>Tanggal</th><th>Aksi</th></tr></thead>
                <tbody>
                    <?php foreach ($recent_trx as $t): ?>
                        <tr>
                            <td><strong><?= e($t['invoice_no']) ?></strong></td>
                            <td><?= e($t['full_name']) ?></td>
                            <td class="text-primary" style="font-weight:700;"><?= rupiah($t['grand_total']) ?></td>
                            <td><span class="status-badge status-<?= e($t['status']) ?>"><?= e(ucfirst($t['status'])) ?></span></td>
                            <td><?= date('d M Y H:i', strtotime($t['created_at'])) ?></td>
                            <td><a href="<?= url('admin/transaction_detail.php?id=' . $t['id']) ?>" class="btn btn-outline btn-sm"><i class="fas fa-eye"></i></a></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>
    </div>

    <!-- Produk Terlaris -->
    <div class="panel">
        <div class="panel-header">
            <h2><i class="fas fa-fire"></i> Produk Terlaris</h2>
            <a href="<?= url('admin/products.php') ?>" class="btn btn-outline btn-sm">Kelola</a>
        </div>
        <?php if (empty($top_products)): ?>
            <div class="empty-state"><i class="fas fa-box"></i><p>Belum ada produk.</p></div>
        <?php else: ?>
            <div style="display:flex;flex-direction:column;gap:10px;">
                <?php foreach ($top_products as $i => $p): ?>
                    <div style="display:flex;align-items:center;gap:12px;padding:8px;border-radius:8px;background:var(--bg-alt);">
                        <div style="font-size:20px;font-weight:800;color:<?= $i<3?'var(--primary)':'var(--text-muted)' ?>;width:24px;"><?= $i+1 ?></div>
                        <img src="<?= e($p['image']) ?>" alt="" style="width:40px;height:40px;object-fit:cover;border-radius:6px;">
                        <div style="flex:1;min-width:0;">
                            <div style="font-weight:600;font-size:13px;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;"><?= e($p['name']) ?></div>
                            <div class="text-muted" style="font-size:11px;"><?= rupiah($p['price']) ?> &middot; Stok <?= $p['stock'] ?></div>
                        </div>
                        <div style="text-align:right;">
                            <div class="text-success" style="font-weight:700;font-size:14px;"><?= $p['sold'] ?></div>
                            <div class="text-muted" style="font-size:10px;">terjual</div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
</div>

<!-- Quick Actions -->
<div class="panel">
    <div class="panel-header"><h2><i class="fas fa-bolt"></i> Quick Actions</h2></div>
    <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(180px,1fr));gap:12px;">
        <a href="<?= url('admin/products.php?action=add') ?>" class="btn btn-primary"><i class="fas fa-plus"></i> Tambah Produk</a>
        <a href="<?= url('admin/transactions.php') ?>" class="btn btn-dark"><i class="fas fa-receipt"></i> Lihat Transaksi</a>
        <a href="<?= url('admin/customers.php') ?>" class="btn btn-outline"><i class="fas fa-users"></i> Data Pelanggan</a>
        <a href="<?= url('index.php') ?>" target="_blank" class="btn btn-outline"><i class="fas fa-external-link-alt"></i> Lihat Toko</a>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/admin_footer.php'; ?>
