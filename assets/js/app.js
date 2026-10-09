// Memakai fungsi statistik yang dipisahkan ke utils.js.
import { ringkasInventaris } from './utils.js';

// Konfigurasi Endpoint Modul 7
const USE_LOCAL_DATA = true; // Set true jika lab offline
const endpoint = USE_LOCAL_DATA 
  ? './data/dataPintu.json' 
  : 'https://jsonplaceholder.typicode.com/users';

const output = document.querySelector('#api-output');
const message = document.querySelector('#api-message');
const btnRetry = document.querySelector('#btn-retry');

// FUNGSI UTAMA FETCH DATA API MODUL 7
async function loadUsers() {
  if (message) {
    message.textContent = 'Memuat data unit kost...';
    message.className = 'status-message loading';
  }
  if (btnRetry) btnRetry.style.display = 'none';
  if (output) output.replaceChildren();

  try {
    const response = await fetch(endpoint);

    if (!response.ok) {
      throw new Error(`HTTP Error: ${response.status}`);
    }

    const data = await response.json();
    renderCards(data);

    // 1. Tampilkan pesan sukses
    if (message) {
      message.textContent = `Berhasil memuat ${data.length} data unit kost.`;
      message.className = 'status-message success';

      // 2. Hilangkan teks pesan secara otomatis setelah 3 detik (3000 ms)
      setTimeout(() => {
        message.textContent = '';
      }, 3000);
    }

  } catch (error) {
    console.error('Fetch Error:', error);
    if (message) {
      message.textContent = 'Data belum dapat dimuat. Coba kembali.';
      message.className = 'status-message error';

      // Hilangkan pesan error secara otomatis setelah 3 detik
      setTimeout(() => {
        message.textContent = '';
      }, 3000);
    }
    if (btnRetry) btnRetry.style.display = 'inline-block';
  }
}

// 4. FUNGSI RENDER KE DOM 
function renderCards(items) {
  if (!output) return;
  output.replaceChildren(); // Safe DOM Update

  if (items.length === 0) {
    const pesanKosong = document.createElement('p');
    pesanKosong.textContent = 'Unit yang Anda cari tidak ditemukan.';
    output.append(pesanKosong);
    return;
  }

  items.slice(0, 5).forEach(item => {
    const article = document.createElement('article');
    article.className = 'card-unit';

    // 1. Elemen Gambar (Gambar saja tanpa figcaption agar nama tidak dobel)
    const figure = document.createElement('figure');
    const img = document.createElement('img');
    img.src = item.gambar || 'pintukost.jpeg';
    img.alt = `Tampak depan unit ${item.nama || item.name} Kost Pak Sapriadi`;
    img.width = 300;
    img.loading = 'lazy';
    figure.append(img); // Cukup masukkan img saja tanpa figcaption nama unit

    // 2. Nama Unit Pintu 
    const h3 = document.createElement('h3');
    h3.textContent = item.nama || item.name;

    // 3. Status Ketersediaan Unit
    const pStatus = document.createElement('p');
    pStatus.textContent = `Status: ${item.status || 'Tersedia'}`;

    // 4. Tarif Sewa
    const pHarga = document.createElement('p');
    pHarga.textContent = item.harga 
      ? `Tarif sewa: Rp ${item.harga.toLocaleString('id-ID')} / bulan`
      : `Email: ${item.email}`;

    // 5. Fasilitas Unit
    const pFasilitas = document.createElement('p');
    pFasilitas.textContent = `Fasilitas: ${item.fasilitas || 'Ruang tengah, 2 kamar tidur, dapur, dan toilet.'}`;

    // Susun elemen ke dalam kartu article (Foto dulu, baru Judul Nama & Detail)
    article.append(figure, h3, pStatus, pHarga, pFasilitas);
    output.append(article);
  });
}

  
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

// Baca dan normalkan nilai formulir minat sewa.
export function readFormData(form) {
  const formData = new FormData(form);
  return {
    nama: String(formData.get('nama') ?? '').trim(),
    telepon: String(formData.get('telepon') ?? '').trim(),
    email: String(formData.get('email') ?? '').trim(),
    pilihan_pintu: String(formData.get('pilihan_pintu') ?? '').trim(),
    tanggal_survei: String(formData.get('tanggal_survei') ?? '').trim(),
    pesan: String(formData.get('pesan') ?? '').trim()
  };
}

// Pesan untuk field kosong dibedakan dari format/nilai yang tidak valid.
export function validateForm(formData) {
  const errors = {};
  const unitTersedia = ['pintu-02', 'pintu-03'];
  const now = new Date();
  const today = new Date(now.getTime() - now.getTimezoneOffset() * 60000).toISOString().slice(0, 10);

  if (!formData.nama) errors.nama = 'Nama lengkap wajib diisi.';
  else if (!/^[\p{L} ]{3,}$/u.test(formData.nama)) errors.nama = 'Format nama tidak valid: gunakan minimal 3 huruf dan spasi saja.';

  if (!formData.telepon) errors.telepon = 'Nomor WhatsApp wajib diisi.';
  else if (!/^08\d{8,12}$/.test(formData.telepon)) errors.telepon = 'Format nomor tidak valid: gunakan angka saja, diawali 08 (10–14 digit).';

  if (!formData.email) errors.email = 'Email wajib diisi.';
  else if (!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(formData.email)) errors.email = 'Format email tidak valid. Contoh: nama@contoh.com.';

  if (!formData.pilihan_pintu) errors['pilihan-pintu'] = 'Unit pintu wajib dipilih.';
  else if (!unitTersedia.includes(formData.pilihan_pintu)) errors['pilihan-pintu'] = 'Pilih unit yang tersedia dari daftar.';

  if (formData.tanggal_survei) {
    if (!/^\d{4}-\d{2}-\d{2}$/.test(formData.tanggal_survei)) errors['tanggal-survei'] = 'Format tanggal rencana tidak valid.';
    else if (formData.tanggal_survei > today) errors['tanggal-survei'] = 'Tanggal rencana tidak boleh melebihi hari ini.';
  }
  return errors;
}

// Cegah pengiriman ke server; tampilkan error terkait atau preview data valid.
const formulir = document.querySelector('#form-minat');
const daftarMinat = document.querySelector('#daftar-minat');
const itemDaftarMinat = document.querySelector('#item-daftar-minat');
const fieldIds = {
  nama: 'nama',
  telepon: 'telepon',
  email: 'email',
  'pilihan-pintu': 'pilihan-pintu',
  'tanggal-survei': 'tanggal-survei',
  pesan: 'pesan'
};

const inputTanggalSurvei = document.querySelector('#tanggal-survei');
if (inputTanggalSurvei) {
  const now = new Date();
  inputTanggalSurvei.max = new Date(now.getTime() - now.getTimezoneOffset() * 60000).toISOString().slice(0, 10);
}

function perbaruiFeedbackField(field, id) {
  const input = document.getElementById(id);
  const errorText = document.getElementById(`${id}-error`);
  if (!input || !errorText) return;
  const error = validateForm(readFormData(formulir))[field];
  input.setAttribute('aria-invalid', String(Boolean(error)));
  errorText.textContent = error ?? '';
}

Object.entries(fieldIds).forEach(([field, id]) => {
  const input = document.getElementById(id);
  input?.addEventListener('input', () => perbaruiFeedbackField(field, id));
  input?.addEventListener('change', () => perbaruiFeedbackField(field, id));
  input?.addEventListener('blur', () => perbaruiFeedbackField(field, id));
});

formulir?.addEventListener('submit', event => {
  event.preventDefault();
  const fields = Object.entries(fieldIds);
  fields.forEach(([, id]) => {
    document.getElementById(id)?.setAttribute('aria-invalid', 'false');
    const errorText = document.getElementById(`${id}-error`);
    if (errorText) errorText.textContent = '';
  });
  const data = readFormData(formulir);
  const errors = validateForm(data);
  const invalidFields = fields.filter(([field]) => errors[field]);
  if (invalidFields.length) {
    invalidFields.forEach(([field, id]) => {
      document.getElementById(id)?.setAttribute('aria-invalid', 'true');
      const errorText = document.getElementById(`${id}-error`);
      if (errorText) errorText.textContent = errors[field];
    });
    document.getElementById(invalidFields[0][1])?.focus();
    return;
  }

  if (!itemDaftarMinat || !daftarMinat) return;
  const labelUnit = formulir.elements.pilihan_pintu.selectedOptions[0]?.textContent.trim() ?? data.pilihan_pintu;
  const labels = [
    ['Nama Lengkap', data.nama],
    ['Nomor WhatsApp', data.telepon],
    ['Email', data.email],
    ['Unit Pintu', labelUnit],
    ['Tanggal Rencana Survei', data.tanggal_survei || 'Tidak diisi'],
    ['Pesan', data.pesan || 'Tidak ada pesan']
  ];
  const item = document.createElement('li');
  item.className = 'interest-entry';
  const judul = document.createElement('h4');
  judul.textContent = `Minat sewa ${itemDaftarMinat.children.length + 1}`;
  const detail = document.createElement('dl');
  labels.forEach(([label, value]) => {
    const term = document.createElement('dt');
    term.textContent = label;
    const description = document.createElement('dd');
    description.textContent = value;
    detail.append(term, description);
  });
  item.append(judul, detail);
  itemDaftarMinat.append(item);
  daftarMinat.hidden = false;
});


// 5. EKSEKUSI PEMANGGILAN DITARUH DI BARIS PALING BAWAH
loadUsers();