<?php
// web/cetak_struk.php
session_start();
if(!isset($_SESSION['user_id'])) header('Location: login.php');
require_once __DIR__ . '/../api/config.php';

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if ($id <= 0) {
  http_response_code(400);
  echo "ID transaksi tidak valid"; exit;
}

// Ambil header transaksi
$sql = "SELECT t.id, t.created_at, t.total, t.tipe, t.status,
               s.id AS santri_id, s.nama AS nama_santri,
               u.id AS user_id,  u.nama AS nama_user
        FROM transaksi t
        LEFT JOIN santri s ON s.id = t.id_santri
        LEFT JOIN users  u ON u.id = t.id_user
        WHERE t.id = ?";
$stmt = $mysqli->prepare($sql);
$stmt->bind_param('i', $id);
$stmt->execute();
$hdr = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$hdr) { echo "Transaksi tidak ditemukan"; exit; }

// Ambil item
$sql = "SELECT ti.qty, ti.harga_satuan, ti.subtotal, b.kode, b.nama
        FROM transaksi_items ti
        LEFT JOIN barang b ON b.id = ti.id_barang
        WHERE ti.id_transaksi = ?
        ORDER BY ti.id ASC";
$stmt = $mysqli->prepare($sql);
$stmt->bind_param('i', $id);
$stmt->execute();
$items = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt->close();

function h($s){ return htmlspecialchars((string)$s, ENT_QUOTES, 'UTF-8'); }
function rupiah($n){ return 'Rp '.number_format((int)$n,0,',','.'); }

// Info toko (opsional, bisa hardcode / ambil dari setting)
$toko_nama = 'Koperasi Pondok';
$toko_alamat = 'Jl. Pesantren No. 1';
$toko_telp = '0812-0000-0000';
?>
<!doctype html>
<html lang="id">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width,initial-scale=1">
<title>Struk #<?= (int)$hdr['id'] ?></title>
<style>
  /* Layout thermal receipt 80mm */
  @media print {
    @page { size: 80mm auto; margin: 5mm; }
    body { margin: 0; }
    .no-print { display:none !important; }
  }
  body { font-family: ui-monospace, SFMono-Regular, Menlo, Consolas, "Liberation Mono", monospace; background:#111; color:#eee; }
  .sheet { width: 320px; max-width: 100%; margin: 16px auto; background:#18181b; border:1px solid #27272a; border-radius:10px; padding:14px; }
  h1,h2,h3 { margin:0; }
  .center { text-align:center; }
  .muted { color:#a1a1aa; }
  .row { display:flex; justify-content:space-between; gap:8px; }
  .divider { border-top:1px dashed #3f3f46; margin:8px 0; }
  table { width:100%; border-collapse:collapse; }
  th, td { padding:4px 0; text-align:left; font-size:12px; }
  .right { text-align:right; }
  .btn { padding:8px 10px; border-radius:8px; border:0; cursor:pointer; background:#eab308; color:#111; font-weight:700; }
  .toolbar { display:flex; gap:8px; margin:12px auto; justify-content:center; }
</style>
</head>
<body>
  <div class="sheet">
    <div class="center">
      <div style="font-weight:800; font-size:16px;"><?= h($toko_nama) ?></div>
      <div class="muted" style="font-size:11px;"><?= h($toko_alamat) ?> • <?= h($toko_telp) ?></div>
    </div>

    <div class="divider"></div>

    <div style="font-size:12px; line-height:1.3;">
      <div class="row"><div>No</div><div class="right">#<?= (int)$hdr['id'] ?></div></div>
      <div class="row"><div>Tanggal</div><div class="right"><?= h($hdr['created_at']) ?></div></div>
      <div class="row"><div>Santri</div><div class="right"><?= h($hdr['nama_santri'] ?? '-') ?> (ID: <?= h($hdr['santri_id'] ?? '-') ?>)</div></div>
      <div class="row"><div>Kasir</div><div class="right"><?= h($hdr['nama_user'] ?? '-') ?></div></div>
      <div class="row"><div>Tipe</div><div class="right"><?= h($hdr['tipe']) ?></div></div>
      <div class="row"><div>Status</div><div class="right"><?= h($hdr['status']) ?></div></div>
    </div>

    <div class="divider"></div>

    <table>
      <tbody>
        <?php foreach($items as $it): ?>
          <tr>
            <td colspan="3"><?= h(($it['kode'] ?? '').' '.($it['nama'] ?? '')) ?></td>
          </tr>
          <tr>
            <td class="muted" style="width:40%"><?= (int)$it['qty'] ?> x <?= rupiah($it['harga_satuan']) ?></td>
            <td></td>
            <td class="right" style="width:40%; font-weight:700;"><?= rupiah($it['subtotal']) ?></td>
          </tr>
        <?php endforeach; ?>
      </tbody>
    </table>

    <div class="divider"></div>

    <div class="row" style="font-size:13px; font-weight:800;">
      <div>Total</div>
      <div class="right"><?= rupiah($hdr['total']) ?></div>
    </div>

    <div class="divider"></div>

    <div class="center muted" style="font-size:11px;">Terima kasih 🙏</div>
  </div>

  <div class="toolbar no-print">
    <button class="btn" onclick="window.print()">🖨 Cetak</button>
    <button class="btn" onclick="window.close()">Tutup</button>
  </div>

  <script>
    // Otomatis buka dialog print saat halaman siap
    window.addEventListener('load', () => {
      setTimeout(() => { window.print(); }, 200);
    });
  </script>
</body>
</html>
