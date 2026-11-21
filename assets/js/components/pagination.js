export function updatePagination({ previous, next, info, page, totalPages, total }) {
  if (previous) previous.disabled = page <= 1;
  if (next) next.disabled = page >= totalPages;
  if (info) info.textContent = `Halaman ${page} dari ${totalPages} (${total} item)`;
}
