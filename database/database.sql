-- ============================================================
-- FILE: database/database.sql
-- DESKRIPSI: File SQL lengkap untuk database Aplikasi Pendataan
--            Pelanggan dan Transaksi Berbasis Web.
-- CARA IMPORT: Buka phpMyAdmin -> Import -> Pilih file ini -> Go
-- ============================================================

-- Buat database jika belum ada
CREATE DATABASE IF NOT EXISTS `db_pendataan_pelanggan`
  DEFAULT CHARACTER SET utf8mb4
  DEFAULT COLLATE utf8mb4_unicode_ci;

-- Gunakan database ini
USE `db_pendataan_pelanggan`;

-- ============================================================
-- TABEL 1: user
-- Menyimpan data pengguna sistem (Admin & Petugas)
-- ============================================================
CREATE TABLE IF NOT EXISTS `user` (
  `id_user`        INT(11)      NOT NULL AUTO_INCREMENT,
  `nama`           VARCHAR(100) NOT NULL,
  `username`       VARCHAR(50)  NOT NULL,
  `email`          VARCHAR(100) NOT NULL DEFAULT '',
  `password`       VARCHAR(255) NOT NULL,
  `role`           ENUM('Admin','Petugas') NOT NULL DEFAULT 'Petugas',
  `remember_token` VARCHAR(255) DEFAULT NULL,
  `created_at`     TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id_user`),
  UNIQUE KEY `uk_username` (`username`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='Data pengguna sistem';

-- ============================================================
-- TABEL 2: pelanggan
-- Menyimpan informasi lengkap profil pelanggan
-- ============================================================
CREATE TABLE IF NOT EXISTS `pelanggan` (
  `id_pelanggan`   INT(11)      NOT NULL AUTO_INCREMENT,
  `kode_pelanggan` VARCHAR(20)  NOT NULL,
  `nama`           VARCHAR(100) NOT NULL,
  `alamat`         TEXT         NOT NULL,
  `telepon`        VARCHAR(20)  NOT NULL,
  `email`          VARCHAR(100) NOT NULL DEFAULT '',
  `jenis_kelamin`  ENUM('L','P') NOT NULL,
  `tanggal_daftar` DATE         NOT NULL,
  `status`         ENUM('Aktif','Nonaktif') NOT NULL DEFAULT 'Aktif',
  `created_at`     TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id_pelanggan`),
  UNIQUE KEY `uk_kode_pelanggan` (`kode_pelanggan`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='Data pelanggan';

-- ============================================================
-- TABEL 3: transaksi
-- Menyimpan histori transaksi keuangan pelanggan
-- ============================================================
CREATE TABLE IF NOT EXISTS `transaksi` (
  `id_transaksi`   INT(11)      NOT NULL AUTO_INCREMENT,
  `kode_transaksi` VARCHAR(20)  NOT NULL,
  `id_pelanggan`   INT(11)      NOT NULL,
  `tanggal`        DATE         NOT NULL,
  `jenis_transaksi` ENUM('Pemasukan','Pengeluaran') NOT NULL,
  `jumlah`         DECIMAL(15,2) NOT NULL DEFAULT 0.00,
  `keterangan`     TEXT         DEFAULT NULL,
  `status`         ENUM('Lunas','Pending','Dibatalkan') NOT NULL DEFAULT 'Lunas',
  `created_at`     TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id_transaksi`),
  UNIQUE KEY `uk_kode_transaksi` (`kode_transaksi`),
  CONSTRAINT `fk_transaksi_pelanggan`
    FOREIGN KEY (`id_pelanggan`)
    REFERENCES `pelanggan` (`id_pelanggan`)
    ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='Data transaksi keuangan';

-- ============================================================
-- DATA AWAL: Tabel user
-- Password admin123 di-hash menggunakan password_hash() PHP
-- Hash: $2y$12$ELTMqc.GEobBmjqIFO4J7eM8/T9bLbMrRtEJSUoOHmHNPXaU.gIa6
-- ============================================================
INSERT INTO `user` (`nama`, `username`, `email`, `password`, `role`) VALUES
('Administrator Utama', 'admin', 'admin@webpelanggan.id',
 '$2y$12$ELTMqc.GEobBmjqIFO4J7eM8/T9bLbMrRtEJSUoOHmHNPXaU.gIa6', 'Admin'),
('Petugas Administrasi', 'petugas', 'petugas@webpelanggan.id',
 '$2y$12$ELTMqc.GEobBmjqIFO4J7eM8/T9bLbMrRtEJSUoOHmHNPXaU.gIa6', 'Petugas')
ON DUPLICATE KEY UPDATE nama = VALUES(nama);

-- ============================================================
-- DATA AWAL: Tabel pelanggan (5 data contoh)
-- ============================================================
INSERT INTO `pelanggan`
  (`kode_pelanggan`,`nama`,`alamat`,`telepon`,`email`,`jenis_kelamin`,`tanggal_daftar`,`status`)
VALUES
('PLG-2026-0001','Ahmad Hidayat','Jl. Merdeka No. 12, Jakarta Pusat','081234567890','ahmad@mail.com','L','2026-01-10','Aktif'),
('PLG-2026-0002','Siti Rahmawati','Jl. Mawar No. 45, Bandung','085712345678','siti@mail.com','P','2026-02-15','Aktif'),
('PLG-2026-0003','Budi Santoso','Jl. Diponegoro No. 8, Surabaya','089987654321','budi@mail.com','L','2026-03-20','Aktif'),
('PLG-2026-0004','Dewi Lestari','Jl. Melati No. 10, Yogyakarta','082134567812','dewi@mail.com','P','2026-04-05','Nonaktif'),
('PLG-2026-0005','Rizal Firmansyah','Jl. Sudirman No. 99, Semarang','087645678901','rizal@mail.com','L','2026-05-18','Aktif')
ON DUPLICATE KEY UPDATE kode_pelanggan = VALUES(kode_pelanggan);

-- ============================================================
-- DATA AWAL: Tabel transaksi (8 data contoh)
-- ============================================================
INSERT INTO `transaksi`
  (`kode_transaksi`,`id_pelanggan`,`tanggal`,`jenis_transaksi`,`jumlah`,`keterangan`,`status`)
VALUES
('TRX-20260701-0001',1,'2026-07-01','Pemasukan',  1500000.00,'Pembayaran uang muka layanan web','Lunas'),
('TRX-20260701-0002',2,'2026-07-01','Pemasukan',   750000.00,'Pembelian lisensi software bulanan','Lunas'),
('TRX-20260701-0003',3,'2026-07-01','Pengeluaran', 200000.00,'Refund kelebihan pembayaran','Lunas'),
('TRX-20260702-0001',1,'2026-07-02','Pemasukan',  2000000.00,'Pelunasan jasa desain UI/UX','Lunas'),
('TRX-20260702-0002',5,'2026-07-02','Pemasukan',   500000.00,'Pembayaran hosting tahunan','Lunas'),
('TRX-20260703-0001',2,'2026-07-03','Pengeluaran', 150000.00,'Biaya pengembalian barang','Pending'),
('TRX-20260703-0002',4,'2026-07-03','Pemasukan',   350000.00,'Pembayaran layanan konsultasi','Lunas'),
('TRX-20260703-0003',3,'2026-07-03','Pemasukan',  1000000.00,'Invoice bulanan maintenance web','Lunas')
ON DUPLICATE KEY UPDATE kode_transaksi = VALUES(kode_transaksi);
