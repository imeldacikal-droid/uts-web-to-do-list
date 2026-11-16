<?php
require 'helper.php';
require_login();
$judul_halaman = 'To Do List';
$menu_aktif = 'dashboard';

// Jumlah kegiatan & yang selesai per bulan (tahun kalender)
$ringkasan = [];
$q = mysqli_query($conn, "SELECT MONTH(tanggal) AS b, COUNT(*) AS total, SUM(selesai) AS done FROM todos
    WHERE YEAR(tanggal) = $TAHUN AND " . sql_todo_terlihat() . " GROUP BY MONTH(tanggal)");
while ($r = mysqli_fetch_assoc($q)) { $ringkasan[(int)$r['b']] = $r; }

include 'partials/header.php';
?>
<div class="container-fluid px-4">
    <h1 class="mt-4">To Do List</h1>
    <ol class="breadcrumb mb-4">
        <li class="breadcrumb-item active">Time to get things done today! &middot; Kalender <?= $TAHUN ?></li>
    </ol>

    <?php if (!empty($_GET['msg'])): ?>
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <?= e($_GET['msg']) ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <div class="row">
        <?php foreach ($BULAN_PANJANG as $no => $nama):
            $total = (int)($ringkasan[$no]['total'] ?? 0);
            $done  = (int)($ringkasan[$no]['done'] ?? 0);
            list($warna, $teks) = $WARNA_BULAN[$no];
        ?>
        <div class="col-xl-3 col-md-6">
            <div class="card mb-4" style="background-color: <?= $warna ?>; color: <?= $teks ?>;">
                <div class="card-body d-flex justify-content-between align-items-center">
                    <span><?= $nama ?></span>
                    <span class="small"><?= $done ?>/<?= $total ?> selesai</span>
                </div>
                <div class="card-footer d-flex align-items-center justify-content-between">
                    <a class="small stretched-link" style="color: <?= $teks ?>;" href="bulan.php?bulan=<?= $no ?>">View Details</a>
                    <div class="small" style="color: <?= $teks ?>;"><i class="fas fa-angle-right"></i></div>
                </div>
            </div>
        </div>
        <?php endforeach; ?>
    </div>
</div>
<?php include 'partials/footer.php'; ?>
