<?php
require_once '../config/database.php';
require_once '../includes/helpers.php';

requireLogin();

if ($_SESSION['user_role'] !== 'admin') {
    redirect(BASE_URL . 'user/lapor.php');
}

$id = (int)($_GET['id'] ?? 0);
$stmt = $pdo->prepare("SELECT l.*, k.nama AS kategori FROM laporan l
                       JOIN kategori k ON l.kategori_id=k.id WHERE l.id=?");
$stmt->execute([$id]);
$l = $stmt->fetch();

if (!$l) {
    die('Laporan tidak ditemukan. <a href="dashboard.php">Kembali</a>');
}

$sukses = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $statusBaru = $_POST['status'];
    $catatan    = sanitize($_POST['catatan']);
    $prioritas  = $_POST['prioritas'];

    $pdo->beginTransaction();
    $pdo->prepare("UPDATE laporan SET status=?, catatan_admin=?, prioritas=?,
                   ditangani_oleh=? WHERE id=?")
        ->execute([$statusBaru, $catatan, $prioritas, $_SESSION['user_id'], $id]);
    $pdo->prepare("INSERT INTO riwayat_status
                   (laporan_id, status_lama, status_baru, catatan, diubah_oleh)
                   VALUES (?,?,?,?,?)")
        ->execute([$id, $l['status'], $statusBaru, $catatan, $_SESSION['user_id']]);
    $pdo->commit();
    logAudit($pdo, 'update_status', "Laporan #$id -> $statusBaru");

    // Refresh data
    $stmt->execute([$id]);
    $l = $stmt->fetch();
    $sukses = 'Status berhasil diperbarui.';
}

// Ambil bukti
$bukti = $pdo->prepare("SELECT * FROM bukti WHERE laporan_id=?");
$bukti->execute([$id]); $bukti = $bukti->fetchAll();

// Ambil riwayat
$riwayat = $pdo->prepare("SELECT r.*, u.nama FROM riwayat_status r
                          LEFT JOIN users u ON r.diubah_oleh=u.id
                          WHERE r.laporan_id=? ORDER BY r.created_at");
$riwayat->execute([$id]); $riwayat = $riwayat->fetchAll();
?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Detail Laporan - Portal Layanan PPKS</title>
<link rel="stylesheet" href="<?= BASE_URL ?>assets/css/landing.css">
<style>
  .detail-wrap { max-width: 1150px; margin: 30px auto; padding: 0 25px; }
  .back-link {
    display: inline-flex; align-items: center; gap: 6px;
    color: #1261c9; font-weight: 600; font-size: 0.9rem;
    margin-bottom: 16px; text-decoration: none;
  }
  .back-link:hover { text-decoration: underline; }
  .detail-grid {
    display: grid; grid-template-columns: 1.4fr 1fr; gap: 20px;
  }
  @media (max-width: 900px) { .detail-grid { grid-template-columns: 1fr; } }
  .card {
    background: #fff; padding: 26px; border-radius: 14px;
    box-shadow: 0 4px 15px rgba(15, 35, 60, 0.06);
    border: 1px solid #e4ebf2; margin-bottom: 18px;
  }
  .card h3 {
    color: #102a43; font-size: 0.95rem; font-weight: 700;
    text-transform: uppercase; letter-spacing: 0.5px;
    margin-bottom: 14px; padding-bottom: 10px;
    border-bottom: 2px solid #f0f4f9;
  }
  .card h1 {
    color: #102a43; font-size: 1.3rem; font-weight: 800;
    margin-bottom: 16px;
  }
  .info-row { padding: 8px 0; border-bottom: 1px solid #f7fafd; font-size: 0.88rem; }
  .info-row:last-child { border-bottom: none; }
  .info-row strong { color: #627d98; display: inline-block; min-width: 130px; font-weight: 600; }
  .info-row span { color: #172033; }
  .kronologi {
    background: #f7fafd; padding: 16px; border-radius: 9px;
    border-left: 3px solid #1261c9; font-size: 0.9rem;
    color: #334e68; line-height: 1.8; margin-top: 10px;
  }
  .bukti-list { list-style: none; padding: 0; }
  .bukti-list li {
    padding: 10px 14px; background: #f7fafd;
    border-radius: 8px; margin-bottom: 8px;
    font-size: 0.85rem; border: 1px solid #e8edf3;
  }
  .bukti-list a { color: #1261c9; text-decoration: none; font-weight: 600; }
  .bukti-list a:hover { text-decoration: underline; }
  .form-card label {
    display: block; color: #334e68; font-size: 0.85rem;
    font-weight: 600; margin: 14px 0 6px;
  }
  .form-card select, .form-card textarea {
    width: 100%; padding: 11px 14px;
    border: 1px solid #cbdbea; border-radius: 9px;
    font-size: 0.9rem; background: #fff; color: #172033;
    font-family: inherit;
  }
  .form-card select:focus, .form-card textarea:focus {
    outline: none; border-color: #1261c9;
    box-shadow: 0 0 0 3px rgba(18, 97, 201, 0.12);
  }
  .form-card textarea { resize: vertical; }
  .form-btn {
    width: 100%; margin-top: 18px; padding: 13px;
    background: #1261c9; color: #fff; border: none;
    border-radius: 9px; font-weight: 700; font-size: 0.9rem;
    cursor: pointer; transition: 0.2s;
  }
  .form-btn:hover { background: #0d54b0; }
  .alert {
    padding: 12px 16px; border-radius: 8px;
    font-size: 0.88rem; margin-bottom: 14px;
  }
  .alert.success { background: #d1fae5; color: #065f46; }
  .timeline { list-style: none; padding: 0; }
  .timeline li {
    border-left: 3px solid #1261c9;
    padding: 8px 0 14px 16px; margin-bottom: 6px;
  }
  .timeline li small {
    color: #829ab1; display: block;
    font-size: 0.75rem; margin-top: 4px;
  }
  .timeline li p { font-size: 0.85rem; margin-top: 6px; color: #52606d; }
  .badge {
    display: inline-block; padding: 4px 12px;
    border-radius: 12px; font-size: 0.72rem;
    font-weight: 700; color: #fff;
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
      <li><a href="<?= BASE_URL ?>admin/dashboard.php" class="active">Dashboard</a></li>
      <li><a href="<?= BASE_URL ?>auth/logout.php">Logout (<?= sanitize($_SESSION['user_nama']) ?>)</a></li>
    </ul>
  </div>
</header>

<div class="detail-wrap">
  <a href="<?= BASE_URL ?>admin/dashboard.php" class="back-link">← Kembali ke Dashboard</a>

  <?php if ($sukses): ?>
    <div class="alert success">✅ <?= $sukses ?></div>
  <?php endif; ?>

  <div class="detail-grid">

    <!-- KIRI: Detail Laporan -->
    <div>
      <div class="card">
        <h1><?= sanitize($l['judul']) ?></h1>
        <div class="info-row"><strong>Kode:</strong> <span><?= sanitize($l['kode_tracking']) ?></span></div>
        <div class="info-row"><strong>Kategori:</strong> <span><?= sanitize($l['kategori']) ?></span></div>
        <div class="info-row"><strong>Status:</strong> <?= statusBadge($l['status']) ?></div>
        <div class="info-row"><strong>Prioritas:</strong> <span><?= ucfirst($l['prioritas']) ?></span></div>
        <div class="info-row"><strong>Waktu Kejadian:</strong> <span><?= $l['waktu_kejadian'] ?: '-' ?></span></div>
        <div class="info-row"><strong>Lokasi:</strong> <span><?= sanitize($l['lokasi_kejadian']) ?: '-' ?></span></div>
        <div class="info-row"><strong>Terlapor:</strong> <span><?= sanitize($l['terlapor']) ?: '-' ?></span></div>
        <div class="info-row"><strong>Dilaporkan:</strong> <span><?= tglIndo($l['created_at']) ?></span></div>
      </div>

      <div class="card">
        <h3>Kronologi</h3>
        <div class="kronologi"><?= nl2br(sanitize($l['kronologi'])) ?></div>
      </div>

      <?php if (!$l['anonim']): ?>
        <div class="card">
          <h3>Identitas Pelapor</h3>
          <div class="info-row"><strong>Nama:</strong> <span><?= sanitize($l['nama_pelapor']) ?></span></div>
          <div class="info-row"><strong>NIM:</strong> <span><?= sanitize($l['nim']) ?: '-' ?></span></div>
          <div class="info-row"><strong>Email:</strong> <span><?= sanitize($l['email_pelapor']) ?: '-' ?></span></div>
          <div class="info-row"><strong>Telp:</strong> <span><?= sanitize($l['telp_pelapor']) ?: '-' ?></span></div>
        </div>
      <?php else: ?>
        <div class="card" style="text-align:center;color:#829ab1">
          <em>Pelapor memilih anonim</em>
        </div>
      <?php endif; ?>

      <div class="card">
        <h3>Bukti Pendukung</h3>
        <?php if ($bukti): ?>
          <ul class="bukti-list">
            <?php foreach ($bukti as $b): ?>
              <li>
                📎 <a href="<?= UPLOAD_URL . $b['nama_file_simpan'] ?>" target="_blank">
                  <?= sanitize($b['nama_file_asli']) ?>
                </a>
                <span style="color:#829ab1;font-size:0.78rem">(<?= round($b['ukuran']/1024) ?> KB)</span>
              </li>
            <?php endforeach; ?>
          </ul>
        <?php else: ?>
          <p style="color:#829ab1;font-size:0.88rem">Tidak ada bukti dilampirkan.</p>
        <?php endif; ?>
      </div>
    </div>

    <!-- KANAN: Update Status + Riwayat -->
    <div>
      <div class="card form-card">
        <h3>Update Penanganan</h3>
        <form method="POST">
          <label>Status</label>
          <select name="status">
            <?php foreach (['diterima','diverifikasi','proses','selesai','ditolak','dialihkan'] as $s): ?>
              <option value="<?= $s ?>" <?= $l['status']===$s?'selected':'' ?>>
                <?= ucfirst($s) ?>
              </option>
            <?php endforeach; ?>
          </select>

          <label>Prioritas</label>
          <select name="prioritas">
            <?php foreach (['rendah','sedang','tinggi'] as $p): ?>
              <option value="<?= $p ?>" <?= $l['prioritas']===$p?'selected':'' ?>>
                <?= ucfirst($p) ?>
              </option>
            <?php endforeach; ?>
          </select>

          <label>Catatan</label>
          <textarea name="catatan" rows="4" placeholder="Catatan tindak lanjut..."><?= sanitize($l['catatan_admin']) ?></textarea>

          <button type="submit" class="form-btn">💾 Simpan Perubahan</button>
        </form>
      </div>

      <div class="card">
        <h3>Riwayat Penanganan</h3>
        <ul class="timeline">
          <?php foreach ($riwayat as $r): ?>
            <li>
              <?= statusBadge($r['status_baru']) ?>
              <small><?= tglIndo($r['created_at']) ?> — oleh <?= sanitize($r['nama'] ?? 'Sistem') ?></small>
              <?php if ($r['catatan']): ?>
                <p><?= sanitize($r['catatan']) ?></p>
              <?php endif; ?>
            </li>
          <?php endforeach; ?>
        </ul>
      </div>
    </div>

  </div>
</div>

</body>
</html>