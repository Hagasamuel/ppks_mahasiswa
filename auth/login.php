<?php
require_once '../config/database.php';
require_once '../includes/helpers.php';

if (isLoggedIn()) {
    if ($_SESSION['user_role'] === 'admin') {
        redirect(BASE_URL . 'admin/dashboard.php');
    } else {
        redirect(BASE_URL . 'user/lapor.php');
    }
}

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $pass  = $_POST['password'] ?? '';

    $stmt = $pdo->prepare("SELECT * FROM users WHERE email=? AND aktif=1");
    $stmt->execute([$email]);
    $user = $stmt->fetch();

    if ($user && password_verify($pass, $user['password_hash'])) {
        session_regenerate_id(true);
        $_SESSION['user_id']    = $user['id'];
        $_SESSION['user_nama']  = $user['nama'];
        $_SESSION['user_role']  = $user['role'];
        $_SESSION['user_nim']   = $user['nim'];
        $_SESSION['user_prodi'] = $user['prodi'];

        $pdo->prepare("UPDATE users SET last_login=NOW() WHERE id=?")
            ->execute([$user['id']]);
        logAudit($pdo, 'login', "$email ({$user['role']})");

        if ($user['role'] === 'admin') {
            redirect(BASE_URL . 'admin/dashboard.php');
        } else {
            redirect(BASE_URL . 'user/lapor.php');
        }
    } else {
        $error = 'Email atau password salah.';
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Login - Portal Layanan PPKS</title>
<link rel="stylesheet" href="<?= BASE_URL ?>assets/css/landing.css">
<style>
  .auth-wrap { max-width: 420px; margin: 70px auto; padding: 0 20px; }
  .auth-card { background: #fff; padding: 32px; border-radius: 16px;
    box-shadow: 0 10px 30px rgba(15, 35, 60, 0.08); border: 1px solid #e4ebf2; }
  .auth-card h1 { color: #102a43; font-size: 1.5rem; font-weight: 800;
    text-align: center; margin-bottom: 8px; }
  .auth-card .sub { text-align: center; color: #627d98;
    font-size: 0.9rem; margin-bottom: 24px; }
  .auth-card label { display: block; color: #334e68; font-size: 0.85rem;
    font-weight: 600; margin: 14px 0 6px; }
  .auth-card input { width: 100%; padding: 11px 14px;
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
      <li><a href="<?= BASE_URL ?>auth/daftar.php">Daftar</a></li>
    </ul>
  </div>
</header>

<div class="auth-wrap">
  <div class="auth-card">
    <h1>Login</h1>
    <p class="sub">Masuk untuk melapor atau mengelola laporan</p>

    <?php if ($error): ?>
      <div class="auth-alert error"><?= $error ?></div>
    <?php endif; ?>

    <form method="POST">
      <label>Email</label>
      <input type="email" name="email" required autofocus placeholder="email@kampus.ac.id">

      <label>Password</label>
      <input type="password" name="password" required placeholder="Password">

      <button type="submit" class="auth-btn">Masuk</button>
    </form>

    <p class="auth-foot">
      Belum punya akun? <a href="<?= BASE_URL ?>auth/daftar.php">Daftar di sini</a>
    </p>
  </div>
</div>

</body>
</html>