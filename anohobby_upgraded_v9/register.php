<?php
require_once __DIR__ . '/includes/functions.php';
global $pdo;

if (is_logged_in()) {
    header('Location: ' . url('index.php'));
    exit;
}

$errors = [];
$input = ['username'=>'','email'=>'','full_name'=>'','phone'=>'','address'=>''];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $input['username']  = trim($_POST['username'] ?? '');
    $input['email']     = trim($_POST['email'] ?? '');
    $input['full_name'] = trim($_POST['full_name'] ?? '');
    $input['phone']     = trim($_POST['phone'] ?? '');
    $input['address']   = trim($_POST['address'] ?? '');
    $password  = $_POST['password'] ?? '';
    $password2 = $_POST['password2'] ?? '';

    // Validasi
    if ($input['username'] === '') $errors[] = 'Username wajib diisi.';
    elseif (!preg_match('/^[a-zA-Z0-9_]{3,30}$/', $input['username'])) $errors[] = 'Username 3-30 karakter, hanya huruf/angka/underscore.';
    else {
        $stmt = $pdo->prepare("SELECT id FROM users WHERE username = ?");
        $stmt->execute([$input['username']]);
        if ($stmt->fetch()) $errors[] = 'Username sudah dipakai.';
    }

    if ($input['email'] === '') $errors[] = 'Email wajib diisi.';
    elseif (!filter_var($input['email'], FILTER_VALIDATE_EMAIL)) $errors[] = 'Format email tidak valid.';
    else {
        $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ?");
        $stmt->execute([$input['email']]);
        if ($stmt->fetch()) $errors[] = 'Email sudah terdaftar.';
    }

    if ($input['full_name'] === '') $errors[] = 'Nama lengkap wajib diisi.';
    if (strlen($password) < 6) $errors[] = 'Password minimal 6 karakter.';
    if ($password !== $password2) $errors[] = 'Konfirmasi password tidak cocok.';

    if (empty($errors)) {
        $hash = password_hash($password, PASSWORD_DEFAULT);
        $stmt = $pdo->prepare("INSERT INTO users (username, email, password, full_name, phone, address, role) VALUES (?, ?, ?, ?, ?, ?, 'customer')");
        $stmt->execute([$input['username'], $input['email'], $hash, $input['full_name'], $input['phone'], $input['address']]);
        $uid = $pdo->lastInsertId();

        // Auto login setelah registrasi
        $_SESSION['user_id']   = $uid;
        $_SESSION['username']  = $input['username'];
        $_SESSION['full_name'] = $input['full_name'];
        $_SESSION['role']      = 'customer';

        set_flash('success', 'Registrasi berhasil! Selamat datang di ' . STORE_NAME . '.');
        header('Location: ' . url('index.php'));
        exit;
    }
}

$page_title = 'Register';
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register - <?= STORE_NAME ?></title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="<?= asset('css/style.css') ?>">
</head>
<body>
<div class="auth-wrap">
    <div class="auth-card" style="max-width:520px;">
        <div class="auth-logo">
            <div class="logo" style="justify-content:center;">
                <img src="<?= asset('img/logo.png') ?>" alt="<?= STORE_NAME ?>" class="logo-img">
            </div>
        </div>
        <h2>Buat Akun Baru</h2>
        <p class="auth-subtitle">Daftar gratis untuk mulai berbelanja modelkit, TCG, dan figure</p>

        <?php if (!empty($errors)): ?>
            <?php foreach ($errors as $err): ?>
                <div class="flash flash-error"><i class="fas fa-exclamation-circle"></i> <?= e($err) ?></div>
            <?php endforeach; ?>
        <?php endif; ?>

        <form method="post" action="">
            <div class="form-row">
                <div class="form-group">
                    <label class="form-label">Username <span class="req">*</span></label>
                    <input type="text" name="username" class="form-control" value="<?= e($input['username']) ?>" required>
                </div>
                <div class="form-group">
                    <label class="form-label">Email <span class="req">*</span></label>
                    <input type="email" name="email" class="form-control" value="<?= e($input['email']) ?>" required>
                </div>
            </div>
            <div class="form-group">
                <label class="form-label">Nama Lengkap <span class="req">*</span></label>
                <input type="text" name="full_name" class="form-control" value="<?= e($input['full_name']) ?>" required>
            </div>
            <div class="form-row">
                <div class="form-group">
                    <label class="form-label">No. HP</label>
                    <input type="text" name="phone" class="form-control" value="<?= e($input['phone']) ?>">
                </div>
                <div class="form-group">
                    <label class="form-label">Alamat</label>
                    <input type="text" name="address" class="form-control" value="<?= e($input['address']) ?>">
                </div>
            </div>
            <div class="form-row">
                <div class="form-group">
                    <label class="form-label">Password <span class="req">*</span></label>
                    <input type="password" name="password" class="form-control" required>
                    <div class="form-hint">Min. 6 karakter</div>
                </div>
                <div class="form-group">
                    <label class="form-label">Konfirmasi Password <span class="req">*</span></label>
                    <input type="password" name="password2" class="form-control" required>
                </div>
            </div>
            <button type="submit" class="btn btn-primary btn-block btn-lg"><i class="fas fa-user-plus"></i> Daftar Sekarang</button>
        </form>

        <div class="auth-footer">
            Sudah punya akun? <a href="<?= url('login.php') ?>">Sign in</a>
        </div>
    </div>
</div>
</body>
</html>
