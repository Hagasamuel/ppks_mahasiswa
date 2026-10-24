<?php
/**
 * File bantu — fungsi-fungsi yang dipakai berulang
 */

// Generate kode tracking unik, mis. PPKS-A1B2C3D4
function generateTrackingCode() {
    return 'PPKS-' . strtoupper(bin2hex(random_bytes(4)));
}

// Bersihkan input dari user
function sanitize($v) {
    return htmlspecialchars(trim($v ?? ''), ENT_QUOTES, 'UTF-8');
}

// Redirect cepat
function redirect($url) {
    header("Location: $url");
    exit;
}

// Cek apakah admin sudah login
function isLoggedIn() {
    return isset($_SESSION['user_id']);
}

// Wajib login — kalau belum, redirect ke halaman login
function requireLogin() {
    if (!isLoggedIn()) {
        redirect(BASE_URL . 'auth/login.php');
    }
}

// Catat aksi ke audit_log
function logAudit($pdo, $aksi, $detail = '') {
    $uid = $_SESSION['user_id'] ?? null;
    $ip  = $_SERVER['REMOTE_ADDR'] ?? null;
    try {
        $pdo->prepare("INSERT INTO audit_log (user_id, aksi, detail, ip_address)
                       VALUES (?,?,?,?)")
            ->execute([$uid, $aksi, $detail, $ip]);
    } catch (Exception $e) {
        // Diamkan — jangan sampai log error mengganggu user
    }
}

// Tampilkan badge status dengan warna
function statusBadge($status) {
    $map = [
        'diterima'     => ['Diterima', '#3b82f6'],
        'diverifikasi' => ['Diverifikasi', '#8b5cf6'],
        'proses'       => ['Dalam Proses', '#f59e0b'],
        'selesai'      => ['Selesai', '#10b981'],
        'ditolak'      => ['Ditolak', '#ef4444'],
        'dialihkan'    => ['Dialihkan', '#6b7280'],
    ];
    [$label, $color] = $map[$status] ?? [$status, '#888'];
    return "<span style='background:$color;color:#fff;padding:3px 10px;border-radius:12px;font-size:12px;font-weight:600'>$label</span>";
}

// Format tanggal Indonesia sederhana
function tglIndo($datetime) {
    if (!$datetime) return '-';
    $bulan = ['', 'Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun',
              'Jul', 'Agu', 'Sep', 'Okt', 'Nov', 'Des'];
    $ts = strtotime($datetime);
    return date('d', $ts) . ' ' . $bulan[(int)date('n', $ts)] . ' ' . date('Y H:i', $ts);
}