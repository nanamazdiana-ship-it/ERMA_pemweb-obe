import { apiRequest } from '../core/api.js';
import { notify } from '../components/notification.js';

const list = document.querySelector('#tenant-room-list');
const message = document.querySelector('#tenant-room-message');

try {
  const { data } = await apiRequest('./api/account.php');
  list?.replaceChildren();
  const records = data.leases || [];
  if (!records.length && list) list.textContent = 'Belum ada data sewa pada akun ini.';
  for (const lease of records) {
    const card = document.createElement('article');
    card.className = 'card';
    const heading = document.createElement('h2');
    heading.textContent = `${lease.nama_pintu} (${lease.kode_pintu})`;
    const dates = document.createElement('p');
    dates.textContent = `${lease.tanggal_mulai} sampai ${lease.tanggal_selesai}`;
    const status = document.createElement('p');
    status.textContent = `Status: ${lease.status} · Rp ${Number(lease.harga_sewa).toLocaleString('id-ID')} per bulan`;
    card.append(heading, dates, status);
    list?.append(card);
  }
  notify(message, `${records.length} catatan sewa dimuat.`, 'success');
} catch (error) {
  notify(message, error.message, 'error');
}
