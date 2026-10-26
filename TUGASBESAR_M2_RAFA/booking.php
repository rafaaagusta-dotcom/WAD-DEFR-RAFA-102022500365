<?php
require_once 'koneksi.php';
date_default_timezone_set('Asia/Jakarta');

function e($s) { return htmlspecialchars((string) $s, ENT_QUOTES, 'UTF-8'); }

$error  = '';
$sukses = '';

$daftar_meja = [];
$r = $koneksi->query(
    'SELECT m.id_meja, m.nomor_meja, m.kapasitas, a.nama_area
     FROM meja m JOIN area a ON m.id_area = a.id_area
     ORDER BY m.nomor_meja'
);
if ($r) {
    while ($row = $r->fetch_assoc()) {
        $daftar_meja[(int) $row['id_meja']] = $row;
    }
}

$form = [
    'nama'    => '',
    'telepon' => '',
    'id_meja' => isset($_GET['meja']) ? (int) $_GET['meja'] : 0,
    'tanggal' => date('Y-m-d'),
    'mulai'   => '',
    'selesai' => '',
    'jumlah'  => 1,
];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $aksi = $_POST['aksi'] ?? '';

    if ($aksi === 'batal') {
        $id_res = (int) ($_POST['id_reservasi'] ?? 0);
        $stmt = $koneksi->prepare("UPDATE reservasi SET status = 'batal' WHERE id_reservasi = ? AND status = 'aktif'");
        $stmt->bind_param('i', $id_res);
        $stmt->execute();
        header('Location: booking.php?status=' . ($stmt->affected_rows > 0 ? 'batal' : 'gagal'));
        exit;
    }

    if ($aksi === 'booking') {
        $form['nama']    = trim($_POST['nama'] ?? '');
        $form['telepon'] = trim($_POST['telepon'] ?? '');
        $form['id_meja'] = (int) ($_POST['id_meja'] ?? 0);
        $form['tanggal'] = $_POST['tanggal'] ?? '';
        $form['mulai']   = $_POST['mulai'] ?? '';
        $form['selesai'] = $_POST['selesai'] ?? '';
        $form['jumlah']  = (int) ($_POST['jumlah'] ?? 0);

        $tgl_valid = DateTime::createFromFormat('Y-m-d', $form['tanggal']);
        $tgl_valid = $tgl_valid && $tgl_valid->format('Y-m-d') === $form['tanggal'];
        $jam_valid = preg_match('/^\d{2}:\d{2}$/', $form['mulai']) && preg_match('/^\d{2}:\d{2}$/', $form['selesai']);

        if ($form['nama'] === '' || $form['telepon'] === '') {
            $error = 'Nama dan nomor telepon wajib diisi.';
        } elseif (!preg_match('/^[0-9+\-\s()]{8,20}$/', $form['telepon'])) {
            $error = 'Format nomor telepon tidak valid.';
        } elseif (!isset($daftar_meja[$form['id_meja']])) {
            $error = 'Silakan pilih meja yang valid.';
        } elseif (!$tgl_valid || !$jam_valid) {
            $error = 'Tanggal dan jam wajib diisi dengan benar.';
        } elseif ($form['tanggal'] < date('Y-m-d')) {
            $error = 'Tanggal booking tidak boleh di masa lalu.';
        } elseif ($form['tanggal'] === date('Y-m-d') && $form['mulai'] < date('H:i')) {
            $error = 'Jam mulai sudah lewat.';
        } elseif ($form['selesai'] <= $form['mulai']) {
            $error = 'Jam selesai harus lebih besar dari jam mulai.';
        } elseif ($form['jumlah'] < 1) {
            $error = 'Jumlah orang minimal 1.';
        } elseif ($form['jumlah'] > (int) $daftar_meja[$form['id_meja']]['kapasitas']) {
            $error = 'Jumlah orang melebihi kapasitas meja (' . $daftar_meja[$form['id_meja']]['kapasitas'] . ' orang).';
        } else {
            $mulai   = $form['mulai'] . ':00';
            $selesai = $form['selesai'] . ':00';

            $cek = $koneksi->prepare(
                "SELECT id_reservasi FROM reservasi
                 WHERE id_meja = ? AND tanggal = ? AND status = 'aktif'
                   AND jam_mulai < ? AND jam_selesai > ?"
            );
            $cek->bind_param('isss', $form['id_meja'], $form['tanggal'], $selesai, $mulai);
            $cek->execute();
            $cek->store_result();

            if ($cek->num_rows > 0) {
                $error = 'Meja sudah dibooking pada jam tersebut. Pilih jam atau meja lain.';
            } else {
                $stmt = $koneksi->prepare(
                    'INSERT INTO reservasi (id_meja, nama_pelanggan, no_telepon, tanggal, jam_mulai, jam_selesai, jumlah_orang)
                     VALUES (?, ?, ?, ?, ?, ?, ?)'
                );
                $stmt->bind_param(
                    'isssssi',
                    $form['id_meja'], $form['nama'], $form['telepon'],
                    $form['tanggal'], $mulai, $selesai, $form['jumlah']
                );

                if ($stmt->execute()) {
                    header('Location: booking.php?status=sukses');
                    exit;
                }
                $error = 'Gagal menyimpan booking.';
            }
        }
    }
}

$st = $_GET['status'] ?? '';
if ($st === 'sukses') {
    $sukses = 'Booking berhasil disimpan.';
} elseif ($st === 'batal') {
    $sukses = 'Booking berhasil dibatalkan.';
} elseif ($st === 'gagal') {
    $error = 'Booking tidak ditemukan atau sudah dibatalkan.';
}

$reservasi = $koneksi->query(
    'SELECT r.*, m.nomor_meja, a.nama_area
     FROM reservasi r
     JOIN meja m ON r.id_meja = m.id_meja
     JOIN area a ON m.id_area = a.id_area
     ORDER BY r.tanggal DESC, r.jam_mulai DESC'
);

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
  <title>Booking Meja | Restoran &amp; Cafe</title>
  <link rel="stylesheet" href="style.css">
</head>
<body class="<?= $adaFoto ? 'foto' : '' ?>">
  <header class="resto">
    <div class="resto-isi">
      <nav>
        <strong>Restoran &amp; Cafe</strong>
        <a href="#">Dashboard</a>
        <a href="#">Buku Menu</a>
        <a href="index.php">Meja &amp; Area</a>
        <a href="booking.php" class="aktif">Booking</a>
        <a href="#">Staff</a>
        <a href="#">Supplier</a>
      </nav>
      <h1>Booking Meja</h1>
      <p>Isi data berikut untuk memesan meja atau tempat.</p>
    </div>
  </header>

  <main>
    <?php if ($error !== ''): ?>
      <p class="pesan error"><?= e($error) ?></p>
    <?php endif; ?>
    <?php if ($sukses !== ''): ?>
      <p class="pesan"><?= e($sukses) ?></p>
    <?php endif; ?>

    <form method="post" action="booking.php" class="kartu-form">
      <input type="hidden" name="aksi" value="booking">
      <div class="grid-form">
        <label>Nama Pelanggan
          <input type="text" name="nama" value="<?= e($form['nama']) ?>" required>
        </label>
        <label>No. Telepon
          <input type="tel" name="telepon" value="<?= e($form['telepon']) ?>" required>
        </label>
        <label>Meja
          <select name="id_meja" required>
            <option value="">-- Pilih meja --</option>
            <?php foreach ($daftar_meja as $id_m => $m): ?>
              <option value="<?= $id_m ?>" <?= $form['id_meja'] === $id_m ? 'selected' : '' ?>>
                Meja <?= e($m['nomor_meja']) ?> - <?= e($m['nama_area']) ?> (<?= e($m['kapasitas']) ?> orang)
              </option>
            <?php endforeach; ?>
          </select>
        </label>
        <label>Tanggal
          <input type="date" name="tanggal" min="<?= date('Y-m-d') ?>" value="<?= e($form['tanggal']) ?>" required>
        </label>
        <label>Jam Mulai
          <input type="time" name="mulai" value="<?= e($form['mulai']) ?>" required>
        </label>
        <label>Jam Selesai
          <input type="time" name="selesai" value="<?= e($form['selesai']) ?>" required>
        </label>
        <label>Jumlah Orang
          <input type="number" name="jumlah" min="1" value="<?= e($form['jumlah']) ?>" required>
        </label>
      </div>

      <div class="tombol-baris">
        <button type="submit" class="btn">Booking Sekarang</button>
        <a href="index.php" class="btn garis">Kembali</a>
      </div>
    </form>

    <h2 class="judul">Daftar Booking</h2>
    <div class="tabel-bungkus">
      <table>
        <thead>
          <tr>
            <th>Nama</th>
            <th>Telepon</th>
            <th>Meja</th>
            <th>Tanggal</th>
            <th>Jam</th>
            <th>Orang</th>
            <th>Status</th>
            <th>Aksi</th>
          </tr>
        </thead>
        <tbody>
          <?php if (!$reservasi || $reservasi->num_rows === 0): ?>
            <tr><td colspan="8">Belum ada booking.</td></tr>
          <?php else: while ($row = $reservasi->fetch_assoc()): ?>
            <tr class="<?= $row['status'] === 'batal' ? 'batal' : '' ?>">
              <td><?= e($row['nama_pelanggan']) ?></td>
              <td><?= e($row['no_telepon']) ?></td>
              <td><?= e($row['nomor_meja']) ?> (<?= e($row['nama_area']) ?>)</td>
              <td><?= e(date('d-m-Y', strtotime($row['tanggal']))) ?></td>
              <td><?= e(substr($row['jam_mulai'], 0, 5)) ?> - <?= e(substr($row['jam_selesai'], 0, 5)) ?></td>
              <td><?= e($row['jumlah_orang']) ?></td>
              <td><span class="badge <?= e($row['status']) ?>"><?= e(ucfirst($row['status'])) ?></span></td>
              <td>
                <?php if ($row['status'] === 'aktif'): ?>
                  <form method="post" action="booking.php" style="margin:0"
                        onsubmit="return confirm('Batalkan booking ini?')">
                    <input type="hidden" name="aksi" value="batal">
                    <input type="hidden" name="id_reservasi" value="<?= (int) $row['id_reservasi'] ?>">
                    <button type="submit" class="btn kecil bahaya">Batalkan</button>
                  </form>
                <?php else: ?>
                  -
                <?php endif; ?>
              </td>
            </tr>
          <?php endwhile; endif; ?>
        </tbody>
      </table>
    </div>
  </main>
</body>
</html>
