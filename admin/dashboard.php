<?php
require_once '../config/database.php';
require_once '../includes/helpers.php';

requireLogin();

// Proteksi: hanya admin yang boleh masuk
if ($_SESSION['user_role'] !== 'admin') {
    redirect(BASE_URL . 'user/lapor.php');
}

// Filter
$status   = $_GET['status']   ?? '';
$kategori = $_GET['kategori'] ?? '';

$sql = "SELECT l.*, k.nama AS kategori, u.nama AS penanggung
        FROM laporan l
        JOIN kategori k ON l.kategori_id = k.id
        LEFT JOIN users u ON l.ditangani_oleh = u.id
        WHERE 1=1";
$params = [];
if ($status)   { $sql .= " AND l.status=?";      $params[] = $status; }
if ($kategori) { $sql .= " AND l.kategori_id=?"; $params[] = $kategori; }
$sql .= " ORDER BY l.created_at DESC";

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$laporan = $stmt->fetchAll();

// Statistik
$stat = $pdo->query("SELECT status, COUNT(*) c FROM laporan GROUP BY status")
            ->fetchAll(PDO::FETCH_KEY_PAIR);
$total = array_sum($stat);
$kategoriList = $pdo->query("SELECT * FROM kategori")->fetchAll();
?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Dashboard Admin - Portal Layanan PPKS</title>
<link rel="stylesheet" href="<?= BASE_URL ?>assets/css/landing.css">
<style>
  .dash-wrap { max-width: 1250px; margin: 30px auto; padding: 0 25px; }
  .dash-header {
    display: flex; justify-content: space-between;
    align-items: center; margin-bottom: 24px; flex-wrap: wrap; gap: 12px;
  }
  .dash-header h1 {
    color: #102a43; font-size: 1.6rem; font-weight: 800;
  }
  .dash-header .welcome {
    color: #627d98; font-size: 0.9rem;
  }
  .stat-grid {
    display: grid; grid-template-columns: repeat(5, 1fr);
    gap: 14px; margin-bottom: 24px;
  }
  @media (max-width: 900px) { .stat-grid { grid-template-columns: repeat(2, 1fr); } }
  .stat-card {
    background: #fff; padding: 20px; border-radius: 14px;
    box-shadow: 0 4px 15px rgba(15, 35, 60, 0.06);
    border: 1px solid #e4ebf2; text-align: left;
  }
  .stat-card .num {
    font-size: 1.8rem; font-weight: 800; color: #1261c9;
    display: block; margin-bottom: 4px;
  }
  .stat-card .lbl {
    font-size: 0.78rem; color: #627d98; font-weight: 600;
    text-transform: uppercase; letter-spacing: 0.5px;
  }
  .stat-card.total .num { color: #102a43; }
  .filter-bar {
    background: #fff; padding: 16px 20px; border-radius: 14px;
    box-shadow: 0 4px 15px rgba(15, 35, 60, 0.06);
    border: 1px solid #e4ebf2; margin-bottom: 18px;
    display: flex; gap: 12px; flex-wrap: wrap; align-items: center;
  }
  .filter-bar select {
    padding: 9px 14px; border: 1px solid #cbdbea;
    border-radius: 9px; font-size: 0.85rem;
    background: #fff; color: #172033; min-width: 180px;
  }
  .filter-bar select:focus {
    outline: none; border-color: #1261c9;
    box-shadow: 0 0 0 3px rgba(18, 97, 201, 0.12);
  }
  .btn-filter {
    padding: 9px 20px; background: #1261c9; color: #fff;
    border: none; border-radius: 9px; font-weight: 700;
    font-size: 0.85rem; cursor: pointer; transition: 0.2s;
  }
  .btn-filter:hover { background: #0d54b0; }
  .btn-reset {
    padding: 9px 20px; background: #f1f6fb; color: #52606d;
    border: 1px solid #cbdbea; border-radius: 9px;
    font-weight: 600; font-size: 0.85rem;
    text-decoration: none; transition: 0.2s;
  }
  .btn-reset:hover { background: #e4ebf2; }
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
      <li><a href="<?= BASE_URL ?>admin/dashboard.php" class="active">Dashboard</a></li>
      <li><a href="<?= BASE_URL ?>auth/logout.php">Logout (<?= sanitize($_SESSION['user_nama']) ?>)</a></li>
    </ul>
  </div>
</header>

<div class="dash-wrap">

  <div class="dash-header">
    <div>
      <h1>Dashboard Admin PPKS</h1>
      <p class="welcome">Selamat datang, <strong><?= sanitize($_SESSION['user_nama']) ?></strong> — kelola laporan kekerasan di sini.</p>
    </div>
  </div>

  <div class="stat-grid">
    <div class="stat-card total">
      <span class="num"><?= $total ?></span>
      <span class="lbl">Total Laporan</span>
    </div>
    <div class="stat-card">
      <span class="num"><?= $stat['diterima'] ?? 0 ?></span>
      <span class="lbl">Diterima</span>
    </div>
    <div class="stat-card">
      <span class="num"><?= $stat['diverifikasi'] ?? 0 ?></span>
      <span class="lbl">Diverifikasi</span>
    </div>
    <div class="stat-card">
      <span class="num"><?= $stat['proses'] ?? 0 ?></span>
      <span class="lbl">Dalam Proses</span>
    </div>
    <div class="stat-card">
      <span class="num"><?= $stat['selesai'] ?? 0 ?></span>
      <span class="lbl">Selesai</span>
    </div>
  </div>

  <form method="GET" class="filter-bar">
    <select name="status">
      <option value="">-- Semua Status --</option>
      <?php foreach (['diterima','diverifikasi','proses','selesai','ditolak','dialihkan'] as $s): ?>
        <option value="<?= $s ?>" <?= $status===$s?'selected':'' ?>><?= ucfirst($s) ?></option>
      <?php endforeach; ?>
    </select>
    <select name="kategori">
      <option value="">-- Semua Kategori --</option>
      <?php foreach ($kategoriList as $k): ?>
        <option value="<?= $k['id'] ?>" <?= $kategori==$k['id']?'selected':'' ?>>
          <?= sanitize($k['nama']) ?>
        </option>
      <?php endforeach; ?>
    </select>
    <button type="submit" class="btn-filter">🔍 Filter</button>
    <a href="<?= BASE_URL ?>admin/dashboard.php" class="btn-reset">Reset</a>
  </form>

  <div class="table-wrap">
    <?php if ($laporan): ?>
      <table>
        <thead>
          <tr>
            <th>Kode</th>
            <th>Judul</th>
            <th>Kategori</th>
            <th>Pelapor</th>
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
              <td>
                <?php if ($l['anonim']): ?>
                  <em style="color:#829ab1">Anonim</em>
                <?php else: ?>
                  <?= sanitize($l['nama_pelapor']) ?>
                <?php endif; ?>
              </td>
              <td><?= statusBadge($l['status']) ?></td>
              <td><?= tglIndo($l['created_at']) ?></td>
              <td><a href="<?= BASE_URL ?>admin/detail.php?id=<?= $l['id'] ?>" class="btn-view">Detail</a></td>
            </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    <?php else: ?>
      <div class="empty">
        <div class="empty-icon">📭</div>
        <p>Belum ada laporan masuk.</p>
      </div>
    <?php endif; ?>
  </div>

</div>

</body>
</html>