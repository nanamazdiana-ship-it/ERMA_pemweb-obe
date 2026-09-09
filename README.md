# Praktikum Web Kost Pak Sapriadi (3 Pintu) - Milestone Minggu 2

Repositori ini memuat struktur HTML5 semantik dan aksesibilitas web dasar untuk Milestone Minggu 2 Mata Kuliah Pemrograman Web (26TJ453127), Jurusan Teknik Komputer, Universitas Borneo Tarakan.

## 1. Deskripsi Praktikum
* **Nama Sistem:** Sistem Informasi dan Manajemen Kost Pak Sapriadi (3 Pintu)
* **Konsep Properti:** Memiliki 3 pintu sewa mandiri dengan tarif sewa terjangkau **Rp 1.000.000 / bulan**. Di dalam setiap pintu terdapat ruang tengah, 2 kamar tidur, dapur, dan toilet mandiri.
* **Tujuan Web:** Menyajikan status ketersediaan 3 unit pintu, pelaporan kerusakan sarana, dan transaksi pembayaran sewa.

## 2. Cara Menjalankan Aplikasi
1. Jalankan aplikasi server lokal **Laragon 5** (pastikan modul Apache aktif).
2. Simpan folder repositori ini pada direktori: `C:\laragon\www\proyek-semester`.
3. Buka peramban web dan akses alamat:
   `http://localhost/proyek-semester/`

## 3. Sitemap & Wireframe Sederhana
### A. Sitemap Halaman Utama
- Header & Navigasi Utama
  - #tentang (Profil & Judul Utama H1)
  - #katalog (Daftar 3 Pintu Kost)
  - #kontak (Formulir Minat Sewa)
- Footer

### B. Wireframe Sederhana
+-------------------------------------------------------+
| HEADER: Kost Pak Sapriadi | Nav: Tentang, Katalog, Kontak
+-------------------------------------------------------+
| MAIN                                                  |
|  [SECTION 1: H1 Judul Utama & Profil Kost]            |
|                                                       |
|  [SECTION 2: Katalog Kamar 3 Pintu]                   |
|   - ARTICLE: Pintu 01 (Status: Tersedia)              |
|   - ARTICLE: Pintu 02 (Status: Terisi)                |
|   - ARTICLE: Pintu 03 (Status: Tersedia)              |
|                                                       |
|  [SECTION 3: Formulir Minat Sewa]                     |
|   - Label & Input: Nama, WhatsApp, Pilihan Pintu, Pesan|
|   - Tombol Kirim                                      |
+-------------------------------------------------------+
| FOOTER: Hak Cipta © 2026 Kost Pak Sapriadi (3 Pintu)  |
+-------------------------------------------------------+

## 4. Checklist Aksesibilitas Dasar (Self-Review)
- [x] Dokumen menggunakan `lang="id"`.
- [x] Struktur semantik lengkap: `header`, `nav`, `main`, 3 `section`, `article`, `form`, dan `footer`.
- [x] Halaman bebas dari penggunaan tag `<div>` yang tidak bermakna.
- [x] Hierarki heading logis dan berurutan (`h1` -> `h2` -> `h3`).
- [x] Setiap kolom form terhubung dengan elemen `label` melalui atribut `for` dan `id`.
- [x] Navigasi keyboard (tombol Tab) dapat menjangkau seluruh link, input, dan tombol aksi.