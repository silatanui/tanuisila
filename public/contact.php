<?php
$activePage = 'contact';
require_once __DIR__ . '/header.php';

$feedback = '';
$feedbackClass = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $name = trim($_POST['name'] ?? '');
  $email = trim($_POST['email'] ?? '');
  $subject = trim($_POST['subject'] ?? '');
  $message = trim($_POST['message'] ?? '');

  if (!empty($name) && !empty($email) && !empty($message)) {
    try {
      $stmt = $pdo->prepare('INSERT INTO `messages` (`name`, `email`, `subject`, `message`) VALUES (:name, :email, :subject, :message)');
      $stmt->execute([
        ':name' => $name,
        ':email' => $email,
        ':subject' => $subject,
        ':message' => $message
      ]);
      $feedback = 'Thank you! Your message has been sent successfully.';
      $feedbackClass = 'success';
    } catch (Throwable $e) {
      $feedback = 'An error occurred while saving your message. Please try again.';
      $feedbackClass = 'error';
    }
  } else {
    $feedback = 'Please complete all required fields (Name, Email, Message).';
    $feedbackClass = 'error';
  }
}
?>

<main class="container">
  <section id="contact" class="section-row" style="border-bottom:none; min-height: 600px;">
    <div class="section-label">Contact</div>
    <div class="section-content" style="width: 100%;">
      <h2>Let's build something purposeful.</h2>
      <p style="color: var(--muted); margin-bottom: 40px; font-size: 0.98rem; max-width: 660px; line-height: 1.45;">
        Get in touch to discuss research opportunities, software engineering projects, or technical collaboration. Submit the secure form below or reach out directly.
      </p>

      <!-- DUAL COLUMN CONTACT PAGE (Details vs Form) -->
      <div class="contact-editorial-layout" style="display: grid; grid-template-columns: 1fr 1.3fr; gap: 40px;">
        
        <!-- COLUMN 1: EDITORIAL DIRECT CHANNELS -->
        <div class="contact-channels-container" style="display: flex; flex-direction: column; gap: 24px;">
          
          <!-- EMAIL BOX -->
          <div class="editorial-channel-card" style="background: var(--soft-bg); border: 1px solid var(--light-gray); padding: 24px; box-shadow: 0 4px 12px var(--shadow);">
            <div style="font-size: 0.72rem; font-weight: 700; color: var(--accent-muted); letter-spacing: 0.12em; text-transform: uppercase; margin-bottom: 10px;"><i class="fa-solid fa-envelope" style="margin-right: 6px;"></i> Secure Email</div>
            <a href="mailto:<?php echo htmlspecialchars($profile['email'] ?: $contact['email'] ?? 'silatanuikipngetich@gmail.com'); ?>" 
               style="font-size: 1.15rem; font-weight: 700; color: var(--text); border-bottom: 2px solid var(--text); padding-bottom: 2px; display: inline-block; word-break: break-all;">
              <?php echo htmlspecialchars($profile['email'] ?: $contact['email'] ?? 'silatanuikipngetich@gmail.com'); ?>
            </a>
            <p style="margin: 10px 0 0; font-size: 0.85rem; color: var(--muted); line-height: 1.35;">Expected response within 24 business hours.</p>
          </div>

          <!-- DIRECT METAS -->
          <div class="editorial-channel-card" style="background: var(--soft-bg); border: 1px solid var(--light-gray); padding: 24px; box-shadow: 0 4px 12px var(--shadow); display: grid; gap: 16px;">
            <?php if (!empty($profile['phone'])): ?>
              <div>
                <div style="font-size: 0.72rem; font-weight: 700; color: var(--accent-muted); letter-spacing: 0.12em; text-transform: uppercase; margin-bottom: 4px;"><i class="fa-solid fa-phone" style="margin-right: 6px;"></i> Direct Contact</div>
                <span style="font-size: 1.05rem; font-weight: 600; color: var(--text);"><?php echo htmlspecialchars($profile['phone']); ?></span>
              </div>
            <?php endif; ?>

            <div>
              <div style="font-size: 0.72rem; font-weight: 700; color: var(--accent-muted); letter-spacing: 0.12em; text-transform: uppercase; margin-bottom: 4px;"><i class="fa-solid fa-location-dot" style="margin-right: 6px;"></i> Active Coordinates</div>
              <span style="font-size: 1.05rem; font-weight: 600; color: var(--text);"><?php echo htmlspecialchars($profile['location'] ?: 'Debrecen, Hungary / Nairobi, Kenya'); ?></span>
            </div>
          </div>

          <!-- DIGITAL CHANNELS -->
          <div class="editorial-channel-card" style="background: var(--soft-bg); border: 1px solid var(--light-gray); padding: 24px; box-shadow: 0 4px 12px var(--shadow);">
            <div style="font-size: 0.72rem; font-weight: 700; color: var(--accent-muted); letter-spacing: 0.12em; text-transform: uppercase; margin-bottom: 12px;"><i class="fa-solid fa-share-nodes" style="margin-right: 6px;"></i> Developer Workspaces</div>
            <div style="display: flex; gap: 16px;">
              <?php if (!empty($profile['github'])): ?>
                <a href="<?php echo htmlspecialchars($profile['github']); ?>" target="_blank" 
                   style="font-size: 0.88rem; font-weight: 700; text-transform: uppercase; letter-spacing: 0.05em; border-bottom: 2px solid var(--text); padding-bottom: 2px; color: var(--text); display: flex; align-items: center; gap: 6px;">
                  <i class="fa-brands fa-github"></i> GitHub
                </a>
              <?php endif; ?>
              <?php if (!empty($profile['linkedin'])): ?>
                <a href="<?php echo htmlspecialchars($profile['linkedin']); ?>" target="_blank" 
                   style="font-size: 0.88rem; font-weight: 700; text-transform: uppercase; letter-spacing: 0.05em; border-bottom: 2px solid var(--text); padding-bottom: 2px; color: var(--text); display: flex; align-items: center; gap: 6px;">
                  <i class="fa-brands fa-linkedin"></i> LinkedIn
                </a>
              <?php endif; ?>
            </div>
          </div>

        </div>

        <!-- COLUMN 2: SECURE FORM -->
        <div style="background: var(--soft-bg); border: 1px solid var(--light-gray); padding: 36px; box-shadow: 0 4px 12px var(--shadow); position: relative;">
          <h3 style="margin-top: 0; margin-bottom: 6px; font-size: 1.4rem; letter-spacing: -0.04em; color: var(--text); font-weight: 800;">Contact Form</h3>
          <p style="margin: 0 0 24px; font-size: 0.88rem; color: var(--muted);">Fields marked with an asterisk (*) are required to validate security signatures.</p>

          <?php if (!empty($feedback)): ?>
            <div style="margin-bottom: 24px; padding: 14px; border: 1px solid var(--light-gray); background: <?php echo $feedbackClass === 'success' ? '#d1fae5' : '#fee2e2'; ?>; color: <?php echo $feedbackClass === 'success' ? '#065f46' : '#991b1b'; ?>; font-weight: 600;">
              <?php echo htmlspecialchars($feedback); ?>
            </div>
          <?php endif; ?>

          <form action="contact.php" method="POST" style="display: flex; flex-direction: column; gap: 20px;">
            
            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px;">
              <div style="display: flex; flex-direction: column; gap: 6px;">
                <label style="font-size: 0.72rem; font-weight: 700; letter-spacing: 0.1em; text-transform: uppercase; color: var(--accent-muted);">Your Name *</label>
                <input type="text" name="name" required placeholder="John Doe"
                       style="padding: 12px; background: var(--bg); border: 1px solid var(--light-gray); color: var(--text); outline: none; font-family: inherit; font-size: 0.9rem; transition: border-color 0.2s ease;">
              </div>
              <div style="display: flex; flex-direction: column; gap: 6px;">
                <label style="font-size: 0.72rem; font-weight: 700; letter-spacing: 0.1em; text-transform: uppercase; color: var(--accent-muted);">Email Address *</label>
                <input type="email" name="email" required placeholder="john.doe@example.com"
                       style="padding: 12px; background: var(--bg); border: 1px solid var(--light-gray); color: var(--text); outline: none; font-family: inherit; font-size: 0.9rem; transition: border-color 0.2s ease;">
              </div>
            </div>

            <div style="display: flex; flex-direction: column; gap: 6px;">
              <label style="font-size: 0.72rem; font-weight: 700; letter-spacing: 0.1em; text-transform: uppercase; color: var(--accent-muted);">Subject</label>
              <input type="text" name="subject" placeholder="Research collaboration opportunity"
                     style="padding: 12px; background: var(--bg); border: 1px solid var(--light-gray); color: var(--text); outline: none; font-family: inherit; font-size: 0.9rem; transition: border-color 0.2s ease;">
            </div>

            <div style="display: flex; flex-direction: column; gap: 6px;">
              <label style="font-size: 0.72rem; font-weight: 700; letter-spacing: 0.1em; text-transform: uppercase; color: var(--accent-muted);">Message Body *</label>
              <textarea name="message" required rows="6" placeholder="Enter your message here..."
                        style="padding: 12px; background: var(--bg); border: 1px solid var(--light-gray); color: var(--text); outline: none; font-family: inherit; font-size: 0.9rem; resize: vertical; transition: border-color 0.2s ease;"></textarea>
            </div>

            <button type="submit" 
                    style="padding: 14px 28px; background: var(--text); color: #fff; border: 1px solid var(--text); font-weight: 700; letter-spacing: 0.08em; text-transform: uppercase; cursor: pointer; align-self: flex-start; transition: all 0.2s ease; display: inline-flex; align-items: center; gap: 10px;">
              Send Message <i class="fa-solid fa-paper-plane"></i>
            </button>
          </form>
        </div>

      </div>

    </div>
  </section>
</main>

<style>
  /* Input focus highlights */
  input:focus, textarea:focus {
    border-color: var(--accent) !important;
    background: #ffffff !important;
  }
  
  button:hover {
    background: var(--accent) !important;
    border-color: var(--accent) !important;
    color: #fff !important;
  }

  /* Responsive styling */
  @media (max-width: 900px) {
    .contact-editorial-layout {
      grid-template-columns: 1fr !important;
      gap: 30px;
    }
  }
</style>

<?php
require_once __DIR__ . '/footer.php';
?>
