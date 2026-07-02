<?php
/**
 * FILE: auth/logout.php
 * DESKRIPSI: Handler Logout — menghapus semua session & cookie Remember Me.
 */
require_once __DIR__ . '/../config/koneksi.php';

// Hapus token remember_token dari database jika ada
if (!empty($_SESSION['id_user'])) {
    $upd  = "UPDATE `user` SET remember_token = NULL WHERE id_user = ?";
    $stmt = mysqli_prepare($koneksi, $upd);
    mysqli_stmt_bind_param($stmt, 'i', $_SESSION['id_user']);
    mysqli_stmt_execute($stmt);
    mysqli_stmt_close($stmt);
}

// Hapus seluruh data session
$_SESSION = [];

// Hapus session cookie dari browser
if (ini_get('session.use_cookies')) {
    $params = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000,
        $params['path'], $params['domain'],
        $params['secure'], $params['httponly']
    );
}
session_destroy();

// Hapus Cookie Remember Me
setcookie('rem_uid',   '', time() - 3600, '/', '', false, true);
setcookie('rem_token', '', time() - 3600, '/', '', false, true);

// Redirect ke halaman login
header('Location: login.php?logout=1');
exit;
