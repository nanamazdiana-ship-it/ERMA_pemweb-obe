# Instalasi lokal

1. Jalankan Apache dan MySQL melalui Laragon. Gunakan PHP 8.4+ dengan ekstensi `pdo_mysql` dan `mbstring`.
2. Buat database baru lalu impor `database/schema.sql`. Untuk database proyek lama, ikuti urutan migrasi di [README utama](../README.md) dan jangan jalankan migrasi pertama jika kolom fasilitas sudah ada.
3. Salin `.env.example` menjadi `.env`, kemudian isi koneksi database lokal.
4. Jalankan `composer install` bila `vendor/` belum tersedia.
5. Buat/provision akun admin melalui proses lokal yang dipercaya; tool pembuat akun sengaja tidak disimpan di repository.
6. Buka direktori proyek melalui Apache, misalnya `http://localhost/pemweb-obe/`.

Jalankan smoke test API dengan akun admin khusus pengujian melalui `KOST_TEST_EMAIL`, `KOST_TEST_PASSWORD`, dan bila perlu `KOST_TEST_BASE_URL`, lalu jalankan `php tests/api/api-smoke.php`.
