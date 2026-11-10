<?php
session_start();
include 'koneksi.php';

$error = "";
$sukses = "";

if (isset($_POST['reset'])) {
    $email     = trim($_POST['email']);
    $password  = $_POST['password'];
    $konfirmasi = $_POST['konfirmasi'];

    if (strlen($password) < 6) {
        $error = "Password baru minimal 6 karakter!";
    } elseif ($password !== $konfirmasi) {
        $error = "Konfirmasi password tidak cocok!";
    } else {
        $cek = mysqli_prepare($conn, "SELECT id FROM users WHERE email = ? LIMIT 1");
        mysqli_stmt_bind_param($cek, "s", $email);
        mysqli_stmt_execute($cek);
        $user = mysqli_fetch_assoc(mysqli_stmt_get_result($cek));

        if (!$user) {
            $error = "Email tidak terdaftar!";
        } else {
            $hash = password_hash($password, PASSWORD_DEFAULT);
            $upd = mysqli_prepare($conn, "UPDATE users SET password = ? WHERE id = ?");
            mysqli_stmt_bind_param($upd, "si", $hash, $user['id']);
            mysqli_stmt_execute($upd);
            header("Location: login.php?status=reset");
            exit();
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
    <title>Lupa Password - To Do List App</title>
    <link href="css/styles.css" rel="stylesheet" />
    <script src="https://use.fontawesome.com/releases/v6.3.0/js/all.js" crossorigin="anonymous"></script>
</head>
<body class="bg-primary">
    <div id="layoutAuthentication">
        <div id="layoutAuthentication_content">
            <main>
                <div class="container">
                    <div class="row justify-content-center">
                        <div class="col-lg-5">
                            <div class="card shadow-lg border-0 rounded-lg mt-5">
                                <div class="card-header"><h3 class="text-center font-weight-light my-4">Reset Password</h3></div>
                                <div class="card-body">
                                    <div class="small mb-3 text-muted">Masukkan email akunmu dan password baru.</div>

                                    <?php if ($error != ""): ?>
                                        <div class="alert alert-danger"><?= htmlspecialchars($error); ?></div>
                                    <?php endif; ?>

                                    <form method="POST" action="">
                                        <div class="form-floating mb-3">
                                            <input class="form-control" id="inputEmail" name="email" type="email" placeholder="name@example.com" required />
                                            <label for="inputEmail">Email address</label>
                                        </div>
                                        <div class="form-floating mb-3">
                                            <input class="form-control" id="inputPassword" name="password" type="password" placeholder="Password baru" required />
                                            <label for="inputPassword">Password Baru</label>
                                        </div>
                                        <div class="form-floating mb-3">
                                            <input class="form-control" id="inputKonfirmasi" name="konfirmasi" type="password" placeholder="Konfirmasi" required />
                                            <label for="inputKonfirmasi">Konfirmasi Password</label>
                                        </div>
                                        <div class="d-flex align-items-center justify-content-between mt-4 mb-0">
                                            <a class="small" href="login.php">Kembali ke login</a>
                                            <button type="submit" name="reset" class="btn btn-primary">Reset Password</button>
                                        </div>
                                    </form>
                                </div>
                                <div class="card-footer text-center py-3">
                                    <div class="small"><a href="register.php">Belum punya akun? Sign up!</a></div>
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
                    <div class="text-muted small">Copyright &copy; To-Do List App 2026</div>
                </div>
            </footer>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/js/bootstrap.bundle.min.js" crossorigin="anonymous"></script>
    <script src="js/scripts.js"></script>
</body>
</html>