<?php
/**
 * FILE: auth/register.php
 * DESKRIPSI: Halaman Registrasi Pengguna Baru.
 * Hanya bisa diakses oleh Admin (untuk menambah petugas baru).
 * Bisa juga diakses publik jika ingin membuka pendaftaran.
 */

require_once __DIR__ . '/../config/koneksi.php';

// Jika ada session dan role-nya Admin, lanjut
// Jika belum login tapi ingin register, bisa dibiarkan terbuka
// (uncomment proteksi_admin() jika ingin hanya Admin yang bisa register)
proteksi_admin();

$error   = '';
$success = '';

// ===========================================================
// PROSES FORM REGISTER (POST)
// ===========================================================
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    if (!verify_csrf()) {
        $error = 'Token keamanan tidak valid.';
    } else {
        $nama       = trim($_POST['nama']     ?? '');
        $username   = trim($_POST['username'] ?? '');
        $email      = trim($_POST['email']    ?? '');
        $role       = trim($_POST['role']     ?? 'Petugas');
        $password   = $_POST['password']   ?? '';
        $konfirmasi = $_POST['konfirmasi_password'] ?? '';

        // Validasi server-side
        if (empty($nama) || empty($username) || empty($password) || empty($konfirmasi)) {
            $error = 'Semua kolom wajib diisi.';
        } elseif (strlen($password) < 8) {
            $error = 'Password minimal 8 karakter.';
        } elseif ($password !== $konfirmasi) {
            $error = 'Konfirmasi password tidak cocok.';
        } elseif (!in_array($role, ['Admin', 'Petugas'])) {
            $error = 'Role tidak valid.';
        } else {
            // Cek apakah username sudah ada
            $cek = mysqli_prepare($koneksi, "SELECT id_user FROM user WHERE username = ? LIMIT 1");
            mysqli_stmt_bind_param($cek, 's', $username);
            mysqli_stmt_execute($cek);
            mysqli_stmt_store_result($cek);

            if (mysqli_stmt_num_rows($cek) > 0) {
                $error = 'Username sudah digunakan. Pilih username lain.';
            } else {
                // Hash password dan insert
                $hashed = password_hash($password, PASSWORD_BCRYPT);
                $ins = mysqli_prepare($koneksi,
                    "INSERT INTO user (nama, username, email, password, role) VALUES (?, ?, ?, ?, ?)");
                mysqli_stmt_bind_param($ins, 'sssss', $nama, $username, $email, $hashed, $role);

                if (mysqli_stmt_execute($ins)) {
                    set_flash('success', "Pengguna <strong>$nama</strong> berhasil ditambahkan!");
                    header('Location: ../dashboard.php'); exit;
                } else {
                    $error = 'Gagal menyimpan data. Coba lagi.';
                }
                mysqli_stmt_close($ins);
            }
            mysqli_stmt_close($cek);
        }
    }
}

$csrf = csrf_token();
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registrasi — Pendataan Pelanggan</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link rel="stylesheet" href="../assets/css/style.css">
    <script>(function(){const t=localStorage.getItem('theme')||'light';document.documentElement.setAttribute('data-theme',t);})();</script>
</head>
<body class="auth-page">

    <div class="auth-box" style="max-width:480px;">
        <div class="auth-logo" style="background:#16A34A;">
            <i class="fas fa-user-plus"></i>
        </div>
        <h2 class="auth-title">Daftarkan Pengguna</h2>
        <p class="auth-sub">Tambah akun Admin atau Petugas baru</p>

        <?php if ($error): ?>
        <div class="alert alert-danger d-flex gap-2 align-items-center py-2 mb-4" style="border-radius:10px;font-size:13px;">
            <i class="fas fa-exclamation-circle"></i><?= e($error); ?>
        </div>
        <?php endif; ?>

        <form action="register.php" method="POST" class="needs-validation" novalidate>
            <input type="hidden" name="_csrf_token" value="<?= e($csrf); ?>">

            <div class="row g-3">
                <!-- Nama -->
                <div class="col-12">
                    <label class="form-label">Nama Lengkap</label>
                    <div class="input-group">
                        <span class="input-group-text" style="border-color:#E2E8F0;background:#F8FAFC;border-radius:8px 0 0 8px;">
                            <i class="fas fa-id-card text-muted" style="font-size:13px;"></i>
                        </span>
                        <input type="text" name="nama" class="form-control"
                               value="<?= e($_POST['nama'] ?? ''); ?>"
                               placeholder="Nama lengkap" required
                               style="border-radius:0 8px 8px 0;">
                        <div class="invalid-feedback">Nama wajib diisi.</div>
                    </div>
                </div>

                <!-- Username -->
                <div class="col-md-6">
                    <label class="form-label">Username</label>
                    <input type="text" name="username" class="form-control"
                           value="<?= e($_POST['username'] ?? ''); ?>"
                           placeholder="username_unik" required>
                    <div class="invalid-feedback">Username wajib diisi.</div>
                </div>

                <!-- Email -->
                <div class="col-md-6">
                    <label class="form-label">Email <small class="text-muted">(opsional)</small></label>
                    <input type="email" name="email" class="form-control"
                           value="<?= e($_POST['email'] ?? ''); ?>"
                           placeholder="email@contoh.com">
                </div>

                <!-- Role -->
                <div class="col-12">
                    <label class="form-label">Hak Akses (Role)</label>
                    <select name="role" class="form-select" required>
                        <option value="Petugas" <?= ($_POST['role'] ?? '') === 'Petugas' ? 'selected' : ''; ?>>Petugas</option>
                        <option value="Admin"   <?= ($_POST['role'] ?? '') === 'Admin'   ? 'selected' : ''; ?>>Admin</option>
                    </select>
                </div>

                <!-- Password -->
                <div class="col-md-6">
                    <label class="form-label">Password</label>
                    <div class="input-group">
                        <input type="password" name="password" id="pw1" class="form-control"
                               placeholder="Min. 8 karakter" required minlength="8"
                               style="border-radius:8px 0 0 8px;">
                        <button type="button" class="input-group-text" onclick="togglePwd('pw1','ico1')" style="cursor:pointer;border-radius:0 8px 8px 0;">
                            <i class="fas fa-eye text-muted" id="ico1" style="font-size:12px;"></i>
                        </button>
                        <div class="invalid-feedback">Password minimal 8 karakter.</div>
                    </div>
                </div>

                <!-- Konfirmasi Password -->
                <div class="col-md-6">
                    <label class="form-label">Konfirmasi Password</label>
                    <input type="password" name="konfirmasi_password" id="pw2"
                           class="form-control" placeholder="Ulangi password" required
                           style="border-radius:8px;">
                    <div class="invalid-feedback">Konfirmasi password wajib diisi.</div>
                </div>
            </div>

            <button type="submit" class="btn btn-success w-100 py-2 mt-4" style="border-radius:10px;font-weight:700;">
                <i class="fas fa-user-check me-2"></i>Daftarkan Akun
            </button>
        </form>

        <div class="text-center mt-4" style="font-size:13px;color:#64748B;">
            Sudah punya akun?
            <a href="login.php" style="color:#2563EB;font-weight:600;">Login di sini</a>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script src="../assets/js/script.js"></script>
    <script>
    function togglePwd(id, iconId) {
        const el = document.getElementById(id);
        const ic = document.getElementById(iconId);
        if (!el) return;
        el.type = el.type === 'password' ? 'text' : 'password';
        ic.className = el.type === 'password' ? 'fas fa-eye text-muted' : 'fas fa-eye-slash text-muted';
        ic.style.fontSize = '12px';
    }
    </script>
</body>
</html>
