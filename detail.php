<?php
require 'helper.php';
require_login();

$hari = $_GET['hari'] ?? '';
if (!isset($HARI[$hari])) { header('Location: dashboard.php'); exit; }
$judul_halaman = 'Detail ' . $hari;
$menu_aktif = 'dashboard';
$u = uid();

// Kegiatan milik sendiri + kegiatan kolaborasi yang sudah diterima
$stmt = mysqli_prepare($conn, "SELECT t.*, pm.nama AS pemilik,
        (SELECT COUNT(*) FROM kolaborasi k WHERE k.todo_id = t.id AND k.status = 'diterima') AS jml_kolab,
        (SELECT k2.id FROM kolaborasi k2 WHERE k2.todo_id = t.id AND k2.user_id = $u AND k2.status = 'diterima' LIMIT 1) AS kolab_id
    FROM todos t JOIN users pm ON pm.id = t.user_id
    WHERE t.hari = ? AND " . sql_todo_terlihat('t') . "
    ORDER BY t.tanggal, t.jam_mulai");
mysqli_stmt_bind_param($stmt, 's', $hari);
mysqli_stmt_execute($stmt);
$semua = mysqli_fetch_all(mysqli_stmt_get_result($stmt), MYSQLI_ASSOC);

// Hitungan untuk progres dan filter (selalu dari semua kegiatan hari ini)
$total = count($semua);
$done  = count(array_filter($semua, function ($r) { return $r['selesai']; }));
$belum = $total - $done;

// Filter status: semua | belum | selesai
$filter = $_GET['status'] ?? 'semua';
if (!in_array($filter, ['semua', 'belum', 'selesai'], true)) { $filter = 'semua'; }
$rows = array_values(array_filter($semua, function ($r) use ($filter) {
    if ($filter === 'belum')   { return !$r['selesai']; }
    if ($filter === 'selesai') { return (bool)$r['selesai']; }
    return true;
}));
$warna = $HARI[$hari];

$script_tambahan = <<<'JS'
<script>
// Centang kegiatan -> langsung disimpan ke database lewat toggle.php
var progres = document.getElementById('progress');
var jumlahSelesai = parseInt(progres.dataset.done, 10);
var jumlahTotal = parseInt(progres.dataset.total, 10);
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
                jumlahSelesai += cb.checked ? 1 : -1;
                progres.textContent = jumlahSelesai + '/' + jumlahTotal + ' selesai';
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
                <span class="badge bg-secondary ms-2" id="progress" data-done="<?= $done ?>" data-total="<?= $total ?>"><?= $done ?>/<?= $total ?> selesai</span>
            </span>
            <a href="form.php?hari=<?= e($hari) ?>" class="btn btn-sm btn-primary"><i class="fas fa-plus"></i> Tambah Kegiatan</a>
        </div>
        <div class="card-body">
            <div class="btn-group btn-group-sm mb-3" role="group" aria-label="Filter status">
                <a href="detail.php?hari=<?= e($hari) ?>&status=semua" class="btn <?= $filter === 'semua' ? 'btn-primary' : 'btn-outline-primary' ?>">Semua (<?= $total ?>)</a>
                <a href="detail.php?hari=<?= e($hari) ?>&status=belum" class="btn <?= $filter === 'belum' ? 'btn-primary' : 'btn-outline-primary' ?>">Belum selesai (<?= $belum ?>)</a>
                <a href="detail.php?hari=<?= e($hari) ?>&status=selesai" class="btn <?= $filter === 'selesai' ? 'btn-primary' : 'btn-outline-primary' ?>">Selesai (<?= $done ?>)</a>
            </div>

            <?php if (!$rows): ?>
                <p class="text-muted mb-0">
                    <?= $total === 0 ? 'Belum ada kegiatan di hari ' . e($hari) . '.' : 'Tidak ada kegiatan dengan filter ini.' ?>
                </p>
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
                            <th style="width:250px">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($rows as $r): $milikku = ((int)$r['user_id'] === $u); ?>
                        <tr class="<?= $r['selesai'] ? 'text-decoration-line-through text-muted' : '' ?>">
                            <td class="text-center"><input type="checkbox" class="form-check-input cek" data-id="<?= $r['id'] ?>" <?= $r['selesai'] ? 'checked' : '' ?>></td>
                            <td><?= e($r['hari']) ?></td>
                            <td><?= tgl_indo($r['tanggal']) ?></td>
                            <td><?= format_jam($r['jam_mulai'], $r['jam_selesai']) ?></td>
                            <td>
                                <?= e($r['kegiatan']) ?>
                                <span class="badge <?= $KATEGORI[$r['kategori']] ?? 'bg-secondary' ?> ms-1"><?= e($r['kategori']) ?></span>
                                <?php if (!$milikku): ?>
                                    <div class="small text-muted"><i class="fas fa-user-group"></i> Kolaborasi dari <?= e($r['pemilik']) ?></div>
                                <?php elseif ((int)$r['jml_kolab'] > 0): ?>
                                    <div class="small text-muted"><i class="fas fa-users"></i> <?= (int)$r['jml_kolab'] ?> kolaborator</div>
                                <?php endif; ?>
                            </td>
                            <td><?= e($r['lokasi']) ?></td>
                            <td>
                                <?php if ($milikku): ?>
                                    <a href="kolaborasi.php?todo_id=<?= $r['id'] ?>" class="btn btn-sm btn-info text-white"><i class="fas fa-paper-plane"></i> Send</a>
                                    <a href="form.php?id=<?= $r['id'] ?>" class="btn btn-sm btn-warning"><i class="fas fa-pen"></i> Edit</a>
                                    <form action="hapus.php" method="post" class="d-inline" onsubmit="return confirm('Hapus kegiatan ini?');">
                                        <input type="hidden" name="id" value="<?= $r['id'] ?>">
                                        <button class="btn btn-sm btn-danger"><i class="fas fa-trash"></i></button>
                                    </form>
                                <?php else: ?>
                                    <form action="kolab_aksi.php" method="post" class="d-inline" onsubmit="return confirm('Keluar dari kolaborasi kegiatan ini?');">
                                        <input type="hidden" name="aksi" value="keluar">
                                        <input type="hidden" name="kolab_id" value="<?= (int)$r['kolab_id'] ?>">
                                        <button class="btn btn-sm btn-outline-danger"><i class="fas fa-right-from-bracket"></i> Keluar</button>
                                    </form>
                                <?php endif; ?>
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
