# Matriks pengujian

| Area | Kasus | Hasil yang diharapkan |
| --- | --- | --- |
| API | GET koleksi dan detail | 200, JSON dan metadata; ID tidak ada menghasilkan 404 |
| API | POST valid / duplikat / tidak valid | 201 / 409 / 422 |
| API | PUT dan PATCH | 200 dan data berubah; field invalid menghasilkan 422 |
| API | DELETE | Admin menerima 204; ID tidak ada 404 |
| API | Tanpa sesi / role rendah | 401 / 403 pada operasi terlarang |
| Auth | Anonymous membuka unit lalu mencoba POST | GET 200; POST 401 |
| Auth | Anonymous membaca profil `/api/me.php` | 401 `UNAUTHENTICATED` |
| Auth | User login membaca profil `/api/me.php` | 200; berisi profil tanpa `password_hash` |
| Auth | Penghuni/viewer mencoba POST, PUT, DELETE unit | GET 200; seluruh perubahan 403 |
| Auth | Petugas mencoba create/update/delete unit | POST/PUT/PATCH diizinkan; DELETE 403 |
| Auth | Admin mencoba create/update/delete unit | Semua perubahan diizinkan |
| Auth | Login/logout | Password valid; session ID berubah setelah login; logout POST menghapus session dan cookie |
| API | Tanpa CSRF atau JSON malformed | 403 / 400 |
| API | Inventory dan akun | Inventaris dari DB; akun penghuni hanya menerima sewa/pembayarannya sendiri |
| Error | Database tidak tersedia | 500 generik tanpa detail SQL atau kredensial |
| UI | Login, logout, CRUD, cari, filter, halaman | Data tetap berasal dari API; status dan pesan tampil |
| UI | Viewport 320px, 768px, desktop | Konten tidak meluber horizontal; tabel dapat digulir |

Jalankan smoke test dengan `KOST_TEST_EMAIL` dan `KOST_TEST_PASSWORD` milik akun admin yang dapat dipakai khusus pengujian:

```powershell
$env:KOST_TEST_EMAIL = 'admin-test@example.test'
$env:KOST_TEST_PASSWORD = 'password akun pengujian'
php tests/api/api-smoke.php
```

Smoke test membuat satu unit berkode `TEST-<random>` lalu menghapusnya. Jalankan hanya terhadap instalasi lokal/test.

Uji otorisasi semua role memakai akun dan unit sementara yang dibuat otomatis lalu dibersihkan:

```powershell
php tests/api/auth-authorization.php
```

Secara default tes mengakses `http://localhost/pemweb-obe`; atur `KOST_TEST_BASE_URL` jika alamat lokal berbeda.

## Uji role melalui Postman/Thunder Client

Untuk setiap akun, mulai dengan `GET /api/auth.php` dan simpan `data.csrf_token`. Login memakai `POST /api/auth.php` dengan header `X-CSRF-Token` dan body JSON `{"action":"login","email":"...","password":"..."}`. Simpan cookie `PHPSESSID` dari respons. Gunakan cookie dan token tersebut untuk request berikut:

| Akun | Request | Hasil |
| --- | --- | --- |
| Anonymous | `GET /api/units.php` | 200 |
| Anonymous | `POST /api/units.php` | 401 |
| Penghuni (viewer) | `GET /api/units.php` | 200 |
| Penghuni (viewer) | `POST /api/units.php` | 403 |
| Petugas | `POST` atau `PATCH /api/units.php` | 201 atau 200 |
| Petugas | `DELETE /api/units.php?id=<id-uji>` | 403 |
| Admin | `DELETE /api/units.php?id=<id-uji>` | 204 |
| Semua role | `POST /api/auth.php` dengan `{"action":"logout"}` dan token CSRF | 200; sesi berakhir |

Logout menghapus cookie sesi. Setelah login, periksa cookie di Postman/DevTools: `HttpOnly`, `SameSite=Lax`, dan `Secure` saat aplikasi memakai HTTPS.
