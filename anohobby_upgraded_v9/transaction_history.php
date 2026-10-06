<?php
require_once __DIR__ . '/includes/functions.php';
require_login();
global $pdo;

$user_id = $_SESSION['user_id'];

// Jika admin -> tampilkan semua transaksi. Jika customer -> hanya miliknya.
if (is_admin()) {
    $stmt = $pdo->query("SELECT t.*, u.username, u.full_name FROM transactions t JOIN users u ON t.user_id = u.id ORDER BY t.created_at DESC");
} else {
    $stmt = $pdo->prepare("SELECT t.*, u.username, u.full_name FROM transactions t JOIN users u ON t.user_id = u.id WHERE t.user_id = ? ORDER BY t.created_at DESC");
    $stmt->execute([$user_id]);
}
$transactions = $stmt->fetchAll();

$page_title = 'Riwayat Transaksi';
include __DIR__ . '/includes/header.php';
?>
<div class="container" style="margin-top:16px;">
    <div class="breadcrumb">
        <a href="<?= url('index.php') ?>">Beranda</a>
        <span class="sep">/</span>
        <a href="<?= url('profile.php') ?>">Profile</a>
        <span class="sep">/</span>
        <span>Riwayat Transaksi</span>
    </div>
</div>

<section class="section">
    <div class="section-header">
        <h2><i class="fas fa-receipt accent"></i> Riwayat Transaksi</h2>
    </div>

    <?php if (empty($transactions)): ?>
        <div class="empty-state">
            <i class="fas fa-receipt"></i>
            <h3>Belum ada transaksi</h3>
            <p>Yuk mulai belanja untuk mengisi riwayat transaksimu!</p>
            <a href="<?= url('products.php') ?>" class="btn btn-primary mt-2"><i class="fas fa-shopping-bag"></i> Mulai Belanja</a>
        </div>
    <?php else: ?>
        <div class="panel" style="background:white;border-radius:12px;padding:20px;border:1px solid var(--border-light);overflow-x:auto;">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>No. Invoice</th>
                        <th>Tanggal</th>
                        <?php if (is_admin()): ?><th>Pelanggan</th><?php endif; ?>
                        <th>Pembayaran</th>
                        <th>Pengiriman</th>
                        <th>Total</th>
                        <th>Status</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($transactions as $t): ?>
                        <tr>
                            <td><strong><?= e($t['invoice_no']) ?></strong></td>
                            <td><?= tgl_indo($t['created_at']) ?></td>
                            <?php if (is_admin()): ?><td><?= e($t['full_name']) ?><br><small class="muted">@<?= e($t['username']) ?></small></td><?php endif; ?>
                            <td><?= e($t['payment_method']) ?></td>
                            <td><?= e($t['shipping_method']) ?></td>
                            <td>
                                <?= rupiah($t['grand_total']) ?>
                                <?php if (!empty($t['voucher_code'])): ?>
                                    <br><small class="muted" style="color:#2E7D32;"><i class="fas fa-ticket-alt"></i> <?= e($t['voucher_code']) ?></small>
                                <?php endif; ?>
                                <?php if ((int)$t['points_used'] > 0): ?>
                                    <br><small class="muted" style="color:#FF8F00;"><i class="fas fa-coins"></i> <?= number_format((int)$t['points_used']) ?> koin</small>
                                <?php endif; ?>
                                <?php if ((int)$t['points_earned'] > 0): ?>
                                    <br><small class="muted" style="color:#FF8F00;">+<?= number_format((int)$t['points_earned']) ?> koin didapat</small>
                                <?php endif; ?>
                            </td>
                            <td><span class="invoice-status status-<?= e($t['status']) ?>"><?= e(ucfirst($t['status'])) ?></span></td>
                            <td>
                                <div class="table-actions">
                                    <a href="<?= url('invoice.php?id=' . $t['id']) ?>" title="Lihat Invoice"><i class="fas fa-eye"></i> Invoice</a>
                                    <a href="<?= url('invoice.php?id=' . $t['id']) ?>" target="_blank" title="Print"><i class="fas fa-print"></i></a>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php endif; ?>
</section>

<?php include __DIR__ . '/includes/footer.php'; ?>
