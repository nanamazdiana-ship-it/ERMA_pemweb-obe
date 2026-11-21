# API Kost Pak Sapriadi

Semua endpoint mengembalikan JSON. Error memakai bentuk `{ "error": { "code", "message", "fields" } }`. API memakai sesi cookie same-origin; perubahan data membutuhkan header `X-CSRF-Token` yang diperoleh dari `GET /api/auth.php`.

## Auth

- `GET /api/auth.php`: status pengguna dan token CSRF.
- `POST /api/auth.php` dengan `{"action":"login","email":"...","password":"..."}`: login.
- `POST /api/auth.php` dengan `{"action":"logout"}`: logout.
- `GET /api/me.php`: profil akun yang sedang login tanpa password hash; anonymous menerima `401`.
- `GET /api/account.php`: data sewa dan pembayaran milik penghuni yang sedang masuk.

Kontrol tombol di UI hanya mengatur tampilan. Pengguna dapat mengubah DOM atau mengirim request API sendiri, sehingga setiap operasi sensitif tetap memeriksa session dan role di server. `401 UNAUTHENTICATED` berarti belum login; `403 FORBIDDEN` berarti sudah login tetapi role tidak memiliki izin.

Role: `penghuni` hanya membaca; `petugas` dapat membuat dan mengubah unit; `admin` memiliki seluruh izin termasuk menghapus.
Role `penghuni` harus ditautkan ke satu baris `penghuni_kost` melalui kolom unik `pengguna.penghuni_id`. Penghuni yang belum ditautkan menerima `409 TENANT_ACCOUNT_UNLINKED` pada endpoint akun.

## Penghuni, pembayaran, fasilitas, dan pengaduan

- `GET /api/tenants.php`: daftar penghuni untuk admin/petugas.
- `GET /api/payments.php`: daftar pembayaran untuk admin/petugas; penghuni hanya menerima pembayaran terkait datanya sendiri.
- `GET /api/facilities.php`: daftar fasilitas/inventaris yang tersimpan di database.
- `GET /api/complaints.php`: daftar pengaduan milik penghuni aktif, atau seluruh pengaduan untuk admin/petugas.
- `POST /api/complaints.php` (penghuni, CSRF): buat pengaduan dengan `judul` dan `deskripsi`.
- `PATCH /api/complaints.php?id=1` (admin/petugas, CSRF): ubah `status` menjadi `Baru`, `Diproses`, atau `Selesai`.

Pengaduan memerlukan tabel `pengaduan_kost`, yang dibuat oleh migrasi `database/migrations/003_create_complaints.sql`.

## Unit kost

- `GET /api/units.php?page=1&per_page=10&q=pintu&status=Tersedia`: koleksi + metadata pagination.
- `GET /api/units.php?id=1`: detail, `404` jika tidak ditemukan.
- `POST /api/units.php`: buat unit; JSON berisi `kode`, `nama`, `status`, `harga`, `fasilitas`.
- `PUT /api/units.php?id=1`: ganti semua field.
- `PATCH /api/units.php?id=1`: ubah sebagian field.
- `DELETE /api/units.php?id=1`: hapus unit; `204` tanpa body.

Status hanya `Tersedia` atau `Terisi`; harga bilangan bulat 0–100.000.000; kode maksimal 20 karakter; nama maksimal 100; fasilitas maksimal 1.000. Kode duplikat menghasilkan `409`.

## Health

`GET /api/health.php` memeriksa ketersediaan API. Endpoint ini tidak memeriksa koneksi DB.
