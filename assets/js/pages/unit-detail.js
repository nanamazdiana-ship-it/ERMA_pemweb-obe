import { apiRequest } from '../core/api.js';

const output = document.querySelector('#unit-detail');
const message = document.querySelector('#unit-detail-message');
const id = new URLSearchParams(location.search).get('id');

try {
  if (!id || !/^\d+$/.test(id)) throw new Error('ID unit tidak valid.');
  const { data: unit } = await apiRequest(`./api/units.php?id=${encodeURIComponent(id)}`);
  const title = document.createElement('h2');
  title.textContent = unit.nama;
  const details = document.createElement('p');
  details.textContent = `${unit.kode} · ${unit.status} · Rp ${Number(unit.harga).toLocaleString('id-ID')} per bulan`;
  const facilities = document.createElement('p');
  facilities.textContent = unit.fasilitas || 'Informasi fasilitas belum tersedia.';
  output?.append(title, details, facilities);
  if (message) message.textContent = '';
} catch (error) {
  if (message) message.textContent = error.message;
}
