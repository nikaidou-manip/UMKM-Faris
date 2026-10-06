<?php
require_once __DIR__ . '/includes/functions.php';
require_login();
global $pdo;

$user_id = $_SESSION['user_id'];
$stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
$stmt->execute([$user_id]);
$user = $stmt->fetch();

// ===== HANDLE FOTO PROFIL (upload dari galeri/device, atau hapus) =====
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['avatar_action'])) {
    $avatar_action = $_POST['avatar_action'];

    if ($avatar_action === 'upload' && isset($_FILES['avatar']) && $_FILES['avatar']['error'] !== UPLOAD_ERR_NO_FILE) {
        $file = $_FILES['avatar'];
        $allowed_mimes = ['image/jpeg' => 'jpg', 'image/png' => 'png', 'image/webp' => 'webp', 'image/gif' => 'gif'];
        $max_size = 2 * 1024 * 1024; // maks 2MB

        if ($file['error'] !== UPLOAD_ERR_OK) {
            set_flash('error', 'Upload foto gagal. Silakan coba lagi.');
        } elseif ($file['size'] > $max_size) {
            set_flash('error', 'Ukuran foto maksimal 2MB.');
        } else {
            // Verifikasi file benar-benar gambar (bukan cuma ekstensinya)
            $info = @getimagesize($file['tmp_name']);
            $mime = $info['mime'] ?? null;
            if (!$info || !isset($allowed_mimes[$mime])) {
                set_flash('error', 'Format foto tidak didukung. Gunakan JPG, PNG, WEBP, atau GIF.');
            } else {
                $ext = $allowed_mimes[$mime];
                $filename = 'avatar_' . $user_id . '_' . time() . '_' . bin2hex(random_bytes(3)) . '.' . $ext;
                $dest_dir = uploads_path('avatars');
                if (!is_dir($dest_dir)) @mkdir($dest_dir, 0755, true);
                $dest_path = $dest_dir . '/' . $filename;

                if (move_uploaded_file($file['tmp_name'], $dest_path)) {
                    // Hapus file foto lama supaya folder upload tidak menumpuk
                    if (!empty($user['avatar'])) {
                        $old_path = uploads_path($user['avatar']);
                        if (is_file($old_path)) @unlink($old_path);
                    }
                    $new_avatar = 'avatars/' . $filename;
                    $stmt = $pdo->prepare("UPDATE users SET avatar = ? WHERE id = ?");
                    $stmt->execute([$new_avatar, $user_id]);
                    $_SESSION['avatar'] = $new_avatar;
                    set_flash('success', 'Foto profil berhasil diperbarui.');
                } else {
                    set_flash('error', 'Gagal menyimpan foto. Coba lagi.');
                }
            }
        }
    } elseif ($avatar_action === 'remove') {
        if (!empty($user['avatar'])) {
            $old_path = uploads_path($user['avatar']);
            if (is_file($old_path)) @unlink($old_path);
        }
        $stmt = $pdo->prepare("UPDATE users SET avatar = NULL WHERE id = ?");
        $stmt->execute([$user_id]);
        unset($_SESSION['avatar']);
        set_flash('success', 'Foto profil dihapus.');
    }

    header('Location: ' . url('profile.php?tab=' . ($_GET['tab'] ?? 'profile')));
    exit;
}

// Hitung statistik customer
$stmt = $pdo->prepare("SELECT COUNT(*) AS cnt, COALESCE(SUM(grand_total),0) AS total FROM transactions WHERE user_id = ?");
$stmt->execute([$user_id]);
$stat = $stmt->fetch();

$stmt = $pdo->prepare("SELECT COUNT(*) FROM transactions WHERE user_id = ? AND status='completed'");
$stmt->execute([$user_id]);
$completed = $stmt->fetchColumn();

$tab = $_GET['tab'] ?? 'profile';

// ===== HANDLE EDIT PROFILE (diproses di sini, SEBELUM header.php, supaya header('Location') tidak error) =====
$errors = [];
$form = [
    'full_name' => $user['full_name'],
    'username' => $user['username'],
    'email' => $user['email'],
    'phone' => $user['phone'],
    'address' => $user['address'],
];

if ($_SERVER['REQUEST_METHOD'] === 'POST' && !isset($_POST['avatar_action'])) {
    $form['full_name'] = trim($_POST['full_name'] ?? '');
    $form['username']  = trim($_POST['username'] ?? '');
    $form['email']     = trim($_POST['email'] ?? '');
    $form['phone']     = trim($_POST['phone'] ?? '');
    $form['address']   = trim($_POST['address'] ?? '');
    $new_pass = $_POST['new_password'] ?? '';
    $confirm_pass = $_POST['confirm_password'] ?? '';

    if ($form['full_name']==='') $errors[] = 'Nama lengkap wajib diisi.';
    if ($form['username']==='') $errors[] = 'Username wajib diisi.';
    else {
        $stmt = $pdo->prepare("SELECT id FROM users WHERE username = ? AND id <> ?");
        $stmt->execute([$form['username'], $user_id]);
        if ($stmt->fetch()) $errors[] = 'Username sudah dipakai orang lain.';
    }
    if ($form['email']==='' || !filter_var($form['email'], FILTER_VALIDATE_EMAIL)) $errors[] = 'Email tidak valid.';
    else {
        $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ? AND id <> ?");
        $stmt->execute([$form['email'], $user_id]);
        if ($stmt->fetch()) $errors[] = 'Email sudah dipakai akun lain.';
    }
    if ($new_pass !== '' && strlen($new_pass) < 6) $errors[] = 'Password baru min. 6 karakter.';
    if ($new_pass !== '' && $new_pass !== $confirm_pass) $errors[] = 'Konfirmasi password tidak cocok.';

    if (empty($errors)) {
        if ($new_pass) {
            $hash = password_hash($new_pass, PASSWORD_DEFAULT);
            $stmt = $pdo->prepare("UPDATE users SET full_name=?, username=?, email=?, phone=?, address=?, password=? WHERE id=?");
            $stmt->execute([$form['full_name'], $form['username'], $form['email'], $form['phone'], $form['address'], $hash, $user_id]);
        } else {
            $stmt = $pdo->prepare("UPDATE users SET full_name=?, username=?, email=?, phone=?, address=? WHERE id=?");
            $stmt->execute([$form['full_name'], $form['username'], $form['email'], $form['phone'], $form['address'], $user_id]);
        }
        // Update session
        $_SESSION['full_name'] = $form['full_name'];
        $_SESSION['username'] = $form['username'];
        set_flash('success', 'Profile berhasil diperbarui.');
        header('Location: ' . url('profile.php?tab=profile'));
        exit;
    }
}

$page_title = 'Profile';
include __DIR__ . '/includes/header.php';
?>
<div class="container" style="margin-top:16px;">
    <div class="breadcrumb">
        <a href="<?= url('index.php') ?>">Beranda</a>
        <span class="sep">/</span>
        <span>Profile Saya</span>
    </div>
</div>

<section class="profile-wrap">
    <!-- SIDEBAR -->
    <aside class="profile-sidebar">
        <div class="profile-avatar-wrap">
            <?php $avatar_src = user_avatar_url($user['avatar'] ?? null); ?>
            <?php if ($avatar_src): ?>
                <img src="<?= e($avatar_src) ?>" alt="Foto Profil" class="profile-avatar profile-avatar-img">
            <?php else: ?>
                <div class="profile-avatar"><?= strtoupper(substr($user['full_name'],0,1)) ?></div>
            <?php endif; ?>

            <form action="<?= url('profile.php?tab=' . e($tab)) ?>" method="post" enctype="multipart/form-data" id="avatarForm">
                <input type="hidden" name="avatar_action" value="upload">
                <label for="avatarInput" class="profile-avatar-edit-btn" title="Ganti foto profil">
                    <i class="fas fa-camera"></i>
                </label>
                <input type="file" name="avatar" id="avatarInput" accept="image/jpeg,image/png,image/webp,image/gif" style="display:none;" onchange="document.getElementById('avatarForm').submit()">
            </form>
        </div>
        <?php if ($avatar_src): ?>
            <form action="<?= url('profile.php?tab=' . e($tab)) ?>" method="post" style="text-align:center;margin-bottom:6px;">
                <input type="hidden" name="avatar_action" value="remove">
                <button type="submit" class="avatar-remove-link" data-confirm="Hapus foto profil ini?"><i class="fas fa-trash-alt"></i> Hapus Foto</button>
            </form>
        <?php else: ?>
            <div style="text-align:center;margin-bottom:6px;font-size:11px;color:var(--text-muted);">
                <i class="fas fa-image"></i> Belum ada foto profil
            </div>
        <?php endif; ?>
        <div class="profile-name"><?= e($user['full_name']) ?></div>
        <div class="profile-email"><?= e($user['email']) ?></div>
        <div style="text-align:center;margin-bottom:16px;">
            <span class="invoice-status status-<?= $user['role']==='admin'?'paid':'shipped' ?>"><?= e(ucfirst($user['role'])) ?></span>
        </div>

        <!-- Saldo Koin AnoHobby -->
        <div class="profile-points-card">
            <div class="ppc-header">
                <i class="fas fa-coins"></i>
                <span>Koin AnoHobby</span>
            </div>
            <div class="ppc-balance"><?= number_format((int)$user['points']) ?></div>
            <div class="ppc-equiv">≈ <?= rupiah((int)$user['points']) ?></div>
            <a href="<?= url('my_vouchers.php') ?>" class="ppc-link"><i class="fas fa-ticket-alt"></i> Lihat Voucher Saya</a>
        </div>

        <nav class="profile-nav">
            <a href="<?= url('profile.php?tab=profile') ?>" class="<?= $tab==='profile'?'active':'' ?>"><i class="fas fa-id-card"></i> Edit Profile</a>
            <a href="<?= url('profile.php?tab=stats') ?>" class="<?= $tab==='stats'?'active':'' ?>"><i class="fas fa-chart-line"></i> Statistik</a>
            <a href="<?= url('my_vouchers.php') ?>"><i class="fas fa-ticket-alt"></i> Voucher Saya</a>
            <a href="<?= url('transaction_history.php') ?>"><i class="fas fa-receipt"></i> Riwayat Transaksi</a>
            <a href="<?= url('cart.php') ?>"><i class="fas fa-shopping-bag"></i> Keranjang Saya</a>
            <?php if (is_admin()): ?>
                <a href="<?= url('admin/dashboard.php') ?>"><i class="fas fa-tachometer-alt"></i> Dashboard Admin</a>
            <?php endif; ?>
            <a href="<?= url('logout.php') ?>" class="text-danger"><i class="fas fa-sign-out-alt"></i> Logout</a>
        </nav>
    </aside>

    <!-- CONTENT -->
    <div class="profile-content">
        <?php if ($tab === 'stats'): ?>
            <h2><i class="fas fa-chart-line"></i> Statistik Belanja</h2>
            <div class="stat-grid" style="display:grid;grid-template-columns:repeat(auto-fit,minmax(180px,1fr));gap:14px;">
                <div style="background:var(--primary-light);padding:18px;border-radius:12px;">
                    <div style="font-size:11px;color:var(--text-muted);text-transform:uppercase;font-weight:700;">Total Transaksi</div>
                    <div style="font-size:26px;font-weight:800;color:var(--primary);"><?= $stat['cnt'] ?></div>
                </div>
                <div style="background:#E8F5E9;padding:18px;border-radius:12px;">
                    <div style="font-size:11px;color:var(--text-muted);text-transform:uppercase;font-weight:700;">Selesai</div>
                    <div style="font-size:26px;font-weight:800;color:var(--green);"><?= $completed ?></div>
                </div>
                <div style="background:#FFF3E0;padding:18px;border-radius:12px;">
                    <div style="font-size:11px;color:var(--text-muted);text-transform:uppercase;font-weight:700;">Total Belanja</div>
                    <div style="font-size:22px;font-weight:800;color:#EF6C00;"><?= rupiah($stat['total']) ?></div>
                </div>
            </div>

            <h2 style="margin-top:24px;"><i class="fas fa-history"></i> Transaksi Terbaru</h2>
            <?php
                $stmt = $pdo->prepare("SELECT * FROM transactions WHERE user_id = ? ORDER BY created_at DESC LIMIT 5");
                $stmt->execute([$user_id]);
                $recent = $stmt->fetchAll();
            ?>
            <?php if (empty($recent)): ?>
                <p class="muted">Belum ada transaksi.</p>
            <?php else: ?>
                <table class="data-table" style="margin-top:12px;">
                    <thead>
                        <tr><th>Invoice</th><th>Tanggal</th><th>Total</th><th>Status</th><th>Aksi</th></tr>
                    </thead>
                    <tbody>
                        <?php foreach ($recent as $t): ?>
                            <tr>
                                <td><strong><?= e($t['invoice_no']) ?></strong></td>
                                <td><?= tgl_indo($t['created_at']) ?></td>
                                <td class="accent" style="font-weight:700;"><?= rupiah($t['grand_total']) ?></td>
                                <td><span class="invoice-status status-<?= e($t['status']) ?>"><?= e(ucfirst($t['status'])) ?></span></td>
                                <td><a href="<?= url('invoice.php?id=' . $t['id']) ?>" class="btn btn-outline btn-sm"><i class="fas fa-eye"></i> Lihat</a></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php endif; ?>

        <?php else: ?>
            <h2><i class="fas fa-id-card"></i> Edit Profile</h2>
            <?php
                $f = get_flash(); if (!empty($f)) foreach ($f as $m): ?>
                    <div class="flash flash-<?= e($m['type']) ?>"><i class="fas fa-info-circle"></i> <?= e($m['msg']) ?></div>
                <?php endforeach;

                foreach ($errors as $er) echo "<div class='flash flash-error'><i class='fas fa-exclamation-circle'></i> " . e($er) . "</div>";
            ?>
            <form method="post" action="">
                <div class="form-row">
                    <div class="form-group">
                        <label class="form-label">Nama Lengkap <span class="req">*</span></label>
                        <input type="text" name="full_name" class="form-control" value="<?= e($form['full_name']) ?>" required>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Username <span class="req">*</span></label>
                        <input type="text" name="username" class="form-control" value="<?= e($form['username']) ?>" required>
                    </div>
                </div>
                <div class="form-group">
                    <label class="form-label">Email <span class="req">*</span></label>
                    <input type="email" name="email" class="form-control" value="<?= e($form['email']) ?>" required>
                </div>
                <div class="form-row">
                    <div class="form-group">
                        <label class="form-label">No. HP</label>
                        <input type="text" name="phone" class="form-control" value="<?= e($form['phone']) ?>">
                    </div>
                    <div class="form-group">
                        <label class="form-label">Alamat</label>
                        <input type="text" name="address" class="form-control" value="<?= e($form['address']) ?>">
                    </div>
                </div>
                <hr style="border:0;border-top:1px solid var(--border-light);margin:18px 0;">
                <h3 style="font-size:14px;font-weight:700;margin-bottom:10px;"><i class="fas fa-lock accent"></i> Ganti Password</h3>
                <p class="form-hint" style="margin-bottom:14px;">Kosongkan jika tidak ingin mengubah password.</p>
                <div class="form-row">
                    <div class="form-group">
                        <label class="form-label">Password Baru</label>
                        <input type="password" name="new_password" class="form-control" placeholder="Min. 6 karakter">
                    </div>
                    <div class="form-group">
                        <label class="form-label">Konfirmasi Password</label>
                        <input type="password" name="confirm_password" class="form-control">
                    </div>
                </div>
                <button type="submit" class="btn btn-primary btn-lg"><i class="fas fa-save"></i> Simpan Perubahan</button>
            </form>
        <?php endif; ?>
    </div>
</section>

<?php include __DIR__ . '/includes/footer.php'; ?>
