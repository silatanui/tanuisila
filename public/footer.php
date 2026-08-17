  <!-- PREMIUM EDITORIAL FOOTER SECTION -->
  <footer class="site-footer" style="padding: 80px 0 50px; background: var(--soft-bg); border-top: 1px solid var(--light-gray); color: var(--muted);">
    <div class="container">
      
      <!-- GRID FOOTER MATRIX -->
      <div class="footer-layout-matrix" style="display: grid; grid-template-columns: 1.5fr 1fr 1fr; gap: 40px; margin-bottom: 60px;">
        
        <!-- COLUMN 1: CORPORATE IDENTITY BRAND -->
        <div style="display: flex; flex-direction: column; gap: 18px; align-items: flex-start;">
          <div style="display: flex; align-items: center; gap: 12px;">
            <img src="<?php echo htmlspecialchars($logoFile); ?>" alt="Branding Logo" class="footer-logo" style="width: 44px; height: 44px; object-fit: contain; padding: 4px; background: #fff; border: 1px solid var(--light-gray);">
            <div style="line-height: 1.2;">
              <h4 style="margin: 0; font-size: 1.15rem; font-weight: 800; color: var(--text); letter-spacing: -0.04em;"><?php echo htmlspecialchars($settings['site_title'] ?? $profile['full_name']); ?></h4>
              <small style="color: var(--accent-muted); font-size: 0.7rem; font-weight: 700; letter-spacing: 0.1em; text-transform: uppercase;">Computer Scientist</small>
            </div>
          </div>
          <p class="footer-brand-copy" style="margin: 4px 0 0; font-size: 0.88rem; color: var(--muted); max-width: 320px; line-height: 1.45;">
            Theoretical foundations meeting robust enterprise software implementations. Solving complex problems with algorithmic design.
          </p>
        </div>

        <!-- COLUMN 2: INTERNAL LINKS SITE INDEX -->
        <div>
          <h5 style="margin:0 0 16px; font-size:0.75rem; font-weight:700; letter-spacing:0.12em; text-transform:uppercase; color: var(--text);">Site Index</h5>
          <ul class="footer-links-list" style="list-style:none; padding:0; margin:0; display:grid; gap:10px; font-size:0.86rem; font-weight:600;">
            <li><a href="index.php" style="color: var(--muted); text-decoration:none; transition: color 0.15s ease;">Home Portal</a></li>
            <li><a href="about.php" style="color: var(--muted); text-decoration:none; transition: color 0.15s ease;">Biography Details</a></li>
            <li><a href="projects.php" style="color: var(--muted); text-decoration:none; transition: color 0.15s ease;">Engineering Portfolio</a></li>
            <li><a href="research.php" style="color: var(--muted); text-decoration:none; transition: color 0.15s ease;">Academic Research</a></li>
          </ul>
        </div>

        <!-- COLUMN 3: MORE DIRECT LINKS INDEX -->
        <div>
          <h5 style="margin:0 0 16px; font-size:0.75rem; font-weight:700; letter-spacing:0.12em; text-transform:uppercase; color: var(--text);">Specialties Gateway</h5>
          <ul class="footer-links-list" style="list-style:none; padding:0; margin:0; display:grid; gap:10px; font-size:0.86rem; font-weight:600;">
            <li><a href="skills.php" style="color: var(--muted); text-decoration:none; transition: color 0.15s ease;">Technical Toolkit Matrix</a></li>
            <li><a href="experience.php" style="color: var(--muted); text-decoration:none; transition: color 0.15s ease;">Professional Background</a></li>
            <li><a href="education.php" style="color: var(--muted); text-decoration:none; transition: color 0.15s ease;">Completed Academy</a></li>
            <li><a href="contact.php" style="color: var(--muted); text-decoration:none; transition: color 0.15s ease;">Secure Channels</a></li>
          </ul>
        </div>

      </div>

  <!-- BOTTOM BRANDING DIVIDERS -->
  <div style="border-top:1px solid var(--light-gray); padding-top:24px; display:flex; align-items:center; justify-content:space-between; flex-wrap:wrap; gap:16px; font-size:0.8rem; font-weight:600; color: var(--accent-muted); width: 100%;">
    <p style="margin:0;">© <?php echo date('Y'); ?> <?php echo htmlspecialchars($settings['site_title'] ?? $profile['full_name']); ?>. All rights reserved.</p>
    <div style="display:flex; gap:16px; align-items:center;">
      <?php if (!empty($profile['github'])): ?>
        <a href="<?php echo htmlspecialchars($profile['github']); ?>" target="_blank" style="text-decoration:none; transition: opacity 0.15s ease;"><i class="fa-brands fa-github" style="font-size:1.1rem; color:var(--text);"></i></a>
      <?php endif; ?>
      <?php if (!empty($profile['linkedin'])): ?>
        <a href="<?php echo htmlspecialchars($profile['linkedin']); ?>" target="_blank" style="text-decoration:none; transition: opacity 0.15s ease;"><i class="fa-brands fa-linkedin" style="font-size:1.1rem; color:var(--text);"></i></a>
      <?php endif; ?>
    </div>
  </div>

    </div>
  </footer>

  <div class="cookie-banner" id="cookieBanner" style="position:fixed; left:0; right:0; bottom:0; background:var(--text); color:#fff; z-index:200; border-top:1px solid var(--light-gray); display: none;">
    <div class="container" style="display:flex; align-items:center; justify-content:space-between; min-height:60px; padding-top:10px; padding-bottom:10px;">
      <div class="cookie-text" style="font-size:0.82rem; color:#ccc;">We use cookies to enhance your experience. <a href="privacy.php" style="color:#fff; text-decoration:underline;">Privacy Policy</a></div>
      <div class="actions" style="display:flex; gap:10px;">
        <button class="manage" onclick="acceptCookies()" style="border:1px solid rgba(255,255,255,0.2); background:transparent; color:#fff; cursor:pointer; font-weight:700; font-size:0.75rem; letter-spacing:0.08em; text-transform:uppercase; padding:8px 16px;">Manage</button>
        <button class="accept" onclick="acceptCookies()" style="border:none; background:#ffffff; color:#0f172a; cursor:pointer; font-weight:700; font-size:0.75rem; letter-spacing:0.08em; text-transform:uppercase; padding:8px 16px;">Accept All</button>

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
  </script>
</body>
</html>
