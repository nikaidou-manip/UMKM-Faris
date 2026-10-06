<?php
require_once __DIR__ . '/includes/functions.php';
global $pdo;

if (is_logged_in()) {
    // Sudah login -> arahkan ke tujuan atau dashboard sesuai role
    $redirect = $_GET['redirect'] ?? (is_admin() ? url('admin/dashboard.php') : url('index.php'));
    header('Location: ' . $redirect);
    exit;
}

$errors = [];
$input = ['login' => ''];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $login = trim($_POST['login'] ?? '');
    $password = $_POST['password'] ?? '';
    $input['login'] = $login;

    if ($login === '' || $password === '') {
        $errors[] = 'Username/email dan password wajib diisi.';
    } else {
        // cari user by username atau email
        $stmt = $pdo->prepare("SELECT * FROM users WHERE username = ? OR email = ? LIMIT 1");
        $stmt->execute([$login, $login]);
        $user = $stmt->fetch();

        if (!$user || !password_verify($password, $user['password'])) {
            $errors[] = 'Username/email atau password salah.';
        } else {
            // sukses login
            $_SESSION['user_id']   = $user['id'];
            $_SESSION['username']  = $user['username'];
            $_SESSION['full_name'] = $user['full_name'];
            $_SESSION['role']      = $user['role'];
            $_SESSION['avatar']    = $user['avatar'] ?? null;

            set_flash('success', "Selamat datang, {$user['full_name']}!");
            $redirect = $_GET['redirect'] ?? ($user['role'] === 'admin' ? url('admin/dashboard.php') : url('index.php'));
            header('Location: ' . $redirect);
            exit;
        }
    }
}

$page_title = 'Login';
$flashes = get_flash();
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - <?= STORE_NAME ?></title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800;900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="<?= asset('css/style.css') ?>">
    <link rel="stylesheet" href="<?= asset('css/shop-ui.css') ?>?v=<?= @filemtime(__DIR__ . '/assets/css/shop-ui.css') ?>">
</head>
<body>
<div class="auth-split-wrap">

    <!-- ===== PANEL BRAND (kiri) ===== -->
    <aside class="auth-brand">
        <div class="ab-logo">
            <img src="<?= asset('img/logo.png') ?>" alt="<?= STORE_NAME ?>">
        </div>
        <div class="ab-body">
            <h1>Dunia hobby kamu,<br><span>satu akun.</span></h1>
            <p>Masuk untuk lanjut belanja modelkit, figure, dan TCG favoritmu, plus nikmati voucher dan koin eksklusif.</p>
            <div class="ab-perks">
                <div><i class="fas fa-bolt"></i> Akses flash sale lebih cepat</div>
                <div><i class="fas fa-ticket-alt"></i> Klaim voucher &amp; kumpulkan koin</div>
                <div><i class="fas fa-receipt"></i> Pantau riwayat pesanan kapan saja</div>
            </div>
        </div>
        <div class="ab-foot">&copy; <?= date('Y') ?> <?= STORE_NAME ?></div>
    </aside>

    <!-- ===== FORM LOGIN (kanan) ===== -->
    <main class="auth-panel">
        <div class="auth-form-card">
            <div class="afc-logo"><img src="<?= asset('img/logo.png') ?>" alt="<?= STORE_NAME ?>"></div>
            <h2>Welcome back!</h2>
            <p class="afc-sub">Masuk untuk lanjut berbelanja hobby kesayanganmu.</p>

            <?php foreach ($errors as $err): ?>
                <div class="flash flash-error"><i class="fas fa-exclamation-circle"></i> <?= e($err) ?></div>
            <?php endforeach; ?>
            <?php foreach ($flashes as $msg): ?>
                <div class="flash flash-<?= e($msg['type']) ?>"><i class="fas fa-info-circle"></i> <?= e($msg['msg']) ?></div>
            <?php endforeach; ?>

            <form method="post" action="">
                <div class="form-group">
                    <label class="form-label" for="login">Username atau Email</label>
                    <div class="input-icon">
                        <i class="fas fa-user ii-left"></i>
                        <input type="text" id="login" name="login" class="form-control" placeholder="Masukkan username atau email" value="<?= e($input['login']) ?>" required autofocus autocomplete="username">
                    </div>
                </div>
                <div class="form-group">
                    <label class="form-label" for="password">Password</label>
                    <div class="input-icon has-toggle">
                        <i class="fas fa-lock ii-left"></i>
                        <input type="password" id="password" name="password" class="form-control" placeholder="Masukkan password" required autocomplete="current-password">
                        <button type="button" class="pw-toggle" id="pwToggle" aria-label="Tampilkan password"><i class="fas fa-eye"></i></button>
                    </div>
                </div>
                <button type="submit" class="btn btn-primary btn-block btn-lg auth-submit"><i class="fas fa-sign-in-alt"></i> Sign In</button>
            </form>

            <div class="auth-divider">Belum punya akun?</div>
            <a href="<?= url('register.php') ?>" class="auth-alt-btn"><i class="fas fa-user-plus"></i> Buat Akun Baru</a>

            <a href="<?= url('index.php') ?>" class="auth-back"><i class="fas fa-arrow-left"></i> Kembali ke Beranda</a>
        </div>
    </main>
</div>

<script>
(function () {
    var btn = document.getElementById('pwToggle');
    var input = document.getElementById('password');
    if (!btn || !input) return;
    btn.addEventListener('click', function () {
        var show = input.type === 'password';
        input.type = show ? 'text' : 'password';
        btn.querySelector('i').className = show ? 'fas fa-eye-slash' : 'fas fa-eye';
        btn.setAttribute('aria-label', show ? 'Sembunyikan password' : 'Tampilkan password');
    });
})();
</script>
</body>
</html>
