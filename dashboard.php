<?php
/**
 * FILE: dashboard.php
 * DESKRIPSI: Halaman utama Dashboard.
 * Menampilkan:
 *   - Kartu statistik (pelanggan, transaksi, admin, petugas)
 *   - Grafik Chart.js (Arus Keuangan + Distribusi Gender)
 *   - Tabel Transaksi Terbaru (Recent Activity)
 *   - Jam Digital
 *   - Mini Kalender Bulan Ini
 */

$base_path    = '';
$page_title   = 'Dashboard';
$current_page = 'dashboard';

require_once 'config/koneksi.php';
proteksi_halaman();
require_once 'includes/header.php';
require_once 'includes/sidebar.php';

// ===========================================================
// QUERY STATISTIK DASHBOARD
// ===========================================================
$total_pelanggan   = count_table('pelanggan');
$total_transaksi   = count_table('transaksi');

$r_admin   = mysqli_query($koneksi, "SELECT COUNT(*) c FROM user WHERE role='Admin'");
$r_petugas = mysqli_query($koneksi, "SELECT COUNT(*) c FROM user WHERE role='Petugas'");
$total_admin   = (int)(mysqli_fetch_assoc($r_admin)['c']   ?? 0);
$total_petugas = (int)(mysqli_fetch_assoc($r_petugas)['c'] ?? 0);

// Pemasukan & Pengeluaran Total
$r_kas = mysqli_query($koneksi,
    "SELECT jenis_transaksi, SUM(jumlah) AS total FROM transaksi GROUP BY jenis_transaksi");
$kas_data = ['Pemasukan' => 0, 'Pengeluaran' => 0];
while ($row = mysqli_fetch_assoc($r_kas)) $kas_data[$row['jenis_transaksi']] = (float)$row['total'];

// Distribusi Gender Pelanggan
$r_gender = mysqli_query($koneksi, "SELECT jenis_kelamin, COUNT(*) c FROM pelanggan GROUP BY jenis_kelamin");
$gender_data = ['L' => 0, 'P' => 0];
while ($row = mysqli_fetch_assoc($r_gender)) $gender_data[$row['jenis_kelamin']] = (int)$row['c'];

// Data Grafik Bulanan Transaksi (6 bulan terakhir)
$r_bulan = mysqli_query($koneksi,
    "SELECT DATE_FORMAT(tanggal,'%b %Y') AS bulan,
            SUM(CASE WHEN jenis_transaksi='Pemasukan'  THEN jumlah ELSE 0 END) AS masuk,
            SUM(CASE WHEN jenis_transaksi='Pengeluaran' THEN jumlah ELSE 0 END) AS keluar
     FROM transaksi
     WHERE tanggal >= DATE_SUB(CURDATE(), INTERVAL 6 MONTH)
     GROUP BY DATE_FORMAT(tanggal,'%Y-%m')
     ORDER BY MIN(tanggal) ASC");
$chart_labels = $chart_masuk = $chart_keluar = [];
while ($r = mysqli_fetch_assoc($r_bulan)) {
    $chart_labels[] = $r['bulan'];
    $chart_masuk[]  = (float)$r['masuk'];
    $chart_keluar[] = (float)$r['keluar'];
}

// 5 Transaksi Terbaru
$r_recent = mysqli_query($koneksi,
    "SELECT t.*, p.nama AS nama_pelanggan
     FROM transaksi t
     JOIN pelanggan p ON t.id_pelanggan = p.id_pelanggan
     ORDER BY t.id_transaksi DESC LIMIT 5");
?>

<!-- ======================================================
     LAYOUT UTAMA: Sidebar + Main
====================================================== -->
<div class="d-flex" id="app-layout">
<?php require_once 'includes/sidebar.php'; /* sudah di-include tapi sidebar butuh wrapper */ ?>

    <!-- Main Wrapper -->
    <div id="main-wrapper">
        <?php require_once 'includes/navbar.php'; ?>

        <main>
        <div class="content-wrapper">

            <!-- Page Header -->
            <div class="page-header d-flex flex-column flex-sm-row justify-content-between align-items-start gap-3">
                <div>
                    <h4 class="mb-1"><i class="fas fa-tachometer-alt text-primary me-2"></i>Dashboard</h4>
                    <p class="mb-0">Selamat datang, <strong><?= e($_SESSION['nama']); ?></strong>!
                       Ringkasan data sistem hari ini.</p>
                </div>
                <div class="text-end">
                    <div class="clock-display" id="digital-clock">00:00:00</div>
                    <small id="date-today" class="text-muted"></small>
                </div>
            </div>

            <!-- ==========================================
                 KARTU STATISTIK
            ========================================== -->
            <div class="row g-3 mb-4">
                <div class="col-6 col-xl-3">
                    <div class="stat-card stat-blue">
                        <div class="stat-label">Total Pelanggan</div>
                        <div class="stat-value"><?= number_format($total_pelanggan); ?></div>
                        <div class="stat-sub"><a href="pelanggan/index.php" style="color:rgba(255,255,255,.75);">Lihat semua →</a></div>
                        <div class="stat-icon"><i class="fas fa-users"></i></div>
                    </div>
                </div>
                <div class="col-6 col-xl-3">
                    <div class="stat-card stat-green">
                        <div class="stat-label">Total Transaksi</div>
                        <div class="stat-value"><?= number_format($total_transaksi); ?></div>
                        <div class="stat-sub"><a href="transaksi/index.php" style="color:rgba(255,255,255,.75);">Lihat semua →</a></div>
                        <div class="stat-icon"><i class="fas fa-exchange-alt"></i></div>
                    </div>
                </div>
                <div class="col-6 col-xl-3">
                    <div class="stat-card stat-orange">
                        <div class="stat-label">Admin</div>
                        <div class="stat-value"><?= $total_admin; ?></div>
                        <div class="stat-sub">Pengguna Admin aktif</div>
                        <div class="stat-icon"><i class="fas fa-user-shield"></i></div>
                    </div>
                </div>
                <div class="col-6 col-xl-3">
                    <div class="stat-card stat-purple">
                        <div class="stat-label">Petugas</div>
                        <div class="stat-value"><?= $total_petugas; ?></div>
                        <div class="stat-sub">Pengguna Petugas aktif</div>
                        <div class="stat-icon"><i class="fas fa-user-tie"></i></div>
                    </div>
                </div>
            </div>

            <!-- ==========================================
                 GRAFIK CHART.JS
            ========================================== -->
            <div class="row g-3 mb-4">
                <!-- Grafik Arus Keuangan Bulanan -->
                <div class="col-12 col-lg-8">
                    <div class="card h-100">
                        <div class="card-header">
                            <div>
                                <h6 class="mb-0 fw-bold">Arus Keuangan (6 Bulan Terakhir)</h6>
                                <small class="text-muted">Pemasukan vs Pengeluaran</small>
                            </div>
                        </div>
                        <div class="card-body">
                            <canvas id="chartKeuangan" style="max-height:260px;"></canvas>
                        </div>
                    </div>
                </div>

                <!-- Grafik Distribusi Gender -->
                <div class="col-12 col-lg-4">
                    <div class="card h-100">
                        <div class="card-header">
                            <div>
                                <h6 class="mb-0 fw-bold">Demografi Pelanggan</h6>
                                <small class="text-muted">Berdasarkan Jenis Kelamin</small>
                            </div>
                        </div>
                        <div class="card-body d-flex align-items-center justify-content-center">
                            <div style="max-width:220px;width:100%;">
                                <canvas id="chartGender"></canvas>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- ==========================================
                 RECENT ACTIVITY + RINGKASAN KAS
            ========================================== -->
            <div class="row g-3">
                <!-- Tabel Transaksi Terbaru -->
                <div class="col-12 col-lg-8">
                    <div class="card">
                        <div class="card-header">
                            <h6 class="mb-0 fw-bold"><i class="fas fa-history me-2 text-primary"></i>Transaksi Terbaru</h6>
                            <a href="transaksi/index.php" class="btn btn-sm btn-outline-primary">Lihat Semua</a>
                        </div>
                        <div class="table-responsive">
                            <table class="table" id="main-table">
                                <thead>
                                    <tr>
                                        <th>Kode</th>
                                        <th>Pelanggan</th>
                                        <th>Tanggal</th>
                                        <th>Jenis</th>
                                        <th class="text-end">Jumlah</th>
                                        <th>Status</th>
                                    </tr>
                                </thead>
                                <tbody>
                                <?php if (mysqli_num_rows($r_recent) > 0):
                                    while ($trx = mysqli_fetch_assoc($r_recent)): ?>
                                    <tr>
                                        <td><span class="code-badge"><?= e($trx['kode_transaksi']); ?></span></td>
                                        <td class="fw-semibold"><?= e($trx['nama_pelanggan']); ?></td>
                                        <td><?= date('d/m/Y', strtotime($trx['tanggal'])); ?></td>
                                        <td>
                                            <?php if ($trx['jenis_transaksi'] === 'Pemasukan'): ?>
                                                <span class="badge badge-masuk"><i class="fas fa-arrow-down me-1"></i>Masuk</span>
                                            <?php else: ?>
                                                <span class="badge badge-keluar"><i class="fas fa-arrow-up me-1"></i>Keluar</span>
                                            <?php endif; ?>
                                        </td>
                                        <td class="text-end fw-bold"><?= rupiah($trx['jumlah']); ?></td>
                                        <td>
                                            <?php
                                            $st = $trx['status'] ?? 'Lunas';
                                            $cls = match($st) {
                                                'Lunas'      => 'badge-lunas',
                                                'Pending'    => 'badge-pending',
                                                'Dibatalkan' => 'badge-batal',
                                                default      => 'badge-lunas'
                                            };
                                            ?>
                                            <span class="badge <?= $cls; ?>"><?= e($st); ?></span>
                                        </td>
                                    </tr>
                                    <?php endwhile;
                                else: ?>
                                    <tr><td colspan="6" class="text-center py-4 text-muted">
                                        <i class="fas fa-folder-open fa-2x mb-2 d-block"></i>Belum ada data transaksi.
                                    </td></tr>
                                <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <!-- Ringkasan Kas & Aksi Cepat -->
                <div class="col-12 col-lg-4">
                    <!-- Ringkasan Kas -->
                    <div class="card mb-3">
                        <div class="card-header">
                            <h6 class="mb-0 fw-bold"><i class="fas fa-wallet me-2 text-success"></i>Ringkasan Kas</h6>
                        </div>
                        <div class="card-body p-0">
                            <div class="p-3 border-bottom d-flex justify-content-between align-items-center">
                                <div>
                                    <div style="font-size:11px;color:var(--text-muted);text-transform:uppercase;font-weight:600;">Pemasukan</div>
                                    <div class="fw-bold" style="color:#16A34A;font-size:15px;"><?= rupiah($kas_data['Pemasukan']); ?></div>
                                </div>
                                <i class="fas fa-arrow-trend-up" style="font-size:24px;color:#16A34A;opacity:.4;"></i>
                            </div>
                            <div class="p-3 border-bottom d-flex justify-content-between align-items-center">
                                <div>
                                    <div style="font-size:11px;color:var(--text-muted);text-transform:uppercase;font-weight:600;">Pengeluaran</div>
                                    <div class="fw-bold" style="color:#DC2626;font-size:15px;"><?= rupiah($kas_data['Pengeluaran']); ?></div>
                                </div>
                                <i class="fas fa-arrow-trend-down" style="font-size:24px;color:#DC2626;opacity:.4;"></i>
                            </div>
                            <div class="p-3 d-flex justify-content-between align-items-center">
                                <div>
                                    <div style="font-size:11px;color:var(--text-muted);text-transform:uppercase;font-weight:600;">Saldo Bersih</div>
                                    <?php $saldo = $kas_data['Pemasukan'] - $kas_data['Pengeluaran']; ?>
                                    <div class="fw-bold" style="color:<?= $saldo >= 0 ? '#2563EB' : '#DC2626'; ?>;font-size:15px;"><?= rupiah($saldo); ?></div>
                                </div>
                                <i class="fas fa-scale-balanced" style="font-size:24px;color:var(--text-muted);opacity:.4;"></i>
                            </div>
                        </div>
                    </div>

                    <!-- Aksi Cepat -->
                    <div class="card">
                        <div class="card-header">
                            <h6 class="mb-0 fw-bold"><i class="fas fa-bolt me-2 text-warning"></i>Aksi Cepat</h6>
                        </div>
                        <div class="card-body d-flex flex-column gap-2">
                            <a href="pelanggan/tambah.php" class="btn btn-primary">
                                <i class="fas fa-user-plus me-2"></i>Tambah Pelanggan
                            </a>
                            <a href="transaksi/tambah.php" class="btn btn-success">
                                <i class="fas fa-plus-circle me-2"></i>Tambah Transaksi
                            </a>
                            <a href="laporan/transaksi.php" class="btn btn-outline-secondary">
                                <i class="fas fa-file-invoice me-2"></i>Buka Laporan
                            </a>
                        </div>
                    </div>
                </div>
            </div>

        </div><!-- .content-wrapper -->
        </main>
    </div><!-- #main-wrapper -->
</div><!-- #app-layout -->

<?php
// Script halaman khusus untuk Chart.js
$page_scripts = '<script>
document.addEventListener("DOMContentLoaded", function() {
    const isDark = document.documentElement.getAttribute("data-theme") === "dark";
    const gridColor  = isDark ? "rgba(255,255,255,.07)" : "rgba(0,0,0,.06)";
    const textColor  = isDark ? "#94A3B8" : "#64748B";

    // Chart 1: Arus Keuangan Bulanan (Bar)
    const ctx1 = document.getElementById("chartKeuangan");
    if (ctx1) {
        new Chart(ctx1.getContext("2d"), {
            type: "bar",
            data: {
                labels: ' . json_encode($chart_labels ?: ['Jul 2026']) . ',
                datasets: [
                    {
                        label: "Pemasukan",
                        data: ' . json_encode($chart_masuk ?: [0]) . ',
                        backgroundColor: "rgba(22,163,74,.75)",
                        borderRadius: 6, borderSkipped: false
                    },
                    {
                        label: "Pengeluaran",
                        data: ' . json_encode($chart_keluar ?: [0]) . ',
                        backgroundColor: "rgba(220,38,38,.75)",
                        borderRadius: 6, borderSkipped: false
                    }
                ]
            },
            options: {
                animation: false,
                responsive: true, maintainAspectRatio: true,
                plugins: { legend: { labels: { color: textColor, font: { size: 12 } } } },
                scales: {
                    x: { grid: { color: gridColor }, ticks: { color: textColor } },
                    y: {
                        grid: { color: gridColor }, ticks: { color: textColor,
                        callback: v => "Rp " + Intl.NumberFormat("id-ID").format(v) }
                    }
                }
            }
        });
    }

    // Chart 2: Distribusi Gender (Doughnut)
    const ctx2 = document.getElementById("chartGender");
    if (ctx2) {
        new Chart(ctx2.getContext("2d"), {
            type: "doughnut",
            data: {
                labels: ["Laki-laki", "Perempuan"],
                datasets: [{ data: [' . $gender_data['L'] . ', ' . $gender_data['P'] . '],
                    backgroundColor: ["#2563EB","#F43F5E"], borderWidth: 3,
                    borderColor: isDark ? "#1E293B" : "#fff"
                }]
            },
            options: {
                animation: false,
                responsive: true, cutout: "65%",
                plugins: {
                    legend: { position: "bottom", labels: { color: textColor, boxWidth: 12, padding: 16, font: { size: 12 } } }
                }
            }
        });
    }
});
</script>';

require_once 'includes/footer.php';
?>
