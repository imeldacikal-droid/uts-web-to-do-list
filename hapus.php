<?php
// DELETE -> langsung terhapus dari tabel `todos`
require 'helper.php';
require_login();
if ($_SERVER['REQUEST_METHOD'] !== 'POST') { header('Location: dashboard.php'); exit; }
$id = (int)($_POST['id'] ?? 0);
$u = uid();

$stmt = mysqli_prepare($conn, "SELECT tanggal FROM todos WHERE id = ? AND user_id = ?");
mysqli_stmt_bind_param($stmt, 'ii', $id, $u);
mysqli_stmt_execute($stmt);
$row = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));
$tanggal = $row['tanggal'] ?? '';

$stmt = mysqli_prepare($conn, "DELETE FROM todos WHERE id = ? AND user_id = ?");
mysqli_stmt_bind_param($stmt, 'ii', $id, $u);
if (mysqli_stmt_execute($stmt)) {
    header('Location: ' . ($tanggal ? 'detail.php?tanggal=' . urlencode($tanggal) . '&msg=' : 'dashboard.php?msg=') . urlencode('Kegiatan berhasil dihapus.'));
} else {
    header('Location: ' . ($tanggal ? 'detail.php?tanggal=' . urlencode($tanggal) . '&err=' : 'dashboard.php?err=') . urlencode('Gagal menghapus dari database.'));
}
exit;
