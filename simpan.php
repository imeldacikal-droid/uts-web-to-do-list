<?php
// CREATE (id kosong) & UPDATE (id terisi) -> langsung tersimpan ke tabel `todos`
require 'helper.php';
require_login();
if ($_SERVER['REQUEST_METHOD'] !== 'POST') { header('Location: dashboard.php'); exit; }

$id       = (int)($_POST['id'] ?? 0);
$tanggal  = trim($_POST['tanggal'] ?? '');
$mulai    = trim($_POST['jam_mulai'] ?? '');
$selesai  = trim($_POST['jam_selesai'] ?? '');
$kegiatan = trim($_POST['kegiatan'] ?? '');
$lokasi   = trim($_POST['lokasi'] ?? '');

$balik = 'form.php?' . ($id ? 'id=' . $id . '&' : '');
$tglOk = DateTime::createFromFormat('Y-m-d', $tanggal);
if (!$tglOk || $tglOk->format('Y-m-d') !== $tanggal || $mulai === '' || $kegiatan === '' || $lokasi === '') {
    header('Location: ' . $balik . 'err=' . urlencode('Semua kolom wajib diisi (kecuali jam selesai).'));
    exit;
}
$hari    = $NAMA_HARI[(int)date('N', strtotime($tanggal))]; // hari dihitung dari tanggal
$mulai   = $mulai . ':00';
$selesai = $selesai === '' ? null : $selesai . ':00';

$u = uid();
if ($id) {
    $stmt = mysqli_prepare($conn, "UPDATE todos SET hari=?, tanggal=?, jam_mulai=?, jam_selesai=?, kegiatan=?, lokasi=? WHERE id=? AND user_id=?");
    mysqli_stmt_bind_param($stmt, 'ssssssii', $hari, $tanggal, $mulai, $selesai, $kegiatan, $lokasi, $id, $u);
    $pesan = 'Kegiatan berhasil diperbarui.';
} else {
    $stmt = mysqli_prepare($conn, "INSERT INTO todos (user_id, hari, tanggal, jam_mulai, jam_selesai, kegiatan, lokasi) VALUES (?,?,?,?,?,?,?)");
    mysqli_stmt_bind_param($stmt, 'issssss', $u, $hari, $tanggal, $mulai, $selesai, $kegiatan, $lokasi);
    $pesan = 'Kegiatan berhasil ditambahkan.';
}

if (!mysqli_stmt_execute($stmt)) {
    header('Location: ' . $balik . 'err=' . urlencode('Gagal menyimpan ke database: ' . mysqli_stmt_error($stmt)));
    exit;
}

header('Location: detail.php?hari=' . urlencode($hari) . '&msg=' . urlencode($pesan));
exit;
