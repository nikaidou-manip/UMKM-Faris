<?php
require_once __DIR__ . '/../includes/admin_header.php';
global $pdo;

// ===== HANDLE RESET PASSWORD =====
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'reset_password') {
    $uid = (int)($_POST['user_id'] ?? 0);
    $new_pass = $_POST['new_password'] ?? '';
    if (strlen($new_pass) < 6) {
        set_flash('error', 'Password baru minimal 6 karakter.');
    } else {
        $hash = password_hash($new_pass, PASSWORD_DEFAULT);
        $stmt = $pdo->prepare("UPDATE users SET password = ? WHERE id = ? AND role = 'customer'");
        $stmt->execute([$hash, $uid]);
        if ($stmt->rowCount()) {
            $stmt = $pdo->prepare("SELECT username, full_name FROM users WHERE id = ?");
            $stmt->execute([$uid]);
            $u = $stmt->fetch();
            set_flash('success', "Password pelanggan \"{$u['full_name']}\" (@{$u['username']}) berhasil direset.");
        } else {
            set_flash('error', 'Gagal reset password. Pelanggan tidak ditemukan.');
        }
    }
    header('Location: ' . url('admin/customers.php'));
    exit;
}

// ===== HANDLE DELETE CUSTOMER =====
if (isset($_GET['delete'])) {
    $del_id = (int)$_GET['delete'];
    $stmt = $pdo->prepare("SELECT username, full_name FROM users WHERE id = ? AND role='customer'");
    $stmt->execute([$del_id]);
    $u = $stmt->fetch();
    if ($u) {
        $pdo->prepare("DELETE FROM users WHERE id = ?")->execute([$del_id]);
        set_flash('success', "Pelanggan \"{$u['full_name']}\" (@{$u['username']}) telah dihapus.");
    }
    header('Location: ' . url('admin/customers.php'));
    exit;
}

// ===== LIST CUSTOMERS =====
$search = trim($_GET['q'] ?? '');
$sql = "SELECT u.*,
        (SELECT COUNT(*) FROM transactions t WHERE t.user_id = u.id) AS trx_count,
        (SELECT COALESCE(SUM(t.grand_total),0) FROM transactions t WHERE t.user_id = u.id AND t.status <> 'cancelled') AS total_spent
        FROM users u WHERE u.role = 'customer'";
$params = [];
if ($search) {
    $sql .= " AND (u.username LIKE ? OR u.email LIKE ? OR u.full_name LIKE ?)";
    $params[] = "%$search%"; $params[] = "%$search%"; $params[] = "%$search%";
}
$sql .= " ORDER BY u.created_at DESC";
$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$customers = $stmt->fetchAll();

$page_title = 'Data Pelanggan';
?>
<div class="stat-grid">
    <div class="stat-card"><div class="stat-info"><div class="stat-label">Total Pelanggan</div><div class="stat-value"><?= count($customers) ?></div></div><div class="stat-icon orange"><i class="fas fa-users"></i></div></div>
    <div class="stat-card"><div class="stat-info"><div class="stat-label">Pelanggan Aktif</div><div class="stat-value"><?= count(array_filter($customers, fn($c) => $c['trx_count']>0)) ?></div></div><div class="stat-icon green"><i class="fas fa-user-check"></i></div></div>
</div>

<div class="panel">
    <div class="panel-header">
        <h2><i class="fas fa-users"></i> Tabel Data Pelanggan</h2>
    </div>

    <form method="get" action="" class="filter-bar" style="margin-bottom:14px;display:flex;gap:10px;flex-wrap:wrap;">
        <input type="text" name="q" value="<?= e($search) ?>" placeholder="Cari nama / username / email..." class="form-control" style="max-width:300px;">
        <button type="submit" class="btn btn-primary"><i class="fas fa-search"></i> Cari</button>
        <a href="<?= url('admin/customers.php') ?>" class="btn btn-outline"><i class="fas fa-redo"></i></a>
    </form>

    <?php if (empty($customers)): ?>
        <div class="empty-state"><i class="fas fa-users"></i><h3>Belum ada pelanggan</h3><p>Pelanggan yang register akan tampil di sini.</p></div>
    <?php else: ?>
        <div style="overflow-x:auto;">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Nama</th>
                        <th>Kontak</th>
                        <th>Alamat</th>
                        <th>HP</th>
                        <th>Transaksi</th>
                        <th>Total Belanja</th>
                        <th>Koin</th>
                        <th>Bergabung</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($customers as $c): ?>
                        <tr>
                            <td><?= $c['id'] ?></td>
                            <td>
                                <strong><?= e($c['full_name']) ?></strong><br>
                                <small class="text-muted">@<?= e($c['username']) ?></small>
                            </td>
                            <td><?= e($c['email']) ?></td>
                            <td style="max-width:200px;font-size:12px;"><?= e($c['address'] ?: '-') ?></td>
                            <td><?= e($c['phone'] ?: '-') ?></td>
                            <td class="text-center"><strong class="text-primary"><?= $c['trx_count'] ?></strong></td>
                            <td class="text-success" style="font-weight:700;"><?= rupiah($c['total_spent']) ?></td>
                            <td><small style="color:#FF8F00;"><i class="fas fa-coins"></i> <?= number_format((int)$c['points']) ?> koin</small></td>
                            <td><small class="text-muted"><?= date('d M Y', strtotime($c['created_at'])) ?></small></td>
                            <td>
                                <div class="table-actions">
                                    <button onclick="openResetModal(<?= $c['id'] ?>, '<?= e($c['full_name']) ?>', '<?= e($c['username']) ?>')" title="Reset Password"><i class="fas fa-key"></i></button>
                                    <a href="<?= url('admin/customers.php?delete=' . $c['id']) ?>" class="danger" data-confirm="Yakin hapus pelanggan &quot;<?= e($c['full_name']) ?>&quot;? Semua data transaksi terkait juga akan terhapus."><i class="fas fa-trash"></i></a>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php endif; ?>
</div>

<!-- Modal Reset Password -->
<div id="resetModal" style="display:none;position:fixed;inset:0;background:rgba(0,0,0,0.5);z-index:1000;align-items:center;justify-content:center;">
    <div style="background:white;border-radius:12px;padding:24px;max-width:420px;width:90%;box-shadow:0 25px 50px rgba(0,0,0,0.3);">
        <h3 style="font-size:18px;font-weight:700;margin-bottom:6px;"><i class="fas fa-key text-primary"></i> Reset Password Pelanggan</h3>
        <p style="font-size:13px;color:var(--text-muted);margin-bottom:16px;">
            Pelanggan: <strong id="rm_name"></strong> (<span id="rm_username"></span>)
        </p>
        <form method="post" action="">
            <input type="hidden" name="action" value="reset_password">
            <input type="hidden" name="user_id" id="rm_uid" value="">
            <div class="form-group">
                <label class="form-label">Password Baru <span class="req">*</span></label>
                <input type="text" name="new_password" class="form-control" required minlength="6" placeholder="Min. 6 karakter" id="rm_password">
                <div class="form-hint">Password lama akan ditimpa. Pelanggan perlu login dengan password baru ini.</div>
            </div>
            <div style="display:flex;gap:8px;">
                <button type="submit" class="btn btn-primary" style="flex:1;"><i class="fas fa-save"></i> Reset Password</button>
                <button type="button" onclick="closeResetModal()" class="btn btn-outline">Batal</button>
            </div>
        </form>
    </div>
</div>

<script>
function openResetModal(uid, name, username) {
    document.getElementById('rm_uid').value = uid;
    document.getElementById('rm_name').textContent = name;
    document.getElementById('rm_username').textContent = '@' + username;
    document.getElementById('rm_password').value = '';
    document.getElementById('resetModal').style.display = 'flex';
}
function closeResetModal() {
    document.getElementById('resetModal').style.display = 'none';
}
document.getElementById('resetModal').addEventListener('click', function(e) {
    if (e.target === this) closeResetModal();
});
</script>

<?php require_once __DIR__ . '/../includes/admin_footer.php'; ?>
