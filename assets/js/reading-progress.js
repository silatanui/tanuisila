(function () {
  const article = document.querySelector('.blog-article-content') || document.querySelector('#blog-post');
  if (!article) return;

  const bar = document.createElement('div');
  bar.className = 'reading-progress';
  bar.setAttribute('aria-hidden', 'true');
  document.body.appendChild(bar);

  function update() {
    const rect = article.getBoundingClientRect();
    const total = article.offsetHeight - window.innerHeight;
    const scrolled = Math.min(Math.max(-rect.top, 0), Math.max(total, 1));
    const pct = total > 0 ? (scrolled / total) * 100 : 0;
    bar.style.width = pct + '%';
  }

  window.addEventListener('scroll', update, { passive: true });
  window.addEventListener('resize', update);
  update();
})();
