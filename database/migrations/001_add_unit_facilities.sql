-- Migrasi additive untuk database lama. Jalankan sekali hanya jika kolom belum ada.
-- Menambahkan nilai kosong untuk unit lama; kode, harga, status, dan relasi tidak berubah.
ALTER TABLE unit_kost
  ADD COLUMN fasilitas VARCHAR(1000) NOT NULL DEFAULT '' AFTER harga_sewa;
