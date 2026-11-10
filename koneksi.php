<?php
// Konfigurasi database (sesuaikan dengan XAMPP/Laragon kamu)
$host = "localhost";
$user = "root";
$pass = "";
$db   = "todolist";

mysqli_report(MYSQLI_REPORT_OFF);
$conn = @mysqli_connect($host, $user, $pass, $db);

if (!$conn) {
    http_response_code(500);
    include '500.php';
    exit();
}

mysqli_set_charset($conn, "utf8mb4");