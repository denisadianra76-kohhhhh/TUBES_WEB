<?php
/**
 * FILE: auth/login.php
 * DESKRIPSI: Halaman Login pengguna.
 * Fitur:
 *   - Validasi client-side & server-side
 *   - Remember Me via cookie (30 hari)
 *   - CSRF protection
 *   - Flash messages dari session
 *   - Auto-redirect jika sudah login
 */

require_once __DIR__ . '/../config/koneksi.php';

// Sudah login? langsung ke dashboard
if (isset($_SESSION['id_user'])) {
    header('Location: ../dashboard.php'); exit;
}

// Coba auto-login via Cookie Remember Me
if (check_remember_me_cookie()) {
    header('Location: ../dashboard.php'); exit;
}

// ===========================================================
// PROSES FORM LOGIN (POST)
// ===========================================================
$login_error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    // 1. Verifikasi CSRF
    if (!verify_csrf()) {
        $login_error = 'Token keamanan tidak valid. Muat ulang halaman dan coba lagi.';
    } else {
        $username    = trim($_POST['username'] ?? '');
        $password    = $_POST['password'] ?? '';
        $remember_me = !empty($_POST['remember_me']);

        if (empty($username) || empty($password)) {
            $login_error = 'Username dan password wajib diisi.';
        } else {
            // 2. Cari user di database (Prepared Statement)
            $sql  = "SELECT * FROM `user` WHERE username = ? LIMIT 1";
            $stmt = mysqli_prepare($koneksi, $sql);
            mysqli_stmt_bind_param($stmt, 's', $username);
            mysqli_stmt_execute($stmt);
            $user = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));
            mysqli_stmt_close($stmt);

            if ($user && password_verify($password, $user['password'])) {
                // 3. Login berhasil — set session
                session_regenerate_id(true);
                $_SESSION['id_user']  = $user['id_user'];
                $_SESSION['nama']     = $user['nama'];
                $_SESSION['username'] = $user['username'];
                $_SESSION['role']     = $user['role'];

                // 4. Handle "Ingat Saya" Cookie
                if ($remember_me) {
                    $raw_token    = bin2hex(random_bytes(32));
                    $hashed_token = password_hash($raw_token, PASSWORD_BCRYPT);

                    $upd = "UPDATE `user` SET remember_token = ? WHERE id_user = ?";
                    $s2  = mysqli_prepare($koneksi, $upd);
                    mysqli_stmt_bind_param($s2, 'si', $hashed_token, $user['id_user']);
                    mysqli_stmt_execute($s2);
                    mysqli_stmt_close($s2);

                    $expire = time() + (86400 * 30); // 30 hari
                    setcookie('rem_uid',   $user['id_user'], $expire, '/', '', false, true);
                    setcookie('rem_token', $raw_token,       $expire, '/', '', false, true);
                }

                set_flash('success', 'Selamat datang, ' . $user['nama'] . '! 👋');
                header('Location: ../dashboard.php');
                exit;
            } else {
                $login_error = 'Username atau password salah. Coba lagi.';
            }
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
    <title>Login — Pendataan Pelanggan</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link rel="stylesheet" href="../assets/css/style.css">
    <script>(function(){const t=localStorage.getItem('theme')||'light';document.documentElement.setAttribute('data-theme',t);})();</script>
</head>
<body class="auth-page">
    
    <!-- Floating Glass Orbs -->
    <div class="orb orb-1"></div>
    <div class="orb orb-2"></div>
    <div class="orb orb-3"></div>


    <div class="auth-box">
        <!-- Logo -->
        <div class="auth-logo">
            <i class="fas fa-cubes"></i>
        </div>
        <h2 class="auth-title">Selamat Datang</h2>
        <p class="auth-sub">Masuk ke Aplikasi Pendataan Pelanggan</p>

        <!-- Alert Error -->
        <?php if ($login_error): ?>
        <div class="alert alert-danger d-flex align-items-center gap-2 py-2 mb-4" style="border-radius:10px;font-size:13px;">
            <i class="fas fa-exclamation-circle"></i>
            <?= e($login_error); ?>
        </div>
        <?php endif; ?>

        <!-- Alert Success (dari redirect) -->
        <?php $fs = get_flash('success'); if ($fs): ?>
        <div class="alert alert-success py-2 mb-4" style="border-radius:10px;font-size:13px;">
            <?= e($fs); ?>
        </div>
        <?php endif; ?>

        <!-- Form Login -->
        <form action="login.php" method="POST" class="needs-validation" novalidate>
            <input type="hidden" name="_csrf_token" value="<?= e($csrf); ?>">

            <!-- Username -->
            <div class="mb-3">
                <label for="username" class="form-label">Username</label>
                <div class="input-group">
                    <span class="input-group-text" style="border-radius:8px 0 0 8px;border-color:#E2E8F0;background:#F8FAFC;">
                        <i class="fas fa-user text-muted" style="font-size:13px;"></i>
                    </span>
                    <input type="text" id="username" name="username"
                           class="form-control" placeholder="Masukkan username"
                           value="<?= e($_POST['username'] ?? ''); ?>"
                           autocomplete="username" required
                           style="border-radius:0 8px 8px 0;">
                    <div class="invalid-feedback">Username wajib diisi.</div>
                </div>
            </div>

            <!-- Password -->
            <div class="mb-3">
                <div class="d-flex justify-content-between">
                    <label for="password" class="form-label">Password</label>
                </div>
                <div class="input-group">
                    <span class="input-group-text" style="border-radius:8px 0 0 8px;border-color:#E2E8F0;background:#F8FAFC;">
                        <i class="fas fa-lock text-muted" style="font-size:13px;"></i>
                    </span>
                    <input type="password" id="password" name="password"
                           class="form-control" placeholder="Masukkan password"
                           autocomplete="current-password" required
                           style="border-radius:0 8px 8px 0;">
                    <button type="button" class="input-group-text"
                            onclick="togglePwd('password','eye-icon-1')"
                            style="border-radius:0 8px 8px 0;cursor:pointer;border-left:none;">
                        <i class="fas fa-eye text-muted" id="eye-icon-1" style="font-size:13px;"></i>
                    </button>
                    <div class="invalid-feedback">Password wajib diisi.</div>
                </div>
            </div>

            <!-- Remember Me -->
            <div class="mb-4 d-flex justify-content-between align-items-center">
                <div class="form-check">
                    <input class="form-check-input" type="checkbox" id="remember_me" name="remember_me" value="1">
                    <label class="form-check-label" for="remember_me" style="font-size:13px;">Ingat Saya</label>
                </div>
            </div>

            <!-- Submit -->
            <button type="submit" class="btn btn-primary w-100 py-2" style="border-radius:10px;font-size:14px;font-weight:700;">
                <i class="fas fa-sign-in-alt me-2"></i>Masuk Sekarang
            </button>
        </form>

        <!-- Link Register -->
        <div class="text-center mt-4" style="font-size:13px;color:#64748B;">
            Belum punya akun?
            <a href="register.php" style="color:#2563EB;font-weight:600;">Daftar di sini</a>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script>
    function togglePwd(id, iconId) {
        const i = document.getElementById(id);
        const ic = document.getElementById(iconId);
        if (!i) return;
        if (i.type === 'password') {
            i.type = 'text';
            ic.className = 'fas fa-eye-slash text-muted';
        } else {
            i.type = 'password';
            ic.className = 'fas fa-eye text-muted';
        }
    }
    </script>
</body>
</html>
