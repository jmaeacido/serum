(() => {
  let messages = [];
  const $ = selector => document.querySelector(selector);
  const escape = value => String(value ?? '').replace(/[&<>'"]/g, character => ({ '&':'&amp;','<':'&lt;','>':'&gt;',"'":'&#39;','"':'&quot;' })[character]);
  const date = value => { const parsed = new Date(value); return Number.isNaN(parsed.getTime()) ? value : parsed.toLocaleString(); };
  const render = () => {
    const query = $('[data-search]').value.toLowerCase().trim();
    const direction = $('[data-direction]').value;
    const rows = messages.filter(message => (!direction || message.direction === direction) && (!query || [message.from,message.to,message.subject,message.preview].join(' ').toLowerCase().includes(query)));
    $('[data-loading]').hidden = true; $('[data-empty]').hidden = rows.length > 0; $('[data-table]').hidden = !rows.length;
    $('[data-count]').textContent = `${rows.length} record${rows.length === 1 ? '' : 's'}`;
    $('[data-rows]').innerHTML = rows.map(message => `<tr><td><span class="direction direction--${escape(message.direction)}">${escape(message.direction)}</span></td><td class="muted">${escape(date(message.date))}</td><td><div><strong>From:</strong> ${escape(message.from || '—')}</div><div class="muted"><strong>To:</strong> ${escape(message.to || '—')}</div></td><td><div>${escape(message.subject || '(No subject)')}</div><div class="muted preview">${escape(message.preview || '')}</div></td><td class="${message.status === 'failed' ? 'status--failed' : 'muted'}">${escape(message.status || 'saved')}</td></tr>`).join('');
  };
  const load = async () => { try { const data = await AdminApi.emails(); messages = data.messages || []; render(); } catch (error) { if (error.status === 401) location.replace('../login/'); else $('[data-loading]').textContent = error.message; } };
  $('[data-search]').addEventListener('input', render); $('[data-direction]').addEventListener('change', render); $('[data-refresh]').addEventListener('click', load);
  $('[data-logout]').addEventListener('click', async () => { try { await AdminApi.logout(); } finally { location.href = '../login/'; } }); load();
})();
