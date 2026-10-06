<?php
require_once __DIR__ . '/includes/functions.php';

// Hapus session
$_SESSION = [];
if (ini_get('session.use_cookies')) {
    $p = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000, $p['path'], $p['domain'], $p['secure'], $p['httponly']);
}
session_destroy();

// Start fresh session untuk flash message
session_start();
set_flash('success', 'Anda telah berhasil logout. Sampai jumpa kembali!');

header('Location: ' . url('login.php'));
exit;
