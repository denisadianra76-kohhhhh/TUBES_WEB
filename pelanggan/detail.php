<?php
/**
 * FILE: pelanggan/detail.php
 * DESKRIPSI: Halaman detail lengkap pelanggan beserta riwayat transaksinya.
 */

$base_path    = '../';
$page_title   = 'Detail Pelanggan';
$current_page = 'pelanggan';

require_once '../config/koneksi.php';
proteksi_halaman();

$id = (int)($_GET['id'] ?? 0);
if (!$id) { set_flash('error','ID tidak valid.'); header('Location: index.php'); exit; }

$s = mysqli_prepare($koneksi, "SELECT * FROM pelanggan WHERE id_pelanggan = ? LIMIT 1");
mysqli_stmt_bind_param($s, 'i', $id);
mysqli_stmt_execute($s);
$plg = mysqli_fetch_assoc(mysqli_stmt_get_result($s));
mysqli_stmt_close($s);

if (!$plg) { set_flash('error','Data tidak ditemukan.'); header('Location: index.php'); exit; }

// Ambil transaksi pelanggan ini
$s2 = mysqli_prepare($koneksi,
    "SELECT * FROM transaksi WHERE id_pelanggan = ? ORDER BY tanggal DESC");
mysqli_stmt_bind_param($s2, 'i', $id);
mysqli_stmt_execute($s2);
$trx_result = mysqli_stmt_get_result($s2);

require_once '../includes/header.php';
?>

<div class="d-flex" id="app-layout">
<?php require_once '../includes/sidebar.php'; ?>

    <div id="main-wrapper">
        <?php require_once '../includes/navbar.php'; ?>
        <main>
        <div class="content-wrapper">

            <div class="page-header d-flex justify-content-between align-items-start">
                <div>
                    <nav class="breadcrumb-custom mb-2">
                        <a href="../dashboard.php">Dashboard</a><span class="sep">/</span>
                        <a href="index.php">Pelanggan</a><span class="sep">/</span>
                        <span class="current">Detail</span>
                    </nav>
                    <h4><i class="fas fa-user-tag text-info me-2"></i>Detail Pelanggan</h4>
                </div>
                <div class="d-flex gap-2">
                    <a href="edit.php?id=<?= $id; ?>" class="btn btn-warning text-white">
                        <i class="fas fa-edit me-1"></i>Edit
                    </a>
                    <a href="index.php" class="btn btn-outline-secondary">
                        <i class="fas fa-arrow-left me-1"></i>Kembali
                    </a>
                </div>
            </div>

            <div class="row g-3">
                <!-- Profil Card -->
                <div class="col-12 col-md-4">
                    <div class="card text-center p-4">
                        <div style="width:80px;height:80px;border-radius:50%;background:var(--primary);color:#fff;font-size:32px;font-weight:800;display:flex;align-items:center;justify-content:center;margin:0 auto 16px;">
                            <?= e(strtoupper(mb_substr($plg['nama'],0,1))); ?>
                        </div>
                        <h5 class="fw-bold mb-1"><?= e($plg['nama']); ?></h5>
                        <p class="text-muted mb-2"><span class="code-badge"><?= e($plg['kode_pelanggan']); ?></span></p>
                        <span class="badge <?= $plg['status']==='Aktif' ? 'badge-aktif' : 'badge-nonaktif'; ?> py-2 px-3">
                            <?= e($plg['status']); ?>
                        </span>
                    </div>
                </div>

                <!-- Info Detail -->
                <div class="col-12 col-md-8">
                    <div class="card h-100">
                        <div class="card-header">
                            <h6 class="mb-0 fw-bold">Informasi Pelanggan</h6>
                        </div>
                        <div class="card-body">
                            <table class="table table-borderless" style="font-size:14px;">
                                <tbody>
                                    <tr>
                                        <td class="text-muted" style="width:150px;">Jenis Kelamin</td>
                                        <td class="fw-semibold"><?= $plg['jenis_kelamin'] === 'L' ? '♂ Laki-laki' : '♀ Perempuan'; ?></td>
                                    </tr>
                                    <tr>
                                        <td class="text-muted">No. Telepon</td>
                                        <td class="fw-semibold font-monospace"><?= e($plg['telepon']); ?></td>
                                    </tr>
                                    <tr>
                                        <td class="text-muted">Email</td>
                                        <td class="fw-semibold"><?= e($plg['email'] ?: '-'); ?></td>
                                    </tr>
                                    <tr>
                                        <td class="text-muted">Tanggal Daftar</td>
                                        <td class="fw-semibold"><?= date('d F Y', strtotime($plg['tanggal_daftar'])); ?></td>
                                    </tr>
                                    <tr>
                                        <td class="text-muted">Terdaftar Sejak</td>
                                        <td class="fw-semibold text-muted"><?= date('d F Y H:i', strtotime($plg['created_at'])); ?></td>
                                    </tr>
                                    <tr>
                                        <td class="text-muted">Alamat</td>
                                        <td class="fw-semibold"><?= nl2br(e($plg['alamat'])); ?></td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <!-- Riwayat Transaksi -->
                <div class="col-12">
                    <div class="card">
                        <div class="card-header">
                            <h6 class="mb-0 fw-bold"><i class="fas fa-history me-2 text-primary"></i>Riwayat Transaksi</h6>
                            <a href="../transaksi/tambah.php?plg=<?= $id; ?>" class="btn btn-sm btn-primary">
                                <i class="fas fa-plus me-1"></i>Tambah Transaksi
                            </a>
                        </div>
                        <div class="table-responsive">
                            <table class="table">
                                <thead>
                                    <tr>
                                        <th>Kode</th>
                                        <th>Tanggal</th>
                                        <th>Jenis</th>
                                        <th class="text-end">Jumlah</th>
                                        <th>Status</th>
                                        <th>Keterangan</th>
                                    </tr>
                                </thead>
                                <tbody>
                                <?php if (mysqli_num_rows($trx_result) > 0):
                                    while ($t = mysqli_fetch_assoc($trx_result)): ?>
                                    <tr>
                                        <td><span class="code-badge"><?= e($t['kode_transaksi']); ?></span></td>
                                        <td><?= date('d/m/Y', strtotime($t['tanggal'])); ?></td>
                                        <td>
                                            <span class="badge <?= $t['jenis_transaksi']==='Pemasukan' ? 'badge-masuk' : 'badge-keluar'; ?>">
                                                <?= e($t['jenis_transaksi']); ?>
                                            </span>
                                        </td>
                                        <td class="text-end fw-bold"><?= rupiah($t['jumlah']); ?></td>
                                        <td>
                                            <?php $cls = match($t['status']) {'Lunas'=>'badge-lunas','Pending'=>'badge-pending',default=>'badge-batal'}; ?>
                                            <span class="badge <?= $cls; ?>"><?= e($t['status']); ?></span>
                                        </td>
                                        <td class="text-muted"><?= e($t['keterangan'] ?: '-'); ?></td>
                                    </tr>
                                    <?php endwhile;
                                else: ?>
                                    <tr><td colspan="6" class="text-center py-4 text-muted">Belum ada transaksi.</td></tr>
                                <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

        </div>
        </main>
    </div>
</div>

<?php
mysqli_stmt_close($s2);
require_once '../includes/footer.php';
?>
