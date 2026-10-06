<?php
// ============================================================
// DATABASE CONFIGURATION
// Sesuaikan host, user, password, dan nama database
// dengan konfigurasi MySQL/MariaDB Anda (XAMPP/Laragon/MAMP)
// ============================================================

define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '20O91214');              // default XAMPP kosong, Laragon juga kosong
define('DB_NAME', 'anohobby');

// Konfigurasi toko
define('STORE_NAME', 'ANOHOBBY');
define('STORE_TAGLINE', 'Hobby Shop - Modelkit, TCG, PVC Figure, Tools Rakit');
define('STORE_ADDRESS', 'Jln.Perjuangan No.10 Karyamulya Kesambi Kota Cirebon (Majasem)');
define('STORE_PHONE', '+62 812-3456-7890');
define('STORE_EMAIL', 'anohobby515@gmail.com');

// Koneksi database (PDO)
try {
    $pdo = new PDO(
        "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8mb4",
        DB_USER,
        DB_PASS,
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false,
        ]
    );
} catch (PDOException $e) {
    // Tampilkan pesan error yang informatif untuk pengembangan
    die("<h2>Database connection failed</h2>
         <p>Pastikan Anda sudah meng-import file <code>database/hobby_store.sql</code> ke phpMyAdmin / MySQL.</p>
         <p>Error: " . htmlspecialchars($e->getMessage()) . "</p>
         <p>Cek file <code>config/database.php</code> untuk konfigurasi kredensial.</p>");
}
