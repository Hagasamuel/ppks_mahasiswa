<?php
require_once '../config/database.php';
require_once '../includes/helpers.php';

requireLogin();

if ($_SESSION['user_role'] !== 'mahasiswa') {
    redirect(BASE_URL . 'admin/dashboard.php');
}

// Ambil laporan milik user ini (dengan NIM sama, atau dari session)
// Mahasiswa hanya bisa lihat laporan yang pakai NIM-nya
$stmt = $pdo->prepare("SELECT l.*, k.nama AS kategori
                       FROM laporan l
                       JOIN kategori k ON l.kategori_id = k.id
                       WHERE l.nim = ?
                       ORDER BY l.created_at DESC");
$stmt->execute([$_SESSION['user_nim']]);
$laporan = $stmt->fetchAll();

// Statistik pribadi
$total = count($laporan);
$selesai = count(array_filter($laporan, fn($l) => $l['status'] === 'selesai'));
$proses = count(array_filter($laporan, fn($l) => in_array($l['status'], ['diverifikasi','proses'])));
?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Laporan Saya - Portal Layanan PPKS</title>
<link rel="stylesheet" href="<?= BASE_URL ?>assets/css/landing.css">
<style>
  .lap-wrap { max-width: 1100px; margin: 40px auto; padding: 0 25px; }
  .lap-header {
    display: flex; justify-content: space-between;
    align-items: center; margin-bottom: 24px;
    flex-wrap: wrap; gap: 12px;
  }
  .lap-header h1 { color: #102a43; font-size: 1.5rem; font-weight: 800; }
  .lap-header .sub { color: #627d98; font-size: 0.9rem; margin-top: 4px; }
  .btn-new {
    padding: 10px 20px; background: #1261c9; color: #fff;
    border-radius: 9px; text-decoration: none;
    font-weight: 700; font-size: 0.85rem; transition: 0.2s;
  }
  .btn-new:hover { background: #0d54b0; }
  .stat-grid {
    display: grid; grid-template-columns: repeat(3, 1fr);
    gap: 14px; margin-bottom: 24px;
  }
  @media (max-width: 600px) { .stat-grid { grid-template-columns: 1fr; } }
  .stat-card {
    background: #fff; padding: 20px; border-radius: 14px;
    box-shadow: 0 4px 15px rgba(15, 35, 60, 0.06);
    border: 1px solid #e4ebf2;
  }
  .stat-card .num {
    font-size: 1.8rem; font-weight: 800;
    color: #1261c9; display: block; margin-bottom: 4px;
  }
  .stat-card .lbl {
    font-size: 0.78rem; color: #627d98; font-weight: 600;
    text-transform: uppercase; letter-spacing: 0.5px;
  }
  .table-wrap {
    background: #fff; border-radius: 14px;
    box-shadow: 0 4px 15px rgba(15, 35, 60, 0.06);
    border: 1px solid #e4ebf2; overflow: hidden;
  }
  table { width: 100%; border-collapse: collapse; }
  thead { background: #f7fafd; }
  th, td {
    padding: 14px 16px; text-align: left;
    border-bottom: 1px solid #f0f4f9; font-size: 0.85rem;
  }
  th {
    color: #334e68; font-weight: 700;
    font-size: 0.78rem; text-transform: uppercase;
    letter-spacing: 0.5px;
  }
  tbody tr:hover { background: #f9fbfd; }
  tbody tr:last-child td { border-bottom: none; }
  .badge {
    display: inline-block; padding: 4px 12px;
    border-radius: 12px; font-size: 0.72rem;
    font-weight: 700; color: #fff;
  }
  .btn-view {
    padding: 6px 14px; background: #1261c9; color: #fff;
    border-radius: 7px; text-decoration: none;
    font-size: 0.78rem; font-weight: 600;
    transition: 0.2s; display: inline-block;
  }
  .btn-view:hover { background: #0d54b0; }
  .empty {
    text-align: center; padding: 60px 20px; color: #829ab1;
  }
  .empty-icon { font-size: 3rem; margin-bottom: 10px; }
  .empty .btn-new { margin-top: 16px; display: inline-block; }
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
      <li><a href="<?= BASE_URL ?>user/lapor.php">Buat Laporan</a></li>
      <li><a href="<?= BASE_URL ?>user/laporan-saya.php" class="active">Laporan Saya</a></li>
      <li><a href="<?= BASE_URL ?>user/cek_status.php">Cek Status</a></li>
      <li><a href="<?= BASE_URL ?>auth/logout.php">Logout (<?= sanitize($_SESSION['user_nama']) ?>)</a></li>
    </ul>
  </div>
</header>

<div class="lap-wrap">

  <div class="lap-header">
    <div>
      <h1>Laporan Saya</h1>
      <p class="sub">Daftar semua laporan yang pernah Anda buat</p>
    </div>
    <a href="<?= BASE_URL ?>user/lapor.php" class="btn-new">+ Buat Laporan Baru</a>
  </div>

  <div class="stat-grid">
    <div class="stat-card">
      <span class="num"><?= $total ?></span>
      <span class="lbl">Total Laporan</span>
    </div>
    <div class="stat-card">
      <span class="num"><?= $proses ?></span>
      <span class="lbl">Sedang Diproses</span>
    </div>
    <div class="stat-card">
      <span class="num"><?= $selesai ?></span>
      <span class="lbl">Selesai</span>
    </div>
  </div>

  <div class="table-wrap">
    <?php if ($laporan): ?>
      <table>
        <thead>
          <tr>
            <th>Kode</th>
            <th>Judul</th>
            <th>Kategori</th>
            <th>Status</th>
            <th>Tanggal</th>
            <th>Aksi</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($laporan as $l): ?>
            <tr>
              <td><strong><?= sanitize($l['kode_tracking']) ?></strong></td>
              <td><?= sanitize($l['judul']) ?></td>
              <td><?= sanitize($l['kategori']) ?></td>
              <td><?= statusBadge($l['status']) ?></td>
              <td><?= tglIndo($l['created_at']) ?></td>
              <td>
                <a href="<?= BASE_URL ?>user/cek_status.php?kode=<?= $l['kode_tracking'] ?>" class="btn-view">Lihat</a>
              </td>
            </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    <?php else: ?>
      <div class="empty">
        <div class="empty-icon">📭</div>
        <p>Anda belum pernah membuat laporan.</p>
        <a href="<?= BASE_URL ?>user/lapor.php" class="btn-new">Buat Laporan Pertama</a>
      </div>
    <?php endif; ?>
  </div>

</div>

</body>
</html>