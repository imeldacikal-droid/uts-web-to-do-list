<?php
// Daftar akun baru -> tersimpan ke tabel `users`
require 'helper.php';
if (is_login()) { header('Location: dashboard.php'); exit; }

$error = '';
$f = ['nama' => '', 'username' => '', 'email' => '', 'gender' => 'Perempuan', 'tgl_lahir' => ''];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    foreach ($f as $k => $v) { $f[$k] = trim($_POST[$k] ?? $v); }
    $password = $_POST['password'] ?? '';
    $konfirm  = $_POST['konfirmasi'] ?? '';
    $tglOk    = DateTime::createFromFormat('Y-m-d', $f['tgl_lahir']);

    if ($f['nama'] === '' || $f['username'] === '' || $f['email'] === '' || $f['tgl_lahir'] === '') {
        $error = 'Semua kolom wajib diisi.';
    } elseif (!preg_match('/^[A-Za-z0-9_]{3,30}$/', $f['username'])) {
        $error = 'Username 3-30 karakter, hanya huruf, angka, dan underscore (_).';
    } elseif (!filter_var($f['email'], FILTER_VALIDATE_EMAIL)) {
        $error = 'Format email tidak valid.';
    } elseif (!in_array($f['gender'], ['Laki-laki', 'Perempuan'], true)) {
        $error = 'Pilih jenis kelamin.';
    } elseif (!$tglOk || $tglOk->format('Y-m-d') !== $f['tgl_lahir']) {
        $error = 'Tanggal lahir tidak valid.';
    } elseif (strlen($password) < 6) {
        $error = 'Password minimal 6 karakter.';
    } elseif ($password !== $konfirm) {
        $error = 'Konfirmasi password tidak sama.';
    } else {
        $cek = mysqli_prepare($conn, "SELECT id FROM users WHERE username = ?");
        mysqli_stmt_bind_param($cek, 's', $f['username']);
        mysqli_stmt_execute($cek);
        if (mysqli_fetch_assoc(mysqli_stmt_get_result($cek))) {
            $error = 'Username sudah dipakai, pilih yang lain.';
        } else {
            $hash = password_hash($password, PASSWORD_DEFAULT);
            $stmt = mysqli_prepare($conn, "INSERT INTO users (nama, username, email, gender, tgl_lahir, password) VALUES (?,?,?,?,?,?)");
            mysqli_stmt_bind_param($stmt, 'ssssss', $f['nama'], $f['username'], $f['email'], $f['gender'], $f['tgl_lahir'], $hash);
            if (mysqli_stmt_execute($stmt)) {
                header('Location: index.php?daftar=1');
                exit;
            }
            $error = 'Gagal menyimpan ke database.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no" />
    <title>Daftar Akun - To Do List</title>
    <link href="css/styles.css" rel="stylesheet" />
</head>
<body class="bg-primary">
    <div id="layoutAuthentication">
        <div id="layoutAuthentication_content">
            <main>
                <div class="container">
                    <div class="row justify-content-center">
                        <div class="col-lg-7">
                            <div class="card shadow-lg border-0 rounded-lg mt-5 mb-5">
                                <div class="card-header"><h3 class="text-center font-weight-light my-4">Daftar Akun</h3></div>
                                <div class="card-body">
                                    <?php if ($error): ?><div class="alert alert-danger"><?= e($error) ?></div><?php endif; ?>
                                    <form method="post" action="register.php">
                                        <div class="form-floating mb-3">
                                            <input class="form-control" id="nama" name="nama" type="text" placeholder="Nama lengkap" value="<?= e($f['nama']) ?>" required />
                                            <label for="nama">Nama Lengkap</label>
                                        </div>
                                        <div class="row mb-3">
                                            <div class="col-md-6">
                                                <div class="form-floating mb-3 mb-md-0">
                                                    <input class="form-control" id="username" name="username" type="text" placeholder="Username" value="<?= e($f['username']) ?>" required />
                                                    <label for="username">Username</label>
                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                <div class="form-floating">
                                                    <input class="form-control" id="email" name="email" type="email" placeholder="Email" value="<?= e($f['email']) ?>" required />
                                                    <label for="email">Email</label>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="row mb-3">
                                            <div class="col-md-6">
                                                <div class="form-floating mb-3 mb-md-0">
                                                    <select class="form-select" id="gender" name="gender" required>
                                                        <option value="Laki-laki" <?= $f['gender'] === 'Laki-laki' ? 'selected' : '' ?>>Laki-laki</option>
                                                        <option value="Perempuan" <?= $f['gender'] === 'Perempuan' ? 'selected' : '' ?>>Perempuan</option>
                                                    </select>
                                                    <label for="gender">Jenis Kelamin</label>
                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                <div class="form-floating">
                                                    <input class="form-control" id="tgl_lahir" name="tgl_lahir" type="date" value="<?= e($f['tgl_lahir']) ?>" required />
                                                    <label for="tgl_lahir">Tanggal Lahir</label>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="row mb-3">
                                            <div class="col-md-6">
                                                <div class="form-floating mb-3 mb-md-0">
                                                    <input class="form-control" id="password" name="password" type="password" placeholder="Password" required />
                                                    <label for="password">Password (min. 6 karakter)</label>
                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                <div class="form-floating">
                                                    <input class="form-control" id="konfirmasi" name="konfirmasi" type="password" placeholder="Konfirmasi" required />
                                                    <label for="konfirmasi">Konfirmasi Password</label>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="d-grid mt-4 mb-0">
                                            <button class="btn btn-primary" type="submit">Buat Akun</button>
                                        </div>
                                    </form>
                                </div>
                                <div class="card-footer text-center py-3">
                                    <div class="small"><a href="index.php">Sudah punya akun? Login</a></div>
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
