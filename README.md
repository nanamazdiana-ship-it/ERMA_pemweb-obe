# Kost Pak Sapriadi

Website informasi dan pengelolaan kost berbasis HTML, CSS, JavaScript ES modules, PHP 8.4+, PDO, dan MySQL/MariaDB. Katalog unit, inventaris, dan pengelolaan unit membaca database; autentikasi menggunakan session cookie.

## Persiapan lokal (Laragon)

1. Jalankan Apache dan MySQL di Laragon.
2. Pastikan PHP 8.4+ aktif dengan ekstensi `pdo_mysql` dan `mbstring`.
3. **Database baru:** jalankan `database/schema.sql` sekali melalui HeidiSQL/phpMyAdmin. Skema mencakup unit, penghuni, sewa, pembayaran, pengguna, dan inventaris.
4. **Database yang sudah berisi data:** buat backup. Pastikan tabel lama `unit_kost`, `penghuni_kost`, `transaksi_sewa`, dan `pembayaran_kost` cocok dengan struktur proyek. Jalankan `database/migrations/001_add_unit_facilities.sql` hanya jika kolom `fasilitas` belum ada, lalu jalankan `002_create_auth_and_inventory.sql` dan `003_create_complaints.sql`. Migrasi menambah kolom fasilitas, membuat tabel akun/inventaris/pengaduan, dan mengimpor inventaris lama tanpa menghapus atau mengubah baris unit, penghuni, sewa, atau pembayaran.
5. Salin `.env.example` menjadi `.env`, lalu atur `DB_HOST`, `DB_PORT`, `DB_DATABASE`, `DB_USERNAME`, dan `DB_PASSWORD` sesuai MySQL lokal. Jangan commit `.env`.
6. Jalankan `composer install` jika folder `vendor/` belum ada.
7. Buat akun awal dari terminal Laragon:

   ```powershell
   php bin/create-user.php "Admin Kost" admin@example.test admin
   ```

   Masukkan password minimal 12 karakter saat diminta (prompt Windows menyembunyikan input). Buat akun `petugas` atau `penghuni` dengan mengganti role; akun penghuni harus ditautkan ke ID pada tabel `penghuni_kost`, misalnya `php bin/create-user.php "Nama Penghuni" penghuni@example.test penghuni 2`. Password disimpan sebagai hash `password_hash()`.
   Reset password akun yang lupa dengan `php bin/reset-user-password.php admin@example.test`, lalu masukkan password baru saat diminta.
8. Buka `http://localhost/pemweb-obe/`. `index.php` menangani halaman melalui parameter `?page=`.

## Hak akses

| Role | Baca unit/inventaris | Tambah/ubah unit | Hapus unit |
| --- | --- | --- | --- |
| Penghuni | Ya | Tidak | Tidak |
| Petugas | Ya | Ya | Tidak |
| Admin | Ya | Ya | Ya |

Backend memeriksa sesi, role, dan token CSRF. Menyembunyikan kontrol di browser hanya membantu UI dan bukan pengganti pemeriksaan server.

## API dan dokumentasi

- Endpoint dan format: [`docs/API.md`](docs/API.md)
- Kasus uji dan smoke test: [`docs/test-matrix.md`](docs/test-matrix.md)
- Health check: `GET /api/health.php`
- Unit CRUD: `GET/POST /api/units.php`, `GET/PUT/PATCH/DELETE /api/units.php?id=...`
- Login/session/CSRF: `GET/POST /api/auth.php`
- Fasilitas/inventaris: `GET /api/facilities.php`

Smoke test memakai satu akun admin khusus pengujian dan membuat lalu menghapus satu unit berkode unik:

```powershell
$env:KOST_TEST_EMAIL = 'admin-test@example.test'
$env:KOST_TEST_PASSWORD = 'password akun pengujian'
php tests/api/api-smoke.php
```

## Batas fitur saat ini

Formulir minat memvalidasi dan menampilkan pratinjau di browser; belum menyimpan data prospek. Penghuni yang ditautkan ke akun dapat melihat sewa dan pembayaran miliknya; belum ada UI untuk mengelola kontrak/pembayaran. Room tour memakai gambar panorama demonstrasi Pannellum dan perlu diganti dengan foto kost sebenarnya.
