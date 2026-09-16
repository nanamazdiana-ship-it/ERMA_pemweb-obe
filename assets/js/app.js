// File: assets/js/app.js

// 1. IMPORT 
import { ringkasInventaris } from './utils.js';

// 1. UPDATE DATA INVENTARIS (Tambahkan Properti 'lokasi')
const inventaris = [
  { id: 1, nama: 'Router Wi-Fi Utama', kategori: 'Fasilitas', jumlah: 1, kondisi: 'Baik', lokasi: 'Lantai 1' },
  { id: 2, nama: 'Profil Air / Tandon', kategori: 'Fasilitas', jumlah: 3, kondisi: 'Baik', lokasi: 'samping' },
  { id: 3, nama: 'Kasur Springbed', kategori: 'Mebel', jumlah: 6, kondisi: 'Baik', lokasi: 'Kamar' },
  { id: 4, nama: 'Lemari Pakaian', kategori: 'Mebel', jumlah: 3, kondisi: 'Baik', lokasi: 'Kamar' }
];


// 3. PENGOLAHAN DATA (filter, map, reduce)
const alatBaik = inventaris.filter(item => item.kondisi === 'Baik');
const namaAlat = inventaris.map(({ nama }) => nama);
const totalUnit = inventaris.reduce((total, item) => total + item.jumlah, 0);

// 4. LOG DASAR
console.log(' DATA INVENTARIS');
console.table(inventaris);
console.log('FASILITAS KONDISI BAIK (FILTER)');
console.table(alatBaik);
console.log('DAFTAR NAMA FASILITAS KOST (MAP)');
console.log(namaAlat);
console.log(' 5. TOTAL SELURUH UNIT FASILITAS (REDUCE) ');
console.log(`Total Unit: ${totalUnit} unit`);

// 5. PEMANGGILAN FUNGSI DARI UTILS.JS HARUS DI BARIS PALING BAWAH
console.log('STATISTIK RINGKASAN INVENTARIS (UTILS.JS)');
console.log(ringkasInventaris(inventaris));

// ==========================================
// LATIHAN 2: Fungsi Cari Item berdasarkan ID (find)
// ==========================================
const cariItemById = (idCari) => inventaris.find(item => item.id === idCari);

console.log('CARI ITEM BERDASARKAN ID ');
const itemDitemukan = cariItemById();
console.log(itemDitemukan ? itemDitemukan : 'Item tidak ditemukan!');

// === LATIHAN 3: RINGKASAN ITEM MENGGUNAKAN DESTRUCTURING & TEMPLATE LITERAL ===
console.log('RINGKASAN SETIAP ITEM');

// Mengolah seluruh item di array menggunakan map/forEach
inventaris.forEach(item => {
  // Destructuring properti dari objek item
  const { id, nama, lokasi, kondisi, jumlah } = item;
  
  const ringkasan = `[ID: ${id}] ${nama} - Lokasi: ${lokasi} | Kondisi: ${kondisi} (${jumlah} unit)`;
  
  console.log(ringkasan);
});
