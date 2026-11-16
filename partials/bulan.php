<?php
require 'helper.php';
require_login();
$bulan = (int)($_GET['bulan'] ?? 0);
if ($bulan < 1 || $bulan > 12) { header('Location: dashboard.php'); exit; }
$nama_bulan = $BULAN_PANJANG[$bulan];
$judul_halaman = $nama_bulan . ' ' . $TAHUN;
$menu_aktif = 'dashboard';

$n_minggu = jumlah_minggu($bulan, $TAHUN);
$awal  = date('Y-m-d', senin_awal_bulan($bulan, $TAHUN));
$akhir = date('Y-m-d', strtotime('+' . ($n_minggu * 7 - 1) . ' days', strtotime($awal)));
$per_tanggal = ringkasan_tanggal($awal, $akhir);

include 'partials/header.php';
?>
<div class="container-fluid px-4">
    <h1 class="mt-4"><?= $nama_bulan ?> <?= $TAHUN ?></h1>
    <ol class="breadcrumb mb-4">
        <li class="breadcrumb-item"><a href="dashboard.php">To Do List</a></li>
        <li class="breadcrumb-item active"><?= $nama_bulan ?></li>
    </ol>

    <div class="row">
        <?php for ($m = 1; $m <= $n_minggu; $m++):
            $tgl = tanggal_minggu($bulan, $TAHUN, $m);
            $total = $done = 0; $dalam = [];
            foreach ($tgl as $d) {
                if ((int)date('n', strtotime($d)) !== $bulan) { continue; } // hanya tanggal di bulan ini
                $dalam[] = $d;
                $total += $per_tanggal[$d]['total'] ?? 0;
                $done  += $per_tanggal[$d]['done'] ?? 0;
            }
            list($warna, $teks) = $WARNA_MINGGU[($m - 1) % count($WARNA_MINGGU)];
            $rentang = tgl_pendek($dalam[0]) . ' - ' . tgl_pendek(end($dalam));
        ?>
        <div class="col-xl-3 col-md-6">
            <div class="card mb-4" style="background-color: <?= $warna ?>; color: <?= $teks ?>;">
                <div class="card-body d-flex justify-content-between align-items-center">
                    <span>Minggu <?= $m ?></span>
                    <span class="small"><?= $done ?>/<?= $total ?> selesai</span>
                </div>
                <div class="px-3 pb-2 small"><?= e($rentang) ?></div>
                <div class="card-footer d-flex align-items-center justify-content-between">
                    <a class="small stretched-link" style="color: <?= $teks ?>;" href="minggu.php?bulan=<?= $bulan ?>&amp;minggu=<?= $m ?>">View Details</a>
                    <div class="small" style="color: <?= $teks ?>;"><i class="fas fa-angle-right"></i></div>
                </div>
            </div>
        </div>
        <?php endfor; ?>
    </div>
    <a href="dashboard.php" class="btn btn-secondary mb-4">Kembali</a>
</div>
<?php include 'partials/footer.php'; ?>
