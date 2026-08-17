<?php
session_start();
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/sidebar.php';
require_once __DIR__ . '/topbar.php';

if (empty($_SESSION['admin_logged_in'])) {
    header('Location: login.php');
    exit;
}

$notice = '';
$error = '';

if (isset($_GET['delete'])) {
    $id = (int)$_GET['delete'];
    if ($id > 0) {
        $stmt = $pdo->prepare('DELETE FROM `messages` WHERE id = ?');
        $stmt->execute([$id]);
        $notice = 'Message deleted successfully.';
    }
}

if (isset($_GET['mark_read'])) {
    $id = (int)$_GET['mark_read'];
    if ($id > 0) {
        $stmt = $pdo->prepare('UPDATE `messages` SET `is_read` = 1 WHERE id = ?');
        $stmt->execute([$id]);
        $notice = 'Message marked as read.';
    }
}

if (isset($_GET['mark_all_read'])) {
    try {
        $pdo->exec('UPDATE `messages` SET `is_read` = 1 WHERE `is_read` = 0');
        $notice = 'All messages marked as read.';
    } catch (Throwable $e) {}
}

$messages = $pdo->query('SELECT * FROM `messages` ORDER BY id DESC')->fetchAll(PDO::FETCH_ASSOC);
?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Inbox Messages — Portfolio Admin</title>
  <link rel="icon" type="image/jpeg" href="../Tanui-Sila-Logo-v3.jpg">
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@400;500;600;700;800&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css" crossorigin="anonymous" referrerpolicy="no-referrer" />
  <link rel="stylesheet" href="../assets/css/admin.css?v=1">
  <style>
    .message-card {
      background: var(--panel-alt);
      border: 1px solid var(--line);
      padding: 20px;
      margin-bottom: 16px;
      position: relative;
    }
    .message-header {
      display: flex;
      justify-content: space-between;
      border-bottom: 1px solid var(--line);
      padding-bottom: 10px;
      margin-bottom: 12px;
    }
    .message-meta {
      font-size: 0.88rem;
      color: var(--muted);
    }
    .message-body {
      font-size: 0.95rem;
      line-height: 1.5;
      color: var(--text);
      white-space: pre-wrap;
    }
  </style>
</head>
<body>
  <div class="app-shell">
    <?php echo renderSidebar('messages.php'); ?>

    <main class="main-panel">
      <?php echo renderTopbar('Inbox Messages'); ?>

      <?php if (!empty($notice)): ?>
        <div class="notice"><?php echo htmlspecialchars($notice); ?></div>
      <?php endif; ?>

      <section class="panel">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
          <h2 style="margin:0;">Visitor Inquiry Logs (<?php echo count($messages); ?>)</h2>
          <?php 
            $hasUnread = false;
            foreach ($messages as $msg) {
                if ((isset($msg['is_read']) ? (int)$msg['is_read'] : 0) === 0) {
                    $hasUnread = true;
                    break;
                }
            }
            if ($hasUnread):
          ?>
            <a href="messages.php?mark_all_read=1" class="btn" style="padding: 6px 12px; font-size: 0.8rem; background: var(--primary); color: #fff; text-decoration: none;">Mark All as Read</a>
          <?php endif; ?>
        </div>
        
        <?php if (empty($messages)): ?>
          <p style="color: var(--muted);">No messages received yet.</p>
        <?php else: ?>
          <?php foreach ($messages as $msg): ?>
            <?php 
              $isRead = isset($msg['is_read']) ? (int)$msg['is_read'] : 0; 
            ?>
            <div class="message-card" style="<?php echo $isRead == 0 ? 'border-left: 4px solid #ef4444; background: rgba(239, 68, 68, 0.03);' : ''; ?>">
              <div class="message-header">
                <div>
                  <strong style="font-size:1.1rem; color:var(--text);"><?php echo htmlspecialchars($msg['name']); ?></strong>
                  <?php if ($isRead == 0): ?>
                    <span style="background: #ef4444; color: #fff; padding: 2px 6px; font-size: 0.68rem; font-weight: 700; text-transform: uppercase; margin-left: 8px; vertical-align: middle; display: inline-block;">New</span>
                  <?php endif; ?>
                  <div class="message-meta" style="margin-top: 4px;">
                    <i class="fa-solid fa-envelope" style="margin-right:6px;"></i><a href="mailto:<?php echo htmlspecialchars($msg['email']); ?>" style="color: var(--primary); text-decoration:none;"><?php echo htmlspecialchars($msg['email']); ?></a>
                    <?php if (!empty($msg['subject'])): ?>
                      <span style="margin: 0 10px;">|</span><strong>Subject:</strong> <?php echo htmlspecialchars($msg['subject']); ?>
                    <?php endif; ?>
                  </div>
                </div>
                <div style="text-align: right;">
                  <span class="message-meta"><?php echo htmlspecialchars($msg['created_at']); ?></span>
                  <div style="margin-top:6px; display: flex; gap: 8px; justify-content: flex-end;">
                    <?php if ($isRead == 0): ?>
                      <a href="messages.php?mark_read=<?php echo $msg['id']; ?>" class="btn" style="padding: 4px 10px; font-size: 0.8rem; text-decoration: none; border: 1px solid var(--line); color: var(--text); background: var(--bg);"><i class="fa-solid fa-check"></i> Mark as Read</a>
                    <?php endif; ?>
                    <a href="messages.php?delete=<?php echo $msg['id']; ?>" class="btn danger" onclick="return confirm('Are you sure you want to delete this message?');" style="padding: 4px 10px; font-size: 0.8rem; text-decoration: none; color: #fff;"><i class="fa-solid fa-trash"></i> Delete</a>
                  </div>
                </div>
              </div>
              <div class="message-body"><?php echo htmlspecialchars($msg['message']); ?></div>
            </div>
          <?php endforeach; ?>
        <?php endif; ?>
      </section>
    </main>
  </div>
</body>
</html>
