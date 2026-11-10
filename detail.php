<?php
require 'helper.php';
require_login();

$hari = $_GET['hari'] ?? '';
if (!isset($HARI[$hari])) { header('Location: dashboard.php'); exit; }
$judul_halaman = 'Detail ' . $hari;
$menu_aktif = 'dashboard';

$stmt = mysqli_prepare($conn, "SELECT * FROM todos WHERE hari = ? AND user_id = ? ORDER BY tanggal, jam_mulai");
$u = uid();
mysqli_stmt_bind_param($stmt, 'si', $hari, $u);
mysqli_stmt_execute($stmt);
$rows = mysqli_fetch_all(mysqli_stmt_get_result($stmt), MYSQLI_ASSOC);
$done = count(array_filter($rows, function ($r) { return $r['selesai']; }));
$warna = $HARI[$hari];

$script_tambahan = <<<'JS'
<script>
// Centang kegiatan -> langsung disimpan ke database lewat toggle.php
document.querySelectorAll('.cek').forEach(function (cb) {
    cb.addEventListener('change', function () {
        var baris = cb.closest('tr');
        var data = new URLSearchParams({ id: cb.dataset.id, selesai: cb.checked ? 1 : 0 });
        fetch('toggle.php', { method: 'POST', body: data })
            .then(function (r) { return r.json(); })
            .then(function (res) {
                if (!res.ok) throw new Error();
                baris.classList.toggle('text-decoration-line-through', cb.checked);
                baris.classList.toggle('text-muted', cb.checked);
                var total = document.querySelectorAll('.cek').length;
                var selesai = document.querySelectorAll('.cek:checked').length;
                document.getElementById('progress').textContent = selesai + '/' + total + ' selesai';
            })
            .catch(function () { cb.checked = !cb.checked; alert('Gagal menyimpan, coba lagi.'); });
    });
});
</script>
JS;
include 'partials/header.php';
?>
<div class="container-fluid px-4">
    <h1 class="mt-4">Kegiatan Hari <?= e($hari) ?></h1>
    <ol class="breadcrumb mb-4">
        <li class="breadcrumb-item"><a href="dashboard.php">To Do List</a></li>
        <li class="breadcrumb-item active"><?= e($hari) ?></li>
    </ol>

    <?php if (!empty($_GET['msg'])): ?>
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <?= e($_GET['msg']) ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>
    <?php if (!empty($_GET['err'])): ?>
        <div class="alert alert-danger"><?= e($_GET['err']) ?></div>
    <?php endif; ?>

    <div class="mb-3 d-flex flex-wrap gap-2">
        <?php foreach ($HARI as $nama => $w): ?>
            <a href="detail.php?hari=<?= $nama ?>" class="btn btn-sm <?= $nama === $hari ? 'btn-dark' : 'btn-outline-dark' ?>"><?= $nama ?></a>
        <?php endforeach; ?>
    </div>

    <div class="card mb-4">
        <div class="card-header d-flex justify-content-between align-items-center" style="border-left: 6px solid <?= $warna ?>;">
            <span><i class="fas fa-list-check me-1"></i> Daftar Kegiatan <?= e($hari) ?>
                <span class="badge bg-secondary ms-2" id="progress"><?= $done ?>/<?= count($rows) ?> selesai</span>
            </span>
            <a href="form.php?hari=<?= e($hari) ?>" class="btn btn-sm btn-primary"><i class="fas fa-plus"></i> Tambah Kegiatan</a>
        </div>
        <div class="card-body">
            <?php if (!$rows): ?>
                <p class="text-muted mb-0">Belum ada kegiatan di hari <?= e($hari) ?>.</p>
            <?php else: ?>
            <div class="table-responsive">
                <table class="table table-bordered table-hover align-middle">
                    <thead>
                        <tr>
                            <th style="width:70px">Selesai</th>
                            <th>Hari</th>
                            <th>Tanggal</th>
                            <th>Pukul</th>
                            <th>Kegiatan</th>
                            <th>Tempat</th>
                            <th style="width:150px">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($rows as $r): ?>
                        <tr class="<?= $r['selesai'] ? 'text-decoration-line-through text-muted' : '' ?>">
                            <td class="text-center"><input type="checkbox" class="form-check-input cek" data-id="<?= $r['id'] ?>" <?= $r['selesai'] ? 'checked' : '' ?>></td>
                            <td><?= e($r['hari']) ?></td>
                            <td><?= tgl_indo($r['tanggal']) ?></td>
                            <td><?= format_jam($r['jam_mulai'], $r['jam_selesai']) ?></td>
                            <td><?= e($r['kegiatan']) ?></td>
                            <td><?= e($r['lokasi']) ?></td>
                            <td>
                                <a href="form.php?id=<?= $r['id'] ?>" class="btn btn-sm btn-warning"><i class="fas fa-pen"></i> Edit</a>
                                <form action="hapus.php" method="post" class="d-inline" onsubmit="return confirm('Hapus kegiatan ini?');">
                                    <input type="hidden" name="id" value="<?= $r['id'] ?>">
                                    <button class="btn btn-sm btn-danger"><i class="fas fa-trash"></i></button>
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
