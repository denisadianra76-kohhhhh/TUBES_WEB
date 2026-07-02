<?php
/**
 * FILE: about.php
 * DESKRIPSI: Halaman informasi aplikasi.
 */

$base_path    = '';
$page_title   = 'Tentang Aplikasi';
$current_page = 'about';

require_once 'config/koneksi.php';
proteksi_halaman();
require_once 'includes/header.php';
?>

<div class="d-flex" id="app-layout">
<?php require_once 'includes/sidebar.php'; ?>

    <div id="main-wrapper">
        <?php require_once 'includes/navbar.php'; ?>
        <main>
        <div class="content-wrapper">

            <div class="page-header">
                <h4 class="mb-1"><i class="fas fa-info-circle text-primary me-2"></i>Tentang Aplikasi</h4>
                <p class="mb-0">Aplikasi Pendataan Pelanggan dan Transaksi adalah sistem sederhana untuk mengelola data pelanggan, transaksi, dan laporan.</p>
            </div>

            <div class="row gy-3">
                <div class="col-12 col-xl-8">
                    <div class="card">
                        <div class="card-header">
                            <h6 class="mb-0 fw-bold">Info Aplikasi</h6>
                        </div>
                        <div class="card-body">
                            <p>Aplikasi ini dibuat untuk kebutuhan tugas besar mata kuliah Pemrograman Web. Fitur utama meliputi:</p>
                            <ul>
                                <li>Manajemen pengguna (Admin & Petugas)</li>
                                <li>CRUD data pelanggan</li>
                                <li>CRUD transaksi keuangan</li>
                                <li>Filter, sort, dan pagination</li>
                                <li>Laporan cetak pelanggan dan transaksi</li>
                                <li>Autentikasi, role-based access control, dan CSRF protection</li>
                            </ul>
                            <p>Database default: <strong>db_pendataan_pelanggan</strong>.</p>
                            <p>Login awal: <strong>admin / admin123</strong>.</p>
                        </div>
                    </div>
                </div>
                <div class="col-12 col-xl-4">
                    <div class="card bg-light border-dashed">
                        <div class="card-body">
                            <h6 class="fw-bold">Detail Teknis</h6>
                            <dl class="row mt-3" style="font-size:14px;">
                                <dt class="col-5 text-muted">Framework</dt><dd class="col-7">PHP + MySQLi</dd>
                                <dt class="col-5 text-muted">CSS</dt><dd class="col-7">Bootstrap 5</dd>
                                <dt class="col-5 text-muted">JS</dt><dd class="col-7">Chart.js, SweetAlert2</dd>
                                <dt class="col-5 text-muted">Penulis</dt><dd class="col-7">Tim Pengembang</dd>
                            </dl>
                        </div>
                    </div>
                </div>
            </div>

        </div>
        </main>
    </div>
</div>

<?php require_once 'includes/footer.php';
