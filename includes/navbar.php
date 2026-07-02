<?php
/**
 * FILE: includes/navbar.php
 * DESKRIPSI: Top Navbar responsif dengan tombol toggle sidebar,
 *            jam digital, tombol dark mode, dan dropdown profil.
 * Membutuhkan: $base_path, $page_title sudah di-set.
 */
if (!isset($base_path)) $base_path = '';
?>

<!-- ======================================================
     TOP NAVBAR
====================================================== -->
<header id="top-navbar">

    <!-- Kiri: Toggle Sidebar & Brand Mobile -->
    <div class="d-flex align-items-center gap-3">
        <button class="btn-icon" id="sidebar-toggle" title="Toggle Sidebar" aria-label="Toggle Sidebar">
            <i class="fas fa-bars"></i>
        </button>

        <!-- Brand hanya muncul di mobile -->
        <span class="navbar-brand-mobile">
            <i class="fas fa-cubes me-1 text-primary"></i>Pendataan
        </span>

        <!-- Jam digital (disembunyikan di layar sangat kecil) -->
        <div class="nav-clock d-none d-md-flex align-items-center gap-2">
            <i class="fas fa-clock text-primary me-1"></i>
            <span id="digital-clock">00:00:00</span>
        </div>
    </div>

    <!-- Tengah: Tanggal (hidden on mobile) -->
    <div class="d-none d-lg-block text-center">
        <small id="date-today" class="text-muted" style="font-size:12px;"></small>
    </div>

    <!-- Kanan: Actions -->
    <div class="navbar-right">

        <!-- Dark Mode Toggle -->
        <button class="btn-icon" id="dark-mode-btn" title="Toggle Mode Gelap">
            <i class="fas fa-moon" id="dark-mode-icon"></i>
        </button>

        <!-- Notifikasi (placeholder) -->
        <button class="btn-icon position-relative" title="Notifikasi">
            <i class="fas fa-bell"></i>
            <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger" style="font-size:9px;padding:2px 5px;">3</span>
        </button>

        <!-- Divider -->
        <div class="vr mx-1" style="height:20px;"></div>

        <!-- User Dropdown -->
        <div class="dropdown">
            <button class="btn-icon d-flex align-items-center gap-2 pe-2"
                    data-bs-toggle="dropdown" aria-expanded="false"
                    style="width:auto;border-radius:8px;padding:4px 8px;">
                <div style="width:30px;height:30px;border-radius:50%;background:var(--primary);color:#fff;font-weight:700;font-size:12px;display:flex;align-items:center;justify-content:center;">
                    <?= e(strtoupper(mb_substr($_SESSION['nama'] ?? 'U', 0, 1))); ?>
                </div>
                <div class="d-none d-sm-block text-start">
                    <div style="font-size:12.5px;font-weight:600;color:var(--text-primary);line-height:1.2;"><?= e($_SESSION['nama'] ?? ''); ?></div>
                    <div style="font-size:10.5px;color:var(--text-muted);"><?= e($_SESSION['role'] ?? ''); ?></div>
                </div>
                <i class="fas fa-chevron-down" style="font-size:10px;color:var(--text-muted);"></i>
            </button>
            <ul class="dropdown-menu dropdown-menu-end shadow border-0 mt-1" style="min-width:180px;border-radius:10px;">
                <li class="px-3 py-2 border-bottom">
                    <div style="font-weight:600;font-size:13px;"><?= e($_SESSION['nama'] ?? ''); ?></div>
                    <small class="text-muted"><?= e($_SESSION['role'] ?? ''); ?></small>
                </li>
                <li>
                    <a class="dropdown-item py-2" href="<?= $base_path; ?>profil/index.php">
                        <i class="fas fa-user-cog me-2 text-muted" style="width:16px;"></i>Profil Saya
                    </a>
                </li>
                <li><hr class="dropdown-divider my-1"></li>
                <li>
                    <a class="dropdown-item py-2 text-danger" href="<?= $base_path; ?>auth/logout.php"
                       onclick="return confirm('Yakin ingin keluar?')">
                        <i class="fas fa-sign-out-alt me-2" style="width:16px;"></i>Keluar
                    </a>
                </li>
            </ul>
        </div>

    </div>
</header>
<!-- END TOP NAVBAR -->
