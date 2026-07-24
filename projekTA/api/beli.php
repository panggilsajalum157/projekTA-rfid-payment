<?php
// api/beli.php — versi kompatibel PHP < 7.4 (tanpa arrow function) dan aman untuk multi-item
header('Content-Type: application/json');
session_start();
require_once __DIR__ . '/config.php';

function bad($msg, $code=400) {
  http_response_code($code);
  echo json_encode(['status'=>'error','message'=>$msg], JSON_UNESCAPED_UNICODE);
  exit;
}

$uid    = isset($_POST['uid']) ? trim($_POST['uid']) : '';
$idUser = isset($_SESSION['user_id']) ? (int)$_SESSION['user_id'] : null;

/**
 * Ambil items dari POST:
 * - Prefer: items = JSON [{"id_barang":1,"qty":2}, ...]
 * - Fallback: barang_id & qty (single)
 */
$items = [];
if (isset($_POST['items'])) {
  $arr = json_decode($_POST['items'], true);
  if (is_array($arr)) {
    foreach ($arr as $r) {
      $idb = isset($r['id_barang']) ? (int)$r['id_barang'] : 0;
      $q   = isset($r['qty']) ? (int)$r['qty'] : 0;
      if ($idb > 0 && $q > 0) $items[] = ['id_barang'=>$idb, 'qty'=>$q];
    }
  }
}
if (!$items) {
  $idBarang = isset($_POST['barang_id']) ? (int)$_POST['barang_id'] : 0;
  $qty      = isset($_POST['qty']) ? (int)$_POST['qty'] : 0;
  if ($idBarang > 0 && $qty > 0) $items = [['id_barang'=>$idBarang, 'qty'=>$qty]];
}

if ($uid === '') bad('UID tidak boleh kosong');
if (!$items)     bad('Daftar item kosong');

// Helper untuk bind_param dinamis (tanpa splat/variadic)
function bind_params_dynamic($stmt, $types, &$values) {
  // $values harus by-reference
  $params = [];
  $params[] = $types;
  foreach ($values as $k => &$v) { $params[] = &$v; }
  // call_user_func_array butuh referensi nyata
  return call_user_func_array([$stmt, 'bind_param'], $params);
}

try {
  $mysqli->begin_transaction();

  // Kunci data santri sesuai UID kartu
  $stmt = $mysqli->prepare("SELECT id, saldo FROM santri WHERE rfid_uid = ? AND aktif = 1 FOR UPDATE");
  if (!$stmt) { throw new Exception('Prepare santri gagal: '.$mysqli->error); }
  $stmt->bind_param('s', $uid);
  $stmt->execute();
  $res = $stmt->get_result();
  $san = $res ? $res->fetch_assoc() : null;
  if (!$san) { $mysqli->rollback(); bad('Santri tidak ditemukan / tidak aktif'); }

  // Ambil semua id barang dari items (kompatibel PHP <7.4 tanpa arrow fn)
  $ids = array_map(function ($x) {
    return isset($x['id_barang']) ? (int)$x['id_barang'] : 0;
  }, is_array($items) ? $items : []);
  // Filter id valid > 0
  $ids = array_values(array_filter($ids, function ($v) { return (int)$v > 0; }));

  if (count($ids) === 0) { $mysqli->rollback(); bad('Daftar item tidak valid'); }

  // Build query IN (?, ?, ...)
  $placeholders = implode(',', array_fill(0, count($ids), '?'));
  $types = str_repeat('i', count($ids));
  $sql = "SELECT id, nama, harga, stok FROM barang WHERE id IN ($placeholders) FOR UPDATE";
  $stmt = $mysqli->prepare($sql);
  if (!$stmt) { throw new Exception('Prepare barang gagal: '.$mysqli->error); }

  // bind dinamis
  // Pastikan $ids elemen by-ref
  for ($i=0; $i<count($ids); $i++) { $ids[$i] = (int)$ids[$i]; }
  bind_params_dynamic($stmt, $types, $ids);
  $stmt->execute();
  $res = $stmt->get_result();

  $barangMap = [];
  while ($r = $res->fetch_assoc()) {
    $barangMap[(int)$r['id']] = $r;
  }

  // Validasi items: qty>0, stok cukup; hitung total
  $total = 0;
  foreach ($items as $it) {
    $idb = (int)$it['id_barang'];
    $qty = (int)$it['qty'];
    if (!isset($barangMap[$idb])) { $mysqli->rollback(); bad('Barang tidak ditemukan: ID '.$idb); }
    if ($qty <= 0) { $mysqli->rollback(); bad('Qty tidak valid untuk ID '.$idb); }
    $stok  = (int)$barangMap[$idb]['stok'];
    $harga = (int)$barangMap[$idb]['harga'];
    if ($stok < $qty) { $mysqli->rollback(); bad('Stok tidak mencukupi untuk "'.$barangMap[$idb]['nama'].'"'); }
    $total += $harga * $qty;
  }

  // Cek saldo santri
  if ((int)$san['saldo'] < $total) { $mysqli->rollback(); bad('Saldo tidak mencukupi'); }

  // Buat transaksi pending
  $stmt = $mysqli->prepare("INSERT INTO transaksi (id_santri, id_user, total, tipe, status) VALUES (?, ?, ?, 'pembelian', 'pending')");
  if (!$stmt) { throw new Exception('Prepare transaksi gagal: '.$mysqli->error); }
  $stmt->bind_param('iii', $san['id'], $idUser, $total);
  $stmt->execute();
  $trxId = $mysqli->insert_id;

  // Siapkan statement insert item & update stok
  $insItem = $mysqli->prepare("INSERT INTO transaksi_items (id_transaksi, id_barang, qty, harga_satuan, subtotal) VALUES (?, ?, ?, ?, ?)");
  if (!$insItem) { throw new Exception('Prepare insert item gagal: '.$mysqli->error); }
  $updStok = $mysqli->prepare("UPDATE barang SET stok = stok - ? WHERE id = ?");
  if (!$updStok) { throw new Exception('Prepare update stok gagal: '.$mysqli->error); }

  foreach ($items as $it) {
    $idb = (int)$it['id_barang'];
    $qty = (int)$it['qty'];
    $harga = (int)$barangMap[$idb]['harga'];
    $subtotal = $harga * $qty;

    $insItem->bind_param('iiiii', $trxId, $idb, $qty, $harga, $subtotal);
    $insItem->execute();

    $updStok->bind_param('ii', $qty, $idb);
    $updStok->execute();
  }

  // Kurangi saldo santri
  $stmt = $mysqli->prepare("UPDATE santri SET saldo = saldo - ? WHERE id = ?");
  if (!$stmt) { throw new Exception('Prepare update saldo gagal: '.$mysqli->error); }
  $stmt->bind_param('ii', $total, $san['id']);
  $stmt->execute();

  // Set transaksi success
  $stmt = $mysqli->prepare("UPDATE transaksi SET status = 'success' WHERE id = ?");
  if (!$stmt) { throw new Exception('Prepare set status gagal: '.$mysqli->error); }
  $stmt->bind_param('i', $trxId);
  $stmt->execute();

  $mysqli->commit();

  echo json_encode([
    'status' => 'success',
    'message' => 'Pembelian berhasil',
    'transaksi_id' => $trxId,
    'total' => $total
  ], JSON_UNESCAPED_UNICODE);

} catch (Throwable $e) {
  if ($mysqli->errno) { $mysqli->rollback(); }
  http_response_code(500);
  echo json_encode(['status'=>'error','message'=>'Server error: '.$e->getMessage()], JSON_UNESCAPED_UNICODE);
}
