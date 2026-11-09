<?php
session_start();
include '../koneksi.php';

// --- PROTEKSI SESSION DIMATIKAN SEMENTARA (Biar ga dilempar ke login pas kamu tes tampilan) ---
// if (!isset($_SESSION['user_id'])) {
//     header("Location: ../login.php");
//     exit();
// }

$user_id = $_SESSION['user_id'] ?? 1; // Default dummy user ID kalau belum login
$task_id = $_GET['id'] ?? '';

// Ambil data tugas berdasarkan ID
$query_data = mysqli_query($conn, "SELECT * FROM tasks WHERE id='$task_id'");
$data       = mysqli_fetch_assoc($query_data);

// Jika form disubmit (Tombol Simpan diklik)
if (isset($_POST['update'])) {
    $kegiatan  = mysqli_real_escape_string($conn, $_POST['kegiatan']);
    $hari      = $_POST['hari'];
    $waktu     = $_POST['waktu'];
    $deskripsi = mysqli_real_escape_string($conn, $_POST['deskripsi']);
    $status    = $_POST['status'];

    $update = mysqli_query($conn, "UPDATE tasks SET 
        kegiatan  = '$kegiatan', 
        hari      = '$hari', 
        waktu     = '$waktu', 
        deskripsi = '$deskripsi', 
        status    = '$status' 
        WHERE id  = '$task_id'");

    if ($update) {
        echo "<script>alert('Data berhasil diubah!'); window.location='index.php';</script>";
    }
}
?>

<!DOCTYPE html>
<html lang="id">
    <head>
        <meta charset="utf-8" />
        <meta http-equiv="X-UA-Compatible" content="IE=edge" />
        <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no" />
        <title>Edit Kegiatan - SB Admin</title>
        <link href="../css/styles.css" rel="stylesheet" />
        <script src="https://use.fontawesome.com/releases/v6.3.0/js/all.js" crossorigin="anonymous"></script>
    </head>
    <body class="sb-nav-fixed">
        <nav class="sb-topnav navbar navbar-expand navbar-dark bg-dark">
            <!-- Navbar Brand-->
            <a class="navbar-brand ps-3" href="index.php">To-Do List App</a>
            <!-- Sidebar Toggle-->
            <button class="btn btn-link btn-sm order-1 order-lg-0 me-4 me-lg-0" id="sidebarToggle" href="#!"><i class="fas fa-bars"></i></button>
            <!-- Navbar Menu-->
            <ul class="navbar-nav ms-auto me-3 me-lg-4">
                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle" id="navbarDropdown" href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false"><i class="fas fa-user fa-fw"></i> <?= $_SESSION['nama_lengkap'] ?? 'User'; ?></a>
                    <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="navbarDropdown">
                        <li><a class="dropdown-item" href="../logout.php">Logout</a></li>
                    </ul>
                </li>
            </ul>
        </nav>
        
        <div id="layoutSidenav">
            <!-- Sidebar Navigation -->
            <div id="layoutSidenav_nav">
                <nav class="sb-sidenav accordion sb-sidenav-dark" id="sidenavAccordion">
                    <div class="sb-sidenav-menu">
                        <div class="nav">
                            <div class="sb-sidenav-menu-heading">Menu Utama</div>
                            <a class="nav-link" href="index.php">
                                <div class="sb-nav-link-icon"><i class="fas fa-tachometer-alt"></i></div>
                                Dashboard
                            </a>
                            <a class="nav-link" href="tambah.php">
                                <div class="sb-nav-link-icon"><i class="fas fa-plus-circle"></i></div>
                                Tambah Tugas
                            </a>
                        </div>
                    </div>
                    <div class="sb-sidenav-footer">
                        <div class="small">Logged in as:</div>
                        <?= $_SESSION['username'] ?? 'User'; ?>
                    </div>
                </nav>
            </div>

            <!-- Content Body -->
            <div id="layoutSidenav_content">
                <main>
                    <div class="container-fluid px-4">
                        <!-- Judul Halaman SUDAH DIGANTI -->
                        <h1 class="mt-4">Edit Kegiatan</h1>
                        <ol class="breadcrumb mb-4">
                            <li class="breadcrumb-item"><a href="index.php">Dashboard</a></li>
                            <li class="breadcrumb-item active">Edit Kegiatan</li>
                        </ol>

                        <!-- Card Form Edit 
                        <div class="card mb-4 shadow">
                            <div class="card-header bg-warning text-dark">
                                <i class="fas fa-edit me-1"></i> Form Update Data Tugas
                            </div>
                            <div class="card-body">
                                <form method="POST" action="">
                                    <div class="mb-3">
                                        <label class="form-label">Nama Kegiatan</label>
                                        <input type="text" name="kegiatan" class="form-control" value="<?= htmlspecialchars($data['kegiatan'] ?? ''); ?>" required>
                                    </div>
                                    <div class="row">
                                        <div class="col-md-6 mb-3">
                                            <label class="form-label">Hari</label>
                                            <select name="hari" class="form-select" required>
                                                <?php 
                                                $hari_list = ['Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu', 'Minggu'];
                                                foreach($hari_list as $h) {
                                                    $selected = (($data['hari'] ?? '') == $h) ? 'selected' : '';
                                                    echo "<option value='$h' $selected>$h</option>";
                                                }
                                                ?>
                                            </select>
                                        </div>
                                        <div class="col-md-6 mb-3">
                                            <label class="form-label">Waktu</label>
                                            <input type="time" name="waktu" class="form-control" value="<?= $data['waktu'] ?? ''; ?>" required>
                                        </div>
                                    </div>
                                    <div class="mb-3">
                                        <label class="form-label">Deskripsi / Catatan</label>
                                        <textarea name="deskripsi" class="form-control" rows="3"><?= htmlspecialchars($data['deskripsi'] ?? ''); ?></textarea>
                                    </div>
                                    <div class="mb-3">
                                        <label class="form-label">Status Kegiatan</label>
                                        <select name="status" class="form-select">
                                            <option value="Pending" <?= (($data['status'] ?? '') == 'Pending') ? 'selected' : ''; ?>>Pending</option>
                                            <option value="Selesai" <?= (($data['status'] ?? '') == 'Selesai') ? 'selected' : ''; ?>>Selesai</option>
                                        </select>
                                    </div>
                                    <div class="d-flex justify-content-between mt-4">
                                        <a href="index.php" class="btn btn-secondary">Batal</a>
                                        <button type="submit" name="update" class="btn btn-warning">Simpan Perubahan</button>
                                    </div>
                                </form>
                            </div>
                        </div>

                    </div>
                </main>
                
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
        <script src="../js/scripts.js"></script>
    </body>
</html>