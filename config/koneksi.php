<?php
/**
 * FILE: config/koneksi.php
 * DESKRIPSI: File konfigurasi utama — menangani:
 *   1. Koneksi database MySQL via MySQLi
 *   2. Manajemen Session yang aman
 *   3. Fungsi utilitas: escape, CSRF, format rupiah, generate kode
 *   4. Auto-login via Cookie Remember Me
 *   5. Proteksi halaman terpusat
 */

// ===========================================================
// 1. KONFIGURASI DATABASE
// ===========================================================
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'db_pendataan_pelanggan');
define('DB_PORT', 3306);

// ===========================================================
// 2. PENGATURAN SESSION YANG AMAN
// ===========================================================
if (session_status() === PHP_SESSION_NONE) {
    // Konfigurasi cookie session yang aman
    session_set_cookie_params([
        'lifetime' => 86400,           // 1 hari
        'path'     => '/',
        'domain'   => '',
        'secure'   => false,           // Ganti true jika pakai HTTPS
        'httponly' => true,            // Cegah akses dari JavaScript (Anti-XSS)
        'samesite' => 'Lax'            // Cegah CSRF lintas situs
    ]);
    session_name('SESS_PENDATAAN');
    session_start();
}

// Regenerasi session ID secara berkala (Cegah Session Fixation)
if (!isset($_SESSION['_initiated'])) {
    session_regenerate_id(true);
    $_SESSION['_initiated'] = true;
    $_SESSION['_regen_time'] = time();
} elseif (time() - $_SESSION['_regen_time'] > 1800) {
    session_regenerate_id(true);
    $_SESSION['_regen_time'] = time();
}

// ===========================================================
// 3. KONEKSI KE DATABASE MYSQL
// ===========================================================
$koneksi = @mysqli_connect(DB_HOST, DB_USER, DB_PASS, DB_NAME, DB_PORT);

// Cek koneksi — jika gagal tampilkan pesan terformat
if (!$koneksi) {
    die(json_encode([
        'error' => true,
        'message' => 'Koneksi database gagal: ' . mysqli_connect_error()
    ]) ?: '<div style="font-family:sans-serif;padding:20px;background:#fee;color:#c00;border:1px solid #c00;border-radius:8px;margin:20px;">
        <h3>⚠️ Koneksi Database Gagal</h3>
        <p>' . mysqli_connect_error() . '</p>
        <p>Pastikan MySQL XAMPP sudah berjalan dan database <strong>db_pendataan_pelanggan</strong> sudah diimport.</p>
    </div>');
}

// Pastikan encoding UTF-8 untuk mendukung karakter Indonesia
mysqli_set_charset($koneksi, 'utf8mb4');

// ===========================================================
// 4. FUNGSI KEAMANAN
// ===========================================================

/**
 * Sanitasi output HTML — mencegah XSS
 */
function e(string $str): string {
    return htmlspecialchars($str, ENT_QUOTES | ENT_HTML5, 'UTF-8');
}

/**
 * Alias fungsi escape (kompatibilitas dengan kode lama)
 */
function escape(string $str): string {
    return e($str);
}

/**
 * Generate token CSRF dan simpan ke session
 */
function csrf_token(): string {
    if (empty($_SESSION['_csrf_token'])) {
        $_SESSION['_csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['_csrf_token'];
}

/**
 * Hidden input CSRF untuk disisipkan ke dalam form
 */
function csrf_field(): string {
    return '<input type="hidden" name="_csrf_token" value="' . csrf_token() . '">';
}

/**
 * Verifikasi token CSRF dari POST request
 */
function verify_csrf(): bool {
    $token = $_POST['_csrf_token'] ?? '';
    return isset($_SESSION['_csrf_token']) && hash_equals($_SESSION['_csrf_token'], $token);
}

// ===========================================================
// 5. FUNGSI AUTO-LOGIN COOKIE REMEMBER ME
// ===========================================================

/**
 * Memeriksa Cookie Remember Me dan melakukan auto-login jika valid
 * Mengembalikan true jika berhasil auto-login
 */
function check_remember_me_cookie(): bool {
    global $koneksi;

    if (!isset($_COOKIE['rem_uid'], $_COOKIE['rem_token'])) {
        return false;
    }

    $uid   = (int) $_COOKIE['rem_uid'];
    $token = $_COOKIE['rem_token'];

    $sql  = "SELECT * FROM `user` WHERE id_user = ? LIMIT 1";
    $stmt = mysqli_prepare($koneksi, $sql);
    if (!$stmt) return false;

    mysqli_stmt_bind_param($stmt, 'i', $uid);
    mysqli_stmt_execute($stmt);
    $row = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));
    mysqli_stmt_close($stmt);

    if ($row && $row['remember_token'] && password_verify($token, $row['remember_token'])) {
        // Set session seperti login biasa
        $_SESSION['id_user']  = $row['id_user'];
        $_SESSION['nama']     = $row['nama'];
        $_SESSION['username'] = $row['username'];
        $_SESSION['role']     = $row['role'];
        session_regenerate_id(true);
        return true;
    }

    return false;
}

// ===========================================================
// 6. PROTEKSI HALAMAN (Cek Login + Auto-Login Cookie)
// ===========================================================

/**
 * Paksa halaman hanya bisa diakses oleh user yang sudah login.
 * Mencoba auto-login lewat cookie sebelum redirect ke halaman login.
 */
function proteksi_halaman(): void {
    if (!isset($_SESSION['id_user'])) {
        if (!check_remember_me_cookie()) {
            $_SESSION['_flash_error'] = 'Silakan login terlebih dahulu.';
            $redirect = '/TUBES_WEB/auth/login.php';
            header("Location: $redirect");
            exit;
        }
    }
}

/**
 * Proteksi khusus untuk role Admin saja
 */
function proteksi_admin(): void {
    proteksi_halaman();
    if ($_SESSION['role'] !== 'Admin') {
        $_SESSION['_flash_error'] = 'Halaman ini hanya bisa diakses oleh Admin.';
        header("Location: /TUBES_WEB/dashboard.php");
        exit;
    }
}

// ===========================================================
// 7. FUNGSI FLASH MESSAGE (Session Alert)
// ===========================================================

function set_flash(string $type, string $message): void {
    $_SESSION['_flash_' . $type] = $message;
}

function get_flash(string $type): string {
    $msg = $_SESSION['_flash_' . $type] ?? '';
    unset($_SESSION['_flash_' . $type]);
    return $msg;
}

function has_flash(string $type): bool {
    return !empty($_SESSION['_flash_' . $type]);
}

// ===========================================================
// 8. FUNGSI FORMAT & GENERATOR
// ===========================================================

/**
 * Format angka menjadi format Rupiah Indonesia
 */
function rupiah(float $angka): string {
    return 'Rp ' . number_format($angka, 0, ',', '.');
}

/**
 * Generate kode pelanggan otomatis: PLG-YYYY-XXXX
 */
function generate_kode_pelanggan(): string {
    global $koneksi;
    $tahun = date('Y');
    $like  = 'PLG-' . $tahun . '-%';
    $sql   = "SELECT MAX(CAST(SUBSTRING(kode_pelanggan, 10) AS UNSIGNED)) AS maxn
              FROM pelanggan WHERE kode_pelanggan LIKE ?";
    $stmt  = mysqli_prepare($koneksi, $sql);
    mysqli_stmt_bind_param($stmt, 's', $like);
    mysqli_stmt_execute($stmt);
    $row   = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));
    mysqli_stmt_close($stmt);
    $next  = ((int)($row['maxn'] ?? 0)) + 1;
    return 'PLG-' . $tahun . '-' . str_pad($next, 4, '0', STR_PAD_LEFT);
}

/**
 * Generate kode transaksi otomatis: TRX-YYYYMMDD-XXXX
 */
function generate_kode_transaksi(): string {
    global $koneksi;
    $tgl  = date('Ymd');
    $like = 'TRX-' . $tgl . '-%';
    $sql  = "SELECT MAX(CAST(SUBSTRING(kode_transaksi, 14) AS UNSIGNED)) AS maxn
             FROM transaksi WHERE kode_transaksi LIKE ?";
    $stmt = mysqli_prepare($koneksi, $sql);
    mysqli_stmt_bind_param($stmt, 's', $like);
    mysqli_stmt_execute($stmt);
    $row  = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));
    mysqli_stmt_close($stmt);
    $next = ((int)($row['maxn'] ?? 0)) + 1;
    return 'TRX-' . $tgl . '-' . str_pad($next, 4, '0', STR_PAD_LEFT);
}

/**
 * Ambil total baris dari sebuah tabel
 */
function count_table(string $table): int {
    global $koneksi;
    $table  = preg_replace('/[^a-z_]/i', '', $table); // Whitelist nama tabel
    $result = mysqli_query($koneksi, "SELECT COUNT(*) AS c FROM `$table`");
    $row    = mysqli_fetch_assoc($result);
    return (int)($row['c'] ?? 0);
}
