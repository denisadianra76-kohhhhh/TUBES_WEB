<?php
/**
 * FILE: pelanggan/tambah.php
 * DESKRIPSI: Form tambah data pelanggan baru.
 * Auto-generate kode pelanggan format PLG-YYYY-XXXX.
 * Dilengkapi validasi client + server side, CSRF protection.
 */

$base_path    = '../';
$page_title   = 'Tambah Pelanggan';
$current_page = 'pelanggan';

require_once '../config/koneksi.php';
proteksi_halaman();
require_once '../includes/header.php';

// Auto-generate kode pelanggan baru
$kode_baru = generate_kode_pelanggan();

// ===========================================================
// PROSES SUBMIT FORM
// ===========================================================
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verify_csrf()) {
        set_flash('error', 'Token keamanan tidak valid. Coba lagi.');
        header('Location: tambah.php'); exit;
    }

    $kode          = trim($_POST['kode_pelanggan'] ?? '');
    $nama          = trim($_POST['nama']           ?? '');
    $alamat        = trim($_POST['alamat']          ?? '');
    $telepon       = trim($_POST['telepon']         ?? '');
    $email         = trim($_POST['email']           ?? '');
    $jenis_kelamin = trim($_POST['jenis_kelamin']   ?? '');
    $tgl_daftar    = trim($_POST['tanggal_daftar']  ?? '');
    $status        = trim($_POST['status']          ?? 'Aktif');

    // Validasi
    $err = [];
    if (!$nama)          $err[] = 'Nama wajib diisi.';
    if (!$alamat)        $err[] = 'Alamat wajib diisi.';
    if (!preg_match('/^\d{6,15}$/', $telepon)) $err[] = 'Nomor telepon tidak valid (6-15 digit angka).';
    if ($email && !filter_var($email, FILTER_VALIDATE_EMAIL)) $err[] = 'Format email tidak valid.';
    if (!in_array($jenis_kelamin, ['L','P'])) $err[] = 'Jenis kelamin wajib dipilih.';
    if (!$tgl_daftar) $err[] = 'Tanggal daftar wajib diisi.';
    if (!in_array($status, ['Aktif','Nonaktif'])) $err[] = 'Status tidak valid.';

    if ($err) {
        set_flash('error', implode('<br>', $err));
        header('Location: tambah.php'); exit;
    }

    $sql  = "INSERT INTO pelanggan (kode_pelanggan,nama,alamat,telepon,email,jenis_kelamin,tanggal_daftar,status)
             VALUES (?,?,?,?,?,?,?,?)";
    $stmt = mysqli_prepare($koneksi, $sql);
    mysqli_stmt_bind_param($stmt, 'ssssssss',
        $kode, $nama, $alamat, $telepon, $email, $jenis_kelamin, $tgl_daftar, $status);

    if (mysqli_stmt_execute($stmt)) {
        set_flash('success', "Pelanggan <strong>$nama</strong> berhasil ditambahkan!");
        header('Location: index.php'); exit;
    } else {
        set_flash('error', 'Gagal menyimpan data: ' . mysqli_error($koneksi));
        header('Location: tambah.php'); exit;
    }
}

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
                    <span class="current">Tambah</span>
                </nav>
                <h4><i class="fas fa-user-plus text-primary me-2"></i>Tambah Pelanggan Baru</h4>
                <p>Isi formulir di bawah ini untuk mendaftarkan pelanggan baru.</p>
            </div>

            <div class="row justify-content-center">
                <div class="col-12 col-xl-10">
                    <div class="card">
                        <div class="card-header">
                            <h6 class="mb-0 fw-bold">Formulir Data Pelanggan</h6>
                            <a href="index.php" class="btn btn-sm btn-outline-secondary">
                                <i class="fas fa-arrow-left me-1"></i>Kembali
                            </a>
                        </div>
                        <div class="card-body">
                            <form action="tambah.php" method="POST" class="needs-validation" novalidate>
                                <?= csrf_field(); ?>

                                <div class="row g-3">
                                    <!-- Kode Pelanggan (auto) -->
                                    <div class="col-md-6">
                                        <label class="form-label">Kode Pelanggan</label>
                                        <div class="input-group">
                                            <input type="text" name="kode_pelanggan" id="kode_pelanggan"
                                                   class="form-control font-monospace fw-bold"
                                                   value="<?= e($kode_baru); ?>" readonly
                                                   style="background:var(--bg-body);">
                                            <button type="button" class="btn btn-outline-secondary"
                                                    onclick="refreshKode()" title="Refresh Kode">
                                                <i class="fas fa-sync-alt"></i>
                                            </button>
                                        </div>
                                        <div class="form-text">Kode dibuat otomatis oleh sistem.</div>
                                    </div>

                                    <!-- Nama -->
                                    <div class="col-md-6">
                                        <label class="form-label">Nama Lengkap <span class="text-danger">*</span></label>
                                        <input type="text" name="nama" class="form-control"
                                               placeholder="Masukkan nama lengkap" required
                                               value="<?= e($_POST['nama'] ?? ''); ?>">
                                        <div class="invalid-feedback">Nama wajib diisi.</div>
                                    </div>

                                    <!-- Telepon -->
                                    <div class="col-md-6">
                                        <label class="form-label">No. Telepon <span class="text-danger">*</span></label>
                                        <div class="input-group">
                                            <span class="input-group-text" style="border-radius:8px 0 0 8px;background:var(--bg-body);">
                                                <i class="fas fa-phone text-muted" style="font-size:12px;"></i>
                                            </span>
                                            <input type="text" name="telepon" class="form-control"
                                                   placeholder="08xxxxxxxxxx" required
                                                   pattern="[0-9]{6,15}"
                                                   value="<?= e($_POST['telepon'] ?? ''); ?>"
                                                   style="border-radius:0 8px 8px 0;">
                                        </div>
                                        <div class="invalid-feedback">Nomor telepon harus 6-15 digit angka.</div>
                                    </div>

                                    <!-- Email -->
                                    <div class="col-md-6">
                                        <label class="form-label">Alamat Email</label>
                                        <div class="input-group">
                                            <span class="input-group-text" style="border-radius:8px 0 0 8px;background:var(--bg-body);">
                                                <i class="fas fa-envelope text-muted" style="font-size:12px;"></i>
                                            </span>
                                            <input type="email" name="email" class="form-control"
                                                   placeholder="email@contoh.com"
                                                   value="<?= e($_POST['email'] ?? ''); ?>"
                                                   style="border-radius:0 8px 8px 0;">
                                        </div>
                                    </div>

                                    <!-- Jenis Kelamin -->
                                    <div class="col-md-4">
                                        <label class="form-label">Jenis Kelamin <span class="text-danger">*</span></label>
                                        <div class="d-flex gap-3 mt-1">
                                            <div class="form-check">
                                                <input class="form-check-input" type="radio" name="jenis_kelamin" id="jk_l" value="L"
                                                       <?= ($_POST['jenis_kelamin'] ?? 'L') === 'L' ? 'checked' : ''; ?> required>
                                                <label class="form-check-label" for="jk_l">♂ Laki-laki</label>
                                            </div>
                                            <div class="form-check">
                                                <input class="form-check-input" type="radio" name="jenis_kelamin" id="jk_p" value="P"
                                                       <?= ($_POST['jenis_kelamin'] ?? '') === 'P' ? 'checked' : ''; ?> required>
                                                <label class="form-check-label" for="jk_p">♀ Perempuan</label>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Tanggal Daftar -->
                                    <div class="col-md-4">
                                        <label class="form-label">Tanggal Daftar <span class="text-danger">*</span></label>
                                        <input type="date" name="tanggal_daftar" class="form-control"
                                               value="<?= e($_POST['tanggal_daftar'] ?? date('Y-m-d')); ?>" required>
                                        <div class="invalid-feedback">Tanggal daftar wajib diisi.</div>
                                    </div>

                                    <!-- Status -->
                                    <div class="col-md-4">
                                        <label class="form-label">Status <span class="text-danger">*</span></label>
                                        <select name="status" class="form-select" required>
                                            <option value="Aktif"    <?= ($_POST['status'] ?? 'Aktif') === 'Aktif'    ? 'selected' : ''; ?>>Aktif</option>
                                            <option value="Nonaktif" <?= ($_POST['status'] ?? '') === 'Nonaktif' ? 'selected' : ''; ?>>Nonaktif</option>
                                        </select>
                                    </div>

                                    <!-- Alamat -->
                                    <div class="col-12">
                                        <label class="form-label">Alamat Lengkap <span class="text-danger">*</span></label>
                                        <textarea name="alamat" class="form-control" rows="3"
                                                  placeholder="Jl. Contoh No. 123, Kota, Provinsi…" required><?= e($_POST['alamat'] ?? ''); ?></textarea>
                                        <div class="invalid-feedback">Alamat wajib diisi.</div>
                                    </div>
                                </div><!-- .row -->

                                <!-- Action Buttons -->
                                <div class="d-flex justify-content-end gap-2 mt-4 border-top pt-3">
                                    <a href="index.php" class="btn btn-outline-secondary">
                                        <i class="fas fa-times me-1"></i>Batal
                                    </a>
                                    <button type="reset" class="btn btn-outline-warning">
                                        <i class="fas fa-undo me-1"></i>Reset
                                    </button>
                                    <button type="submit" class="btn btn-primary">
                                        <i class="fas fa-save me-2"></i>Simpan Pelanggan
                                    </button>
                                </div>
                            </form>
                        </div><!-- .card-body -->
                    </div><!-- .card -->
                </div>
            </div>

        </div>
        </main>
    </div>
</div>

<?php
$page_scripts = '<script>
function refreshKode() {
    // Regenerasi kode visual (PHP sudah buat kode saat load, ini hanya UI)
    const el = document.getElementById("kode_pelanggan");
    if (el) {
        el.style.opacity = "0.5";
        setTimeout(() => { el.style.opacity = "1"; }, 400);
    }
}
</script>';
require_once '../includes/footer.php';
?>
