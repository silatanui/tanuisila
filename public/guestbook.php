<?php
$activePage = 'guestbook';
require_once __DIR__ . '/header.php';
require_once __DIR__ . '/../config/config.php';

$feedback = '';
$feedbackType = '';

try {
  $pdo->exec("CREATE TABLE IF NOT EXISTS guestbook (
    id INT AUTO_INCREMENT PRIMARY KEY,
    author_name VARCHAR(120) NOT NULL,
    message TEXT NOT NULL,
    status VARCHAR(20) NOT NULL DEFAULT 'approved',
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
  ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
} catch (Throwable $e) {
  // table may already exist
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'sign_guestbook') {
  $name = trim($_POST['author_name'] ?? '');
  $message = trim($_POST['message'] ?? '');
  $hp = trim($_POST['website_hp'] ?? '');
  if ($hp !== '') {
    $feedback = 'Thanks for signing the guestbook.';
    $feedbackType = 'success';
  } elseif ($name === '' || $message === '') {
    $feedback = 'Name and message are required.';
    $feedbackType = 'error';
  } elseif (mb_strlen($message) > 500) {
    $feedback = 'Please keep messages under 500 characters.';
    $feedbackType = 'error';
  } else {
    try {
      $stmt = $pdo->prepare('INSERT INTO guestbook (author_name, message, status) VALUES (?, ?, ?)');
      $stmt->execute([$name, $message, 'approved']);
      $feedback = 'Thanks — your note is live.';
      $feedbackType = 'success';
      $name = '';
      $message = '';
    } catch (Throwable $e) {
      $feedback = 'Could not save right now. Please try again later.';
      $feedbackType = 'error';
    }
  }
}

$entries = [];
try {
  $entries = $pdo->query("SELECT * FROM guestbook WHERE status = 'approved' ORDER BY created_at DESC LIMIT 40")->fetchAll(PDO::FETCH_ASSOC);
} catch (Throwable $e) {
  $entries = [];
}
?>
<main id="main-content" class="container">
  <section class="section-row" style="border-bottom:none;">
    <div class="section-label">Guestbook</div>
    <div class="section-content">
      <div class="page-hero-mini">
        <h2>Sign the guestbook</h2>
        <p>Leave a short hello, a tip, or a note from the road — classic personal-web energy.</p>
      </div>

      <?php if ($feedback): ?>
        <div style="margin-bottom:18px;padding:12px 14px;border:1px solid <?php echo $feedbackType === 'success' ? '#a7f3d0' : '#fecaca'; ?>;background:<?php echo $feedbackType === 'success' ? '#ecfdf5' : '#fef2f2'; ?>;color:<?php echo $feedbackType === 'success' ? '#065f46' : '#991b1b'; ?>;font-weight:600;font-size:0.9rem;">
          <?php echo htmlspecialchars($feedback); ?>
        </div>
      <?php endif; ?>

      <form method="post" class="tool-panel reveal-on-enter" style="margin-bottom:28px;">
        <input type="hidden" name="action" value="sign_guestbook">
        <div style="display:none !important;"><label>Website<input type="text" name="website_hp" tabindex="-1" autocomplete="off"></label></div>
        <div>
          <label for="author_name">Name</label>
          <input id="author_name" name="author_name" required maxlength="120" value="<?php echo htmlspecialchars($name ?? ''); ?>" placeholder="Your name">
        </div>
        <div>
          <label for="message">Message</label>
          <textarea id="message" name="message" required maxlength="500" placeholder="Say hello…"><?php echo htmlspecialchars($message ?? ''); ?></textarea>
        </div>
        <div class="tool-actions">
          <button type="submit" class="btn primary">Sign guestbook</button>
        </div>
      </form>

      <div class="status-list">
        <?php if (empty($entries)): ?>
          <div class="status-row"><div class="status-row__label">Empty</div><div class="status-row__value">Be the first to sign.</div></div>
        <?php else: ?>
          <?php foreach ($entries as $row): ?>
            <div class="status-row reveal-on-enter">
              <div class="status-row__label"><?php echo htmlspecialchars(date('M j, Y', strtotime($row['created_at']))); ?></div>
              <div class="status-row__value">
                <strong style="color:var(--text);"><?php echo htmlspecialchars($row['author_name']); ?></strong><br>
                <?php echo nl2br(htmlspecialchars($row['message'])); ?>
              </div>
            </div>
          <?php endforeach; ?>
        <?php endif; ?>
      </div>
    </div>
  </section>
</main>
<?php require_once __DIR__ . '/footer.php'; ?>
