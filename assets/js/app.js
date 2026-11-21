import { apiRequest } from './core/api.js';

// ========================================
// 1. KONFIGURASI DAN ELEMEN KATALOG
// ========================================
const endpoint = './api/units.php?per_page=50';

const output = document.querySelector('#api-output');
const message = document.querySelector('#api-message');
const btnRetry = document.querySelector('#btn-retry');
const searchInput = document.querySelector('#searchInput');

let dataUnitKost = [];

// ========================================
// 2. MENGAMBIL DATA UNIT KOST DARI JSON
// ========================================
async function loadUsers() {
  if (message) {
    message.textContent = 'Memuat data unit kost...';
    message.className = 'status-message loading';
  }

  if (btnRetry) btnRetry.style.display = 'none';
  if (output) output.replaceChildren();

  try {
    const payload = await apiRequest(endpoint);
    const data = payload.data;

    if (!Array.isArray(data)) {
      throw new Error(payload.error?.message || 'Format data unit kost dari server tidak valid.');
    }

    dataUnitKost = data;
    tampilkanKatalogTersaring();

    if (message) {
      message.textContent = `Berhasil memuat ${data.length} data unit kost.`;
      message.className = 'status-message success';

      setTimeout(() => {
        message.textContent = '';
      }, 3000);
    }
  } catch (error) {
    console.error('Gagal mengambil data unit kost:', error);

    if (message) {
      message.textContent =
        'Data belum dapat dimuat. Periksa koneksi atau lokasi file JSON, lalu coba kembali.';
      message.className = 'status-message error';
    }

    if (btnRetry) {
      btnRetry.style.display = 'inline-block';
    }
  }
}

// ========================================
// 3. MENAMPILKAN KARTU UNIT KOST
// ========================================
function renderCards(items) {
  if (!output) return;

  output.replaceChildren();

  // Pesan jika hasil pencarian kosong.
  if (items.length === 0) {
    const pesanKosong = document.createElement('p');
    pesanKosong.textContent =
      'Unit yang Anda cari tidak ditemukan.';

    output.append(pesanKosong);
    return;
  }

  // Menampilkan semua unit yang sesuai dengan pencarian.
  items.forEach(item => {
    const article = document.createElement('article');
    article.className = 'card';

    // Gambar unit kost.
    const figure = document.createElement('figure');
    const img = document.createElement('img');

    const namaUnit = item.nama || item.name || 'Unit Kost';
    const gambarCadangan = 'assets/images/kost/pintuKost.jpeg';

    img.src = item.gambar || gambarCadangan;
    img.alt = `Tampak depan ${namaUnit} Kost Pak Sapriadi`;
    img.className = 'gambar-unit';
    img.loading = 'lazy';

    // Menghindari gambar rusak jika gambar utama tidak ditemukan.
    img.addEventListener('error', function () {
      if (img.src.endsWith(gambarCadangan)) {
        img.hidden = true;
      } else {
        img.src = gambarCadangan;
      }
    }, { once: true });

    figure.append(img);

    // Nama unit.
    const judul = document.createElement('h3');
    judul.textContent = namaUnit;

    // Status ketersediaan.
    const pStatus = document.createElement('p');
    const status = item.status || 'Belum diketahui';
    pStatus.className = 'unit-status';
    pStatus.textContent = `Status: ${status}`;

    // Harga sewa.
    const pHarga = document.createElement('p');

    if (typeof item.harga === 'number') {
      pHarga.textContent =
        `Tarif sewa: Rp ${item.harga.toLocaleString('id-ID')} / bulan`;
    } else {
      pHarga.textContent = item.email
        ? `Email: ${item.email}`
        : 'Tarif sewa belum tersedia.';
    }

    // Fasilitas unit.
    const pFasilitas = document.createElement('p');
    pFasilitas.textContent =
      `Fasilitas: ${item.fasilitas || 'Ruang tengah, 2 kamar tidur, dapur, dan toilet.'}`;

    article.append(
      figure,
      judul,
      pStatus,
      pHarga,
      pFasilitas
    );

    const detailLink = document.createElement('a');
    detailLink.href = `index.php?page=public/unit-detail&id=${encodeURIComponent(item.id)}`;
    detailLink.textContent = 'Lihat detail unit';
    article.append(detailLink);

    output.append(article);
  });

  updateAvailableUnitOptions(dataUnitKost);
}

function updateAvailableUnitOptions(units) {
  const select = document.querySelector('#pilihan-pintu');
  if (!select) return;
  const selected = select.value;
  select.replaceChildren(new Option('-- Pilih Unit Pintu --', ''));
  units.filter(unit => unit.status === 'Tersedia').forEach(unit => {
    select.add(new Option(`${unit.nama} (${unit.status})`, String(unit.kode)));
  });
  if ([...select.options].some(option => option.value === selected)) select.value = selected;
}

// ========================================
// 4. PENCARIAN KATALOG UNIT KOST
// ========================================
function tampilkanKatalogTersaring() {
  const kataKunci =
    searchInput?.value.trim().toLocaleLowerCase('id') ?? '';

  const hasil = dataUnitKost.filter(unit => {
    const namaUnit = String(
      unit.nama ?? unit.name ?? ''
    ).toLocaleLowerCase('id');

    const fasilitas = String(
      unit.fasilitas ?? ''
    ).toLocaleLowerCase('id');

    const kodeUnit = String(
      unit.kode ?? ''
    ).toLocaleLowerCase('id');

    // Pencarian mencakup nama, kode, dan fasilitas.
    return (
      namaUnit.includes(kataKunci) ||
      kodeUnit.includes(kataKunci) ||
      fasilitas.includes(kataKunci)
    );
  });

  renderCards(hasil);
}

// Jalankan pencarian saat pengguna mengetik.
searchInput?.addEventListener('input', () => {
  tampilkanKatalogTersaring();
});

// Tombol untuk mengambil ulang data.
btnRetry?.addEventListener('click', loadUsers);

// ========================================
// 5. DATA INVENTARIS KOST
// ========================================
let inventaris = [];

// ========================================
// 6. PENGATURAN DAFTAR INVENTARIS
// ========================================
const daftarAlat = document.querySelector('#daftar-alat');
const tombolFilter = document.querySelectorAll('[data-filter]');
const inputCari = document.querySelector('#cari-inventaris');
const pilihanJumlah = document.querySelector('#jumlah-per-halaman');
const kontrolHalaman = document.querySelector('#pagination-controls');

const kunciJumlahPerHalaman = 'kost-items-per-page';
const pilihanJumlahValid = [5, 10, 20];

let jumlahPerHalaman = 5;
let halamanAktif = 1;
let filterAktif = 'Semua';

async function loadInventory() {
  const statusMessage = document.querySelector('#inventory-message');
  if (statusMessage) statusMessage.textContent = 'Memuat inventaris…';
  try {
    const result = await apiRequest('./api/facilities.php');
    inventaris = result.data;
    perbaruiDaftar();
    if (statusMessage) statusMessage.textContent = `${inventaris.length} item inventaris dimuat.`;
  } catch (error) {
    if (statusMessage) statusMessage.textContent = error.message;
    if (daftarAlat) daftarAlat.replaceChildren();
  }
}

// Memulihkan pilihan jumlah item dari localStorage.
try {
  const tersimpan = Number(
    localStorage.getItem(kunciJumlahPerHalaman)
  );

  if (pilihanJumlahValid.includes(tersimpan)) {
    jumlahPerHalaman = tersimpan;
  }
} catch (error) {
  // Tetap gunakan nilai awal jika localStorage tidak tersedia.
}

if (pilihanJumlah) {
  pilihanJumlah.value = String(jumlahPerHalaman);
}

// ========================================
// 7. NAVIGASI HALAMAN INVENTARIS
// ========================================
function renderKontrolHalaman(totalItem) {
  if (!kontrolHalaman) return;

  kontrolHalaman.replaceChildren();

  const totalHalaman = Math.max(
    1,
    Math.ceil(totalItem / jumlahPerHalaman)
  );

  const sebelumnya = document.createElement('button');
  sebelumnya.type = 'button';
  sebelumnya.dataset.pageAction = 'previous';
  sebelumnya.textContent = 'Sebelumnya';
  sebelumnya.disabled = halamanAktif <= 1;

  const statusHalaman = document.createElement('span');
  statusHalaman.textContent =
    `Halaman ${halamanAktif} dari ${totalHalaman} (${totalItem} item)`;
  statusHalaman.setAttribute('aria-live', 'polite');

  const berikutnya = document.createElement('button');
  berikutnya.type = 'button';
  berikutnya.dataset.pageAction = 'next';
  berikutnya.textContent = 'Berikutnya';
  berikutnya.disabled = halamanAktif >= totalHalaman;

  kontrolHalaman.append(
    sebelumnya,
    statusHalaman,
    berikutnya
  );
}

// ========================================
// 8. MENAMPILKAN KARTU INVENTARIS
// ========================================
function renderItems(items) {
  if (!daftarAlat) return;

  daftarAlat.replaceChildren();

  const totalHalaman = Math.max(
    1,
    Math.ceil(items.length / jumlahPerHalaman)
  );

  // Pastikan halaman aktif tetap berada dalam batas yang benar.
  halamanAktif = Math.min(halamanAktif, totalHalaman);

  renderKontrolHalaman(items.length);

  if (items.length === 0) {
    const pesan = document.createElement('p');
    pesan.textContent =
      'Tidak ada inventaris yang cocok dengan pencarian atau filter ini.';

    daftarAlat.append(pesan);
    return;
  }

  const awal = (halamanAktif - 1) * jumlahPerHalaman;
  const itemHalaman = items.slice(
    awal,
    awal + jumlahPerHalaman
  );

  itemHalaman.forEach(item => {
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

    const kondisi = document.createElement('p');
    kondisi.textContent = `Kondisi: ${item.kondisi}`;

    const lokasi = document.createElement('p');
    lokasi.textContent = `Lokasi: ${item.lokasi}`;

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

// ========================================
// 9. PENCARIAN DAN FILTER INVENTARIS
// ========================================
function perbaruiDaftar() {
  const kataKunci =
    inputCari?.value.trim().toLocaleLowerCase('id') ?? '';

  const hasil = inventaris.filter(item => {
    const cocokKondisi =
      filterAktif === 'Semua' ||
      item.kondisi === filterAktif;

    // Cari berdasarkan nama, kategori, kondisi, dan lokasi.
    const teksInventaris = [
      item.nama,
      item.kategori,
      item.kondisi,
      item.lokasi
    ].join(' ').toLocaleLowerCase('id');

    const cocokPencarian =
      teksInventaris.includes(kataKunci);

    return cocokKondisi && cocokPencarian;
  });

  renderItems(hasil);
}

// Mengaktifkan filter kondisi inventaris.
function pilihFilter(kondisiPilihan) {
  filterAktif = kondisiPilihan;
  halamanAktif = 1;

  perbaruiDaftar();

  tombolFilter.forEach(tombol => {
    const terpilih =
      tombol.dataset.filter === kondisiPilihan;

    tombol.setAttribute(
      'aria-pressed',
      String(terpilih)
    );
  });
}

tombolFilter.forEach(tombol => {
  tombol.addEventListener('click', () => {
    pilihFilter(tombol.dataset.filter);
  });
});

// Filter awal.
pilihFilter('Semua');
loadInventory();

// Pencarian inventaris.
inputCari?.addEventListener('input', () => {
  halamanAktif = 1;
  perbaruiDaftar();
});

// ========================================
// 10. JUMLAH ITEM PER HALAMAN
// ========================================
pilihanJumlah?.addEventListener('change', () => {
  const nilai = Number(pilihanJumlah.value);

  if (!pilihanJumlahValid.includes(nilai)) return;

  jumlahPerHalaman = nilai;
  halamanAktif = 1;

  try {
    localStorage.setItem(
      kunciJumlahPerHalaman,
      String(jumlahPerHalaman)
    );
  } catch (error) {
    // Pilihan tetap berlaku selama halaman digunakan.
  }

  perbaruiDaftar();
});

// Tombol halaman sebelumnya dan berikutnya.
kontrolHalaman?.addEventListener('click', event => {
  const tombol = event.target.closest(
    'button[data-page-action]'
  );

  if (!tombol || tombol.disabled) return;

  if (tombol.dataset.pageAction === 'next') {
    halamanAktif += 1;
  } else if (tombol.dataset.pageAction === 'previous') {
    halamanAktif -= 1;
  }

  perbaruiDaftar();
});

// ========================================
// 11. MEMBUKA DAN MENUTUP DETAIL INVENTARIS
// ========================================
daftarAlat?.addEventListener('click', event => {
  const tombol = event.target.closest(
    'button[data-action="detail"]'
  );

  if (!tombol || !daftarAlat.contains(tombol)) return;

  const panelDetail = tombol
    .closest('.card')
    ?.querySelector('.item-detail');

  if (!panelDetail) return;

  const dibuka = panelDetail.hidden;

  panelDetail.hidden = !dibuka;
  tombol.textContent = dibuka
    ? 'Tutup Detail'
    : 'Detail';

  tombol.setAttribute(
    'aria-expanded',
    String(dibuka)
  );
});

// ========================================
// 12. PENGATURAN TEMA TERANG DAN GELAP
// ========================================
const kunciTema = 'Kost-theme-preference';
const tombolTema = document.querySelector('#theme-button');

function terapkanTema(tema) {
  const gelap = tema === 'dark';

  if (gelap) {
    document.documentElement.dataset.theme = 'dark';
  } else {
    delete document.documentElement.dataset.theme;
  }

  if (!tombolTema) return;

  tombolTema.setAttribute(
    'aria-pressed',
    String(gelap)
  );

  const label = gelap
    ? 'Ganti ke tema terang'
    : 'Ganti ke tema gelap';

  tombolTema.setAttribute('aria-label', label);
  tombolTema.title = label;

  tombolTema.innerHTML = gelap
    ? '<svg class="theme-icon" viewBox="0 0 24 24" aria-hidden="true"><path d="M20.2 15.1A8.5 8.5 0 0 1 8.9 3.8 8.6 8.6 0 1 0 20.2 15.1Z"/></svg>'
    : '<svg class="theme-icon" viewBox="0 0 24 24" aria-hidden="true"><circle cx="12" cy="12" r="4"/><path d="M12 2v2m0 16v2M4.93 4.93l1.42 1.42m11.3 11.3 1.42 1.42M2 12h2m16 0h2M4.93 19.07l1.42-1.42m11.3-11.3 1.42-1.42"/></svg>';
}

// Memuat tema yang sebelumnya dipilih.
try {
  const temaTersimpan = localStorage.getItem(kunciTema);

  terapkanTema(
    temaTersimpan === 'dark' ? 'dark' : 'light'
  );
} catch (error) {
  terapkanTema('light');
}

// Mengganti tema ketika tombol ditekan.
tombolTema?.addEventListener('click', () => {
  const temaBaru =
    document.documentElement.dataset.theme === 'dark'
      ? 'light'
      : 'dark';

  terapkanTema(temaBaru);

  try {
    localStorage.setItem(kunciTema, temaBaru);
  } catch (error) {
    // Tema tetap berubah untuk sesi ini.
  }
});

// ========================================
// 13. MEMBACA DATA FORMULIR
// ========================================
export function readFormData(form) {
  const formData = new FormData(form);

  return {
    nama: String(formData.get('nama') ?? '').trim(),
    telepon: String(formData.get('telepon') ?? '').trim(),
    email: String(formData.get('email') ?? '').trim(),
    pilihan_pintu: String(
      formData.get('pilihan_pintu') ?? ''
    ).trim(),
    tanggal_survei: String(
      formData.get('tanggal_survei') ?? ''
    ).trim(),
    pesan: String(formData.get('pesan') ?? '').trim()
  };
}

// ========================================
// 14. VALIDASI FORMULIR MINAT SEWA
// ========================================
export function validateForm(formData) {
  const errors = {};

  // Pilihan dibentuk dari data unit yang benar-benar tersedia di database.
  const unitTersedia = [...(document.querySelector('#pilihan-pintu')?.options ?? [])]
    .filter(option => option.value !== '')
    .map(option => option.value);

  // Tanggal hari ini berdasarkan waktu lokal.
  const now = new Date();
  const today = new Date(
    now.getTime() - now.getTimezoneOffset() * 60000
  ).toISOString().slice(0, 10);

  // Validasi nama.
  if (!formData.nama) {
    errors.nama = 'Nama lengkap wajib diisi.';
  } else if (!/^[\p{L} ]{3,}$/u.test(formData.nama)) {
    errors.nama =
      'Nama harus berisi minimal 3 huruf dan spasi saja.';
  }

  // Validasi nomor WhatsApp.
  if (!formData.telepon) {
    errors.telepon = 'Nomor WhatsApp wajib diisi.';
  } else if (!/^08\d{8,12}$/.test(formData.telepon)) {
    errors.telepon =
      'Gunakan angka saja, diawali 08, dengan panjang 10–14 digit.';
  }

  // Validasi email.
  if (!formData.email) {
    errors.email = 'Email wajib diisi.';
  } else if (!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(formData.email)) {
    errors.email =
      'Format email tidak valid. Contoh: nama@contoh.com.';
  }

  // Validasi unit yang dipilih.
  if (!formData.pilihan_pintu) {
    errors['pilihan-pintu'] = 'Unit pintu wajib dipilih.';
  } else if (!unitTersedia.includes(formData.pilihan_pintu)) {
    errors['pilihan-pintu'] =
      'Pilih unit yang tersedia dari daftar.';
  }

  // Validasi tanggal: tanggal lampau tidak diperbolehkan.
  if (formData.tanggal_survei) {
    const tanggal = formData.tanggal_survei;
    const tanggalValid = /^\d{4}-\d{2}-\d{2}$/.test(tanggal);

    if (!tanggalValid) {
      errors['tanggal-survei'] =
        'Format tanggal rencana tidak valid.';
    } else {
      const tanggalObj = new Date(`${tanggal}T00:00:00`);

      if (
        Number.isNaN(tanggalObj.getTime()) ||
        tanggalObj.toISOString().slice(0, 10) !== tanggal
      ) {
        errors['tanggal-survei'] =
          'Tanggal yang dimasukkan tidak valid.';
      } else if (tanggal < today) {
        errors['tanggal-survei'] =
          'Tanggal survei tidak boleh sebelum hari ini.';
      }
    }
  }

  return errors;
}

// ========================================
// 15. PENGATURAN FORMULIR MINAT SEWA
// ========================================
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

// Batasi tanggal paling awal ke hari ini.
const inputTanggalSurvei =
  document.querySelector('#tanggal-survei');

if (inputTanggalSurvei) {
  const now = new Date();

  inputTanggalSurvei.min = new Date(
    now.getTime() - now.getTimezoneOffset() * 60000
  ).toISOString().slice(0, 10);
}

// Memperbarui pesan kesalahan pada satu field.
function perbaruiFeedbackField(field, id) {
  const input = document.getElementById(id);
  const errorText = document.getElementById(`${id}-error`);

  if (!input || !errorText || !formulir) return;

  const error = validateForm(
    readFormData(formulir)
  )[field];

  input.setAttribute(
    'aria-invalid',
    String(Boolean(error))
  );

  errorText.textContent = error ?? '';
}

// Memeriksa field ketika pengguna mengetik atau berpindah field.
Object.entries(fieldIds).forEach(([field, id]) => {
  const input = document.getElementById(id);

  input?.addEventListener('input', () => {
    perbaruiFeedbackField(field, id);
  });

  input?.addEventListener('change', () => {
    perbaruiFeedbackField(field, id);
  });

  input?.addEventListener('blur', () => {
    perbaruiFeedbackField(field, id);
  });
});

// ========================================
// 16. PROSES PRATINJAU FORMULIR
// ========================================
formulir?.addEventListener('submit', event => {
  event.preventDefault();

  const fields = Object.entries(fieldIds);

  // Bersihkan pesan kesalahan sebelumnya.
  fields.forEach(([, id]) => {
    document.getElementById(id)?.setAttribute(
      'aria-invalid',
      'false'
    );

    const errorText = document.getElementById(`${id}-error`);

    if (errorText) {
      errorText.textContent = '';
    }
  });

  const data = readFormData(formulir);
  const errors = validateForm(data);

  const invalidFields = fields.filter(
    ([field]) => errors[field]
  );

  // Jika ada kesalahan, tampilkan pesan dan fokuskan field pertama.
  if (invalidFields.length > 0) {
    invalidFields.forEach(([field, id]) => {
      document.getElementById(id)?.setAttribute(
        'aria-invalid',
        'true'
      );

      const errorText =
        document.getElementById(`${id}-error`);

      if (errorText) {
        errorText.textContent = errors[field];
      }
    });

    document.getElementById(
      invalidFields[0][1]
    )?.focus();

    return;
  }

  if (!itemDaftarMinat || !daftarMinat) return;

  // Ambil teks unit yang dipilih.
  const labelUnit =
    formulir.elements.pilihan_pintu
      .selectedOptions[0]?.textContent.trim()
    ?? data.pilihan_pintu;

  // Data yang akan ditampilkan pada pratinjau.
  const labels = [
    ['Nama Lengkap', data.nama],
    ['Nomor WhatsApp', data.telepon],
    ['Email', data.email],
    ['Unit Pintu', labelUnit],
    [
      'Tanggal Rencana Survei',
      data.tanggal_survei || 'Tidak diisi'
    ],
    ['Pesan', data.pesan || 'Tidak ada pesan']
  ];

  const item = document.createElement('li');
  item.className = 'interest-entry';

  const judul = document.createElement('h4');
  judul.textContent =
    `Minat sewa ${itemDaftarMinat.children.length + 1}`;

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

  // Tampilkan ringkasan data minat.
  daftarMinat.hidden = false;
});

// ========================================
// 17. MENJALANKAN APLIKASI
// ========================================
loadUsers();
window.addEventListener('kost:units-changed', loadUsers);
