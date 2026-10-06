<?php
require_once __DIR__ . '/includes/functions.php';
require_login();
global $pdo;

$id = (int)($_GET['id'] ?? 0);
if (!$id) { header('Location: ' . url('index.php')); exit; }

// Load transaction + items
$stmt = $pdo->prepare("SELECT t.*, u.username, u.email FROM transactions t JOIN users u ON t.user_id = u.id WHERE t.id = ?");
$stmt->execute([$id]);
$trx = $stmt->fetch();

if (!$trx) {
    set_flash('error', 'Transaksi tidak ditemukan.');
    header('Location: ' . url('index.php'));
    exit;
}

// Hak akses: pemilik transaksi atau admin
if ($trx['user_id'] != $_SESSION['user_id'] && !is_admin()) {
    set_flash('error', 'Anda tidak punya akses ke invoice ini.');
    header('Location: ' . url('index.php'));
    exit;
}

$stmt = $pdo->prepare("SELECT * FROM transaction_items WHERE transaction_id = ?");
$stmt->execute([$id]);
$items = $stmt->fetchAll();

$page_title = 'Invoice ' . $trx['invoice_no'];
include __DIR__ . '/includes/header.php';
?>
<div class="container" style="margin-top:16px;">
    <div class="breadcrumb">
        <a href="<?= url('index.php') ?>">Beranda</a>
        <span class="sep">/</span>
        <?php if (is_admin()): ?>
            <a href="<?= url('admin/transactions.php') ?>">Data Transaksi</a>
        <?php else: ?>
            <a href="<?= url('transaction_history.php') ?>">Riwayat Transaksi</a>
        <?php endif; ?>
        <span class="sep">/</span>
        <span>Invoice <?= e($trx['invoice_no']) ?></span>
    </div>
</div>

<div class="invoice-wrap">
    <div class="invoice-actions">
        <a href="<?= is_admin() ? url('admin/transactions.php') : url('transaction_history.php') ?>" class="btn btn-outline btn-sm"><i class="fas fa-arrow-left"></i> Kembali</a>
        <button class="btn btn-primary btn-sm" data-print><i class="fas fa-print"></i> Print Invoice</button>
    </div>

    <div class="invoice-card">
        <div class="invoice-head">
            <div class="inv-from">
                <strong>HOBBY<span class="accent">VERSE</span></strong>
                <div class="muted" style="font-size:12px;margin-top:6px;line-height:1.6;">
                    <?= STORE_ADDRESS ?><br>
                    <?= STORE_PHONE ?> &middot; <?= STORE_EMAIL ?>
                </div>
            </div>
            <div class="inv-to">
                <h3>INVOICE</h3>
                <div class="muted" style="font-size:12px;"><?= e($trx['invoice_no']) ?></div>
                <span class="invoice-status status-<?= e($trx['status']) ?>"><?= e(ucfirst($trx['status'])) ?></span>
            </div>
        </div>

        <div class="invoice-meta">
            <div>
                <strong>Tanggal</strong>
                <?= tgl_indo($trx['created_at']) ?>
            </div>
            <div>
                <strong>Pelanggan</strong>
                <?= e($trx['recipient_name']) ?><br>
                <span class="muted"><?= e($trx['recipient_phone']) ?></span>
            </div>
            <div>
                <strong>Alamat Kirim</strong>
                <?= e($trx['shipping_address']) ?>
            </div>
        </div>

        <table class="invoice-table">
            <thead>
                <tr>
                    <th>#</th>
                    <th>Produk</th>
                    <th class="num">Harga</th>
                    <th class="num">Qty</th>
                    <th class="num">Subtotal</th>
                </tr>
            </thead>
            <tbody>
                <?php $no=1; foreach ($items as $it): ?>
                    <tr>
                        <td><?= $no++ ?></td>
                        <td><?= e($it['product_name']) ?></td>
                        <td class="num"><?= rupiah($it['price']) ?></td>
                        <td class="num"><?= $it['quantity'] ?></td>
                        <td class="num"><?= rupiah($it['subtotal']) ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>

        <div class="invoice-totals">
            <div class="row">
                <span>Subtotal Produk</span>
                <span><?= rupiah($trx['total']) ?></span>
            </div>
            <?php if (!empty($trx['voucher_code']) && (float)$trx['voucher_discount'] > 0): ?>
                <div class="row" style="color:#2E7D32;">
                    <span><i class="fas fa-ticket-alt"></i> Voucher "<?= e($trx['voucher_code']) ?>"</span>
                    <span>&minus; <?= rupiah($trx['voucher_discount']) ?></span>
                </div>
            <?php endif; ?>
            <?php if ((int)$trx['points_used'] > 0): ?>
                <div class="row" style="color:#FF8F00;">
                    <span><i class="fas fa-coins"></i> Koin Dipakai (<?= number_format((int)$trx['points_used']) ?>)</span>
                    <span>&minus; <?= rupiah($trx['points_discount']) ?></span>
                </div>
            <?php endif; ?>
            <div class="row">
                <span>Ongkos Kirim (<?= e($trx['shipping_method']) ?>)</span>
                <span><?= rupiah($trx['shipping_cost']) ?></span>
            </div>
            <div class="row grand">
                <span>TOTAL</span>
                <span class="accent"><?= rupiah($trx['grand_total']) ?></span>
            </div>
            <?php if ((int)$trx['points_earned'] > 0): ?>
                <div class="row" style="color:#FF8F00;background:#FFF8E1;padding:8px 12px;border-radius:6px;margin-top:8px;">
                    <span><i class="fas fa-gift"></i> Koin yang Didapat</span>
                    <strong>+ <?= number_format((int)$trx['points_earned']) ?> koin</strong>
                </div>
            <?php endif; ?>
        </div>

        <div style="margin-top:24px;padding:14px;background:var(--bg-alt);border-radius:8px;font-size:13px;">
            <strong><i class="fas fa-credit-card accent"></i> Metode Pembayaran:</strong><br>
            <?= e($trx['payment_method']) ?>
        </div>

        <div class="invoice-footer">
            <p>Terima kasih telah berbelanja di ANOHOBBY!</p>
            <p class="muted">Invoice ini sah dan diproses oleh sistem. Simpan sebagai bukti pembayaran.</p>
            <p class="muted" style="margin-top:8px;font-size:11px;">&mdash; ANOHOBBY &middot; Toko Hobby Online Indonesia &mdash;</p>
        </div>
    </div>
</div>

<?php include __DIR__ . '/includes/footer.php'; ?>
