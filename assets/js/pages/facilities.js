import { apiRequest } from '../core/api.js';
import { notify } from '../components/notification.js';

const list = document.querySelector('#facility-list');
const message = document.querySelector('#facility-message');

async function loadFacilities() {
  notify(message, 'Memuat fasilitas…', 'loading');
  try {
    const result = await apiRequest('./api/facilities.php');
    list?.replaceChildren();
    for (const facility of result.data) {
      const card = document.createElement('article');
      card.className = 'card';
      const title = document.createElement('h2');
      title.textContent = facility.nama;
      const detail = document.createElement('p');
      detail.textContent = `${facility.kategori} · ${facility.jumlah} unit · ${facility.kondisi} · ${facility.lokasi}`;
      card.append(title, detail);
      list?.append(card);
    }
    if (!result.data.length && list) list.textContent = 'Belum ada data fasilitas.';
    notify(message, `${result.data.length} item fasilitas dimuat.`, 'success');
  } catch (error) {
    notify(message, error.message, 'error');
  }
}

loadFacilities();
