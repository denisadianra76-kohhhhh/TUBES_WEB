<?php
/**
 * FILE: transaksi/detail.php
 * DESKRIPSI: Halaman detail transaksi.
 */

$base_path    = '../';
$page_title   = 'Detail Transaksi';
$current_page = 'transaksi';

require_once '../config/koneksi.php';
proteksi_halaman();

$id = (int)($_GET['id'] ?? 0);
if (!$id) { set_flash('error','ID tidak valid.'); header('Location: index.php'); exit; }

$sql = "SELECT t.*, p.nama AS nama_pelanggan, p.kode_pelanggan
        FROM transaksi t
        JOIN pelanggan p ON t.id_pelanggan = p.id_pelanggan
        WHERE t.id_transaksi = ? LIMIT 1";
$stmt = mysqli_prepare($koneksi, $sql);
mysqli_stmt_bind_param($stmt, 'i', $id);
mysqli_stmt_execute($stmt);
$data = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));
mysqli_stmt_close($stmt);

if (!$data) { set_flash('error','Transaksi tidak ditemukan.'); header('Location: index.php'); exit; }

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
                        <a href="index.php">Transaksi</a><span class="sep">/</span>
                        <span class="current">Detail</span>
                    </nav>
                    <h4><i class="fas fa-info-circle text-info me-2"></i>Detail Transaksi</h4>
                    <p>Informasi lengkap untuk transaksi <strong><?= e($data['kode_transaksi']); ?></strong>.</p>
                </div>
                <div class="d-flex gap-2">
                    <a href="edit.php?id=<?= $id; ?>" class="btn btn-warning text-white"><i class="fas fa-edit me-1"></i>Edit</a>
                    <a href="index.php" class="btn btn-outline-secondary"><i class="fas fa-arrow-left me-1"></i>Kembali</a>
                </div>
            </div>

            <div class="row gy-3">
                <div class="col-12 col-xl-5">
                    <div class="card">
                        <div class="card-body">
                            <div class="d-flex align-items-center gap-3 mb-4">
                                <div class="bg-primary text-white rounded-circle d-flex align-items-center justify-content-center" style="width:60px;height:60px;">
                                    <i class="fas fa-file-invoice-dollar fa-lg"></i>
                                </div>
                                <div>
                                    <h5 class="mb-1"><?= e($data['kode_transaksi']); ?></h5>
                                    <small class="text-muted"><?= date('d F Y', strtotime($data['tanggal'])); ?></small>
                                </div>
                            </div>
                            <table class="table table-borderless" style="font-size:14px;">
                                <tbody>
                                    <tr><td class="text-muted" style="width:160px;">Pelanggan</td><td class="fw-semibold"><?= e($data['kode_pelanggan'] . ' — ' . $data['nama_pelanggan']); ?></td></tr>
                                    <tr><td class="text-muted">Jenis</td><td class="fw-semibold"><?= e($data['jenis_transaksi']); ?></td></tr>
                                    <tr><td class="text-muted">Jumlah</td><td class="fw-semibold text-success"><?= rupiah($data['jumlah']); ?></td></tr>
                                    <tr><td class="text-muted">Status</td><td>
                                        <?php $cls = match($data['status']) {'Lunas'=>'badge-lunas','Pending'=>'badge-pending',default=>'badge-batal'}; ?>
                                        <span class="badge <?= $cls; ?>"><?= e($data['status']); ?></span>
                                    </td></tr>
                                    <tr><td class="text-muted">Keterangan</td><td class="fw-semibold"><?= e($data['keterangan'] ?: '-'); ?></td></tr>
                                    <tr><td class="text-muted">Dibuat</td><td class="fw-semibold"><?= date('d F Y H:i', strtotime($data['created_at'])); ?></td></tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
                <div class="col-12 col-xl-7">
                    <div class="card h-100">
                        <div class="card-header">
                            <h6 class="mb-0 fw-bold">Detail Lengkap</h6>
                        </div>
                        <div class="card-body">
                            <p class="mb-3">Berikut informasi transaksi yang tersimpan dalam sistem. Gunakan tombol edit untuk memperbarui data.</p>
                            <div class="row gy-2">
                                <div class="col-12"><strong class="text-muted">Kode Transaksi</strong><div><?= e($data['kode_transaksi']); ?></div></div>
                                <div class="col-12 col-md-6"><strong class="text-muted">Tanggal</strong><div><?= date('d/m/Y', strtotime($data['tanggal'])); ?></div></div>
                                <div class="col-12 col-md-6"><strong class="text-muted">Jenis Transaksi</strong><div><?= e($data['jenis_transaksi']); ?></div></div>
                                <div class="col-12 col-md-6"><strong class="text-muted">Jumlah</strong><div><?= rupiah($data['jumlah']); ?></div></div>
                                <div class="col-12 col-md-6"><strong class="text-muted">Status</strong><div><span class="badge <?= $cls; ?>"><?= e($data['status']); ?></span></div></div>
                                <div class="col-12"><strong class="text-muted">Keterangan</strong><div><?= nl2br(e($data['keterangan'] ?: '-')); ?></div></div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

        </div>
        </main>
    </div>
</div>

<?php require_once '../includes/footer.php';
