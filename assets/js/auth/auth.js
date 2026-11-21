import { apiRequest, setCsrfToken } from '../core/api.js';

const endpoint = './api/auth.php';
const form = document.querySelector('#login-form');
const profile = document.querySelector('#profil');
const message = document.querySelector('#auth-message');
const userName = document.querySelector('#profil-nama');
const userEmail = document.querySelector('#profil-email');
const userRole = document.querySelector('#profil-role');
const logoutButton = document.querySelector('#logout-btn');
const sewaSection = document.querySelector('#sewa-saya');
const sewaList = document.querySelector('#sewa-list');

let currentUser = null;

async function requestAuth(body, method = 'POST') {
  const result = await apiRequest(endpoint, { method, ...(body ? { body } : {}) });
  if (result?.data?.csrf_token) setCsrfToken(result.data.csrf_token);
  return result.data;
}

function renderAccount() {
  window.kostAuthState = { user: currentUser };
  if (form) form.hidden = Boolean(currentUser);
  if (profile) profile.hidden = !currentUser;
  window.dispatchEvent(new CustomEvent('kost:auth-changed', { detail: { user: currentUser } }));
  if (!currentUser) return;
  if (userName) userName.textContent = currentUser.nama;
  if (userEmail) userEmail.textContent = currentUser.email;
  if (userRole) userRole.textContent = currentUser.role;
  if (sewaSection) sewaSection.hidden = currentUser.role !== 'penghuni';
  if (currentUser.role === 'penghuni') loadTenantData();
}

function formatRupiah(value) {
  return `Rp ${Number(value).toLocaleString('id-ID')}`;
}

async function loadTenantData() {
  if (!sewaList) return;
  sewaList.replaceChildren();
  try {
    const response = await fetch('./api/account.php', { headers: { Accept: 'application/json' }, credentials: 'same-origin' });
    const result = await response.json();
    if (!response.ok) throw new Error(result.error?.message || 'Informasi sewa belum dapat dimuat.');
    const records = [
      ...(result.data.leases || []).map((lease) => ({
        label: `Sewa ${lease.kode_pintu} · ${lease.tanggal_mulai}–${lease.tanggal_selesai} · ${lease.status} · ${formatRupiah(lease.harga_sewa)}`
      })),
      ...(result.data.payments || []).map((payment) => ({
        label: `Pembayaran ${payment.tanggal_bayar} · ${payment.status} · ${formatRupiah(payment.jumlah_bayar)}${payment.keterangan ? ` · ${payment.keterangan}` : ''}`
      }))
    ];
    if (records.length === 0) records.push({ label: 'Belum ada catatan sewa atau pembayaran.' });
    records.forEach(({ label }) => {
      const item = document.createElement('li');
      item.textContent = label;
      sewaList.append(item);
    });
  } catch (error) {
    const item = document.createElement('li');
    item.textContent = error.message;
    sewaList.append(item);
  }
}

function showMessage(text, kind = '') {
  if (!message) return;
  message.textContent = text;
  message.className = `status-message ${kind}`.trim();
}

form?.addEventListener('submit', async (event) => {
  event.preventDefault();
  const email = document.querySelector('#login-email')?.value.trim() || '';
  const passwordInput = document.querySelector('#login-password');
  const submit = document.querySelector('#login-submit');
  if (submit) submit.disabled = true;
  showMessage('Memeriksa akun…', 'loading');
  try {
    const result = await requestAuth({ action: 'login', email, password: passwordInput?.value || '' });
    currentUser = result.user;
    form.reset();
    showMessage(`Berhasil masuk sebagai ${currentUser.nama}.`, 'success');
    renderAccount();
    window.dispatchEvent(new CustomEvent('kost:csrf-refresh', { detail: { token: result.csrf_token } }));
  } catch (error) {
    showMessage(error.message, 'error');
  } finally {
    if (submit) submit.disabled = false;
  }
});

logoutButton?.addEventListener('click', async () => {
  logoutButton.disabled = true;
  try {
    await requestAuth({ action: 'logout' });
    currentUser = null;
    const session = await requestAuth(null, 'GET');
    showMessage('Anda sudah keluar.', 'success');
    renderAccount();
    window.dispatchEvent(new CustomEvent('kost:csrf-refresh', { detail: { token: session.csrf_token } }));
  } catch (error) {
    showMessage(error.message, 'error');
  } finally {
    logoutButton.disabled = false;
  }
});

try {
  const result = await requestAuth(null, 'GET');
  currentUser = result.user;
  renderAccount();
  window.dispatchEvent(new CustomEvent('kost:csrf-ready', { detail: { token: result.csrf_token } }));
} catch (error) {
  showMessage('Status akun belum dapat dimuat. Periksa koneksi ke server.', 'error');
}

window.addEventListener('kost:csrf-refresh', (event) => {
  if (event.detail?.token) {
    setCsrfToken(event.detail.token);
    window.kostAuthState = { user: currentUser };
  }
});
