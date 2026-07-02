<?php
$base_path    = '../';
$page_title   = 'Laporan Pelanggan';
$current_page = 'laporan-pelanggan';

require_once '../config/koneksi.php';
proteksi_halaman();
require_once '../includes/header.php';

// Filter
$status = $_GET['status'] ?? '';
$jk     = $_GET['jk'] ?? '';

// Query
$where = [];
$params = [];
$types = "";

if ($status !== '') {
    $where[] = "status = ?";
    $params[] = $status;
    $types .= "s";
}
if ($jk !== '') {
    $where[] = "jenis_kelamin = ?";
    $params[] = $jk;
    $types .= "s";
}

$where_sql = $where ? 'WHERE ' . implode(' AND ', $where) : '';
$sql_data = "SELECT * FROM pelanggan $where_sql ORDER BY tanggal_daftar DESC, id_pelanggan DESC";

$stmt = mysqli_prepare($koneksi, $sql_data);
if ($types) mysqli_stmt_bind_param($stmt, $types, ...$params);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
?>

<div class="d-flex" id="app-layout">
<?php require_once '../includes/sidebar.php'; ?>
    <div id="main-wrapper">
        <?php require_once '../includes/navbar.php'; ?>
        <main>
        <div class="content-wrapper">
            <div class="page-header d-flex flex-column flex-sm-row justify-content-between align-items-start gap-3 print-hide">
                <div>
                    <h4 class="mb-1"><i class="fas fa-file-user text-primary me-2"></i>Laporan Pelanggan</h4>
                    <p class="mb-0">Cetak data pelanggan yang terdaftar.</p>
                </div>
                <button onclick="window.print()" class="btn btn-success">
                    <i class="fas fa-print me-2"></i>Cetak Laporan
                </button>
            </div>

            <div class="card mb-3 print-hide">
                <div class="card-body">
                    <form method="GET" action="pelanggan.php" class="row g-2 align-items-end">
                        <div class="col-6 col-md-4">
                            <label class="form-label">Status</label>
                            <select name="status" class="form-select">
                                <option value="">Semua</option>
                                <option value="Aktif" <?= $status==='Aktif'?'selected':''; ?>>Aktif</option>
                                <option value="Nonaktif" <?= $status==='Nonaktif'?'selected':''; ?>>Nonaktif</option>
                            </select>
                        </div>
                        <div class="col-6 col-md-4">
                            <label class="form-label">Jenis Kelamin</label>
                            <select name="jk" class="form-select">
                                <option value="">Semua</option>
                                <option value="L" <?= $jk==='L'?'selected':''; ?>>Laki-laki</option>
                                <option value="P" <?= $jk==='P'?'selected':''; ?>>Perempuan</option>
                            </select>
                        </div>
                        <div class="col-12 col-md-4">
                            <button type="submit" class="btn btn-primary w-100"><i class="fas fa-filter me-1"></i>Filter</button>
                        </div>
                    </form>
                </div>
            </div>

            <div class="card print-area">
                <div class="card-body">
                    <div class="text-center mb-4 d-none print-show">
                        <h3>Laporan Pelanggan</h3>
                        <p>Tanggal Cetak: <?= date('d/m/Y'); ?></p>
                        <hr>
                    </div>
                    <div class="table-responsive">
                        <table class="table table-bordered table-striped">
                            <thead>
                                <tr>
                                    <th>No</th>
                                    <th>Kode</th>
                                    <th>Nama</th>
                                    <th>L/P</th>
                                    <th>Telepon</th>
                                    <th>Tgl Daftar</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody>
                            <?php
                            if (mysqli_num_rows($result) > 0):
                                $no = 1;
                                while ($row = mysqli_fetch_assoc($result)):
                            ?>
                                <tr>
                                    <td><?= $no++; ?></td>
                                    <td><?= e($row['kode_pelanggan']); ?></td>
                                    <td><span class="fw-bold"><?= e($row['nama']); ?></span></td>
                                    <td><?= e($row['jenis_kelamin']); ?></td>
                                    <td><?= e($row['telepon']); ?></td>
                                    <td><?= date('d/m/Y', strtotime($row['tanggal_daftar'])); ?></td>
                                    <td>
                                        <span class="badge <?= $row['status'] === 'Aktif' ? 'bg-success' : 'bg-danger'; ?> print-hide"><?= e($row['status']); ?></span>
                                        <span class="d-none print-show"><?= e($row['status']); ?></span>
                                    </td>
                                </tr>
                            <?php endwhile; else: ?>
                                <tr><td colspan="7" class="text-center py-4">Tidak ada data pelanggan.</td></tr>
                            <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
        </main>
    </div>
</div>

<style>
@media print {
    body * { visibility: hidden; }
    .print-area, .print-area * { visibility: visible; }
    .print-area { position: absolute; left: 0; top: 0; width: 100%; }
    .print-hide { display: none !important; }
    .print-show { display: block !important; }
    .card { border: none !important; box-shadow: none !important; }
}
.print-show { display: none; }
</style>

<?php
mysqli_stmt_close($stmt);
require_once '../includes/footer.php';
?>
