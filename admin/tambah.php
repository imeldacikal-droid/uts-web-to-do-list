<?php 
include '../includes/header.php';
include '../includes/sidebar.php';

if (isset($_POST['simpan'])) {
    $user_id   = $_SESSION['user_id'];
    $kegiatan  = mysqli_real_escape_string($conn, $_POST['kegiatan']);
    $hari      = $_POST['hari'];
    $waktu     = $_POST['waktu'];
    $deskripsi = mysqli_real_escape_string($conn, $_POST['deskripsi']);

    $query = mysqli_query($conn, "INSERT INTO tasks (user_id, kegiatan, hari, waktu, deskripsi, status) VALUES ('$user_id', '$kegiatan', '$hari', '$waktu', '$deskripsi', 'Pending')");

    if ($query) {
        echo "<script>alert('Tugas berhasil ditambahkan!'); window.location='index.php';</script>";
    }
}
?>

<div id="layoutSidenav_content">
    <main>
        <div class="container-fluid px-4">
            <h1 class="mt-4">Tambah Kegiatan</h1>
            <div class="card mb-4 shadow">
                <div class="card-header bg-primary text-white"><i class="fas fa-plus me-1"></i> Form Tambah Tugas</div>
                <div class="card-body">
                    <form method="POST" action="">
                        <div class="mb-3">
                            <label class="form-label">Nama Kegiatan</label>
                            <input type="text" name="kegiatan" class="form-control" required>
                        </div>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Hari</label>
                                <select name="hari" class="form-select" required>
                                    <option value="Senin">Senin</option>
                                    <option value="Selasa">Selasa</option>
                                    <option value="Rabu">Rabu</option>
                                    <option value="Kamis">Kamis</option>
                                    <option value="Jumat">Jumat</option>
                                    <option value="Sabtu">Sabtu</option>
                                    <option value="Minggu">Minggu</option>
                                </select>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Waktu</label>
                                <input type="time" name="waktu" class="form-control" required>
                            </div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Deskripsi</label>
                            <textarea name="deskripsi" class="form-control" rows="3"></textarea>
                        </div>
                        <a href="index.php" class="btn btn-secondary">Batal</a>
                        <button type="submit" name="simpan" class="btn btn-primary">Simpan Tugas</button>
                    </form>
                </div>
            </div>
        </div>
    </main>
<?php include '../includes/footer.php'; ?>