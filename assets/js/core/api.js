let csrfToken = '';

export function setCsrfToken(token) {
  if (typeof token === 'string') csrfToken = token;
}

export async function apiRequest(url, options = {}) {
  const method = options.method || 'GET';
  const headers = new Headers(options.headers || {});
  headers.set('Accept', 'application/json');
  let body = options.body;
  if (body !== undefined && body !== null && typeof body !== 'string') {
    headers.set('Content-Type', 'application/json');
    body = JSON.stringify(body);
  } else if (typeof body === 'string' && !headers.has('Content-Type')) {
    headers.set('Content-Type', 'application/json');
  }
  if (method !== 'GET' && method !== 'HEAD' && csrfToken) headers.set('X-CSRF-Token', csrfToken);

  const response = await fetch(url, {
    credentials: 'same-origin',
    ...options,
    method,
    headers,
    ...(body !== undefined ? { body } : {})
  });
  if (response.status === 204) return null;

  const result = await response.json().catch(() => ({}));
  if (!response.ok) {
    const error = new Error(result.error?.message || `Permintaan gagal (${response.status}).`);
    error.status = response.status;
    error.code = result.error?.code || 'REQUEST_FAILED';
    error.fields = result.error?.fields || {};
    throw error;
  }
  return result;
}
