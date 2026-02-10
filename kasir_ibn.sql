-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Feb 10, 2026 at 07:47 AM
-- Server version: 10.4.32-MariaDB
-- PHP Version: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `kasir_ibn`
--

-- --------------------------------------------------------

--
-- Table structure for table `detailpenjualan`
--

CREATE TABLE `detailpenjualan` (
  `DetailID` int(11) NOT NULL,
  `PenjualanID` int(11) NOT NULL,
  `ProdukID` int(11) NOT NULL,
  `JumlahProduk` int(11) NOT NULL,
  `Subtotal` decimal(10,2) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `detailpenjualan`
--

INSERT INTO `detailpenjualan` (`DetailID`, `PenjualanID`, `ProdukID`, `JumlahProduk`, `Subtotal`) VALUES
(1, 1, 2, 1, 15000.00),
(2, 1, 1, 1, 10000.00),
(3, 2, 2, 1, 15000.00),
(4, 2, 1, 1, 10000.00),
(5, 3, 3, 1, 13000.00),
(6, 4, 2, 1, 15000.00),
(7, 5, 1, 1, 10000.00),
(8, 6, 4, 1, 2000000.00),
(9, 6, 1, 1, 10000.00),
(10, 6, 2, 1, 15000.00),
(11, 7, 5, 1, 10000.00),
(12, 7, 8, 1, 25000.00),
(13, 7, 6, 1, 40000.00),
(14, 7, 2, 1, 30000.00),
(15, 7, 3, 1, 20000.00),
(16, 7, 1, 1, 25000.00),
(17, 7, 7, 1, 25000.00),
(18, 8, 5, 1, 10000.00),
(19, 9, 5, 1, 10000.00),
(20, 9, 6, 1, 40000.00);

-- --------------------------------------------------------

--
-- Table structure for table `pelanggan`
--

CREATE TABLE `pelanggan` (
  `PelangganID` int(11) NOT NULL,
  `NamaPelanggan` varchar(255) NOT NULL,
  `KodeMember` varchar(20) DEFAULT NULL,
  `Alamat` text DEFAULT NULL,
  `NomorTelepon` varchar(15) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `pelanggan`
--

INSERT INTO `pelanggan` (`PelangganID`, `NamaPelanggan`, `KodeMember`, `Alamat`, `NomorTelepon`, `created_at`) VALUES
(1, 'Umum', NULL, '-', '-', '2026-02-09 07:15:50'),
(2, 'ibnnn', 'MBR001', 'adalah', '08112345433', '2026-02-09 07:18:34'),
(3, 'ibnu', 'MBR002', 'ss', '08723828634', '2026-02-09 07:19:12'),
(4, 'orkay', 'MBR003', 'bisnis', '08123452322', '2026-02-09 07:24:29');

-- --------------------------------------------------------

--
-- Table structure for table `penjualan`
--

CREATE TABLE `penjualan` (
  `PenjualanID` int(11) NOT NULL,
  `TanggalPenjualan` datetime NOT NULL DEFAULT current_timestamp(),
  `TotalHarga` decimal(10,2) NOT NULL,
  `Diskon` decimal(10,2) DEFAULT 0.00,
  `TotalBayar` decimal(10,2) NOT NULL,
  `PelangganID` int(11) NOT NULL,
  `UserID` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `penjualan`
--

INSERT INTO `penjualan` (`PenjualanID`, `TanggalPenjualan`, `TotalHarga`, `Diskon`, `TotalBayar`, `PelangganID`, `UserID`) VALUES
(1, '2026-02-09 14:19:20', 25000.00, 2500.00, 22500.00, 3, 3),
(2, '2026-02-09 14:21:48', 25000.00, 0.00, 25000.00, 1, 3),
(3, '2026-02-09 14:22:03', 13000.00, 1300.00, 11700.00, 2, 3),
(4, '2026-02-09 14:22:38', 15000.00, 1500.00, 13500.00, 3, 2),
(5, '2026-02-09 14:22:52', 10000.00, 0.00, 10000.00, 1, 2),
(6, '2026-02-09 14:25:11', 2025000.00, 202500.00, 1822500.00, 4, 2),
(7, '2026-02-09 14:54:06', 175000.00, 17500.00, 157500.00, 3, 2),
(8, '2026-02-10 08:18:14', 10000.00, 0.00, 10000.00, 1, 2),
(9, '2026-02-10 13:28:56', 50000.00, 5000.00, 45000.00, 2, 2);

-- --------------------------------------------------------

--
-- Table structure for table `produk`
--

CREATE TABLE `produk` (
  `ProdukID` int(11) NOT NULL,
  `NamaProduk` varchar(255) NOT NULL,
  `Harga` decimal(10,2) NOT NULL,
  `Stok` int(11) NOT NULL DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `produk`
--

INSERT INTO `produk` (`ProdukID`, `NamaProduk`, `Harga`, `Stok`, `created_at`) VALUES
(1, 'kopi susu', 25000.00, 95, '2026-02-09 07:16:52'),
(2, 'coklat ice', 30000.00, 95, '2026-02-09 07:17:09'),
(3, 'fresh milk', 20000.00, 13, '2026-02-09 07:17:29'),
(4, 'Paket Usaha', 2000000.00, 4, '2026-02-09 07:23:38'),
(5, 'air putih', 10000.00, 27, '2026-02-09 07:29:26'),
(6, 'Cheesecake', 40000.00, 28, '2026-02-09 07:47:55'),
(7, 'Pudding', 25000.00, 19, '2026-02-09 07:49:45'),
(8, 'Brownies mini', 25000.00, 19, '2026-02-09 07:52:28');

-- --------------------------------------------------------

--
-- Table structure for table `user`
--

CREATE TABLE `user` (
  `UserID` int(11) NOT NULL,
  `Username` varchar(255) NOT NULL,
  `Password` varchar(255) NOT NULL,
  `Role` enum('admin','petugas') NOT NULL DEFAULT 'petugas',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `user`
--

INSERT INTO `user` (`UserID`, `Username`, `Password`, `Role`, `created_at`) VALUES
(1, 'admin', '0192023a7bbd73250516f069df18b500', 'admin', '2026-02-09 07:15:50'),
(2, 'ibn', '202cb962ac59075b964b07152d234b70', 'petugas', '2026-02-09 07:17:58'),
(3, 'dib', '202cb962ac59075b964b07152d234b70', 'petugas', '2026-02-09 07:18:09');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `detailpenjualan`
--
ALTER TABLE `detailpenjualan`
  ADD PRIMARY KEY (`DetailID`),
  ADD KEY `PenjualanID` (`PenjualanID`),
  ADD KEY `ProdukID` (`ProdukID`);

--
-- Indexes for table `pelanggan`
--
ALTER TABLE `pelanggan`
  ADD PRIMARY KEY (`PelangganID`),
  ADD UNIQUE KEY `KodeMember` (`KodeMember`);

--
-- Indexes for table `penjualan`
--
ALTER TABLE `penjualan`
  ADD PRIMARY KEY (`PenjualanID`),
  ADD KEY `PelangganID` (`PelangganID`),
  ADD KEY `UserID` (`UserID`);

--
-- Indexes for table `produk`
--
ALTER TABLE `produk`
  ADD PRIMARY KEY (`ProdukID`);

--
-- Indexes for table `user`
--
ALTER TABLE `user`
  ADD PRIMARY KEY (`UserID`),
  ADD UNIQUE KEY `Username` (`Username`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `detailpenjualan`
--
ALTER TABLE `detailpenjualan`
  MODIFY `DetailID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=21;

--
-- AUTO_INCREMENT for table `pelanggan`
--
ALTER TABLE `pelanggan`
  MODIFY `PelangganID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `penjualan`
--
ALTER TABLE `penjualan`
  MODIFY `PenjualanID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT for table `produk`
--
ALTER TABLE `produk`
  MODIFY `ProdukID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT for table `user`
--
ALTER TABLE `user`
  MODIFY `UserID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `detailpenjualan`
--
ALTER TABLE `detailpenjualan`
  ADD CONSTRAINT `detailpenjualan_ibfk_1` FOREIGN KEY (`PenjualanID`) REFERENCES `penjualan` (`PenjualanID`) ON DELETE CASCADE,
  ADD CONSTRAINT `detailpenjualan_ibfk_2` FOREIGN KEY (`ProdukID`) REFERENCES `produk` (`ProdukID`) ON DELETE CASCADE;

--
-- Constraints for table `penjualan`
--
ALTER TABLE `penjualan`
  ADD CONSTRAINT `penjualan_ibfk_1` FOREIGN KEY (`PelangganID`) REFERENCES `pelanggan` (`PelangganID`) ON DELETE CASCADE,
  ADD CONSTRAINT `penjualan_ibfk_2` FOREIGN KEY (`UserID`) REFERENCES `user` (`UserID`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
