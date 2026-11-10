<?php
session_start();
include 'koneksi.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: 401.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$daftar_hari = ['Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu', 'Minggu'];

// Kalau dibuka dengan ?id= (link lama), cari harinya lalu arahkan ke ?hari=
if (isset($_GET['id']) && !isset($_GET['hari'])) {
    $cari = mysqli_prepare($conn, "SELECT hari FROM todos WHERE id = ? AND user_id = ? LIMIT 1");
    $id_cari = (int) $_GET['id'];
    mysqli_stmt_bind_param($cari, "ii", $id_cari, $user_id);
    mysqli_stmt_execute($cari);
    $row = mysqli_fetch_assoc(mysqli_stmt_get_result($cari));
    if ($row) {
        header("Location: edit.php?hari=" . urlencode($row['hari']));
        exit();
    }
    header("Location: 404.php");
    exit();
}

$hari = ucfirst(strtolower(trim($_GET['hari'] ?? '')));
if ($hari === '') {
    header("Location: index.php");
    exit();
}
if (!in_array($hari, $daftar_hari)) {
    header("Location: 404.php");
    exit();
}

$error = "";

// ---- Proses UPDATE (dari modal Edit) ----
if (isset($_POST['update'])) {
    $id       = (int) $_POST['id'];
    $hari_baru = $_POST['hari'];
    $kegiatan = trim($_POST['kegiatan']);
    $waktu    = trim($_POST['waktu']);

    if (!in_array($hari_baru, $daftar_hari)) {
        $error = "Hari tidak valid!";
    } elseif ($kegiatan === "" || $waktu === "") {
        $error = "Kegiatan dan waktu wajib diisi!";
    } else {
        $upd = mysqli_prepare($conn, "UPDATE todos SET hari = ?, kegiatan = ?, waktu = ? WHERE id = ? AND user_id = ?");
        mysqli_stmt_bind_param($upd, "sssii", $hari_baru, $kegiatan, $waktu, $id, $user_id);
        mysqli_stmt_execute($upd);
        header("Location: edit.php?hari=" . urlencode($hari_baru) . "&status=edit");
        exit();
    }
}

// ---- Ambil data kegiatan hari ini ----
$stmt = mysqli_prepare($conn, "SELECT id, hari, kegiatan, waktu FROM todos WHERE user_id = ? AND hari = ? ORDER BY waktu");
mysqli_stmt_bind_param($stmt, "is", $user_id, $hari);
mysqli_stmt_execute($stmt);
$todos = mysqli_fetch_all(mysqli_stmt_get_result($stmt), MYSQLI_ASSOC);

$pesan = [
    'tambah' => 'Kegiatan berhasil ditambahkan.',
    'edit'   => 'Kegiatan berhasil diubah.',
    'hapus'  => 'Kegiatan berhasil dihapus.',
];
$status = $_GET['status'] ?? '';

// JavaScript: isi modal Edit dengan data baris yang diklik
$script_tambahan = <<<'JS'
<script>
document.querySelectorAll('.btn-edit').forEach(function (btn) {
    btn.addEventListener('click', function () {
        document.getElementById('edit-id').value       = this.dataset.id;
        document.getElementById('edit-hari').value     = this.dataset.hari;
        document.getElementById('edit-kegiatan').value = this.dataset.kegiatan;
        document.getElementById('edit-waktu').value    = this.dataset.waktu;
    });
});
</script>
JS;

$judul_halaman = "To Do List " . $hari;
$hari_aktif = $hari;
include '_atas.php';
?>
                    <h1 class="mt-4">To Do List <?= htmlspecialchars($hari); ?></h1>
                    <ol class="breadcrumb mb-4">
                        <li class="breadcrumb-item"><a href="index.php">To Do List</a></li>
                        <li class="breadcrumb-item active"><?= htmlspecialchars($hari); ?></li>
                    </ol>

                    <?php if (isset($pesan[$status])): ?>
                        <div class="alert alert-success"><?= $pesan[$status]; ?></div>
                    <?php endif; ?>
                    <?php if ($error != ""): ?>
                        <div class="alert alert-danger"><?= htmlspecialchars($error); ?></div>
                    <?php endif; ?>

                    <div class="card mb-4">
                        <div class="card-header d-flex justify-content-between align-items-center">
                            <div><i class="fas fa-table me-1"></i> Kegiatan hari <?= htmlspecialchars($hari); ?></div>
                            <a href="tambah.php?hari=<?= urlencode($hari); ?>" class="btn btn-primary btn-sm"><i class="fas fa-plus me-1"></i> Tambah</a>
                        </div>
                        <div class="card-body">
                            <?php if (count($todos) === 0): ?>
                                <p class="text-muted mb-0">Belum ada kegiatan di hari ini. Klik <b>Tambah</b> untuk menambahkan.</p>
                            <?php else: ?>
                            <table id="datatablesSimple">
                                <thead>
                                    <tr>
                                        <th>No</th>
                                        <th>Kegiatan</th>
                                        <th>Waktu</th>
                                        <th>Aksi</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($todos as $i => $t): ?>
                                    <tr>
                                        <td><?= $i + 1; ?></td>
                                        <td><?= htmlspecialchars($t['kegiatan']); ?></td>
                                        <td><?= htmlspecialchars($t['waktu']); ?></td>
                                        <td>
                                            <button type="button" class="btn btn-warning btn-sm btn-edit"
                                                data-bs-toggle="modal" data-bs-target="#modalEdit"
                                                data-id="<?= $t['id']; ?>"
                                                data-hari="<?= htmlspecialchars($t['hari']); ?>"
                                                data-kegiatan="<?= htmlspecialchars($t['kegiatan']); ?>"
                                                data-waktu="<?= htmlspecialchars($t['waktu']); ?>">Edit</button>
                                            <a href="hapus.php?id=<?= $t['id']; ?>" class="btn btn-danger btn-sm" onclick="return confirm('Yakin hapus kegiatan ini?')">Hapus</a>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                            <?php endif; ?>
                        </div>
                    </div>

                    <!-- Modal Edit -->
                    <div class="modal fade" id="modalEdit" tabindex="-1" aria-labelledby="modalEditLabel" aria-hidden="true">
                        <div class="modal-dialog">
                            <form method="POST" action="edit.php?hari=<?= urlencode($hari); ?>" class="modal-content">
                                <div class="modal-header">
                                    <h5 class="modal-title" id="modalEditLabel">Edit Kegiatan</h5>
                                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                </div>
                                <div class="modal-body">
                                    <input type="hidden" name="id" id="edit-id">
                                    <div class="mb-3">
                                        <label for="edit-hari" class="form-label">Hari</label>
                                        <select class="form-select" id="edit-hari" name="hari" required>
                                            <?php foreach ($daftar_hari as $h): ?>
                                                <option value="<?= $h; ?>"><?= $h; ?></option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>
                                    <div class="mb-3">
                                        <label for="edit-kegiatan" class="form-label">Nama Kegiatan</label>
                                        <input type="text" class="form-control" id="edit-kegiatan" name="kegiatan" maxlength="150" required>
                                    </div>
                                    <div class="mb-3">
                                        <label for="edit-waktu" class="form-label">Waktu</label>
                                        <input type="text" class="form-control" id="edit-waktu" name="waktu" placeholder="08.00 - 10.00" maxlength="30" required>
                                    </div>
                                </div>
                                <div class="modal-footer">
                                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                                    <button type="submit" name="update" class="btn btn-primary">Update</button>
                                </div>
                            </form>
                        </div>
                    </div>
<?php include '_bawah.php'; ?>