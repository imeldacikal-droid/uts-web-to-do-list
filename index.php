<?php
// Halaman pertama: login ->  http://localhost/todoo-web
require 'helper.php';

if (is_login()) { header('Location: dashboard.php'); exit; }

$error = '';
$username = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';

    $stmt = mysqli_prepare($conn, "SELECT id, nama, password FROM users WHERE username = ?");
    mysqli_stmt_bind_param($stmt, 's', $username);
    mysqli_stmt_execute($stmt);
    $row = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));

    if ($row && password_verify($password, $row['password'])) {
        session_regenerate_id(true);
        $_SESSION['user_id']  = (int)$row['id'];
        $_SESSION['username'] = $username;
        $_SESSION['nama']     = $row['nama'];
        header('Location: dashboard.php');
        exit;
    }
    $error = 'Username atau password salah.';
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no" />
    <title>Login - To Do List</title>
    <link href="css/styles.css" rel="stylesheet" />
</head>
<body class="bg-primary">
    <div id="layoutAuthentication">
        <div id="layoutAuthentication_content">
            <main>
                <div class="container">
                    <div class="row justify-content-center">
                        <div class="col-lg-5">
                            <div class="card shadow-lg border-0 rounded-lg mt-5">
                                <div class="card-header">
                                    <h3 class="text-center font-weight-light my-4">To Do List - Login</h3>
                                </div>
                                <div class="card-body">
                                    <?php if (!empty($_GET['daftar'])): ?>
                                        <div class="alert alert-success">Akun berhasil dibuat. Silakan login.</div>
                                    <?php endif; ?>
                                    <?php if ($error): ?>
                                        <div class="alert alert-danger"><?= e($error) ?></div>
                                    <?php endif; ?>
                                    <form method="post" action="index.php">
                                        <div class="form-floating mb-3">
                                            <input class="form-control" id="inputUsername" name="username" type="text"
                                                   placeholder="Username" value="<?= e($username) ?>" required autofocus />
                                            <label for="inputUsername">Username</label>
                                        </div>
                                        <div class="form-floating mb-3">
                                            <input class="form-control" id="inputPassword" name="password" type="password"
                                                   placeholder="Password" required />
                                            <label for="inputPassword">Password</label>
                                        </div>
                                        <div class="d-grid mt-4 mb-0">
                                            <button class="btn btn-primary" type="submit">Login</button>
                                        </div>
                                    </form>
                                </div>
                                <div class="card-footer text-center py-3">
                                    <div class="small"><a href="register.php">Belum punya akun? Daftar</a></div>
                                    <div class="small text-muted mt-2">Template: <a href="https://startbootstrap.com/template/sb-admin" target="_blank" rel="noopener">SB Admin</a> by Start Bootstrap (MIT License)</div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </main>
        </div>
    </div>
</body>
</html>