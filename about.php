<?php
require 'helper.php';
require_login();
$judul_halaman = 'Profil Pengguna';
$menu_aktif = 'about';
$pesan = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nama     = trim($_POST['nama'] ?? '');
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';
    $gender   = $_POST['gender'] ?? '';
    $email    = trim($_POST['email'] ?? '');
    $tgl      = trim($_POST['tgl_lahir'] ?? '');
    $tglOk    = DateTime::createFromFormat('Y-m-d', $tgl);

    if ($nama === '' || $username === '' || $email === '' || !in_array($gender, ['Laki-laki', 'Perempuan'], true)
        || !$tglOk || $tglOk->format('Y-m-d') !== $tgl) {
        $error = 'Semua kolom wajib diisi dengan benar.';
    } elseif (!preg_match('/^[A-Za-z0-9_]{3,30}$/', $username)) {
        $error = 'Username 3-30 karakter, hanya huruf, angka, dan underscore (_).';
    } elseif ($password !== '' && strlen($password) < 6) {
        $error = 'Password baru minimal 6 karakter.';
    } else {
        $u = uid();
        if ($password !== '') {
            $hash = password_hash($password, PASSWORD_DEFAULT);
            $stmt = mysqli_prepare($conn, "UPDATE users SET nama=?, username=?, email=?, gender=?, tgl_lahir=?, password=? WHERE id=?");
            mysqli_stmt_bind_param($stmt, 'ssssssi', $nama, $username, $email, $gender, $tgl, $hash, $u);
        } else {
            $stmt = mysqli_prepare($conn, "UPDATE users SET nama=?, username=?, email=?, gender=?, tgl_lahir=? WHERE id=?");
            mysqli_stmt_bind_param($stmt, 'sssssi', $nama, $username, $email, $gender, $tgl, $u);
        }
        if (mysqli_stmt_execute($stmt)) {
            $_SESSION['username'] = $username;
            $_SESSION['nama']     = $nama;
            $pesan = 'Profil berhasil disimpan.';
        } else {
            $error = 'Username sudah dipakai atau gagal disimpan.';
        }
    }
}

$stmt = mysqli_prepare($conn, "SELECT nama, username, email, gender, tgl_lahir FROM users WHERE id = ?");
$u = uid();
mysqli_stmt_bind_param($stmt, 'i', $u);
mysqli_stmt_execute($stmt);
$p = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));
if ($error && $_SERVER['REQUEST_METHOD'] === 'POST') { // pertahankan isian saat gagal
    $p = ['nama' => $nama, 'username' => $username, 'email' => $email, 'gender' => $gender, 'tgl_lahir' => $tgl];
}

include 'partials/header.php';
?>
<div class="container-fluid px-4">
    <h1 class="mt-4">Profil Pengguna</h1>
    <ol class="breadcrumb mb-4">
        <li class="breadcrumb-item"><a href="dashboard.php">To Do List</a></li>
        <li class="breadcrumb-item active">Profil</li>
    </ol>

    <?php if ($pesan): ?><div class="alert alert-success"><?= e($pesan) ?></div><?php endif; ?>
    <?php if ($error): ?><div class="alert alert-danger"><?= e($error) ?></div><?php endif; ?>

    <div class="card mb-4" style="max-width: 700px;">
        <div class="card-header"><i class="fas fa-user-edit me-1"></i> Informasi Akun Saya</div>
        <div class="card-body">
            <form action="about.php" method="post">
                <div class="mb-3">
                    <label class="small mb-1" for="nama">Nama Lengkap</label>
                    <input class="form-control" id="nama" name="nama" type="text" value="<?= e($p['nama']) ?>" required />
                </div>
                <div class="mb-3">
                    <label class="small mb-1" for="username">Username (untuk login)</label>
                    <input class="form-control" id="username" name="username" type="text" value="<?= e($p['username']) ?>" required />
                </div>
                <div class="mb-3">
                    <label class="small mb-1" for="password">Password Baru (Opsional)</label>
                    <input class="form-control" id="password" name="password" type="password" placeholder="Kosongkan jika tidak ingin mengubah password" />
                </div>
                <div class="mb-3">
                    <label class="small mb-1" for="gender">Jenis Kelamin</label>
                    <select class="form-control" id="gender" name="gender" required>
                        <option value="Laki-laki" <?= $p['gender'] === 'Laki-laki' ? 'selected' : '' ?>>Laki-laki</option>
                        <option value="Perempuan" <?= $p['gender'] === 'Perempuan' ? 'selected' : '' ?>>Perempuan</option>
                    </select>
                </div>
                <div class="mb-3">
                    <label class="small mb-1" for="email">Email</label>
                    <input class="form-control" id="email" name="email" type="email" value="<?= e($p['email']) ?>" required />
                </div>
                <div class="mb-3">
                    <label class="small mb-1" for="tgl_lahir">Tanggal Lahir</label>
                    <input class="form-control" id="tgl_lahir" name="tgl_lahir" type="date" value="<?= e($p['tgl_lahir']) ?>" required />
                </div>
                <button class="btn btn-primary" type="submit">Simpan Perubahan</button>
            </form>
        </div>
    </div>
</div>
<?php include 'partials/footer.php'; ?>
