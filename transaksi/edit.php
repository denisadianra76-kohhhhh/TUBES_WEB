<?php
/**
 * FILE: transaksi/edit.php
 * DESKRIPSI: Form edit transaksi.
 */

$base_path    = '../';
$page_title   = 'Edit Transaksi';
$current_page = 'transaksi';

require_once '../config/koneksi.php';
proteksi_halaman();

$id = (int)($_GET['id'] ?? 0);
if (!$id) { set_flash('error','ID tidak valid.'); header('Location: index.php'); exit; }

$sql = "SELECT t.*, p.nama AS nama_pelanggan FROM transaksi t
        JOIN pelanggan p ON t.id_pelanggan=p.id_pelanggan
        WHERE t.id_transaksi = ? LIMIT 1";
$stmt = mysqli_prepare($koneksi, $sql);
mysqli_stmt_bind_param($stmt, 'i', $id);
mysqli_stmt_execute($stmt);
$data = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));
mysqli_stmt_close($stmt);

if (!$data) { set_flash('error','Transaksi tidak ditemukan.'); header('Location: index.php'); exit; }

$pelanggan_list = mysqli_query($koneksi,
    "SELECT id_pelanggan, kode_pelanggan, nama FROM pelanggan ORDER BY nama ASC");

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verify_csrf()) {
        set_flash('error', 'Token keamanan tidak valid.');
        header("Location: edit.php?id=$id"); exit;
    }

    $id_pelanggan   = (int)($_POST['id_pelanggan'] ?? 0);
    $tanggal        = trim($_POST['tanggal'] ?? '');
    $jenis          = trim($_POST['jenis_transaksi'] ?? '');
    $jumlah         = trim($_POST['jumlah'] ?? '');
    $status         = trim($_POST['status'] ?? 'Lunas');
    $keterangan     = trim($_POST['keterangan'] ?? '');

    $err = [];
    if (!$id_pelanggan) $err[] = 'Pelanggan wajib dipilih.';
    if (!$tanggal) $err[] = 'Tanggal transaksi wajib diisi.';
    if (!in_array($jenis, ['Pemasukan','Pengeluaran'])) $err[] = 'Jenis transaksi tidak valid.';
    if (!is_numeric($jumlah) || (float)$jumlah <= 0) $err[] = 'Jumlah harus lebih besar dari 0.';
    if (!in_array($status, ['Lunas','Pending','Dibatalkan'])) $err[] = 'Status tidak valid.';

    if ($err) {
        set_flash('error', implode('<br>', $err));
        header("Location: edit.php?id=$id"); exit;
    }

    $sql = "UPDATE transaksi SET id_pelanggan=?, tanggal=?, jenis_transaksi=?, jumlah=?, keterangan=?, status=?
            WHERE id_transaksi=?";
    $stmt = mysqli_prepare($koneksi, $sql);
    $jumlah = number_format((float)$jumlah, 2, '.', '');
    mysqli_stmt_bind_param($stmt, 'issdssi', $id_pelanggan, $tanggal, $jenis, $jumlah, $keterangan, $status, $id);

    if (mysqli_stmt_execute($stmt)) {
        set_flash('success', 'Transaksi berhasil diperbarui!');
        header('Location: index.php'); exit;
    }

    set_flash('error', 'Gagal memperbarui transaksi: ' . mysqli_error($koneksi));
    header("Location: edit.php?id=$id"); exit;
}

$csrf = csrf_token();
require_once '../includes/header.php';
?>

<div class="d-flex" id="app-layout">
<?php require_once '../includes/sidebar.php'; ?>

    <div id="main-wrapper">
        <?php require_once '../includes/navbar.php'; ?>
        <main>
        <div class="content-wrapper">

            <div class="page-header">
                <nav class="breadcrumb-custom mb-2">
                    <a href="../dashboard.php">Dashboard</a><span class="sep">/</span>
                    <a href="index.php">Transaksi</a><span class="sep">/</span>
                    <span class="current">Edit</span>
                </nav>
                <h4><i class="fas fa-edit text-warning me-2"></i>Edit Transaksi</h4>
                <p>Perbarui data transaksi <strong><?= e($data['kode_transaksi']); ?></strong>.</p>
            </div>

            <div class="row justify-content-center">
                <div class="col-12 col-xl-10">
                    <div class="card">
                        <div class="card-header d-flex justify-content-between align-items-center">
                            <div>
                                <h6 class="mb-0 fw-bold">Edit Transaksi</h6>
                            </div>
                            <a href="index.php" class="btn btn-sm btn-outline-secondary">
                                <i class="fas fa-arrow-left me-1"></i>Kembali
                            </a>
                        </div>
                        <div class="card-body">
                            <form action="edit.php?id=<?= $id; ?>" method="POST" class="needs-validation" novalidate>
                                <?= csrf_field(); ?>
                                <div class="row g-3">
                                    <div class="col-md-6">
                                        <label class="form-label">Kode Transaksi</label>
                                        <input type="text" class="form-control font-monospace" readonly
                                               value="<?= e($data['kode_transaksi']); ?>" style="background:var(--bg-body);">
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label">Pelanggan <span class="text-danger">*</span></label>
                                        <select name="id_pelanggan" class="form-select" required>
                                            <option value="">Pilih pelanggan...</option>
                                            <?php while ($plg = mysqli_fetch_assoc($pelanggan_list)): ?>
                                            <option value="<?= $plg['id_pelanggan']; ?>"
                                                <?= $plg['id_pelanggan'] === (int)($_POST['id_pelanggan'] ?? $data['id_pelanggan']) ? 'selected' : ''; ?>>
                                                <?= e($plg['kode_pelanggan'] . ' — ' . $plg['nama']); ?>
                                            </option>
                                            <?php endwhile; ?>
                                        </select>
                                        <div class="invalid-feedback">Pilih pelanggan terlebih dahulu.</div>
                                    </div>
                                    <div class="col-md-4">
                                        <label class="form-label">Tanggal <span class="text-danger">*</span></label>
                                        <input type="date" name="tanggal" class="form-control"
                                               value="<?= e($_POST['tanggal'] ?? $data['tanggal']); ?>" required>
                                    </div>
                                    <div class="col-md-4">
                                        <label class="form-label">Jenis Transaksi <span class="text-danger">*</span></label>
                                        <select name="jenis_transaksi" class="form-select" required>
                                            <option value="Pemasukan" <?= ($_POST['jenis_transaksi'] ?? $data['jenis_transaksi']) === 'Pemasukan' ? 'selected' : ''; ?>>Pemasukan</option>
                                            <option value="Pengeluaran" <?= ($_POST['jenis_transaksi'] ?? $data['jenis_transaksi']) === 'Pengeluaran' ? 'selected' : ''; ?>>Pengeluaran</option>
                                        </select>
                                    </div>
                                    <div class="col-md-4">
                                        <label class="form-label">Jumlah <span class="text-danger">*</span></label>
                                        <input type="number" name="jumlah" class="form-control" step="0.01" min="0.01"
                                               value="<?= e($_POST['jumlah'] ?? $data['jumlah']); ?>" required>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label">Status <span class="text-danger">*</span></label>
                                        <select name="status" class="form-select" required>
                                            <option value="Lunas" <?= ($_POST['status'] ?? $data['status']) === 'Lunas' ? 'selected' : ''; ?>>Lunas</option>
                                            <option value="Pending" <?= ($_POST['status'] ?? $data['status']) === 'Pending' ? 'selected' : ''; ?>>Pending</option>
                                            <option value="Dibatalkan" <?= ($_POST['status'] ?? $data['status']) === 'Dibatalkan' ? 'selected' : ''; ?>>Dibatalkan</option>
                                        </select>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label">Keterangan</label>
                                        <input type="text" name="keterangan" class="form-control"
                                               value="<?= e($_POST['keterangan'] ?? $data['keterangan']); ?>">
                                    </div>
                                </div>
                                <div class="d-flex justify-content-end gap-2 mt-4 border-top pt-3">
                                    <a href="index.php" class="btn btn-outline-secondary"><i class="fas fa-times me-1"></i>Batal</a>
                                    <button type="submit" class="btn btn-warning text-white"><i class="fas fa-save me-2"></i>Perbarui Transaksi</button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>

        </div>
        </main>
    </div>
</div>

<?php require_once '../includes/footer.php';
