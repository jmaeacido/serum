(() => {
  const form = document.querySelector('[data-login]');
  const error = document.querySelector('[data-error]');
  AdminApi.session().then(data => { if (data.authenticated) location.replace('../'); }).catch(() => {});
  form.addEventListener('submit', async event => {
    event.preventDefault(); error.textContent = '';
    const button = form.querySelector('button'); button.disabled = true;
    try { await AdminApi.login(form.password.value); location.replace('../'); }
    catch (exception) { error.textContent = exception.message; button.disabled = false; }
  });
})();
