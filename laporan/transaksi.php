<?php
$base_path    = '../';
$page_title   = 'Laporan Transaksi';
$current_page = 'laporan-transaksi';

require_once '../config/koneksi.php';
proteksi_halaman();
require_once '../includes/header.php';

// Filter
$start_date = $_GET['start_date'] ?? date('Y-m-01');
$end_date   = $_GET['end_date'] ?? date('Y-m-t');
$jenis      = $_GET['jenis'] ?? '';
$status     = $_GET['status'] ?? '';

// Query
$where = ["t.tanggal BETWEEN ? AND ?"];
$params = [$start_date, $end_date];
$types = "ss";

if ($jenis !== '') {
    $where[] = "t.jenis_transaksi = ?";
    $params[] = $jenis;
    $types .= "s";
}
if ($status !== '') {
    $where[] = "t.status = ?";
    $params[] = $status;
    $types .= "s";
}

$where_sql = 'WHERE ' . implode(' AND ', $where);
$sql_data = "SELECT t.*, p.nama AS nama_pelanggan FROM transaksi t
             JOIN pelanggan p ON t.id_pelanggan=p.id_pelanggan
             $where_sql ORDER BY t.tanggal DESC, t.id_transaksi DESC";

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
                    <h4 class="mb-1"><i class="fas fa-file-invoice-dollar text-primary me-2"></i>Laporan Transaksi</h4>
                    <p class="mb-0">Cetak riwayat transaksi berdasarkan periode waktu.</p>
                </div>
                <button onclick="window.print()" class="btn btn-success">
                    <i class="fas fa-print me-2"></i>Cetak Laporan
                </button>
            </div>

            <div class="card mb-3 print-hide">
                <div class="card-body">
                    <form method="GET" action="transaksi.php" class="row g-2 align-items-end">
                        <div class="col-12 col-sm-6 col-md-3">
                            <label class="form-label">Tanggal Awal</label>
                            <input type="date" name="start_date" class="form-control" value="<?= e($start_date); ?>">
                        </div>
                        <div class="col-12 col-sm-6 col-md-3">
                            <label class="form-label">Tanggal Akhir</label>
                            <input type="date" name="end_date" class="form-control" value="<?= e($end_date); ?>">
                        </div>
                        <div class="col-6 col-sm-6 col-md-2">
                            <label class="form-label">Jenis</label>
                            <select name="jenis" class="form-select">
                                <option value="">Semua</option>
                                <option value="Pemasukan" <?= $jenis==='Pemasukan'?'selected':''; ?>>Pemasukan</option>
                                <option value="Pengeluaran" <?= $jenis==='Pengeluaran'?'selected':''; ?>>Pengeluaran</option>
                            </select>
                        </div>
                        <div class="col-6 col-sm-6 col-md-2">
                            <label class="form-label">Status</label>
                            <select name="status" class="form-select">
                                <option value="">Semua</option>
                                <option value="Lunas" <?= $status==='Lunas'?'selected':''; ?>>Lunas</option>
                                <option value="Pending" <?= $status==='Pending'?'selected':''; ?>>Pending</option>
                                <option value="Dibatalkan" <?= $status==='Dibatalkan'?'selected':''; ?>>Dibatalkan</option>
                            </select>
                        </div>
                        <div class="col-12 col-md-2 d-flex gap-2">
                            <button type="submit" class="btn btn-primary w-100"><i class="fas fa-filter me-1"></i>Filter</button>
                        </div>
                    </form>
                </div>
            </div>

            <div class="card print-area">
                <div class="card-body">
                    <div class="text-center mb-4 d-none print-show">
                        <h3>Laporan Transaksi</h3>
                        <p>Periode: <?= date('d/m/Y', strtotime($start_date)); ?> s/d <?= date('d/m/Y', strtotime($end_date)); ?></p>
                        <hr>
                    </div>
                    <div class="table-responsive">
                        <table class="table table-bordered table-striped">
                            <thead>
                                <tr>
                                    <th>No</th>
                                    <th>Tanggal</th>
                                    <th>Kode Transaksi</th>
                                    <th>Pelanggan</th>
                                    <th>Jenis</th>
                                    <th>Status</th>
                                    <th class="text-end">Jumlah</th>
                                </tr>
                            </thead>
                            <tbody>
                            <?php
                            $total_pemasukan = 0;
                            $total_pengeluaran = 0;
                            if (mysqli_num_rows($result) > 0):
                                $no = 1;
                                while ($row = mysqli_fetch_assoc($result)):
                                    if ($row['jenis_transaksi'] === 'Pemasukan' && $row['status'] === 'Lunas') {
                                        $total_pemasukan += $row['jumlah'];
                                    } elseif ($row['jenis_transaksi'] === 'Pengeluaran' && $row['status'] === 'Lunas') {
                                        $total_pengeluaran += $row['jumlah'];
                                    }
                            ?>
                                <tr>
                                    <td><?= $no++; ?></td>
                                    <td><?= date('d/m/Y', strtotime($row['tanggal'])); ?></td>
                                    <td><?= e($row['kode_transaksi']); ?></td>
                                    <td><?= e($row['nama_pelanggan']); ?></td>
                                    <td>
                                        <?php if ($row['jenis_transaksi'] === 'Pemasukan'): ?>
                                            <span class="badge badge-masuk print-hide"><i class="fas fa-arrow-down me-1"></i>Masuk</span>
                                            <span class="d-none print-show">Pemasukan</span>
                                        <?php else: ?>
                                            <span class="badge badge-keluar print-hide"><i class="fas fa-arrow-up me-1"></i>Keluar</span>
                                            <span class="d-none print-show">Pengeluaran</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <?php $cls = match($row['status']) {'Lunas'=>'badge-lunas','Pending'=>'badge-pending',default=>'badge-batal'}; ?>
                                        <span class="badge <?= $cls; ?> print-hide"><?= e($row['status']); ?></span>
                                        <span class="d-none print-show"><?= e($row['status']); ?></span>
                                    </td>
                                    <td class="text-end fw-bold"><?= rupiah($row['jumlah']); ?></td>
                                </tr>
                            <?php endwhile; else: ?>
                                <tr><td colspan="7" class="text-center py-4">Tidak ada data transaksi pada periode ini.</td></tr>
                            <?php endif; ?>
                            </tbody>
                            <tfoot>
                                <tr>
                                    <th colspan="6" class="text-end">Total Pemasukan (Lunas)</th>
                                    <th class="text-end text-success"><?= rupiah($total_pemasukan); ?></th>
                                </tr>
                                <tr>
                                    <th colspan="6" class="text-end">Total Pengeluaran (Lunas)</th>
                                    <th class="text-end text-danger"><?= rupiah($total_pengeluaran); ?></th>
                                </tr>
                                <tr>
                                    <th colspan="6" class="text-end">Saldo</th>
                                    <th class="text-end fw-bold"><?= rupiah($total_pemasukan - $total_pengeluaran); ?></th>
                                </tr>
                            </tfoot>
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
