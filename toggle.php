<?php
// Centang/uncentang "selesai" -> langsung UPDATE ke database (dipanggil via fetch)
// Pemilik DAN kolaborator yang sudah menerima undangan boleh mencentang.
require 'helper.php';
header('Content-Type: application/json');
if (!is_login() || $_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(403);
    echo json_encode(['ok' => false]);
    exit;
}
$id = (int)($_POST['id'] ?? 0);
$selesai = (int)($_POST['selesai'] ?? 0) ? 1 : 0;
$stmt = mysqli_prepare($conn, "UPDATE todos SET selesai = ? WHERE id = ? AND " . sql_todo_terlihat('todos'));
mysqli_stmt_bind_param($stmt, 'ii', $selesai, $id);
echo json_encode(['ok' => $id > 0 && mysqli_stmt_execute($stmt)]);
