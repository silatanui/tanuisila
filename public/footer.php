  <!-- PREMIUM EDITORIAL FOOTER SECTION -->
  <footer class="site-footer" style="padding: 64px 0 40px; background: var(--soft-bg); border-top: 1px solid var(--light-gray); color: var(--muted);">
    <div class="container">
      
      <!-- GRID FOOTER MATRIX -->
      <div class="footer-layout-matrix" style="display: grid; grid-template-columns: 1.5fr 1fr 1fr; gap: 40px; margin-bottom: 40px;">
        
        <!-- COLUMN 1: CORPORATE IDENTITY BRAND -->
        <div style="display: flex; flex-direction: column; gap: 14px; align-items: flex-start;">
          <div style="display: flex; align-items: center; gap: 12px;">
            <img src="<?php echo htmlspecialchars(appUrl($logoFile)); ?>" alt="Branding Logo" class="footer-logo" style="width: 40px; height: 40px; object-fit: contain; padding: 4px; background: #fff; border: 1px solid var(--light-gray);">
            <div style="line-height: 1.2;">
              <h4 class="brand-name" style="margin: 0; font-size: 1.05rem; font-weight: 800; color: var(--text); letter-spacing: -0.04em;"><?php echo htmlspecialchars($settings['site_title'] ?? $profile['full_name']); ?></h4>
              <small style="color: var(--muted); font-size: 0.68rem; font-weight: 600; letter-spacing: 0.08em; text-transform: uppercase;">Computer Scientist</small>
            </div>
          </div>
          <p class="footer-brand-copy" style="margin: 0; font-size: 0.88rem; color: var(--muted); max-width: 320px; line-height: 1.5;">
            Theoretical foundations meeting robust enterprise software implementations.
          </p>
        </div>

        <!-- COLUMN 2: INTERNAL LINKS SITE INDEX -->
        <div>
          <h5 style="margin:0 0 12px; font-size:0.7rem; font-weight:700; letter-spacing:0.1em; text-transform:uppercase; color: var(--muted);">Site</h5>
          <ul class="footer-links-list" style="list-style:none; padding:0; margin:0; display:grid; gap:8px; font-size:0.88rem; font-weight:500;">
            <li><a href="<?php echo appUrl('index.php'); ?>" style="color: var(--text); text-decoration:none;">Home</a></li>
            <li><a href="<?php echo appUrl('about.php'); ?>" style="color: var(--text); text-decoration:none;">About</a></li>
            <li><a href="<?php echo appUrl('projects.php'); ?>" style="color: var(--text); text-decoration:none;">Projects</a></li>
            <li><a href="<?php echo appUrl('blog.php'); ?>" style="color: var(--text); text-decoration:none;">Blog</a></li>
            <li><a href="<?php echo appUrl('now.php'); ?>" style="color: var(--text); text-decoration:none;">Now</a></li>
          </ul>
        </div>

        <!-- COLUMN 3: MORE DIRECT LINKS INDEX -->
        <div>
          <h5 style="margin:0 0 12px; font-size:0.7rem; font-weight:700; letter-spacing:0.1em; text-transform:uppercase; color: var(--muted);">Explore</h5>
          <ul class="footer-links-list" style="list-style:none; padding:0; margin:0; display:grid; gap:8px; font-size:0.88rem; font-weight:500;">
            <li><a href="<?php echo appUrl('lab.php'); ?>" style="color: var(--text); text-decoration:none;">Lab</a></li>
            <li><a href="<?php echo appUrl('saas.php'); ?>" style="color: var(--text); text-decoration:none;">SaaS</a></li>
            <li><a href="<?php echo appUrl('tools.php'); ?>" style="color: var(--text); text-decoration:none;">Tools</a></li>
            <li><a href="<?php echo appUrl('sitemap.php'); ?>" style="color: var(--text); text-decoration:none;">Sitemap</a></li>
            <li><a href="<?php echo appUrl('contact.php'); ?>" style="color: var(--text); text-decoration:none;">Contact</a></li>
          </ul>
        </div>

      </div>

  <!-- BOTTOM BRANDING DIVIDERS -->
  <div style="border-top:1px solid var(--light-gray); padding-top:20px; display:flex; align-items:center; justify-content:space-between; flex-wrap:wrap; gap:16px; font-size:0.78rem; font-weight:500; color: var(--muted); width: 100%;">
    <p style="margin:0;">© <?php echo date('Y'); ?> <?php echo htmlspecialchars($settings['site_title'] ?? $profile['full_name']); ?>. All rights reserved. · Updated <?php echo date('M Y'); ?> · <a href="<?php echo appUrl('feed.php'); ?>" style="color:inherit;text-decoration:underline;">RSS</a> · <a href="<?php echo appUrl('colophon.php'); ?>" style="color:inherit;text-decoration:underline;">Colophon</a></p>
    <div style="display:flex; gap:16px; align-items:center;">
      <?php if (!empty($profile['github'])): ?>
        <a href="<?php echo htmlspecialchars($profile['github']); ?>" target="_blank" rel="noopener noreferrer" style="text-decoration:none;"><i class="fa-brands fa-github" style="font-size:1.05rem; color:var(--text);"></i></a>
      <?php endif; ?>
      <?php if (!empty($profile['linkedin'])): ?>
        <a href="<?php echo htmlspecialchars($profile['linkedin']); ?>" target="_blank" rel="noopener noreferrer" style="text-decoration:none;"><i class="fa-brands fa-linkedin" style="font-size:1.05rem; color:var(--text);"></i></a>
      <?php endif; ?>
    </div>
  </div>

    </div>
  </footer>

  <script>
    // Automatic Skeleton Shimmer Image Loader + missing-image placeholders
    function buildMediaPlaceholder(label) {
      const ph = document.createElement('span');
      ph.className = 'media-ph';
      ph.setAttribute('aria-hidden', 'true');
      const letter = (label || '').trim().charAt(0);
      ph.innerHTML =
        '<span class="media-ph__bg"></span><span class="media-ph__pattern"></span>' +
        (letter
          ? '<span class="media-ph__letter">' + letter.toUpperCase() + '</span>'
          : '<span class="media-ph__icon"></span>');
      return ph;
    }

    function replaceBrokenImage(img) {
      if (!img || img.dataset.phApplied === '1') return;
      img.dataset.phApplied = '1';
      const label = img.getAttribute('alt') || '';
      const ph = buildMediaPlaceholder(label);
      const wrap = img.closest('.img-skeleton-wrap, .bl-hub-feature__media, .bl-hub-topread__thumb, .bl-hub-card__media, .bp-related-card__thumb, .proj-mockup__frame, .bp-banner__media');
      img.replaceWith(ph);
      if (wrap) {
        wrap.classList.add('loaded', 'has-placeholder');
        wrap.classList.remove('img-skeleton-wrap');
      }
    }

    function bindImageLifecycle(img, wrap) {
      if (!img) return;
      const markLoaded = () => { if (wrap) wrap.classList.add('loaded'); };
      if (img.complete) {
        if (img.naturalWidth > 0) markLoaded();
        else replaceBrokenImage(img);
        return;
      }
      img.addEventListener('load', markLoaded, { once: true });
      img.addEventListener('error', () => replaceBrokenImage(img), { once: true });
    }

    function initSkeletonLoaders() {
      const wrappers = document.querySelectorAll('.img-skeleton-wrap');
      wrappers.forEach(wrap => {
        bindImageLifecycle(wrap.querySelector('img'), wrap);
      });

      document.querySelectorAll('article img, .blog-post img, .editorial-bio-content img, .bp-banner__media img, .proj-mockup__frame img').forEach(img => {
        if (img.closest('.img-skeleton-wrap') || img.classList.contains('no-skeleton')) {
          if (!img.closest('.img-skeleton-wrap')) {
            bindImageLifecycle(img, img.parentElement);
          }
          return;
        }
        const wrap = document.createElement('div');
        wrap.className = 'img-skeleton-wrap';
        if (img.style.height) wrap.style.height = img.style.height;
        if (img.style.width) wrap.style.width = img.style.width;
        if (img.style.marginBottom) wrap.style.marginBottom = img.style.marginBottom;
        img.parentNode.insertBefore(wrap, img);
        wrap.appendChild(img);
        bindImageLifecycle(img, wrap);
      });
    }

    function initRevealOnEnter() {
      const nodes = document.querySelectorAll('.reveal-on-enter, .section-label');
      if (!nodes.length) return;

      nodes.forEach(el => {
        if (!el.classList.contains('reveal-on-enter')) {
          el.classList.add('reveal-on-enter');
        }
      });

      if (window.matchMedia('(prefers-reduced-motion: reduce)').matches || !('IntersectionObserver' in window)) {
        nodes.forEach(el => el.classList.add('is-visible'));
        return;
      }

      // Use a near-zero threshold so tall blocks (e.g. long articles) still reveal
      const io = new IntersectionObserver((entries) => {
        entries.forEach(entry => {
          if (entry.isIntersecting) {
            entry.target.classList.add('is-visible');
            io.unobserve(entry.target);
          }
        });
      }, { threshold: 0.01, rootMargin: '0px 0px -40px 0px' });

      nodes.forEach(el => io.observe(el));
    }

    if (document.readyState === 'loading') {
      document.addEventListener('DOMContentLoaded', () => {
        initSkeletonLoaders();
        initRevealOnEnter();
      });
    } else {
      initSkeletonLoaders();
      initRevealOnEnter();
    }
  </script>
  <script src="<?php echo appUrl('assets/js/command-palette.js'); ?>?v=<?php echo @filemtime(__DIR__ . '/../assets/js/command-palette.js'); ?>" defer></script>
  <script src="<?php echo appUrl('assets/js/reading-progress.js'); ?>?v=<?php echo @filemtime(__DIR__ . '/../assets/js/reading-progress.js'); ?>" defer></script>
  <script src="<?php echo appUrl('assets/js/back-to-top.js'); ?>?v=<?php echo @filemtime(__DIR__ . '/../assets/js/back-to-top.js'); ?>" defer></script>
</body>
</html>
