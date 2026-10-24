<?php
require_once '../config/database.php';
require_once '../includes/helpers.php';

requireLogin();

$kategori = $pdo->query("SELECT * FROM kategori WHERE aktif=1")->fetchAll();
$sukses = $error = '';
$kode = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $anonim      = isset($_POST['anonim']) ? 1 : 0;
        $kategori_id = (int)$_POST['kategori_id'];
        $judul       = sanitize($_POST['judul']);
        $kronologi   = sanitize($_POST['kronologi']);
        $lokasi      = sanitize($_POST['lokasi'] ?? '');
        $waktu       = $_POST['waktu'] ?: null;
        $terlapor    = sanitize($_POST['terlapor'] ?? '');

        if (!$kategori_id || !$judul || !$kronologi) {
            throw new Exception('Field bertanda * wajib diisi.');
        }

        $kode = generateTrackingCode();

        $nama = $anonim ? null : sanitize($_POST['nama'] ?? $_SESSION['user_nama']);
        $nim  = $anonim ? null : sanitize($_POST['nim'] ?? $_SESSION['user_nim']);
        $mail = $anonim ? null : sanitize($_POST['email'] ?? '');
        $telp = $anonim ? null : sanitize($_POST['telp'] ?? '');

        $pdo->beginTransaction();

        $stmt = $pdo->prepare("INSERT INTO laporan
          (kode_tracking, anonim, nama_pelapor, nim, email_pelapor, telp_pelapor,
           kategori_id, judul, kronologi, lokasi_kejadian, waktu_kejadian, terlapor)
          VALUES (?,?,?,?,?,?,?,?,?,?,?,?)");
        $stmt->execute([
            $kode, $anonim, $nama, $nim, $mail, $telp,
            $kategori_id, $judul, $kronologi, $lokasi, $waktu, $terlapor
        ]);
        $laporan_id = $pdo->lastInsertId();

        if (!empty($_FILES['bukti']['name'][0])) {
            $allowed = ['image/jpeg', 'image/png', 'application/pdf',
                        'application/msword',
                        'application/vnd.openxmlformats-officedocument.wordprocessingml.document'];
            if (!is_dir(UPLOAD_DIR)) mkdir(UPLOAD_DIR, 0755, true);

            foreach ($_FILES['bukti']['tmp_name'] as $i => $tmp) {
                if ($_FILES['bukti']['error'][$i] !== UPLOAD_ERR_OK) continue;
                if ($_FILES['bukti']['size'][$i] > MAX_FILE_SIZE) continue;
                $mime = mime_content_type($tmp);
                if (!in_array($mime, $allowed)) continue;

                $ext  = pathinfo($_FILES['bukti']['name'][$i], PATHINFO_EXTENSION);
                $simp = uniqid('ev_', true) . '.' . strtolower($ext);
                if (move_uploaded_file($tmp, UPLOAD_DIR . $simp)) {
                    $pdo->prepare("INSERT INTO bukti
                      (laporan_id, nama_file_asli, nama_file_simpan, tipe_file, ukuran)
                      VALUES (?,?,?,?,?)")
                      ->execute([$laporan_id, $_FILES['bukti']['name'][$i],
                                 $simp, $mime, $_FILES['bukti']['size'][$i]]);
                }
            }
        }

        $pdo->prepare("INSERT INTO riwayat_status (laporan_id, status_baru, catatan)
                       VALUES (?,?,?)")
            ->execute([$laporan_id, 'diterima', 'Laporan diterima sistem']);

        $pdo->commit();
        logAudit($pdo, 'buat_laporan', "Kode: $kode");
        $sukses = true;
    } catch (Exception $e) {
        if ($pdo->inTransaction()) $pdo->rollBack();
        $error = $e->getMessage();
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Buat Laporan - Portal Layanan PPKS</title>
<link rel="stylesheet" href="<?= BASE_URL ?>assets/css/landing.css">
<style>
  .form-wrap { max-width: 720px; margin: 40px auto; padding: 0 20px; }
  .form-card { background: #fff; padding: 32px; border-radius: 16px;
    box-shadow: 0 10px 30px rgba(15, 35, 60, 0.08); border: 1px solid #e4ebf2; }
  .form-card h1 { color: #102a43; font-size: 1.5rem;
    font-weight: 800; margin-bottom: 6px; }
  .form-card .sub { color: #627d98; font-size: 0.9rem; margin-bottom: 24px; }
  .form-card label { display: block; color: #334e68; font-size: 0.85rem;
    font-weight: 600; margin: 14px 0 6px; }
  .form-card input, .form-card select, .form-card textarea {
    width: 100%; padding: 11px 14px;
    border: 1px solid #cbdbea; border-radius: 9px;
    font-size: 0.9rem; background: #fff; color: #172033; font-family: inherit; }
  .form-card input:focus, .form-card select:focus, .form-card textarea:focus {
    outline: none; border-color: #1261c9;
    box-shadow: 0 0 0 3px rgba(18, 97, 201, 0.12); }
  .form-card textarea { resize: vertical; }
  .grid-2 { display: grid; grid-template-columns: 1fr 1fr; gap: 12px; }
  @media (max-width: 600px) { .grid-2 { grid-template-columns: 1fr; } }
  .checkbox-row { display: flex; align-items: center; gap: 8px;
    margin: 8px 0 12px; padding: 12px 14px; background: #f4f8fd;
    border-radius: 9px; border: 1px solid #d5e7fb; }
  .checkbox-row input { width: auto; }
  .checkbox-row label { margin: 0; font-weight: 600; }
  .form-btn { width: 100%; margin-top: 22px; padding: 13px;
    background: #1261c9; color: #fff; border: none;
    border-radius: 9px; font-weight: 700; font-size: 0.9rem;
    cursor: pointer; transition: 0.2s; }
  .form-btn:hover { background: #0d54b0; }
  .alert { padding: 12px 16px; border-radius: 8px;
    font-size: 0.9rem; margin-bottom: 14px; }
  .alert.error { background: #fee2e2; color: #991b1b; }
  .alert.success { background: #d1fae5; color: #065f46; }
  .alert.info { background: #dbeafe; color: #1e40af; }
  .tracking-code { font-size: 1.8rem; font-weight: 800;
    letter-spacing: 3px; background: #e8f2ff; color: #1261c9;
    padding: 20px; text-align: center; border-radius: 12px;
    margin: 18px 0; font-family: monospace; }
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
      <li><a href="<?= BASE_URL ?>user/lapor.php" class="active">Buat Laporan</a></li>
      <li><a href="<?= BASE_URL ?>user/laporan_saya.php">Laporan Saya</a></li>  
      <li><a href="<?= BASE_URL ?>user/cek_status.php">Cek Status</a></li>
      <li><a href="<?= BASE_URL ?>auth/logout.php">Logout (<?= sanitize($_SESSION['user_nama']) ?>)</a></li>
    </ul>
  </div>
</header>

<div class="form-wrap">

<?php if ($sukses): ?>
  <div class="form-card" style="text-align:center">
    <div style="font-size:3rem;margin-bottom:10px">✅</div>
    <h1>Laporan Berhasil Dikirim</h1>
    <p class="sub">Simpan kode pelacakan ini untuk memantau status laporan Anda:</p>
    <div class="tracking-code"><?= $kode ?></div>
    <p style="color:#627d98;font-size:0.85rem">Kode ini hanya ditampilkan sekali. Catat atau screenshot sekarang.</p>
    <a href="<?= BASE_URL ?>user/cek_status.php?kode=<?= $kode ?>" class="form-btn" style="display:block;text-align:center;text-decoration:none;color:#fff">Lihat Status Laporan</a>
  </div>
<?php else: ?>
  <div class="form-card">
    <h1>Formulir Pengaduan</h1>
    <p class="sub">Isi data dengan tenang. Identitas Anda akan dilindungi.</p>

    <?php if ($error): ?>
      <div class="alert error"><?= $error ?></div>
    <?php endif; ?>

    <form method="POST" enctype="multipart/form-data">
      <div class="checkbox-row">
        <input type="checkbox" name="anonim" id="anonim" checked>
        <label for="anonim">Kirim sebagai <strong>Anonim</strong> (identitas tidak diminta)</label>
      </div>

      <div id="identitasFields" style="display:none">
        <div class="alert info">🔒 Identitas Anda akan disimpan dan hanya diakses oleh Satgas berwenang.</div>
        <div class="grid-2">
          <div>
            <label>Nama</label>
            <input type="text" name="nama" value="<?= sanitize($_SESSION['user_nama']) ?>">
          </div>
          <div>
            <label>NIM</label>
            <input type="text" name="nim" value="<?= sanitize($_SESSION['user_nim']) ?>">
          </div>
        </div>
        <div class="grid-2">
          <div>
            <label>Email</label>
            <input type="email" name="email" placeholder="email@kampus.ac.id">
          </div>
          <div>
            <label>No. HP/WA</label>
            <input type="text" name="telp" placeholder="08xxxxxxxxxx">
          </div>
        </div>
      </div>

      <label>Kategori Kekerasan *</label>
      <select name="kategori_id" required>
        <option value="">-- Pilih Kategori --</option>
        <?php foreach ($kategori as $k): ?>
          <option value="<?= $k['id'] ?>"><?= sanitize($k['nama']) ?></option>
        <?php endforeach; ?>
      </select>

      <label>Judul Laporan *</label>
      <input type="text" name="judul" required maxlength="200"
             placeholder="Contoh: Pelecehan verbal di ruang kelas">

      <label>Kronologi Kejadian *</label>
      <textarea name="kronologi" rows="6" required
        placeholder="Ceritakan secara detail: apa, siapa, kapan, di mana, bagaimana..."></textarea>

      <div class="grid-2">
        <div>
          <label>Lokasi Kejadian</label>
          <input type="text" name="lokasi" placeholder="Gedung / ruang / area">
        </div>
        <div>
          <label>Tanggal Kejadian</label>
          <input type="date" name="waktu">
        </div>
      </div>

      <label>Terlapor (jika diketahui)</label>
      <input type="text" name="terlapor" placeholder="Nama/inisial/deskripsi terlapor">

      <label>Bukti Pendukung (opsional, maks 10MB/file)</label>
      <input type="file" name="bukti[]" multiple accept=".jpg,.jpeg,.png,.pdf,.doc,.docx">
      <small style="color:#627d98;font-size:0.8rem">Format: JPG, PNG, PDF, DOC, DOCX</small>

      <button type="submit" class="form-btn">📤 Kirim Laporan</button>
    </form>
  </div>
<?php endif; ?>

</div>

<script>
document.getElementById('anonim').addEventListener('change', e => {
  document.getElementById('identitasFields').style.display =
    e.target.checked ? 'none' : 'block';
});
</script>
</body>
</html>