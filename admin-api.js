const AdminApi = (() => {
  const scriptUrl = document.currentScript?.src || '';
  const root = scriptUrl ? new URL('.', scriptUrl).pathname.replace(/\/$/, '') : '';
  const apiRoot = `${root}/api`.replace(/\/{2,}/g, '/') || '/api';
  const request = async (path, options = {}) => {
    const response = await fetch(`${apiRoot}${path}`, {
      ...options,
      credentials: 'same-origin',
      headers: { 'Content-Type': 'application/json', ...(options.headers || {}) },
    });
    const data = await response.json().catch(() => ({}));
    if (!response.ok) {
      const error = new Error(data.error || 'Request failed.');
      error.status = response.status;
      throw error;
    }
    return data;
  };
  return {
    session: () => request('/admin-session.php'),
    login: password => request('/admin-login.php', { method: 'POST', body: JSON.stringify({ password }) }),
    logout: () => request('/admin-logout.php', { method: 'POST' }),
    emails: () => request('/admin-emails.php'),
  };
})();
