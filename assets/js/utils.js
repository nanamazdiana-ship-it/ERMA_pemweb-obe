// File: assets/js/utils.js

/**
 * Menerima array data inventaris dan mengembalikan objek ringkasan
 */
export function ringkasInventaris(data) {
  if (!Array.isArray(data)) {
    return { jenisAlat: 0, totalUnit: 0, perluCek: 0 };
  }

  return {
    jenisAlat: data.length,
    totalUnit: data.reduce((sum, item) => sum + item.jumlah, 0),
    perluCek: data.filter(item => item.kondisi !== 'Baik').length
  };
}