const email = document.querySelector('#login-email');
const password = document.querySelector('#login-password');
email?.focus({ preventScroll: true });
password?.addEventListener('keydown', (event) => {
  if (event.key === 'Enter') document.querySelector('#login-submit')?.click();
});
