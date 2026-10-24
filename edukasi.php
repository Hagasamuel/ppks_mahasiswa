<?php
require_once 'config/database.php';
require_once 'includes/helpers.php';
?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Edukasi & Kontak Darurat - Portal Layanan PPKS</title>
<link rel="stylesheet" href="<?= BASE_URL ?>assets/css/landing.css">
<style>
  .edu-wrap { max-width: 900px; margin: 40px auto; padding: 0 25px; }
  .edu-hero {
    background: linear-gradient(135deg, #1261c9, #0d54b0);
    color: #fff; padding: 50px 30px; border-radius: 16px;
    text-align: center; margin-bottom: 30px;
  }
  .edu-hero h1 { font-size: 2rem; font-weight: 800; margin-bottom: 10px; }
  .edu-hero p { font-size: 1rem; opacity: 0.95; max-width: 600px; margin: auto; }
  .edu-card {
    background: #fff; padding: 28px; border-radius: 14px;
    box-shadow: 0 4px 15px rgba(15, 35, 60, 0.06);
    border: 1px solid #e4ebf2; margin-bottom: 20px;
  }
  .edu-card h3 {
    color: #102a43; font-size: 1.1rem; font-weight: 800;
    margin-bottom: 14px; padding-bottom: 10px;
    border-bottom: 2px solid #f0f4f9;
  }
  .edu-card ol, .edu-card ul {
    margin-left: 22px; color: #334e68;
    font-size: 0.9rem; line-height: 1.9;
  }
  .edu-card li { padding: 3px 0; }
  .kontak-list { list-style: none; margin: 0; padding: 0; }
  .kontak-list li {
    display: flex; align-items: center; gap: 14px;
    padding: 14px 16px; background: #f7fafd;
    border-radius: 10px; margin-bottom: 10px;
    border: 1px solid #e8edf3;
  }
  .kontak-icon {
    width: 42px; height: 42px; display: flex;
    align-items: center; justify-content: center;
    background: #e8f2ff; border-radius: 10px;
    font-size: 20px; flex-shrink: 0;
  }
  .kontak-info strong { color: #102a43; display: block; font-size: 0.9rem; }
  .kontak-info span { color: #627d98; font-size: 0.85rem; }
  .alert-box {
    background: #fff4df; border-left: 4px solid #f59e0b;
    padding: 14px 18px; border-radius: 8px;
    color: #92400e; font-size: 0.88rem; margin-top: 12px;
  }
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
      <li><a href="<?= BASE_URL ?>edukasi.php" class="active">Edukasi</a></li>
      <li><a href="<?= BASE_URL ?>user/cek_status.php">Lacak Laporan</a></li>
      <?php if (isLoggedIn()): ?>
        <li><a href="<?= BASE_URL ?>auth/logout.php">Logout (<?= sanitize($_SESSION['user_nama']) ?>)</a></li>
      <?php else: ?>
        <li><a href="<?= BASE_URL ?>auth/login.php">Login</a></li>
        <li><a href="<?= BASE_URL ?>auth/daftar.php">Daftar</a></li>
      <?php endif; ?>
    </ul>
  </div>
</header>

<div class="edu-wrap">

  <div class="edu-hero">
    <h1>📚 Edukasi & Kontak Darurat</h1>
    <p>Pelajari prosedur pelaporan, hak-hak Anda sebagai pelapor, dan layanan pendampingan yang tersedia.</p>
  </div>

  <div class="edu-card">
    <h3>📋 Prosedur Pelaporan</h3>
    <ol>
      <li>Login atau daftar akun mahasiswa terlebih dahulu.</li>
      <li>Isi formulir pelaporan (anonim atau teridentifikasi).</li>
      <li>Lampirkan bukti pendukung jika ada (foto, dokumen, dll).</li>
      <li>Simpan kode pelacakan yang Anda terima.</li>
      <li>Satgas PPKS akan memverifikasi & menindaklanjuti laporan.</li>
      <li>Pantau status via menu <strong>Cek Status</strong>.</li>
    </ol>
  </div>

  <div class="edu-card">
    <h3>🛡️ Hak-Hak Pelapor</h3>
    <ul>
      <li><strong>Kerahasiaan identitas</strong> — identitas Anda dilindungi.</li>
      <li><strong>Pendampingan</strong> — Anda berhak mendapat pendampingan psikologis & hukum.</li>
      <li><strong>Perlindungan</strong> — Anda dilindungi dari ancaman atau balasan.</li>
      <li><strong>Informasi</strong> — Anda berhak tahu perkembangan kasus Anda.</li>
      <li><strong>Non-diskriminasi</strong> — pelayanan tanpa memandang latar belakang.</li>
    </ul>
  </div>

  <div class="edu-card" id="kontak">
    <h3>📞 Kontak Darurat & Pendampingan</h3>
    <ul class="kontak-list">
      <li>
        <div class="kontak-icon">🛡️</div>
        <div class="kontak-info">
          <strong>Satgas PPKS Kampus</strong>
          <span>0812-xxxx-xxxx</span>
        </div>
      </li>
      <li>
        <div class="kontak-icon">💬</div>
        <div class="kontak-info">
          <strong>Konseling Kampus</strong>
          <span>konseling@kampus.ac.id</span>
        </div>
      </li>
      <li>
        <div class="kontak-icon">⚖️</div>
        <div class="kontak-info">
          <strong>Komnas Perempuan</strong>
          <span>021-3903963</span>
        </div>
      </li>
      <li>
        <div class="kontak-icon">📞</div>
        <div class="kontak-info">
          <strong>SAPA129 (KPPPA)</strong>
          <span>Telepon: 129</span>
        </div>
      </li>
      <li>
        <div class="kontak-icon">🧠</div>
        <div class="kontak-info">
          <strong>Layanan Psikologi Kampus</strong>
          <span>0813-xxxx-xxxx</span>
        </div>
      </li>
    </ul>
    <div class="alert-box">
      ⚠️ <strong>Dalam bahaya langsung?</strong> Segera hubungi <strong>110</strong> (polisi) atau layanan darurat terdekat.
    </div>
  </div>

  <div class="edu-card">
    <h3>❓ Pertanyaan Umum</h3>
    <p style="color:#334e68;font-size:0.9rem;line-height:1.8">
      <strong>Apakah laporan saya benar-benar rahasia?</strong><br>
      Ya. Identitas pelapor hanya dapat diakses oleh Satgas berwenang, dan hanya jika Anda memilih untuk tidak anonim.<br><br>

      <strong>Apakah saya bisa melapor tanpa bukti?</strong><br>
      Bisa. Bukti bersifat opsional. Namun, bukti dapat memperkuat laporan Anda.<br><br>

      <strong>Berapa lama laporan diproses?</strong><br>
      Setiap laporan diverifikasi maksimal 3 hari kerja, dan ditindaklanjuti sesuai tingkat urgensi kasus.
    </p>
  </div>

</div>

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