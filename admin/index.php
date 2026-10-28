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
$networks7 = analyticsCountUniqueNetworksSince($pdo, $weekAgo);
$networks30 = analyticsCountUniqueNetworksSince($pdo, $monthAgo);

$visitorSeries = analyticsDailyUniqueSeries($pdo, 30);
$blogReaderSeries = analyticsDailyBlogReadersSeries($pdo, 30);
$countryBreakdown = analyticsCountryBreakdown($pdo, 30);
$projectInterestSeries = analyticsDailyProjectInterestSeries($pdo, 30);
$topProjects = analyticsTopProjects($pdo, 8);

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

$topPostLabels = [];
$topPostValues = [];
foreach ($topPosts as $p) {
    $topPostLabels[] = trim((string) ($p['title'] ?? 'Untitled')) ?: 'Untitled';
    $topPostValues[] = (int) ($p['views'] ?? 0);
}

$projectBarLabels = [];
$projectBarViews = [];
$projectBarClicks = [];
foreach ($topProjects as $proj) {
    $title = trim((string) ($proj['title'] ?? 'Untitled')) ?: 'Untitled';
    if (function_exists('mb_strlen') && mb_strlen($title) > 36) {
        $title = mb_substr($title, 0, 33) . '…';
    } elseif (strlen($title) > 36) {
        $title = substr($title, 0, 33) . '…';
    }
    $projectBarLabels[] = $title;
    $projectBarViews[] = (int) ($proj['views'] ?? 0);
    $projectBarClicks[] = (int) ($proj['clicks'] ?? 0);
}

$chartPayload = [
    'visitors' => $visitorSeries,
    'blogReaders' => $blogReaderSeries,
    'countries' => $countryBreakdown['series'],
    'countryNames' => $countryBreakdown['names'] ?? [],
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
    'topPosts' => [
        'labels' => $topPostLabels,
        'values' => $topPostValues,
    ],
    'projectInterest' => $projectInterestSeries,
    'topProjects' => [
        'labels' => $projectBarLabels,
        'views' => $projectBarViews,
        'clicks' => $projectBarClicks,
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
  <link rel="stylesheet" href="../assets/css/admin.css?v=17">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/jsvectormap@1.5.3/dist/css/jsvectormap.min.css">
  <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.6/dist/chart.umd.min.js" defer></script>
  <script src="https://cdn.jsdelivr.net/npm/jsvectormap@1.5.3/dist/js/jsvectormap.min.js" defer></script>
  <script src="https://cdn.jsdelivr.net/npm/jsvectormap@1.5.3/dist/maps/world.js" defer></script>
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
          <span class="dash-kpi__meta"><?php echo number_format($networks7); ?> networks (IPs)</span>
        </article>
        <article class="dash-kpi">
          <span class="dash-kpi__label">Unique · last 30 days</span>
          <strong class="dash-kpi__value"><?php echo number_format($unique30); ?></strong>
          <span class="dash-kpi__meta"><?php echo number_format($networks30); ?> networks · IP + browser</span>
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

      <section class="panel dash-map-panel">
        <div class="admin-list-head">
          <div>
            <h2 style="margin:0;">Visitors by country</h2>
            <p class="dash-chart-card__hint">
              Squares show visitor bands that auto-scale with traffic (last 30 days).
              Orange dots mark small or missing places (e.g. Hong Kong) that the outline cannot shade clearly.
              Location is approximate from IP.
            </p>
          </div>
          <span class="dash-map-total"><?php echo number_format((int) $countryBreakdown['total']); ?> located</span>
        </div>
        <div class="dash-map-layout">
          <div class="dash-map-wrap">
            <div id="world-visitors-map" class="dash-world-map" role="img" aria-label="World map of unique visitors by country"></div>
            <div class="dash-map-legend" id="world-visitors-legend" aria-label="Visitor count legend"></div>
          </div>
          <aside class="dash-country-list">
            <h3>All places</h3>
            <?php if (empty($countryBreakdown['top'])): ?>
              <p class="muted-copy">No country data yet. It appears as real visitors arrive.</p>
            <?php else: ?>
              <ol>
                <?php foreach ($countryBreakdown['top'] as $row): ?>
                  <li>
                    <div>
                      <strong><?php echo htmlspecialchars($row['name']); ?></strong>
                      <?php if (!empty($row['city'])): ?>
                        <span><?php echo htmlspecialchars($row['city']); ?></span>
                      <?php else: ?>
                        <span><?php echo htmlspecialchars($row['code']); ?></span>
                      <?php endif; ?>
                    </div>
                    <em><?php echo number_format((int) $row['count']); ?></em>
                  </li>
                <?php endforeach; ?>
              </ol>
            <?php endif; ?>
          </aside>
        </div>
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
          <div class="dash-pie">
            <div class="dash-chart-wrap dash-chart-wrap--donut">
              <canvas id="chart-content-mix" aria-label="Content mix doughnut chart"></canvas>
            </div>
            <ul class="dash-pie-legend" id="legend-content-mix"></ul>
          </div>
        </div>

        <div class="panel dash-chart-card">
          <div class="admin-list-head">
            <div>
              <h2 style="margin:0;">Top posts by views</h2>
              <p class="dash-chart-card__hint">Share of views among your most-read published posts.</p>
            </div>
          </div>
          <div class="dash-pie">
            <div class="dash-chart-wrap dash-chart-wrap--donut">
              <canvas id="chart-top-posts" aria-label="Top posts by views pie chart"></canvas>
            </div>
            <ul class="dash-pie-legend" id="legend-top-posts"></ul>
          </div>
        </div>

        <div class="panel dash-chart-card dash-chart-card--wide">
          <div class="admin-list-head">
            <div>
              <h2 style="margin:0;">Project interest / day</h2>
              <p class="dash-chart-card__hint">Distinct people who saw a project card or launched a live demo (24h window per project).</p>
            </div>
          </div>
          <div class="dash-chart-wrap">
            <canvas id="chart-project-interest" height="120" aria-label="Project interest line chart"></canvas>
          </div>
        </div>

        <div class="panel dash-chart-card dash-chart-card--wide">
          <div class="admin-list-head">
            <div>
              <h2 style="margin:0;">Top projects: views vs launches</h2>
              <p class="dash-chart-card__hint">Card views on the projects page versus outbound “Launch live” clicks.</p>
            </div>
          </div>
          <div class="dash-chart-wrap dash-chart-wrap--bars">
            <canvas id="chart-top-projects" aria-label="Top projects horizontal bar chart"></canvas>
          </div>
        </div>
      </section>

      <section class="dash-split">
        <div class="panel">
          <div class="admin-list-head">
            <h2 style="margin:0;"><i class="fa-solid fa-rocket" style="color:var(--primary);"></i> Top projects</h2>
            <a href="projects.php" class="btn small">Manage projects</a>
          </div>
          <?php if (empty($topProjects)): ?>
            <p class="muted-copy">No projects yet.</p>
          <?php else: ?>
            <div class="table-wrap">
              <table class="data-table" style="width:100%;">
                <thead>
                  <tr>
                    <th>Project</th>
                    <th>Views</th>
                    <th>Launches</th>
                    <th class="no-sort">Open</th>
                  </tr>
                </thead>
                <tbody>
                  <?php foreach ($topProjects as $proj): ?>
                    <tr>
                      <td><strong><?php echo htmlspecialchars($proj['title']); ?></strong></td>
                      <td data-order="<?php echo (int) $proj['views']; ?>"><?php echo number_format((int) $proj['views']); ?></td>
                      <td data-order="<?php echo (int) $proj['clicks']; ?>"><?php echo number_format((int) $proj['clicks']); ?></td>
                      <td class="actions-cell">
                        <?php if (!empty($proj['url'])): ?>
                          <a class="btn small" href="<?php echo htmlspecialchars($proj['url']); ?>" target="_blank" rel="noopener">Live</a>
                        <?php else: ?>
                          <span class="muted-copy">—</span>
                        <?php endif; ?>
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

    </main>
  </div>

  <script>
  window.__DASH_CHARTS = <?php echo json_encode($chartPayload, JSON_UNESCAPED_UNICODE); ?>;
  </script>
  <script>
  document.addEventListener('DOMContentLoaded', function () {
    if (!window.__DASH_CHARTS) return;

    var data = window.__DASH_CHARTS;
    var brand = '#ea580c';
    var slate = '#0f172a';
    var muted = '#94a3b8';
    var grid = 'rgba(15, 23, 42, 0.08)';

    // World choropleth (jsVectorMap) + pins for tiny / missing places
    if (typeof jsVectorMap !== 'undefined' && document.getElementById('world-visitors-map')) {
      var countryValues = data.countries || {};
      var countryNames = data.countryNames || {};
      var maxVal = 0;
      Object.keys(countryValues).forEach(function (k) {
        if (countryValues[k] > maxVal) maxVal = countryValues[k];
      });

      function niceCeil(x) {
        if (x <= 1) return 1;
        var exp = Math.floor(Math.log10(x));
        var base = Math.pow(10, exp);
        var f = x / base;
        var nice = f <= 1 ? 1 : f <= 2 ? 2 : f <= 2.5 ? 2.5 : f <= 5 ? 5 : 10;
        return Math.round(nice * base);
      }

      function formatBound(n) {
        if (n >= 1000000) {
          var m = n / 1000000;
          return (Math.abs(m - Math.round(m)) < 1e-9 ? String(Math.round(m)) : m.toFixed(1).replace(/\.0$/, '')) + 'M';
        }
        if (n >= 1000) {
          var k = n / 1000;
          return (Math.abs(k - Math.round(k)) < 1e-9 ? String(Math.round(k)) : k.toFixed(1).replace(/\.0$/, '')) + 'k';
        }
        return String(n);
      }

      // Discrete bands that grow with traffic: 1–3, 10–49, 1k–4.9k, 10k+ …
      // Always span the full light→dark palette so the top country (e.g. USA)
      // uses the darkest shade and that same swatch appears in the legend.
      function buildVisitorBuckets(peak) {
        var palette = ['#fecaca', '#f87171', '#ea580c', '#9a3412', '#431407'];
        peak = Math.max(1, Math.floor(peak || 1));
        var highs = [];
        if (peak === 1) {
          highs = [1];
        } else if (peak <= 5) {
          for (var i = 1; i <= peak; i++) highs.push(i);
        } else {
          var steps = 5;
          for (var s = 1; s <= steps; s++) {
            highs.push(niceCeil(Math.pow(peak, s / steps)));
          }
          highs[highs.length - 1] = Math.max(highs[highs.length - 1], peak);
          var uniq = [];
          highs.forEach(function (h) {
            if (!uniq.length || uniq[uniq.length - 1] !== h) uniq.push(h);
          });
          highs = uniq;
        }

        var buckets = [];
        var prev = 0;
        var last = highs.length - 1;
        for (var b = 0; b < highs.length; b++) {
          var min = prev + 1;
          var max = highs[b];
          if (min > max) continue;
          var isTop = b === last;
          var label = min === max
            ? formatBound(min)
            : (isTop ? formatBound(min) + '+' : formatBound(min) + '–' + formatBound(max));
          var colorIndex = last <= 0
            ? palette.length - 1
            : Math.round(b * (palette.length - 1) / last);
          buckets.push({
            min: min,
            max: max,
            color: palette[colorIndex],
            label: label
          });
          prev = max;
        }
        return buckets;
      }

      function bucketForCount(n, buckets) {
        if (n <= 0 || !buckets.length) return null;
        for (var i = 0; i < buckets.length; i++) {
          if (n <= buckets[i].max) return buckets[i];
        }
        return buckets[buckets.length - 1];
      }

      var buckets = buildVisitorBuckets(maxVal);
      // Exact hex fills — avoids jsVectorMap scale interpolation inventing
      // colors (e.g. near-black USA) that never appear in the legend.
      var regionColors = {};
      Object.keys(countryValues).forEach(function (code) {
        var bucket = bucketForCount(countryValues[code], buckets);
        if (bucket) regionColors[code] = bucket.color;
      });

      var legendEl = document.getElementById('world-visitors-legend');
      if (legendEl) {
        var itemsHtml = buckets.map(function (bucket) {
          return '<span class="dash-map-legend__item">' +
            '<i class="dash-map-legend__swatch" style="background:' + bucket.color + '"></i>' +
            '<em>' + bucket.label + '</em>' +
            '</span>';
        }).join('');
        legendEl.innerHTML =
          '<div class="dash-map-legend__items">' + itemsHtml + '</div>' +
          '<span class="dash-map-legend__pin" title="Small places">' +
          '<i class="fa-solid fa-circle"></i> Dot = small place</span>';
      }

      // Coords for places missing from (or too small on) the world outline.
      // jsVectorMap world.js omits HK, SG, MO, MT, and many islands.
      var pinCoords = {
        HK: [22.3193, 114.1694],
        MO: [22.1987, 113.5439],
        SG: [1.3521, 103.8198],
        TW: [23.6978, 120.9605],
        MT: [35.9375, 14.3754],
        MC: [43.7384, 7.4246],
        SM: [43.9424, 12.4578],
        AD: [42.5063, 1.5218],
        LI: [47.1660, 9.5554],
        VA: [41.9029, 12.4534],
        GI: [36.1408, -5.3536],
        LU: [49.8153, 6.1296],
        BH: [26.0667, 50.5577],
        QA: [25.3548, 51.1839],
        KW: [29.3117, 47.4818],
        BN: [4.5353, 114.7277],
        MV: [3.2028, 73.2207],
        MU: [-20.3484, 57.5522],
        SC: [-4.6796, 55.4920],
        BB: [13.1939, -59.5432],
        JM: [18.1096, -77.2975],
        TT: [10.6918, -61.2225],
        BS: [25.0343, -77.3963],
        AG: [17.0608, -61.7964],
        LC: [13.9094, -60.9789],
        VC: [12.9843, -61.2872],
        GD: [12.1165, -61.6790],
        KN: [17.3578, -62.7830],
        DM: [15.4150, -61.3710],
        CY: [35.1264, 33.4299],
        IL: [31.0461, 34.8516],
        LB: [33.8547, 35.8623],
        AE: [23.4241, 53.8478],
        FO: [61.8926, -6.9118],
        IS: [64.9631, -19.0208],
        CW: [12.1696, -68.9900],
        AW: [12.5211, -69.9683],
        KY: [19.3133, -81.2546],
        BM: [32.3078, -64.7505],
        RE: [-21.1151, 55.5364],
        MQ: [14.6415, -61.0242],
        GP: [16.2650, -61.5510],
        PF: [-17.6797, -149.4068],
        NC: [-20.9043, 165.6180],
        FJ: [-17.7134, 178.0650],
        TO: [-21.1789, -175.1982],
        WS: [-13.7590, -172.1046],
        GU: [13.4443, 144.7937],
        MP: [15.0979, 145.6739],
        VI: [18.3358, -64.8963],
        PR: [18.2208, -66.5901],
        PS: [31.9522, 35.2332]
      };

      var markers = [];
      Object.keys(countryValues).forEach(function (code) {
        var coords = pinCoords[code];
        // Pin curated small/missing places whenever they have visits.
        if (!coords) return;

        var n = countryValues[code] || 0;
        var place = countryNames[code] || code;
        var label = n === 1 ? '1 unique visitor' : (n + ' unique visitors');
        var bucket = bucketForCount(n, buckets);
        var fill = bucket ? bucket.color : '#ea580c';
        var r = maxVal > 0 ? Math.max(5, Math.min(11, 5 + Math.round((n / maxVal) * 6))) : 6;
        markers.push({
          name: place + ' — ' + label,
          coords: coords,
          style: {
            initial: {
              fill: fill,
              stroke: '#ffffff',
              strokeWidth: 1.75,
              r: r
            },
            hover: {
              fill: '#0f172a',
              stroke: '#ffffff',
              strokeWidth: 2,
              r: r + 1
            }
          }
        });
      });

      var map = new jsVectorMap({
        selector: '#world-visitors-map',
        map: 'world',
        backgroundColor: 'transparent',
        zoomOnScroll: true,
        zoomButtons: true,
        regionsSelectable: false,
        regionStyle: {
          initial: {
            fill: '#e2e8f0',
            stroke: '#ffffff',
            strokeWidth: 0.35,
            fillOpacity: 1
          },
          hover: {
            fillOpacity: 0.92,
            cursor: 'pointer'
          }
        },
        markers: markers,
        markerStyle: {
          initial: {
            fill: '#ea580c',
            stroke: '#ffffff',
            strokeWidth: 1.75,
            r: 6
          },
          hover: {
            fill: '#0f172a',
            strokeWidth: 2
          }
        },
        onRegionTooltipShow: function (event, tooltip, code) {
          var n = countryValues[code] || 0;
          var label = n === 1 ? '1 unique visitor' : (n + ' unique visitors');
          tooltip.text(
            (tooltip.text() || code) + ' — ' + label,
            true
          );
        }
      });

      // Paint each country with the same hex as its legend swatch.
      Object.keys(regionColors).forEach(function (code) {
        var region = map.regions && map.regions[code];
        if (region && region.element && typeof region.element.setStyle === 'function') {
          region.element.setStyle('fill', regionColors[code]);
        }
      });

      // Keep SVG sharp when the panel is wide
      window.addEventListener('resize', function () {
        var mapEl = document.querySelector('#world-visitors-map .jvm-container');
        if (mapEl && typeof mapEl.dispatchEvent === 'function') {
          window.dispatchEvent(new Event('resize'));
        }
      });
    }

    if (typeof Chart === 'undefined') return;

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
            x: {
              grid: { display: false },
              ticks: {
                maxRotation: 0,
                autoSkip: true,
                autoSkipPadding: 10,
                callback: function (val, i, ticks) {
                  var label = this.getLabelForValue(val);
                  var last = ticks.length - 1;
                  if (i === 0 || i === last || i % 4 === 0) return label;
                  return '';
                }
              }
            },
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
                callback: function (val, i, ticks) {
                  var label = this.getLabelForValue(val);
                  var last = ticks.length - 1;
                  if (i === 0 || i === last || i % 3 === 0) return label;
                  return '';
                }
              }
            },
            y: { beginAtZero: true, ticks: { precision: 0 }, grid: { color: grid } }
          }
        }
      });
    }

    function escapeHtml(str) {
      return String(str)
        .replace(/&/g, '&amp;')
        .replace(/</g, '&lt;')
        .replace(/>/g, '&gt;')
        .replace(/"/g, '&quot;');
    }

    function fillHtmlLegend(elId, labels, values, colors, unit) {
      var el = document.getElementById(elId);
      if (!el || !labels || !labels.length) return;
      var total = (values || []).reduce(function (a, b) { return a + (b || 0); }, 0) || 1;
      el.innerHTML = labels.map(function (label, i) {
        var n = values[i] || 0;
        var pct = Math.round((n / total) * 100);
        var safe = escapeHtml(label);
        var meta = unit === 'views'
          ? n.toLocaleString() + ' views · ' + pct + '%'
          : n.toLocaleString() + ' · ' + pct + '%';
        return '<li>' +
          '<i class="dash-pie-legend__swatch" style="background:' + (colors[i] || '#94a3b8') + '"></i>' +
          '<div><strong title="' + safe + '">' + safe + '</strong>' +
          '<span>' + meta + '</span></div>' +
          '</li>';
      }).join('');
    }

    var mixColors = ['#ea580c', '#0f172a', '#c2410c', '#334155', '#fb923c'];
    var mixCtx = document.getElementById('chart-content-mix');
    if (mixCtx) {
      fillHtmlLegend('legend-content-mix', data.content.labels, data.content.values, mixColors, 'count');
      new Chart(mixCtx, {
        type: 'doughnut',
        data: {
          labels: data.content.labels,
          datasets: [{
            data: data.content.values,
            backgroundColor: mixColors,
            borderWidth: 0
          }]
        },
        options: {
          responsive: true,
          maintainAspectRatio: false,
          plugins: {
            legend: { display: false }
          },
          cutout: '62%'
        }
      });
    }

    var topColors = ['#ea580c', '#0f172a', '#c2410c', '#334155', '#fb923c', '#64748b'];
    var topPostsCtx = document.getElementById('chart-top-posts');
    if (topPostsCtx && data.topPosts && data.topPosts.labels && data.topPosts.labels.length) {
      fillHtmlLegend('legend-top-posts', data.topPosts.labels, data.topPosts.values, topColors, 'views');
      new Chart(topPostsCtx, {
        type: 'pie',
        data: {
          labels: data.topPosts.labels,
          datasets: [{
            data: data.topPosts.values,
            backgroundColor: topColors,
            borderWidth: 0
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
                  var n = ctx.parsed || 0;
                  var total = ctx.dataset.data.reduce(function (a, b) { return a + b; }, 0) || 1;
                  var pct = Math.round((n / total) * 100);
                  return ' ' + n.toLocaleString() + ' views (' + pct + '%)';
                }
              }
            }
          }
        }
      });
    }

    var projectInterestCtx = document.getElementById('chart-project-interest');
    if (projectInterestCtx && data.projectInterest) {
      new Chart(projectInterestCtx, {
        type: 'line',
        data: {
          labels: data.projectInterest.labels,
          datasets: [
            {
              label: 'Card views',
              data: data.projectInterest.views,
              borderColor: brand,
              backgroundColor: 'rgba(234, 88, 12, 0.12)',
              fill: true,
              tension: 0.35,
              pointRadius: 2,
              pointHoverRadius: 5,
              borderWidth: 2.5
            },
            {
              label: 'Live launches',
              data: data.projectInterest.clicks,
              borderColor: slate,
              backgroundColor: 'rgba(15, 23, 42, 0.06)',
              fill: true,
              tension: 0.35,
              pointRadius: 2,
              pointHoverRadius: 5,
              borderWidth: 2.5
            }
          ]
        },
        options: {
          responsive: true,
          maintainAspectRatio: false,
          interaction: { mode: 'index', intersect: false },
          plugins: {
            legend: { position: 'bottom', labels: { boxWidth: 12, padding: 14 } }
          },
          scales: {
            x: { grid: { display: false }, ticks: { maxRotation: 0, autoSkipPadding: 12 } },
            y: { beginAtZero: true, ticks: { precision: 0 }, grid: { color: grid } }
          }
        }
      });
    }

    var topProjectsCtx = document.getElementById('chart-top-projects');
    if (topProjectsCtx && data.topProjects && data.topProjects.labels && data.topProjects.labels.length) {
      var barCount = data.topProjects.labels.length;
      topProjectsCtx.parentElement.style.height = Math.max(260, 48 + barCount * 42) + 'px';
      new Chart(topProjectsCtx, {
        type: 'bar',
        data: {
          labels: data.topProjects.labels,
          datasets: [
            {
              label: 'Card views',
              data: data.topProjects.views,
              backgroundColor: brand,
              borderWidth: 0,
              borderRadius: 0
            },
            {
              label: 'Live launches',
              data: data.topProjects.clicks,
              backgroundColor: slate,
              borderWidth: 0,
              borderRadius: 0
            }
          ]
        },
        options: {
          indexAxis: 'y',
          responsive: true,
          maintainAspectRatio: false,
          plugins: {
            legend: { position: 'bottom', labels: { boxWidth: 12, padding: 14 } }
          },
          scales: {
            x: { beginAtZero: true, ticks: { precision: 0 }, grid: { color: grid } },
            y: { grid: { display: false }, ticks: { font: { size: 11 } } }
          }
        }
      });
    }
  });
  </script>
</body>
</html>
