import { apiRequest } from '../core/api.js';
import { notify } from '../components/notification.js';

const adminBody = document.querySelector('#complaints-body');
const tenantList = document.querySelector('#complaints-list');
const message = document.querySelector('#complaints-message');
const form = document.querySelector('#complaint-form');

function renderTenantComplaint(item) {
  const card = document.createElement('article');
  card.className = 'card';
  const title = document.createElement('h2');
  title.textContent = item.judul;
  const description = document.createElement('p');
  description.textContent = item.deskripsi;
  const status = document.createElement('p');
  status.textContent = `Status: ${item.status}`;
  card.append(title, description, status);
  tenantList?.append(card);
}

async function loadComplaints() {
  notify(message, 'Memuat pengaduan…', 'loading');
  try {
    const result = await apiRequest('./api/complaints.php');
    adminBody?.replaceChildren();
    tenantList?.replaceChildren();
    for (const complaint of result.data) {
      if (tenantList) {
        renderTenantComplaint(complaint);
        continue;
      }
      const row = document.createElement('tr');
      const values = [complaint.created_at, complaint.nama_lengkap, complaint.judul, complaint.deskripsi];
      for (const value of values) {
        const cell = document.createElement('td');
        cell.textContent = value;
        row.append(cell);
      }
      const statusCell = document.createElement('td');
      const select = document.createElement('select');
      select.setAttribute('aria-label', `Status pengaduan ${complaint.id}`);
      for (const value of ['Baru', 'Diproses', 'Selesai']) {
        const option = new Option(value, value, false, value === complaint.status);
        select.add(option);
      }
      statusCell.append(select);
      const actionCell = document.createElement('td');
      const save = document.createElement('button');
      save.type = 'button';
      save.textContent = 'Simpan';
      save.addEventListener('click', async () => {
        save.disabled = true;
        try {
          await apiRequest(`./api/complaints.php?id=${encodeURIComponent(complaint.id)}`, { method: 'PATCH', body: { status: select.value } });
          notify(message, 'Status pengaduan diperbarui.', 'success');
        } catch (error) { notify(message, error.message, 'error'); }
        finally { save.disabled = false; }
      });
      actionCell.append(save);
      row.append(statusCell, actionCell);
      adminBody?.append(row);
    }
    notify(message, `${result.data.length} pengaduan dimuat.`, 'success');
  } catch (error) {
    notify(message, error.message, 'error');
  }
}

form?.addEventListener('submit', async (event) => {
  event.preventDefault();
  const title = document.querySelector('#complaint-title');
  const description = document.querySelector('#complaint-description');
  const submit = form.querySelector('button[type="submit"]');
  if (submit) submit.disabled = true;
  try {
    await apiRequest('./api/complaints.php', { method: 'POST', body: { judul: title.value.trim(), deskripsi: description.value.trim() } });
    form.reset();
    notify(message, 'Pengaduan berhasil dikirim.', 'success');
    await loadComplaints();
  } catch (error) {
    notify(message, error.message, 'error');
  } finally {
    if (submit) submit.disabled = false;
  }
});

loadComplaints();
