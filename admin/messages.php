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

// Handle Delete Single Message
if (isset($_GET['delete'])) {
    $id = (int)$_GET['delete'];
    if ($id > 0) {
        $stmt = $pdo->prepare('DELETE FROM `messages` WHERE id = ?');
        $stmt->execute([$id]);
        $notice = 'Message deleted successfully.';
    }
}

// Handle Delete All Read Messages
if (isset($_GET['delete_read'])) {
    try {
        $stmt = $pdo->prepare('DELETE FROM `messages` WHERE `is_read` = 1');
        $stmt->execute();
        $notice = 'All read messages have been cleaned up.';
    } catch (Throwable $e) {
        $error = 'Failed to delete read messages.';
    }
}

// Handle Mark as Read
if (isset($_GET['mark_read'])) {
    $id = (int)$_GET['mark_read'];
    if ($id > 0) {
        $stmt = $pdo->prepare('UPDATE `messages` SET `is_read` = 1 WHERE id = ?');
        $stmt->execute([$id]);
        $notice = 'Message marked as read.';
    }
}

// Handle Mark as Unread
if (isset($_GET['mark_unread'])) {
    $id = (int)$_GET['mark_unread'];
    if ($id > 0) {
        $stmt = $pdo->prepare('UPDATE `messages` SET `is_read` = 0 WHERE id = ?');
        $stmt->execute([$id]);
        $notice = 'Message marked as unread.';
    }
}

// Handle Mark All Read
if (isset($_GET['mark_all_read'])) {
    try {
        $pdo->exec('UPDATE `messages` SET `is_read` = 1 WHERE `is_read` = 0');
        $notice = 'All messages marked as read.';
    } catch (Throwable $e) {}
}

// Fetch all messages
$messages = $pdo->query('SELECT * FROM `messages` ORDER BY id DESC')->fetchAll(PDO::FETCH_ASSOC);

// Calculate Stats
$totalCount  = count($messages);
$unreadCount = 0;
$readCount   = 0;
foreach ($messages as $m) {
    if (empty($m['is_read'])) {
        $unreadCount++;
    } else {
        $readCount++;
    }
}
?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Inbox Messages - Portfolio Admin</title>
  <link rel="icon" type="image/jpeg" href="../Tanui-Sila-Logo-v3.jpg">
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@400;500;600;700;800&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css" crossorigin="anonymous" referrerpolicy="no-referrer" />
  <link rel="stylesheet" href="../assets/css/admin.css?v=1">
  <style>
    .kpi-grid {
      display: grid;
      grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
      gap: 16px;
      margin-bottom: 24px;
    }
    .kpi-card {
      background: var(--panel-alt);
      border: 1px solid var(--line);
      padding: 20px;
      display: flex;
      align-items: center;
      gap: 16px;
    }
    .kpi-icon {
      width: 48px;
      height: 48px;
      display: flex;
      align-items: center;
      justify-content: center;
      font-size: 1.3rem;
      background: rgba(15, 23, 42, 0.05);
      color: var(--text);
    }
    .kpi-val {
      font-size: 1.8rem;
      font-weight: 800;
      color: var(--text);
      line-height: 1;
      margin-bottom: 4px;
    }
    .kpi-lbl {
      font-size: 0.75rem;
      font-weight: 700;
      letter-spacing: 0.08em;
      text-transform: uppercase;
      color: var(--muted);
    }
    .filter-bar {
      display: flex;
      justify-content: space-between;
      align-items: center;
      flex-wrap: wrap;
      gap: 12px;
      margin-bottom: 20px;
      padding-bottom: 16px;
      border-bottom: 1px solid var(--line);
    }
    .filter-tabs {
      display: flex;
      gap: 6px;
    }
    .tab-btn {
      padding: 6px 14px;
      font-size: 0.8rem;
      font-weight: 700;
      border: 1px solid var(--line);
      background: var(--panel-alt);
      color: var(--muted);
      cursor: pointer;
      text-decoration: none;
      transition: all 0.15s ease;
    }
    .tab-btn.active, .tab-btn:hover {
      background: var(--text);
      color: #fff;
      border-color: var(--text);
    }
    .search-input-wrap {
      position: relative;
      min-width: 260px;
    }
    .search-input-wrap input {
      width: 100%;
      padding: 8px 12px 8px 34px;
      font-size: 0.85rem;
      border: 1px solid var(--line);
      background: var(--bg);
      color: var(--text);
      outline: none;
    }
    .search-input-wrap i {
      position: absolute;
      left: 12px;
      top: 50%;
      transform: translateY(-50%);
      color: var(--muted);
      font-size: 0.8rem;
    }
    .message-card {
      background: var(--panel-alt);
      border: 1px solid var(--line);
      padding: 22px;
      margin-bottom: 16px;
      position: relative;
      transition: border-color 0.2s ease, box-shadow 0.2s ease;
    }
    .message-card.unread {
      border-left: 4px solid var(--primary);
      background: rgba(234, 88, 12, 0.03);
    }
    .message-header {
      display: flex;
      justify-content: space-between;
      align-items: flex-start;
      border-bottom: 1px solid var(--line);
      padding-bottom: 12px;
      margin-bottom: 14px;
      flex-wrap: wrap;
      gap: 12px;
    }
    .avatar-circle {
      width: 42px;
      height: 42px;
      background: var(--text);
      color: #fff;
      display: flex;
      align-items: center;
      justify-content: center;
      font-weight: 800;
      font-size: 1.1rem;
      flex-shrink: 0;
    }
    .message-body {
      font-size: 0.95rem;
      line-height: 1.6;
      color: var(--text);
      white-space: pre-wrap;
      background: var(--soft-bg);
      padding: 16px;
      border: 1px solid var(--light-gray);
    }
  </style>
</head>
<body>
  <div class="app-shell">
    <?php echo renderSidebar('messages.php'); ?>

    <main class="main-panel">
      <?php echo renderTopbar('Inbox Messages'); ?>

      <?php if (!empty($notice)): ?>
        <div class="notice"><i class="fa-solid fa-circle-check"></i> <?php echo htmlspecialchars($notice); ?></div>
      <?php endif; ?>

      <?php if (!empty($error)): ?>
        <div class="error-box"><i class="fa-solid fa-triangle-exclamation"></i> <?php echo htmlspecialchars($error); ?></div>
      <?php endif; ?>

      <!-- ═══════════════════════════════════════════════════════ -->
      <!-- STATS OVERVIEW                                          -->
      <!-- ═══════════════════════════════════════════════════════ -->
      <div class="kpi-grid">
        <div class="kpi-card">
          <div class="kpi-icon" style="background: rgba(234, 88, 12, 0.1); color: var(--primary);">
            <i class="fa-solid fa-inbox"></i>
          </div>
          <div>
            <div class="kpi-val"><?php echo $totalCount; ?></div>
            <div class="kpi-lbl">Total Inquiries</div>
          </div>
        </div>

        <div class="kpi-card">
          <div class="kpi-icon" style="background: rgba(239, 68, 68, 0.1); color: #dc2626;">
            <i class="fa-solid fa-envelope-open-text"></i>
          </div>
          <div>
            <div class="kpi-val" style="color: #dc2626;"><?php echo $unreadCount; ?></div>
            <div class="kpi-lbl">Unread Messages</div>
          </div>
        </div>

        <div class="kpi-card">
          <div class="kpi-icon" style="background: rgba(34, 197, 94, 0.1); color: #16a34a;">
            <i class="fa-solid fa-circle-check"></i>
          </div>
          <div>
            <div class="kpi-val"><?php echo $readCount; ?></div>
            <div class="kpi-lbl">Completed / Read</div>
          </div>
        </div>
      </div>

      <!-- ═══════════════════════════════════════════════════════ -->
      <!-- MESSAGES PANEL                                          -->
      <!-- ═══════════════════════════════════════════════════════ -->
      <section class="panel">
        
        <!-- Filter & Search Bar -->
        <div class="filter-bar">
          <div class="filter-tabs">
            <button type="button" class="tab-btn active" onclick="filterMessages('all', this)">
              All Messages (<?php echo $totalCount; ?>)
            </button>
            <button type="button" class="tab-btn" onclick="filterMessages('unread', this)">
              Unread (<?php echo $unreadCount; ?>)
            </button>
            <button type="button" class="tab-btn" onclick="filterMessages('read', this)">
              Read (<?php echo $readCount; ?>)
            </button>
          </div>

          <div style="display: flex; gap: 10px; align-items: center; flex-wrap: wrap;">
            <div class="search-input-wrap">
              <i class="fa-solid fa-magnifying-glass"></i>
              <input type="text" id="search-messages" placeholder="Search by name, email, keyword..." onkeyup="searchMessages()">
            </div>

            <?php if ($unreadCount > 0): ?>
              <a href="messages.php?mark_all_read=1" class="btn small" style="background: var(--primary); color: #fff;">
                <i class="fa-solid fa-check-double"></i> Mark All Read
              </a>
            <?php endif; ?>

            <?php if ($readCount > 0): ?>
              <a href="messages.php?delete_read=1" class="btn small danger" onclick="return confirm('Delete all read messages?');">
                <i class="fa-solid fa-trash-can"></i> Clean Read
              </a>
            <?php endif; ?>
          </div>
        </div>

        <!-- Messages Listing -->
        <?php if (empty($messages)): ?>
          <div style="padding: 40px; text-align: center; color: var(--muted);">
            <i class="fa-solid fa-envelope-open" style="font-size: 2.5rem; opacity: 0.3; margin-bottom: 12px;"></i>
            <p style="margin: 0; font-size: 1rem; font-weight: 600;">Your inbox is clear. No visitor messages received yet.</p>
          </div>
        <?php else: ?>
          <div id="messages-list">
            <?php foreach ($messages as $msg): ?>
              <?php 
                $isRead = !empty($msg['is_read']);
                $initial = strtoupper(substr(trim($msg['name'] ?: 'V'), 0, 1));
                $replySubject = 'Re: ' . ($msg['subject'] ?: 'Inquiry regarding Tanui Sila portfolio');
                $replyBody = "Hi " . htmlspecialchars($msg['name']) . ",\n\nThank you for reaching out regarding your inquiry:\n> " . str_replace("\n", "\n> ", htmlspecialchars($msg['message'])) . "\n\nBest regards,\nTanui Sila";
                $mailtoUrl = 'mailto:' . rawurlencode($msg['email']) . '?subject=' . rawurlencode($replySubject) . '&body=' . rawurlencode($replyBody);
              ?>
              
              <div class="message-card <?php echo $isRead ? 'read' : 'unread'; ?>" data-status="<?php echo $isRead ? 'read' : 'unread'; ?>">
                <div class="message-header">
                  <div style="display: flex; gap: 14px; align-items: flex-start;">
                    <div class="avatar-circle"><?php echo $initial; ?></div>
                    <div>
                      <div style="display: flex; align-items: center; gap: 8px; flex-wrap: wrap;">
                        <strong style="font-size: 1.15rem; color: var(--text);"><?php echo htmlspecialchars($msg['name']); ?></strong>
                        <?php if (!$isRead): ?>
                          <span style="background: #ef4444; color: #fff; padding: 2px 8px; font-size: 0.68rem; font-weight: 800; text-transform: uppercase;">
                            <i class="fa-solid fa-sparkles"></i> Unread
                          </span>
                        <?php else: ?>
                          <span style="background: rgba(15, 23, 42, 0.08); color: var(--muted); padding: 2px 8px; font-size: 0.68rem; font-weight: 700; text-transform: uppercase;">
                            Read
                          </span>
                        <?php endif; ?>
                      </div>

                      <div style="margin-top: 4px; font-size: 0.86rem; color: var(--muted); display: flex; flex-wrap: wrap; gap: 12px; align-items: center;">
                        <span><i class="fa-solid fa-envelope" style="color: var(--primary);"></i> <a href="mailto:<?php echo htmlspecialchars($msg['email']); ?>" style="color: var(--primary); text-decoration: underline;"><?php echo htmlspecialchars($msg['email']); ?></a></span>
                        <?php if (!empty($msg['subject'])): ?>
                          <span><i class="fa-solid fa-tag" style="color: var(--muted);"></i> <strong><?php echo htmlspecialchars($msg['subject']); ?></strong></span>
                        <?php endif; ?>
                      </div>
                    </div>
                  </div>

                  <!-- Date & Action Buttons -->
                  <div style="text-align: right; display: flex; flex-direction: column; align-items: flex-end; gap: 8px;">
                    <span style="font-size: 0.76rem; color: var(--muted); font-family: monospace;">
                      <i class="fa-regular fa-clock"></i> <?php echo htmlspecialchars($msg['created_at']); ?>
                    </span>

                    <div style="display: flex; gap: 6px; flex-wrap: wrap;">
                      <a href="<?php echo $mailtoUrl; ?>" class="btn small primary" title="Reply to sender via Email">
                        <i class="fa-solid fa-reply"></i> Reply
                      </a>
                      
                      <?php if (!$isRead): ?>
                        <a href="messages.php?mark_read=<?php echo (int)$msg['id']; ?>" class="btn small" style="background: var(--bg); border: 1px solid var(--line);" title="Mark message as read">
                          <i class="fa-solid fa-check"></i> Mark Read
                        </a>
                      <?php else: ?>
                        <a href="messages.php?mark_unread=<?php echo (int)$msg['id']; ?>" class="btn small" style="background: var(--bg); border: 1px solid var(--line);" title="Mark message as unread">
                          <i class="fa-solid fa-envelope"></i> Mark Unread
                        </a>
                      <?php endif; ?>

                      <a href="messages.php?delete=<?php echo (int)$msg['id']; ?>" class="btn small danger" onclick="return confirm('Delete message from <?php echo htmlspecialchars($msg['name']); ?>?');" title="Delete message">
                        <i class="fa-solid fa-trash"></i>
                      </a>
                    </div>
                  </div>
                </div>

                <!-- Message Text -->
                <div class="message-body"><?php echo nl2br(htmlspecialchars($msg['message'])); ?></div>
              </div>
            <?php endforeach; ?>
          </div>
        <?php endif; ?>

      </section>
    </main>
  </div>

  <script>
    function filterMessages(status, btn) {
      document.querySelectorAll('.filter-tabs .tab-btn').forEach(b => b.classList.remove('active'));
      btn.classList.add('active');

      const cards = document.querySelectorAll('#messages-list .message-card');
      cards.forEach(card => {
        if (status === 'all' || card.dataset.status === status) {
          card.style.display = 'block';
        } else {
          card.style.display = 'none';
        }
      });
    }

    function searchMessages() {
      const q = document.getElementById('search-messages').value.toLowerCase().trim();
      const cards = document.querySelectorAll('#messages-list .message-card');
      cards.forEach(card => {
        const text = card.textContent.toLowerCase();
        if (text.includes(q)) {
          card.style.display = 'block';
        } else {
          card.style.display = 'none';
        }
      });
    }
  </script>
</body>
</html>
