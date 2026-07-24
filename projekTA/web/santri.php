<?php
require_once __DIR__ . '/../api/config.php';

/**
 * Mode API ringan:
 * - GET /santri.php?uid=RFID_UID   -> balas JSON {status,data}
 * - GET /santri.php?id=ID_SANTRI   -> alternatif by id
 * Tujuan: sumber data saldo terbaru setelah transaksi.
 */
if (isset($_GET['uid']) || isset($_GET['id'])) {
    header('Content-Type: application/json');

    $byUid = isset($_GET['uid']) ? trim($_GET['uid']) : null;
    $byId  = isset($_GET['id'])  ? (int)$_GET['id']     : null;

    if ($byUid) {
        $stmt = $mysqli->prepare("SELECT id, rfid_uid, nama, saldo FROM santri WHERE rfid_uid = ?");
        $stmt->bind_param('s', $byUid);
    } else {
        $stmt = $mysqli->prepare("SELECT id, rfid_uid, nama, saldo FROM santri WHERE id = ?");
        $stmt->bind_param('i', $byId);
    }

    if (!$stmt) {
        http_response_code(500);
        echo json_encode(['status' => 'error', 'message' => 'DB prepare gagal']);
        exit;
    }

    $stmt->execute();
    $res = $stmt->get_result();
    if ($row = $res->fetch_assoc()) {
        echo json_encode(['status' => 'found', 'data' => $row], JSON_UNESCAPED_UNICODE);
    } else {
        echo json_encode(['status' => 'not_found'], JSON_UNESCAPED_UNICODE);
    }
    exit;
}


// CREATE
if (isset($_POST['create'])) {
    $uid   = $_POST['rfid_uid'];
    $nama  = $_POST['nama'];
    $saldo = $_POST['saldo'];
    $stmt = $conn->prepare("INSERT INTO santri (rfid_uid, nama, saldo) VALUES (?,?,?)");
    $stmt->bind_param("ssi", $uid, $nama, $saldo);
    $stmt->execute();
    echo "<script>alert('Data berhasil ditambahkan');window.location='santri.php';</script>";
}

// UPDATE
if (isset($_POST['update'])) {
    $id    = $_POST['id'];
    $nama  = $_POST['nama'];
    $saldo = $_POST['saldo'];
    $stmt = $conn->prepare("UPDATE santri SET nama=?, saldo=? WHERE id=?");
    $stmt->bind_param("sii", $nama, $saldo, $id);
    $stmt->execute();
    echo "<script>alert('Data berhasil diupdate');window.location='santri.php';</script>";
}

// DELETE
if (isset($_GET['delete'])) {
    $id = $_GET['delete'];
    $stmt = $conn->prepare("DELETE FROM santri WHERE id=?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    echo "<script>alert('Data berhasil dihapus');window.location='santri.php';</script>";
}

// AMBIL data
$res = $conn->query("SELECT * FROM santri ORDER BY id DESC");
?>
<!DOCTYPE html>
<html>
    
<head>
    <title>Data Santri</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

  <!-- Hide 'Tambah Santri' without changing layout -->
  <style>
    a[href*="santri_tambah.php"],
    #btnTambahSantri,
    .btn-tambah-santri {
      display: none !important;
      visibility: hidden !important;
    }
  
  /* also hide Bootstrap modal trigger for the add-santri button */
  [data-bs-toggle="modal"][data-bs-target="#tambahModal"],
  button[data-bs-target="#tambahModal"],
  .btn[data-bs-target="#tambahModal"] {
    display: none !important;
    visibility: hidden !important;
  }
</style>

</head>
<body class="bg-light">

<div class="container py-5">
    <div class="card shadow-lg border-0 rounded-4">
        <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
            <h3 class="mb-0">📋 Data Santri</h3>
            <button class="btn btn-warning fw-bold" data-bs-toggle="modal" data-bs-target="#tambahModal">+ Tambah Santri</button>
        </div>
        <div class="card-body">
            <table class="table table-hover table-bordered align-middle text-center">
                <thead class="table-dark">
                    <tr>
                        <th>ID</th>
                        <th>UID</th>
                        <th>Nama</th>
                        <th>Saldo</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                <?php while($row = $res->fetch_assoc()){ ?>
                    <tr>
                        <td><?= $row['id'] ?></td>
                        <td><span class="badge bg-secondary"><?= $row['rfid_uid'] ?></span></td>
                        <td><?= $row['nama'] ?></td>
                        <td><span class="badge bg-success">Rp <?= number_format($row['saldo'],0,',','.') ?></span></td>
                        <td>
                            <!-- Tombol Edit -->
                            <button class="btn btn-sm btn-info text-white" data-bs-toggle="modal" data-bs-target="#editModal<?= $row['id'] ?>">Edit</button>
                            <!-- Tombol Delete -->
                            <a href="santri.php?delete=<?= $row['id'] ?>" class="btn btn-sm btn-danger" onclick="return confirm('Yakin hapus data ini?')">Delete</a>
                        </td>
                    </tr>

                    <!-- Modal Edit -->
                    <div class="modal fade" id="editModal<?= $row['id'] ?>" tabindex="-1">
                        <div class="modal-dialog">
                            <div class="modal-content rounded-4">
                                <div class="modal-header bg-info text-white">
                                    <h5 class="modal-title">✏️ Edit Data Santri</h5>
                                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                </div>
                                <form method="post">
                                    <div class="modal-body">
                                        <input type="hidden" name="id" value="<?= $row['id'] ?>">
                                        <div class="mb-3">
                                            <label class="form-label">Nama</label>
                                            <input type="text" name="nama" class="form-control" value="<?= $row['nama'] ?>" required>
                                        </div>
                                        <div class="mb-3">
                                            <label class="form-label">Saldo</label>
                                            <input type="number" name="saldo" class="form-control" value="<?= $row['saldo'] ?>" required>
                                        </div>
                                    </div>
                                    <div class="modal-footer">
                                        <button type="submit" name="update" class="btn btn-info text-white">💾 Simpan</button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                <?php } ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Modal Tambah -->
<div class="modal fade" id="tambahModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content rounded-4">
            <div class="modal-header bg-warning">
                <h5 class="modal-title fw-bold">➕ Tambah Data Santri</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="post">
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">UID</label>
                        <input type="text" name="rfid_uid" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Nama</label>
                        <input type="text" name="nama" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Saldo</label>
                        <input type="number" name="saldo" class="form-control" value="0" required>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="submit" name="create" class="btn btn-warning fw-bold">💾 Simpan</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

<script>
  // Auto-refresh santri page after POS sets the flag
  if (sessionStorage.getItem('refreshSantri') === '1') {
    sessionStorage.removeItem('refreshSantri');
    location.reload();
  }
</script>

</body>
</html>
