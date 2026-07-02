<?php
/**
 * FILE: pelanggan/edit.php
 * DESKRIPSI: Form edit data pelanggan yang sudah ada.
 * Mengambil data existing dari DB lalu mengisi form.
 */

$base_path    = '../';
$page_title   = 'Edit Pelanggan';
$current_page = 'pelanggan';

require_once '../config/koneksi.php';
proteksi_halaman();

$id = (int)($_GET['id'] ?? 0);
if (!$id) { set_flash('error', 'ID tidak valid.'); header('Location: index.php'); exit; }

// Ambil data existing
$stmt_sel = mysqli_prepare($koneksi, "SELECT * FROM pelanggan WHERE id_pelanggan = ? LIMIT 1");
mysqli_stmt_bind_param($stmt_sel, 'i', $id);
mysqli_stmt_execute($stmt_sel);
$data = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt_sel));
mysqli_stmt_close($stmt_sel);

if (!$data) { set_flash('error', 'Data pelanggan tidak ditemukan.'); header('Location: index.php'); exit; }

// ===========================================================
// PROSES UPDATE
// ===========================================================
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verify_csrf()) { set_flash('error','Token CSRF tidak valid.'); header("Location: edit.php?id=$id"); exit; }

    $nama          = trim($_POST['nama']           ?? '');
    $alamat        = trim($_POST['alamat']          ?? '');
    $telepon       = trim($_POST['telepon']         ?? '');
    $email         = trim($_POST['email']           ?? '');
    $jenis_kelamin = trim($_POST['jenis_kelamin']   ?? '');
    $tgl_daftar    = trim($_POST['tanggal_daftar']  ?? '');
    $status        = trim($_POST['status']          ?? 'Aktif');

    $err = [];
    if (!$nama)          $err[] = 'Nama wajib diisi.';
    if (!$alamat)        $err[] = 'Alamat wajib diisi.';
    if (!preg_match('/^\d{6,15}$/', $telepon)) $err[] = 'Nomor telepon tidak valid.';
    if ($email && !filter_var($email, FILTER_VALIDATE_EMAIL)) $err[] = 'Format email tidak valid.';
    if (!in_array($jenis_kelamin, ['L','P'])) $err[] = 'Jenis kelamin wajib dipilih.';

    if ($err) { set_flash('error', implode('<br>',$err)); header("Location: edit.php?id=$id"); exit; }

    $sql  = "UPDATE pelanggan SET nama=?,alamat=?,telepon=?,email=?,jenis_kelamin=?,tanggal_daftar=?,status=?
             WHERE id_pelanggan=?";
    $stmt = mysqli_prepare($koneksi, $sql);
    mysqli_stmt_bind_param($stmt, 'sssssssi', $nama,$alamat,$telepon,$email,$jenis_kelamin,$tgl_daftar,$status,$id);

    if (mysqli_stmt_execute($stmt)) {
        set_flash('success', "Data pelanggan <strong>$nama</strong> berhasil diperbarui!");
        header('Location: index.php'); exit;
    } else {
        set_flash('error', 'Gagal memperbarui: ' . mysqli_error($koneksi));
        header("Location: edit.php?id=$id"); exit;
    }
}

require_once '../includes/header.php';
$csrf = csrf_token();
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
                    <a href="index.php">Pelanggan</a><span class="sep">/</span>
                    <span class="current">Edit</span>
                </nav>
                <h4><i class="fas fa-user-edit text-warning me-2"></i>Edit Pelanggan</h4>
                <p>Perbarui informasi pelanggan <strong><?= e($data['nama']); ?></strong>.</p>
            </div>

            <div class="row justify-content-center">
                <div class="col-12 col-xl-10">
                    <div class="card">
                        <div class="card-header">
                            <div>
                                <h6 class="mb-0 fw-bold">Edit Data Pelanggan</h6>
                                <small class="text-muted">Kode: <span class="code-badge"><?= e($data['kode_pelanggan']); ?></span></small>
                            </div>
                            <a href="index.php" class="btn btn-sm btn-outline-secondary">
                                <i class="fas fa-arrow-left me-1"></i>Kembali
                            </a>
                        </div>
                        <div class="card-body">
                            <form action="edit.php?id=<?= $id; ?>" method="POST" class="needs-validation" novalidate>
                                <?= csrf_field(); ?>

                                <div class="row g-3">
                                    <!-- Kode (readonly) -->
                                    <div class="col-md-6">
                                        <label class="form-label">Kode Pelanggan</label>
                                        <input type="text" class="form-control font-monospace fw-bold"
                                               value="<?= e($data['kode_pelanggan']); ?>" readonly
                                               style="background:var(--bg-body);">
                                    </div>

                                    <!-- Nama -->
                                    <div class="col-md-6">
                                        <label class="form-label">Nama Lengkap <span class="text-danger">*</span></label>
                                        <input type="text" name="nama" class="form-control"
                                               value="<?= e($data['nama']); ?>" required>
                                        <div class="invalid-feedback">Nama wajib diisi.</div>
                                    </div>

                                    <!-- Telepon -->
                                    <div class="col-md-6">
                                        <label class="form-label">No. Telepon <span class="text-danger">*</span></label>
                                        <input type="text" name="telepon" class="form-control"
                                               value="<?= e($data['telepon']); ?>" required pattern="[0-9]{6,15}">
                                        <div class="invalid-feedback">Nomor telepon 6-15 digit angka.</div>
                                    </div>

                                    <!-- Email -->
                                    <div class="col-md-6">
                                        <label class="form-label">Alamat Email</label>
                                        <input type="email" name="email" class="form-control"
                                               value="<?= e($data['email']); ?>">
                                    </div>

                                    <!-- Jenis Kelamin -->
                                    <div class="col-md-4">
                                        <label class="form-label">Jenis Kelamin <span class="text-danger">*</span></label>
                                        <div class="d-flex gap-3 mt-1">
                                            <div class="form-check">
                                                <input class="form-check-input" type="radio" name="jenis_kelamin" id="jk_l" value="L"
                                                       <?= $data['jenis_kelamin'] === 'L' ? 'checked' : ''; ?> required>
                                                <label class="form-check-label" for="jk_l">♂ Laki-laki</label>
                                            </div>
                                            <div class="form-check">
                                                <input class="form-check-input" type="radio" name="jenis_kelamin" id="jk_p" value="P"
                                                       <?= $data['jenis_kelamin'] === 'P' ? 'checked' : ''; ?>>
                                                <label class="form-check-label" for="jk_p">♀ Perempuan</label>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Tanggal Daftar -->
                                    <div class="col-md-4">
                                        <label class="form-label">Tanggal Daftar <span class="text-danger">*</span></label>
                                        <input type="date" name="tanggal_daftar" class="form-control"
                                               value="<?= e($data['tanggal_daftar']); ?>" required>
                                    </div>

                                    <!-- Status -->
                                    <div class="col-md-4">
                                        <label class="form-label">Status</label>
                                        <select name="status" class="form-select">
                                            <option value="Aktif"    <?= $data['status']==='Aktif'    ?'selected':''; ?>>Aktif</option>
                                            <option value="Nonaktif" <?= $data['status']==='Nonaktif' ?'selected':''; ?>>Nonaktif</option>
                                        </select>
                                    </div>

                                    <!-- Alamat -->
                                    <div class="col-12">
                                        <label class="form-label">Alamat Lengkap <span class="text-danger">*</span></label>
                                        <textarea name="alamat" class="form-control" rows="3" required><?= e($data['alamat']); ?></textarea>
                                        <div class="invalid-feedback">Alamat wajib diisi.</div>
                                    </div>
                                </div>

                                <div class="d-flex justify-content-end gap-2 mt-4 border-top pt-3">
                                    <a href="index.php" class="btn btn-outline-secondary"><i class="fas fa-times me-1"></i>Batal</a>
                                    <button type="submit" class="btn btn-warning text-white">
                                        <i class="fas fa-save me-2"></i>Perbarui Data
                                    </button>
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

<?php require_once '../includes/footer.php'; ?>
