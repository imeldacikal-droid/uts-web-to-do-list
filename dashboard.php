<?php
require 'helper.php';
require_login();
$judul_halaman = 'To Do List';
$menu_aktif = 'dashboard';

// Jumlah kegiatan & yang selesai per hari
$ringkasan = [];
$q = mysqli_query($conn, "SELECT hari, COUNT(*) AS total, SUM(selesai) AS done FROM todos WHERE user_id = " . uid() . " GROUP BY hari");
while ($r = mysqli_fetch_assoc($q)) { $ringkasan[$r['hari']] = $r; }

$semua = mysqli_query($conn, "SELECT * FROM todos WHERE user_id = " . uid() . " ORDER BY tanggal, jam_mulai");

$script_tambahan = <<<'JS'
<script src="https://cdn.jsdelivr.net/npm/simple-datatables@7.1.2/dist/umd/simple-datatables.min.js" crossorigin="anonymous"></script>
<script>
window.addEventListener('DOMContentLoaded', function () {
    var tabel = document.getElementById('datatablesSimple');
    if (tabel && window.simpleDatatables) { new simpleDatatables.DataTable(tabel); }
});
</script>
JS;
include 'partials/header.php';
?>
<div class="container-fluid px-4">
    <h1 class="mt-4">To Do List</h1>
    <ol class="breadcrumb mb-4">
        <li class="breadcrumb-item active">Time to get things done today!</li>
    </ol>

    <?php if (!empty($_GET['msg'])): ?>
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <?= e($_GET['msg']) ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <div class="row">
        <?php foreach ($HARI as $nama => $warna):
            $total = (int)($ringkasan[$nama]['total'] ?? 0);
            $done  = (int)($ringkasan[$nama]['done'] ?? 0);
            $teks  = $nama === 'Selasa' ? '#212529' : '#fff'; // kuning butuh teks gelap
        ?>
        <div class="col-xl-3 col-md-6">
            <div class="card mb-4" style="background-color: <?= $warna ?>; color: <?= $teks ?>;">
                <div class="card-body d-flex justify-content-between align-items-center">
                    <span><?= $nama ?></span>
                    <span class="small"><?= $done ?>/<?= $total ?> selesai</span>
                </div>
                <div class="card-footer d-flex align-items-center justify-content-between">
                    <a class="small stretched-link" style="color: <?= $teks ?>;" href="detail.php?hari=<?= $nama ?>">View Details</a>
                    <div class="small" style="color: <?= $teks ?>;"><i class="fas fa-angle-right"></i></div>
                </div>
            </div>
        </div>
        <?php endforeach; ?>
    </div>

    <div class="card mb-4">
        <div class="card-header d-flex justify-content-between align-items-center">
            <span><i class="fas fa-table me-1"></i> To-Do List (Senin - Minggu)</span>
            <a href="form.php" class="btn btn-sm btn-primary"><i class="fas fa-plus"></i> Tambah Kegiatan</a>
        </div>
        <div class="card-body">
            <table id="datatablesSimple">
                <thead>
                    <tr>
                        <th>Selesai</th>
                        <th>Hari</th>
                        <th>Tanggal</th>
                        <th>Pukul</th>
                        <th>Kegiatan</th>
                        <th>Lokasi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($t = mysqli_fetch_assoc($semua)): ?>
                    <tr>
                        <td><input type="checkbox" class="form-check-input" disabled <?= $t['selesai'] ? 'checked' : '' ?>></td>
                        <td><a href="detail.php?hari=<?= e($t['hari']) ?>"><?= e($t['hari']) ?></a></td>
                        <td><?= tgl_indo($t['tanggal']) ?></td>
                        <td><?= format_jam($t['jam_mulai'], $t['jam_selesai']) ?></td>
                        <td><?= e($t['kegiatan']) ?></td>
                        <td><?= e($t['lokasi']) ?></td>
                    </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
<?php include 'partials/footer.php'; ?>
