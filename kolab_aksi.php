<?php
// Semua aksi kolaborasi (POST): undang, hapus, terima, tolak, keluar
require 'helper.php';
require_login();
if ($_SERVER['REQUEST_METHOD'] !== 'POST') { header('Location: dashboard.php'); exit; }

function balik($url, $jenis, $pesan) {
    header('Location: ' . $url . (strpos($url, '?') !== false ? '&' : '?') . $jenis . '=' . urlencode($pesan));
    exit;
}

$u        = uid();
$aksi     = $_POST['aksi'] ?? '';
$todo_id  = (int)($_POST['todo_id'] ?? 0);
$kolab_id = (int)($_POST['kolab_id'] ?? 0);
$target   = (int)($_POST['target_id'] ?? 0);

if ($aksi === 'undang') {
    $kembali = 'kolaborasi.php?todo_id=' . $todo_id;

    // Hanya pemilik kegiatan yang boleh mengundang
    $stmt = mysqli_prepare($conn, "SELECT id FROM todos WHERE id = ? AND user_id = ?");
    mysqli_stmt_bind_param($stmt, 'ii', $todo_id, $u);
    mysqli_stmt_execute($stmt);
    if (!mysqli_fetch_assoc(mysqli_stmt_get_result($stmt))) { balik('dashboard.php', 'err', 'Kegiatan tidak ditemukan.'); }

    if ($target === $u) { balik($kembali, 'err', 'Kamu tidak bisa mengundang diri sendiri.'); }
    $stmt = mysqli_prepare($conn, "SELECT id FROM users WHERE id = ?");
    mysqli_stmt_bind_param($stmt, 'i', $target);
    mysqli_stmt_execute($stmt);
    if (!mysqli_fetch_assoc(mysqli_stmt_get_result($stmt))) { balik($kembali, 'err', 'Pengguna tidak ditemukan.'); }

    $stmt = mysqli_prepare($conn, "INSERT INTO kolaborasi (todo_id, user_id, pengundang_id) VALUES (?,?,?)
        ON DUPLICATE KEY UPDATE status = IF(status = 'diterima', 'diterima', 'pending'), pengundang_id = VALUES(pengundang_id)");
    mysqli_stmt_bind_param($stmt, 'iii', $todo_id, $target, $u);
    if (mysqli_stmt_execute($stmt)) { balik($kembali, 'msg', 'Undangan berhasil dikirim.'); }
    balik($kembali, 'err', 'Gagal mengirim undangan.');
}

if ($aksi === 'hapus') {
    // Pemilik menghapus/membatalkan kolaborator
    $kembali = 'kolaborasi.php?todo_id=' . $todo_id;
    $stmt = mysqli_prepare($conn, "DELETE k FROM kolaborasi k JOIN todos t ON t.id = k.todo_id WHERE k.id = ? AND t.user_id = ?");
    mysqli_stmt_bind_param($stmt, 'ii', $kolab_id, $u);
    mysqli_stmt_execute($stmt);
    balik($kembali, 'msg', 'Kolaborator dihapus dari kegiatan.');
}

if ($aksi === 'terima' || $aksi === 'tolak') {
    $status = $aksi === 'terima' ? 'diterima' : 'ditolak';
    $stmt = mysqli_prepare($conn, "UPDATE kolaborasi SET status = ? WHERE id = ? AND user_id = ? AND status = 'pending'");
    mysqli_stmt_bind_param($stmt, 'sii', $status, $kolab_id, $u);
    mysqli_stmt_execute($stmt);
    balik('undangan.php', 'msg', $aksi === 'terima' ? 'Undangan diterima. Kegiatan muncul di daftarmu.' : 'Undangan ditolak.');
}

if ($aksi === 'keluar') {
    $stmt = mysqli_prepare($conn, "DELETE FROM kolaborasi WHERE id = ? AND user_id = ?");
    mysqli_stmt_bind_param($stmt, 'ii', $kolab_id, $u);
    mysqli_stmt_execute($stmt);
    $tg = $_POST['tanggal'] ?? '';
    balik(tanggal_valid($tg) ? 'detail.php?tanggal=' . $tg : 'dashboard.php', 'msg', 'Kamu sudah keluar dari kolaborasi.');
}

header('Location: dashboard.php');
exit;
