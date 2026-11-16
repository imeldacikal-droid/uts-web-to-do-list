<?php
require 'helper.php';
require_login();
$bulan  = (int)($_GET['bulan'] ?? 0);
$minggu = (int)($_GET['minggu'] ?? 0);
if ($bulan < 1 || $bulan > 12 || $minggu < 1 || $minggu > jumlah_minggu($bulan, $TAHUN)) { header('Location: dashboard.php'); exit; }
$nama_bulan = $BULAN_PANJANG[$bulan];
$judul_halaman = 'Minggu ' . $minggu . ' ' . $nama_bulan;
$menu_aktif = 'dashboard';

$tgl = tanggal_minggu($bulan, $TAHUN, $minggu);
$per_tanggal = ringkasan_tanggal(reset($tgl), end($tgl));

include 'partials/header.php';
?>
<div class="container-fluid px-4">
    <h1 class="mt-4">Minggu <?= $minggu ?> - <?= $nama_bulan ?> <?= $TAHUN ?></h1>
    <ol class="breadcrumb mb-4">
        <li class="breadcrumb-item"><a href="dashboard.php">To Do List</a></li>
        <li class="breadcrumb-item"><a href="bulan.php?bulan=<?= $bulan ?>"><?= $nama_bulan ?></a></li>
        <li class="breadcrumb-item active">Minggu <?= $minggu ?></li>
    </ol>

    <div class="row">
        <?php foreach ($HARI as $nama => $warna):
            $d = $tgl[$nama];
            $aktif = ((int)date('n', strtotime($d)) === $bulan); // tanggal di luar bulan ini tidak bisa dibuka
            $total = $per_tanggal[$d]['total'] ?? 0;
            $done  = $per_tanggal[$d]['done'] ?? 0;
            $teks  = $nama === 'Selasa' ? '#212529' : '#fff'; // kuning butuh teks gelap
            if (!$aktif) { $warna = '#adb5bd'; $teks = '#fff'; }
        ?>
        <div class="col-xl-3 col-md-6">
            <div class="card mb-4" style="background-color: <?= $warna ?>; color: <?= $teks ?>; <?= $aktif ? '' : 'opacity:.6;' ?>">
                <div class="card-body d-flex justify-content-between align-items-center">
                    <span><?= $nama ?>, <?= tgl_pendek($d) ?></span>
                    <span class="small"><?= $aktif ? "$done/$total selesai" : '-' ?></span>
                </div>
                <div class="card-footer d-flex align-items-center justify-content-between">
                    <?php if ($aktif): ?>
                        <a class="small stretched-link" style="color: <?= $teks ?>;" href="detail.php?tanggal=<?= $d ?>">View Details</a>
                        <div class="small" style="color: <?= $teks ?>;"><i class="fas fa-angle-right"></i></div>
                    <?php else: ?>
                        <span class="small">Bukan bulan <?= $nama_bulan ?></span>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        <?php endforeach; ?>
    </div>
    <a href="bulan.php?bulan=<?= $bulan ?>" class="btn btn-secondary mb-4">Kembali</a>
</div>
<?php include 'partials/footer.php'; ?>
