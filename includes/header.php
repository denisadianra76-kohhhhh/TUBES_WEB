<?php
/**
 * FILE: includes/header.php
 * DESKRIPSI: Template HTML head — dipanggil di awal setiap halaman.
 * Variabel yang wajib diset sebelum include:
 *   $page_title  (string) — judul halaman
 *   $base_path   (string) — path relatif ke root (misal: '../' atau '')
 */

// Tentukan path dasar jika belum diset
if (!isset($base_path)) $base_path = '';
if (!isset($page_title)) $page_title = 'Aplikasi Pendataan Pelanggan';
?>
<!DOCTYPE html>
<html lang="id" data-theme="light">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="Aplikasi Pendataan Pelanggan dan Transaksi Berbasis Web">
    <meta name="author" content="Senior Full Stack Web Developer">
    <title><?= e($page_title); ?> — Pendataan Pelanggan</title>

    <!-- Favicon -->
    <link rel="icon" type="image/svg+xml" href="<?= $base_path; ?>assets/images/favicon.svg">

    <!-- Bootstrap 5.3 CSS -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css">

    <!-- Font Awesome 6 -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">

    <!-- Google Fonts: Inter -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap">

    <!-- Custom CSS -->
    <link rel="stylesheet" href="<?= $base_path; ?>assets/css/style.css">

    <!-- Inline: Set theme sebelum render untuk mencegah flash -->
    <script>
        (function() {
            const t = localStorage.getItem('theme') || 'light';
            document.documentElement.setAttribute('data-theme', t);
        })();
    </script>
</head>
<body>
<!-- Loading Screen -->
<div id="loading-screen">
    <div class="spinner"></div>
    <p style="margin-top:16px;font-size:13px;color:#64748B;font-family:Inter,sans-serif;">Memuat Halaman...</p>
</div>

<!-- Backdrop untuk sidebar mobile -->
<div id="sidebar-backdrop"></div>
