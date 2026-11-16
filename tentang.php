<?php
require 'helper.php';
require_login();
$judul_halaman = 'Tentang Aplikasi';
$menu_aktif = 'tentang';
include 'partials/header.php';
?>
<div class="container-fluid px-4">
    <h1 class="mt-4">Tentang Aplikasi</h1>
    <ol class="breadcrumb mb-4">
        <li class="breadcrumb-item"><a href="dashboard.php">To Do List</a></li>
        <li class="breadcrumb-item active">Tentang Aplikasi</li>
    </ol>

    <div class="card mb-4">
        <div class="card-header"><i class="fas fa-circle-info me-1"></i> Apa itu To-Do List?</div>
        <div class="card-body">
            <p>To-Do List adalah aplikasi web untuk mencatat dan mengatur kegiatan mingguan.Kegiatan dikelompokkan per bulan, minggu, dan hari, sehingga jadwal lebih teratur dan tidak mudah terlewat. Setiap pengguna memiliki akun sendiri, jadi catatan kegiatan bersifat pribadi, dan kegiatan dapat dibagikan ke pengguna lain lewat fitur kolaborasi.</p>
            <p class="mb-0">Aplikasi ini dibuat untuk memenuhi tugas Ujian Tengah Semester (UTS) mata kuliah Pemrograman Web, Program Studi Teknik Informatika, Universitas Paramadina.</p>
        </div>
    </div>

    <div class="row">
        <div class="col-lg-6">
            <div class="card mb-4">
                <div class="card-header"><i class="fas fa-star me-1"></i> Fitur Utama</div>
                <div class="card-body">
                    <ul class="mb-0">
                        <li>Daftar akun, login, dan logout memakai session PHP</li>
                        <li>Kegiatan per bulan, minggu, dan hari dengan progres selesai</li>
                        <li>Tambah, lihat, ubah, dan hapus kegiatan (CRUD)</li>
                        <li>Centang kegiatan selesai tanpa memuat ulang halaman</li>
                        <li>Kategori kegiatan (Kuliah, Organisasi, Pribadi, Lainnya)</li>
                        <li>Filter kegiatan: semua, belum selesai, atau selesai</li>
                        <li>Kolaborasi: cari pengguna lain lalu kirim undangan lewat tombol Send</li>
                        <li>Profil pengguna yang dapat diubah</li>
                    </ul>
                </div>
            </div>
        </div>
        <div class="col-lg-6">
            <div class="card mb-4">
                <div class="card-header"><i class="fas fa-circle-question me-1"></i> Cara Menggunakan</div>
                <div class="card-body">
                    <ol class="mb-0">
                        <li>Daftar akun, lalu login.</li>
                        <li>Pilih bulan, minggu, lalu hari, kemudian klik Tambah Kegiatan.</li>
                        <li>Centang kegiatan yang sudah selesai.</li>
                        <li>Klik Send untuk mengajak pengguna lain berkolaborasi.</li>
                        <li>Buka menu Undangan untuk menerima atau menolak ajakan.</li>
                    </ol>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-lg-6">
            <div class="card mb-4">
                <div class="card-header"><i class="fas fa-code me-1"></i> Teknologi</div>
                <div class="card-body">
                    HTML, CSS, JavaScript, Bootstrap 5 (template SB Admin), PHP native, dan MySQL.
                </div>
            </div>
        </div>
        <div class="col-lg-6">
            <div class="card mb-4">
                <div class="card-header"><i class="fas fa-users me-1"></i> Tim Pembuat</div>
                <div class="card-body">
                    <ul class="mb-0">
                        <li>Adzkia Zafira Sahlah (125103049)</li>
                        <li>Imelda Cikal Pratiwi (125103023)</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>
<?php include 'partials/footer.php'; ?>
