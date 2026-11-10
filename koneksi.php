<?php
// Koneksi MySQL (XAMPP default: user root, tanpa password).
// Database & tabel otomatis
$DB_HOST = 'localhost';
$DB_USER = 'root';
$DB_PASS = '';
$DB_NAME = 'todolist';

mysqli_report(MYSQLI_REPORT_OFF);
$conn = @mysqli_connect($DB_HOST, $DB_USER, $DB_PASS);
if (!$conn) {
    die('Koneksi database gagal: ' . mysqli_connect_error() . '. Pastikan MySQL di XAMPP sudah Start.');
}
mysqli_set_charset($conn, 'utf8mb4');
mysqli_query($conn, "CREATE DATABASE IF NOT EXISTS `$DB_NAME` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci");
mysqli_select_db($conn, $DB_NAME);

// Setup tabel hanya dijalankan kalau tabel `users` belum ada
$cek = mysqli_query($conn, "SHOW TABLES LIKE 'users'");
if ($cek && mysqli_num_rows($cek) === 0) {
    mysqli_query($conn, "CREATE TABLE users (
        id INT AUTO_INCREMENT PRIMARY KEY,
        nama VARCHAR(100) NOT NULL,
        username VARCHAR(50) NOT NULL UNIQUE,
        email VARCHAR(100) NOT NULL,
        gender ENUM('Laki-laki','Perempuan') NOT NULL,
        tgl_lahir DATE NOT NULL,
        password VARCHAR(255) NOT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

    // Akun contoh: admin / admin123
    $hash = password_hash('admin123', PASSWORD_DEFAULT);
    $st = mysqli_prepare($conn, "INSERT INTO users (nama, username, email, gender, tgl_lahir, password)
        VALUES ('Adzkia Zafira', 'admin', 'adzkia.zafira@students.paramadina.ac.id', 'Perempuan', '2007-05-11', ?)");
    mysqli_stmt_bind_param($st, 's', $hash);
    mysqli_stmt_execute($st);
    $adminId = mysqli_insert_id($conn);

    $adaTodos = mysqli_num_rows(mysqli_query($conn, "SHOW TABLES LIKE 'todos'")) > 0;
    mysqli_query($conn, "CREATE TABLE IF NOT EXISTS todos (
        id INT AUTO_INCREMENT PRIMARY KEY,
        user_id INT NOT NULL,
        hari ENUM('Senin','Selasa','Rabu','Kamis','Jumat','Sabtu','Minggu') NOT NULL,
        tanggal DATE NOT NULL,
        jam_mulai TIME NOT NULL,
        jam_selesai TIME NULL,
        kegiatan VARCHAR(255) NOT NULL,
        lokasi VARCHAR(255) NOT NULL,
        selesai TINYINT(1) NOT NULL DEFAULT 0,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

    if ($adaTodos) {
        // Tabel todos versi lama: tambah kolom user_id, kegiatan lama dimiliki akun admin
        $kol = mysqli_query($conn, "SHOW COLUMNS FROM todos LIKE 'user_id'");
        if ($kol && mysqli_num_rows($kol) === 0) {
            mysqli_query($conn, "ALTER TABLE todos ADD user_id INT NOT NULL DEFAULT 0 AFTER id");
            mysqli_query($conn, "UPDATE todos SET user_id = $adminId");
        }
    } else {
        mysqli_query($conn, "INSERT INTO todos (user_id, hari, tanggal, jam_mulai, jam_selesai, kegiatan, lokasi) VALUES
            ($adminId, 'Senin',  '2026-11-09','08:00:00','10:00:00','Kuliah Pemrograman Web','Kampus Paramadina'),
            ($adminId, 'Selasa', '2026-11-10','13:00:00','15:00:00','Rapat Himpunan Mahasiswa (HMTI)','Ruang Organisasi'),
            ($adminId, 'Rabu',   '2026-11-11','10:00:00','12:00:00','Kerjakan Tugas Proyek PHP','Perpustakaan / Rumah'),
            ($adminId, 'Kamis',  '2026-11-12','15:30:00','17:30:00','Diskusi Kelompok Website','Kafe Lokal Cikarang'),
            ($adminId, 'Jumat',  '2026-11-13','09:00:00','11:00:00','Bimbingan Dosen','Ruang Dosen'),
            ($adminId, 'Sabtu',  '2026-11-14','08:00:00',NULL,'Olahraga / Jogging & Refreshing','Taman Kota'),
            ($adminId, 'Minggu', '2026-11-15','19:00:00',NULL,'Evaluasi & Persiapan Kuliah Minggu Depan','Rumah')");
    }
}
