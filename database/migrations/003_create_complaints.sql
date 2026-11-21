-- Tabel baru untuk pengaduan penghuni; tidak mengubah tabel/data yang sudah ada.
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
