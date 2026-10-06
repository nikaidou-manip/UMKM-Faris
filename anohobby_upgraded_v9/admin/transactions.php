<?php
require_once __DIR__ . '/../includes/functions.php';
require_admin();
global $pdo;

// ===== HANDLE DELETE (HARUS SEBELUM OUTPUT HTML) =====
if (isset($_GET['delete'])) {
    $del_id = (int)$_GET['delete'];
    $stmt = $pdo->prepare("SELECT invoice_no FROM transactions WHERE id = ?");
    $stmt->execute([$del_id]);
    $inv = $stmt->fetchColumn();
    if ($inv) {
        $pdo->prepare("DELETE FROM transactions WHERE id = ?")->execute([$del_id]);
        set_flash('success', "Transaksi {$inv} telah dihapus.");
    }
    header('Location: ' . url('admin/transactions.php'));
    exit;
}

// Lanjut load admin_header (yang output HTML)
require_once __DIR__ . '/../includes/admin_header.php';

// Filter status
$status_filter = $_GET['status'] ?? '';
$search = trim($_GET['q'] ?? '');

$sql = "SELECT t.*, u.username, u.full_name, u.email
        FROM transactions t JOIN users u ON t.user_id = u.id WHERE 1=1";
$params = [];
if ($status_filter) { $sql .= " AND t.status = ?"; $params[] = $status_filter; }
if ($search) {
    $sql .= " AND (t.invoice_no LIKE ? OR u.full_name LIKE ? OR u.username LIKE ?)";
    $params[] = "%$search%"; $params[] = "%$search%"; $params[] = "%$search%";
}
$sql .= " ORDER BY t.created_at DESC";
$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$transactions = $stmt->fetchAll();

// Stats
$total_trx = count($transactions);
$total_revenue = array_sum(array_map(fn($t) => $t['status']!=='cancelled' ? (float)$t['grand_total'] : 0, $transactions));
$pending = count(array_filter($transactions, fn($t) => $t['status']==='pending'));

$page_title = 'Data Transaksi';
?>
<div class="stat-grid">
    <div class="stat-card"><div class="stat-info"><div class="stat-label">Total Transaksi</div><div class="stat-value"><?= $total_trx ?></div></div><div class="stat-icon orange"><i class="fas fa-receipt"></i></div></div>
    <div class="stat-card"><div class="stat-info"><div class="stat-label">Total Pendapatan</div><div class="stat-value" style="font-size:18px;"><?= rupiah($total_revenue) ?></div></div><div class="stat-icon green"><i class="fas fa-money-bill-wave"></i></div></div>
    <div class="stat-card"><div class="stat-info"><div class="stat-label">Pending</div><div class="stat-value"><?= $pending ?></div></div><div class="stat-icon purple"><i class="fas fa-clock"></i></div></div>
</div>

<div class="panel">
    <div class="panel-header">
        <h2><i class="fas fa-receipt"></i> Tabel Data Transaksi</h2>
    </div>

    <form method="get" action="" class="filter-bar" style="margin-bottom:14px;display:flex;gap:10px;flex-wrap:wrap;">
        <input type="text" name="q" value="<?= e($search) ?>" placeholder="Cari invoice / pelanggan..." class="form-control" style="max-width:280px;">
        <select name="status" class="form-control" style="max-width:180px;">
            <option value="">Semua Status</option>
            <?php foreach (['pending','paid','shipped','completed','cancelled'] as $s): ?>
                <option value="<?= $s ?>" <?= $status_filter===$s?'selected':'' ?>><?= ucfirst($s) ?></option>
            <?php endforeach; ?>
        </select>
        <button type="submit" class="btn btn-primary"><i class="fas fa-search"></i> Filter</button>
        <a href="<?= url('admin/transactions.php') ?>" class="btn btn-outline"><i class="fas fa-redo"></i></a>
    </form>

    <?php if (empty($transactions)): ?>
        <div class="empty-state"><i class="fas fa-receipt"></i><h3>Tidak ada transaksi</h3><p>Belum ada transaksi yang sesuai filter.</p></div>
    <?php else: ?>
        <div style="overflow-x:auto;">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>No. Invoice</th>
                        <th>Pelanggan</th>
                        <th>Tanggal</th>
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
                            <td>
                                <?= e($t['full_name']) ?><br>
                                <small class="text-muted">@<?= e($t['username']) ?> &middot; <?= e($t['email']) ?></small>
                            </td>
                            <td><?= date('d M Y H:i', strtotime($t['created_at'])) ?></td>
                            <td><?= e($t['payment_method']) ?></td>
                            <td><?= e($t['shipping_method']) ?><br><small class="text-muted"><?= rupiah($t['shipping_cost']) ?></small></td>
                            <td class="text-primary" style="font-weight:700;">
                                <?= rupiah($t['grand_total']) ?>
                                <?php if (!empty($t['voucher_code'])): ?>
                                    <br><small style="color:#2E7D32;"><i class="fas fa-ticket-alt"></i> <?= e($t['voucher_code']) ?>
                                    <?php if ((float)$t['voucher_discount'] > 0): ?> (-<?= rupiah($t['voucher_discount']) ?>)<?php endif; ?>
                                    </small>
                                <?php endif; ?>
                                <?php if ((int)$t['points_used'] > 0): ?>
                                    <br><small style="color:#F44336;"><i class="fas fa-coins"></i> Pakai <?= number_format((int)$t['points_used']) ?> koin (-<?= rupiah($t['points_discount']) ?>)</small>
                                <?php endif; ?>
                                <?php if ((int)$t['points_earned'] > 0): ?>
                                    <br><small style="color:#2E7D32;"><i class="fas fa-gift"></i> Dapat <?= number_format((int)$t['points_earned']) ?> koin</small>
                                <?php endif; ?>
                            </td>
                            <td><span class="status-badge status-<?= e($t['status']) ?>"><?= e(ucfirst($t['status'])) ?></span></td>
                            <td>
                                <div class="table-actions">
                                    <a href="<?= url('admin/transaction_detail.php?id=' . $t['id']) ?>" title="Detail"><i class="fas fa-eye"></i> Detail</a>
                                    <a href="<?= url('invoice.php?id=' . $t['id']) ?>" target="_blank" title="Invoice"><i class="fas fa-print"></i></a>
                                    <a href="<?= url('admin/transactions.php?delete=' . $t['id']) ?>" class="danger" data-confirm="Yakin hapus transaksi &quot;<?= e($t['invoice_no']) ?>&quot;? Stok produk TIDAK dikembalikan otomatis."><i class="fas fa-trash"></i></a>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php endif; ?>
</div>

<?php
// Handle delete sudah diproses di atas
?>

<?php require_once __DIR__ . '/../includes/admin_footer.php'; ?>
