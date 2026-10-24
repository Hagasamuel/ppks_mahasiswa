<?php
require_once '../config/database.php';
require_once '../includes/helpers.php';

requireLogin();

$laporan = $riwayat = null;
$error = '';
$kode = $_GET['kode'] ?? '';

if ($kode) {
    $stmt = $pdo->prepare("SELECT l.*, k.nama AS kategori
                           FROM laporan l JOIN kategori k ON l.kategori_id=k.id
                           WHERE l.kode_tracking = ?");
    $stmt->execute([$kode]);
    $laporan = $stmt->fetch();

    if ($laporan) {
        $r = $pdo->prepare("SELECT status_baru, catatan, created_at
                            FROM riwayat_status WHERE laporan_id=?
                            ORDER BY created_at ASC");
        $r->execute([$laporan['id']]);
        $riwayat = $r->fetchAll();
    } else {
        $error = 'Kode pelacakan tidak ditemukan. Periksa kembali kode Anda.';
    }
}

function badgeStatus($status) {
    $map = [
        'diterima'     => ['Diterima', '#3b82f6'],
        'diverifikasi' => ['Diverifikasi', '#8b5cf6'],
        'proses'       => ['Dalam Proses', '#f59e0b'],
        'selesai'      => ['Selesai', '#10b981'],
        'ditolak'      => ['Ditolak', '#ef4444'],
        'dialihkan'    => ['Dialihkan', '#6b7280'], 
    ];
    [$label, $color] = $map[$status] ?? [$status, '#888'];
    return "<span style='background:$color;color:#fff;padding:4px 12px;border-radius:12px;font-size:0.75rem;font-weight:600'>$label</span>";
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Cek Status - Portal Layanan PPKS</title>
<link rel="stylesheet" href="<?= BASE_URL ?>assets/css/landing.css">
<style>
  .cek-wrap { max-width: 720px; margin: 40px auto; padding: 0 20px; }
  .cek-card { background: #fff; padding: 32px; border-radius: 16px;
    box-shadow: 0 10px 30px rgba(15, 35, 60, 0.08); border: 1px solid #e4ebf2;
    margin-bottom: 20px; }
  .cek-card h1 { color: #102a43; font-size: 1.5rem;
    font-weight: 800; margin-bottom: 6px; }
  .cek-card .sub { color: #627d98; font-size: 0.9rem; margin-bottom: 20px; }
  .cek-card label { display: block; color: #334e68; font-size: 0.85rem;
    font-weight: 600; margin: 14px 0 6px; }
  .cek-card input[type=text] { width: 100%; padding: 11px 14px;
    border: 1px solid #cbdbea; border-radius: 9px;
    font-size: 0.9rem; background: #fff; color: #172033; }
  .cek-card input[type=text]:focus { outline: none; border-color: #1261c9;
    box-shadow: 0 0 0 3px rgba(18, 97, 201, 0.12); }
  .cek-btn { margin-top: 18px; padding: 12px 24px;
    background: #1261c9; color: #fff; border: none;
    border-radius: 9px; font-weight: 700; font-size: 0.9rem;
    cursor: pointer; transition: 0.2s; }
  .cek-btn:hover { background: #0d54b0; }
  .alert { padding: 12px 16px; border-radius: 8px;
    font-size: 0.9rem; margin-bottom: 14px; }
  .alert.error { background: #fee2e2; color: #991b1b; }
  .info-row { padding: 10px 0; border-bottom: 1px solid #f0f4f9; }
  .info-row strong { color: #334e68; display: inline-block;
    min-width: 140px; font-size: 0.9rem; }
  .info-row span { color: #172033; font-size: 0.9rem; }
  .timeline { list-style: none; padding-left: 0; margin-top: 10px; }
  .timeline li { border-left: 3px solid #1261c9;
    padding: 8px 0 14px 16px; margin-bottom: 6px;
    position: relative; }
  .timeline li small { color: #829ab1; display: block;
    font-size: 0.75rem; margin-top: 4px; }
  .timeline li p { font-size: 0.85rem; margin-top: 4px; color: #52606d; }
  .kronologi-box { background: #f7fafd; padding: 14px;
    border-radius: 9px; border-left: 3px solid #1261c9;
    font-size: 0.9rem; color: #334e68; line-height: 1.7; margin-top: 8px; }
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
      <li><a href="<?= BASE_URL ?>user/laporan_saya.php">Laporan Saya</a></li>  
      <li><a href="<?= BASE_URL ?>user/cek_status.php" class="active">Cek Status</a></li>
      <li><a href="<?= BASE_URL ?>auth/logout.php">Logout (<?= sanitize($_SESSION['user_nama']) ?>)</a></li>
    </ul>
  </div>
</header>

<div class="cek-wrap">
  <div class="cek-card">
    <h1>Cek Status Laporan</h1>
    <p class="sub">Masukkan kode pelacakan untuk melihat perkembangan laporan Anda</p>

    <form method="GET">
      <label>Kode Pelacakan</label>
      <input type="text" name="kode" placeholder="Contoh: PPKS-A1B2C3D4"
             value="<?= sanitize($kode) ?>" required>
      <button type="submit" class="cek-btn">🔍 Cek Status</button>
    </form>
  </div>

  <?php if ($error): ?>
    <div class="alert error"><?= $error ?></div>
  <?php endif; ?>

  <?php if ($laporan): ?>
    <div class="cek-card">
      <h1><?= sanitize($laporan['judul']) ?></h1>
      <p class="sub">Detail laporan Anda</p>

      <div class="info-row"><strong>Kode:</strong> <span><?= sanitize($laporan['kode_tracking']) ?></span></div>
      <div class="info-row"><strong>Kategori:</strong> <span><?= sanitize($laporan['kategori']) ?></span></div>
      <div class="info-row"><strong>Status:</strong> <?= badgeStatus($laporan['status']) ?></div>
      <div class="info-row"><strong>Dilaporkan:</strong> <span><?= tglIndo($laporan['created_at']) ?></span></div>

      <h3 style="color:#102a43;font-size:1rem;font-weight:700;margin:20px 0 8px">Kronologi</h3>
      <div class="kronologi-box"><?= nl2br(sanitize($laporan['kronologi'])) ?></div>

      <h3 style="color:#102a43;font-size:1rem;font-weight:700;margin:20px 0 8px">Riwayat Penanganan</h3>
      <ul class="timeline">
        <?php foreach ($riwayat as $r): ?>
          <li>
            <?= badgeStatus($r['status_baru']) ?>
            <small><?= tglIndo($r['created_at']) ?></small>
            <?php if ($r['catatan']): ?>
              <p><?= sanitize($r['catatan']) ?></p>
            <?php endif; ?>
          </li>
        <?php endforeach; ?>
      </ul>
    </div>
  <?php endif; ?>
</div>

</body>
</html>