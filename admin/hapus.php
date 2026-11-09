<?php
session_start();
include 'koneksi.php';

// Proteksi Session (Harus Login)
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

// Cek apakah ada parameter ID dari URL
if (!isset($_GET['id'])) {
    header("Location: dashboard.php");
    exit();
}

$task_id = $_GET['id'];

// Ambil data tugas untuk memastikan data ada & milik user yang sedang login
$query = mysqli_query($conn, "SELECT * FROM tasks WHERE id='$task_id' AND user_id='$user_id'");
$data  = mysqli_fetch_assoc($query);

// Jika data tidak ditemukan, kembalikan ke dashboard
if (!$data) {
    header("Location: dashboard.php");
    exit();
}

// Logika penanganan ketika tombol "Ya, Hapus Data" diklik
if (isset($_POST['konfirmasi_hapus'])) {
    $delete = mysqli_query($conn, "DELETE FROM tasks WHERE id='$task_id' AND user_id='$user_id'");

    if ($delete) {
        header("Location: dashboard.php?msg=deleted");
        exit();
    } else {
        $error = "Gagal menghapus data dari database!";
    }
}
?>

<!DOCTYPE html>
<html lang="id">
    <head>
        <meta charset="utf-8" />
        <meta http-equiv="X-UA-Compatible" content="IE=edge" />
        <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no" />
        <title>Hapus Kegiatan - SB Admin</title>
        <link href="css/styles.css" rel="stylesheet" />
        <script src="https://use.fontawesome.com/releases/v6.3.0/js/all.js" crossorigin="anonymous"></script>
    </head>
    <body class="sb-nav-fixed">
        <nav class="sb-topnav navbar navbar-expand navbar-dark bg-dark">
            <!-- Navbar Brand-->
            <a class="navbar-brand ps-3" href="dashboard.php">To-Do List App</a>
            <!-- Sidebar Toggle-->
            <button class="btn btn-link btn-sm order-1 order-lg-0 me-4 me-lg-0" id="sidebarToggle" href="#!"><i class="fas fa-bars"></i></button>
            <!-- Navbar-->
            <ul class="navbar-nav ms-auto me-3 me-lg-4">
                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle" id="navbarDropdown" href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false"><i class="fas fa-user fa-fw"></i> <?= $_SESSION['nama_lengkap']; ?></a>
                    <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="navbarDropdown">
                        <li><a class="dropdown-item" href="logout.php">Logout</a></li>
                    </ul>
                </li>
            </ul>
        </nav>
        <div id="layoutSidenav">
            <div id="layoutSidenav_nav">
                <nav class="sb-sidenav accordion sb-sidenav-dark" id="sidenavAccordion">
                    <div class="sb-sidenav-menu">
                        <div class="nav">
                            <div class="sb-sidenav-menu-heading">Core</div>
                            <a class="nav-link" href="dashboard.php">
                                <div class="sb-nav-link-icon"><i class="fas fa-tachometer-alt"></i></div>
                                Dashboard
                            </a>
                            <div class="sb-sidenav-menu-heading">Interface</div>
                            <a class="nav-link collapsed" href="#" data-bs-toggle="collapse" data-bs-target="#pagesCollapseAuth" aria-expanded="false" aria-controls="pagesCollapseAuth">
                                <div class="sb-nav-link-icon"><i class="fas fa-book-open"></i></div>
                                Autentikasi
                                <div class="sb-sidenav-collapse-arrow"><i class="fas fa-angle-down"></i></div>
                            </a>
                            <div class="collapse" id="pagesCollapseAuth" aria-labelledby="headingOne" data-bs-parent="#sidenavAccordion">
                                <nav class="sb-sidenav-menu-nested nav">
                                    <a class="nav-link" href="login.php">Login</a>
                                    <a class="nav-link" href="register.php">Register</a>
                                    <a class="nav-link" href="password.php">Forgot Password</a>
                                </nav>
                            </div>
                        </div>
                    </div>
                    <div class="sb-sidenav-footer">
                        <div class="small">Logged in as:</div>
                        <?= $_SESSION['username']; ?>
                    </div>
                </nav>
            </div>
            <div id="layoutSidenav_content">
                <main>
                    <div class="container-fluid px-4">
                        <h1 class="mt-4 text-danger">Hapus Kegiatan</h1>
                        <ol class="breadcrumb mb-4">
                            <li class="breadcrumb-item"><a href="dashboard.php">Dashboard</a></li>
                            <li class="breadcrumb-item active">Hapus Kegiatan</li>
                        </ol>

                        <div class="row">
                            <div class="col-lg-6">
                                <div class="card mb-4 border-danger shadow">
                                    <div class="card-header bg-danger text-white">
                                        <i class="fas fa-exclamation-triangle me-1"></i> Konfirmasi Penghapusan
                                    </div>
                                    <div class="card-body">
                                        
                                        <?php if(isset($error)): ?>
                                            <div class="alert alert-danger"><?= $error; ?></div>
                                        <?php endif; ?>

                                        <p class="fs-5">Apakah kamu yakin ingin menghapus kegiatan berikut?</p>
                                        
                                        <div class="alert alert-secondary">
                                            <strong>Kegiatan:</strong> <?= htmlspecialchars($data['kegiatan']); ?><br>
                                            <strong>Hari/Waktu:</strong> <?= $data['hari']; ?>, <?= $data['waktu']; ?><br>
                                            <strong>Deskripsi:</strong> <?= htmlspecialchars($data['deskripsi']); ?>
                                        </div>

                                        <form method="POST" action="">
                                            <div class="d-flex justify-content-between mt-4">
                                                <a href="dashboard.php" class="btn btn-secondary"><i class="fas fa-arrow-left me-1"></i> Batal</a>
                                                <button type="submit" name="konfirmasi_hapus" class="btn btn-danger"><i class="fas fa-trash me-1"></i> Ya, Hapus Data</button>
                                            </div>
                                        </form>

                                    </div>
                                </div>
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
        <script src="js/scripts.js"></script>
    </body>
</html>