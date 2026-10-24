<?php
require_once 'config/database.php';
require_once 'includes/helpers.php';
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Portal Layanan PPKS - Kanal Pengaduan Kekerasan Kampus</title>
  <link rel="stylesheet" href="<?= BASE_URL ?>assets/css/landing.css">
</head>
<body>

  <header class="navbar">
    <div class="navbar-container">
      <div class="logo">
        <span class="logo-icon">🛡️</span> Portal Layanan PPKS
      </div>
      <ul class="nav-links">
        <li><a href="<?= BASE_URL ?>index.php" class="active">Beranda</a></li>
        <li><a href="<?= BASE_URL ?>edukasi.php">Edukasi</a></li>
        <li><a href="<?= BASE_URL ?>user/cek_status.php">Lacak Laporan</a></li>
        <li><a href="<?= BASE_URL ?>edukasi.php#kontak">Kontak Darurat</a></li>

        <?php if (isLoggedIn()): ?>
          <?php if ($_SESSION['user_role'] === 'admin'): ?>
            <li><a href="<?= BASE_URL ?>admin/dashboard.php">Dashboard</a></li>
          <?php else: ?>
            <li><a href="<?= BASE_URL ?>user/laporan-saya.php">Dashboard</a></li>
          <?php endif; ?>
          <li><a href="<?= BASE_URL ?>auth/logout.php">Logout</a></li>
        <?php else: ?>
          <li><a href="<?= BASE_URL ?>auth/login.php">Login</a></li>
          <li><a href="<?= BASE_URL ?>auth/daftar.php">Daftar</a></li>
        <?php endif; ?>
      </ul>
    </div>
  </header>

  <section class="hero">
    <h1>Ruang Aman untuk Bercerita & Melapor</h1>
    <p>Platform terintegrasi Satgas PPKS untuk pelaporan kekerasan yang aman, rahasia, dan terdokumentasi.</p>

    <div class="hero-buttons">
      <?php if (isLoggedIn()): ?>
        <a href="<?= BASE_URL ?>user/lapor.php" class="btn-cta">Buat Laporan</a>
      <?php else: ?>
        <a href="<?= BASE_URL ?>auth/login.php" class="btn-cta">Buat Laporan</a>
      <?php endif; ?>
      <a href="<?= BASE_URL ?>edukasi.php" class="btn-cta btn-secondary">Pelajari Prosedur</a>
    </div>

    <p class="hero-note">🔒 Identitas Anda dijamin kerahasiaannya. Tersedia opsi pelaporan anonim.</p>
  </section>

  <section class="features-container">
    <div class="feature-card card-1">
      <div class="icon-box">🛡️</div>
      <h3>Pengaduan Rahasia</h3>
      <p>Lapor secara anonim atau dengan identitas yang dirahasiakan. Hanya Satgas berwenang yang dapat mengakses.</p>
    </div>
    <div class="feature-card card-2">
      <div class="icon-box">📎</div>
      <h3>Unggah Bukti Pendukung</h3>
      <p>Lampirkan dokumen, foto, atau keterangan pendukung untuk memperkuat laporan Anda.</p>
    </div>
    <div class="feature-card card-3">
      <div class="icon-box">📊</div>
      <h3>Pelacakan Status</h3>
      <p>Pantau perkembangan laporan Anda (diterima, verifikasi, proses, selesai) secara real-time.</p>
    </div>
    <div class="feature-card card-4">
      <div class="icon-box">🤝</div>
      <h3>Edukasi & Kontak Darurat</h3>
      <p>Informasi prosedur pelaporan, hak-hak korban, serta kontak pendampingan psikologis.</p>
    </div>
  </section>

  <section class="alur-container">
    <h2>Alur Pengaduan</h2>
    <div class="alur-grid">
      <div class="alur-step">
        <div class="step-number">1</div>
        <h3>Isi Formulir</h3>
        <p>Laporkan kejadian secara anonim atau terbuka, lengkap dengan bukti pendukung.</p>
      </div>
      <div class="alur-step">
        <div class="step-number">2</div>
        <h3>Verifikasi Satgas</h3>
        <p>Satgas PPKS memverifikasi laporan dan menentukan kategori kekerasan.</p>
      </div>
      <div class="alur-step">
        <div class="step-number">3</div>
        <h3>Tindak Lanjut</h3>
        <p>Kasus diproses dan korban mendapatkan pendampingan psikologis & hukum.</p>
      </div>
      <div class="alur-step">
        <div class="step-number">4</div>
        <h3>Pelacakan Status</h3>
        <p>Pelapor dapat memantau status hingga kasus dinyatakan selesai.</p>
      </div>
    </div>
  </section>

  <footer>
    <div class="footer-content">
      <p>&copy; <?= date('Y') ?> Portal PPKS - Semua Hak Dilindungi</p>
      <div class="footer-icons">
        <a href="tel:119">📞 Hotline: 119</a>
        <a href="<?= BASE_URL ?>edukasi.php#kontak">⚖️ Kontak Darurat</a>
      </div>
    </div>
  </footer>

</body>
</html>