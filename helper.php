<?php
session_start();
require_once __DIR__ . '/koneksi.php';

// Daftar hari + warna kartu
$HARI = [
    'Senin'  => '#0d6efd',
    'Selasa' => '#ffc107',
    'Rabu'   => '#198754',
    'Kamis'  => '#dc3545',
    'Jumat'  => '#fd7e14',
    'Sabtu'  => '#795548',
    'Minggu' => '#6f42c1',
];
// date('N'): 1 = Senin ... 7 = Minggu
$NAMA_HARI  = [1 => 'Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu', 'Minggu'];
$NAMA_BULAN = [1 => 'Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun', 'Jul', 'Agu', 'Sep', 'Okt', 'Nov', 'Des'];

function e($s) { return htmlspecialchars((string)$s, ENT_QUOTES, 'UTF-8'); }
function is_login() { return !empty($_SESSION['user_id']); }
function uid() { return (int)($_SESSION['user_id'] ?? 0); }
function require_login() {
    if (!is_login()) { header('Location: index.php'); exit; }
}
function nama_user() { return $_SESSION['nama'] ?? $_SESSION['username'] ?? ''; }
function tgl_indo($tanggal) {
    global $NAMA_BULAN;
    $t = strtotime($tanggal);
    return date('j', $t) . ' ' . $NAMA_BULAN[(int)date('n', $t)] . ' ' . date('Y', $t);
}
function format_jam($mulai, $selesai) {
    $m = str_replace(':', '.', substr($mulai, 0, 5));
    $s = $selesai ? str_replace(':', '.', substr($selesai, 0, 5)) : 'selesai';
    return $m . ' - ' . $s;
}

// Kegiatan yang boleh dilihat pengguna: miliknya sendiri + yang undangannya sudah diterima
function sql_todo_terlihat($alias = 'todos') {
    $u = uid();
    return "($alias.user_id = $u OR $alias.id IN (SELECT todo_id FROM kolaborasi WHERE user_id = $u AND status = 'diterima'))";
}
// Jumlah undangan kolaborasi yang belum dijawab (untuk badge di sidebar)
function jml_undangan() {
    global $conn;
    $r = mysqli_query($conn, "SELECT COUNT(*) AS n FROM kolaborasi WHERE user_id = " . uid() . " AND status = 'pending'");
    return $r ? (int)mysqli_fetch_assoc($r)['n'] : 0;
}

// Kategori kegiatan => kelas warna badge Bootstrap
$KATEGORI = [
    'Kuliah'     => 'bg-primary',
    'Organisasi' => 'bg-success',
    'Pribadi'    => 'bg-info text-dark',
    'Lainnya'    => 'bg-secondary',
];
