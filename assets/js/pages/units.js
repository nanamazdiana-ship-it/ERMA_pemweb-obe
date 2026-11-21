import { apiRequest } from '../core/api.js';
import { notify } from '../components/notification.js';
import { confirmAction } from '../components/modal.js';
import { updatePagination } from '../components/pagination.js';

const api = './api/units.php';
const form = document.querySelector('#crud-form');
const tbody = document.querySelector('#crud-tbody');
const message = document.querySelector('#crud-message');
const note = document.querySelector('#crud-readonly-note');
const search = document.querySelector('#crud-cari');
const statusFilter = document.querySelector('#crud-filter-status');
const perPageSelect = document.querySelector('#crud-per-page');
const pageInfo = document.querySelector('#crud-page-info');
const previousButton = document.querySelector('#crud-prev');
const nextButton = document.querySelector('#crud-next');
const cancelButton = document.querySelector('#crud-cancel');
const submitButton = document.querySelector('#crud-submit');
const fields = {
  kode: document.querySelector('#crud-kode'),
  nama: document.querySelector('#crud-nama'),
  status: document.querySelector('#crud-status'),
  harga: document.querySelector('#crud-harga'),
  fasilitas: document.querySelector('#crud-fasilitas')
};

let page = 1;
let totalPages = 1;
let user = null;
let requestSequence = 0;
let debounceTimer;

function setMessage(text, type = '') {
  notify(message, text, type);
}

function renderAccess() {
  const canWrite = ['admin', 'petugas'].includes(user?.role);
  const canDelete = user?.role === 'admin';
  if (form) form.hidden = !canWrite;
  if (note) {
    note.hidden = canWrite;
    note.textContent = user
      ? `Akun ${user.role} hanya dapat melihat data unit.`
      : 'Masuk sebagai petugas atau admin untuk menambah dan mengubah unit.';
  }
  tbody?.querySelectorAll('[data-action="edit"]').forEach((button) => { button.hidden = !canWrite; });
  tbody?.querySelectorAll('[data-action="delete"]').forEach((button) => { button.hidden = !canDelete; });
}

function addCell(row, text) {
  const cell = document.createElement('td');
  cell.textContent = text;
  row.append(cell);
  return cell;
}

function renderRows(units) {
  if (!tbody) return;
  tbody.replaceChildren();
  if (units.length === 0) {
    const row = document.createElement('tr');
    const cell = document.createElement('td');
    cell.colSpan = 5;
    cell.textContent = 'Belum ada unit yang cocok dengan pencarian.';
    row.append(cell);
    tbody.append(row);
    return;
  }
  for (const unit of units) {
    const row = document.createElement('tr');
    row.dataset.id = String(unit.id);
    addCell(row, unit.kode);
    addCell(row, unit.nama);
    const badgeCell = addCell(row, unit.status);
    badgeCell.className = `status-${unit.status === 'Tersedia' ? 'available' : 'occupied'}`;
    addCell(row, `Rp ${Number(unit.harga).toLocaleString('id-ID')}`);
    const actions = document.createElement('td');
    for (const [action, label] of [['edit', 'Ubah'], ['delete', 'Hapus']]) {
      const button = document.createElement('button');
      button.type = 'button';
      button.dataset.action = action;
      button.textContent = label;
      button.hidden = action === 'edit' ? !['admin', 'petugas'].includes(user?.role) : user?.role !== 'admin';
      actions.append(button);
    }
    row.append(actions);
    tbody.append(row);
  }
}

async function loadUnits() {
  if (!tbody) return;
  const sequence = ++requestSequence;
  setMessage('Memuat unit…', 'loading');
  if (submitButton) submitButton.disabled = true;
  const params = new URLSearchParams({
    page: String(page),
    per_page: perPageSelect?.value || '10',
    q: search?.value.trim() || '',
    status: statusFilter?.value || ''
  });
  try {
    const result = await apiRequest(`${api}?${params}`);
    if (sequence !== requestSequence) return;
    renderRows(result.data);
    totalPages = result.meta.total_pages;
    page = result.meta.page;
    updatePagination({ previous: previousButton, next: nextButton, info: pageInfo, page, totalPages, total: result.meta.total });
    setMessage(result.meta.total ? '' : 'Belum ada data unit.', result.meta.total ? '' : '');
    renderAccess();
  } catch (error) {
    if (sequence !== requestSequence) return;
    renderRows([]);
    setMessage(error.message, 'error');
  } finally {
    if (sequence === requestSequence && submitButton) submitButton.disabled = false;
  }
}

function clearErrors() {
  for (const [name, input] of Object.entries(fields)) {
    input?.setAttribute('aria-invalid', 'false');
    const error = document.querySelector(`#crud-${name}-error`);
    if (error) error.textContent = '';
  }
}

function showErrors(errors = {}) {
  clearErrors();
  for (const [name, text] of Object.entries(errors)) {
    const input = fields[name];
    const error = document.querySelector(`#crud-${name}-error`);
    if (input) input.setAttribute('aria-invalid', 'true');
    if (error) error.textContent = text;
  }
  const first = Object.keys(errors)[0];
  fields[first]?.focus();
}

function resetForm() {
  form?.reset();
  const id = document.querySelector('#crud-id');
  if (id) id.value = '';
  const title = document.querySelector('#crud-form-title');
  if (title) title.textContent = 'Tambah Unit Baru';
  if (submitButton) submitButton.textContent = 'Simpan Unit';
  if (cancelButton) cancelButton.hidden = true;
  clearErrors();
}

form?.addEventListener('submit', async (event) => {
  event.preventDefault();
  clearErrors();
  const id = document.querySelector('#crud-id')?.value;
  const payload = Object.fromEntries(Object.entries(fields).map(([key, input]) => [key, input.value.trim()]));
  if (submitButton) submitButton.disabled = true;
  try {
    await apiRequest(id ? `${api}?id=${encodeURIComponent(id)}` : api, {
      method: id ? 'PUT' : 'POST',
      body: JSON.stringify(payload)
    });
    setMessage(id ? 'Unit berhasil diperbarui.' : 'Unit berhasil ditambahkan.', 'success');
    resetForm();
    page = 1;
    await loadUnits();
    window.dispatchEvent(new CustomEvent('kost:units-changed'));
  } catch (error) {
    if (Object.keys(error.fields).length) showErrors(error.fields);
    setMessage(error.message, 'error');
  } finally {
    if (submitButton) submitButton.disabled = false;
  }
});

tbody?.addEventListener('click', async (event) => {
  const button = event.target.closest('button[data-action]');
  const row = button?.closest('tr[data-id]');
  if (!button || !row) return;
  const id = row.dataset.id;
  if (button.dataset.action === 'edit') {
    try {
      const { data: unit } = await apiRequest(`${api}?id=${encodeURIComponent(id)}`);
      document.querySelector('#crud-id').value = unit.id;
      for (const [key, input] of Object.entries(fields)) input.value = unit[key] ?? '';
      document.querySelector('#crud-form-title').textContent = `Ubah Unit ${unit.nama}`;
      if (submitButton) submitButton.textContent = 'Simpan Perubahan';
      if (cancelButton) cancelButton.hidden = false;
      fields.kode?.focus();
    } catch (error) { setMessage(error.message, 'error'); }
    return;
  }
  if (button.dataset.action === 'delete' && confirmAction('Hapus unit ini? Tindakan ini tidak dapat dibatalkan.')) {
    button.disabled = true;
    try {
      await apiRequest(`${api}?id=${encodeURIComponent(id)}`, { method: 'DELETE' });
      setMessage('Unit berhasil dihapus.', 'success');
      await loadUnits();
      window.dispatchEvent(new CustomEvent('kost:units-changed'));
    } catch (error) { setMessage(error.message, 'error'); button.disabled = false; }
  }
});

cancelButton?.addEventListener('click', resetForm);
previousButton?.addEventListener('click', () => { if (page > 1) { page -= 1; loadUnits(); } });
nextButton?.addEventListener('click', () => { if (page < totalPages) { page += 1; loadUnits(); } });
search?.addEventListener('input', () => { clearTimeout(debounceTimer); debounceTimer = setTimeout(() => { page = 1; loadUnits(); }, 250); });
statusFilter?.addEventListener('change', () => { page = 1; loadUnits(); });
perPageSelect?.addEventListener('change', () => { page = 1; loadUnits(); });

window.addEventListener('kost:auth-changed', (event) => { user = event.detail?.user || null; renderAccess(); loadUnits(); });
if (window.kostAuthState) {
  user = window.kostAuthState.user || null;
  renderAccess();
}
loadUnits();
