<?php
session_start();
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../includes/analytics.php';
require_once __DIR__ . '/sidebar.php';
require_once __DIR__ . '/topbar.php';

if (empty($_SESSION['admin_logged_in'])) {
    header('Location: login.php');
    exit;
}

$notice = '';
$error = '';

// Counts
$counts = [
    'education' => 0,
    'experience' => 0,
    'publications' => 0,
    'projects' => 0,
    'blog_published' => 0,
    'blog_draft' => 0,
    'blog_all' => 0,
    'comments_total' => 0,
    'comments_pending' => 0,
    'messages_total' => 0,
    'messages_unread' => 0,
    'blog_views_total' => 0,
];

try {
    $counts['education'] = (int) $pdo->query('SELECT COUNT(*) FROM education')->fetchColumn();
    $counts['experience'] = (int) $pdo->query('SELECT COUNT(*) FROM experience')->fetchColumn();
    $counts['publications'] = (int) $pdo->query('SELECT COUNT(*) FROM publications')->fetchColumn();
    $counts['projects'] = (int) $pdo->query('SELECT COUNT(*) FROM projects')->fetchColumn();
    $counts['blog_all'] = (int) $pdo->query('SELECT COUNT(*) FROM blog_posts')->fetchColumn();
    $counts['blog_published'] = (int) $pdo->query("SELECT COUNT(*) FROM blog_posts WHERE status = 'published'")->fetchColumn();
    $counts['blog_draft'] = (int) $pdo->query("SELECT COUNT(*) FROM blog_posts WHERE status = 'draft'")->fetchColumn();
    $counts['comments_total'] = (int) $pdo->query('SELECT COUNT(*) FROM blog_comments')->fetchColumn();
    $counts['comments_pending'] = (int) $pdo->query("SELECT COUNT(*) FROM blog_comments WHERE status = 'pending'")->fetchColumn();
    $counts['messages_total'] = (int) $pdo->query('SELECT COUNT(*) FROM messages')->fetchColumn();
    $counts['messages_unread'] = (int) $pdo->query('SELECT COUNT(*) FROM messages WHERE is_read = 0')->fetchColumn();
    $counts['blog_views_total'] = (int) $pdo->query('SELECT COALESCE(SUM(views), 0) FROM blog_posts')->fetchColumn();
} catch (Throwable $e) {
    $error = 'Some dashboard stats could not be loaded.';
}

$today = date('Y-m-d');
$yesterday = date('Y-m-d', strtotime('-1 day'));
$weekAgo = date('Y-m-d', strtotime('-6 days'));
$monthAgo = date('Y-m-d', strtotime('-29 days'));

$uniqueToday = analyticsCountUniqueOn($pdo, $today);
$uniqueYesterday = analyticsCountUniqueOn($pdo, $yesterday);
$unique7 = analyticsCountUniqueSince($pdo, $weekAgo);
$unique30 = analyticsCountUniqueSince($pdo, $monthAgo);

$visitorSeries = analyticsDailyUniqueSeries($pdo, 30);
$blogReaderSeries = analyticsDailyBlogReadersSeries($pdo, 30);

$topPosts = [];
try {
    $topPosts = $pdo->query("
        SELECT id, title, slug, views, status
        FROM blog_posts
        WHERE status = 'published'
        ORDER BY views DESC, id DESC
        LIMIT 6
    ")->fetchAll(PDO::FETCH_ASSOC);
} catch (Throwable $e) {}

$recentMessages = [];
try {
    $recentMessages = $pdo->query("
        SELECT id, name, subject, created_at, is_read
        FROM messages
        ORDER BY id DESC
        LIMIT 5
    ")->fetchAll(PDO::FETCH_ASSOC);
} catch (Throwable $e) {}

$chartPayload = [
    'visitors' => $visitorSeries,
    'blogReaders' => $blogReaderSeries,
    'content' => [
        'labels' => ['Blog', 'Projects', 'Education', 'Experience', 'Publications'],
        'values' => [
            $counts['blog_all'],
            $counts['projects'],
            $counts['education'],
            $counts['experience'],
            $counts['publications'],
        ],
    ],
    'blogStatus' => [
        'labels' => ['Published', 'Draft', 'Other'],
        'values' => [
            $counts['blog_published'],
            $counts['blog_draft'],
            max(0, $counts['blog_all'] - $counts['blog_published'] - $counts['blog_draft']),
        ],
    ],
];
?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Portfolio Dashboard</title>
  <link rel="icon" type="image/jpeg" href="../Tanui-Sila-Logo-v3.jpg">
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@400;500;600;700;800&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css" crossorigin="anonymous" referrerpolicy="no-referrer" />
  <link rel="stylesheet" href="../assets/css/admin.css?v=4">
  <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.6/dist/chart.umd.min.js" defer></script>
</head>
<body>
  <div class="app-shell">
    <?php echo renderSidebar('index.php'); ?>

    <main class="main-panel">
      <?php echo renderTopbar('Dashboard'); ?>

      <?php if (!empty($notice)): ?>
        <div class="notice"><?php echo htmlspecialchars($notice); ?></div>
      <?php endif; ?>
      <?php if (!empty($error)): ?>
        <div class="error-box"><?php echo htmlspecialchars($error); ?></div>
      <?php endif; ?>

      <?php if ($counts['messages_unread'] > 0): ?>
        <div class="error-box dash-alert" style="border-left: 4px solid var(--danger);">
          <div style="display:flex; align-items:center; gap:12px;">
            <i class="fa-solid fa-bell" style="color: var(--danger);"></i>
            <span style="font-weight:700;">You have <?php echo (int) $counts['messages_unread']; ?> unread message(s).</span>
          </div>
          <a href="messages.php" class="btn primary small"><i class="fa-solid fa-envelope-open-text"></i> Inbox</a>
        </div>
      <?php endif; ?>

      <section class="dash-kpis">
        <article class="dash-kpi dash-kpi--accent">
          <span class="dash-kpi__label">Unique visitors today</span>
          <strong class="dash-kpi__value"><?php echo number_format($uniqueToday); ?></strong>
          <span class="dash-kpi__meta">Yesterday: <?php echo number_format($uniqueYesterday); ?></span>
        </article>
        <article class="dash-kpi">
          <span class="dash-kpi__label">Unique · last 7 days</span>
          <strong class="dash-kpi__value"><?php echo number_format($unique7); ?></strong>
          <span class="dash-kpi__meta">One count per person / day</span>
        </article>
        <article class="dash-kpi">
          <span class="dash-kpi__label">Unique · last 30 days</span>
          <strong class="dash-kpi__value"><?php echo number_format($unique30); ?></strong>
          <span class="dash-kpi__meta">Site-wide (not pageviews)</span>
        </article>
        <article class="dash-kpi">
          <span class="dash-kpi__label">Blog post views</span>
          <strong class="dash-kpi__value"><?php echo number_format($counts['blog_views_total']); ?></strong>
          <span class="dash-kpi__meta"><?php echo (int) $counts['blog_published']; ?> published posts</span>
        </article>
        <article class="dash-kpi">
          <span class="dash-kpi__label">Unread messages</span>
          <strong class="dash-kpi__value"><?php echo number_format($counts['messages_unread']); ?></strong>
          <span class="dash-kpi__meta"><?php echo (int) $counts['messages_total']; ?> total</span>
        </article>
        <article class="dash-kpi">
          <span class="dash-kpi__label">Pending comments</span>
          <strong class="dash-kpi__value"><?php echo number_format($counts['comments_pending']); ?></strong>
          <span class="dash-kpi__meta"><?php echo (int) $counts['comments_total']; ?> total</span>
        </article>
      </section>

      <section class="dash-charts">
        <div class="panel dash-chart-card dash-chart-card--wide">
          <div class="admin-list-head">
            <div>
              <h2 style="margin:0;">Unique visitors / day</h2>
              <p class="dash-chart-card__hint">Each person is counted once per day, even if they open many pages.</p>
            </div>
          </div>
          <div class="dash-chart-wrap">
            <canvas id="chart-unique-visitors" height="110" aria-label="Unique visitors line chart"></canvas>
          </div>
        </div>

        <div class="panel dash-chart-card">
          <div class="admin-list-head">
            <div>
              <h2 style="margin:0;">Blog readers / day</h2>
              <p class="dash-chart-card__hint">Distinct devices reading posts (24h window per post).</p>
            </div>
          </div>
          <div class="dash-chart-wrap">
            <canvas id="chart-blog-readers" height="160" aria-label="Blog readers bar chart"></canvas>
          </div>
        </div>

        <div class="panel dash-chart-card">
          <div class="admin-list-head">
            <div>
              <h2 style="margin:0;">Content mix</h2>
              <p class="dash-chart-card__hint">Portfolio inventory by section.</p>
            </div>
          </div>
          <div class="dash-chart-wrap dash-chart-wrap--donut">
            <canvas id="chart-content-mix" aria-label="Content mix doughnut chart"></canvas>
          </div>
        </div>

        <div class="panel dash-chart-card">
          <div class="admin-list-head">
            <div>
              <h2 style="margin:0;">Blog status</h2>
              <p class="dash-chart-card__hint">Published vs draft posts.</p>
            </div>
          </div>
          <div class="dash-chart-wrap dash-chart-wrap--donut">
            <canvas id="chart-blog-status" aria-label="Blog status doughnut chart"></canvas>
          </div>
        </div>
      </section>

      <section class="dash-split">
        <div class="panel">
          <div class="admin-list-head">
            <h2 style="margin:0;"><i class="fa-solid fa-fire" style="color:var(--primary);"></i> Top posts by views</h2>
            <a href="blog.php" class="btn small">Manage blog</a>
          </div>
          <?php if (empty($topPosts)): ?>
            <p class="muted-copy">No published posts yet.</p>
          <?php else: ?>
            <div class="table-wrap">
              <table class="data-table" style="width:100%;">
                <thead>
                  <tr>
                    <th>Title</th>
                    <th>Views</th>
                    <th class="no-sort">Open</th>
                  </tr>
                </thead>
                <tbody>
                  <?php foreach ($topPosts as $p): ?>
                    <tr>
                      <td><strong><?php echo htmlspecialchars($p['title']); ?></strong></td>
                      <td data-order="<?php echo (int) $p['views']; ?>"><?php echo number_format((int) $p['views']); ?></td>
                      <td class="actions-cell">
                        <a class="btn small" href="../public/post.php?slug=<?php echo urlencode($p['slug']); ?>" target="_blank" rel="noopener">View</a>
                      </td>
                    </tr>
                  <?php endforeach; ?>
                </tbody>
              </table>
            </div>
          <?php endif; ?>
        </div>

        <div class="panel">
          <div class="admin-list-head">
            <h2 style="margin:0;"><i class="fa-solid fa-inbox" style="color:var(--primary);"></i> Recent messages</h2>
            <a href="messages.php" class="btn small">Inbox</a>
          </div>
          <?php if (empty($recentMessages)): ?>
            <p class="muted-copy">No messages yet.</p>
          <?php else: ?>
            <ul class="dash-feed">
              <?php foreach ($recentMessages as $m): ?>
                <li class="dash-feed__item<?php echo empty($m['is_read']) ? ' is-unread' : ''; ?>">
                  <div>
                    <strong><?php echo htmlspecialchars($m['name']); ?></strong>
                    <span><?php echo htmlspecialchars($m['subject'] ?: '(no subject)'); ?></span>
                  </div>
                  <time><?php echo !empty($m['created_at']) ? date('M j', strtotime($m['created_at'])) : ''; ?></time>
                </li>
              <?php endforeach; ?>
            </ul>
          <?php endif; ?>
        </div>
      </section>

      <section class="panel">
        <div class="admin-list-head">
          <h2 style="margin:0;">Quick links</h2>
        </div>
        <div class="quick-grid">
          <a href="blog.php" class="quick-card">
            <div class="icon-wrap"><i class="fa-solid fa-pen-nib"></i></div>
            <h3>Blog Posts</h3>
            <p><?php echo (int) $counts['blog_published']; ?> published · <?php echo (int) $counts['blog_draft']; ?> drafts</p>
          </a>
          <a href="projects.php" class="quick-card">
            <div class="icon-wrap"><i class="fa-solid fa-rocket"></i></div>
            <h3>Projects</h3>
            <p><?php echo (int) $counts['projects']; ?> projects in your portfolio.</p>
          </a>
          <a href="comments.php" class="quick-card">
            <div class="icon-wrap"><i class="fa-solid fa-comments"></i></div>
            <h3>Comments</h3>
            <p><?php echo (int) $counts['comments_pending']; ?> awaiting moderation.</p>
          </a>
          <a href="messages.php" class="quick-card">
            <div class="icon-wrap"><i class="fa-solid fa-envelope"></i></div>
            <h3>Messages</h3>
            <p><?php echo (int) $counts['messages_unread']; ?> unread in the inbox.</p>
          </a>
          <a href="profile.php" class="quick-card">
            <div class="icon-wrap"><i class="fa-solid fa-user"></i></div>
            <h3>Profile</h3>
            <p>Bio, contact details, and social links.</p>
          </a>
          <a href="settings.php" class="quick-card">
            <div class="icon-wrap"><i class="fa-solid fa-gear"></i></div>
            <h3>Settings</h3>
            <p>Site title, branding, and preferences.</p>
          </a>
        </div>
      </section>
    </main>
  </div>

  <script>
  window.__DASH_CHARTS = <?php echo json_encode($chartPayload, JSON_UNESCAPED_UNICODE); ?>;
  </script>
  <script>
  document.addEventListener('DOMContentLoaded', function () {
    if (typeof Chart === 'undefined' || !window.__DASH_CHARTS) return;

    var data = window.__DASH_CHARTS;
    var brand = '#ea580c';
    var slate = '#0f172a';
    var muted = '#94a3b8';
    var grid = 'rgba(15, 23, 42, 0.08)';

    Chart.defaults.font.family = 'Outfit, sans-serif';
    Chart.defaults.color = muted;

    var visitorCtx = document.getElementById('chart-unique-visitors');
    if (visitorCtx) {
      new Chart(visitorCtx, {
        type: 'line',
        data: {
          labels: data.visitors.labels,
          datasets: [{
            label: 'Unique visitors',
            data: data.visitors.values,
            borderColor: brand,
            backgroundColor: 'rgba(234, 88, 12, 0.12)',
            fill: true,
            tension: 0.35,
            pointRadius: 3,
            pointHoverRadius: 5,
            borderWidth: 2.5
          }]
        },
        options: {
          responsive: true,
          maintainAspectRatio: false,
          plugins: {
            legend: { display: false },
            tooltip: {
              callbacks: {
                label: function (ctx) {
                  var n = ctx.parsed.y || 0;
                  return n + (n === 1 ? ' unique visitor' : ' unique visitors');
                }
              }
            }
          },
          scales: {
            x: { grid: { display: false }, ticks: { maxRotation: 0, autoSkipPadding: 12 } },
            y: {
              beginAtZero: true,
              ticks: { precision: 0 },
              grid: { color: grid }
            }
          }
        }
      });
    }

    var readersCtx = document.getElementById('chart-blog-readers');
    if (readersCtx) {
      new Chart(readersCtx, {
        type: 'bar',
        data: {
          labels: data.blogReaders.labels,
          datasets: [{
            label: 'Distinct readers',
            data: data.blogReaders.values,
            backgroundColor: 'rgba(15, 23, 42, 0.85)',
            hoverBackgroundColor: brand,
            borderRadius: 0,
            maxBarThickness: 18
          }]
        },
        options: {
          responsive: true,
          maintainAspectRatio: false,
          plugins: { legend: { display: false } },
          scales: {
            x: {
              grid: { display: false },
              ticks: {
                maxRotation: 0,
                callback: function (val, i) {
                  var label = this.getLabelForValue(val);
                  return i % 3 === 0 ? label : '';
                }
              }
            },
            y: { beginAtZero: true, ticks: { precision: 0 }, grid: { color: grid } }
          }
        }
      });
    }

    var mixCtx = document.getElementById('chart-content-mix');
    if (mixCtx) {
      new Chart(mixCtx, {
        type: 'doughnut',
        data: {
          labels: data.content.labels,
          datasets: [{
            data: data.content.values,
            backgroundColor: ['#ea580c', '#0f172a', '#c2410c', '#334155', '#fb923c'],
            borderWidth: 0
          }]
        },
        options: {
          responsive: true,
          maintainAspectRatio: false,
          plugins: {
            legend: { position: 'bottom', labels: { boxWidth: 12, padding: 14 } }
          },
          cutout: '62%'
        }
      });
    }

    var statusCtx = document.getElementById('chart-blog-status');
    if (statusCtx) {
      new Chart(statusCtx, {
        type: 'doughnut',
        data: {
          labels: data.blogStatus.labels,
          datasets: [{
            data: data.blogStatus.values,
            backgroundColor: ['#ea580c', '#cbd5e1', '#64748b'],
            borderWidth: 0
          }]
        },
        options: {
          responsive: true,
          maintainAspectRatio: false,
          plugins: {
            legend: { position: 'bottom', labels: { boxWidth: 12, padding: 14 } }
          },
          cutout: '62%'
        }
      });
    }
  });
  </script>
</body>
</html>
