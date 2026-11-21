export function notify(element, text, type = '') {
  if (!element) return;
  element.textContent = text;
  element.className = `status-message ${type}`.trim();
}
