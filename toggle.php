<?php
// Centang/uncentang "selesai" -> langsung UPDATE ke database (dipanggil via fetch)
require 'helper.php';
header('Content-Type: application/json');
if (!is_login() || $_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(403);
    echo json_encode(['ok' => false]);
    exit;
}
$id = (int)($_POST['id'] ?? 0);
$u = uid();
$selesai = (int)($_POST['selesai'] ?? 0) ? 1 : 0;
$stmt = mysqli_prepare($conn, "UPDATE todos SET selesai = ? WHERE id = ? AND user_id = ?");
mysqli_stmt_bind_param($stmt, 'iii', $selesai, $id, $u);
echo json_encode(['ok' => $id > 0 && mysqli_stmt_execute($stmt)]);
