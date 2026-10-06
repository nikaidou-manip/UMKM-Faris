<?php
require_once __DIR__ . '/../includes/functions.php';
require_admin();
global $pdo;

// ===== HANDLE POST (tambah/edit) =====
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id              = (int)($_POST['id'] ?? 0);
    $code            = strtoupper(trim($_POST['code'] ?? ''));
    $description     = trim($_POST['description'] ?? '');
    $discount_type   = $_POST['discount_type'] ?? 'fixed';
    $discount_value  = (float)str_replace(['Rp ', '.', ','], ['', '', '.'], $_POST['discount_value'] ?? '0');
    $min_purchase    = (float)str_replace(['Rp ', '.', ','], ['', '', '.'], $_POST['min_purchase'] ?? '0');
    $max_discount    = trim($_POST['max_discount'] ?? '');
    $max_discount    = $max_discount !== '' ? (float)str_replace(['Rp ', '.', ','], ['', '', '.'], $max_discount) : null;
    $quota           = trim($_POST['quota'] ?? '');
    $quota           = $quota !== '' ? (int)$quota : null;
    $starts_at       = trim($_POST['starts_at'] ?? '');
    $ends_at         = trim($_POST['ends_at'] ?? '');
    $is_active       = isset($_POST['is_active']) ? 1 : 0;

    // ===== V2 fields: claimable settings =====
    $claimable             = isset($_POST['claimable']) ? 1 : 0;
    $claim_quota_input     = trim($_POST['claim_quota'] ?? '');
    $claim_quota_v         = $claim_quota_input !== '' ? (int)$claim_quota_input : null;
    $claim_starts_at_input = trim($_POST['claim_starts_at'] ?? '');
    $claim_ends_at_input   = trim($_POST['claim_ends_at'] ?? '');
    $voucher_validity_days = max(1, (int)($_POST['voucher_validity_days'] ?? 30));

    if ($code === '' || $discount_value <= 0) {
        set_flash('error', 'Kode voucher & nilai diskon wajib diisi dengan benar.');
    } elseif (!in_array($discount_type, ['fixed', 'percent'])) {
        set_flash('error', 'Jenis diskon tidak valid.');
    } elseif ($discount_type === 'percent' && $discount_value > 100) {
        set_flash('error', 'Nilai diskon persen tidak boleh lebih dari 100%.');
    } else {
        $starts_at_db = $starts_at ? date('Y-m-d H:i:s', strtotime($starts_at)) : null;
        $ends_at_db   = $ends_at ? date('Y-m-d H:i:s', strtotime($ends_at)) : null;
        $claim_starts_db = $claim_starts_at_input ? date('Y-m-d H:i:s', strtotime($claim_starts_at_input)) : null;
        $claim_ends_db   = $claim_ends_at_input ? date('Y-m-d H:i:s', strtotime($claim_ends_at_input)) : null;

        if ($id > 0) {
            // Edit
            $stmt = $pdo->prepare("UPDATE vouchers SET code=?, description=?, discount_type=?, discount_value=?, min_purchase=?, max_discount=?, quota=?, starts_at=?, ends_at=?, is_active=?, claimable=?, claim_quota=?, claim_starts_at=?, claim_ends_at=?, voucher_validity_days=? WHERE id=?");
            $stmt->execute([$code, $description, $discount_type, $discount_value, $min_purchase, $max_discount, $quota, $starts_at_db, $ends_at_db, $is_active, $claimable, $claim_quota_v, $claim_starts_db, $claim_ends_db, $voucher_validity_days, $id]);
            set_flash('success', "Voucher \"{$code}\" berhasil diperbarui.");
        } else {
            // Cek kode unik
            $check = $pdo->prepare("SELECT id FROM vouchers WHERE code = ?");
            $check->execute([$code]);
            if ($check->fetch()) {
                set_flash('error', "Kode voucher \"{$code}\" sudah digunakan.");
            } else {
                $stmt = $pdo->prepare("INSERT INTO vouchers (code, description, discount_type, discount_value, min_purchase, max_discount, quota, used_count, starts_at, ends_at, is_active, claimable, claim_quota, claimed_count, claim_starts_at, claim_ends_at, voucher_validity_days) VALUES (?, ?, ?, ?, ?, ?, ?, 0, ?, ?, ?, ?, ?, 0, ?, ?, ?)");
                $stmt->execute([$code, $description, $discount_type, $discount_value, $min_purchase, $max_discount, $quota, $starts_at_db, $ends_at_db, $is_active, $claimable, $claim_quota_v, $claim_starts_db, $claim_ends_db, $voucher_validity_days]);
                set_flash('success', "Voucher \"{$code}\" berhasil ditambahkan.");
            }
        }
        header('Location: ' . url('admin/vouchers.php'));
        exit;
    }
}

// ===== HANDLE DELETE =====
if (isset($_GET['delete'])) {
    $del_id = (int)$_GET['delete'];
    $stmt = $pdo->prepare("SELECT code FROM vouchers WHERE id = ?");
    $stmt->execute([$del_id]);
    $code = $stmt->fetchColumn();
    if ($code) {
        $pdo->prepare("DELETE FROM vouchers WHERE id = ?")->execute([$del_id]);
        set_flash('success', "Voucher \"{$code}\" telah dihapus.");
    }
    header('Location: ' . url('admin/vouchers.php'));
    exit;
}

// ===== HANDLE TOGGLE ACTIVE (ajax-friendly) =====
if (isset($_GET['toggle'])) {
    $toggle_id = (int)$_GET['toggle'];
    $pdo->prepare("UPDATE vouchers SET is_active = 1 - is_active WHERE id = ?")->execute([$toggle_id]);
    set_flash('success', 'Status voucher diubah.');
    header('Location: ' . url('admin/vouchers.php'));
    exit;
}

require_once __DIR__ . '/../includes/admin_header.php';

$action  = $_GET['action'] ?? 'list';
$edit_id = (int)($_GET['id'] ?? 0);

// ===== LOAD EDIT DATA =====
$edit_data = null;
if ($action === 'edit' && $edit_id) {
    $edit_data = get_voucher($pdo, $edit_id);
    if (!$edit_data) {
        set_flash('error', 'Voucher tidak ditemukan.');
        header('Location: ' . url('admin/vouchers.php'));
        exit;
    }
}

// ===== LOAD LIST =====
$vouchers = get_all_vouchers($pdo);
$page_title = 'Data Voucher';
?>

<?php if ($action === 'add' || $action === 'edit'): ?>
<!-- ============ FORM TAMBAH/EDIT VOUCHER ============ -->
<div class="panel" style="max-width:780px;">
    <div class="panel-header">
        <h2><i class="fas fa-<?= $action==='edit'?'edit':'plus' ?>"></i> <?= $action==='edit'?'Edit Voucher':'Tambah Voucher Baru' ?></h2>
        <a href="<?= url('admin/vouchers.php') ?>" class="btn btn-outline btn-sm"><i class="fas fa-arrow-left"></i> Kembali</a>
    </div>
    <form method="post" action="<?= url('admin/vouchers.php') ?>">
        <input type="hidden" name="id" value="<?= $edit_data['id'] ?? 0 ?>">
        <div class="form-row">
            <div class="form-group">
                <label class="form-label">Kode Voucher <span class="req">*</span></label>
                <input type="text" name="code" class="form-control" value="<?= e($edit_data['code'] ?? '') ?>" placeholder="WELCOME10" required style="text-transform:uppercase;" autofocus>
                <div class="form-hint">Kode unik. Otomatis diubah jadi huruf kapital. Contoh: HEMAT50K</div>
            </div>
            <div class="form-group">
                <label class="form-label">Status</label>
                <select name="is_active" class="form-control">
                    <option value="1" <?= (($edit_data['is_active'] ?? 1)==1)?'selected':'' ?>>Aktif</option>
                    <option value="0" <?= (($edit_data['is_active'] ?? 1)==0)?'selected':'' ?>>Nonaktif</option>
                </select>
            </div>
        </div>
        <div class="form-group">
            <label class="form-label">Deskripsi</label>
            <input type="text" name="description" class="form-control" value="<?= e($edit_data['description'] ?? '') ?>" placeholder="Diskon 10% untuk member baru">
        </div>
        <div class="form-row">
            <div class="form-group">
                <label class="form-label">Jenis Diskon <span class="req">*</span></label>
                <select name="discount_type" id="discount_type" class="form-control" onchange="toggleDiscountHint()">
                    <option value="fixed" <?= ($edit_data['discount_type'] ?? 'fixed')==='fixed'?'selected':'' ?>>Potongan Tetap (Rp)</option>
                    <option value="percent" <?= ($edit_data['discount_type'] ?? 'fixed')==='percent'?'selected':'' ?>>Persen (%)</option>
                </select>
            </div>
            <div class="form-group">
                <label class="form-label" id="discount_value_label">Nilai Diskon <span class="req">*</span></label>
                <input type="text" name="discount_value" id="discount_value" class="form-control" value="<?= isset($edit_data['discount_value']) ? ($edit_data['discount_type']==='percent' ? $edit_data['discount_value'] : rupiah($edit_data['discount_value'])) : '' ?>" placeholder="50000 atau 10" required>
                <div class="form-hint" id="discount_value_hint">Masukkan nominal Rp (contoh: 50000) atau persen (contoh: 10)</div>
            </div>
        </div>
        <div class="form-row">
            <div class="form-group">
                <label class="form-label">Minimal Belanja (Rp)</label>
                <input type="text" name="min_purchase" class="form-control" value="<?= isset($edit_data['min_purchase']) ? rupiah($edit_data['min_purchase']) : 'Rp 0' ?>" placeholder="Rp 0">
                <div class="form-hint">Subtotal minimal agar voucher bisa dipakai. 0 = tanpa minimum.</div>
            </div>
            <div class="form-group">
                <label class="form-label">Maks. Diskon (Rp) - hanya untuk persen</label>
                <input type="text" name="max_discount" class="form-control" value="<?= !empty($edit_data['max_discount']) ? rupiah($edit_data['max_discount']) : '' ?>" placeholder="Kosongkan jika tanpa batas">
                <div class="form-hint">Hanya berlaku jika jenis diskon persen. Kosongkan jika tanpa batas.</div>
            </div>
        </div>
        <div class="form-row">
            <div class="form-group">
                <label class="form-label">Kuota Pemakaian</label>
                <input type="number" name="quota" class="form-control" value="<?= e($edit_data['quota'] ?? '') ?>" min="1" placeholder="Kosongkan jika tanpa batas">
                <div class="form-hint">Maksimal berapa kali voucher bisa dipakai. Kosongkan untuk tanpa batas.</div>
            </div>
            <div class="form-group">
                <label class="form-label">Sudah Dipakai</label>
                <input type="text" class="form-control" value="<?= (int)($edit_data['used_count'] ?? 0) ?>x" readonly>
                <div class="form-hint">Otomatis terupdate saat transaksi pakai voucher.</div>
            </div>
        </div>
        <div class="form-row">
            <div class="form-group">
                <label class="form-label">Mulai Berlaku</label>
                <input type="datetime-local" name="starts_at" class="form-control" value="<?= !empty($edit_data['starts_at']) ? date('Y-m-d\TH:i', strtotime($edit_data['starts_at'])) : '' ?>">
                <div class="form-hint">Kosongkan = langsung berlaku.</div>
            </div>
            <div class="form-group">
                <label class="form-label">Berakhir Pada</label>
                <input type="datetime-local" name="ends_at" class="form-control" value="<?= !empty($edit_data['ends_at']) ? date('Y-m-d\TH:i', strtotime($edit_data['ends_at'])) : '' ?>">
                <div class="form-hint">Kosongkan = tidak ada batas waktu.</div>
            </div>
        </div>

        <!-- ===== V2: Klaim Voucher (Shopee-style) ===== -->
        <h3 style="font-size:16px;font-weight:700;margin:18px 0 14px;padding-bottom:10px;border-bottom:1px solid var(--border-light);">
            <i class="fas fa-hand-holding accent"></i> Pengaturan Klaim Voucher (Item-based)
        </h3>
        <div class="form-row">
            <div class="form-group" style="display:flex;align-items:center;gap:10px;padding:10px;border:1px solid var(--border);border-radius:8px;">
                <input type="checkbox" name="claimable" id="claimable_chk" value="1" <?= (($edit_data['claimable'] ?? 0)==1)?'checked':'' ?> onchange="toggleClaimSection()">
                <div>
                    <strong style="display:block;">Voucher Bisa Diklaim sebagai Item</strong>
                    <small class="text-muted">Centang agar voucher tampil di halaman "Voucher Saya" untuk user klaim. Mirip Shopee voucher.</small>
                </div>
            </div>
            <div class="form-group">
                <label class="form-label">Masa Berlaku Setelah Klaim (hari)</label>
                <input type="number" name="voucher_validity_days" class="form-control" value="<?= (int)($edit_data['voucher_validity_days'] ?? 30) ?>" min="1">
                <div class="form-hint">Berapa hari voucher berlaku setelah user klaim. Default 30 hari.</div>
            </div>
        </div>
        <div class="form-row" id="claim_section" style="<?= (($edit_data['claimable'] ?? 0)==1)?'':'display:none;' ?>">
            <div class="form-group">
                <label class="form-label">Kuota Klaim</label>
                <input type="number" name="claim_quota" class="form-control" value="<?= e($edit_data['claim_quota'] ?? '') ?>" min="1" placeholder="Kosongkan jika tanpa batas">
                <div class="form-hint">Maksimal berapa user boleh klaim voucher ini.</div>
            </div>
            <div class="form-group">
                <label class="form-label">Sudah Diklaim</label>
                <input type="text" class="form-control" value="<?= (int)($edit_data['claimed_count'] ?? 0) ?>x" readonly>
            </div>
        </div>
        <div class="form-row" id="claim_period_section" style="<?= (($edit_data['claimable'] ?? 0)==1)?'':'display:none;' ?>">
            <div class="form-group">
                <label class="form-label">Periode Klaim - Mulai</label>
                <input type="datetime-local" name="claim_starts_at" class="form-control" value="<?= !empty($edit_data['claim_starts_at']) ? date('Y-m-d\TH:i', strtotime($edit_data['claim_starts_at'])) : '' ?>">
                <div class="form-hint">Kosongkan = langsung bisa diklaim.</div>
            </div>
            <div class="form-group">
                <label class="form-label">Periode Klaim - Berakhir</label>
                <input type="datetime-local" name="claim_ends_at" class="form-control" value="<?= !empty($edit_data['claim_ends_at']) ? date('Y-m-d\TH:i', strtotime($edit_data['claim_ends_at'])) : '' ?>">
                <div class="form-hint">Kosongkan = tidak ada batas waktu klaim.</div>
            </div>
        </div>

        <button type="submit" class="btn btn-primary btn-lg"><i class="fas fa-save"></i> <?= $action==='edit'?'Update':'Tambah' ?> Voucher</button>
        <a href="<?= url('admin/vouchers.php') ?>" class="btn btn-outline btn-lg">Batal</a>
    </form>
</div>

<script>
function toggleDiscountHint() {
    var t = document.getElementById('discount_type').value;
    var label = document.getElementById('discount_value_label');
    var hint  = document.getElementById('discount_value_hint');
    var input = document.getElementById('discount_value');
    if (t === 'percent') {
        label.innerHTML = 'Persentase Diskon (%) <span class="req">*</span>';
        hint.textContent = 'Masukkan angka persen (contoh: 10 untuk 10%)';
        input.setAttribute('placeholder', '10');
    } else {
        label.innerHTML = 'Potongan Tetap (Rp) <span class="req">*</span>';
        hint.textContent = 'Masukkan nominal Rp (contoh: 50000)';
        input.setAttribute('placeholder', '50000');
    }
}
function toggleClaimSection() {
    var chk = document.getElementById('claimable_chk').checked;
    var sections = ['claim_section', 'claim_period_section'];
    sections.forEach(function(id) {
        var el = document.getElementById(id);
        if (el) el.style.display = chk ? '' : 'none';
    });
}
document.addEventListener('DOMContentLoaded', toggleDiscountHint);
</script>

<?php else: ?>
<!-- ============ LIST VOUCHER ============ -->
<div class="panel">
    <div class="panel-header">
        <h2><i class="fas fa-ticket-alt"></i> Tabel Data Voucher (<?= count($vouchers) ?>)</h2>
        <a href="<?= url('admin/vouchers.php?action=add') ?>" class="btn btn-primary"><i class="fas fa-plus"></i> Tambah Voucher</a>
    </div>

    <?php if (empty($vouchers)): ?>
        <div class="empty-state"><i class="fas fa-ticket-alt"></i><h3>Belum ada voucher</h3><p>Tambahkan voucher pertama untuk pelangganmu!</p></div>
    <?php else: ?>
        <div style="overflow-x:auto;">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Kode</th>
                        <th>Deskripsi</th>
                        <th>Diskon</th>
                        <th>Min. Belanja</th>
                        <th>Kuota</th>
                        <th>Periode</th>
                        <th>Status</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($vouchers as $v):
                        $is_expired = !empty($v['ends_at']) && $v['ends_at'] < date('Y-m-d H:i:s');
                        $is_pending = !empty($v['starts_at']) && $v['starts_at'] > date('Y-m-d H:i:s');
                        $status_label = $is_expired ? 'Berakhir' : ($is_pending ? 'Belum mulai' : (((int)$v['is_active']===1) ? 'Aktif' : 'Nonaktif'));
                        $status_color = $is_expired ? 'cancelled' : ($is_pending ? 'pending' : (((int)$v['is_active']===1) ? 'completed' : 'cancelled'));
                    ?>
                        <tr>
                            <td><strong style="font-family:monospace;font-size:14px;"><?= e($v['code']) ?></strong></td>
                            <td style="max-width:200px;"><?= e($v['description'] ?: '-') ?></td>
                            <td>
                                <?php if ($v['discount_type']==='percent'): ?>
                                    <strong><?= (float)$v['discount_value'] ?>%</strong>
                                    <?php if (!empty($v['max_discount'])): ?>
                                        <br><small class="text-muted">max <?= rupiah($v['max_discount']) ?></small>
                                    <?php endif; ?>
                                <?php else: ?>
                                    <strong><?= rupiah($v['discount_value']) ?></strong>
                                <?php endif; ?>
                            </td>
                            <td><?= rupiah($v['min_purchase']) ?></td>
                            <td>
                                <?php if (!empty($v['quota'])): ?>
                                    <?= (int)$v['used_count'] ?> / <?= (int)$v['quota'] ?>
                                <?php else: ?>
                                    <?= (int)$v['used_count'] ?>x &middot; <span class="text-muted">tanpa batas</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <?php if (!empty($v['starts_at'])): ?>
                                    <small>start: <?= date('d M Y H:i', strtotime($v['starts_at'])) ?></small><br>
                                <?php endif; ?>
                                <?php if (!empty($v['ends_at'])): ?>
                                    <small>end: <?= date('d M Y H:i', strtotime($v['ends_at'])) ?></small>
                                <?php else: ?>
                                    <small class="text-muted">tanpa batas</small>
                                <?php endif; ?>
                            </td>
                            <td><span class="status-badge status-<?= e($status_color) ?>"><?= e($status_label) ?></span></td>
                            <td>
                                <div class="table-actions">
                                    <a href="<?= url('admin/vouchers.php?action=edit&id=' . $v['id']) ?>" title="Edit"><i class="fas fa-edit"></i></a>
                                    <a href="<?= url('admin/vouchers.php?toggle=' . $v['id']) ?>" title="Aktif/Nonaktif"><i class="fas fa-power-off"></i></a>
                                    <a href="<?= url('admin/vouchers.php?delete=' . $v['id']) ?>" class="danger" data-confirm="Yakin hapus voucher &quot;<?= e($v['code']) ?>&quot;?"><i class="fas fa-trash"></i></a>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php endif; ?>
</div>
<?php endif; ?>

<?php require_once __DIR__ . '/../includes/admin_footer.php'; ?>
