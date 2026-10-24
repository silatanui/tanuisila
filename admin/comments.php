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

// Handle Actions (Approve / Pending / Spam / Delete)
$action = $_GET['action'] ?? '';
$id = (int) ($_GET['id'] ?? 0);

if (!empty($action) && $id > 0) {
    try {
        if ($action === 'approve') {
            $stmt = $pdo->prepare("UPDATE blog_comments SET status = 'approved' WHERE id = ?");
            $stmt->execute([$id]);
            $notice = 'Comment marked as approved.';
        } elseif ($action === 'pending') {
            $stmt = $pdo->prepare("UPDATE blog_comments SET status = 'pending' WHERE id = ?");
            $stmt->execute([$id]);
            $notice = 'Comment marked as pending moderation.';
        } elseif ($action === 'spam') {
            $stmt = $pdo->prepare("UPDATE blog_comments SET status = 'spam' WHERE id = ?");
            $stmt->execute([$id]);
            $notice = 'Comment marked as spam.';
        } elseif ($action === 'delete') {
            $stmt = $pdo->prepare("DELETE FROM blog_comments WHERE id = ?");
            $stmt->execute([$id]);
            $notice = 'Comment deleted permanently.';
        }
    } catch (Throwable $e) {
        $error = 'Failed to perform action: ' . $e->getMessage();
    }
}

// Handle Bulk Actions
if (isset($_GET['bulk'])) {
    if ($_GET['bulk'] === 'approve_all_pending') {
        $pdo->exec("UPDATE blog_comments SET status = 'approved' WHERE status = 'pending'");
        $notice = 'All pending comments have been approved.';
    } elseif ($_GET['bulk'] === 'delete_all_spam') {
        $pdo->exec("DELETE FROM blog_comments WHERE status = 'spam'");
        $notice = 'All spam comments have been deleted.';
    }
}

// Fetch Filter Parameters
$filterPostId = (int) ($_GET['post_id'] ?? 0);
$filterStatus = trim($_GET['status'] ?? 'all');
if (!in_array($filterStatus, ['all', 'approved', 'pending', 'spam'])) {
    $filterStatus = 'all';
}

// Build query
$whereClauses = [];
$queryParams = [];

if ($filterPostId > 0) {
    $whereClauses[] = "c.post_id = ?";
    $queryParams[] = $filterPostId;
}

if ($filterStatus !== 'all') {
    $whereClauses[] = "c.status = ?";
    $queryParams[] = $filterStatus;
}

$whereSql = !empty($whereClauses) ? 'WHERE ' . implode(' AND ', $whereClauses) : '';

// Fetch Comments with Post Title
$query = "
    SELECT c.*, p.title AS post_title, p.slug AS post_slug
    FROM blog_comments c
    LEFT JOIN blog_posts p ON c.post_id = p.id
    {$whereSql}
    ORDER BY c.created_at DESC, c.id DESC
";
$stmt = $pdo->prepare($query);
$stmt->execute($queryParams);
$commentsList = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Calculate Stats
$totalComments = (int)$pdo->query("SELECT COUNT(*) FROM blog_comments")->fetchColumn();
$approvedCount = (int)$pdo->query("SELECT COUNT(*) FROM blog_comments WHERE status = 'approved'")->fetchColumn();
$pendingCount  = (int)$pdo->query("SELECT COUNT(*) FROM blog_comments WHERE status = 'pending'")->fetchColumn();
$spamCount     = (int)$pdo->query("SELECT COUNT(*) FROM blog_comments WHERE status = 'spam'")->fetchColumn();

// If filtering by post, get post title
$filteredPostTitle = '';
if ($filterPostId > 0) {
    $pStmt = $pdo->prepare("SELECT title FROM blog_posts WHERE id = ?");
    $pStmt->execute([$filterPostId]);
    $filteredPostTitle = $pStmt->fetchColumn() ?: '';
}
?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Blog Comments - Admin</title>
  <link rel="icon" type="image/jpeg" href="../Tanui-Sila-Logo-v3.jpg">
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@400;500;600;700;800&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css" crossorigin="anonymous" referrerpolicy="no-referrer" />
  <link rel="stylesheet" href="../assets/css/admin.css?v=15">
  <?php require_once __DIR__ . '/datatables.php'; adminDataTablesHead(); ?>
  <style>
    .comment-card {
      background: var(--panel-alt);
      border: 1px solid var(--line);
      padding: 20px;
      margin-bottom: 16px;
      transition: border-color 0.15s ease;
      position: relative;
    }
    .comment-card.pending {
      border-left: 4px solid var(--warning);
      background: rgba(217, 119, 6, 0.03);
    }
    .comment-card.spam {
      border-left: 4px solid var(--danger);
      opacity: 0.85;
      background: rgba(220, 38, 38, 0.03);
    }
    .comment-card.approved {
      border-left: 4px solid var(--success);
    }
    .comment-meta-row {
      display: flex;
      justify-content: space-between;
      align-items: flex-start;
      border-bottom: 1px solid var(--line);
      padding-bottom: 12px;
      margin-bottom: 12px;
      flex-wrap: wrap;
      gap: 12px;
    }
    .status-badge {
      display: inline-block;
      padding: 2px 8px;
      font-size: 0.7rem;
      font-weight: 800;
      text-transform: uppercase;
      letter-spacing: 0.04em;
    }
    .status-badge.approved {
      background: var(--success-soft);
      color: var(--success);
      border: 1px solid var(--success-border);
    }
    .status-badge.pending {
      background: var(--warning-soft);
      color: var(--warning);
      border: 1px solid var(--warning-border);
    }
    .status-badge.spam {
      background: var(--danger-soft);
      color: var(--danger);
      border: 1px solid var(--danger-border);
    }
    .filter-btn {
      padding: 6px 14px;
      font-size: 0.8rem;
      font-weight: 700;
      border: 1px solid var(--line);
      background: var(--panel-alt);
      color: var(--muted);
      text-decoration: none;
      transition: all 0.15s ease;
      display: inline-flex;
      align-items: center;
      gap: 6px;
    }
    .filter-btn.active, .filter-btn:hover {
      background: var(--text);
      color: #ffffff;
      border-color: var(--text);
    }
  </style>
</head>
<body>
  <div class="app-shell">
    <?php echo renderSidebar('comments.php'); ?>
    <main class="main-panel">
      <?php echo renderTopbar('Blog Comments'); ?>

      <?php if (!empty($notice)): ?>
        <div class="notice"><i class="fa-solid fa-circle-check"></i> <?php echo htmlspecialchars($notice); ?></div>
      <?php endif; ?>

      <?php if (!empty($error)): ?>
        <div class="error-box"><i class="fa-solid fa-triangle-exclamation"></i> <?php echo htmlspecialchars($error); ?></div>
      <?php endif; ?>

      <!-- ═══════════════════════════════════════════════════════ -->
      <!-- STATS KPI OVERVIEW                                      -->
      <!-- ═══════════════════════════════════════════════════════ -->
      <section class="stats-grid">
        <div class="stat-card">
          <span class="label">Total Comments</span>
          <div class="value"><?php echo $totalComments; ?></div>
        </div>

        <div class="stat-card" style="border-top-color: var(--success);">
          <span class="label">Approved</span>
          <div class="value" style="color: var(--success);"><?php echo $approvedCount; ?></div>
        </div>

        <div class="stat-card" style="border-top-color: var(--warning);">
          <span class="label">Pending Moderation</span>
          <div class="value" style="color: var(--warning);"><?php echo $pendingCount; ?></div>
        </div>

        <div class="stat-card" style="border-top-color: var(--danger);">
          <span class="label">Spam / Flagged</span>
          <div class="value" style="color: var(--danger);"><?php echo $spamCount; ?></div>
        </div>
      </section>

      <!-- ═══════════════════════════════════════════════════════ -->
      <!-- COMMENTS MANAGEMENT PANEL                               -->
      <!-- ═══════════════════════════════════════════════════════ -->
      <section class="panel">
        <div style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 16px; margin-bottom: 24px; padding-bottom: 16px; border-bottom: 1px solid var(--line);">
          <div>
            <h2 style="margin: 0 0 6px 0;">
              <i class="fa-solid fa-comments" style="color: var(--primary);"></i> 
              <?php if ($filterPostId > 0): ?>
                Comments on "<?php echo htmlspecialchars($filteredPostTitle); ?>" (<?php echo count($commentsList); ?>)
              <?php else: ?>
                Manage Blog Comments (<?php echo count($commentsList); ?>)
              <?php endif; ?>
            </h2>
            <?php if ($filterPostId > 0): ?>
              <a href="comments.php" style="font-size: 0.82rem; color: var(--primary); text-decoration: underline; font-weight: 600;">
                ← Show comments from all posts
              </a>
            <?php endif; ?>
          </div>

          <!-- Filter Tabs -->
          <div style="display: flex; gap: 8px; flex-wrap: wrap; align-items: center;">
            <a href="?status=all<?php echo $filterPostId ? '&post_id=' . $filterPostId : ''; ?>" class="filter-btn <?php echo $filterStatus === 'all' ? 'active' : ''; ?>">
              All (<?php echo $totalComments; ?>)
            </a>
            <a href="?status=approved<?php echo $filterPostId ? '&post_id=' . $filterPostId : ''; ?>" class="filter-btn <?php echo $filterStatus === 'approved' ? 'active' : ''; ?>">
              Approved (<?php echo $approvedCount; ?>)
            </a>
            <a href="?status=pending<?php echo $filterPostId ? '&post_id=' . $filterPostId : ''; ?>" class="filter-btn <?php echo $filterStatus === 'pending' ? 'active' : ''; ?>">
              Pending (<?php echo $pendingCount; ?>)
            </a>
            <a href="?status=spam<?php echo $filterPostId ? '&post_id=' . $filterPostId : ''; ?>" class="filter-btn <?php echo $filterStatus === 'spam' ? 'active' : ''; ?>">
              Spam (<?php echo $spamCount; ?>)
            </a>
          </div>
        </div>

        <div style="display: flex; justify-content: flex-end; gap: 8px; flex-wrap: wrap; margin-bottom: 16px;">
          <?php if ($pendingCount > 0): ?>
            <a href="comments.php?bulk=approve_all_pending" class="btn small" style="background: var(--success); color: #fff; border-color: var(--success);" onclick="return confirm('Approve all pending comments?');">
              <i class="fa-solid fa-check-double"></i> Approve All Pending
            </a>
          <?php endif; ?>
          <?php if ($spamCount > 0): ?>
            <a href="comments.php?bulk=delete_all_spam" class="btn small danger" onclick="return confirm('Delete all spam comments permanently?');">
              <i class="fa-solid fa-trash-can"></i> Clear All Spam
            </a>
          <?php endif; ?>
        </div>

        <?php if (empty($commentsList)): ?>
          <div style="padding: 40px; text-align: center; color: var(--muted);">
            <i class="fa-regular fa-comments" style="font-size: 2.5rem; opacity: 0.3; margin-bottom: 12px; display: block;"></i>
            <p style="margin: 0; font-size: 1rem; font-weight: 600;">No comments found in this category.</p>
          </div>
        <?php else: ?>
          <div class="table-wrap">
            <table id="comments-table" class="data-table display" style="width:100%" data-page-length="10" data-order='[[4,"desc"]]'>
              <thead>
                <tr>
                  <th>Author</th>
                  <th>Email</th>
                  <th>Post</th>
                  <th>Comment</th>
                  <th>Status</th>
                  <th>Date</th>
                  <th class="no-sort">Actions</th>
                </tr>
              </thead>
              <tbody>
                <?php foreach ($commentsList as $comm):
                  $currentStatus = $comm['status'] ?? 'approved';
                  $createdFormatted = date('M d, Y g:i A', strtotime($comm['created_at']));
                  $dateTs = strtotime($comm['created_at']) ?: 0;
                  $text = (string)($comm['comment_text'] ?? '');
                ?>
                  <tr>
                    <td><strong><?php echo htmlspecialchars($comm['author_name']); ?></strong></td>
                    <td>
                      <?php if (!empty($comm['author_email'])): ?>
                        <a href="mailto:<?php echo htmlspecialchars($comm['author_email']); ?>" style="color:var(--primary);"><?php echo htmlspecialchars($comm['author_email']); ?></a>
                      <?php else: ?>
                        <span style="color:var(--muted);font-style:italic;">—</span>
                      <?php endif; ?>
                    </td>
                    <td>
                      <?php if (!empty($comm['post_title'])): ?>
                        <a href="../post.php?slug=<?php echo urlencode($comm['post_slug']); ?>#discussion" target="_blank" rel="noopener"><?php echo htmlspecialchars($comm['post_title']); ?></a>
                      <?php else: ?>
                        —
                      <?php endif; ?>
                    </td>
                    <td><?php echo htmlspecialchars(strlen($text) > 120 ? substr($text, 0, 117) . '…' : $text); ?></td>
                    <td><span class="status-pill <?php echo htmlspecialchars($currentStatus); ?>"><?php echo htmlspecialchars(ucfirst($currentStatus)); ?></span></td>
                    <td data-order="<?php echo (int)$dateTs; ?>"><?php echo $createdFormatted; ?></td>
                    <td class="actions-cell">
                      <?php if ($currentStatus !== 'approved'): ?>
                        <a href="?action=approve&id=<?php echo (int)$comm['id']; ?>&status=<?php echo urlencode($filterStatus); ?>" class="btn small" style="background:var(--success);color:#fff;border-color:var(--success);" title="Approve"><i class="fa-solid fa-check"></i></a>
                      <?php endif; ?>
                      <?php if ($currentStatus !== 'pending'): ?>
                        <a href="?action=pending&id=<?php echo (int)$comm['id']; ?>&status=<?php echo urlencode($filterStatus); ?>" class="btn small" title="Pending"><i class="fa-solid fa-hourglass-half"></i></a>
                      <?php endif; ?>
                      <?php if ($currentStatus !== 'spam'): ?>
                        <a href="?action=spam&id=<?php echo (int)$comm['id']; ?>&status=<?php echo urlencode($filterStatus); ?>" class="btn small" style="color:var(--danger);" title="Spam"><i class="fa-solid fa-ban"></i></a>
                      <?php endif; ?>
                      <a href="?action=delete&id=<?php echo (int)$comm['id']; ?>&status=<?php echo urlencode($filterStatus); ?>" class="btn small danger" onclick="return confirm('Delete comment from <?php echo htmlspecialchars(addslashes($comm['author_name'])); ?>?');" title="Delete"><i class="fa-solid fa-trash"></i></a>
                    </td>
                  </tr>
                <?php endforeach; ?>
              </tbody>
            </table>
          </div>
        <?php endif; ?>
      </section>
    </main>
  </div>

  <?php adminDataTablesFooter(); ?>
</body>
</html>
