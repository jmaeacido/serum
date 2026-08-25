(() => {
  AdminApi.session().then(data => { if (!data.authenticated) location.replace('login/'); }).catch(() => location.replace('login/'));
  document.querySelector('[data-logout]').addEventListener('click', async () => { try { await AdminApi.logout(); } finally { location.href = 'login/'; } });
})();
