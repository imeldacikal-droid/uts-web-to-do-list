<?php
session_start();
include 'koneksi.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: 401.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$id = isset($_GET['id']) ? (int) $_GET['id'] : 0;

// Cari hari dulu supaya bisa kembali ke halaman detail yang benar
$cari = mysqli_prepare($conn, "SELECT hari FROM todos WHERE id = ? AND user_id = ? LIMIT 1");
mysqli_stmt_bind_param($cari, "ii", $id, $user_id);
mysqli_stmt_execute($cari);
$row = mysqli_fetch_assoc(mysqli_stmt_get_result($cari));

if (!$row) {
    header("Location: 404.php");
    exit();
}

$del = mysqli_prepare($conn, "DELETE FROM todos WHERE id = ? AND user_id = ?");
mysqli_stmt_bind_param($del, "ii", $id, $user_id);
mysqli_stmt_execute($del);

header("Location: edit.php?hari=" . urlencode($row['hari']) . "&status=hapus");
exit();