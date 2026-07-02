<?php
/**
 * FILE: transaksi/index.php
 * DESKRIPSI: Halaman daftar semua transaksi keuangan.
 * Fitur: search, filter jenis & status, sorting, pagination, live search.
 */

$base_path    = '../';
$page_title   = 'Data Transaksi';
$current_page = 'transaksi';

require_once '../config/koneksi.php';
proteksi_halaman();
require_once '../includes/header.php';

// ===========================================================
// PARAMETER
// ===========================================================
$search  = trim($_GET['search']  ?? '');
$jenis   = trim($_GET['jenis']   ?? '');
$status  = trim($_GET['status']  ?? '');
$sort    = in_array($_GET['sort'] ?? '', ['kode_transaksi','tanggal','jenis_transaksi','jumlah','status'])
           ? $_GET['sort'] : 'id_transaksi';
$order   = ($_GET['order'] ?? 'DESC') === 'ASC' ? 'ASC' : 'DESC';
$per_page = 10;
$page    = max(1, (int)($_GET['page'] ?? 1));
$offset  = ($page - 1) * $per_page;

// ===========================================================
// QUERY
// ===========================================================
$params  = [];
$types   = '';
$where   = [];

if ($search !== '') {
    $where[] = "(t.kode_transaksi LIKE ? OR p.nama LIKE ? OR t.keterangan LIKE ?)";
    $s = "%$search%";
    array_push($params, $s, $s, $s);
    $types .= 'sss';
}
if ($jenis !== '') {
    $where[] = "t.jenis_transaksi = ?";
    $params[] = $jenis; $types .= 's';
}
if ($status !== '') {
    $where[] = "t.status = ?";
    $params[] = $status; $types .= 's';
}

$where_sql = $where ? 'WHERE ' . implode(' AND ', $where) : '';

// Hitung total
$sql_count = "SELECT COUNT(*) c FROM transaksi t JOIN pelanggan p ON t.id_pelanggan=p.id_pelanggan $where_sql";
$stmt_c = mysqli_prepare($koneksi, $sql_count);
if ($types) mysqli_stmt_bind_param($stmt_c, $types, ...$params);
mysqli_stmt_execute($stmt_c);
$total_rows = (int)(mysqli_fetch_assoc(mysqli_stmt_get_result($stmt_c))['c'] ?? 0);
mysqli_stmt_close($stmt_c);
$total_pages = $total_rows > 0 ? ceil($total_rows / $per_page) : 1;

// Data
$sql_data = "SELECT t.*, p.nama AS nama_pelanggan FROM transaksi t
             JOIN pelanggan p ON t.id_pelanggan=p.id_pelanggan
             $where_sql ORDER BY t.$sort $order LIMIT ? OFFSET ?";
$stmt = mysqli_prepare($koneksi, $sql_data);
$pd = array_merge($params, [$per_page, $offset]);
$td = $types . 'ii';
mysqli_stmt_bind_param($stmt, $td, ...$pd);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
?>

<div class="d-flex" id="app-layout">
<?php require_once '../includes/sidebar.php'; ?>

    <div id="main-wrapper">
        <?php require_once '../includes/navbar.php'; ?>
        <main>
        <div class="content-wrapper">

            <div class="page-header d-flex flex-column flex-sm-row justify-content-between align-items-start gap-3">
                <div>
                    <h4 class="mb-1"><i class="fas fa-exchange-alt text-primary me-2"></i>Data Transaksi</h4>
                    <p class="mb-0">Riwayat mutasi keuangan seluruh pelanggan.</p>
                </div>
                <a href="tambah.php" class="btn btn-primary">
                    <i class="fas fa-plus me-2"></i>Tambah Transaksi
                </a>
            </div>

            <!-- Filter Bar -->
            <div class="card mb-3">
                <div class="card-body">
                    <form method="GET" action="index.php" class="row g-2 align-items-end">
                        <div class="col-12 col-sm-4 col-md-3">
                            <label class="form-label">Cari</label>
                            <input type="text" name="search" class="form-control"
                                   placeholder="Kode, pelanggan, keterangan…"
                                   value="<?= e($search); ?>">
                        </div>
                        <div class="col-6 col-sm-3 col-md-2">
                            <label class="form-label">Jenis</label>
                            <select name="jenis" class="form-select" onchange="this.form.submit()">
                                <option value="">Semua</option>
                                <option value="Pemasukan"   <?= $jenis==='Pemasukan'   ?'selected':''; ?>>Pemasukan</option>
                                <option value="Pengeluaran" <?= $jenis==='Pengeluaran' ?'selected':''; ?>>Pengeluaran</option>
                            </select>
                        </div>
                        <div class="col-6 col-sm-3 col-md-2">
                            <label class="form-label">Status</label>
                            <select name="status" class="form-select" onchange="this.form.submit()">
                                <option value="">Semua</option>
                                <option value="Lunas"      <?= $status==='Lunas'      ?'selected':''; ?>>Lunas</option>
                                <option value="Pending"    <?= $status==='Pending'    ?'selected':''; ?>>Pending</option>
                                <option value="Dibatalkan" <?= $status==='Dibatalkan' ?'selected':''; ?>>Dibatalkan</option>
                            </select>
                        </div>
                        <div class="col-6 col-sm-3 col-md-2">
                            <label class="form-label">Urutkan</label>
                            <select name="sort" class="form-select" onchange="this.form.submit()">
                                <option value="id_transaksi"   <?= $sort==='id_transaksi'   ?'selected':''; ?>>Terbaru</option>
                                <option value="tanggal"        <?= $sort==='tanggal'        ?'selected':''; ?>>Tanggal</option>
                                <option value="jumlah"         <?= $sort==='jumlah'         ?'selected':''; ?>>Jumlah</option>
                                <option value="jenis_transaksi"<?= $sort==='jenis_transaksi'?'selected':''; ?>>Jenis</option>
                            </select>
                        </div>
                        <div class="col-6 col-sm-auto d-flex gap-2">
                            <button type="submit" class="btn btn-primary"><i class="fas fa-search me-1"></i>Cari</button>
                            <a href="index.php" class="btn btn-outline-secondary" title="Reset"><i class="fas fa-undo"></i></a>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Tabel -->
            <div class="table-wrapper">
                <div class="p-3 border-bottom d-flex justify-content-between align-items-center flex-wrap gap-2">
                    <strong><?= number_format($total_rows); ?></strong><span class="text-muted ms-1">data transaksi</span>
                    <input type="text" id="table-search" class="form-control form-control-sm"
                           placeholder="🔍 Live search…" style="width:180px;">
                </div>

                <div class="table-responsive">
                    <table class="table table-hover searchable-table" id="main-table">
                        <thead>
                            <tr>
                                <th>No</th>
                                <th>Kode Transaksi</th>
                                <th>Pelanggan</th>
                                <th class="d-none d-md-table-cell">Tanggal</th>
                                <th>Jenis</th>
                                <th class="text-end">Jumlah</th>
                                <th class="d-none d-sm-table-cell">Status</th>
                                <th class="text-center">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                        <?php
                        if (mysqli_num_rows($result) > 0):
                            $no = $offset + 1;
                            while ($row = mysqli_fetch_assoc($result)):
                        ?>
                            <tr>
                                <td class="text-muted"><?= $no++; ?></td>
                                <td><span class="code-badge"><?= e($row['kode_transaksi']); ?></span></td>
                                <td class="fw-semibold"><?= e($row['nama_pelanggan']); ?></td>
                                <td class="d-none d-md-table-cell text-muted"><?= date('d/m/Y', strtotime($row['tanggal'])); ?></td>
                                <td>
                                    <?php if ($row['jenis_transaksi'] === 'Pemasukan'): ?>
                                        <span class="badge badge-masuk"><i class="fas fa-arrow-down me-1"></i>Masuk</span>
                                    <?php else: ?>
                                        <span class="badge badge-keluar"><i class="fas fa-arrow-up me-1"></i>Keluar</span>
                                    <?php endif; ?>
                                </td>
                                <td class="text-end fw-bold"><?= rupiah($row['jumlah']); ?></td>
                                <td class="d-none d-sm-table-cell">
                                    <?php $cls = match($row['status']) {'Lunas'=>'badge-lunas','Pending'=>'badge-pending',default=>'badge-batal'}; ?>
                                    <span class="badge <?= $cls; ?>"><?= e($row['status']); ?></span>
                                </td>
                                <td class="text-center">
                                    <div class="d-flex gap-1 justify-content-center">
                                        <a href="detail.php?id=<?= $row['id_transaksi']; ?>" class="btn btn-xs btn-outline-info" title="Detail"><i class="fas fa-eye"></i></a>
                                        <a href="edit.php?id=<?= $row['id_transaksi']; ?>" class="btn btn-xs btn-outline-warning" title="Edit"><i class="fas fa-edit"></i></a>
                                        <?php if ($_SESSION['role'] === 'Admin'): ?>
                                        <button type="button" class="btn btn-xs btn-outline-danger"
                                                onclick="confirmDelete('hapus.php?id=<?= $row['id_transaksi']; ?>', 'Transaksi <?= e(addslashes($row['kode_transaksi'])); ?>')">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                        <?php else: ?>
                                        <button class="btn btn-xs btn-outline-secondary" disabled title="Hanya Admin"><i class="fas fa-lock"></i></button>
                                        <?php endif; ?>
                                    </div>
                                </td>
                            </tr>
                        <?php endwhile;
                        else: ?>
                            <tr><td colspan="8" class="text-center py-5 text-muted">
                                <i class="fas fa-folder-open fa-3x mb-2 d-block opacity-25"></i>Tidak ada data transaksi.
                            </td></tr>
                        <?php endif; ?>
                        </tbody>
                    </table>
                </div>

                <!-- Pagination -->
                <?php if ($total_pages > 1): ?>
                <div class="p-3 border-top d-flex justify-content-between align-items-center flex-wrap gap-2">
                    <small class="text-muted">Halaman <?= $page; ?> dari <?= $total_pages; ?></small>
                    <nav>
                        <ul class="pagination pagination-sm mb-0">
                            <li class="page-item <?= $page<=1?'disabled':''; ?>">
                                <a class="page-link" href="?page=<?= $page-1; ?>&search=<?= urlencode($search); ?>&jenis=<?= urlencode($jenis); ?>&status=<?= urlencode($status); ?>&sort=<?= $sort; ?>&order=<?= $order; ?>">
                                    <i class="fas fa-chevron-left"></i></a>
                            </li>
                            <?php for ($i=max(1,$page-2);$i<=min($total_pages,$page+2);$i++): ?>
                            <li class="page-item <?= $i==$page?'active':''; ?>">
                                <a class="page-link" href="?page=<?= $i; ?>&search=<?= urlencode($search); ?>&jenis=<?= urlencode($jenis); ?>&status=<?= urlencode($status); ?>&sort=<?= $sort; ?>&order=<?= $order; ?>"><?= $i; ?></a>
                            </li>
                            <?php endfor; ?>
                            <li class="page-item <?= $page>=$total_pages?'disabled':''; ?>">
                                <a class="page-link" href="?page=<?= $page+1; ?>&search=<?= urlencode($search); ?>&jenis=<?= urlencode($jenis); ?>&status=<?= urlencode($status); ?>&sort=<?= $sort; ?>&order=<?= $order; ?>">
                                    <i class="fas fa-chevron-right"></i></a>
                            </li>
                        </ul>
                    </nav>
                </div>
                <?php endif; ?>
            </div>

        </div>
        </main>
    </div>
</div>

<?php
mysqli_stmt_close($stmt);
require_once '../includes/footer.php';
?>
