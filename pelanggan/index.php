<?php
/**
 * FILE: pelanggan/index.php
 * DESKRIPSI: Halaman daftar semua data pelanggan.
 * Fitur: Server-side search, filter status, sorting, pagination,
 *        preview modal, konfirmasi hapus SweetAlert2.
 */

$base_path    = '../';
$page_title   = 'Data Pelanggan';
$current_page = 'pelanggan';

require_once '../config/koneksi.php';
proteksi_halaman();
require_once '../includes/header.php';

// ===========================================================
// PARAMETER: Search, Filter, Sort, Page
// ===========================================================
$search  = trim($_GET['search']  ?? '');
$status  = trim($_GET['status']  ?? '');
$sort    = in_array($_GET['sort'] ?? '', ['kode_pelanggan','nama','email','tanggal_daftar','status'])
           ? $_GET['sort'] : 'kode_pelanggan';
$order   = ($_GET['order'] ?? 'ASC') === 'DESC' ? 'DESC' : 'ASC';
$per_page = 10;
$page    = max(1, (int)($_GET['page'] ?? 1));
$offset  = ($page - 1) * $per_page;

// ===========================================================
// QUERY DENGAN PREPARED STATEMENT
// ===========================================================
$params  = [];
$types   = '';
$where   = [];

if ($search !== '') {
    $where[] = "(nama LIKE ? OR kode_pelanggan LIKE ? OR email LIKE ? OR telepon LIKE ?)";
    $s = "%$search%";
    array_push($params, $s, $s, $s, $s);
    $types .= 'ssss';
}
if ($status !== '') {
    $where[] = "status = ?";
    $params[] = $status;
    $types .= 's';
}

$where_sql = $where ? 'WHERE ' . implode(' AND ', $where) : '';

// Hitung total
$sql_count = "SELECT COUNT(*) c FROM pelanggan $where_sql";
$stmt_c = mysqli_prepare($koneksi, $sql_count);
if ($types) mysqli_stmt_bind_param($stmt_c, $types, ...$params);
mysqli_stmt_execute($stmt_c);
$total_rows = (int)(mysqli_fetch_assoc(mysqli_stmt_get_result($stmt_c))['c'] ?? 0);
mysqli_stmt_close($stmt_c);

$total_pages = $total_rows > 0 ? ceil($total_rows / $per_page) : 1;

// Data aktual
$sql_data = "SELECT * FROM pelanggan $where_sql ORDER BY $sort $order LIMIT ? OFFSET ?";
$stmt = mysqli_prepare($koneksi, $sql_data);
$params_data = array_merge($params, [$per_page, $offset]);
$types_data  = $types . 'ii';
mysqli_stmt_bind_param($stmt, $types_data, ...$params_data);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
?>

<div class="d-flex" id="app-layout">
<?php require_once '../includes/sidebar.php'; ?>

    <div id="main-wrapper">
        <?php require_once '../includes/navbar.php'; ?>

        <main>
        <div class="content-wrapper">

            <!-- Page Header -->
            <div class="page-header d-flex flex-column flex-sm-row justify-content-between align-items-start gap-3">
                <div>
                    <h4 class="mb-1"><i class="fas fa-users text-primary me-2"></i>Data Pelanggan</h4>
                    <p class="mb-0">Kelola informasi lengkap data pelanggan bisnis Anda.</p>
                </div>
                <a href="tambah.php" class="btn btn-primary">
                    <i class="fas fa-plus me-2"></i>Tambah Pelanggan
                </a>
            </div>

            <!-- Filter & Search Bar -->
            <div class="card mb-3">
                <div class="card-body">
                    <form method="GET" action="index.php" class="row g-2 align-items-end">
                        <div class="col-12 col-sm-5 col-md-4">
                            <label class="form-label">Cari Pelanggan</label>
                            <div class="input-group">
                                <span class="input-group-text" style="border-radius:8px 0 0 8px;background:var(--bg-body);">
                                    <i class="fas fa-search text-muted" style="font-size:12px;"></i>
                                </span>
                                <input type="text" name="search" class="form-control"
                                       placeholder="Nama, kode, email, telepon…"
                                       value="<?= e($search); ?>"
                                       style="border-radius:0 8px 8px 0;">
                            </div>
                        </div>
                        <div class="col-6 col-sm-3 col-md-2">
                            <label class="form-label">Status</label>
                            <select name="status" class="form-select" onchange="this.form.submit()">
                                <option value="">Semua</option>
                                <option value="Aktif"    <?= $status==='Aktif'    ?'selected':''; ?>>Aktif</option>
                                <option value="Nonaktif" <?= $status==='Nonaktif' ?'selected':''; ?>>Nonaktif</option>
                            </select>
                        </div>
                        <div class="col-6 col-sm-3 col-md-2">
                            <label class="form-label">Urutkan</label>
                            <select name="sort" class="form-select" onchange="this.form.submit()">
                                <option value="kode_pelanggan" <?= $sort==='kode_pelanggan'?'selected':''; ?>>Kode</option>
                                <option value="nama"           <?= $sort==='nama'           ?'selected':''; ?>>Nama</option>
                                <option value="tanggal_daftar" <?= $sort==='tanggal_daftar' ?'selected':''; ?>>Tanggal</option>
                                <option value="status"         <?= $sort==='status'         ?'selected':''; ?>>Status</option>
                            </select>
                        </div>
                        <div class="col-6 col-sm-auto">
                            <label class="form-label">Urutan</label>
                            <select name="order" class="form-select" onchange="this.form.submit()">
                                <option value="ASC"  <?= $order==='ASC'  ?'selected':''; ?>>A → Z</option>
                                <option value="DESC" <?= $order==='DESC' ?'selected':''; ?>>Z → A</option>
                            </select>
                        </div>
                        <div class="col-6 col-sm-auto d-flex gap-2">
                            <button type="submit" class="btn btn-primary"><i class="fas fa-search me-1"></i>Cari</button>
                            <a href="index.php" class="btn btn-outline-secondary" title="Reset"><i class="fas fa-undo"></i></a>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Tabel Data -->
            <div class="table-wrapper">
                <div class="p-3 border-bottom d-flex justify-content-between align-items-center flex-wrap gap-2">
                    <div>
                        <strong><?= number_format($total_rows); ?></strong> data ditemukan
                        <?php if ($search || $status): ?>
                            <span class="text-muted ms-1 small">(difilter)</span>
                            <a href="index.php" class="text-danger ms-2 small"><i class="fas fa-times me-1"></i>Hapus Filter</a>
                        <?php endif; ?>
                    </div>
                    <div class="d-flex gap-2">
                        <input type="text" id="table-search" class="form-control form-control-sm" placeholder="🔍 Live search…" style="width:180px;">
                    </div>
                </div>

                <div class="table-responsive">
                    <table class="table table-hover searchable-table" id="main-table">
                        <thead>
                            <tr>
                                <th style="width:50px;">No</th>
                                <th>Kode Pelanggan</th>
                                <th>Nama</th>
                                <th class="d-none d-md-table-cell">Telepon</th>
                                <th class="d-none d-lg-table-cell">Email</th>
                                <th class="d-none d-md-table-cell">Tgl Daftar</th>
                                <th>Status</th>
                                <th class="text-center" style="width:140px;">Aksi</th>
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
                                <td><span class="code-badge"><?= e($row['kode_pelanggan']); ?></span></td>
                                <td>
                                    <div class="fw-semibold"><?= e($row['nama']); ?></div>
                                    <small class="text-muted"><?= $row['jenis_kelamin'] === 'L' ? '♂ Laki-laki' : '♀ Perempuan'; ?></small>
                                </td>
                                <td class="d-none d-md-table-cell font-monospace text-muted"><?= e($row['telepon']); ?></td>
                                <td class="d-none d-lg-table-cell text-muted"><?= e($row['email']); ?></td>
                                <td class="d-none d-md-table-cell text-muted"><?= date('d/m/Y', strtotime($row['tanggal_daftar'])); ?></td>
                                <td>
                                    <span class="badge <?= $row['status'] === 'Aktif' ? 'badge-aktif' : 'badge-nonaktif'; ?>">
                                        <?= e($row['status']); ?>
                                    </span>
                                </td>
                                <td class="text-center">
                                    <div class="d-flex gap-1 justify-content-center">
                                        <!-- Detail -->
                                        <a href="detail.php?id=<?= $row['id_pelanggan']; ?>"
                                           class="btn btn-xs btn-outline-info" title="Detail">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                        <!-- Edit -->
                                        <a href="edit.php?id=<?= $row['id_pelanggan']; ?>"
                                           class="btn btn-xs btn-outline-warning" title="Edit">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                        <!-- Hapus (Admin only) -->
                                        <?php if ($_SESSION['role'] === 'Admin'): ?>
                                        <button type="button"
                                                class="btn btn-xs btn-outline-danger" title="Hapus"
                                                onclick="confirmDelete('hapus.php?id=<?= $row['id_pelanggan']; ?>', '<?= e(addslashes($row['nama'])); ?>')">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                        <?php else: ?>
                                        <button class="btn btn-xs btn-outline-secondary" disabled title="Hanya Admin">
                                            <i class="fas fa-lock"></i>
                                        </button>
                                        <?php endif; ?>
                                    </div>
                                </td>
                            </tr>
                        <?php
                            endwhile;
                        else:
                        ?>
                            <tr>
                                <td colspan="8" class="text-center py-5 text-muted">
                                    <i class="fas fa-users-slash fa-3x mb-3 d-block opacity-25"></i>
                                    Tidak ada data pelanggan<?= $search ? ' yang cocok dengan pencarian.' : '.'; ?>
                                </td>
                            </tr>
                        <?php endif; ?>
                        </tbody>
                    </table>
                </div>

                <!-- Pagination -->
                <?php if ($total_pages > 1): ?>
                <div class="p-3 border-top d-flex justify-content-between align-items-center flex-wrap gap-2">
                    <small class="text-muted">
                        Halaman <?= $page; ?> dari <?= $total_pages; ?>
                        (<?= number_format($total_rows); ?> total data)
                    </small>
                    <nav>
                        <ul class="pagination pagination-sm mb-0">
                            <li class="page-item <?= $page<=1 ? 'disabled' : ''; ?>">
                                <a class="page-link" href="?page=<?= $page-1; ?>&search=<?= urlencode($search); ?>&status=<?= urlencode($status); ?>&sort=<?= $sort; ?>&order=<?= $order; ?>">
                                    <i class="fas fa-chevron-left"></i>
                                </a>
                            </li>
                            <?php for ($i = max(1,$page-2); $i <= min($total_pages,$page+2); $i++): ?>
                            <li class="page-item <?= $i==$page ? 'active' : ''; ?>">
                                <a class="page-link" href="?page=<?= $i; ?>&search=<?= urlencode($search); ?>&status=<?= urlencode($status); ?>&sort=<?= $sort; ?>&order=<?= $order; ?>"><?= $i; ?></a>
                            </li>
                            <?php endfor; ?>
                            <li class="page-item <?= $page>=$total_pages ? 'disabled' : ''; ?>">
                                <a class="page-link" href="?page=<?= $page+1; ?>&search=<?= urlencode($search); ?>&status=<?= urlencode($status); ?>&sort=<?= $sort; ?>&order=<?= $order; ?>">
                                    <i class="fas fa-chevron-right"></i>
                                </a>
                            </li>
                        </ul>
                    </nav>
                </div>
                <?php endif; ?>
            </div><!-- .table-wrapper -->

        </div><!-- .content-wrapper -->
        </main>
    </div><!-- #main-wrapper -->
</div>

<?php
mysqli_stmt_close($stmt);
require_once '../includes/footer.php';
?>
