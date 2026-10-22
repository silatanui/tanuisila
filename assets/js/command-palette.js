(function () {
  const links = [
    { label: 'Home', href: 'index.php', icon: 'fa-house' },
    { label: 'About', href: 'about.php', icon: 'fa-user' },
    { label: 'Projects', href: 'projects.php', icon: 'fa-code' },
    { label: 'Blog', href: 'blog.php', icon: 'fa-pen-nib' },
    { label: 'Now', href: 'now.php', icon: 'fa-bolt' },
    { label: 'Lab', href: 'lab.php', icon: 'fa-flask' },
    { label: 'SaaS', href: 'saas.php', icon: 'fa-rocket' },
    { label: 'Tools', href: 'tools.php', icon: 'fa-terminal' },
    { label: 'Reverse Engineering', href: 'reverse-engineering.php', icon: 'fa-microchip' },
    { label: 'Reading', href: 'reading.php', icon: 'fa-book' },
    { label: 'Uses', href: 'uses.php', icon: 'fa-wrench' },
    { label: 'Contact', href: 'contact.php', icon: 'fa-paper-plane' },
    { label: 'Changelog', href: 'changelog.php', icon: 'fa-clock-rotate-left' },
    { label: 'Colophon', href: 'colophon.php', icon: 'fa-circle-info' }
  ];

  function basePath() {
    const script = document.querySelector('script[src*="command-palette.js"]');
    if (!script) return '';
    try {
      const u = new URL(script.src, window.location.href);
      // assets/js -> site root
      return u.pathname.replace(/\/assets\/js\/command-palette\.js.*$/, '');
    } catch (e) {
      return '';
    }
  }

  const root = basePath();
  const overlay = document.createElement('div');
  overlay.className = 'cmdk-overlay';
  overlay.innerHTML = `
    <div class="cmdk-panel" role="dialog" aria-modal="true" aria-label="Command palette">
      <input type="search" id="cmdk-input" placeholder="Jump to a page…" autocomplete="off" />
      <div class="cmdk-list" id="cmdk-list"></div>
      <div class="cmdk-hint">Ctrl/Cmd + K · Esc to close</div>
    </div>`;
  document.body.appendChild(overlay);

  const input = overlay.querySelector('#cmdk-input');
  const list = overlay.querySelector('#cmdk-list');
  let active = 0;
  let filtered = links.slice();

  function hrefFor(path) {
    return (root || '') + '/' + path.replace(/^\//, '');
  }

  function render() {
    const q = (input.value || '').trim().toLowerCase();
    filtered = links.filter(l => !q || l.label.toLowerCase().includes(q));
    active = 0;
    list.innerHTML = filtered.map((l, i) => `
      <a class="cmdk-item${i === 0 ? ' is-active' : ''}" href="${hrefFor(l.href)}" data-idx="${i}">
        <i class="fa-solid ${l.icon}"></i> ${l.label}
      </a>`).join('') || '<div class="cmdk-hint">No matches</div>';
  }

  function open() {
    overlay.classList.add('is-open');
    input.value = '';
    render();
    setTimeout(() => input.focus(), 10);
  }

  function close() {
    overlay.classList.remove('is-open');
  }

  document.addEventListener('keydown', (e) => {
    if ((e.ctrlKey || e.metaKey) && e.key.toLowerCase() === 'k') {
      e.preventDefault();
      overlay.classList.contains('is-open') ? close() : open();
    }
    if (!overlay.classList.contains('is-open')) return;
    if (e.key === 'Escape') close();
    if (e.key === 'ArrowDown') {
      e.preventDefault();
      active = Math.min(active + 1, filtered.length - 1);
      [...list.querySelectorAll('.cmdk-item')].forEach((el, i) => el.classList.toggle('is-active', i === active));
    }
    if (e.key === 'ArrowUp') {
      e.preventDefault();
      active = Math.max(active - 1, 0);
      [...list.querySelectorAll('.cmdk-item')].forEach((el, i) => el.classList.toggle('is-active', i === active));
    }
    if (e.key === 'Enter' && filtered[active]) {
      e.preventDefault();
      window.location.href = hrefFor(filtered[active].href);
    }
  });

  input.addEventListener('input', render);
  overlay.addEventListener('click', (e) => { if (e.target === overlay) close(); });
})();
