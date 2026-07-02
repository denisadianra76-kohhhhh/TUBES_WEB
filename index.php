<?php
/**
 * FILE: index.php (ROOT)
 * DESKRIPSI: Gerbang utama aplikasi dengan fitur AUTO DATABASE INSTALLER.
 *
 * Alur:
 * 1. Cek apakah database sudah ada (tanpa error jika belum ada).
 * 2. Jika belum ada: jalankan installer otomatis dari file database.sql.
 * 3. Jika sudah ada: redirect ke dashboard / login sesuai status session.
 * 4. File install.lock dibuat setelah instalasi selesai (keamanan).
 */

// ===========================================================
// KONFIGURASI DATABASE (tanpa include koneksi.php dulu,
// karena DB belum tentu ada saat pertama kali diakses)
// ===========================================================
$db_host = 'localhost';
$db_user = 'root';
$db_pass = '';
$db_name = 'db_pendataan_pelanggan';
$sql_file = __DIR__ . '/database/database.sql';
$lock_file = __DIR__ . '/database/install.lock';

// Koneksi ke MySQL SERVER (tanpa memilih DB dulu)
$link = @mysqli_connect($db_host, $db_user, $db_pass);

// Jika tidak bisa konek ke MySQL server sama sekali
if (!$link) {
    showInstallPage('error',
        '⚠️ Koneksi ke MySQL Gagal',
        'Tidak dapat terhubung ke server MySQL. Pastikan XAMPP (MySQL) sudah dijalankan.',
        mysqli_connect_error()
    );
    exit;
}

// ===========================================================
// CEK APAKAH DATABASE SUDAH ADA
// ===========================================================
$db_exists = false;
$res = mysqli_query($link, "SHOW DATABASES LIKE '$db_name'");
if ($res && mysqli_num_rows($res) > 0) {
    $db_exists = true;
}

// ===========================================================
// KASUS A: DATABASE SUDAH ADA → redirect ke login/dashboard
// ===========================================================
if ($db_exists) {
    mysqli_close($link);

    // Load sesi untuk cek login
    session_name('SESS_PENDATAAN');
    if (session_status() === PHP_SESSION_NONE) session_start();

    if (isset($_SESSION['id_user'])) {
        header('Location: dashboard.php');
    } else {
        header('Location: auth/login.php');
    }
    exit;
}

// ===========================================================
// KASUS B: DATABASE BELUM ADA → JALANKAN INSTALLER
// ===========================================================

// Baca file SQL
if (!file_exists($sql_file)) {
    showInstallPage('error',
        '⚠️ File SQL Tidak Ditemukan',
        'File <code>database/database.sql</code> tidak ditemukan.',
        'Pastikan file SQL ada di folder database/'
    );
    exit;
}

$sql_content = file_get_contents($sql_file);
if (empty($sql_content)) {
    showInstallPage('error', '⚠️ File SQL Kosong', 'File database.sql tidak memiliki konten.', '');
    exit;
}

// Jalankan query SQL satu per satu
$errors = [];
$success_count = 0;

// Split berdasarkan tanda titik-koma
$queries = array_filter(
    array_map('trim', explode(';', $sql_content)),
    fn($q) => !empty($q) && !preg_match('/^--/', $q)
);

foreach ($queries as $query) {
    if (empty(trim($query))) continue;
    if (!mysqli_query($link, $query)) {
        $errors[] = mysqli_error($link) . " | Query: " . substr($query, 0, 80);
    } else {
        $success_count++;
    }
}

mysqli_close($link);

// Buat file lock agar installer tidak berjalan lagi
file_put_contents($lock_file, date('Y-m-d H:i:s') . ' - Instalasi selesai.');

// ===========================================================
// TAMPILKAN HASIL INSTALASI
// ===========================================================
if (!empty($errors)) {
    showInstallPage('partial',
        '⚠️ Instalasi Sebagian Berhasil',
        "$success_count query berhasil dijalankan. Namun ada " . count($errors) . " error:",
        implode('<br>', array_map('htmlspecialchars', $errors))
    );
} else {
    showInstallPage('success',
        '✅ Instalasi Database Berhasil!',
        "Database <strong>$db_name</strong> berhasil dibuat dengan $success_count query.",
        null,
        true // tampilkan tombol mulai
    );
}

// ===========================================================
// FUNGSI TAMPILAN HALAMAN INSTALLER
// ===========================================================
function showInstallPage(string $type, string $title, string $message, ?string $detail, bool $show_btn = false): void {
    $colors = [
        'success' => ['#16A34A', '#DCFCE7', '#15803D'],
        'error'   => ['#DC2626', '#FEE2E2', '#B91C1C'],
        'partial' => ['#D97706', '#FEF9C3', '#B45309'],
    ];
    [$main, $bg, $text] = $colors[$type] ?? $colors['error'];
    ?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Installer — Aplikasi Pendataan Pelanggan</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <style>
        body { background: linear-gradient(135deg, #1E3A8A, #1E40AF); min-height: 100vh; display: flex; align-items: center; justify-content: center; font-family: 'Segoe UI', sans-serif; }
        .install-box { background: #fff; border-radius: 20px; padding: 40px; max-width: 520px; width: 100%; box-shadow: 0 25px 60px rgba(0,0,0,.25); }
        .icon-circle { width: 70px; height: 70px; background: <?= $bg; ?>; border-radius: 50%; display: flex; align-items: center; justify-content: center; margin: 0 auto 20px; font-size: 28px; color: <?= $main; ?>; }
        pre { background: #F1F5F9; border-radius: 8px; padding: 12px; font-size: 12px; max-height: 200px; overflow-y: auto; }
    </style>
</head>
<body>
    <div class="install-box text-center">
        <div class="icon-circle">
            <i class="fas fa-<?= $type === 'success' ? 'check' : 'exclamation-triangle'; ?>"></i>
        </div>
        <h4 class="fw-bold mb-2"><?= $title; ?></h4>
        <p class="text-muted"><?= $message; ?></p>
        <?php if ($detail): ?>
            <pre class="text-start mt-3"><code><?= $detail; ?></code></pre>
        <?php endif; ?>
        <?php if ($show_btn): ?>
            <div class="mt-4">
                <a href="auth/login.php" class="btn btn-primary btn-lg px-5 rounded-pill">
                    <i class="fas fa-sign-in-alt me-2"></i>Mulai Aplikasi
                </a>
                <p class="mt-3 text-muted" style="font-size:12px;">
                    Login dengan: <strong>admin</strong> / <strong>admin123</strong>
                </p>
            </div>
        <?php endif; ?>
    </div>
</body>
</html>
<?php
}
