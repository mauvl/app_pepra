-- --------------------------------------------------------
-- Host:                         localhost
-- Server version:               10.4.24-MariaDB - mariadb.org binary distribution
-- Server OS:                    Win64
-- HeidiSQL Version:             12.6.0.6765
-- --------------------------------------------------------

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET NAMES utf8 */;
/*!50503 SET NAMES utf8mb4 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;

-- Dumping database structure for pengaduan_sarana
CREATE DATABASE IF NOT EXISTS `pengaduan_sarana` /*!40100 DEFAULT CHARACTER SET utf8mb4 */;
USE `pengaduan_sarana`;

-- Dumping structure for table pengaduan_sarana.admin
CREATE TABLE IF NOT EXISTS `admin` (
  `id_admin` int(11) NOT NULL AUTO_INCREMENT,
  `username` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL,
  `nama_lengkap` varchar(100) NOT NULL,
  `email` varchar(100) DEFAULT NULL,
  `last_login` datetime DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id_admin`),
  UNIQUE KEY `username` (`username`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4;

-- Dumping data for table pengaduan_sarana.admin: ~1 rows (approximately)
INSERT INTO `admin` (`id_admin`, `username`, `password`, `nama_lengkap`, `email`, `last_login`, `created_at`) VALUES
	(1, 'admin', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Admin Prasarana', 'admin@pengaduan.sch.id', NULL, '2024-01-01 00:00:00');

-- Dumping structure for table pengaduan_sarana.aspirasi
CREATE TABLE IF NOT EXISTS `aspirasi` (
  `id_aspirasi` int(11) NOT NULL AUTO_INCREMENT,
  `nis` int(10) NOT NULL,
  `id_kategori` int(11) NOT NULL,
  `lokasi` varchar(100) NOT NULL,
  `deskripsi` text NOT NULL,
  `lampiran` varchar(255) DEFAULT NULL,
  `status` enum('Menunggu','Proses','Selesai') NOT NULL DEFAULT 'Menunggu',
  `feedback` text DEFAULT NULL,
  `bukti_selesai` varchar(255) DEFAULT NULL,
  `tanggal_dibuat` timestamp NOT NULL DEFAULT current_timestamp(),
  `tanggal_diupdate` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id_aspirasi`),
  KEY `fk_aspirasi_siswa` (`nis`),
  KEY `fk_aspirasi_kategori` (`id_kategori`),
  CONSTRAINT `fk_aspirasi_kategori` FOREIGN KEY (`id_kategori`) REFERENCES `kategori` (`id_kategori`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `fk_aspirasi_siswa` FOREIGN KEY (`nis`) REFERENCES `siswa` (`nis`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8mb4;

-- Dumping data for table pengaduan_sarana.aspirasi: ~0 rows (approximately)

-- Dumping structure for table pengaduan_sarana.kategori
CREATE TABLE IF NOT EXISTS `kategori` (
  `id_kategori` int(11) NOT NULL AUTO_INCREMENT,
  `nama_kategori` varchar(50) NOT NULL,
  `deskripsi` text DEFAULT NULL,
  `icon` varchar(50) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id_kategori`)
) ENGINE=InnoDB AUTO_INCREMENT=8 DEFAULT CHARSET=utf8mb4;

-- Dumping data for table pengaduan_sarana.kategori: ~7 rows (approximately)
INSERT INTO `kategori` (`id_kategori`, `nama_kategori`, `deskripsi`, `icon`, `created_at`) VALUES
	(1, 'Kelas', 'Masalah terkait ruang kelas', 'bi-door-closed', '2024-01-01 00:00:00'),
	(2, 'Laboratorium', 'Masalah lab komputer, kimia, fisika', 'bi-cpu', '2024-01-01 00:00:00'),
	(3, 'Perpustakaan', 'Masalah perpustakaan', 'bi-book', '2024-01-01 00:00:00'),
	(4, 'Toilet', 'Masalah toilet/WC', 'bi-droplet', '2024-01-01 00:00:00'),
	(5, 'Lapangan', 'Masalah lapangan olahraga', 'bi-flag', '2024-01-01 00:00:00'),
	(6, 'Aula', 'Masalah aula sekolah', 'bi-building', '2024-01-01 00:00:00'),
	(7, 'Lainnya', 'Masalah lainnya', 'bi-three-dots', '2024-01-01 00:00:00');

-- Dumping structure for table pengaduan_sarana.progres
CREATE TABLE IF NOT EXISTS `progres` (
  `id_progres` int(11) NOT NULL AUTO_INCREMENT,
  `id_aspirasi` int(11) NOT NULL,
  `status` varchar(50) NOT NULL,
  `keterangan` text NOT NULL,
  `dibuat_oleh` varchar(50) NOT NULL,
  `tanggal` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id_progres`),
  KEY `fk_progres_aspirasi` (`id_aspirasi`),
  CONSTRAINT `fk_progres_aspirasi` FOREIGN KEY (`id_aspirasi`) REFERENCES `aspirasi` (`id_aspirasi`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8mb4;

-- Dumping data for table pengaduan_sarana.progres: ~0 rows (approximately)

-- Dumping structure for table pengaduan_sarana.siswa
CREATE TABLE IF NOT EXISTS `siswa` (
  `nis` int(10) NOT NULL,
  `nama` varchar(100) NOT NULL,
  `kelas` varchar(10) NOT NULL,
  `password` varchar(255) NOT NULL,
  `email` varchar(100) DEFAULT NULL,
  `foto` varchar(255) DEFAULT NULL,
  `last_login` datetime DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`nis`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Dumping data for table pengaduan_sarana.siswa: ~2 rows (approximately)
INSERT INTO `siswa` (`nis`, `nama`, `kelas`, `password`, `email`, `foto`, `last_login`, `created_at`) VALUES
	(1234567890, 'Ahmad Fauzi', 'XII RPL 1', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'ahmad@student.sch.id', NULL, NULL, '2024-01-01 00:00:00'),
	(1234567891, 'Siti Rahma', 'XII RPL 2', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'siti@student.sch.id', NULL, NULL, '2024-01-01 00:00:00');

/*!40103 SET TIME_ZONE=IFNULL(@OLD_TIME_ZONE, 'system') */;
/*!40101 SET SQL_MODE=IFNULL(@OLD_SQL_MODE, '') */;
/*!40014 SET FOREIGN_KEY_CHECKS=IFNULL(@OLD_FOREIGN_KEY_CHECKS, 1) */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40111 SET SQL_NOTES=IFNULL(@OLD_SQL_NOTES, 1) */;

-- Note: Password untuk semua user demo adalah "password"