import { apiRequest } from '../core/api.js';
import { notify } from '../components/notification.js';

const body = document.querySelector('#tenants-body');
const message = document.querySelector('#tenants-message');

async function loadTenants() {
  notify(message, 'Memuat data penghuni…', 'loading');
  try {
    const result = await apiRequest('./api/tenants.php');
    body?.replaceChildren();
    for (const tenant of result.data) {
      const row = document.createElement('tr');
      for (const value of [tenant.id, tenant.nama_lengkap, tenant.no_hp, tenant.alamat || '—']) {
        const cell = document.createElement('td');
        cell.textContent = value;
        row.append(cell);
      }
      body?.append(row);
    }
    notify(message, `${result.data.length} penghuni dimuat.`, 'success');
  } catch (error) {
    notify(message, error.message, 'error');
  }
}

loadTenants();
