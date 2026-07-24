<?php
// barang.php

// Konfigurasi database
$host     = "localhost";
$user     = "root";  // sesuaikan
$password = "";      // sesuaikan
$dbname   = "koperasi_pp";

$conn = new mysqli($host, $user, $password, $dbname);
if ($conn->connect_error) {
    die("Koneksi gagal: " . $conn->connect_error);
}

// Tambah data
if (isset($_POST['tambah'])) {
    $kode = $_POST['kode'];
    $nama = $_POST['nama'];
    $harga = $_POST['harga'];
    $stok = $_POST['stok'];

    $sql = "INSERT INTO barang (kode, nama, harga, stok, created_at) 
            VALUES ('$kode', '$nama', '$harga', '$stok', NOW())";
    $conn->query($sql);
    header("Location: barang.php");
    exit;
}

// Hapus data
if (isset($_GET['hapus'])) {
    $id = $_GET['hapus'];
    $sql = "DELETE FROM barang WHERE id='$id'";
    $conn->query($sql);
    header("Location: barang.php");
    exit;
}

// Update data
if (isset($_POST['update'])) {
    $id = $_POST['id'];
    $kode = $_POST['kode'];
    $nama = $_POST['nama'];
    $harga = $_POST['harga'];
    $stok = $_POST['stok'];

    $sql = "UPDATE barang SET kode='$kode', nama='$nama', harga='$harga', stok='$stok' WHERE id='$id'";
    $conn->query($sql);
    header("Location: barang.php");
    exit;
}

// Ambil semua data
$result = $conn->query("SELECT * FROM barang ORDER BY id ASC");
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <title>Manajemen Barang</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <style>
    body { background-color: #f8f9fa; }
    .container { margin-top: 30px; }
    .card { border-radius: 15px; }
    .btn-custom { border-radius: 10px; }
    .modal-content { border-radius: 15px; }
  </style>
</head>
<body>
<div class="container">
  <a href="index.php" class="btn btn-secondary mb-3">← Kembali ke Dashboard</a>
  
  <div class="card shadow">
    <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
      <h5 class="mb-0">📦 Data Barang</h5>
      <button class="btn btn-light btn-sm" data-bs-toggle="modal" data-bs-target="#tambahModal">+ Tambah Barang</button>
    </div>
    <div class="card-body">
      <table class="table table-bordered table-striped align-middle text-center">
        <thead class="table-dark">
          <tr>
            <th>ID</th>
            <th>Kode</th>
            <th>Nama</th>
            <th>Harga</th>
            <th>Stok</th>
            <th>Created At</th>
            <th>Aksi</th>
          </tr>
        </thead>
        <tbody>
        <?php if ($result->num_rows > 0): ?>
          <?php while ($row = $result->fetch_assoc()): ?>
            <tr>
              <td><?= $row['id'] ?></td>
              <td><?= $row['kode'] ?></td>
              <td><?= $row['nama'] ?></td>
              <td>Rp<?= number_format($row['harga'],0,",",".") ?></td>
              <td><?= $row['stok'] ?></td>
              <td><?= $row['created_at'] ?></td>
              <td>
                <!-- Tombol Edit -->
                <button class="btn btn-warning btn-sm btn-custom" data-bs-toggle="modal" data-bs-target="#editModal<?= $row['id'] ?>">Edit</button>
                <!-- Tombol Hapus -->
                <a href="barang.php?hapus=<?= $row['id'] ?>" class="btn btn-danger btn-sm btn-custom" onclick="return confirm('Yakin hapus data ini?')">Hapus</a>
              </td>
            </tr>

            <!-- Modal Edit -->
            <div class="modal fade" id="editModal<?= $row['id'] ?>" tabindex="-1">
              <div class="modal-dialog">
                <div class="modal-content">
                  <form method="post">
                    <div class="modal-header bg-warning">
                      <h5 class="modal-title">Edit Barang</h5>
                      <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                      <input type="hidden" name="id" value="<?= $row['id'] ?>">
                      <div class="mb-3">
                        <label>Kode</label>
                        <input type="text" name="kode" class="form-control" value="<?= $row['kode'] ?>" required>
                      </div>
                      <div class="mb-3">
                        <label>Nama</label>
                        <input type="text" name="nama" class="form-control" value="<?= $row['nama'] ?>" required>
                      </div>
                      <div class="mb-3">
                        <label>Harga</label>
                        <input type="number" name="harga" class="form-control" value="<?= $row['harga'] ?>" required>
                      </div>
                      <div class="mb-3">
                        <label>Stok</label>
                        <input type="number" name="stok" class="form-control" value="<?= $row['stok'] ?>" required>
                      </div>
                    </div>
                    <div class="modal-footer">
                      <button type="submit" name="update" class="btn btn-success">Simpan Perubahan</button>
                      <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    </div>
                  </form>
                </div>
              </div>
            </div>
          <?php endwhile; ?>
        <?php else: ?>
          <tr><td colspan="7">Tidak ada data barang</td></tr>
        <?php endif; ?>
        </tbody>
      </table>
    </div>
  </div>
</div>

<!-- Modal Tambah -->
<div class="modal fade" id="tambahModal" tabindex="-1">
  <div class="modal-dialog">
    <div class="modal-content">
      <form method="post">
        <div class="modal-header bg-primary text-white">
          <h5 class="modal-title">Tambah Barang Baru</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
        </div>
        <div class="modal-body">
          <div class="mb-3">
            <label>Kode</label>
            <input type="text" name="kode" class="form-control" required>
          </div>
          <div class="mb-3">
            <label>Nama</label>
            <input type="text" name="nama" class="form-control" required>
          </div>
          <div class="mb-3">
            <label>Harga</label>
            <input type="number" name="harga" class="form-control" required>
          </div>
          <div class="mb-3">
            <label>Stok</label>
            <input type="number" name="stok" class="form-control" required>
          </div>
        </div>
        <div class="modal-footer">
          <button type="submit" name="tambah" class="btn btn-primary">Simpan</button>
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
        </div>
      </form>
    </div>
  </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
<?php $conn->close(); ?>
