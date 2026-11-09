-- ========================================================
-- Database: `todolist`
-- File: database.sql
-- ========================================================

CREATE DATABASE IF NOT EXISTS `todolist` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci;
USE `todolist`;

-- --------------------------------------------------------
-- 1. Struktur Tabel `users` (Autentikasi & Session)
-- --------------------------------------------------------

CREATE TABLE IF NOT EXISTS `users` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `nama_lengkap` VARCHAR(100) NOT NULL,
  `username` VARCHAR(50) NOT NULL UNIQUE,
  `email` VARCHAR(100) NOT NULL UNIQUE,
  `password` VARCHAR(255) NOT NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------
-- 2. Struktur Tabel `tasks` (CRUD To-Do List Mingguan)
-- --------------------------------------------------------

CREATE TABLE IF NOT EXISTS `tasks` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `user_id` INT NOT NULL,
  `kegiatan` VARCHAR(255) NOT NULL,
  `hari` ENUM('Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu', 'Minggu') NOT NULL,
  `waktu` TIME NOT NULL,
  `deskripsi` TEXT NULL,
  `status` ENUM('Pending', 'Selesai') DEFAULT 'Pending',
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------
-- 3. Data Dummy (Untuk Uji Coba Pertama Kali)
-- Password dummy: 123456
-- --------------------------------------------------------

INSERT INTO `users` (`id`, `nama_lengkap`, `username`, `email`, `password`) VALUES
(1, 'Mahasiswa UTS', 'mahasiswa', 'mahasiswa@example.com', '$2y$10$wT0H7eQ8q0eGgH3R.dZg/.K3x1wWjQz3X9O5dK.XqL1mZ9YxQz1.G');

INSERT INTO `tasks` (`id`, `user_id`, `kegiatan`, `hari`, `waktu`, `deskripsi`, `status`) VALUES
(1, 1, 'Kuliah Pemrograman Web', 'Senin', '08:00:00', 'Materi PHP Native & MySQL', 'Selesai'),
(2, 1, 'Kerja Kelompok UTS', 'Rabu', '14:00:00', 'Mengerjakan Laporan dan Coding UI', 'Pending'),
(3, 1, 'Olahraga / Jogging', 'Sabtu', '16:30:00', 'Keliling lapangan kampus', 'Pending');








