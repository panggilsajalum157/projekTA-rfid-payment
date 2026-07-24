<?php
session_start();
if(!isset($_SESSION['user_id'])) header('Location: login.php');
require_once __DIR__ . '/../api/config.php';

// Ambil daftar barang
$items = $mysqli->query("SELECT * FROM barang ORDER BY id");
?>
<!doctype html>
<html lang="id">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width,initial-scale=1">
<title>POS - Kasir</title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&display=swap" rel="stylesheet">
<script src="https://cdn.tailwindcss.com"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<style>
  html,body{font-family:Inter,ui-sans-serif,system-ui,-apple-system,Segoe UI,Roboto,Noto Sans,Ubuntu,Cantarell,Helvetica Neue,Arial;}
  .glass{background:rgba(255,255,255,0.06); backdrop-filter: blur(8px); -webkit-backdrop-filter: blur(8px);}
</style>
</head>
<body class="min-h-screen bg-gradient-to-br from-slate-900 via-slate-800 to-slate-900 text-white">
  <div class="min-h-screen flex items-center justify-center p-6">
    <div class="w-full max-w-5xl">
      <div class="mb-8">
        <h1 class="text-2xl md:text-3xl font-bold">Koperasi - Point of Sale</h1>
        <p class="mt-2 text-sm text-slate-300">Scan kartu atau pilih barang manual — sistem akan mengurangi saldo & stok otomatis.</p>
      </div>

      <div class="grid md:grid-cols-2 gap-6">
        <!-- Kiri -->
        <div class="glass rounded-xl p-4">
          <h2 class="text-lg font-semibold text-slate-100 mb-3">Transaksi</h2>

          <div class="mb-3">
            <label class="block text-sm text-slate-200 font-medium">UID Kartu</label>
            <input id="uid" type="text" class="w-full px-3 py-2 rounded-lg bg-slate-700 border border-slate-600 text-white" placeholder="Tempel kartu lalu tunggu...">
          </div>

          <div class="mb-3">
            <label class="block text-sm text-slate-200 font-medium">Pilih Barang</label>
            <select id="barang" class="w-full px-3 py-2 rounded-lg bg-slate-700 border border-slate-600 text-white focus:outline-none focus:ring-2 focus:ring-yellow-400">
              <?php if($items && $items->num_rows>0): while($row = $items->fetch_assoc()): ?>
                <option value="<?= $row['id'] ?>" data-nama="<?= htmlspecialchars($row['nama']) ?>" data-harga="<?= (int)$row['harga'] ?>">
                  <?= htmlspecialchars($row['nama']) ?> — Rp <?= number_format((int)$row['harga'],0,',','.') ?>
                </option>
              <?php endwhile; else: ?>
                <option value="0" data-nama="-" data-harga="0">Tidak ada barang</option>
              <?php endif; ?>
            </select>
          </div>

          <div class="mb-4">
            <label class="block text-sm text-slate-200 font-medium">Qty</label>
            <input id="qty" type="number" value="1" min="1" class="w-32 px-3 py-2 rounded-lg bg-slate-700 border border-slate-600 text-white">
          </div>

          <div class="flex items-center gap-3">
            <button id="start" class="flex-1 rounded-lg py-2 px-4 bg-gradient-to-r from-amber-300 to-yellow-400 text-black font-semibold hover:brightness-95">▶ Start Scan</button>
            <button id="stop" class="rounded-lg py-2 px-4 bg-slate-700 text-white hover:bg-slate-600">■ Stop</button>
            <button id="addItem" class="rounded-lg py-2 px-4 bg-slate-700 text-white hover:bg-slate-600">＋ Tambah</button>
            <button id="pay" class="rounded-lg py-2 px-4 bg-gradient-to-r from-lime-300 to-green-400 text-black font-semibold hover:brightness-95">💸 Proses</button>
          </div>

          <div id="msg" class="mt-3 text-sm text-slate-300"></div>

          <div class="mt-5 text-xs text-lime-300 space-y-1">
            <div class="font-mono">UID terdeteksi:</div>
            <pre id="debug" class="font-mono whitespace-pre-wrap"></pre>
          </div>
        </div>

        <!-- Kanan -->
        <div class="glass rounded-xl p-4">
          <h2 class="text-lg font-semibold text-slate-100 mb-3">Ringkasan Transaksi</h2>
          <div id="cartBox" class="mt-2 hidden">
            <div class="text-slate-300 text-sm mb-2">Daftar Item</div>
            <ul id="cartList" class="text-sm text-slate-100 space-y-1"></ul>
          </div>
          <div class="space-y-1 text-sm">
            <div class="flex justify-between"><span class="text-slate-300">Barang</span><span class="text-slate-100" id="summary-name">-</span></div>
            <div class="flex justify-between"><span class="text-slate-300">Harga</span><span class="text-slate-100" id="summary-price">Rp 0</span></div>
            <div class="flex justify-between"><span class="text-slate-300">Qty</span><span class="text-slate-100" id="summary-qty">1</span></div>
          </div>
          <div class="mt-3 pt-3 border-t border-white/10 flex justify-between items-center">
            <span class="text-slate-300">Total</span>
            <span class="text-xl font-bold text-white" id="summary-total">Rp 0</span>
          </div>

          <div class="mt-6 text-sm text-slate-300">
            <b>Petunjuk:</b>
            <ol class="list-decimal list-inside space-y-1">
              <li>Klik <em>Start Scan</em> lalu tempel kartu pada reader (ESP32/RC522).</li>
              <li>Pilih barang & qty, klik <em>＋ Tambah</em> untuk memasukkan ke daftar (ulang jika lebih dari satu barang), lalu klik <em>Proses</em>.</li>
              <li>Jika saldo cukup, transaksi sukses dan saldo berkurang.</li>
            </ol>
          </div>
        </div>
      </div>

      <div class="mt-8 text-center text-xs text-slate-400">
        Powered by Goat • Tailwind POS
      </div>
    </div>
  </div>

<script>
let timer = null;
const uidEl   = document.getElementById('uid');
const msgEl   = document.getElementById('msg');
const debugEl = document.getElementById('debug');
const barangEl = document.getElementById('barang');
const qtyEl    = document.getElementById('qty');
const cartBox  = document.getElementById('cartBox');
const cartList = document.getElementById('cartList');
const summaryTotalEl = document.getElementById('summary-total');
const summaryNameEl = document.getElementById('summary-name');
const summaryPriceEl = document.getElementById('summary-price');
const summaryQtyEl = document.getElementById('summary-qty');

let cart = [];
function rupiah(n){ try { return 'Rp ' + (n||0).toLocaleString('id-ID'); } catch(e){ return 'Rp ' + (n||0); } }
function renderCart(){
  cartList.innerHTML = '';
  if (cart.length === 0) {
    cartBox.classList.add('hidden');
    summaryNameEl && (summaryNameEl.textContent='-');
    summaryPriceEl && (summaryPriceEl.textContent='Rp 0');
    summaryQtyEl && (summaryQtyEl.textContent='0');
    summaryTotalEl && (summaryTotalEl.textContent='Rp 0');
    return;
  }
  cartBox.classList.remove('hidden');
  let total = 0;
  let last = null;
  cart.forEach((it, idx) => {
    total += it.harga * it.qty;
    last = it;
    const li = document.createElement('li');
    li.className = 'flex justify-between';
    li.innerHTML = `<span>${idx+1}. ${it.nama} × ${it.qty}</span><span>${rupiah(it.harga * it.qty)}</span>`;
    cartList.appendChild(li);
  });
  if (last) {
    summaryNameEl && (summaryNameEl.textContent = last.nama);
    summaryPriceEl && (summaryPriceEl.textContent = rupiah(last.harga));
    summaryQtyEl && (summaryQtyEl.textContent = last.qty);
  }
  summaryTotalEl && (summaryTotalEl.textContent = rupiah(total));
}

document.getElementById('start').addEventListener('click', () => {
  if (timer) clearInterval(timer);
  msgEl.textContent = 'Mencari kartu.';
  timer = setInterval(async () => {
    try {
      const res = await fetch('get_uid.php', { cache: 'no-store' });
      const raw = (await res.text()).trim();
      debugEl.textContent = raw; // tampilkan JSON mentah (debug)

      let j = {};
      try { j = JSON.parse(raw); } catch(e){}
      const uid = (j.uid || j.UID || raw || '').toString().trim();
      if (uid) {
        uidEl.value = uid;
        msgEl.textContent = 'UID terbaca.';
      }
    } catch (e) {
      console.error(e);
      msgEl.textContent = 'Gagal ambil UID dari server';
    }
  }, 700);
});

document.getElementById('stop').addEventListener('click', () => {
  if (timer) { clearInterval(timer); timer = null; }
  msgEl.textContent = 'Scan dihentikan.';
});

/* Tambah item ke keranjang */
document.getElementById('addItem').addEventListener('click', () => {
  const barang_id = parseInt(barangEl.value);
  const opt = barangEl.options[barangEl.selectedIndex];
  const nama = opt ? (opt.getAttribute('data-nama') || opt.textContent || '').trim() : '';
  const harga = parseInt(opt ? (opt.getAttribute('data-harga') || '0') : '0');
  const qty = Math.max(1, parseInt(qtyEl.value || '1'));
  if (!barang_id || barang_id===0) { Swal.fire({icon:'warning', title:'Barang belum dipilih'}); return; }
  const exist = cart.find(i => i.id === barang_id);
  if (exist) { exist.qty += qty; } else { cart.push({id: barang_id, nama, harga, qty}); }
  renderCart();
  // reset qty ke 1
  qtyEl.value = '1';
});
qtyEl.addEventListener('keydown', (e) => {
  if (e.key === 'Enter') { e.preventDefault(); document.getElementById('addItem').click(); }
});

/* Proses pembayaran */
document.getElementById('pay').addEventListener('click', async () => {
  let uidRaw = uidEl.value.trim();
  if (uidRaw.startsWith('{') || uidRaw.startsWith('[')) {
    try { const o = JSON.parse(uidRaw); uidRaw = (o.uid || o.UID || '').toString().trim(); } catch(e){}
  }
  const uid = uidRaw;

  // Jika keranjang kosong, gunakan pilihan saat ini sbg fallback
  let items = cart.slice(); // copy
  if (items.length === 0) {
    const barang_id = parseInt(barangEl.value);
    const opt = barangEl.options[barangEl.selectedIndex];
    const nama = opt ? (opt.getAttribute('data-nama') || opt.textContent || '').trim() : '';
    const harga = parseInt(opt ? (opt.getAttribute('data-harga') || '0') : '0');
    const qty = Math.max(1, parseInt(qtyEl.value || '1'));
    if (!barang_id || barang_id===0) { Swal.fire({icon:'warning', title:'Barang belum dipilih'}); return; }
    items = [{id: barang_id, nama, harga, qty}];
  }

  if (!uid) {
    Swal.fire({icon:'warning', title:'UID kosong', text:'Tempelkan kartu dulu.'});
    return;
  }

  const fd = new FormData();
  fd.append('uid', uid);
  fd.append('items', JSON.stringify(items.map(x => ({id_barang:x.id, qty:x.qty}))));

  msgEl.textContent = 'Memproses transaksi...';
  try {
    const res = await fetch('../api/beli.php', { method: 'POST', body: fd });
    const j = await res.json();
    if (j.status === 'success') {
      Swal.fire({icon:'success', title:'Sukses', text: j.message || 'Transaksi berhasil'});
      msgEl.textContent = `Sukses. ID: ${j.transaksi_id}. Total: ${j.total ? rupiah(j.total) : 'Rp ?'}`;
      // kosongkan keranjang & reset ringkasan
      cart = [];
      renderCart();
      qtyEl.value = '1';
    } else {
      Swal.fire({icon:'error', title:'Gagal', text: j.message || 'Transaksi gagal'});
      msgEl.textContent = `Gagal: ${j.message || 'Transaksi gagal'}`;
    }
  } catch (err) {
    console.error(err);
    Swal.fire({icon:'error', title:'Error', text:'Terjadi kesalahan jaringan / server'});
    msgEl.textContent = 'Error saat menghubungi server';
  }
});
</script>
</body>
</html>
<?php /* DB schema ref: :contentReference[oaicite:0]{index=0} */ ?>
