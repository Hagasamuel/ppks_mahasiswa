<?php
// Konfigurasi Database
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'ppks_mahasiswa');

// Konfigurasi Aplikasi
define('BASE_URL', 'http://localhost/ppks_mahasiswa/');
define('UPLOAD_DIR', __DIR__ . '/../uploads/');
define('UPLOAD_URL', BASE_URL . 'uploads/');
define('MAX_FILE_SIZE', 10 * 1024 * 1024); // 10MB

// Koneksi PDO
try {
    $pdo = new PDO(
        "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8mb4",
        DB_USER,
        DB_PASS,
        [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES   => false,
        ]
    );
} catch (PDOException $e) {
    die('❌ Koneksi database gagal: ' . $e->getMessage());
}

// Mulai session (untuk login admin)
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}