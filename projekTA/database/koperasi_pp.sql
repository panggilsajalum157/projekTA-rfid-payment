-- phpMyAdmin SQL Dump
-- version 5.1.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Waktu pembuatan: 04 Sep 2025 pada 08.53
-- Versi server: 10.4.21-MariaDB
-- Versi PHP: 7.3.31

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `koperasi_pp`
--

-- --------------------------------------------------------

--
-- Struktur dari tabel `barang`
--

CREATE TABLE `barang` (
  `id` int(11) NOT NULL,
  `kode` varchar(50) DEFAULT NULL,
  `nama` varchar(150) DEFAULT NULL,
  `harga` int(11) NOT NULL,
  `stok` int(11) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Dumping data untuk tabel `barang`
--

INSERT INTO `barang` (`id`, `kode`, `nama`, `harga`, `stok`, `created_at`) VALUES
(1, 'K001', 'Roti Tawar', 3000, 47, '2025-08-27 19:01:39'),
(2, 'K002', 'Air Mineral 600ml', 4000, 30, '2025-08-27 19:01:39'),
(3, 'K003', 'Indomie Goreng', 3000, 100, '2025-09-04 03:25:28'),
(4, 'K004', 'Indomie Soto', 3000, 100, '2025-09-04 03:25:28'),
(5, 'K005', 'Mie Gelas Rasa Ayam', 2500, 80, '2025-09-04 03:25:28'),
(6, 'K006', 'Roti Coklat', 5000, 50, '2025-09-04 03:25:28'),
(7, 'K007', 'Roti Tawar', 8000, 30, '2025-09-04 03:25:28'),
(8, 'K008', 'Snack Taro', 2000, 120, '2025-09-04 03:25:28'),
(9, 'K009', 'Chiki Balls', 2500, 100, '2025-09-04 03:25:28'),
(10, 'K010', 'Potabee 60gr', 6000, 40, '2025-09-04 03:25:28'),
(11, 'K011', 'Teh Botol Sosro 350ml', 4500, 60, '2025-09-04 03:25:28'),
(12, 'K012', 'Teh Kotak 200ml', 4000, 70, '2025-09-04 03:25:28'),
(13, 'K013', 'Air Mineral 600ml', 4000, 100, '2025-09-04 03:25:28'),
(14, 'K014', 'Air Mineral 1500ml', 5000, 80, '2025-09-04 03:25:28'),
(15, 'K015', 'Susu UHT 200ml', 6000, 50, '2025-09-04 03:25:28'),
(16, 'K016', 'Kopi Good Day Sachet', 2000, 150, '2025-09-04 03:25:28'),
(17, 'K017', 'Energen Sachet', 2500, 100, '2025-09-04 03:25:28'),
(18, 'K018', 'Pop Ice', 3000, 90, '2025-09-04 03:25:28'),
(19, 'K019', 'Sabun Lifebuoy', 4000, 60, '2025-09-04 03:25:28'),
(20, 'K020', 'Shampoo Sachet Sunsilk', 1000, 200, '2025-09-04 03:25:28'),
(21, 'K021', 'Shampoo Sachet Clear', 1000, 200, '2025-09-04 03:25:28'),
(22, 'K022', 'Sikat Gigi Pepsodent', 6000, 50, '2025-09-04 03:25:28'),
(23, 'K023', 'Pasta Gigi Pepsodent 75gr', 10000, 40, '2025-09-04 03:25:28'),
(24, 'K024', 'Minyak Kayu Putih 30ml', 12000, 30, '2025-09-04 03:25:28'),
(25, 'K025', 'Bedak Tabur', 8000, 20, '2025-09-04 03:25:28'),
(26, 'K026', 'Detergen Rinso 200gr', 4000, 70, '2025-09-04 03:25:28'),
(27, 'K027', 'Buku Tulis 38 Lembar', 3000, 100, '2025-09-04 03:25:28'),
(28, 'K028', 'Buku Tulis 58 Lembar', 5000, 80, '2025-09-04 03:25:28'),
(29, 'K029', 'Pulpen Standard', 3000, 150, '2025-09-04 03:25:28'),
(30, 'K030', 'Pensil 2B', 2500, 100, '2025-09-04 03:25:28'),
(31, 'K031', 'Penghapus Karet', 2000, 100, '2025-09-04 03:25:28'),
(32, 'K032', 'Penggaris 30cm', 4000, 60, '2025-09-04 03:25:28'),
(33, 'K033', 'Tipe-X Cair', 6000, 50, '2025-09-04 03:25:28'),
(34, 'K034', 'Tipe-X Roll', 7000, 40, '2025-09-04 03:25:28'),
(35, 'K035', 'Spidol Hitam Snowman', 8000, 30, '2025-09-04 03:25:28'),
(36, 'K036', 'Sarung Biasa', 60000, 20, '2025-09-04 03:25:28'),
(37, 'K037', 'Sarung Wadimor', 120000, 10, '2025-09-04 03:25:28'),
(38, 'K038', 'Peci Hitam', 25000, 30, '2025-09-04 03:25:28'),
(39, 'K039', 'Mukena Remaja', 150000, 10, '2025-09-04 03:25:28'),
(40, 'K040', 'Sajadah Tipis', 40000, 15, '2025-09-04 03:25:28');

-- --------------------------------------------------------

--
-- Struktur dari tabel `rfid_latest`
--

CREATE TABLE `rfid_latest` (
  `id` tinyint(4) NOT NULL DEFAULT 1,
  `uid` varchar(100) DEFAULT NULL,
  `seen_at` timestamp NULL DEFAULT NULL ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Dumping data untuk tabel `rfid_latest`
--

INSERT INTO `rfid_latest` (`id`, `uid`, `seen_at`) VALUES
(1, '43217D28', '2025-08-28 17:44:08');

-- --------------------------------------------------------

--
-- Struktur dari tabel `rfid_scans`
--

CREATE TABLE `rfid_scans` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `uid` varchar(64) NOT NULL,
  `device_id` varchar(64) DEFAULT NULL,
  `idempotency_key` varchar(64) DEFAULT NULL,
  `note` varchar(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Struktur dari tabel `santri`
--

CREATE TABLE `santri` (
  `id` int(11) NOT NULL,
  `nis` varchar(50) DEFAULT NULL,
  `nama` varchar(150) NOT NULL,
  `rfid_uid` varchar(100) DEFAULT NULL,
  `saldo` bigint(20) DEFAULT 0,
  `aktif` tinyint(1) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Dumping data untuk tabel `santri`
--

INSERT INTO `santri` (`id`, `nis`, `nama`, `rfid_uid`, `saldo`, `aktif`, `created_at`) VALUES
(1, NULL, 'FAIZ AMRULLAH', '43217D28', 50000, 1, '2025-08-29 18:16:58'),
(4, NULL, 'ABDUL MUSLIKH', 'E17A6F50', 65000, 1, '2025-09-03 08:31:10'),
(5, NULL, 'SHEVA NAWAF', 'D1815850', 53000, 1, '2025-09-03 08:32:09');

-- --------------------------------------------------------

--
-- Struktur dari tabel `scanlog`
--

CREATE TABLE `scanlog` (
  `id` int(11) NOT NULL,
  `uid` varchar(50) NOT NULL,
  `scan_time` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Dumping data untuk tabel `scanlog`
--

INSERT INTO `scanlog` (`id`, `uid`, `scan_time`) VALUES
(1, '43217D28', '2025-08-31 10:55:18'),
(2, '43217D28', '2025-08-31 11:14:42');

-- --------------------------------------------------------

--
-- Struktur dari tabel `topup`
--

CREATE TABLE `topup` (
  `id` int(11) NOT NULL,
  `id_santri` int(11) NOT NULL,
  `nominal` int(11) NOT NULL,
  `metode` varchar(100) DEFAULT NULL,
  `bukti` varchar(255) DEFAULT NULL,
  `id_user` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Struktur dari tabel `transaksi`
--

CREATE TABLE `transaksi` (
  `id` int(11) NOT NULL,
  `id_santri` int(11) DEFAULT NULL,
  `id_user` int(11) DEFAULT NULL,
  `total` int(11) NOT NULL,
  `tipe` enum('pembelian','topup','refund') DEFAULT 'pembelian',
  `status` enum('success','failed','pending') DEFAULT 'pending',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Struktur dari tabel `transaksi_items`
--

CREATE TABLE `transaksi_items` (
  `id` int(11) NOT NULL,
  `id_transaksi` int(11) DEFAULT NULL,
  `id_barang` int(11) DEFAULT NULL,
  `qty` int(11) NOT NULL,
  `harga_satuan` int(11) NOT NULL,
  `subtotal` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Struktur dari tabel `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `username` varchar(100) NOT NULL,
  `password_hash` varchar(255) NOT NULL,
  `role` enum('admin','kasir') DEFAULT 'kasir',
  `nama` varchar(150) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Dumping data untuk tabel `users`
--

INSERT INTO `users` (`id`, `username`, `password_hash`, `role`, `nama`, `created_at`) VALUES
(1, 'admin', 'admin123', 'admin', 'Administrator', '2025-08-27 19:01:39'),
(2, 'panggilsajalum', 'Miftakhul123', 'admin', 'LUM', '2025-08-27 19:23:56');

--
-- Indexes for dumped tables
--

--
-- Indeks untuk tabel `barang`
--
ALTER TABLE `barang`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `kode` (`kode`);

--
-- Indeks untuk tabel `rfid_latest`
--
ALTER TABLE `rfid_latest`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uid` (`uid`);

--
-- Indeks untuk tabel `rfid_scans`
--
ALTER TABLE `rfid_scans`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uniq_device_idem` (`device_id`,`idempotency_key`),
  ADD KEY `uid` (`uid`),
  ADD KEY `created_at` (`created_at`);

--
-- Indeks untuk tabel `santri`
--
ALTER TABLE `santri`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `rfid_uid` (`rfid_uid`);

--
-- Indeks untuk tabel `scanlog`
--
ALTER TABLE `scanlog`
  ADD PRIMARY KEY (`id`);

--
-- Indeks untuk tabel `topup`
--
ALTER TABLE `topup`
  ADD PRIMARY KEY (`id`),
  ADD KEY `id_santri` (`id_santri`),
  ADD KEY `id_user` (`id_user`);

--
-- Indeks untuk tabel `transaksi`
--
ALTER TABLE `transaksi`
  ADD PRIMARY KEY (`id`),
  ADD KEY `id_santri` (`id_santri`),
  ADD KEY `id_user` (`id_user`);

--
-- Indeks untuk tabel `transaksi_items`
--
ALTER TABLE `transaksi_items`
  ADD PRIMARY KEY (`id`),
  ADD KEY `id_transaksi` (`id_transaksi`),
  ADD KEY `id_barang` (`id_barang`);

--
-- Indeks untuk tabel `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `username` (`username`);

--
-- AUTO_INCREMENT untuk tabel yang dibuang
--

--
-- AUTO_INCREMENT untuk tabel `barang`
--
ALTER TABLE `barang`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=41;

--
-- AUTO_INCREMENT untuk tabel `rfid_scans`
--
ALTER TABLE `rfid_scans`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT untuk tabel `santri`
--
ALTER TABLE `santri`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT untuk tabel `scanlog`
--
ALTER TABLE `scanlog`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT untuk tabel `topup`
--
ALTER TABLE `topup`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT untuk tabel `transaksi`
--
ALTER TABLE `transaksi`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT untuk tabel `transaksi_items`
--
ALTER TABLE `transaksi_items`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT untuk tabel `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- Ketidakleluasaan untuk tabel pelimpahan (Dumped Tables)
--

--
-- Ketidakleluasaan untuk tabel `topup`
--
ALTER TABLE `topup`
  ADD CONSTRAINT `topup_ibfk_1` FOREIGN KEY (`id_santri`) REFERENCES `santri` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `topup_ibfk_2` FOREIGN KEY (`id_user`) REFERENCES `users` (`id`) ON DELETE SET NULL ON UPDATE CASCADE;

--
-- Ketidakleluasaan untuk tabel `transaksi`
--
ALTER TABLE `transaksi`
  ADD CONSTRAINT `transaksi_ibfk_1` FOREIGN KEY (`id_santri`) REFERENCES `santri` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `transaksi_ibfk_2` FOREIGN KEY (`id_user`) REFERENCES `users` (`id`) ON DELETE SET NULL ON UPDATE CASCADE;

--
-- Ketidakleluasaan untuk tabel `transaksi_items`
--
ALTER TABLE `transaksi_items`
  ADD CONSTRAINT `transaksi_items_ibfk_1` FOREIGN KEY (`id_transaksi`) REFERENCES `transaksi` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `transaksi_items_ibfk_2` FOREIGN KEY (`id_barang`) REFERENCES `barang` (`id`) ON UPDATE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
