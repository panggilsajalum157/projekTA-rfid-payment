<?php
// web/transaksi.php
session_start();
if(!isset($_SESSION['user_id'])) header('Location: login.php');
require_once __DIR__ . '/../api/config.php';

// --- Input filter sederhana ---
$q      = isset($_GET['q']) ? trim($_GET['q']) : '';
$dari   = isset($_GET['dari']) ? trim($_GET['dari']) : '';
$sampai = isset($_GET['sampai']) ? trim($_GET['sampai']) : '';
$page   = max(1, (int)($_GET['page'] ?? 1));
$perPage= max(10, min(100, (int)($_GET['per_page'] ?? 20)));
$offset = ($page-1)*$perPage;

// Build WHERE
$where = [];
$params = [];
$types  = '';

if ($q !== '') {
  $where[] = "(s.nama LIKE CONCAT('%', ?, '%') OR u.nama LIKE CONCAT('%', ?, '%') OR t.id = ?)";
  $params[] = $q; $params[] = $q; $params[] = ctype_digit($q) ? (int)$q : 0;
  $types   .= 'ssi';
}
if ($dari !== '') {
  $where[] = "t.created_at >= ?";
  $params[] = $dari . " 00:00:00";
  $types   .= 's';
}
if ($sampai !== '') {
  $where[] = "t.created_at <= ?";
  $params[] = $sampai . " 23:59:59";
  $types   .= 's';
}
$whereSql = $where ? ('WHERE '.implode(' AND ',$where)) : '';

// Count
$sqlCount = "SELECT COUNT(*) AS jml
             FROM transaksi t
             LEFT JOIN santri s ON s.id = t.id_santri
             LEFT JOIN users  u ON u.id = t.id_user
             $whereSql";
$stmt = $mysqli->prepare($sqlCount);
if ($types) { $stmt->bind_param($types, ...$params); }
$stmt->execute();
$totalRows = (int)$stmt->get_result()->fetch_assoc()['jml'];
$stmt->close();

$totalPages = max(1, (int)ceil($totalRows/$perPage));
if ($page > $totalPages) { $page = $totalPages; $offset = ($page-1)*$perPage; }

// Data utama
$sql = "SELECT t.id, t.created_at, t.total, t.tipe, t.status,
               s.id AS santri_id, s.nama AS nama_santri,
               u.id AS user_id,  u.nama AS nama_user
        FROM transaksi t
        LEFT JOIN santri s ON s.id = t.id_santri
        LEFT JOIN users  u ON u.id = t.id_user
        $whereSql
        ORDER BY t.created_at DESC, t.id DESC
        LIMIT ? OFFSET ?";
$stmt = $mysqli->prepare($sql);
if ($types) {
  $stmt->bind_param($types.'ii', ...array_merge($params, [$perPage,$offset]));
} else {
  $stmt->bind_param('ii', $perPage, $offset);
}
$stmt->execute();
$res = $stmt->get_result();
$rows = [];
$trxIds = [];
while($r = $res->fetch_assoc()){
  $rows[] = $r;
  $trxIds[] = (int)$r['id'];
}
$stmt->close();

// ambil item2 per transaksi
$itemsByTrx = [];
if ($trxIds) {
  $in = implode(',', array_fill(0,count($trxIds),'?'));
  $sqlIt = "SELECT ti.id_transaksi, ti.qty, ti.harga_satuan, ti.subtotal,
                   b.nama AS nama_barang, b.kode AS kode_barang
            FROM transaksi_items ti
            LEFT JOIN barang b ON b.id = ti.id_barang
            WHERE ti.id_transaksi IN ($in)
            ORDER BY ti.id_transaksi ASC, ti.id ASC";
  $stmt = $mysqli->prepare($sqlIt);
  $stmt->bind_param(str_repeat('i', count($trxIds)), ...$trxIds);
  $stmt->execute();
  $ri = $stmt->get_result();
  while($it = $ri->fetch_assoc()){
    $itemsByTrx[(int)$it['id_transaksi']][] = $it;
  }
  $stmt->close();
}

function h($s){ return htmlspecialchars((string)$s, ENT_QUOTES, 'UTF-8'); }
function rupiah($n){ return 'Rp '.number_format((int)$n,0,',','.'); }
function qs($extra=[]){
  $base = $_GET; unset($base['page']);
  return http_build_query(array_merge($base,$extra));
}
?>
<!doctype html>
<html lang="id">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width,initial-scale=1">
<title>Riwayat Transaksi</title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&display=swap" rel="stylesheet">
<script src="https://cdn.tailwindcss.com"></script>
<style>
  html,body{font-family:Inter,ui-sans-serif,system-ui,-apple-system,Segoe UI,Roboto,Noto Sans,Ubuntu,Cantarell,Helvetica Neue,Arial;}
  .glass{background:rgba(255,255,255,0.06); backdrop-filter: blur(8px); -webkit-backdrop-filter: blur(8px);}
</style>
</head>
<body class="min-h-screen bg-gradient-to-br from-slate-900 via-slate-800 to-slate-900 text-white">
  <div class="min-h-screen px-4 py-6 md:py-10">
    <div class="max-w-6xl mx-auto">
      <div class="mb-6 md:mb-8 flex items-start md:items-center justify-between gap-4">
        <div>
          <h1 class="text-2xl md:text-3xl font-bold">Riwayat Transaksi</h1>
          <p class="text-slate-300 text-sm md:text-base mt-1">Data dari transaksi yang dibuat lewat POS (<code>pos.php</code>) / API (<code>beli.php</code>).</p>
        </div>
        <div class="flex gap-2">
          <a href="pos.php" class="rounded-lg py-2 px-4 bg-slate-700 hover:bg-slate-600">↩ Kembali ke POS</a>
        </div>
      </div>

      <!-- Filter -->
      <form method="get" class="glass rounded-xl p-4 md:p-5">
        <div class="grid md:grid-cols-5 gap-3">
          <div class="md:col-span-2">
            <label class="block text-sm text-slate-200 mb-1">Cari (Nama Santri/Kasir/ID)</label>
            <input type="text" name="q" value="<?= h($q) ?>" class="w-full px-3 py-2 rounded-lg bg-slate-700 border border-slate-600 text-white placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-yellow-400">
          </div>
          <div>
            <label class="block text-sm text-slate-200 mb-1">Dari</label>
            <input type="date" name="dari" value="<?= h($dari) ?>" class="w-full px-3 py-2 rounded-lg bg-slate-700 border border-slate-600 text-white">
          </div>
          <div>
            <label class="block text-sm text-slate-200 mb-1">Sampai</label>
            <input type="date" name="sampai" value="<?= h($sampai) ?>" class="w-full px-3 py-2 rounded-lg bg-slate-700 border border-slate-600 text-white">
          </div>
          <div>
            <label class="block text-sm text-slate-200 mb-1">Per Halaman</label>
            <select name="per_page" class="w-full px-3 py-2 rounded-lg bg-slate-700 border border-slate-600 text-white">
              <?php foreach([10,20,50,100] as $pp): ?>
                <option value="<?= $pp ?>" <?= $perPage==$pp?'selected':'' ?>><?= $pp ?></option>
              <?php endforeach; ?>
            </select>
          </div>
        </div>
        <div class="mt-3 flex gap-2">
          <button class="rounded-lg py-2 px-4 bg-gradient-to-r from-yellow-300 to-amber-300 text-black font-semibold hover:brightness-95">Terapkan</button>
          <a href="transaksi.php" class="rounded-lg py-2 px-4 bg-slate-700 hover:bg-slate-600">Reset</a>
        </div>
      </form>

      <!-- Ringkasan -->
      <div class="mt-4 text-sm text-slate-300">Menampilkan <span class="font-semibold"><?= (int)$totalRows ?></span> data • Halaman <span class="font-semibold"><?= (int)$page ?></span> / <?= (int)$totalPages ?></div>

      <!-- Tabel -->
      <div class="glass rounded-xl p-3 md:p-5 mt-3 overflow-x-auto">
        <table class="min-w-full text-sm">
          <thead>
            <tr class="text-left text-slate-300 border-b border-slate-700">
              <th class="py-2 pr-3">ID</th>
              <th class="py-2 pr-3">Tanggal</th>
              <th class="py-2 pr-3">Santri</th>
              <th class="py-2 pr-3">Kasir</th>
              <th class="py-2 pr-3">Tipe</th>
              <th class="py-2 pr-3">Status</th>
              <th class="py-2 pr-3">Total</th>
              <th class="py-2">Aksi</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-slate-800">
            <?php if(!$rows): ?>
              <tr><td colspan="8" class="py-4 text-slate-400">Tidak ada data.</td></tr>
            <?php else: ?>
              <?php foreach($rows as $r): 
                    $tid   = (int)$r['id'];
                    $items = $itemsByTrx[$tid] ?? [];
              ?>
              <tr class="align-top">
                <td class="py-3 pr-3 font-mono">#<?= $tid ?></td>
                <td class="py-3 pr-3"><?= h($r['created_at']) ?></td>
                <td class="py-3 pr-3">
                  <div class="font-semibold"><?= h($r['nama_santri'] ?? '-') ?></div>
                  <div class="text-xs text-slate-400">ID: <?= h($r['santri_id'] ?? '-') ?></div>
                </td>
                <td class="py-3 pr-3">
                  <div class="font-semibold"><?= h($r['nama_user'] ?? '-') ?></div>
                  <div class="text-xs text-slate-400">ID: <?= h($r['user_id'] ?? '-') ?></div>
                </td>
                <td class="py-3 pr-3 capitalize"><?= h($r['tipe']) ?></td>
                <td class="py-3 pr-3 capitalize">
                  <?php
                    $badge = 'bg-slate-700';
                    if ($r['status']==='success') $badge='bg-emerald-600';
                    elseif ($r['status']==='failed') $badge='bg-rose-600';
                    elseif ($r['status']==='pending') $badge='bg-amber-600';
                  ?>
                  <span class="text-xs px-2 py-1 rounded <?= $badge ?>"><?= h($r['status']) ?></span>
                </td>
                <td class="py-3 pr-3 font-semibold"><?= rupiah($r['total']) ?></td>
                <td class="py-3">
                  <div class="flex flex-wrap gap-2">
                    <details class="group">
                      <summary class="cursor-pointer rounded-lg px-3 py-2 bg-slate-700 hover:bg-slate-600">Lihat Item (<?= count($items) ?>)</summary>
                      <div class="mt-2 p-3 rounded-lg bg-slate-800/60 border border-slate-700">
                        <?php if(!$items): ?>
                          <div class="text-slate-400 text-sm">Tidak ada item.</div>
                        <?php else: ?>
                          <div class="overflow-x-auto">
                            <table class="min-w-[480px] text-xs">
                              <thead>
                                <tr class="text-left text-slate-300 border-b border-slate-700">
                                  <th class="py-1 pr-2">Barang</th>
                                  <th class="py-1 pr-2">Qty</th>
                                  <th class="py-1 pr-2">Harga</th>
                                  <th class="py-1">Subtotal</th>
                                </tr>
                              </thead>
                              <tbody class="divide-y divide-slate-800">
                                <?php foreach($items as $it): ?>
                                  <tr>
                                    <td class="py-1 pr-2"><?= h(($it['kode_barang'] ?? '').' '.($it['nama_barang'] ?? '')) ?></td>
                                    <td class="py-1 pr-2"><?= (int)$it['qty'] ?></td>
                                    <td class="py-1 pr-2"><?= rupiah($it['harga_satuan']) ?></td>
                                    <td class="py-1"><?= rupiah($it['subtotal']) ?></td>
                                  </tr>
                                <?php endforeach; ?>
                              </tbody>
                            </table>
                          </div>
                        <?php endif; ?>
                      </div>
                    </details>
                    <a href="cetak_struk.php?id=<?= $tid ?>" target="_blank"
                       class="rounded-lg py-2 px-3 bg-gradient-to-r from-yellow-300 to-amber-300 text-black font-semibold hover:brightness-95">
                      🧾 Cetak Struk
                    </a>
                  </div>
                </td>
              </tr>
              <?php endforeach; ?>
            <?php endif; ?>
          </tbody>
        </table>

        <!-- Pagination -->
        <?php if ($totalPages>1): ?>
          <div class="mt-4 flex flex-wrap gap-2">
            <?php
              $start = max(1, $page-3);
              $end   = min($totalPages, $page+3);
              if ($page>1) {
                echo '<a class="rounded-full px-3 py-1 bg-slate-700 hover:bg-slate-600" href="?'.h(qs(['page'=>1])).'">« Awal</a>';
                echo '<a class="rounded-full px-3 py-1 bg-slate-700 hover:bg-slate-600" href="?'.h(qs(['page'=>$page-1])).'">‹ Prev</a>';
              }
              for($i=$start;$i<=$end;$i++){
                $cls = $i==$page ? 'bg-yellow-300 text-black font-semibold' : 'bg-slate-700 hover:bg-slate-600';
                echo '<a class="rounded-full px-3 py-1 '.$cls.'" href="?'.h(qs(['page'=>$i])).'">'.$i.'</a>';
              }
              if ($page<$totalPages) {
                echo '<a class="rounded-full px-3 py-1 bg-slate-700 hover:bg-slate-600" href="?'.h(qs(['page'=>$page+1])).'">Next ›</a>';
                echo '<a class="rounded-full px-3 py-1 bg-slate-700 hover:bg-slate-600" href="?'.h(qs(['page'=>$totalPages])).'">Akhir »</a>';
              }
            ?>
          </div>
        <?php endif; ?>

      </div>
    </div>
  </div>
</body>
</html>
