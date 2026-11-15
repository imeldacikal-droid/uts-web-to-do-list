-- OPSIONAL: aplikasi sudah membuat database & tabel otomatis (lihat koneksi.php).
-- File ini hanya untuk import manual lewat phpMyAdmin.
-- Akun contoh (admin / admin123) dibuat otomatis oleh koneksi.php.

CREATE DATABASE IF NOT EXISTS `todolist` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci;
USE `todolist`;

CREATE TABLE IF NOT EXISTS `users` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `nama` VARCHAR(100) NOT NULL,
  `username` VARCHAR(50) NOT NULL UNIQUE,
  `email` VARCHAR(100) NOT NULL,
  `gender` ENUM('Laki-laki','Perempuan') NOT NULL,
  `tgl_lahir` DATE NOT NULL,
  `password` VARCHAR(255) NOT NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS `todos` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `user_id` INT NOT NULL,
  `hari` ENUM('Senin','Selasa','Rabu','Kamis','Jumat','Sabtu','Minggu') NOT NULL,
  `tanggal` DATE NOT NULL,
  `jam_mulai` TIME NOT NULL,
  `jam_selesai` TIME NULL,
  `kegiatan` VARCHAR(255) NOT NULL,
  `lokasi` VARCHAR(255) NOT NULL,
  `kategori` VARCHAR(20) NOT NULL DEFAULT 'Lainnya',
  `selesai` TINYINT(1) NOT NULL DEFAULT 0,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS `kolaborasi` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `todo_id` INT NOT NULL,
  `user_id` INT NOT NULL,
  `pengundang_id` INT NOT NULL,
  `status` ENUM('pending','diterima','ditolak') NOT NULL DEFAULT 'pending',
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  UNIQUE KEY `uniq_todo_user` (`todo_id`, `user_id`),
  FOREIGN KEY (`todo_id`) REFERENCES `todos`(`id`) ON DELETE CASCADE,
  FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE,
  FOREIGN KEY (`pengundang_id`) REFERENCES `users`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
