<?php
require_once '../config/database.php';
require_once '../includes/helpers.php';

if (isLoggedIn()) redirect(BASE_URL . 'index.php');

$error = '';
$sukses = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $nama  = sanitize($_POST['nama'] ?? '');
        $nim   = sanitize($_POST['nim'] ?? '');
        $prodi = sanitize($_POST['prodi'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $pass  = $_POST['password'] ?? '';
        $pass2 = $_POST['password2'] ?? '';

        if (!$nama || !$nim || !$prodi || !$email || !$pass) {
            throw new Exception('Semua field wajib diisi.');
        }
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            throw new Exception('Format email tidak valid.');
        }
        if (strlen($pass) < 6) {
            throw new Exception('Password minimal 6 karakter.');
        }
        if ($pass !== $pass2) {
            throw new Exception('Konfirmasi password tidak cocok.');
        }

        $cek = $pdo->prepare("SELECT id FROM users WHERE email=? OR nim=?");
        $cek->execute([$email, $nim]);
        if ($cek->fetch()) {
            throw new Exception('Email atau NIM sudah terdaftar.');
        }

        $hash = password_hash($pass, PASSWORD_DEFAULT);

        $pdo->prepare("INSERT INTO users (nama, nim, prodi, email, password_hash, role)
                       VALUES (?,?,?,?,?,'mahasiswa')")
            ->execute([$nama, $nim, $prodi, $email, $hash]);

        logAudit($pdo, 'register_mahasiswa', $email);
        $sukses = true;
    } catch (Exception $e) {
        $error = $e->getMessage();
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Daftar Akun - Portal Layanan PPKS</title>
<link rel="stylesheet" href="<?= BASE_URL ?>assets/css/landing.css">
<style>
  .auth-wrap { max-width: 480px; margin: 50px auto; padding: 0 20px; }
  .auth-card { background: #fff; padding: 32px; border-radius: 16px;
    box-shadow: 0 10px 30px rgba(15, 35, 60, 0.08); border: 1px solid #e4ebf2; }
  .auth-card h1 { color: #102a43; font-size: 1.5rem;
    font-weight: 800; text-align: center; margin-bottom: 8px; }
  .auth-card .sub { text-align: center; color: #627d98;
    font-size: 0.9rem; margin-bottom: 24px; }
  .auth-card label { display: block; color: #334e68; font-size: 0.85rem;
    font-weight: 600; margin: 14px 0 6px; }
  .auth-card input, .auth-card select { width: 100%; padding: 11px 14px;
    border: 1px solid #cbdbea; border-radius: 9px;
    font-size: 0.9rem; background: #fff; color: #172033; }
  .auth-card input:focus { outline: none; border-color: #1261c9;
    box-shadow: 0 0 0 3px rgba(18, 97, 201, 0.12); }
  .auth-btn { width: 100%; margin-top: 22px; padding: 13px;
    background: #1261c9; color: #fff; border: none;
    border-radius: 9px; font-weight: 700; font-size: 0.9rem;
    cursor: pointer; transition: 0.2s; }
  .auth-btn:hover { background: #0d54b0; }
  .auth-alert { padding: 10px 14px; border-radius: 8px;
    font-size: 0.85rem; margin-bottom: 14px; }
  .auth-alert.error { background: #fee2e2; color: #991b1b; }
  .auth-foot { text-align: center; margin-top: 18px;
    font-size: 0.85rem; color: #627d98; }
  .auth-foot a { color: #1261c9; font-weight: 600; }
  .grid-2 { display: grid; grid-template-columns: 1fr 1fr; gap: 12px; }
  @media (max-width: 480px) { .grid-2 { grid-template-columns: 1fr; } }
</style>
</head>
<body>

<header class="navbar">
  <div class="navbar-container">
    <div class="logo">
      <span class="logo-icon">🛡️</span> Portal Layanan PPKS
    </div>
    <ul class="nav-links">
      <li><a href="<?= BASE_URL ?>index.php">Beranda</a></li>
      <li><a href="<?= BASE_URL ?>auth/login.php">Login</a></li>
    </ul>
  </div>
</header>

<div class="auth-wrap">
  <div class="auth-card">
    <?php if ($sukses): ?>
      <h1>✅ Pendaftaran Berhasil</h1>
      <p class="sub">Akun Anda sudah aktif. Silakan login untuk mulai melapor.</p>
      <a href="<?= BASE_URL ?>auth/login.php" class="auth-btn" style="display:block;text-align:center;text-decoration:none;color:#fff">Login Sekarang</a>
    <?php else: ?>
      <h1>Daftar Akun Mahasiswa</h1>
      <p class="sub">Buat akun untuk melaporkan kekerasan di lingkungan kampus</p>

      <?php if ($error): ?>
        <div class="auth-alert error"><?= $error ?></div>
      <?php endif; ?>

      <form method="POST">
        <label>Nama Lengkap *</label>
        <input type="text" name="nama" required value="<?= sanitize($_POST['nama'] ?? '') ?>">

        <div class="grid-2">
          <div>
            <label>NIM *</label>
            <input type="text" name="nim" required value="<?= sanitize($_POST['nim'] ?? '') ?>">
          </div>
          <div>
            <label>Program Studi *</label>
            <input type="text" name="prodi" required placeholder="Teknik Informatika" value="<?= sanitize($_POST['prodi'] ?? '') ?>">
          </div>
        </div>

        <label>Email *</label>
        <input type="email" name="email" required value="<?= sanitize($_POST['email'] ?? '') ?>">

        <div class="grid-2">
          <div>
            <label>Password *</label>
            <input type="password" name="password" required minlength="6">
          </div>
          <div>
            <label>Konfirmasi Password *</label>
            <input type="password" name="password2" required minlength="6">
          </div>
        </div>

        <button type="submit" class="auth-btn">Daftar Sekarang</button>
      </form>

      <p class="auth-foot">
        Sudah punya akun? <a href="<?= BASE_URL ?>auth/login.php">Login di sini</a>
      </p>
    <?php endif; ?>
  </div>
</div>

</body>
</html>