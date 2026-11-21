import { apiRequest } from '../core/api.js';
import { notify } from '../components/notification.js';

const body = document.querySelector('#payments-body');
const message = document.querySelector('#payments-message');

async function loadPayments() {
  notify(message, 'Memuat pembayaran…', 'loading');
  try {
    const result = await apiRequest('./api/payments.php');
    body?.replaceChildren();
    for (const payment of result.data) {
      const row = document.createElement('tr');
      const values = [payment.tanggal_bayar, payment.nama_lengkap || '', `Rp ${Number(payment.jumlah_bayar).toLocaleString('id-ID')}`, payment.status, payment.keterangan || '—'];
      for (const value of values) {
        const cell = document.createElement('td');
        cell.textContent = value;
        row.append(cell);
      }
      body?.append(row);
    }
    notify(message, `${result.data.length} pembayaran dimuat.`, 'success');
  } catch (error) {
    notify(message, error.message, 'error');
  }
}

loadPayments();
