<?php
require_once 'koneksi.php';

function e($s) { return htmlspecialchars((string) $s, ENT_QUOTES, 'UTF-8'); }

function gambarMeja($kapasitas) {
    $n = max(1, min((int) $kapasitas, 12));
    $svg = '<svg class="denah" viewBox="0 0 120 120" aria-hidden="true">';
    $svg .= '<circle class="meja-bulat" cx="60" cy="60" r="26"/>';
    for ($i = 0; $i < $n; $i++) {
        $sudut = 2 * M_PI * $i / $n - M_PI / 2;
        $x = round(60 + 42 * cos($sudut), 1);
        $y = round(60 + 42 * sin($sudut), 1);
        $svg .= '<circle class="kursi" cx="' . $x . '" cy="' . $y . '" r="7"/>';
    }
    return $svg . '</svg>';
}

$hasil = $koneksi->query(
    'SELECT m.id_meja, m.nomor_meja, a.nama_area, m.kapasitas
     FROM meja m
     JOIN area a ON m.id_area = a.id_area
     ORDER BY a.nama_area, m.nomor_meja'
);
if (!$hasil) {
    error_log('Query gagal: ' . $koneksi->error);
    die('Terjadi kesalahan saat mengambil data meja.');
}

$perArea = [];
$totalKursi = 0;
$totalMeja = 0;
while ($row = $hasil->fetch_assoc()) {
    $perArea[$row['nama_area']][] = $row;
    $totalKursi += (int) $row['kapasitas'];
    $totalMeja++;
}
$totalArea = count($perArea);

$status = $_GET['status'] ?? '';
$pesan = '';
if ($status === 'updated') {
    $pesan = 'Data meja berhasil diperbarui.';
} elseif ($status === 'notfound') {
    $pesan = 'Data meja tidak ditemukan.';
}

$adaFoto = file_exists(__DIR__ . '/img/fotogue.jpg');
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Bricolage+Grotesque:opsz,wght@12..96,400;12..96,600;12..96,800&display=swap" rel="stylesheet">
  <title>Meja &amp; Area | Restoran &amp; Cafe</title>
  <link rel="stylesheet" href="style.css">
</head>
<body class="<?= $adaFoto ? 'foto' : '' ?>">
  <header class="resto">
    <div class="resto-isi">
      <nav>
        <strong>Restoran &amp; Cafe</strong>
        <a href="#">Dashboard</a>
        <a href="#">Buku Menu</a>
        <a href="index.php" class="aktif">Meja &amp; Area</a>
        <a href="booking.php">Booking</a>
        <a href="#">Staff</a>
        <a href="#">Supplier</a>
      </nav>
      <h1>Master Meja &amp; Area</h1>
      <p>Daftar meja dan area yang tersedia di restoran. Pilih meja untuk mengubah data atau membuat booking.</p>

      <div class="ringkasan">
        <div><b><?= $totalMeja ?></b><span>Meja</span></div>
        <div><b><?= $totalArea ?></b><span>Area</span></div>
        <div><b><?= $totalKursi ?></b><span>Total kursi</span></div>
      </div>
    </div>
  </header>

  <main>
    <?php if ($pesan !== ''): ?>
      <p class="pesan <?= $status === 'notfound' ? 'error' : '' ?>"><?= e($pesan) ?></p>
    <?php endif; ?>

    <?php if ($totalMeja === 0): ?>
      <div class="kosong">Belum ada data meja. Tambahkan data lewat database untuk mulai.</div>
    <?php endif; ?>

    <?php foreach ($perArea as $namaArea => $daftar):
      $kelas = 'area-' . strtolower(trim(preg_replace('/[^a-z0-9]+/i', '-', $namaArea), '-'));
    ?>
      <section class="area <?= e($kelas) ?>">
        <div class="area-judul">
          <h2><?= e($namaArea) ?></h2>
          <span><?= count($daftar) ?> meja</span>
        </div>

        <div class="daftar">
          <?php foreach ($daftar as $row): ?>
            <article class="meja">
              <?= gambarMeja($row['kapasitas']) ?>
              <h3>Meja <?= e($row['nomor_meja']) ?></h3>
              <p class="kapasitas">Kapasitas <?= e($row['kapasitas']) ?> orang</p>
              <div class="aksi">
                <a href="edit.php?id=<?= (int) $row['id_meja'] ?>">Edit</a>
                <a class="utama" href="booking.php?meja=<?= (int) $row['id_meja'] ?>">Booking</a>
              </div>
            </article>
          <?php endforeach; ?>
        </div>
      </section>
    <?php endforeach; ?>
  </main>
</body>
</html>
