<?php
$activePage = 'privacy';
require_once __DIR__ . '/header.php';
?>

<main class="container">
  <section id="privacy" class="section-row" style="border-bottom:none;">
    <div class="section-label">Privacy</div>
    <div class="section-content" style="width: 100%;">
      <h2>Privacy Policy</h2>
      <p style="color: var(--muted); margin-bottom: 30px; font-size: 0.98rem; max-width: 720px; line-height: 1.45;">
        This policy governs the data collection practices for the portfolio site of Tanui Kipng'etich Sila. Last updated: <?php echo date('F d, Y'); ?>.
      </p>

      <div class="editorial-channel-card" style="background: var(--soft-bg); border: 1px solid var(--light-gray); padding: 30px; box-shadow: 0 4px 12px var(--shadow); line-height: 1.6; color: var(--muted); font-size: 0.94rem;">
        <h3 style="margin-top: 0; font-size: 1.25rem; font-weight: 800; color: var(--text); letter-spacing: -0.03em;">1. Information We Collect</h3>
        <p>
          We collect information directly when you use our contact forms or message portals in the "Contact" tab. This includes your name, email address, optional message subject, and the message content itself.
        </p>

        <h3 style="margin-top: 24px; font-size: 1.25rem; font-weight: 800; color: var(--text); letter-spacing: -0.03em;">2. How We Use Your Information</h3>
        <p>
          Your information is stored inside our secure local database to process in-office inquiry and contact requests. We never sell, exchange, or rent your database records to third parties for commercial or promotional purposes.
        </p>

        <h3 style="margin-top: 24px; font-size: 1.25rem; font-weight: 800; color: var(--text); letter-spacing: -0.03em;">3. Cookies & Tracking Technologies</h3>
        <p>
          We use simple first-party browser cookies to optimize your experience. These local variables keep track of user preferences, such as hiding the cookie accept banner after you click "Accept all" or "Manage".
        </p>
        <p>
          No automated profiling or third-party ad network tracking scripts are loaded on this portfolio.
        </p>

        <h3 style="margin-top: 24px; font-size: 1.25rem; font-weight: 800; color: var(--text); letter-spacing: -0.03em;">4. Your Rights</h3>
        <p>
          You retain full rights to request we wipe or edit your email records. Get in touch directly using our secure email channel if you want to request data deletion.
        </p>
      </div>

    </div>
  </section>
</main>

<?php
require_once __DIR__ . '/footer.php';
?>
