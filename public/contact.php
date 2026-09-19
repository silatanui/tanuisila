<?php
$activePage = 'contact';
require_once __DIR__ . '/header.php';

$feedback = '';
$feedbackClass = '';

$nameValue    = htmlspecialchars((string) ($_POST['name'] ?? ''), ENT_QUOTES, 'UTF-8');
$emailValue   = htmlspecialchars((string) ($_POST['email'] ?? ''), ENT_QUOTES, 'UTF-8');
$subjectValue = htmlspecialchars((string) ($_POST['subject'] ?? ''), ENT_QUOTES, 'UTF-8');
$messageValue = htmlspecialchars((string) ($_POST['message'] ?? ''), ENT_QUOTES, 'UTF-8');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $name    = trim($_POST['name'] ?? '');
  $email   = trim($_POST['email'] ?? '');
  $subject = trim($_POST['subject'] ?? '');
  $message = trim($_POST['message'] ?? '');

  if (!empty($name) && !empty($email) && !empty($message)) {
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
      $feedback = 'Please provide a valid email address.';
      $feedbackClass = 'error';
    } else {
      try {
        $stmt = $pdo->prepare('INSERT INTO `messages` (`name`, `email`, `subject`, `message`, `is_read`, `created_at`) VALUES (:name, :email, :subject, :message, 0, NOW())');
        $stmt->execute([
          ':name'    => $name,
          ':email'   => $email,
          ':subject' => $subject,
          ':message' => $message
        ]);
        $feedback = 'Thank you, ' . htmlspecialchars($name, ENT_QUOTES, 'UTF-8') . '! Your message has been sent successfully. I will get back to you within 24 hours.';
        $feedbackClass = 'success';
        $nameValue = '';
        $emailValue = '';
        $subjectValue = '';
        $messageValue = '';
      } catch (Throwable $e) {
        try {
          $stmt = $pdo->prepare('INSERT INTO `messages` (`name`, `email`, `subject`, `message`) VALUES (:name, :email, :subject, :message)');
          $stmt->execute([
            ':name'    => $name,
            ':email'   => $email,
            ':subject' => $subject,
            ':message' => $message
          ]);
          $feedback = 'Thank you, ' . htmlspecialchars($name, ENT_QUOTES, 'UTF-8') . '! Your message has been sent successfully. I will get back to you within 24 hours.';
          $feedbackClass = 'success';
          $nameValue = '';
          $emailValue = '';
          $subjectValue = '';
          $messageValue = '';
        } catch (Throwable $e2) {
          $feedback = 'An unexpected error occurred while saving your message. Please try again or email directly.';
          $feedbackClass = 'error';
        }
      }
    }
  } else {
    $feedback = 'Please complete all required fields (Name, Email, Message).';
    $feedbackClass = 'error';
  }
}

$contactEmail = trim((string) ($profile['email'] ?? ''));
if ($contactEmail === '' && !empty($contact['email'])) {
  $contactEmail = trim((string) $contact['email']);
}
if ($contactEmail === '') {
  $contactEmail = 'silatanuikipngetich@gmail.com';
}

$contactPhone = trim((string) ($profile['phone'] ?? ''));
if ($contactPhone === '') {
  $contactPhone = '+254 742 178 644';
}

$contactLocation = trim((string) ($profile['location'] ?? ''));
if ($contactLocation === '') {
  $contactLocation = 'Debrecen, Hungary / Nairobi, Kenya';
}
?>

<main class="container">
  <section id="contact" class="section-row contact-section">
    <div class="section-label">Contact</div>
    
    <div class="section-content contact-content">
      
      <!-- Top Section Header -->
      <div class="contact-header-wrap">
        <div>
          <div class="contact-status-pill">
            <span class="status-pulse-dot"></span>
            Available for Research, Engineering &amp; Collaborations
          </div>
          <h2 class="contact-main-heading">
            Let's build something purposeful.
          </h2>
          <p class="contact-intro">
            Have a research inquiry, software engineering project, academic collaboration, or technology question? Reach out through the channels below or send a message directly.
          </p>
        </div>
      </div>

      <!-- Main Asymmetric Grid -->
      <div class="contact-editorial-layout">
        
        <!-- LEFT: Channel Cards -->
        <div class="contact-channels-container">
          
          <!-- Primary Email Card -->
          <div class="editorial-channel-card hero-channel-card">
            <div class="card-kicker">
              <i class="fa-solid fa-envelope"></i> Direct Email
            </div>
            <div class="contact-link-row">
              <a href="mailto:<?php echo htmlspecialchars($contactEmail, ENT_QUOTES, 'UTF-8'); ?>" class="primary-contact-link" title="Click to send email">
                <?php echo htmlspecialchars($contactEmail, ENT_QUOTES, 'UTF-8'); ?>
              </a>
              <button type="button" class="copy-btn" onclick="copyText('<?php echo htmlspecialchars($contactEmail, ENT_QUOTES, 'UTF-8'); ?>', this)" title="Copy email to clipboard">
                <i class="fa-solid fa-copy"></i>
              </button>
            </div>
            <p>Direct communication channel. Expected response time is within 24 business hours.</p>
          </div>

          <!-- Phone & WhatsApp Card -->
          <div class="editorial-channel-card">
            <div class="card-kicker">
              <i class="fa-brands fa-whatsapp"></i> WhatsApp &amp; Phone
            </div>
            <div class="contact-link-row">
              <a href="https://wa.me/254742178644" target="_blank" rel="noopener noreferrer" class="primary-contact-link" style="font-size: 1.05rem;" title="Chat on WhatsApp">
                <?php echo htmlspecialchars($contactPhone, ENT_QUOTES, 'UTF-8'); ?>
              </a>
              <a href="https://wa.me/254742178644" target="_blank" rel="noopener noreferrer" class="copy-btn" title="Open WhatsApp Chat" style="background: rgba(34, 197, 94, 0.1); color: #16a34a; border-color: rgba(34, 197, 94, 0.3);">
                <i class="fa-brands fa-whatsapp"></i>
              </a>
            </div>
            <p>Fastest channel for urgent project consultations and real-time scheduling.</p>
          </div>

          <!-- Location & Coordinates Card -->
          <div class="editorial-channel-card">
            <div class="card-kicker">
              <i class="fa-solid fa-location-dot"></i> Active Locations &amp; Timezones
            </div>
            <div class="location-display-text">
              <?php echo htmlspecialchars($contactLocation, ENT_QUOTES, 'UTF-8'); ?>
            </div>
            <div class="timezone-tags">
              <span class="tz-tag"><i class="fa-regular fa-clock"></i> CET (UTC+1)</span>
              <span class="tz-tag"><i class="fa-regular fa-clock"></i> EAT (UTC+3)</span>
            </div>
          </div>

          <!-- Developer & Research Networks -->
          <div class="editorial-channel-card">
            <div class="card-kicker">
              <i class="fa-solid fa-network-wired"></i> Developer &amp; Research Profiles
            </div>
            <div class="social-links-grid">
              <?php if (!empty($profile['github'])): ?>
                <a href="<?php echo htmlspecialchars($profile['github'], ENT_QUOTES, 'UTF-8'); ?>" target="_blank" rel="noopener noreferrer" class="social-chip">
                  <i class="fa-brands fa-github"></i> GitHub Profile
                </a>
              <?php endif; ?>
              <?php if (!empty($profile['linkedin'])): ?>
                <a href="<?php echo htmlspecialchars($profile['linkedin'], ENT_QUOTES, 'UTF-8'); ?>" target="_blank" rel="noopener noreferrer" class="social-chip">
                  <i class="fa-brands fa-linkedin"></i> LinkedIn Network
                </a>
              <?php endif; ?>
            </div>
          </div>

        </div>

        <!-- RIGHT: Interactive Contact Form -->
        <div class="contact-form-shell">
          <div class="form-shell-header">
            <h3><i class="fa-solid fa-paper-plane" style="color: var(--accent); margin-right: 8px;"></i> Send a Direct Message</h3>
            <p>Fill out the inquiry form below and your message will be dispatched directly to my inbox.</p>
          </div>

          <?php if (!empty($feedback)): ?>
            <div class="contact-feedback <?php echo $feedbackClass === 'success' ? 'feedback-success' : 'feedback-error'; ?>">
              <i class="fa-solid <?php echo $feedbackClass === 'success' ? 'fa-circle-check' : 'fa-circle-exclamation'; ?>"></i>
              <div><?php echo $feedback; ?></div>
            </div>
          <?php endif; ?>

          <form action="contact.php" method="POST" class="contact-form-grid" id="contact-form">
            
            <div class="contact-two-col-fields">
              <div class="form-field">
                <label for="name"><i class="fa-solid fa-user"></i> Full Name *</label>
                <input id="name" type="text" name="name" required placeholder="e.g. Alex Morgan" value="<?php echo $nameValue; ?>">
              </div>
              <div class="form-field">
                <label for="email"><i class="fa-solid fa-envelope"></i> Email Address *</label>
                <input id="email" type="email" name="email" required placeholder="e.g. alex@example.com" value="<?php echo $emailValue; ?>">
              </div>
            </div>

            <div class="form-field">
              <label for="subject"><i class="fa-solid fa-tag"></i> Subject / Topic *</label>
              <input id="subject" type="text" name="subject" required placeholder="e.g. Project Consultation / Research Inquiry" value="<?php echo $subjectValue; ?>">
            </div>

            <div class="form-field">
              <label for="message"><i class="fa-solid fa-message"></i> Detailed Message *</label>
              <textarea id="message" name="message" required rows="6" placeholder="Describe your project, timeline, questions, or collaboration idea..."><?php echo $messageValue; ?></textarea>
            </div>

            <div class="form-submit-row">
              <button type="submit" class="contact-submit-btn" id="submit-btn">
                <span>Send Message</span>
                <i class="fa-solid fa-arrow-right"></i>
              </button>
              <span class="form-privacy-note">
                <i class="fa-solid fa-shield-halved"></i> Your information is kept strictly private.
              </span>
            </div>

          </form>
        </div>

      </div>

    </div>
  </section>
</main>

<style>
  .contact-section {
    border-bottom: none;
    padding-bottom: 60px;
  }

  .contact-content {
    width: 100%;
  }

  .contact-header-wrap {
    margin-bottom: 34px;
  }

  .contact-status-pill {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    padding: 6px 14px;
    background: rgba(34, 197, 94, 0.08);
    border: 1px solid rgba(34, 197, 94, 0.25);
    font-size: 0.72rem;
    font-weight: 800;
    letter-spacing: 0.08em;
    text-transform: uppercase;
    color: #15803d;
    margin-bottom: 12px;
  }

  .status-pulse-dot {
    width: 7px;
    height: 7px;
    background: #22c55e;
    border-radius: 50%;
    display: inline-block;
    box-shadow: 0 0 0 0 rgba(34, 197, 94, 0.6);
    animation: contact-pulse 2s infinite;
  }

  @keyframes contact-pulse {
    0%   { box-shadow: 0 0 0 0 rgba(34, 197, 94, 0.6); }
    70%  { box-shadow: 0 0 0 6px rgba(34, 197, 94, 0); }
    100% { box-shadow: 0 0 0 0 rgba(34, 197, 94, 0); }
  }

  .contact-main-heading {
    margin: 0 0 10px;
    font-size: clamp(1.8rem, 3.2vw, 2.6rem);
    font-weight: 800;
    letter-spacing: -0.04em;
    color: var(--text);
    line-height: 1.1;
  }

  .contact-intro {
    color: var(--muted);
    margin: 0;
    font-size: 1rem;
    max-width: 720px;
    line-height: 1.55;
  }

  .contact-editorial-layout {
    display: grid;
    grid-template-columns: 0.95fr 1.05fr;
    gap: 36px;
    align-items: start;
  }

  .contact-channels-container {
    display: grid;
    gap: 16px;
  }

  .editorial-channel-card {
    position: relative;
    background: var(--soft-bg);
    border: 1px solid var(--light-gray);
    padding: 22px 24px;
    transition: transform 0.2s ease, border-color 0.2s ease;
  }

  .editorial-channel-card:hover {
    border-color: rgba(234, 88, 12, 0.35);
  }

  .card-kicker {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    font-size: 0.72rem;
    font-weight: 800;
    color: var(--accent-muted);
    letter-spacing: 0.1em;
    text-transform: uppercase;
    margin-bottom: 8px;
  }

  .contact-link-row {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 12px;
  }

  .primary-contact-link {
    font-size: 1.12rem;
    font-weight: 800;
    color: var(--text);
    text-decoration: none;
    line-height: 1.3;
    word-break: break-word;
    border-bottom: 2px solid transparent;
    transition: color 0.15s ease, border-color 0.15s ease;
  }

  .primary-contact-link:hover {
    color: var(--accent);
    border-color: var(--accent);
  }

  .copy-btn {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    width: 34px;
    height: 34px;
    background: rgba(15, 23, 42, 0.04);
    border: 1px solid var(--light-gray);
    color: var(--text);
    cursor: pointer;
    font-size: 0.85rem;
    flex-shrink: 0;
    transition: all 0.15s ease;
  }

  .copy-btn:hover {
    background: var(--text);
    color: #fff;
  }

  .editorial-channel-card p {
    margin: 8px 0 0;
    font-size: 0.84rem;
    line-height: 1.45;
    color: var(--muted);
  }

  .location-display-text {
    font-size: 1rem;
    font-weight: 700;
    color: var(--text);
    margin-bottom: 8px;
  }

  .timezone-tags {
    display: flex;
    flex-wrap: wrap;
    gap: 8px;
  }

  .tz-tag {
    display: inline-flex;
    align-items: center;
    gap: 5px;
    font-size: 0.74rem;
    font-weight: 700;
    padding: 3px 8px;
    background: rgba(15, 23, 42, 0.03);
    border: 1px solid var(--light-gray);
    color: var(--muted);
  }

  .social-links-grid {
    display: flex;
    flex-wrap: wrap;
    gap: 8px;
  }

  .social-chip {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    padding: 8px 14px;
    background: rgba(15, 23, 42, 0.03);
    border: 1px solid var(--light-gray);
    font-size: 0.78rem;
    font-weight: 700;
    letter-spacing: 0.04em;
    color: var(--text);
    text-decoration: none;
    transition: all 0.2s ease;
  }

  .social-chip:hover {
    background: var(--text);
    color: #fff;
    border-color: var(--text);
  }

  .social-chip i {
    color: var(--accent);
  }

  .social-chip:hover i {
    color: #fff;
  }

  /* ── FORM SHELL ────────────────────────────────────────── */
  .contact-form-shell {
    position: relative;
    background: var(--soft-bg);
    border: 1px solid var(--light-gray);
    padding: 36px;
    box-shadow: 0 10px 30px rgba(15, 23, 42, 0.04);
  }

  .form-shell-header h3 {
    margin: 0 0 6px;
    font-size: 1.35rem;
    letter-spacing: -0.03em;
    color: var(--text);
    font-weight: 800;
  }

  .form-shell-header p {
    margin: 0 0 20px;
    font-size: 0.86rem;
    color: var(--muted);
    line-height: 1.45;
  }

  .contact-feedback {
    display: flex;
    align-items: flex-start;
    gap: 12px;
    margin-bottom: 22px;
    padding: 14px 16px;
    font-size: 0.88rem;
    font-weight: 600;
    line-height: 1.4;
  }

  .contact-feedback i {
    font-size: 1.1rem;
    margin-top: 2px;
    flex-shrink: 0;
  }

  .feedback-success {
    background: #f0fdf4;
    color: #166534;
    border: 1px solid #bbf7d0;
  }

  .feedback-error {
    background: #fef2f2;
    color: #991b1b;
    border: 1px solid #fecaca;
  }

  .contact-form-grid {
    display: grid;
    gap: 18px;
  }

  .contact-two-col-fields {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 16px;
  }

  .form-field {
    display: grid;
    gap: 6px;
  }

  .form-field label {
    font-size: 0.72rem;
    font-weight: 800;
    letter-spacing: 0.08em;
    text-transform: uppercase;
    color: var(--muted);
    display: flex;
    align-items: center;
    gap: 6px;
  }

  .form-field label i {
    color: var(--accent);
    font-size: 0.72rem;
  }

  .form-field input,
  .form-field textarea {
    padding: 12px 14px;
    background: #ffffff;
    border: 1px solid var(--light-gray);
    color: var(--text);
    outline: none;
    font-family: inherit;
    font-size: 0.92rem;
    transition: border-color 0.2s ease, box-shadow 0.2s ease;
  }

  .form-field textarea {
    resize: vertical;
    min-height: 130px;
  }

  .form-field input:focus,
  .form-field textarea:focus {
    border-color: var(--accent);
    box-shadow: 0 0 0 3px rgba(234, 88, 12, 0.1);
  }

  .form-submit-row {
    display: flex;
    align-items: center;
    justify-content: space-between;
    flex-wrap: wrap;
    gap: 16px;
    margin-top: 6px;
    padding-top: 16px;
    border-top: 1px solid var(--light-gray);
  }

  .contact-submit-btn {
    padding: 13px 28px;
    background: var(--text);
    color: #fff;
    border: 1px solid var(--text);
    font-weight: 800;
    font-size: 0.8rem;
    letter-spacing: 0.08em;
    text-transform: uppercase;
    cursor: pointer;
    transition: all 0.2s ease;
    display: inline-flex;
    align-items: center;
    gap: 10px;
  }

  .contact-submit-btn:hover {
    background: var(--accent);
    border-color: var(--accent);
    transform: translateY(-1px);
  }

  .form-privacy-note {
    font-size: 0.74rem;
    color: var(--muted);
    display: inline-flex;
    align-items: center;
    gap: 6px;
  }

  @media (max-width: 1000px) {
    .contact-editorial-layout {
      grid-template-columns: 1fr;
      gap: 30px;
    }
  }

  @media (max-width: 640px) {
    .contact-two-col-fields {
      grid-template-columns: 1fr;
    }

    .contact-form-shell {
      padding: 24px 20px;
    }

    .contact-submit-btn {
      width: 100%;
      justify-content: center;
    }
  }
</style>

<script>
  function copyText(text, btn) {
    navigator.clipboard.writeText(text).then(() => {
      const origHtml = btn.innerHTML;
      btn.innerHTML = '<i class="fa-solid fa-check" style="color: #22c55e;"></i>';
      setTimeout(() => {
        btn.innerHTML = origHtml;
      }, 2000);
    }).catch(() => {
      alert('Copied: ' + text);
    });
  }
</script>

<?php
require_once __DIR__ . '/footer.php';
?>
