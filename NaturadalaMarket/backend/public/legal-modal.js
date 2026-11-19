(function () {
  const dialog = document.getElementById('legal-dialog');
  if (!dialog) return;

  const body = document.getElementById('legal-dialog-body');
  const title = document.getElementById('legal-dialog-title');
  const accept = document.getElementById('legal-dialog-accept');
  const checkbox = document.getElementById('terms-checkbox');
  const urls = {
    terms: document.querySelector('a[data-legal-open="terms"]').href,
    privacy: document.querySelector('a[data-legal-open="privacy"]').href,
  };
  const cache = {};
  let opener = null;

  async function load(kind) {
    if (cache[kind]) return cache[kind];
    const response = await fetch(urls[kind], { headers: { Accept: 'text/html' }, credentials: 'same-origin' });
    if (!response.ok) throw new Error('HTTP ' + response.status);
    const doc = new DOMParser().parseFromString(await response.text(), 'text/html');
    const article = doc.querySelector('.legal-document');
    cache[kind] = {
      title: article.querySelector('h1').textContent,
      html: (article.querySelector('.legal-review-note')?.outerHTML || '') + article.querySelector('.legal-body').innerHTML,
    };
    return cache[kind];
  }

  async function open(kind, trigger) {
    opener = trigger;
    title.textContent = kind === 'terms' ? 'Regulamin' : 'Polityka prywatności';
    body.textContent = 'Ładowanie…';
    if (!dialog.open) dialog.showModal();
    body.scrollTop = 0;
    try {
      const doc = await load(kind);
      title.textContent = doc.title;
      body.innerHTML = doc.html;
      body.scrollTop = 0;
    } catch (error) {
      body.innerHTML = '<p>Nie udało się wczytać dokumentu. <a href="' + urls[kind] + '" target="_blank" rel="noreferrer">Otwórz go w nowej karcie</a>.</p>';
    }
  }

  document.querySelectorAll('[data-legal-open]').forEach((el) => {
    el.addEventListener('click', (event) => {
      event.preventDefault();
      open(el.dataset.legalOpen, el);
    });
  });

  dialog.querySelectorAll('[data-legal-close]').forEach((el) => el.addEventListener('click', () => dialog.close()));
  dialog.addEventListener('click', (event) => { if (event.target === dialog) dialog.close(); });
  dialog.addEventListener('keydown', (event) => { if (event.key === 'Escape') { event.preventDefault(); dialog.close(); } });
  dialog.addEventListener('close', () => opener && opener.focus());
  accept.addEventListener('click', () => {
    if (checkbox) checkbox.checked = true;
    dialog.close();
  });
})();
