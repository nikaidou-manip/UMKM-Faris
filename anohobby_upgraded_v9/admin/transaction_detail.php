<?php
require_once __DIR__ . '/../includes/admin_header.php';
global $pdo;

$id = (int)($_GET['id'] ?? 0);
if (!$id) { header('Location: ' . url('admin/transactions.php')); exit; }

// Update status via POST
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['new_status'])) {
    $new_status = $_POST['new_status'];
    if (in_array($new_status, ['pending','paid','shipped','completed','cancelled'])) {
        $stmt = $pdo->prepare("UPDATE transactions SET status = ? WHERE id = ?");
        $stmt->execute([$new_status, $id]);
        set_flash('success', "Status transaksi diperbarui menjadi: " . ucfirst($new_status));
    }
    header('Location: ' . url('admin/transaction_detail.php?id=' . $id));
    exit;
}

$stmt = $pdo->prepare("SELECT t.*, u.username, u.email, u.full_name AS customer_name, u.phone AS customer_phone
                       FROM transactions t JOIN users u ON t.user_id = u.id WHERE t.id = ?");
$stmt->execute([$id]);
$trx = $stmt->fetch();
if (!$trx) {
    set_flash('error', 'Transaksi tidak ditemukan.');
    header('Location: ' . url('admin/transactions.php'));
    exit;
}

$stmt = $pdo->prepare("SELECT ti.*, p.image FROM transaction_items ti LEFT JOIN products p ON ti.product_id = p.id WHERE ti.transaction_id = ?");
$stmt->execute([$id]);
$items = $stmt->fetchAll();

$page_title = 'Detail Transaksi';
?>
<div style="display:flex;gap:10px;margin-bottom:16px;">
    <a href="<?= url('admin/transactions.php') ?>" class="btn btn-outline btn-sm"><i class="fas fa-arrow-left"></i> Kembali</a>
    <a href="<?= url('invoice.php?id=' . $trx['id']) ?>" target="_blank" class="btn btn-primary btn-sm"><i class="fas fa-print"></i> Cetak Invoice</a>
</div>

<div style="display:grid;grid-template-columns:2fr 1fr;gap:20px;">
    <!-- Detail -->
    <div class="panel">
        <div class="panel-header"><h2><i class="fas fa-receipt"></i> Invoice <?= e($trx['invoice_no']) ?></h2></div>
        <div style="display:grid;grid-template-columns:1fr 1fr;gap:16px;margin-bottom:18px;">
            <div>
                <strong class="text-muted" style="font-size:11px;text-transform:uppercase;">Tanggal</strong><br>
                <?= tgl_indo($trx['created_at']) ?>
            </div>
            <div>
                <strong class="text-muted" style="font-size:11px;text-transform:uppercase;">Status</strong><br>
                <span class="status-badge status-<?= e($trx['status']) ?>"><?= e(ucfirst($trx['status'])) ?></span>
            </div>
            <div>
                <strong class="text-muted" style="font-size:11px;text-transform:uppercase;">Pelanggan</strong><br>
                <?= e($trx['customer_name']) ?><br>
                <small class="text-muted">@<?= e($trx['username']) ?> &middot; <?= e($trx['email']) ?></small><br>
                <small class="text-muted">HP: <?= e($trx['customer_phone']) ?></small>
            </div>
            <div>
                <strong class="text-muted" style="font-size:11px;text-transform:uppercase;">Penerima & Alamat</strong><br>
                <?= e($trx['recipient_name']) ?> (<?= e($trx['recipient_phone']) ?>)<br>
                <small class="text-muted"><?= e($trx['shipping_address']) ?></small>
            </div>
            <div>
                <strong class="text-muted" style="font-size:11px;text-transform:uppercase;">Pembayaran</strong><br>
                <?= e($trx['payment_method']) ?>
            </div>
            <div>
                <strong class="text-muted" style="font-size:11px;text-transform:uppercase;">Pengiriman</strong><br>
                <?= e($trx['shipping_method']) ?> (<?= rupiah($trx['shipping_cost']) ?>)
            </div>
        </div>

        <h3 style="font-size:14px;font-weight:700;margin:18px 0 10px;"><i class="fas fa-box"></i> Item yang Dibeli</h3>
        <table class="data-table">
            <thead><tr><th>Gambar</th><th>Produk</th><th class="num">Harga</th><th class="num">Qty</th><th class="num">Subtotal</th></tr></thead>
            <tbody>
                <?php foreach ($items as $it): ?>
                    <tr>
                        <td><img src="<?= e($it['image'] ?: 'https://via.placeholder.com/50') ?>" alt="" class="thumb"></td>
                        <td><?= e($it['product_name']) ?></td>
                        <td class="num"><?= rupiah($it['price']) ?></td>
                        <td class="num"><?= $it['quantity'] ?></td>
                        <td class="num text-primary" style="font-weight:700;"><?= rupiah($it['subtotal']) ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
            <tfoot>
                <tr><td colspan="4" style="text-align:right;padding:10px;"><strong>Subtotal</strong></td><td class="num"><?= rupiah($trx['total']) ?></td></tr>
                <?php if (!empty($trx['voucher_code']) && (float)$trx['voucher_discount'] > 0): ?>
                    <tr style="background:#E8F5E9;"><td colspan="4" style="text-align:right;padding:10px;color:#2E7D32;"><strong><i class="fas fa-ticket-alt"></i> Voucher "<?= e($trx['voucher_code']) ?>"</strong></td><td class="num" style="color:#2E7D32;font-weight:700;">&minus; <?= rupiah($trx['voucher_discount']) ?></td></tr>
                <?php endif; ?>
                <?php if ((int)$trx['points_used'] > 0): ?>
                    <tr style="background:#FFF8E1;"><td colspan="4" style="text-align:right;padding:10px;color:#FF8F00;"><strong><i class="fas fa-coins"></i> Koin Dipakai (<?= number_format((int)$trx['points_used']) ?>)</strong></td><td class="num" style="color:#FF8F00;font-weight:700;">&minus; <?= rupiah($trx['points_discount']) ?></td></tr>
                <?php endif; ?>
                <tr><td colspan="4" style="text-align:right;padding:10px;"><strong>Ongkir</strong></td><td class="num"><?= rupiah($trx['shipping_cost']) ?></td></tr>
                <tr style="background:var(--bg-alt);"><td colspan="4" style="text-align:right;padding:12px;font-size:16px;font-weight:800;">GRAND TOTAL</td><td class="num text-primary" style="font-size:16px;font-weight:800;"><?= rupiah($trx['grand_total']) ?></td></tr>
                <?php if ((int)$trx['points_earned'] > 0): ?>
                    <tr style="background:#FFF3E0;"><td colspan="4" style="text-align:right;padding:10px;color:#FF8F00;"><strong><i class="fas fa-gift"></i> Koin yang Didapat User</strong></td><td class="num" style="color:#FF8F00;font-weight:700;">+ <?= number_format((int)$trx['points_earned']) ?> koin</td></tr>
                <?php endif; ?>
            </tfoot>
        </table>
    </div>

    <!-- Sidebar: Update status -->
    <div class="panel">
        <div class="panel-header"><h2><i class="fas fa-cog"></i> Update Status</h2></div>
        <form method="post" action="">
            <div class="form-group">
                <label class="form-label">Status Transaksi</label>
                <select name="new_status" class="form-control">
                    <?php foreach (['pending','paid','shipped','completed','cancelled'] as $s): ?>
                        <option value="<?= $s ?>" <?= $trx['status']===$s?'selected':'' ?>><?= ucfirst($s) ?></option>
                    <?php endforeach; ?>
                </select>
                <div class="form-hint">Ubah status untuk memperbarui progress pesanan.</div>
            </div>
            <button type="submit" class="btn btn-primary btn-block"><i class="fas fa-save"></i> Update Status</button>
        </form>
        <hr style="border:0;border-top:1px solid var(--border-light);margin:18px 0;">
        <h3 style="font-size:13px;font-weight:700;margin-bottom:8px;">Ringkasan</h3>
        <div style="font-size:13px;line-height:1.9;">
            <div><strong>Invoice:</strong> <?= e($trx['invoice_no']) ?></div>
            <div><strong>Total Item:</strong> <?= count($items) ?> produk</div>
            <div><strong>Grand Total:</strong> <span class="text-primary" style="font-weight:700;"><?= rupiah($trx['grand_total']) ?></span></div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/admin_footer.php'; ?>
