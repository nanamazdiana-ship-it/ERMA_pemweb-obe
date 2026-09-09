# Proyek Web Kost Pak Sapriadi (3 Pintu) - Milestone Minggu 3

Repositori ini memuat implementasi CSS Modern, Desain Responsif (Mobile-First), Flexbox, CSS Grid, dan Aksesibilitas Visual untuk Milestone Pertemuan 3 Mata Kuliah Pemrograman Web (26TJ453127), Jurusan Teknik Komputer, Universitas Borneo Tarakan.

## 1. Deskripsi Proyek
* **Nama Sistem:** Sistem Informasi dan Manajemen Kost Pak Sapriadi (3 Pintu)
* **Karakteristik Desain:** Pendekatan *Mobile-First*, palet warna sejuk bernuansa biru modern, tipografi adaptif, dan layout fluid menggunakan Flexbox serta CSS Grid murni tanpa dependensi CSS framework eksternal.

## 2. Cara Menjalankan Aplikasi
1. Jalankan aplikasi server lokal **Laragon 5** (pastikan Apache aktif).
2. Simpan direktori repositori ini pada: `C:\laragon\www\pemweb-obe`.
3. Buka peramban dan akses alamat:
   `http://localhost/pemweb-obe/`

## 3. Catatan Keputusan Desain (Design Decisions)
* **Custom Properties (`:root`):** Menggunakan variabel global CSS untuk warna brand (`#0284c7`), permukaan (`#ffffff`), teks (`#1e293b`), dan skala jarak konsisten (`--space-1` hingga `--space-4`).
* **Flexbox (1 Dimensi):** Diterapkan pada `<header>` dan elemen `<nav>` guna menyusun logo dan link navigasi secara adaptif dengan wrapping horizontal.
* **CSS Grid (2 Dimensi):** Diterapkan pada katalog kamar (`#katalog`) menggunakan `repeat(auto-fit, minmax(17rem, 1fr))` sehingga kartu unit pintu tersusun otomatis dari 1 kolom (mobile), 2 kolom (tablet), hingga 3 kolom (desktop) tanpa banyak media query kaku.
* **Mobile-First & Fluid Units:** Basis CSS dibangun untuk resolusi terkecil (320px), dengan pemanfaatan fungsi `clamp()` untuk ukuran judul utama `h1` dan `width: min(100% - 2rem, 72rem)` untuk batas container tengah.

## 4. Checklist Pengujian Responsif & Aksesibilitas
- [x] **Layar Ponsel (320px):** Layout bertumpuk vertikal, menu navigasi wrap rapi, dan nol horizontal scrolling (bebas overflow).
- [x] **Layar Tablet (768px):** Kartu katalog unit membelah 2 kolom proporsional.
- [x] **Layar Desktop (1024px+):** Tiga kartu unit pintu berjejer sejajar dan lebar konten terjaga di tengah layar.
- [x] **Indikator Fokus Keyboard (`:focus-visible`):** Outline navigasi keyboard tetap terlihat kontras setebal 3px dengan offset 3px saat menekan tombol `Tab`.
- [x] **Kontras Warna:** Rasio kontras teks terhadap latar belakang telah memenuhi standar minimum WCAG AA.