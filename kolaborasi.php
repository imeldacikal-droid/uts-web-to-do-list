<?php
// Tombol "Send": cari user lalu kirim undangan kolaborasi untuk satu kegiatan
require 'helper.php';
require_login();
$menu_aktif = 'dashboard';
$u = uid();
$todo_id = (int)($_GET['todo_id'] ?? 0);

// Hanya pemilik kegiatan yang boleh membuka halaman ini
$stmt = mysqli_prepare($conn, "SELECT * FROM todos WHERE id = ? AND user_id = ?");
mysqli_stmt_bind_param($stmt, 'ii', $todo_id, $u);
mysqli_stmt_execute($stmt);
$todo = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));
if (!$todo) { header('Location: dashboard.php'); exit; }
$judul_halaman = 'Kolaborasi';

// Cari user (username / nama), kecuali diri sendiri dan yang sudah diundang
$q = trim($_GET['q'] ?? '');
$hasil = [];
if ($q !== '') {
    $like = '%' . $q . '%';
    $stmt = mysqli_prepare($conn, "SELECT id, nama, username FROM users
        WHERE (username LIKE ? OR nama LIKE ?) AND id <> ?
        AND id NOT IN (SELECT user_id FROM kolaborasi WHERE todo_id = ? AND status IN ('pending','diterima'))
        ORDER BY nama LIMIT 10");
    mysqli_stmt_bind_param($stmt, 'ssii', $like, $like, $u, $todo_id);
    mysqli_stmt_execute($stmt);
    $hasil = mysqli_fetch_all(mysqli_stmt_get_result($stmt), MYSQLI_ASSOC);
}

// Daftar kolaborator kegiatan ini
$stmt = mysqli_prepare($conn, "SELECT k.id, k.status, us.nama, us.username
    FROM kolaborasi k JOIN users us ON us.id = k.user_id
    WHERE k.todo_id = ? ORDER BY k.created_at DESC");
mysqli_stmt_bind_param($stmt, 'i', $todo_id);
mysqli_stmt_execute($stmt);
$anggota = mysqli_fetch_all(mysqli_stmt_get_result($stmt), MYSQLI_ASSOC);

$label = ['pending' => ['Menunggu', 'bg-warning text-dark'], 'diterima' => ['Diterima', 'bg-success'], 'ditolak' => ['Ditolak', 'bg-secondary']];
include 'partials/header.php';
?>
<div class="container-fluid px-4">
    <h1 class="mt-4">Kolaborasi Kegiatan</h1>
    <ol class="breadcrumb mb-4">
        <?= breadcrumb_tanggal($todo['tanggal'], 'Kolaborasi') ?>
    </ol>

    <?php if (!empty($_GET['msg'])): ?><div class="alert alert-success"><?= e($_GET['msg']) ?></div><?php endif; ?>
    <?php if (!empty($_GET['err'])): ?><div class="alert alert-danger"><?= e($_GET['err']) ?></div><?php endif; ?>

    <div class="card mb-4">
        <div class="card-body">
            <div class="small text-muted">Kegiatan yang diajak berkolaborasi</div>
            <h5 class="mb-1"><?= e($todo['kegiatan']) ?></h5>
            <div class="small"><?= e($todo['hari']) ?>, <?= tgl_indo($todo['tanggal']) ?> &middot; <?= format_jam($todo['jam_mulai'], $todo['jam_selesai']) ?> &middot; <?= e($todo['lokasi']) ?></div>
        </div>
    </div>

    <div class="card mb-4">
        <div class="card-header"><i class="fas fa-magnifying-glass me-1"></i> Cari User</div>
        <div class="card-body">
            <form method="get" action="kolaborasi.php" class="row g-2 mb-3">
                <input type="hidden" name="todo_id" value="<?= $todo_id ?>">
                <div class="col-sm-9">
                    <input type="text" name="q" class="form-control" placeholder="Ketik username atau nama..." value="<?= e($q) ?>" required>
                </div>
                <div class="col-sm-3 d-grid">
                    <button class="btn btn-primary" type="submit">Cari</button>
                </div>
            </form>

            <?php if ($q !== ''): ?>
                <?php if (!$hasil): ?>
                    <p class="text-muted mb-0">Tidak ada pengguna yang cocok dengan "<?= e($q) ?>" (atau sudah diundang).</p>
                <?php else: ?>
                <table class="table table-bordered align-middle mb-0">
                    <thead><tr><th>Nama</th><th>Username</th><th style="width:180px">Aksi</th></tr></thead>
                    <tbody>
                    <?php foreach ($hasil as $h): ?>
                        <tr>
                            <td><?= e($h['nama']) ?></td>
                            <td><?= e($h['username']) ?></td>
                            <td>
                                <form action="kolab_aksi.php" method="post" class="d-inline">
                                    <input type="hidden" name="aksi" value="undang">
                                    <input type="hidden" name="todo_id" value="<?= $todo_id ?>">
                                    <input type="hidden" name="target_id" value="<?= (int)$h['id'] ?>">
                                    <button class="btn btn-sm btn-info text-white"><i class="fas fa-paper-plane"></i> Kirim Undangan</button>
                                </form>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
                <?php endif; ?>
            <?php endif; ?>
        </div>
    </div>

    <div class="card mb-4">
        <div class="card-header"><i class="fas fa-users me-1"></i> Kolaborator</div>
        <div class="card-body">
            <?php if (!$anggota): ?>
                <p class="text-muted mb-0">Belum ada kolaborator. Cari user di atas untuk mengundang.</p>
            <?php else: ?>
            <table class="table table-bordered align-middle mb-0">
                <thead><tr><th>Nama</th><th>Username</th><th>Status</th><th style="width:120px">Aksi</th></tr></thead>
                <tbody>
                <?php foreach ($anggota as $a): ?>
                    <tr>
                        <td><?= e($a['nama']) ?></td>
                        <td><?= e($a['username']) ?></td>
                        <td><span class="badge <?= $label[$a['status']][1] ?>"><?= $label[$a['status']][0] ?></span></td>
                        <td>
                            <form action="kolab_aksi.php" method="post" class="d-inline" onsubmit="return confirm('Hapus kolaborator ini?');">
                                <input type="hidden" name="aksi" value="hapus">
                                <input type="hidden" name="todo_id" value="<?= $todo_id ?>">
                                <input type="hidden" name="kolab_id" value="<?= (int)$a['id'] ?>">
                                <button class="btn btn-sm btn-danger"><i class="fas fa-trash"></i></button>
                            </form>
                        </td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
            <?php endif; ?>
        </div>
    </div>

    <a href="detail.php?tanggal=<?= e($todo['tanggal']) ?>" class="btn btn-secondary mb-4">Kembali</a>
</div>
<?php include 'partials/footer.php'; ?>
