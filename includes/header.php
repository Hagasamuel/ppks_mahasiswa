<?php
// Pastikan helpers & database sudah di-require oleh file pemanggil
?>
<header class="navbar">
  <div class="nav-inner">
    <a href="<?= BASE_URL ?>" class="brand">🛡️ LaporPPKS</a>
    <nav>
      <a href="<?= BASE_URL ?>index.php">Beranda</a>
      <a href="<?= BASE_URL ?>lapor.php">Buat Laporan</a>
      <a href="<?= BASE_URL ?>cek-status.php">Cek Status</a>
      <a href="<?= BASE_URL ?>edukasi.php">Edukasi</a>
      <?php if (isLoggedIn()): ?>
        <a href="<?= BASE_URL ?>admin/dashboard.php">Dashboard</a>
        <a href="<?= BASE_URL ?>admin/logout.php">Logout</a>
      <?php else: ?>
        <a href="<?= BASE_URL ?>login.php">Login Admin</a>
      <?php endif; ?>
    </nav>
  </div>
</header>