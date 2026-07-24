<?php
// register_card.php
include ('../api/config.php');

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['uid'])) {
    $uid   = trim($_POST['uid']);
    $nama  = trim($_POST['nama']);
    $saldo = intval($_POST['saldo']);

    if ($uid === "" || $nama === "") {
        echo json_encode(["status"=>"error", "message"=>"Data tidak lengkap"]);
        exit;
    }

    // cek UID sudah ada
    $cek = $conn->prepare("SELECT id FROM santri WHERE rfid_uid=?");
    $cek->bind_param("s", $uid);
    $cek->execute();
    $cek->store_result();

    if ($cek->num_rows > 0) {
        echo json_encode(["status"=>"error", "message"=>"UID sudah terdaftar"]);
    } else {
        $stmt = $conn->prepare("INSERT INTO santri (rfid_uid,nama,saldo) VALUES (?,?,?)");
        $stmt->bind_param("ssi", $uid, $nama, $saldo);
        if ($stmt->execute()) {
            file_put_contents(__DIR__ . '/last_uid.txt', ''); // reset UID setelah simpan
            echo json_encode(["status"=>"success","message"=>"Santri berhasil disimpan"]);
        } else {
            echo json_encode(["status"=>"error","message"=>"Gagal simpan ke database"]);
        }
    }
    exit; // supaya HTML tidak ikut terkirim saat AJAX
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Daftarkan Kartu (Scan)</title>
    <style>
        body { font-family: Arial, sans-serif; background: #f5f7fb; }
        .container { max-width: 700px; margin: 20px auto; background: white; padding: 20px; border-radius: 12px; box-shadow: 0 4px 10px rgba(0,0,0,0.1); }
        h2 { margin-bottom: 15px; }
        label { display: block; margin-top: 10px; font-weight: bold; }
        input { width: 100%; padding: 10px; margin-top: 5px; border-radius: 6px; border: 1px solid #ccc; }
        button { padding: 10px 15px; margin-top: 15px; border: none; border-radius: 6px; cursor: pointer; }
        .btn { background: #2563eb; color: white; }
        .btn.stop { background: #ef4444; }
        .btn.save { background: #16a34a; }
    </style>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</head>
<body>
<div class="container">
    <h2>Daftarkan Kartu (Scan)</h2>
    <p><b>Klik Start Scan</b>, lalu tempelkan kartu ke reader (ESP32). UID akan otomatis terisi.</p>

    <form id="cardForm" method="POST">
        <label>UID</label>
        <input type="text" id="uid" name="uid" readonly placeholder="Tempelkan kartu dulu">

        <label>Nama</label>
        <input type="text" name="nama" placeholder="Masukkan nama santri" required>

        <label>Saldo</label>
        <input type="number" name="saldo" value="0" required>

        <br>
      <button type="button" class="btn btn-primary" onclick="startScan()">Start Scan</button>
      <button type="button" class="btn btn-danger" onclick="stopScan()">Stop</button>
      <button type="submit" class="btn save">Simpan</button>
      <button type="button" class="btn" onclick="window.location.href='santri.php'">Lihat Data Santri</button>
    </form>
</div>

<script>
let intervalId;
let scanned = false;

function startScan() {
    scanned = false;
    intervalId = setInterval(() => {
        fetch("get_uid.php")
            .then(response => response.json())
            .then(data => {
                console.log("UID diterima dari server:", data.uid);
                if (data.uid && data.uid !== "") {
                    document.getElementById("uid").value = data.uid;

                    if (!scanned) {
                        scanned = true;

                        if(data.registered) {
                            Swal.fire({
                                icon: 'warning',
                                title: 'UID Sudah Terdaftar',
                                text: `UID ${data.uid} sudah digunakan.`,
                                timer: 2500,
                                showConfirmButton: false
                            });
                        } else {
                            Swal.fire({
                                icon: 'success',
                                title: 'Kartu berhasil dibaca!',
                                text: `UID: ${data.uid}`,
                                timer: 1500,
                                showConfirmButton: false
                            });
                        }

                        clearInterval(intervalId);
                    }
                }
            })
            .catch(err => console.error(err));
    }, 1000);
}

function stopScan() {
    clearInterval(intervalId);
}

// Submit dengan AJAX langsung ke file ini
document.getElementById("cardForm").addEventListener("submit", function(e){
    e.preventDefault();

    const formData = new FormData(this);

    fetch("../api/save_card.php", {
        method: "POST",
        body: formData
    })
    .then(res => res.json())
    .then(data => {
        if (data.status === "success") {
            Swal.fire({
                icon: "success",
                title: "Berhasil!",
                text: data.message,
                timer: 2000,
                showConfirmButton: false
            });
        } else {
            Swal.fire({
                icon: "error",
                title: "Gagal!",
                text: data.message
            });
        }
    })
    .catch(err => {
        Swal.fire({
            icon: "error",
            title: "Error!",
            text: "Terjadi kesalahan saat menyimpan data."
        });
    });
});
</script>
</body>
</html>
