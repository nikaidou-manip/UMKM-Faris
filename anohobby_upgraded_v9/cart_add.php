<?php
require_once __DIR__ . '/includes/functions.php';
global $pdo;

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ' . url('products.php'));
    exit;
}

$product_id = (int)($_POST['product_id'] ?? 0);
$qty = max(1, (int)($_POST['qty'] ?? 1));

// Validasi produk
$p = get_product($pdo, $product_id);
if (!$p) {
    set_flash('error', 'Produk tidak ditemukan.');
    header('Location: ' . url('products.php'));
    exit;
}

if ($p['stock'] <= 0) {
    set_flash('error', "Maaf, produk \"{$p['name']}\" sedang habis stoknya.");
    header('Location: ' . url('product_detail.php?id=' . $product_id));
    exit;
}

// Batasi qty sesuai stok
if ($qty > $p['stock']) {
    $qty = $p['stock'];
    set_flash('info', "Jumlah melebihi stok. Kami tambahkan {$qty} unit ke keranjang.");
}

// Tambah ke cart session
$cart = get_cart();
if (isset($cart[$product_id])) {
    $cart[$product_id] = min($p['stock'], $cart[$product_id] + $qty);
} else {
    $cart[$product_id] = $qty;
}
set_cart($cart);

set_flash('success', "\"{$p['name']}\" ({$qty}x) ditambahkan ke keranjang.");

// Redirect kembali ke halaman sebelumnya atau ke cart
$redirect = $_POST['redirect'] ?? $_SERVER['HTTP_REFERER'] ?? url('cart.php');
header('Location: ' . $redirect);
exit;
