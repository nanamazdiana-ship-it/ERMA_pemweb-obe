// File: assets/js/app.js

// 1. IMPORT WAJIB DI BARIS PALING ATAS
import { ringkasInventaris } from './utils.js';

// 2. BUAT / DEKLARASIKAN ARRAY INVENTARIS DULU
const inventaris = [
  { id: 1, nama: 'Router Wi-Fi Utama', kategori: 'Fasilitas', jumlah: 1, kondisi: 'Baik' },
  { id: 2, nama: 'Profil Air / Tandon', kategori: 'Fasilitas', jumlah: 2, kondisi: 'Baik' },
  { id: 3, nama: 'Kasur Springbed', kategori: 'Mebel', jumlah: 6, kondisi: 'Baik' },
  { id: 4, nama: 'Lemari Pakaian', kategori: 'Mebel', jumlah: 3, kondisi: 'Baik' }
];

// 3. PENGOLAHAN DATA (filter, map, reduce)
const alatBaik = inventaris.filter(item => item.kondisi === 'Baik');
const namaAlat = inventaris.map(({ nama }) => nama);
const totalUnit = inventaris.reduce((total, item) => total + item.jumlah, 0);

// 4. LOG DASAR
console.log('=== DATA INVENTARIS ===');
console.table(inventaris);
console.log('=== 3. FASILITAS KONDISI BAIK (FILTER) ===');
console.table(alatBaik);
console.log('=== 4. DAFTAR NAMA FASILITAS KOST (MAP) ===');
console.log(namaAlat);
console.log('=== 5. TOTAL SELURUH UNIT FASILITAS (REDUCE) ===');
console.log(`Total Unit: ${totalUnit} unit`);

// 5. PEMANGGILAN FUNGSI DARI UTILS.JS HARUS DI BARIS PALING BAWAH
console.log('=== 6 & 8. STATISTIK RINGKASAN INVENTARIS (UTILS.JS) ===');
console.log(ringkasInventaris(inventaris));