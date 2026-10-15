<?php
require_once 'koneksi.php';

function e($s) { return htmlspecialchars((string) $s, ENT_QUOTES, 'UTF-8'); }

$error = '';

$id = isset($_GET['id']) ? (int) $_GET['id'] : 0;
if ($id <= 0) {
    header('Location: index.php');
    exit;
}

$daftar_area = [];
$r = $koneksi->query('SELECT id_area, nama_area FROM area ORDER BY id_area');
while ($row = $r->fetch_assoc()) {
    $daftar_area[(int) $row['id_area']] = $row['nama_area'];
}

$nomor_meja = $id_area = $kapasitas = 0;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nomor_meja = (int) ($_POST['nomor_meja'] ?? 0);
    $id_area    = (int) ($_POST['id_area'] ?? 0);
    $kapasitas  = (int) ($_POST['kapasitas'] ?? 0);

    if ($nomor_meja < 1 || !isset($daftar_area[$id_area]) || $kapasitas < 1) {
        $error = 'Semua kolom wajib diisi dengan benar.';
    } else {
        $cek = $koneksi->prepare('SELECT id_meja FROM meja WHERE nomor_meja = ? AND id_meja <> ?');
        $cek->bind_param('ii', $nomor_meja, $id);
        $cek->execute();
        $cek->store_result();

        if ($cek->num_rows > 0) {
            $error = 'Nomor meja sudah digunakan.';
        } else {
            $stmt = $koneksi->prepare('UPDATE meja SET nomor_meja = ?, id_area = ?, kapasitas = ? WHERE id_meja = ?');
            $stmt->bind_param('iiii', $nomor_meja, $id_area, $kapasitas, $id);

            if ($stmt->execute()) {
                header('Location: index.php?status=updated');
                exit;
            }
            $error = 'Gagal menyimpan perubahan.';
        }
    }
}

$stmt = $koneksi->prepare('SELECT nomor_meja, id_area, kapasitas FROM meja WHERE id_meja = ?');
$stmt->bind_param('i', $id);
$stmt->execute();
$data = $stmt->get_result()->fetch_assoc();

if (!$data) {
    header('Location: index.php?status=notfound');
    exit;
}

// Jika ada error, tampilkan kembali input pengguna
if ($error !== '') {
    $data['nomor_meja'] = $nomor_meja;
    $data['id_area']    = $id_area;
    $data['kapasitas']  = $kapasitas;
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
  <title>Edit Meja &amp; Area | Restoran &amp; Cafe</title>
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
        <a href="booking.php">Booking</a>
        <a href="#">Staff</a>
        <a href="#">Supplier</a>
      </nav>
      <h1>Edit Meja &amp; Area</h1>
      <p>Ubah data meja yang dipilih, lalu simpan.</p>
    </div>
  </header>

  <main>
    <?php if ($error !== ''): ?>
      <p class="pesan error"><?= e($error) ?></p>
    <?php endif; ?>

    <form method="post" action="edit.php?id=<?= $id ?>" class="kartu-form sempit">
      <div class="grid-form">
        <label>Nomor Meja
          <input type="number" name="nomor_meja" min="1" value="<?= e($data['nomor_meja']) ?>" required>
        </label>
        <label>Area
          <select name="id_area" required>
            <?php foreach ($daftar_area as $id_a => $nama): ?>
              <option value="<?= $id_a ?>" <?= (int) $data['id_area'] === $id_a ? 'selected' : '' ?>><?= e($nama) ?></option>
            <?php endforeach; ?>
          </select>
        </label>
        <label>Kapasitas (Orang)
          <input type="number" name="kapasitas" min="1" value="<?= e($data['kapasitas']) ?>" required>
        </label>
      </div>

      <div class="tombol-baris">
        <button type="submit" class="btn">Simpan Perubahan</button>
        <a href="index.php" class="btn garis">Batal</a>
      </div>
    </form>
  </main>
</body>
</html>
