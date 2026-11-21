-- Membuat tabel baru saja. Data unit, penghuni, sewa, dan pembayaran tidak disentuh.
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

-- Pindahkan persis data inventaris yang sebelumnya tertanam di frontend.
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
