<?php
/**
 * FILE: includes/sidebar.php
 * DESKRIPSI: Sidebar navigasi utama.
 * Menampilkan menu berdasarkan $current_page dan $_SESSION['role'].
 * Variabel:
 *   $current_page (string) — nama halaman aktif (misal: 'dashboard', 'pelanggan')
 *   $base_path    (string) — path ke root
 */

if (!isset($current_page)) $current_page = '';
if (!isset($base_path))    $base_path    = '';

// Helper: apakah menu aktif?
$active = fn(string $page) => $current_page === $page ? 'active' : '';

// Inisial nama user untuk avatar
$nama_user = $_SESSION['nama'] ?? 'User';
$inisial   = strtoupper(mb_substr($nama_user, 0, 1));
$role_user = $_SESSION['role'] ?? 'Petugas';
?>

<!-- ======================================================
     SIDEBAR NAVIGASI
====================================================== -->
<aside id="sidebar">

    <!-- Brand / Logo -->
    <div class="sidebar-brand">
        <div class="brand-icon">
            <i class="fas fa-cubes"></i>
        </div>
        <div class="brand-text">
            Pendataan
            <span>Pelanggan &amp; Transaksi</span>
        </div>
    </div>

    <!-- Navigasi Menu -->
    <nav class="sidebar-nav">
        <!-- UTAMA -->
        <div class="sidebar-section">Utama</div>

        <a href="<?= $base_path; ?>dashboard.php"
           class="nav-link <?= $active('dashboard'); ?>">
            <span class="nav-icon"><i class="fas fa-tachometer-alt"></i></span>
            <span class="nav-label">Dashboard</span>
        </a>

        <!-- DATA -->
        <div class="sidebar-section">Data Master</div>

        <a href="<?= $base_path; ?>pelanggan/index.php"
           class="nav-link <?= $active('pelanggan'); ?>">
            <span class="nav-icon"><i class="fas fa-users"></i></span>
            <span class="nav-label">Data Pelanggan</span>
        </a>

        <a href="<?= $base_path; ?>transaksi/index.php"
           class="nav-link <?= $active('transaksi'); ?>">
            <span class="nav-icon"><i class="fas fa-exchange-alt"></i></span>
            <span class="nav-label">Data Transaksi</span>
        </a>

        <!-- LAPORAN -->
        <div class="sidebar-section">Laporan</div>

        <a href="<?= $base_path; ?>laporan/pelanggan.php"
           class="nav-link <?= $active('laporan-pelanggan'); ?>">
            <span class="nav-icon"><i class="fas fa-file-user"></i></span>
            <span class="nav-label">Laporan Pelanggan</span>
        </a>

        <a href="<?= $base_path; ?>laporan/transaksi.php"
           class="nav-link <?= $active('laporan-transaksi'); ?>">
            <span class="nav-icon"><i class="fas fa-file-invoice-dollar"></i></span>
            <span class="nav-label">Laporan Transaksi</span>
        </a>

        <!-- AKUN -->
        <div class="sidebar-section">Akun</div>

        <a href="<?= $base_path; ?>profil/index.php"
           class="nav-link <?= $active('profil'); ?>">
            <span class="nav-icon"><i class="fas fa-user-circle"></i></span>
            <span class="nav-label">Profil Saya</span>
        </a>

        <?php if ($role_user === 'Admin'): ?>
        <a href="<?= $base_path; ?>auth/register.php"
           class="nav-link <?= $active('register'); ?>">
            <span class="nav-icon"><i class="fas fa-user-plus"></i></span>
            <span class="nav-label">Tambah Pengguna</span>
        </a>
        <?php endif; ?>

        <a href="<?= $base_path; ?>about.php"
           class="nav-link <?= $active('about'); ?>">
            <span class="nav-icon"><i class="fas fa-info-circle"></i></span>
            <span class="nav-label">Tentang Aplikasi</span>
        </a>

        <a href="<?= $base_path; ?>auth/logout.php"
           class="nav-link text-danger-light"
           style="color: #FCA5A5;"
           onclick="return confirm('Yakin ingin keluar?')">
            <span class="nav-icon"><i class="fas fa-sign-out-alt"></i></span>
            <span class="nav-label">Keluar</span>
        </a>
    </nav>

    <!-- Footer Sidebar: Info User -->
    <div class="sidebar-footer">
        <div class="user-info">
            <div class="user-avatar"><?= e($inisial); ?></div>
            <div>
                <div class="user-name"><?= e($nama_user); ?></div>
                <div class="user-role"><?= e($role_user); ?></div>
            </div>
        </div>
    </div>

</aside>
<!-- END SIDEBAR -->
