<?php
/**
 * Local-only demo data for admin screenshots (dashboard, map, charts, inbox).
 *
 * Usage:
 *   C:\xampp\php\php.exe database/seed_demo_dashboard.php
 *
 * Safe to re-run: clears previous demo visitors/comments/messages marked as demo,
 * then inserts a fresh set. Does not delete real blog posts.
 */
declare(strict_types=1);

require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../includes/research_projects_seed.php';

$demoTag = '__demo_seed';
$now = new DateTimeImmutable('now');

echo "Seeding local demo dashboard data...\n";

// ── Research projects (portfolio inventory) ─────────────────────────────────
ensureDefaultResearchProjects($pdo);
echo "  research_projects: ok\n";

// ── Publications ────────────────────────────────────────────────────────────
$pubs = [
    [
        'Toward Interpretable Multi-Agent Coordination',
        'Workshop on Emergent Communication (EMC)',
        '2025',
        'https://tanuiksila.co.ke',
        'Short paper on reciprocal coordination signals in multi-agent systems.',
        3,
    ],
    [
        'Visualizing Global AI Adoption at City Scale',
        'Data Visualization Practice Notes',
        '2025',
        'https://tanuiksila.co.ke/global_ai_impact_visualization/',
        'Design notes for choropleth dashboards with 5,000+ geographic points.',
        2,
    ],
    [
        'Teaching Computational Thinking with Interactive Solvers',
        'CS Education Briefs',
        '2024',
        'https://tanuiksila.co.ke/PCP/',
        'Classroom use of undecidability demos for undergraduate theory courses.',
        1,
    ],
];
$pubStmt = $pdo->prepare('INSERT INTO publications (title, venue, publication_year, url, summary, sort_order)
    SELECT ?, ?, ?, ?, ?, ?
    FROM DUAL
    WHERE NOT EXISTS (SELECT 1 FROM publications WHERE title = ?)');
foreach ($pubs as $p) {
    $pubStmt->execute([$p[0], $p[1], $p[2], $p[3], $p[4], $p[5], $p[0]]);
}
echo '  publications: ' . (int) $pdo->query('SELECT COUNT(*) FROM publications')->fetchColumn() . "\n";

// ── Projects ────────────────────────────────────────────────────────────────
$projects = [
    ['AI-Driven E-Commerce Automation', 'Master research project automating e-commerce workflows with AI on Blazor Server.', 'https://tanuiksila.co.ke', 10],
    ['Global AI Impact Visualization', 'Interactive choropleth of AI adoption metrics across thousands of data points.', 'https://tanuiksila.co.ke/global_ai_impact_visualization/', 9],
    ['Interactive Curve Design System', 'Parametric curve studio for Bézier, Hermite and B-Spline algorithms.', 'https://tanuiksila.co.ke/CAGD_P1/', 8],
    ['Post Correspondence Problem Solver', 'Interactive undecidability demo for the Post Correspondence Problem.', 'https://tanuiksila.co.ke/PCP/', 7],
    ['Graph Coloring Machine', 'Browser tool for vertex coloring and chromatic-number intuition.', 'https://silatanui.github.io/Graph-Coloring-Machine/', 6],
];
$projStmt = $pdo->prepare('INSERT INTO projects (title, description, url, sort_order)
    SELECT ?, ?, ?, ?
    FROM DUAL
    WHERE NOT EXISTS (SELECT 1 FROM projects WHERE title = ?)');
foreach ($projects as $p) {
    $projStmt->execute([$p[0], $p[1], $p[2], $p[3], $p[0]]);
}
echo '  projects: ' . (int) $pdo->query('SELECT COUNT(*) FROM projects')->fetchColumn() . "\n";

// ── Education (fill if thin) ────────────────────────────────────────────────
$edu = [
    ['University of Debrecen', 'MSc', 'Computer Science', '2024', '2026', 'Stipendium Hungaricum scholar. Focus on AI systems, visualization, and software engineering.', 2],
    ['Kirinyaga University', 'BSc (First Class Honours)', 'Computer Science', '2018', '2022', 'Undergraduate foundation in algorithms, systems, and software development.', 1],
];
$eduStmt = $pdo->prepare('INSERT INTO education (institution, degree, field_name, start_year, end_year, description, sort_order)
    SELECT ?, ?, ?, ?, ?, ?, ?
    FROM DUAL
    WHERE NOT EXISTS (SELECT 1 FROM education WHERE institution = ?)');
foreach ($edu as $e) {
    $eduStmt->execute([$e[0], $e[1], $e[2], $e[3], $e[4], $e[5], $e[6], $e[0]]);
}
echo '  education: ' . (int) $pdo->query('SELECT COUNT(*) FROM education')->fetchColumn() . "\n";

// ── Blog view counts (for pie + top posts table) ────────────────────────────
$viewBoosts = [
    'M-PESA, Cards, and the Future of Payments in Kenya' => 1280,
    'Reciprocal Coordination and Spontaneous Phase Synchronization in Multi-Agent AI Systems.' => 940,
    'Rethinking How We Visualize and Build Machine Learning Models' => 810,
    'AI Is Changing Visual Effects. But What Happens to the People Behind Them?' => 620,
    'The Need for Hatred' => 480,
    'Stop Renting AI This is How to Build Your Own AI Tool Suite for Pennies' => 355,
    '10 Visual Features That Make Your Website Look AI-Generated (And How to Fix Them)' => 290,
    '100 AI RED FLAGS: THINGS TO AVOID TO ELIMINATE THE AI-LOOK IN DOCUMENTS' => 175,
];
$boost = $pdo->prepare('UPDATE blog_posts SET views = GREATEST(views, ?) WHERE title = ? AND status = \'published\'');
foreach ($viewBoosts as $title => $views) {
    $boost->execute([$views, $title]);
}
// Any other published posts get a modest floor so charts are not empty
$pdo->exec("UPDATE blog_posts SET views = GREATEST(views, 40 + (id * 7)) WHERE status = 'published' AND views < 40");
echo "  blog_posts views: boosted\n";

// Ensure at least one draft exists for status mix
$draftCount = (int) $pdo->query("SELECT COUNT(*) FROM blog_posts WHERE status = 'draft'")->fetchColumn();
if ($draftCount === 0) {
    $pdo->prepare("INSERT INTO blog_posts (title, slug, excerpt, content, category, tags, status, reading_time, published_at, views, allow_comments)
        VALUES (?, ?, ?, ?, ?, ?, 'draft', 6, NULL, 0, 1)")->execute([
        'Draft: How I Track Portfolio Analytics',
        'draft-how-i-track-portfolio-analytics',
        'Notes for a future post on unique visitors, country maps, and admin charts.',
        "<p>Working notes on the admin analytics stack: unique daily visitors, IP+browser hashing, and choropleth maps.</p>",
        'Engineering',
        'analytics,admin,portfolio',
    ]);
    echo "  blog_posts: added draft\n";
}

$posts = $pdo->query("SELECT id, title FROM blog_posts WHERE status = 'published' ORDER BY views DESC")->fetchAll(PDO::FETCH_ASSOC);
$postIds = array_map(static fn(array $r): int => (int) $r['id'], $posts);

// ── Clear previous demo rows ────────────────────────────────────────────────
$pdo->exec("DELETE FROM site_daily_visitors WHERE landing_path = '/{$demoTag}'");
$pdo->exec("DELETE FROM blog_post_views WHERE user_agent LIKE '%{$demoTag}%'");
$pdo->exec("DELETE FROM blog_comments WHERE author_email LIKE '%@demo.local'");
$pdo->exec("DELETE FROM messages WHERE email LIKE '%@demo.local'");

// ── Site visitors (map + unique-visitor charts) ─────────────────────────────
// Weighted countries: USA dominates (dark shade), then Kenya/Hungary hubs,
// plus small places (HK, SG) for map pins.
$countries = [
    ['US', 'United States', 'New York', 38],
    ['US', 'United States', 'San Francisco', 22],
    ['US', 'United States', 'Austin', 12],
    ['KE', 'Kenya', 'Nairobi', 28],
    ['KE', 'Kenya', 'Mombasa', 8],
    ['HU', 'Hungary', 'Debrecen', 18],
    ['HU', 'Hungary', 'Budapest', 10],
    ['GB', 'United Kingdom', 'London', 14],
    ['DE', 'Germany', 'Berlin', 11],
    ['CA', 'Canada', 'Toronto', 9],
    ['IN', 'India', 'Bengaluru', 10],
    ['NG', 'Nigeria', 'Lagos', 8],
    ['ZA', 'South Africa', 'Cape Town', 6],
    ['BR', 'Brazil', 'São Paulo', 7],
    ['AU', 'Australia', 'Sydney', 5],
    ['FR', 'France', 'Paris', 6],
    ['JP', 'Japan', 'Tokyo', 5],
    ['NL', 'Netherlands', 'Amsterdam', 4],
    ['AE', 'United Arab Emirates', 'Dubai', 4],
    ['SG', 'Singapore', 'Singapore', 5],
    ['HK', 'Hong Kong', 'Hong Kong', 4],
    ['TW', 'Taiwan', 'Taipei', 3],
    ['GH', 'Ghana', 'Accra', 3],
    ['EG', 'Egypt', 'Cairo', 3],
    ['SE', 'Sweden', 'Stockholm', 2],
    ['PL', 'Poland', 'Warsaw', 2],
    ['MX', 'Mexico', 'Mexico City', 2],
    ['KR', 'South Korea', 'Seoul', 2],
];

$browsers = [
    'Mozilla/5.0 (Windows NT 10.0; Win64; x64) Chrome/131.0.0.0 ' . $demoTag,
    'Mozilla/5.0 (Macintosh; Intel Mac OS X 14_0) Safari/605.1.15 ' . $demoTag,
    'Mozilla/5.0 (X11; Linux x86_64) Firefox/133.0 ' . $demoTag,
    'Mozilla/5.0 (iPhone; CPU iPhone OS 18_0 like Mac OS X) Mobile ' . $demoTag,
    'Mozilla/5.0 (Linux; Android 14) Chrome/131.0.0.0 Mobile ' . $demoTag,
];

$paths = ['/', '/public/blog.php', '/public/projects.php', '/public/about.php', '/public/post.php'];

$visitInsert = $pdo->prepare("INSERT IGNORE INTO site_daily_visitors
    (visit_date, visitor_hash, ip_address, user_agent, landing_path, country_code, country_name, city, first_seen)
    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");

$visitorRows = 0;
$weightSum = array_sum(array_column($countries, 3));

for ($dayOffset = 29; $dayOffset >= 0; $dayOffset--) {
    $day = $now->modify("-{$dayOffset} days");
    $date = $day->format('Y-m-d');
    // Weekdays busier; weekends lighter; gentle growth toward today
    $weekday = (int) $day->format('N');
    $base = $weekday >= 6 ? 18 : 32;
    $growth = (int) round((29 - $dayOffset) * 0.55);
    $dayCount = $base + $growth + random_int(0, 8);

    for ($i = 0; $i < $dayCount; $i++) {
        $pick = random_int(1, $weightSum);
        $running = 0;
        $country = $countries[0];
        foreach ($countries as $c) {
            $running += $c[3];
            if ($pick <= $running) {
                $country = $c;
                break;
            }
        }

        $ua = $browsers[array_rand($browsers)];
        $hash = hash('sha256', $demoTag . '|' . $date . '|' . $i . '|' . $country[0] . '|' . $ua);
        $ip = sprintf('%d.%d.%d.%d', random_int(1, 223), random_int(0, 255), random_int(0, 255), random_int(1, 254));
        $seen = $day->setTime(random_int(7, 22), random_int(0, 59), random_int(0, 59))->format('Y-m-d H:i:s');

        $visitInsert->execute([
            $date,
            $hash,
            $ip,
            $ua,
            '/' . $demoTag,
            $country[0],
            $country[1],
            $country[2],
            $seen,
        ]);
        $visitorRows += $visitInsert->rowCount() > 0 ? 1 : 0;
    }
}
echo "  site_daily_visitors: +{$visitorRows} demo rows\n";

// ── Blog readers / day (post view log) ──────────────────────────────────────
$viewInsert = $pdo->prepare("INSERT INTO blog_post_views
    (post_id, visitor_hash, ip_address, user_agent, viewed_at)
    VALUES (?, ?, ?, ?, ?)");
$viewRows = 0;
if ($postIds) {
    for ($dayOffset = 29; $dayOffset >= 0; $dayOffset--) {
        $day = $now->modify("-{$dayOffset} days");
        $readers = random_int(8, 28);
        for ($i = 0; $i < $readers; $i++) {
            $postId = $postIds[array_rand($postIds)];
            // Bias toward top posts
            if (random_int(1, 100) <= 55) {
                $postId = $postIds[random_int(0, min(3, count($postIds) - 1))];
            }
            $ua = $browsers[array_rand($browsers)];
            $hash = hash('sha256', 'postview|' . $demoTag . '|' . $day->format('Y-m-d') . '|' . $i . '|' . $postId);
            $viewed = $day->setTime(random_int(8, 23), random_int(0, 59), random_int(0, 59))->format('Y-m-d H:i:s');
            $viewInsert->execute([
                $postId,
                $hash,
                sprintf('10.%d.%d.%d', random_int(0, 255), random_int(0, 255), random_int(1, 254)),
                $ua,
                $viewed,
            ]);
            $viewRows++;
        }
    }
}
echo "  blog_post_views: +{$viewRows} demo rows\n";

// ── Comments ────────────────────────────────────────────────────────────────
if ($postIds) {
    $comments = [
        [$postIds[0], 'Amina Wanjiku', 'amina@demo.local', 'Really clear breakdown — the M-PESA angle is practical for builders in East Africa.', 'approved'],
        [$postIds[0], 'James O.', 'james@demo.local', 'Would love a follow-up on settlement rails and open banking.', 'approved'],
        [$postIds[min(1, count($postIds) - 1)], 'Elena Kovacs', 'elena@demo.local', 'The multi-agent framing helped my thesis lit review. Thank you!', 'approved'],
        [$postIds[min(2, count($postIds) - 1)], 'Chris M.', 'chris@demo.local', 'Can you share the chart library you used for the choropleth?', 'pending'],
        [$postIds[min(3, count($postIds) - 1)], 'Priya Nair', 'priya@demo.local', 'Bookmarked. The “AI look” checklist is going straight into our design QA.', 'pending'],
        [$postIds[min(4, count($postIds) - 1)], 'David K.', 'david@demo.local', 'Spam test please ignore', 'spam'],
    ];
    $cStmt = $pdo->prepare('INSERT INTO blog_comments (post_id, author_name, author_email, comment_text, status, created_at) VALUES (?, ?, ?, ?, ?, ?)');
    foreach ($comments as $i => $c) {
        $created = $now->modify('-' . (2 + $i) . ' days')->setTime(11 + $i, 15)->format('Y-m-d H:i:s');
        $cStmt->execute([$c[0], $c[1], $c[2], $c[3], $c[4], $created]);
    }
    echo '  blog_comments: +' . count($comments) . " demo rows\n";
}

// ── Messages ────────────────────────────────────────────────────────────────
$messages = [
    ['Sofia Berg', 'sofia@demo.local', 'Speaking invitation', 'Hi Tanui — we host a small AI meetup in Stockholm. Would you consider a remote talk on multi-agent coordination?', 0],
    ['Marcus Lee', 'marcus@demo.local', 'Collaboration on visualization tooling', 'Your Global AI map is excellent. Open to collaborating on an open dataset layer?', 0],
    ['Naliaka Cherop', 'naliaka@demo.local', 'Tutoring / mentoring', 'I am preparing for an MSc application and would value 30 minutes of advice on research statements.', 0],
    ['Studio North', 'hello@demo.local', 'Project inquiry', 'We need a short technical article series on avoiding generic AI UI patterns. Are you available in November?', 1],
    ['Alumni Office', 'alumni@demo.local', 'Profile update', 'Please confirm your current headline for the university alumni newsletter.', 1],
];
$mStmt = $pdo->prepare('INSERT INTO messages (name, email, subject, message, is_read, created_at) VALUES (?, ?, ?, ?, ?, ?)');
foreach ($messages as $i => $m) {
    $created = $now->modify('-' . $i . ' days')->setTime(9 + $i, 40)->format('Y-m-d H:i:s');
    $mStmt->execute([$m[0], $m[1], $m[2], $m[3], $m[4], $created]);
}
echo '  messages: +' . count($messages) . " demo rows\n";

// ── Project views / launches ────────────────────────────────────────────────
try {
    $pdo->exec("DELETE FROM project_events WHERE user_agent LIKE '%{$demoTag}%'");
    $projectRows = $pdo->query('SELECT id, title FROM projects ORDER BY id ASC')->fetchAll(PDO::FETCH_ASSOC);
    if ($projectRows) {
        $pdo->exec('UPDATE projects SET views = 0, clicks = 0');
        $pInsert = $pdo->prepare("
            INSERT INTO project_events (project_id, event_type, visitor_hash, ip_address, user_agent, created_at)
            VALUES (?, ?, ?, ?, ?, ?)
        ");
        $pUa = 'Mozilla/5.0 Demo ' . $demoTag;
        $pRows = 0;
        foreach ($projectRows as $i => $project) {
            $pid = (int) $project['id'];
            $viewTarget = 40 + ($i * 17) + random_int(5, 25);
            $clickTarget = max(3, (int) round($viewTarget * (0.12 + ($i * 0.03))));
            for ($v = 0; $v < $viewTarget; $v++) {
                $day = $now->modify('-' . random_int(0, 29) . ' days')->setTime(random_int(8, 22), random_int(0, 59));
                $pInsert->execute([
                    $pid,
                    'view',
                    hash('sha256', "pv|$demoTag|$pid|$v|" . $day->format('Y-m-d')),
                    '10.0.' . random_int(0, 255) . '.' . random_int(1, 254),
                    $pUa,
                    $day->format('Y-m-d H:i:s'),
                ]);
                $pRows++;
            }
            for ($c = 0; $c < $clickTarget; $c++) {
                $day = $now->modify('-' . random_int(0, 29) . ' days')->setTime(random_int(8, 22), random_int(0, 59));
                $pInsert->execute([
                    $pid,
                    'click',
                    hash('sha256', "pc|$demoTag|$pid|$c|" . $day->format('Y-m-d')),
                    '10.1.' . random_int(0, 255) . '.' . random_int(1, 254),
                    $pUa,
                    $day->format('Y-m-d H:i:s'),
                ]);
                $pRows++;
            }
            $pdo->prepare('UPDATE projects SET views = ?, clicks = ? WHERE id = ?')
                ->execute([$viewTarget, $clickTarget, $pid]);
        }
        echo "  project_events: +{$pRows} demo rows\n";
    }
} catch (Throwable $e) {
    echo '  project_events: skipped (' . $e->getMessage() . ")\n";
}

// ── Summary for screenshots ─────────────────────────────────────────────────
$located = (int) $pdo->query("SELECT COUNT(*) FROM site_daily_visitors WHERE visit_date >= (CURDATE() - INTERVAL 29 DAY) AND country_code IS NOT NULL")->fetchColumn();
$countriesN = (int) $pdo->query("SELECT COUNT(DISTINCT country_code) FROM site_daily_visitors WHERE visit_date >= (CURDATE() - INTERVAL 29 DAY) AND country_code IS NOT NULL")->fetchColumn();
$pending = (int) $pdo->query("SELECT COUNT(*) FROM blog_comments WHERE status = 'pending'")->fetchColumn();
$unread = (int) $pdo->query("SELECT COUNT(*) FROM messages WHERE is_read = 0")->fetchColumn();

echo "\nDone. Open the admin dashboard and hard-refresh (Ctrl+F5).\n";
echo "  Located visitors (30d): {$located}\n";
echo "  Distinct countries:     {$countriesN}\n";
echo "  Pending comments:      {$pending}\n";
echo "  Unread messages:       {$unread}\n";
echo "\nTip: this seed is local-demo only; re-run anytime to refresh the fake traffic.\n";
