<?php
/**
 * FILE: transaksi/hapus.php
 * DESKRIPSI: Handler hapus transaksi. Hanya Admin yang bisa akses.
 */
require_once '../config/koneksi.php';
proteksi_halaman();

if ($_SESSION['role'] !== 'Admin') {
    set_flash('error', 'Hak akses ditolak. Hanya Admin yang dapat menghapus data.');
    header('Location: index.php'); exit;
}

$id = (int)($_GET['id'] ?? 0);
if (!$id) { set_flash('error','ID tidak valid.'); header('Location: index.php'); exit; }

// Cek data untuk notifikasi
$s = mysqli_prepare($koneksi, "SELECT kode_transaksi FROM transaksi WHERE id_transaksi = ? LIMIT 1");
mysqli_stmt_bind_param($s, 'i', $id);
mysqli_stmt_execute($s);
$row = mysqli_fetch_assoc(mysqli_stmt_get_result($s));
mysqli_stmt_close($s);

if (!$row) { set_flash('error','Data tidak ditemukan.'); header('Location: index.php'); exit; }

$stmt = mysqli_prepare($koneksi, "DELETE FROM transaksi WHERE id_transaksi = ?");
mysqli_stmt_bind_param($stmt, 'i', $id);

if (mysqli_stmt_execute($stmt)) {
    set_flash('success', "Transaksi <strong>{$row['kode_transaksi']}</strong> berhasil dihapus.");
} else {
    set_flash('error', 'Gagal menghapus: ' . mysqli_error($koneksi));
}
mysqli_stmt_close($stmt);
header('Location: index.php'); exit;
