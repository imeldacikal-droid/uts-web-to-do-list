<?php
require 'helper.php';
require_login();
$menu_aktif = 'dashboard';

$id = (int)($_GET['id'] ?? 0);
$data = ['tanggal' => '', 'jam_mulai' => '', 'jam_selesai' => '', 'kegiatan' => '', 'lokasi' => '', 'kategori' => 'Lainnya', 'hari' => ''];

if ($id) {
    $stmt = mysqli_prepare($conn, "SELECT * FROM todos WHERE id = ? AND user_id = ?");
    $u = uid();
    mysqli_stmt_bind_param($stmt, 'ii', $id, $u);
    mysqli_stmt_execute($stmt);
    $row = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));
    if (!$row) { header('Location: dashboard.php'); exit; }
    $data = $row;
} else {
    // Default tanggal = tanggal yang dibuka dari halaman hari; kalau tidak ada, hari ini (kalau masih di tahun kalender)
    $tg = $_GET['tanggal'] ?? '';
    if (!tanggal_valid($tg)) { $tg = (int)date('Y') === $TAHUN ? date('Y-m-d') : $TAHUN . '-01-01'; }
    $data['tanggal'] = $tg;
    $data['hari'] = $NAMA_HARI[(int)date('N', strtotime($tg))];
}
$judul_halaman = $id ? 'Edit Kegiatan' : 'Tambah Kegiatan';
$kembali = tanggal_valid($data['tanggal']) ? 'detail.php?tanggal=' . $data['tanggal'] : 'dashboard.php';

$script_tambahan = <<<'JS'
<script>
// Kolom "Hari" terisi otomatis dari tanggal
var namaHari = ['Minggu','Senin','Selasa','Rabu','Kamis','Jumat','Sabtu'];
var tgl = document.getElementById('tanggal'), hari = document.getElementById('hari');
function updateHari() {
    if (!tgl.value) { hari.value = ''; return; }
    var p = tgl.value.split('-');
    hari.value = namaHari[new Date(p[0], p[1] - 1, p[2]).getDay()];
}
tgl.addEventListener('change', updateHari);
updateHari();
</script>
<script>
// Kotak ketik kategori hanya tampil saat "Lainnya" dipilih
var selKat = document.getElementById('kategori'), kotakLain = document.getElementById('kategori_lain');
function aturKotakLain() {
    var tampil = selKat.value === 'Lainnya';
    kotakLain.style.display = tampil ? '' : 'none';
    if (!tampil) { kotakLain.value = ''; }
}
selKat.addEventListener('change', aturKotakLain);
aturKotakLain();
</script>
JS;
// Kategori buatan sendiri (di luar daftar) ditampilkan sebagai "Lainnya" + isi kotak ketik
$kat = $data['kategori'] ?? 'Lainnya';
$kat_lain = '';
if (!isset($KATEGORI[$kat])) { $kat_lain = $kat; $kat = 'Lainnya'; }
include 'partials/header.php';
?>
<div class="container-fluid px-4">
    <h1 class="mt-4"><?= $id ? 'Edit' : 'Tambah' ?> Kegiatan</h1>
    <ol class="breadcrumb mb-4">
        <li class="breadcrumb-item"><a href="dashboard.php">To Do List</a></li>
        <li class="breadcrumb-item active"><?= $id ? 'Edit' : 'Tambah' ?></li>
    </ol>

    <?php if (!empty($_GET['err'])): ?>
        <div class="alert alert-danger"><?= e($_GET['err']) ?></div>
    <?php endif; ?>

    <div class="card mb-4" style="max-width: 700px;">
        <div class="card-body">
            <form action="simpan.php" method="post">
                <input type="hidden" name="id" value="<?= $id ?>">
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label" for="tanggal">Tanggal</label>
                        <input type="date" class="form-control" id="tanggal" name="tanggal" value="<?= e($data['tanggal']) ?>" min="<?= $TAHUN ?>-01-01" max="<?= $TAHUN ?>-12-31" required>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label" for="hari">Hari (otomatis)</label>
                        <input type="text" class="form-control" id="hari" value="<?= e($data['hari']) ?>" readonly>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label" for="jam_mulai">Jam Mulai</label>
                        <input type="time" class="form-control" id="jam_mulai" name="jam_mulai" value="<?= e(substr((string)$data['jam_mulai'], 0, 5)) ?>" required>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label" for="jam_selesai">Jam Selesai <span class="text-muted small">(kosongkan = "selesai")</span></label>
                        <input type="time" class="form-control" id="jam_selesai" name="jam_selesai" value="<?= e(substr((string)$data['jam_selesai'], 0, 5)) ?>">
                    </div>
                </div>
                <div class="mb-3">
                    <label class="form-label" for="kegiatan">Kegiatan</label>
                    <input type="text" class="form-control" id="kegiatan" name="kegiatan" value="<?= e($data['kegiatan']) ?>" maxlength="255" required>
                </div>
                <div class="mb-3">
                    <label class="form-label" for="lokasi">Tempat</label>
                    <input type="text" class="form-control" id="lokasi" name="lokasi" value="<?= e($data['lokasi']) ?>" maxlength="255" required>
                </div>
                <div class="mb-3">
                    <label class="form-label" for="kategori">Kategori</label>
                    <select class="form-select" id="kategori" name="kategori">
                        <?php foreach ($KATEGORI as $k => $kelas): ?>
                            <option value="<?= e($k) ?>" <?= $kat === $k ? 'selected' : '' ?>><?= e($k) ?></option>
                        <?php endforeach; ?>
                    </select>
                    <input type="text" class="form-control mt-2" id="kategori_lain" name="kategori_lain" maxlength="20"
                        placeholder="Ketik kategori sendiri (opsional, maks. 20 karakter)" value="<?= e($kat_lain) ?>">
                </div>
                <button type="submit" class="btn btn-primary"><i class="fas fa-save"></i> Simpan</button>
                <a href="<?= e($kembali) ?>" class="btn btn-secondary">Batal</a>
            </form>
        </div>
    </div>
</div>
<?php include 'partials/footer.php'; ?>
