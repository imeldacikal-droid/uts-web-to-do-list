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

// ================= KALENDER 2026 (Bulan -> Minggu -> Hari) =================
$TAHUN = 2026; // tahun kalender yang dipakai (sesuai kalender 2026)
$BULAN_PANJANG = [1 => 'Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni', 'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'];
// Warna kotak bulan: [warna latar, warna teks]
$WARNA_BULAN = [
    1  => ['#0d6efd', '#fff'],    2  => ['#ffc107', '#212529'],
    3  => ['#198754', '#fff'],    4  => ['#dc3545', '#fff'],
    5  => ['#fd7e14', '#fff'],    6  => ['#6f42c1', '#fff'],
    7  => ['#20c997', '#212529'], 8  => ['#e83e8c', '#fff'],
    9  => ['#0dcaf0', '#212529'], 10 => ['#6610f2', '#fff'],
    11 => ['#84cc16', '#212529'], 12 => ['#ff5722', '#fff'],
];
// Warna kotak minggu (minggu ke-5 dst. mengulang dari awal)
$WARNA_MINGGU = [
    ['#0d6efd', '#fff'], ['#198754', '#fff'], ['#fd7e14', '#fff'], ['#6f42c1', '#fff'],
    ['#dc3545', '#fff'], ['#20c997', '#212529'],
];

// Senin dari minggu kalender yang memuat tanggal 1 bulan tsb
function senin_awal_bulan($bulan, $tahun) {
    $t = strtotime(sprintf('%04d-%02d-01', $tahun, $bulan));
    return strtotime('-' . ((int)date('N', $t) - 1) . ' days', $t);
}
// Jumlah minggu kalender (baris Senin-Minggu) dalam satu bulan: 4, 5, atau 6
function jumlah_minggu($bulan, $tahun) {
    $akhir = strtotime(sprintf('%04d-%02d-%02d', $tahun, $bulan, cal_days_in_month(CAL_GREGORIAN, $bulan, $tahun)));
    return (int)floor((($akhir - senin_awal_bulan($bulan, $tahun)) / 86400) / 7) + 1;
}
// Minggu ke-berapa (dalam bulannya) untuk sebuah tanggal Y-m-d
function minggu_ke($tanggal) {
    $t = strtotime($tanggal);
    $bulan = (int)date('n', $t); $tahun = (int)date('Y', $t);
    return (int)floor((($t - senin_awal_bulan($bulan, $tahun)) / 86400) / 7) + 1;
}
// 7 tanggal (Senin..Minggu) untuk minggu ke-N dalam bulan. Key = nama hari, value = Y-m-d
function tanggal_minggu($bulan, $tahun, $minggu) {
    global $NAMA_HARI;
    $senin = strtotime('+' . (($minggu - 1) * 7) . ' days', senin_awal_bulan($bulan, $tahun));
    $hasil = [];
    for ($i = 0; $i < 7; $i++) {
        $hasil[$NAMA_HARI[$i + 1]] = date('Y-m-d', strtotime("+$i days", $senin));
    }
    return $hasil;
}
// Apakah string Y-m-d valid & berada di tahun kalender?
function tanggal_valid($tanggal) {
    global $TAHUN;
    $d = DateTime::createFromFormat('Y-m-d', (string)$tanggal);
    return $d && $d->format('Y-m-d') === $tanggal && (int)$d->format('Y') === $TAHUN;
}
// Tanggal "27 Mar" tanpa tahun
function tgl_pendek($tanggal) {
    global $NAMA_BULAN;
    $t = strtotime($tanggal);
    return date('j', $t) . ' ' . $NAMA_BULAN[(int)date('n', $t)];
}
// Ringkasan kegiatan (total & selesai) per tanggal untuk rentang tanggal tertentu
function ringkasan_tanggal($dari, $sampai) {
    global $conn;
    $dari = mysqli_real_escape_string($conn, $dari);
    $sampai = mysqli_real_escape_string($conn, $sampai);
    $hasil = [];
    $q = mysqli_query($conn, "SELECT tanggal, COUNT(*) AS total, SUM(selesai) AS done FROM todos
        WHERE tanggal BETWEEN '$dari' AND '$sampai' AND " . sql_todo_terlihat() . " GROUP BY tanggal");
    while ($r = mysqli_fetch_assoc($q)) { $hasil[$r['tanggal']] = ['total' => (int)$r['total'], 'done' => (int)$r['done']]; }
    return $hasil;
}
// Breadcrumb lengkap sampai level hari. $akhir = teks item terakhir (aktif) atau null
function breadcrumb_tanggal($tanggal, $akhir = null) {
    global $BULAN_PANJANG, $TAHUN, $NAMA_HARI;
    $t = strtotime($tanggal);
    $b = (int)date('n', $t);
    $m = minggu_ke($tanggal);
    $namaHari = $NAMA_HARI[(int)date('N', $t)];
    $html  = '<li class="breadcrumb-item"><a href="dashboard.php">To Do List</a></li>';
    $html .= '<li class="breadcrumb-item"><a href="bulan.php?bulan=' . $b . '">' . $BULAN_PANJANG[$b] . ' ' . $TAHUN . '</a></li>';
    $html .= '<li class="breadcrumb-item"><a href="minggu.php?bulan=' . $b . '&amp;minggu=' . $m . '">Minggu ' . $m . '</a></li>';
    $label = $namaHari . ', ' . tgl_pendek($tanggal);
    if ($akhir === null) {
        $html .= '<li class="breadcrumb-item active">' . e($label) . '</li>';
    } else {
        $html .= '<li class="breadcrumb-item"><a href="detail.php?tanggal=' . e($tanggal) . '">' . e($label) . '</a></li>';
        $html .= '<li class="breadcrumb-item active">' . e($akhir) . '</li>';
    }
    return $html;
}
