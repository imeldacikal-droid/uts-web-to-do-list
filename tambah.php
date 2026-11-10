<?php
session_start();
include 'koneksi.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: 401.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$daftar_hari = ['Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu', 'Minggu'];
$error = "";

// Hari otomatis terisi kalau datang dari halaman detail
$hari_get = $_GET['hari'] ?? 'Senin';
$data = [
    'hari'     => in_array($hari_get, $daftar_hari) ? $hari_get : 'Senin',
    'kegiatan' => '',
    'waktu'    => '',
];

if (isset($_POST['simpan'])) {
    $data['hari']     = $_POST['hari'];
    $data['kegiatan'] = trim($_POST['kegiatan']);
    $data['waktu']    = trim($_POST['waktu']);

    if (!in_array($data['hari'], $daftar_hari)) {
        $error = "Hari tidak valid!";
    } elseif ($data['kegiatan'] === "" || $data['waktu'] === "") {
        $error = "Kegiatan dan waktu wajib diisi!";
    } else {
        $stmt = mysqli_prepare($conn, "INSERT INTO todos (user_id, hari, kegiatan, waktu) VALUES (?, ?, ?, ?)");
        mysqli_stmt_bind_param($stmt, "isss", $user_id, $data['hari'], $data['kegiatan'], $data['waktu']);

        if (mysqli_stmt_execute($stmt)) {
            header("Location: edit.php?hari=" . urlencode($data['hari']) . "&status=tambah");
            exit();
        }
        $error = "Gagal menyimpan data.";
    }
}

$judul_halaman = "Tambah Kegiatan";
$hari_aktif = $data['hari'];
include '_atas.php';
?>
                    <h1 class="mt-4">Tambah Kegiatan</h1>
                    <ol class="breadcrumb mb-4">
                        <li class="breadcrumb-item"><a href="index.php">To Do List</a></li>
                        <li class="breadcrumb-item"><a href="edit.php?hari=<?= urlencode($data['hari']); ?>"><?= htmlspecialchars($data['hari']); ?></a></li>
                        <li class="breadcrumb-item active">Tambah</li>
                    </ol>

                    <div class="card mb-4">
                        <div class="card-header"><i class="fas fa-plus me-1"></i> Form Kegiatan Baru</div>
                        <div class="card-body">
                            <?php if ($error != ""): ?>
                                <div class="alert alert-danger"><?= htmlspecialchars($error); ?></div>
                            <?php endif; ?>
                            <form method="POST" action="">
                                <div class="mb-3">
                                    <label for="hari" class="form-label">Hari</label>
                                    <select class="form-select" id="hari" name="hari" required>
                                        <?php foreach ($daftar_hari as $h): ?>
                                            <option value="<?= $h; ?>" <?= $data['hari'] === $h ? 'selected' : ''; ?>><?= $h; ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                <div class="mb-3">
                                    <label for="kegiatan" class="form-label">Nama Kegiatan</label>
                                    <input type="text" class="form-control" id="kegiatan" name="kegiatan" maxlength="150" value="<?= htmlspecialchars($data['kegiatan']); ?>" required>
                                </div>
                                <div class="mb-3">
                                    <label for="waktu" class="form-label">Waktu</label>
                                    <input type="text" class="form-control" id="waktu" name="waktu" placeholder="08.00 - 10.00" maxlength="30" value="<?= htmlspecialchars($data['waktu']); ?>" required>
                                </div>
                                <button type="submit" name="simpan" class="btn btn-primary">Simpan</button>
                                <a href="edit.php?hari=<?= urlencode($data['hari']); ?>" class="btn btn-secondary">Batal</a>
                            </form>
                        </div>
                    </div>
<?php include '_bawah.php'; ?>