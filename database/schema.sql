-- Bootstrap untuk database baru. Tidak menghapus atau mengubah baris lama.
-- Untuk database yang telah ada, gunakan migration di database/migrations/.
CREATE DATABASE IF NOT EXISTS kost_pak_sapriadi
  CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE kost_pak_sapriadi;

CREATE TABLE IF NOT EXISTS unit_kost (
  id INT(11) NOT NULL AUTO_INCREMENT,
  kode_pintu VARCHAR(20) NOT NULL,
  nama_pintu VARCHAR(100) NOT NULL,
  status ENUM('Tersedia', 'Terisi') NOT NULL DEFAULT 'Tersedia',
  harga_sewa DECIMAL(12,2) NOT NULL,
  fasilitas VARCHAR(1000) NOT NULL DEFAULT '',
  created_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  UNIQUE KEY kode_pintu (kode_pintu),
  KEY idx_unit_kost_status (status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS penghuni_kost (
  id INT(11) NOT NULL AUTO_INCREMENT,
  nama_lengkap VARCHAR(100) NOT NULL,
  no_hp VARCHAR(20) NOT NULL,
  alamat TEXT NULL,
  created_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS transaksi_sewa (
  id INT(11) NOT NULL AUTO_INCREMENT,
  penghuni_id INT(11) NOT NULL,
  unit_id INT(11) NOT NULL,
  tanggal_mulai DATE NOT NULL,
  tanggal_selesai DATE NOT NULL,
  harga_sewa DECIMAL(12,2) NOT NULL,
  status ENUM('Menunggu', 'Aktif', 'Selesai', 'Dibatalkan') NOT NULL DEFAULT 'Menunggu',
  created_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  KEY fk_sewa_penghuni (penghuni_id),
  KEY fk_sewa_unit (unit_id),
  CONSTRAINT fk_sewa_penghuni FOREIGN KEY (penghuni_id) REFERENCES penghuni_kost (id)
    ON UPDATE CASCADE ON DELETE RESTRICT,
  CONSTRAINT fk_sewa_unit FOREIGN KEY (unit_id) REFERENCES unit_kost (id)
    ON UPDATE CASCADE ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS pembayaran_kost (
  id INT(11) NOT NULL AUTO_INCREMENT,
  penghuni_id INT(11) NOT NULL,
  jumlah_bayar DECIMAL(12,2) NOT NULL,
  tanggal_bayar DATE NOT NULL,
  status ENUM('Lunas', 'Belum Lunas') NOT NULL DEFAULT 'Belum Lunas',
  keterangan VARCHAR(255) NULL,
  created_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  KEY fk_pembayaran_penghuni (penghuni_id),
  KEY idx_pembayaran_status (status),
  KEY idx_pembayaran_tanggal (tanggal_bayar),
  CONSTRAINT fk_pembayaran_penghuni FOREIGN KEY (penghuni_id) REFERENCES penghuni_kost (id)
    ON UPDATE CASCADE ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS pengguna (
  id INT(11) NOT NULL AUTO_INCREMENT,
  nama VARCHAR(100) NOT NULL,
  email VARCHAR(190) NOT NULL,
  password_hash VARCHAR(255) NOT NULL,
  role ENUM('penghuni', 'petugas', 'admin') NOT NULL DEFAULT 'penghuni',
  penghuni_id INT(11) NULL DEFAULT NULL,
  aktif TINYINT(1) NOT NULL DEFAULT 1,
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  UNIQUE KEY uq_pengguna_email (email),
  UNIQUE KEY uq_pengguna_penghuni (penghuni_id),
  CONSTRAINT fk_pengguna_penghuni FOREIGN KEY (penghuni_id) REFERENCES penghuni_kost (id)
    ON UPDATE CASCADE ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS inventaris_kost (
  id INT(11) NOT NULL AUTO_INCREMENT,
  nama VARCHAR(120) NOT NULL,
  kategori VARCHAR(80) NOT NULL,
  jumlah INT(10) UNSIGNED NOT NULL DEFAULT 0,
  kondisi ENUM('Baik', 'Perlu Cek') NOT NULL DEFAULT 'Baik',
  lokasi VARCHAR(120) NOT NULL DEFAULT '',
  updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  UNIQUE KEY uq_inventaris_nama (nama),
  KEY idx_inventaris_kondisi (kondisi)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS pengaduan_kost (
  id INT(11) NOT NULL AUTO_INCREMENT,
  penghuni_id INT(11) NOT NULL,
  judul VARCHAR(150) NOT NULL,
  deskripsi TEXT NOT NULL,
  status ENUM('Baru','Diproses','Selesai') NOT NULL DEFAULT 'Baru',
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  KEY idx_pengaduan_penghuni (penghuni_id),
  KEY idx_pengaduan_status (status),
  CONSTRAINT fk_pengaduan_penghuni FOREIGN KEY (penghuni_id) REFERENCES penghuni_kost (id)
    ON UPDATE CASCADE ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Memigrasikan inventaris yang sudah tercatat di frontend lama; aman dijalankan ulang.
INSERT INTO inventaris_kost (nama,kategori,jumlah,kondisi,lokasi)
SELECT 'Router Wi-Fi Utama','Fasilitas',1,'Baik','Lantai 1'
WHERE NOT EXISTS (SELECT 1 FROM inventaris_kost WHERE nama='Router Wi-Fi Utama');
INSERT INTO inventaris_kost (nama,kategori,jumlah,kondisi,lokasi)
SELECT 'Profil Air / Tandon','Fasilitas',3,'Baik','Samping'
WHERE NOT EXISTS (SELECT 1 FROM inventaris_kost WHERE nama='Profil Air / Tandon');
INSERT INTO inventaris_kost (nama,kategori,jumlah,kondisi,lokasi)
SELECT 'Kasur Springbed','Mebel',6,'Baik','Kamar'
WHERE NOT EXISTS (SELECT 1 FROM inventaris_kost WHERE nama='Kasur Springbed');
INSERT INTO inventaris_kost (nama,kategori,jumlah,kondisi,lokasi)
SELECT 'Lemari Pakaian','Mebel',3,'Baik','Kamar'
WHERE NOT EXISTS (SELECT 1 FROM inventaris_kost WHERE nama='Lemari Pakaian');
INSERT INTO inventaris_kost (nama,kategori,jumlah,kondisi,lokasi)
SELECT 'Lampu Teras','Fasilitas',3,'Perlu Cek','Teras'
WHERE NOT EXISTS (SELECT 1 FROM inventaris_kost WHERE nama='Lampu Teras');
