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
              <h4 style="margin: 0; font-size: 1.05rem; font-weight: 800; color: var(--text); letter-spacing: -0.04em;"><?php echo htmlspecialchars($settings['site_title'] ?? $profile['full_name']); ?></h4>
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
          </ul>
        </div>

        <!-- COLUMN 3: MORE DIRECT LINKS INDEX -->
        <div>
          <h5 style="margin:0 0 12px; font-size:0.7rem; font-weight:700; letter-spacing:0.1em; text-transform:uppercase; color: var(--muted);">Explore</h5>
          <ul class="footer-links-list" style="list-style:none; padding:0; margin:0; display:grid; gap:8px; font-size:0.88rem; font-weight:500;">
            <li><a href="<?php echo appUrl('skills.php'); ?>" style="color: var(--text); text-decoration:none;">Skills</a></li>
            <li><a href="<?php echo appUrl('experience.php'); ?>" style="color: var(--text); text-decoration:none;">Experience</a></li>
            <li><a href="<?php echo appUrl('education.php'); ?>" style="color: var(--text); text-decoration:none;">Education</a></li>
            <li><a href="<?php echo appUrl('contact.php'); ?>" style="color: var(--text); text-decoration:none;">Contact</a></li>
          </ul>
        </div>

      </div>

  <!-- BOTTOM BRANDING DIVIDERS -->
  <div style="border-top:1px solid var(--light-gray); padding-top:20px; display:flex; align-items:center; justify-content:space-between; flex-wrap:wrap; gap:16px; font-size:0.78rem; font-weight:500; color: var(--muted); width: 100%;">
    <p style="margin:0;">© <?php echo date('Y'); ?> <?php echo htmlspecialchars($settings['site_title'] ?? $profile['full_name']); ?>. All rights reserved.</p>
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

  <div class="cookie-banner" id="cookieBanner" style="position:fixed; left:0; right:0; bottom:0; background:rgba(15,23,42,0.96); color:#fff; z-index:200; border-top:1px solid rgba(255,255,255,0.08); display: none;">
    <div class="container" style="display:flex; align-items:center; justify-content:space-between; gap:16px; min-height:48px; padding-top:8px; padding-bottom:8px;">
      <div class="cookie-text" style="font-size:0.78rem; color:rgba(255,255,255,0.7);">Cookies help this site run. <a href="<?php echo appUrl('privacy.php'); ?>" style="color:#fff; text-decoration:underline;">Privacy</a></div>
      <div class="actions" style="display:flex; gap:8px;">
        <button class="accept" onclick="acceptCookies()" style="border:none; background:#ffffff; color:#0f172a; cursor:pointer; font-weight:700; font-size:0.72rem; letter-spacing:0.06em; text-transform:uppercase; padding:7px 14px;">Accept</button>
      </div>
    </div>
  </div>

  <script>
    // Self-healing cookie validation handler
    document.addEventListener("DOMContentLoaded", function() {
        if (!getCookie("sila_cookies_accepted")) {
            document.getElementById("cookieBanner").style.display = "block";
        }
    });

    function acceptCookies() {
        setCookie("sila_cookies_accepted", "true", 365);
        document.getElementById("cookieBanner").style.display = "none";
    }

    function setCookie(name, value, days) {
        let expires = "";
        if (days) {
            let date = new Date();
            date.setTime(date.getTime() + (days * 24 * 60 * 60 * 1000));
            expires = "; expires=" + date.toUTCString();
        }
        document.cookie = name + "=" + (value || "") + expires + "; path=/";
    }

    function getCookie(name) {
        let nameEQ = name + "=";
        let ca = document.cookie.split(';');
        for (let i = 0; i < ca.length; i++) {
            let c = ca[i];
            while (c.charAt(0) == ' ') c = c.substring(1, c.length);
            if (c.indexOf(nameEQ) == 0) return c.substring(nameEQ.length, c.length);
        }
        return null;
    }

    // Automatic Skeleton Shimmer Image Loader
    function initSkeletonLoaders() {
      const wrappers = document.querySelectorAll('.img-skeleton-wrap');
      wrappers.forEach(wrap => {
        const img = wrap.querySelector('img');
        if (!img) return;

        if (img.complete && img.naturalWidth > 0) {
          wrap.classList.add('loaded');
        } else {
          img.addEventListener('load', () => wrap.classList.add('loaded'), { once: true });
          img.addEventListener('error', () => wrap.classList.add('loaded'), { once: true });
        }
      });

      document.querySelectorAll('article img, .blog-post img, .editorial-bio-content img').forEach(img => {
        if (!img.closest('.img-skeleton-wrap') && !img.classList.contains('no-skeleton')) {
          const wrap = document.createElement('div');
          wrap.className = 'img-skeleton-wrap';
          if (img.style.height) wrap.style.height = img.style.height;
          if (img.style.width) wrap.style.width = img.style.width;
          if (img.style.marginBottom) wrap.style.marginBottom = img.style.marginBottom;
          img.parentNode.insertBefore(wrap, img);
          wrap.appendChild(img);

          if (img.complete && img.naturalWidth > 0) {
            wrap.classList.add('loaded');
          } else {
            img.addEventListener('load', () => wrap.classList.add('loaded'), { once: true });
            img.addEventListener('error', () => wrap.classList.add('loaded'), { once: true });
          }
        }
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

      const io = new IntersectionObserver((entries) => {
        entries.forEach(entry => {
          if (entry.isIntersecting) {
            entry.target.classList.add('is-visible');
            io.unobserve(entry.target);
          }
        });
      }, { threshold: 0.12, rootMargin: '0px 0px -8% 0px' });

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
