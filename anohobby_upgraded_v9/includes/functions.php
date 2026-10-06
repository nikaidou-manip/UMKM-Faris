<?php
// ============================================================
// FUNCTIONS LIBRARY
// ============================================================
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../config/database.php';

// ----- Format Rupiah -----
function rupiah($angka) {
    return 'Rp ' . number_format((float)$angka, 0, ',', '.');
}

// ----- Generate slug dari string -----
function slugify($text) {
    $text = strtolower($text);
    $text = preg_replace('/[^a-z0-9]+/', '-', $text);
    return trim($text, '-');
}

// ----- Generate nomor invoice unik -----
function generateInvoiceNo($pdo) {
    $date = date('Ymd');
    $prefix = "INV-$date-";
    // hitung transaksi hari ini
    $stmt = $pdo->prepare("SELECT COUNT(*) AS cnt FROM transactions WHERE DATE(created_at) = CURDATE()");
    $stmt->execute();
    $cnt = (int)$stmt->fetchColumn();
    $seq = str_pad($cnt + 1, 4, '0', STR_PAD_LEFT);
    return $prefix . $seq;
}

// ----- Cek apakah user sudah login -----
function is_logged_in() {
    return isset($_SESSION['user_id']);
}

// ----- Cek apakah user adalah admin -----
function is_admin() {
    return is_logged_in() && $_SESSION['role'] === 'admin';
}

// ----- Require login - kalau belum, redirect ke halaman login -----
function require_login() {
    if (!is_logged_in()) {
        header('Location: ' . url('login.php') . '?redirect=' . urlencode($_SERVER['REQUEST_URI']));
        exit;
    }
}

// ----- Require admin -----
function require_admin() {
    if (!is_admin()) {
        header('Location: ' . url('login.php') . '?redirect=' . urlencode($_SERVER['REQUEST_URI']));
        exit;
    }
}

// ----- Generate URL absolut dari path relatif terhadap root project -----
// Dipakai agar link tetap bekerja walau project ditaruh di subfolder.
function url($path = '') {
    // Deteksi base path dari script yang sedang jalan
    // Asumsikan project root = parent dir dari /config, /includes, /admin
    $scriptDir = str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME']));
    // kalau di dalam /admin/, naik 1 level
    if (preg_match('#/admin$#', $scriptDir)) {
        $base = dirname($scriptDir);
    } else {
        $base = $scriptDir;
    }
    $base = rtrim($base, '/');
    return $base . '/' . ltrim($path, '/');
}

// ----- Asset URL -----
function asset($path) {
    return url('assets/' . ltrim($path, '/'));
}

// ----- URL publik untuk file yang diupload user (avatar, dll) -----
function uploads_url($path) {
    return url('uploads/' . ltrim($path, '/'));
}

// ----- Path fisik di server untuk folder uploads -----
function uploads_path($path = '') {
    return __DIR__ . '/../uploads/' . ltrim($path, '/');
}

// ----- URL avatar user, atau null kalau belum punya / file hilang -----
function user_avatar_url($avatar) {
    if (empty($avatar)) return null;
    if (!file_exists(uploads_path($avatar))) return null;
    return uploads_url($avatar);
}

// ----- Cart helpers (session-based, untuk guest & logged-in) -----
function get_cart() {
    return isset($_SESSION['cart']) ? $_SESSION['cart'] : [];   // [product_id => qty]
}

function set_cart($cart) {
    $_SESSION['cart'] = $cart;
}

function cart_count() {
    return array_sum(get_cart());
}

function cart_total($pdo) {
    $total = 0;
    $cart = get_cart();
    if (empty($cart)) return 0;
    $ids = array_keys($cart);
    $in = implode(',', array_fill(0, count($ids), '?'));
    $stmt = $pdo->prepare("SELECT id, price, badge, flash_sale_ends_at, flash_sale_discount FROM products WHERE id IN ($in)");
    $stmt->execute($ids);
    foreach ($stmt->fetchAll() as $row) {
        $unit = get_cart_item_price($row);
        $total += $unit * $cart[$row['id']];
    }
    return $total;
}

// ----- Ambil detail item keranjang lengkap (join produk) -----
function cart_items($pdo) {
    $cart = get_cart();
    if (empty($cart)) return [];
    $ids = array_keys($cart);
    $in = implode(',', array_fill(0, count($ids), '?'));
    $stmt = $pdo->prepare("SELECT id, name, price, stock, image, badge, flash_sale_ends_at, flash_sale_discount FROM products WHERE id IN ($in)");
    $stmt->execute($ids);
    $rows = $stmt->fetchAll();
    $items = [];
    foreach ($rows as $row) {
        $qty = $cart[$row['id']];
        // Flash sale aware pricing
        $unit_price = get_cart_item_price($row);
        $items[] = [
            'product_id'      => $row['id'],
            'name'            => $row['name'],
            'price'           => $unit_price,
            'original_price'  => (float)$row['price'],
            'stock'           => (int)$row['stock'],
            'image'           => $row['image'],
            'qty'             => $qty,
            'subtotal'        => $unit_price * $qty,
            'is_flash_sale'  => has_flash_sale_discount($row),
            'flash_sale_discount' => (int)$row['flash_sale_discount'],
        ];
    }
    return $items;
}

// ----- Flash message helpers -----
function set_flash($type, $msg) {
    $_SESSION['flash'][] = ['type' => $type, 'msg' => $msg];
}
function get_flash() {
    if (empty($_SESSION['flash'])) return [];
    $f = $_SESSION['flash'];
    unset($_SESSION['flash']);
    return $f;
}

// ----- Sanitize output -----
function e($str) {
    return htmlspecialchars((string)$str, ENT_QUOTES, 'UTF-8');
}

// ----- Ambil semua kategori -----
function get_categories($pdo) {
    return $pdo->query("SELECT * FROM categories ORDER BY id")->fetchAll();
}

// ----- Ambil N produk terbaru -----
function get_latest_products($pdo, $limit = 8) {
    $stmt = $pdo->prepare("SELECT p.*, c.name AS category_name, c.slug AS category_slug
                           FROM products p JOIN categories c ON p.category_id = c.id
                           ORDER BY p.created_at DESC LIMIT $limit");
    $stmt->execute();
    return $stmt->fetchAll();
}

// ----- Ambil 1 produk terbaru (untuk banner "New Series Drop" di homepage) -----
function get_latest_product($pdo) {
    $stmt = $pdo->prepare("SELECT p.*, c.name AS category_name, c.slug AS category_slug
                           FROM products p JOIN categories c ON p.category_id = c.id
                           ORDER BY p.created_at DESC LIMIT 1");
    $stmt->execute();
    return $stmt->fetch() ?: null;
}

// ============================================================
// NEW ARRIVAL - tag NEW otomatis hilang setelah 7 hari real-time
// ============================================================

// ----- Cek apakah produk masih dalam masa NEW (kurang dari 7 hari sejak created_at) -----
function is_product_new($product) {
    if (empty($product['badge']) || strtoupper($product['badge']) !== 'NEW') return false;
    if (empty($product['created_at'])) return false;
    $created = strtotime($product['created_at']);
    $age_sec = time() - $created;
    return $age_sec <= (7 * 24 * 60 * 60);   // 7 hari dalam detik
}

// ----- Ambil produk NEW arrival (badge=NEW & masih dalam 7 hari) -----
function get_new_arrival_products($pdo, $limit = 12) {
    $sql = "SELECT p.*, c.name AS category_name, c.slug AS category_slug
            FROM products p JOIN categories c ON p.category_id = c.id
            WHERE p.badge = 'NEW'
              AND p.created_at >= DATE_SUB(NOW(), INTERVAL 7 DAY)
            ORDER BY p.created_at DESC
            LIMIT $limit";
    return $pdo->query($sql)->fetchAll();
}

// ----- Hitung jumlah produk NEW aktif (untuk badge menu navbar) -----
function count_new_arrivals($pdo) {
    $sql = "SELECT COUNT(*) FROM products
            WHERE badge = 'NEW'
              AND created_at >= DATE_SUB(NOW(), INTERVAL 7 DAY)";
    return (int)$pdo->query($sql)->fetchColumn();
}

// ----- Auto-clean: set NULL badge untuk produk NEW yang sudah lewat 7 hari -----
// Dijalankan ringan di setiap load halaman publik untuk memastikan tag hilang real-time
function cleanup_expired_new_badges($pdo) {
    $pdo->exec("UPDATE products SET badge = NULL
                 WHERE badge = 'NEW'
                   AND created_at < DATE_SUB(NOW(), INTERVAL 7 DAY)");
}

// ============================================================
// FLASH SALE - produk dengan badge FLASH SALE & flash_sale_ends_at > NOW()
// ============================================================

// ----- Cek apakah produk sedang flash sale aktif -----
function is_flash_sale_active($product) {
    if (!is_array($product)) return false;
    if (empty($product['badge']) || strtoupper($product['badge']) !== 'FLASH SALE') return false;
    if (empty($product['flash_sale_ends_at'])) return false;
    return strtotime($product['flash_sale_ends_at']) > time();
}

// ----- Ambil produk flash sale yang masih aktif -----
function get_flash_sale_products($pdo, $limit = 12) {
    $sql = "SELECT p.*, c.name AS category_name, c.slug AS category_slug
            FROM products p JOIN categories c ON p.category_id = c.id
            WHERE p.badge = 'FLASH SALE'
              AND p.flash_sale_ends_at IS NOT NULL
              AND p.flash_sale_ends_at > NOW()
            ORDER BY p.flash_sale_ends_at ASC
            LIMIT $limit";
    return $pdo->query($sql)->fetchAll();
}

// ----- Hitung jumlah flash sale aktif (untuk badge menu navbar) -----
function count_flash_sale($pdo) {
    $sql = "SELECT COUNT(*) FROM products
            WHERE badge = 'FLASH SALE'
              AND flash_sale_ends_at IS NOT NULL
              AND flash_sale_ends_at > NOW()";
    return (int)$pdo->query($sql)->fetchColumn();
}

// ----- Auto-clean: set NULL badge untuk flash sale yang sudah expired -----
// Dijalankan ringan di setiap load halaman publik
function cleanup_expired_flash_sales($pdo) {
    $pdo->exec("UPDATE products
                 SET badge = NULL, flash_sale_ends_at = NULL
                 WHERE badge = 'FLASH SALE'
                   AND flash_sale_ends_at IS NOT NULL
                   AND flash_sale_ends_at < NOW()");
}

// ----- Hitung sisa waktu flash sale (detik). Return 0 jika sudah lewat -----
function flash_sale_remaining_seconds($product) {
    if (empty($product['flash_sale_ends_at'])) return 0;
    $remaining = strtotime($product['flash_sale_ends_at']) - time();
    return max(0, $remaining);
}

// ----- Format sisa waktu flash sale untuk display: "2h 15m 30s" -----
function format_countdown_short($seconds) {
    if ($seconds <= 0) return '00:00:00';
    $days = floor($seconds / 86400);
    $hours = floor(($seconds % 86400) / 3600);
    $mins = floor(($seconds % 3600) / 60);
    $secs = $seconds % 60;
    if ($days > 0) {
        return $days . 'd ' . str_pad($hours, 2, '0', STR_PAD_LEFT) . ':' . str_pad($mins, 2, '0', STR_PAD_LEFT) . ':' . str_pad($secs, 2, '0', STR_PAD_LEFT);
    }
    return str_pad($hours, 2, '0', STR_PAD_LEFT) . ':' . str_pad($mins, 2, '0', STR_PAD_LEFT) . ':' . str_pad($secs, 2, '0', STR_PAD_LEFT);
}

// ============================================================
// VOUCHER SYSTEM
// ============================================================

// ----- Ambil semua voucher (untuk admin) -----
function get_all_vouchers($pdo) {
    $sql = "SELECT * FROM vouchers ORDER BY created_at DESC";
    return $pdo->query($sql)->fetchAll();
}

// ----- Ambil 1 voucher by id -----
function get_voucher($pdo, $id) {
    $stmt = $pdo->prepare("SELECT * FROM vouchers WHERE id = ? LIMIT 1");
    $stmt->execute([$id]);
    return $stmt->fetch();
}

// ----- Ambil 1 voucher by code (case-insensitive) -----
function get_voucher_by_code($pdo, $code) {
    $stmt = $pdo->prepare("SELECT * FROM vouchers WHERE UPPER(code) = UPPER(?) LIMIT 1");
    $stmt->execute([trim($code)]);
    return $stmt->fetch();
}

// ----- Cek apakah voucher valid & return array [valid, message, voucher] -----
function validate_voucher($pdo, $code, $subtotal) {
    $code = trim($code);
    if ($code === '') {
        return [false, 'Kode voucher tidak boleh kosong.', null];
    }
    $v = get_voucher_by_code($pdo, $code);
    if (!$v) {
        return [false, "Voucher \"{$code}\" tidak ditemukan.", null];
    }
    if ((int)$v['is_active'] !== 1) {
        return [false, "Voucher \"{$code}\" sudah tidak aktif.", $v];
    }
    $now = date('Y-m-d H:i:s');
    if (!empty($v['starts_at']) && $v['starts_at'] > $now) {
        return [false, "Voucher \"{$code}\" belum berlaku (mulai " . tgl_indo($v['starts_at']) . ").", $v];
    }
    if (!empty($v['ends_at']) && $v['ends_at'] < $now) {
        return [false, "Voucher \"{$code}\" sudah berakhir.", $v];
    }
    if (!empty($v['quota']) && (int)$v['used_count'] >= (int)$v['quota']) {
        return [false, "Voucher \"{$code}\" sudah mencapai kuota penggunaan.", $v];
    }
    if ((float)$v['min_purchase'] > 0 && (float)$subtotal < (float)$v['min_purchase']) {
        return [false, "Minimal belanja " . rupiah($v['min_purchase']) . " untuk menggunakan voucher ini. Subtotal kamu " . rupiah($subtotal) . ".", $v];
    }
    return [true, '', $v];
}

// ----- Hitung diskon voucher berdasarkan subtotal -----
function calculate_voucher_discount($voucher, $subtotal) {
    if (!$voucher) return 0.0;
    $subtotal = (float)$subtotal;
    if ($voucher['discount_type'] === 'percent') {
        $disc = $subtotal * ((float)$voucher['discount_value'] / 100);
        if (!empty($voucher['max_discount'])) {
            $disc = min($disc, (float)$voucher['max_discount']);
        }
    } else {
        // fixed
        $disc = (float)$voucher['discount_value'];
    }
    return min($disc, $subtotal);   // diskon tidak boleh > subtotal
}

// ----- Format deskripsi diskon voucher untuk display -----
function voucher_discount_label($voucher) {
    if (!$voucher) return '';
    if ($voucher['discount_type'] === 'percent') {
        $label = "Diskon " . ((float)$voucher['discount_value']) . "%";
        if (!empty($voucher['max_discount'])) {
            $label .= " (max " . rupiah($voucher['max_discount']) . ")";
        }
        return $label;
    }
    return "Potongan " . rupiah($voucher['discount_value']);
}

// ============================================================
// KOIN ANOHOBBY (Points System - like Shopee Coins)
// ============================================================
// Conversion: 1 koin = Rp 1 (1:1)
// Earn rate: 1% dari grand_total (rounded down)
// Initial dummy balance: lihat kolom `users.points`

// ----- Ambil saldo koin user -----
function get_user_points($pdo, $user_id) {
    $stmt = $pdo->prepare("SELECT points FROM users WHERE id = ?");
    $stmt->execute([$user_id]);
    return (int)$stmt->fetchColumn();
}

// ----- Hitung koin yang akan didapat dari transaksi (1% dari grand_total) -----
function calculate_earned_points($grand_total) {
    return (int)floor((float)$grand_total * 0.01);
}

// ----- Hitung maksimum koin yang bisa dipakai (default: min(saldo, 25% dari subtotal)) -----
function max_usable_points($pdo, $user_id, $subtotal) {
    $balance = get_user_points($pdo, $user_id);
    $cap_25_percent = (int)floor((float)$subtotal * 0.25);
    return min($balance, $cap_25_percent);
}

// ----- Format koin untuk display -----
function format_points($points) {
    return number_format((int)$points) . ' koin';
}

// ============================================================
// USER VOUCHERS (Item-based vouchers, like Shopee)
// ============================================================

// ----- Ambil semua voucher yang available untuk di-claim user -----
// (claimable=1, is_active=1, dalam periode claim, belum mencapai claim_quota)
function get_claimable_vouchers($pdo) {
    $sql = "SELECT * FROM vouchers
            WHERE claimable = 1
              AND is_active = 1
              AND (claim_starts_at IS NULL OR claim_starts_at <= NOW())
              AND (claim_ends_at IS NULL OR claim_ends_at >= NOW())
              AND (claim_quota IS NULL OR claimed_count < claim_quota)
            ORDER BY created_at DESC";
    return $pdo->query($sql)->fetchAll();
}

// ----- Ambil semua voucher milik user (item) -----
function get_user_vouchers($pdo, $user_id, $status = null) {
    $sql = "SELECT * FROM user_vouchers WHERE user_id = ?";
    $params = [$user_id];
    if ($status !== null) {
        $sql .= " AND status = ?";
        $params[] = $status;
    }
    $sql .= " ORDER BY claimed_at DESC";
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    return $stmt->fetchAll();
}

// ----- Ambil voucher milik user yang available untuk checkout -----
function get_user_available_vouchers($pdo, $user_id, $subtotal = 0) {
    // Auto-expire dulu
    cleanup_expired_user_vouchers($pdo);

    $sql = "SELECT * FROM user_vouchers
            WHERE user_id = ?
              AND status = 'available'
              AND (expires_at IS NULL OR expires_at > NOW())
              AND min_purchase <= ?
            ORDER BY claimed_at DESC";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$user_id, $subtotal]);
    return $stmt->fetchAll();
}

// ----- Ambil 1 user_voucher by id -----
function get_user_voucher($pdo, $id, $user_id = null) {
    if ($user_id !== null) {
        $stmt = $pdo->prepare("SELECT * FROM user_vouchers WHERE id = ? AND user_id = ? LIMIT 1");
        $stmt->execute([$id, $user_id]);
    } else {
        $stmt = $pdo->prepare("SELECT * FROM user_vouchers WHERE id = ? LIMIT 1");
        $stmt->execute([$id]);
    }
    return $stmt->fetch();
}

// ----- Cek apakah user sudah pernah claim voucher ini -----
function has_user_claimed_voucher($pdo, $user_id, $voucher_id) {
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM user_vouchers WHERE user_id = ? AND voucher_id = ? AND status = 'available'");
    $stmt->execute([$user_id, $voucher_id]);
    return (int)$stmt->fetchColumn() > 0;
}

// ----- Claim voucher untuk user (return [success, message]) -----
function claim_voucher($pdo, $user_id, $voucher_id) {
    $v = get_voucher($pdo, $voucher_id);
    if (!$v) {
        return [false, 'Voucher tidak ditemukan.'];
    }
    if ((int)$v['claimable'] !== 1) {
        return [false, 'Voucher ini tidak bisa di-claim.'];
    }
    if ((int)$v['is_active'] !== 1) {
        return [false, 'Voucher tidak aktif.'];
    }
    $now = date('Y-m-d H:i:s');
    if (!empty($v['claim_starts_at']) && $v['claim_starts_at'] > $now) {
        return [false, 'Voucher belum dibuka untuk klaim.'];
    }
    if (!empty($v['claim_ends_at']) && $v['claim_ends_at'] < $now) {
        return [false, 'Periode klaim voucher sudah berakhir.'];
    }
    if (!empty($v['claim_quota']) && (int)$v['claimed_count'] >= (int)$v['claim_quota']) {
        return [false, 'Kuota klaim voucher sudah habis.'];
    }
    if (has_user_claimed_voucher($pdo, $user_id, $voucher_id)) {
        return [false, 'Kamu sudah punya voucher ini dan belum dipakai.'];
    }

    // Hitung expires_at user_voucher (claim_at + validity_days)
    $validity_days = max(1, (int)$v['voucher_validity_days']);
    $expires_at = date('Y-m-d H:i:s', strtotime("+{$validity_days} days"));

    // Insert user_voucher
    $stmt = $pdo->prepare("INSERT INTO user_vouchers
        (user_id, voucher_id, voucher_code_snapshot, description_snapshot, discount_type, discount_value, min_purchase, max_discount, status, claimed_at, expires_at)
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, 'available', NOW(), ?)");
    $stmt->execute([
        $user_id,
        $voucher_id,
        $v['code'],
        $v['description'],
        $v['discount_type'],
        $v['discount_value'],
        $v['min_purchase'],
        $v['max_discount'],
        $expires_at,
    ]);

    // Increment claimed_count
    $pdo->prepare("UPDATE vouchers SET claimed_count = claimed_count + 1 WHERE id = ?")->execute([$voucher_id]);

    return [true, "Voucher \"{$v['code']}\" berhasil diklaim! Berlaku sampai " . tgl_indo($expires_at) . "."];
}

// ----- Hitung diskon dari user_voucher -----
function calculate_user_voucher_discount($user_voucher, $subtotal) {
    if (!$user_voucher) return 0.0;
    $subtotal = (float)$subtotal;
    if ($user_voucher['discount_type'] === 'percent') {
        $disc = $subtotal * ((float)$user_voucher['discount_value'] / 100);
        if (!empty($user_voucher['max_discount'])) {
            $disc = min($disc, (float)$user_voucher['max_discount']);
        }
    } else {
        $disc = (float)$user_voucher['discount_value'];
    }
    return min($disc, $subtotal);
}

// ----- Mark user_voucher sebagai used -----
function mark_user_voucher_used($pdo, $user_voucher_id, $transaction_id) {
    $stmt = $pdo->prepare("UPDATE user_vouchers SET status='used', used_at=NOW(), transaction_id=? WHERE id=? AND status='available'");
    $stmt->execute([$transaction_id, $user_voucher_id]);
    return $stmt->rowCount() > 0;
}

// ----- Auto-expire user_vouchers yang sudah lewat expires_at -----
function cleanup_expired_user_vouchers($pdo) {
    $pdo->exec("UPDATE user_vouchers
                 SET status = 'expired'
                 WHERE status = 'available'
                   AND expires_at IS NOT NULL
                   AND expires_at < NOW()");
}

// ----- Format label diskon user_voucher -----
function user_voucher_discount_label($uv) {
    if (!$uv) return '';
    if ($uv['discount_type'] === 'percent') {
        $label = "Diskon " . ((float)$uv['discount_value']) . "%";
        if (!empty($uv['max_discount'])) {
            $label .= " (max " . rupiah($uv['max_discount']) . ")";
        }
        return $label;
    }
    return "Potongan " . rupiah($uv['discount_value']);
}

// ============================================================
// FLASH SALE - RANDOM WEIGHTED DISCOUNT (25% - 90%)
// ============================================================
// Distribusi probabilitas:
//   25-35%  -> 30%  chance  (paling umum)
//   36-50%  -> 30%  chance
//   51-65%  -> 20%  chance
//   66-75%  -> 12%  chance
//   76-85%  -> 6%   chance
//   86-90%  -> 2%   chance  (paling jarang)
// ============================================================

function generate_weighted_flash_sale_discount() {
    $buckets = [
        ['min' => 25, 'max' => 35, 'weight' => 30],
        ['min' => 36, 'max' => 50, 'weight' => 30],
        ['min' => 51, 'max' => 65, 'weight' => 20],
        ['min' => 66, 'max' => 75, 'weight' => 12],
        ['min' => 76, 'max' => 85, 'weight' => 6],
        ['min' => 86, 'max' => 90, 'weight' => 2],
    ];
    $total_weight = array_sum(array_column($buckets, 'weight'));   // 100
    $r = mt_rand(1, $total_weight);
    $cumulative = 0;
    foreach ($buckets as $b) {
        $cumulative += $b['weight'];
        if ($r <= $cumulative) {
            return mt_rand($b['min'], $b['max']);
        }
    }
    return 25;
}

// ----- Hitung harga setelah flash sale discount -----
function flash_sale_price($product) {
    if (!is_array($product)) return 0;
    $original = (float)($product['price'] ?? 0);
    $disc_pct = (int)($product['flash_sale_discount'] ?? 0);
    if ($disc_pct <= 0) return $original;
    if ($disc_pct > 90) $disc_pct = 90;
    return $original * (1 - ($disc_pct / 100));
}

// ----- Cek apakah produk punya flash sale discount aktif -----
function has_flash_sale_discount($product) {
    if (!is_array($product)) return false;
    return is_flash_sale_active($product) && (int)($product['flash_sale_discount'] ?? 0) > 0;
}

// ----- Format label persen diskon untuk display -----
function flash_sale_discount_label($product) {
    $disc = (int)$product['flash_sale_discount'];
    return '-' . $disc . '%';
}

// ----- Ambil harga item untuk cart/checkout (flash sale aware) -----
// Fungsi ini mengembalikan harga yang harus dibayar per unit untuk produk di cart
function get_cart_item_price($product) {
    if (!is_array($product)) return 0;
    return has_flash_sale_discount($product) ? flash_sale_price($product) : (float)($product['price'] ?? 0);
}

// ----- Ambil produk by kategori (slug) -----
function get_products_by_category($pdo, $slug) {
    $stmt = $pdo->prepare("SELECT p.*, c.name AS category_name, c.slug AS category_slug
                           FROM products p JOIN categories c ON p.category_id = c.id
                           WHERE c.slug = ? ORDER BY p.id");
    $stmt->execute([$slug]);
    return $stmt->fetchAll();
}

// ----- Ambil 1 produk by id -----
function get_product($pdo, $id) {
    $stmt = $pdo->prepare("SELECT p.*, c.name AS category_name, c.slug AS category_slug
                           FROM products p JOIN categories c ON p.category_id = c.id
                           WHERE p.id = ?");
    $stmt->execute([$id]);
    return $stmt->fetch();
}

// ----- Ambil 1 kategori by slug -----
function get_category_by_slug($pdo, $slug) {
    $stmt = $pdo->prepare("SELECT * FROM categories WHERE slug = ? LIMIT 1");
    $stmt->execute([$slug]);
    return $stmt->fetch();
}

// ===== Daftar filter per kategori (slug => array of filters) =====
function get_category_filters($category_slug) {
    $filters = [
        'model-kit' => [
            'brand' => [
                'label' => 'Brand',
                'options' => ['Bandai', 'Bootleg', 'Third Party', 'Mochin']
            ]
        ],
        'tcg-cards' => [
            'series' => [
                'label' => 'Seri',
                'options' => ['Pokemon', 'Yu-Gi-Oh', 'One Piece', 'Magic']
            ]
        ],
        // Tambahkan kategori lain sesuai kebutuhan
        'pvc-figure' => [
            'manufacturer' => [
                'label' => 'Produsen',
                'options' => ['Good Smile', 'KADOKAWA', 'Alter', 'Kotobukiya']
            ]
        ],
    ];
    return $filters[$category_slug] ?? [];
}

// ----- Ambil produk by kategori (slug) - digunakan di category.php -----
function get_products_by_category_slug($pdo, $slug, $sort = 'newest', $filters = []) {
    $sql = "SELECT p.*, c.name AS category_name, c.slug AS category_slug
            FROM products p JOIN categories c ON p.category_id = c.id
            WHERE c.slug = ?";
    $params = [$slug];

    // Terapkan filter jika ada
    if (!empty($filters)) {
        foreach ($filters as $key => $value) {
            // Misal filter brand
            if ($key === 'brand' && !empty($value)) {
                $sql .= " AND p.brand = ?";
                $params[] = $value;
            }
            // Bisa ditambah filter lain (series, manufacturer, dll) sesuai kolom
        }
    }

    switch ($sort) {
        case 'price_asc':  $sql .= " ORDER BY p.price ASC"; break;
        case 'price_desc': $sql .= " ORDER BY p.price DESC"; break;
        case 'name':       $sql .= " ORDER BY p.name ASC"; break;
        default:           $sql .= " ORDER BY p.created_at DESC";
    }
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    return $stmt->fetchAll();
}

// ----- Hitung total produk & stok tersedia per kategori -----
function get_category_stats($pdo, $category_id) {
    $stmt = $pdo->prepare("SELECT COUNT(*) AS total_products, COALESCE(SUM(stock),0) AS total_stock FROM products WHERE category_id = ?");
    $stmt->execute([$category_id]);
    return $stmt->fetch();
}

// ----- Ambil N produk untuk section "NEW STOCK" -----
// Prioritas: produk dengan stok > 0, diurutkan berdasarkan created_at DESC
function get_new_stock_products($pdo, $limit = 8) {
    $stmt = $pdo->prepare("SELECT p.*, c.name AS category_name, c.slug AS category_slug
                           FROM products p JOIN categories c ON p.category_id = c.id
                           ORDER BY (p.stock = 0) ASC, p.created_at DESC
                           LIMIT $limit");
    $stmt->execute();
    return $stmt->fetchAll();
}

// ----- Format tanggal Indonesia -----
function tgl_indo($datetime) {
    $bulan = [1=>'Januari','Februari','Maret','April','Mei','Juni','Juli','Agustus','September','Oktober','November','Desember'];
    $ts = strtotime($datetime);
    return date('d', $ts) . ' ' . $bulan[(int)date('n', $ts)] . ' ' . date('Y', $ts) . ' ' . date('H:i', $ts);
}

// ----- Opsi pengiriman -----
function shipping_options() {
    return [
        'JNE REG'       => ['label' => 'JNE REG (2-3 hari)',     'cost' => 25000],
        'JNE YES'       => ['label' => 'JNE YES (1 hari)',       'cost' => 45000],
        'J&T EZ'        => ['label' => 'J&T EZ (2-3 hari)',      'cost' => 22000],
        'SiCepat REG'   => ['label' => 'SiCepat REG (2-3 hari)', 'cost' => 24000],
        'AnterAja REG'  => ['label' => 'AnterAja REG (2-3 hari)','cost' => 23000],
        'GoSend Instant'=> ['label' => 'GoSend Instant (hari ini)', 'cost' => 35000],
    ];
}

// ----- Opsi pembayaran -----
function payment_options() {
    return [
        'Transfer Bank BCA'      => 'Transfer Bank BCA - 1234567890 a.n. AnoHobby',
        'Transfer Bank Mandiri'  => 'Transfer Bank Mandiri - 9876543210 a.n. AnoHobby',
        'Transfer Bank BNI'      => 'Transfer Bank BNI - 5555666677 a.n. AnoHobby',
        'GoPay'                  => 'GoPay - 081234567890',
        'OVO'                    => 'OVO - 081234567890',
        'DANA'                   => 'DANA - 081234567890',
        'COD (Cash on Delivery)' => 'COD - Bayar di tempat (max Rp 5.000.000)',
    ];
}
