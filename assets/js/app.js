// Memakai fungsi statistik yang dipisahkan ke utils.js.
import { ringkasInventaris } from './utils.js';

// Sumber data inventaris yang dipakai oleh Console, pencarian, filter, dan kartu.
const inventaris = [
  { id: 1, nama: 'Router Wi-Fi Utama', kategori: 'Fasilitas', jumlah: 1, kondisi: 'Baik', lokasi: 'Lantai 1' },
  { id: 2, nama: 'Profil Air / Tandon', kategori: 'Fasilitas', jumlah: 3, kondisi: 'Baik', lokasi: 'Samping' },
  { id: 3, nama: 'Kasur Springbed', kategori: 'Mebel', jumlah: 6, kondisi: 'Baik', lokasi: 'Kamar' },
  { id: 4, nama: 'Lemari Pakaian', kategori: 'Mebel', jumlah: 3, kondisi: 'Baik', lokasi: 'Kamar' },
  { id: 5, nama: 'Lampu Teras', kategori: 'Fasilitas', jumlah: 3, kondisi: 'Perlu Cek', lokasi: 'Teras' }
];

// Contoh filter, map, reduce, dan fungsi utils; hasilnya ditampilkan di Console.
const alatBaik = inventaris.filter(item => item.kondisi === 'Baik');
const namaAlat = inventaris.map(({ nama }) => nama);
const totalUnit = inventaris.reduce((total, item) => total + item.jumlah, 0);

console.log('=== DATA INVENTARIS ===');
console.table(inventaris);
console.log('=== INVENTARIS KONDISI BAIK (FILTER) ===');
console.table(alatBaik);
console.log('=== DAFTAR NAMA INVENTARIS (MAP) ===');
console.log(namaAlat);
console.log('=== TOTAL SELURUH UNIT INVENTARIS (REDUCE) ===');
console.log(`Total Unit: ${totalUnit} unit`);
console.log('=== RINGKASAN INVENTARIS (UTILS.JS) ===');
console.log(ringkasInventaris(inventaris));

// Elemen halaman dan pengaturan untuk daftar inventaris.
const daftarAlat = document.querySelector('#daftar-alat');
const tombolFilter = document.querySelectorAll('[data-filter]');
const inputCari = document.querySelector('#cari-inventaris');
const pilihanJumlah = document.querySelector('#jumlah-per-halaman');
const kontrolHalaman = document.querySelector('#pagination-controls');
const kunciJumlahPerHalaman = 'kost-items-per-page';
const pilihanJumlahValid = [5, 10, 20];
let jumlahPerHalaman = 5;
let halamanAktif = 1;

try {
  const tersimpan = Number(localStorage.getItem(kunciJumlahPerHalaman));
  if (pilihanJumlahValid.includes(tersimpan)) jumlahPerHalaman = tersimpan;
} catch (error) {
  // Gunakan nilai awal jika localStorage tidak tersedia.
}
if (pilihanJumlah) pilihanJumlah.value = String(jumlahPerHalaman);

// Membuat tombol navigasi dan informasi halaman sesuai jumlah hasil.
function renderKontrolHalaman(totalItem) {
  if (!kontrolHalaman) return;
  kontrolHalaman.replaceChildren();
  const totalHalaman = Math.max(1, Math.ceil(totalItem / jumlahPerHalaman));

  const sebelumnya = document.createElement('button');
  sebelumnya.type = 'button';
  sebelumnya.dataset.pageAction = 'previous';
  sebelumnya.textContent = 'Sebelumnya';
  sebelumnya.disabled = halamanAktif === 1;

  const statusHalaman = document.createElement('span');
  statusHalaman.textContent = `Halaman ${halamanAktif} dari ${totalHalaman} (${totalItem} item)`;
  statusHalaman.setAttribute('aria-live', 'polite');

  const berikutnya = document.createElement('button');
  berikutnya.type = 'button';
  berikutnya.dataset.pageAction = 'next';
  berikutnya.textContent = 'Berikutnya';
  berikutnya.disabled = halamanAktif >= totalHalaman;

  kontrolHalaman.append(sebelumnya, statusHalaman, berikutnya);
}

// Mengosongkan daftar lalu membuat kartu hanya untuk halaman yang sedang aktif.
function renderItems(items) {
  if (!daftarAlat) return;
  daftarAlat.replaceChildren();
  const totalHalaman = Math.max(1, Math.ceil(items.length / jumlahPerHalaman));
  halamanAktif = Math.min(halamanAktif, totalHalaman);
  renderKontrolHalaman(items.length);

  if (items.length === 0) {
    const pesan = document.createElement('p');
    pesan.textContent = 'Tidak ada inventaris yang cocok dengan pencarian atau filter ini.';
    daftarAlat.appendChild(pesan);
    return;
  }

  const awal = (halamanAktif - 1) * jumlahPerHalaman;
  items.slice(awal, awal + jumlahPerHalaman).forEach(item => {
    const card = document.createElement('article');
    card.className = 'card';
    card.dataset.itemId = String(item.id);

    const judul = document.createElement('h3');
    judul.textContent = item.nama;

    const detail = document.createElement('div');
    detail.className = 'item-detail';
    detail.hidden = true;

    const kategori = document.createElement('p');
    kategori.textContent = `Kategori: ${item.kategori}`;

    const jumlah = document.createElement('p');
    jumlah.textContent = `Jumlah: ${item.jumlah} unit`;

    const lokasi = document.createElement('p');
    lokasi.textContent = `Lokasi: ${item.lokasi}`;
    const kondisi = document.createElement('p');
    kondisi.textContent = `Kondisi: ${item.kondisi}`;
    detail.append(kategori, jumlah, kondisi, lokasi);

    const tombolDetail = document.createElement('button');
    tombolDetail.type = 'button';
    tombolDetail.dataset.action = 'detail';
    tombolDetail.textContent = 'Detail';
    tombolDetail.setAttribute('aria-expanded', 'false');

    card.append(judul, tombolDetail, detail);
    daftarAlat.append(card);
  });
}

// Menggabungkan pencarian nama dan filter kondisi sebelum merender kartu.
let filterAktif = 'Semua';

function perbaruiDaftar() {
  const kataKunci = inputCari?.value.trim().toLocaleLowerCase('id') ?? '';
  const hasil = inventaris.filter(item => {
    const cocokKondisi = filterAktif === 'Semua' || item.kondisi === filterAktif;
    const cocokNama = item.nama.toLocaleLowerCase('id').includes(kataKunci);
    return cocokKondisi && cocokNama;
  });
  renderItems(hasil);
}

// Mengubah kondisi filter aktif dan memperbarui penanda tombol terpilih.
function pilihFilter(kondisiPilihan) {
  filterAktif = kondisiPilihan;
  halamanAktif = 1;
  perbaruiDaftar();
  tombolFilter.forEach(tombol => {
    const terpilih = tombol.dataset.filter === kondisiPilihan;
    tombol.setAttribute('aria-pressed', String(terpilih));
  });
}

// Menghubungkan input pencarian, filter, pilihan jumlah item, dan navigasi halaman.
tombolFilter.forEach(tombol => {
  tombol.addEventListener('click', () => {
    pilihFilter(tombol.dataset.filter);
  });
});
pilihFilter('Semua');
inputCari?.addEventListener('input', () => {
  halamanAktif = 1;
  perbaruiDaftar();
});

pilihanJumlah?.addEventListener('change', () => {
  const nilai = Number(pilihanJumlah.value);
  if (!pilihanJumlahValid.includes(nilai)) return;

  jumlahPerHalaman = nilai;
  halamanAktif = 1;
  try {
    localStorage.setItem(kunciJumlahPerHalaman, String(jumlahPerHalaman));
  } catch (error) {
    // Perubahan berlaku untuk sesi ini jika localStorage tidak tersedia.
  }
  perbaruiDaftar();
});

kontrolHalaman?.addEventListener('click', event => {
  const tombol = event.target.closest('button[data-page-action]');
  if (!tombol || tombol.disabled) return;

  halamanAktif += tombol.dataset.pageAction === 'next' ? 1 : -1;
  perbaruiDaftar();
});

// Event delegation: satu listener melayani tombol Detail dari semua kartu dinamis.
daftarAlat?.addEventListener('click', event => {
  const tombol = event.target.closest('button[data-action="detail"]');
  if (!tombol || !daftarAlat.contains(tombol)) return;

  const panelDetail = tombol.closest('.card')?.querySelector('.item-detail');
  if (!panelDetail) return;

  const dibuka = panelDetail.hidden;
  panelDetail.hidden = !dibuka;
  tombol.textContent = dibuka ? 'Tutup Detail' : 'Detail';
  tombol.setAttribute('aria-expanded', String(dibuka));
});

// Memulihkan, menerapkan, dan menyimpan preferensi tema terang atau gelap.
const kunciTema = 'Kost-theme-preference';
const tombolTema = document.querySelector('#theme-button');

function terapkanTema(tema) {
  const gelap = tema === 'dark';
  if (gelap) document.documentElement.dataset.theme = 'dark';
  else delete document.documentElement.dataset.theme;
  if (tombolTema) {
    tombolTema.setAttribute('aria-pressed', String(gelap));
    const label = gelap ? 'Ganti ke tema terang' : 'Ganti ke tema gelap';
    tombolTema.setAttribute('aria-label', label);
    tombolTema.title = label;
    tombolTema.innerHTML = gelap
      ? '<svg class="theme-icon" viewBox="0 0 24 24" aria-hidden="true"><path d="M20.2 15.1A8.5 8.5 0 0 1 8.9 3.8 8.6 8.6 0 1 0 20.2 15.1Z"/></svg>'
      : '<svg class="theme-icon" viewBox="0 0 24 24" aria-hidden="true"><circle cx="12" cy="12" r="4"/><path d="M12 2v2m0 16v2M4.93 4.93l1.42 1.42m11.3 11.3 1.42 1.42M2 12h2m16 0h2M4.93 19.07l1.42-1.42m11.3-11.3 1.42-1.42"/></svg>';
  }
}

try {
  const temaTersimpan = localStorage.getItem(kunciTema);
  terapkanTema(temaTersimpan === 'dark' ? 'dark' : 'light');
} catch (error) {
  terapkanTema('light');
}

tombolTema?.addEventListener('click', () => {
  const temaBaru = document.documentElement.dataset.theme === 'dark' ? 'light' : 'dark';
  terapkanTema(temaBaru);
  try {
    localStorage.setItem(kunciTema, temaBaru);
  } catch (error) {
    // Tema tetap berubah untuk sesi ini jika penyimpanan browser tidak tersedia.
  }
});

// Menghentikan submit demo, menampilkan konfirmasi, lalu mereset formulir.
const formulir = document.querySelector('#kontak form');
formulir?.addEventListener('submit', event => {
  event.preventDefault();
  alert('Terima kasih! Minat sewa Anda berhasil dicatat.');
  formulir.reset();
});
