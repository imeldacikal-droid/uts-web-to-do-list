<?php
session_start();
include 'koneksi.php';

if (isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit();
}

$error = "";
$nama = $username = $email = "";

if (isset($_POST['register'])) {
    $nama      = trim($_POST['nama_lengkap']);
    $username  = trim($_POST['username']);
    $email     = trim($_POST['email']);
    $password  = $_POST['password'];
    $konfirmasi = $_POST['konfirmasi'];

    if ($nama === "" || $username === "" || $email === "" || $password === "") {
        $error = "Semua field wajib diisi!";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = "Format email tidak valid!";
    } elseif (strlen($password) < 6) {
        $error = "Password minimal 6 karakter!";
    } elseif ($password !== $konfirmasi) {
        $error = "Konfirmasi password tidak cocok!";
    } else {
        // Cek username/email sudah dipakai atau belum
        $cek = mysqli_prepare($conn, "SELECT id FROM users WHERE username = ? OR email = ? LIMIT 1");
        mysqli_stmt_bind_param($cek, "ss", $username, $email);
        mysqli_stmt_execute($cek);
        mysqli_stmt_store_result($cek);

        if (mysqli_stmt_num_rows($cek) > 0) {
            $error = "Username atau email sudah terdaftar!";
        } else {
            $hash = password_hash($password, PASSWORD_DEFAULT);
            $ins = mysqli_prepare($conn, "INSERT INTO users (nama_lengkap, username, email, password) VALUES (?, ?, ?, ?)");
            mysqli_stmt_bind_param($ins, "ssss", $nama, $username, $email, $hash);

            if (mysqli_stmt_execute($ins)) {
                header("Location: login.php?status=success");
                exit();
            }
            $error = "Registrasi gagal, coba lagi.";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8" />
    <meta http-equiv="X-UA-Compatible" content="IE=edge" />
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no" />
    <title>Register - To Do List App</title>
    <link href="css/styles.css" rel="stylesheet" />
    <script src="https://use.fontawesome.com/releases/v6.3.0/js/all.js" crossorigin="anonymous"></script>
</head>
<body class="bg-primary">
    <div id="layoutAuthentication">
        <div id="layoutAuthentication_content">
            <main>
                <div class="container">
                    <div class="row justify-content-center">
                        <div class="col-lg-7">
                            <div class="card shadow-lg border-0 rounded-lg mt-5">
                                <div class="card-header"><h3 class="text-center font-weight-light my-4">Buat Akun</h3></div>
                                <div class="card-body">

                                    <?php if ($error != ""): ?>
                                        <div class="alert alert-danger"><?= htmlspecialchars($error); ?></div>
                                    <?php endif; ?>

                                    <form method="POST" action="">
                                        <div class="form-floating mb-3">
                                            <input class="form-control" id="inputNama" name="nama_lengkap" type="text" placeholder="Nama lengkap" value="<?= htmlspecialchars($nama); ?>" required />
                                            <label for="inputNama">Nama Lengkap</label>
                                        </div>
                                        <div class="form-floating mb-3">
                                            <input class="form-control" id="inputUsername" name="username" type="text" placeholder="Username" value="<?= htmlspecialchars($username); ?>" required />
                                            <label for="inputUsername">Username</label>
                                        </div>
                                        <div class="form-floating mb-3">
                                            <input class="form-control" id="inputEmail" name="email" type="email" placeholder="name@example.com" value="<?= htmlspecialchars($email); ?>" required />
                                            <label for="inputEmail">Email address</label>
                                        </div>
                                        <div class="row mb-3">
                                            <div class="col-md-6">
                                                <div class="form-floating mb-3 mb-md-0">
                                                    <input class="form-control" id="inputPassword" name="password" type="password" placeholder="Password" required />
                                                    <label for="inputPassword">Password</label>
                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                <div class="form-floating mb-3 mb-md-0">
                                                    <input class="form-control" id="inputKonfirmasi" name="konfirmasi" type="password" placeholder="Konfirmasi" required />
                                                    <label for="inputKonfirmasi">Konfirmasi Password</label>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="mt-4 mb-0">
                                            <div class="d-grid"><button type="submit" name="register" class="btn btn-primary btn-block">Daftar</button></div>
                                        </div>
                                    </form>
                                </div>
                                <div class="card-footer text-center py-3">
                                    <div class="small"><a href="login.php">Sudah punya akun? Login</a></div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </main>
        </div>
        <div id="layoutAuthentication_footer">
            <footer class="py-4 bg-light mt-auto">
                <div class="container-fluid px-4">
                    <div class="d-flex align-items-center justify-content-between small">
                        <div class="text-muted">Copyright &copy; To-Do List App 2026</div>
                    </div>
                </div>
            </footer>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/js/bootstrap.bundle.min.js" crossorigin="anonymous"></script>
    <script src="js/scripts.js"></script>
</body>
</html>