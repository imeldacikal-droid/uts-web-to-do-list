<?php
// Undangan kolaborasi yang masuk: terima atau tolak
require 'helper.php';
require_login();
$judul_halaman = 'Undangan';
$menu_aktif = 'undangan';

$stmt = mysqli_prepare($conn, "SELECT k.id, t.kegiatan, t.hari, t.tanggal, t.jam_mulai, t.jam_selesai, t.lokasi, pg.nama AS pengundang
    FROM kolaborasi k
    JOIN todos t ON t.id = k.todo_id
    JOIN users pg ON pg.id = k.pengundang_id
    WHERE k.user_id = ? AND k.status = 'pending'
    ORDER BY k.created_at DESC");
$u = uid();
mysqli_stmt_bind_param($stmt, 'i', $u);
mysqli_stmt_execute($stmt);
$rows = mysqli_fetch_all(mysqli_stmt_get_result($stmt), MYSQLI_ASSOC);

include 'partials/header.php';
?>
<div class="container-fluid px-4">
    <h1 class="mt-4">Undangan Kolaborasi</h1>
    <ol class="breadcrumb mb-4">
        <li class="breadcrumb-item"><a href="dashboard.php">To Do List</a></li>
        <li class="breadcrumb-item active">Undangan</li>
    </ol>

    <?php if (!empty($_GET['msg'])): ?><div class="alert alert-success"><?= e($_GET['msg']) ?></div><?php endif; ?>

    <div class="card mb-4">
        <div class="card-header"><i class="fas fa-envelope me-1"></i> Undangan yang belum dijawab</div>
        <div class="card-body">
            <?php if (!$rows): ?>
                <p class="text-muted mb-0">Belum ada undangan masuk.</p>
            <?php else: ?>
            <div class="table-responsive">
                <table class="table table-bordered align-middle">
                    <thead>
                        <tr><th>Dari</th><th>Kegiatan</th><th>Waktu</th><th>Tempat</th><th style="width:190px">Aksi</th></tr>
                    </thead>
                    <tbody>
                    <?php foreach ($rows as $r): ?>
                        <tr>
                            <td><?= e($r['pengundang']) ?></td>
                            <td><?= e($r['kegiatan']) ?></td>
                            <td><?= e($r['hari']) ?>, <?= tgl_indo($r['tanggal']) ?><br><span class="small text-muted"><?= format_jam($r['jam_mulai'], $r['jam_selesai']) ?></span></td>
                            <td><?= e($r['lokasi']) ?></td>
                            <td>
                                <form action="kolab_aksi.php" method="post" class="d-inline">
                                    <input type="hidden" name="aksi" value="terima">
                                    <input type="hidden" name="kolab_id" value="<?= (int)$r['id'] ?>">
                                    <button class="btn btn-sm btn-success"><i class="fas fa-check"></i> Terima</button>
                                </form>
                                <form action="kolab_aksi.php" method="post" class="d-inline">
                                    <input type="hidden" name="aksi" value="tolak">
                                    <input type="hidden" name="kolab_id" value="<?= (int)$r['id'] ?>">
                                    <button class="btn btn-sm btn-outline-danger"><i class="fas fa-xmark"></i> Tolak</button>
                                </form>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            <?php endif; ?>
        </div>
    </div>
</div>
<?php include 'partials/footer.php'; ?>
