const key = 'Kost-theme-preference';
const button = document.querySelector('#theme-button');

function apply(theme) {
  const dark = theme === 'dark';
  if (dark) document.documentElement.dataset.theme = 'dark';
  else delete document.documentElement.dataset.theme;
  button?.setAttribute('aria-pressed', String(dark));
  const label = dark ? 'Ganti ke tema terang' : 'Ganti ke tema gelap';
  button?.setAttribute('aria-label', label);
  if (button) button.title = label;
}

try { apply(localStorage.getItem(key) === 'dark' ? 'dark' : 'light'); }
catch { apply('light'); }
button?.addEventListener('click', () => {
  const next = document.documentElement.dataset.theme === 'dark' ? 'light' : 'dark';
  apply(next);
  try { localStorage.setItem(key, next); } catch { /* Theme remains active for this page. */ }
});
