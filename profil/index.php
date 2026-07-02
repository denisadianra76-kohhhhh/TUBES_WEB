<?php
/**
 * FILE: profil/index.php
 * DESKRIPSI: Halaman profil pengguna saat ini.
 */

$base_path    = '../';
$page_title   = 'Profil Saya';
$current_page = 'profil';

require_once '../config/koneksi.php';
proteksi_halaman();

$user_id = (int)($_SESSION['id_user'] ?? 0);

if (!$user_id) {
    set_flash('error', 'Silakan login terlebih dahulu.');
    header('Location: ../auth/login.php'); exit;
}

$stmt = mysqli_prepare($koneksi, "SELECT * FROM user WHERE id_user = ? LIMIT 1");
mysqli_stmt_bind_param($stmt, 'i', $user_id);
mysqli_stmt_execute($stmt);
$user = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));
mysqli_stmt_close($stmt);

if (!$user) {
    set_flash('error', 'Data pengguna tidak ditemukan.');
    header('Location: ../auth/login.php'); exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verify_csrf()) {
        set_flash('error', 'Token keamanan tidak valid.');
        header('Location: index.php'); exit;
    }

    $nama   = trim($_POST['nama'] ?? '');
    $email  = trim($_POST['email'] ?? '');
    $pass   = $_POST['password'] ?? '';
    $confirm = $_POST['konfirmasi_password'] ?? '';

    $err = [];
    if (!$nama) $err[] = 'Nama wajib diisi.';
    if ($email && !filter_var($email, FILTER_VALIDATE_EMAIL)) $err[] = 'Format email tidak valid.';
    if ($pass !== '' || $confirm !== '') {
        if (strlen($pass) < 8) $err[] = 'Password minimal 8 karakter.';
        if ($pass !== $confirm) $err[] = 'Konfirmasi password tidak cocok.';
    }

    if ($err) {
        set_flash('error', implode('<br>', $err));
        header('Location: index.php'); exit;
    }

    if ($pass !== '') {
        $hash = password_hash($pass, PASSWORD_BCRYPT);
        $sql = "UPDATE user SET nama=?, email=?, password=? WHERE id_user=?";
        $stmt = mysqli_prepare($koneksi, $sql);
        mysqli_stmt_bind_param($stmt, 'sssi', $nama, $email, $hash, $user_id);
    } else {
        $sql = "UPDATE user SET nama=?, email=? WHERE id_user=?";
        $stmt = mysqli_prepare($koneksi, $sql);
        mysqli_stmt_bind_param($stmt, 'ssi', $nama, $email, $user_id);
    }

    if (mysqli_stmt_execute($stmt)) {
        $_SESSION['nama'] = $nama;
        set_flash('success', 'Profil berhasil diperbarui.');
        header('Location: index.php'); exit;
    }

    set_flash('error', 'Gagal memperbarui profil: ' . mysqli_error($koneksi));
    header('Location: index.php'); exit;
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

            <div class="page-header d-flex flex-column flex-sm-row justify-content-between align-items-start gap-3">
                <div>
                    <h4 class="mb-1"><i class="fas fa-user-circle text-primary me-2"></i>Profil Saya</h4>
                    <p class="mb-0">Kelola informasi akun Anda dan ubah password jika diperlukan.</p>
                </div>
            </div>

            <div class="row justify-content-center">
                <div class="col-12 col-xl-8">
                    <div class="card">
                        <div class="card-header">
                            <h6 class="mb-0 fw-bold">Informasi Akun</h6>
                        </div>
                        <div class="card-body">
                            <form action="index.php" method="POST" class="needs-validation" novalidate>
                                <?= csrf_field(); ?>
                                <div class="row g-3">
                                    <div class="col-md-6">
                                        <label class="form-label">Nama Lengkap <span class="text-danger">*</span></label>
                                        <input type="text" name="nama" class="form-control" required
                                               value="<?= e($_POST['nama'] ?? $user['nama']); ?>">
                                        <div class="invalid-feedback">Nama wajib diisi.</div>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label">Username</label>
                                        <input type="text" class="form-control" readonly
                                               value="<?= e($user['username']); ?>">
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label">Email</label>
                                        <input type="email" name="email" class="form-control"
                                               value="<?= e($_POST['email'] ?? $user['email']); ?>">
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label">Role</label>
                                        <input type="text" class="form-control" readonly
                                               value="<?= e($user['role']); ?>">
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label">Password Baru</label>
                                        <input type="password" name="password" class="form-control"
                                               placeholder="Kosongkan jika tidak ingin mengubah">
                                        <div class="form-text">Minimal 8 karakter jika ingin mengganti password.</div>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label">Konfirmasi Password</label>
                                        <input type="password" name="konfirmasi_password" class="form-control"
                                               placeholder="Ulangi password baru">
                                    </div>
                                </div>
                                <div class="d-flex justify-content-end gap-2 mt-4 border-top pt-3">
                                    <button type="submit" class="btn btn-primary"><i class="fas fa-save me-2"></i>Simpan Perubahan</button>
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
