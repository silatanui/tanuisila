<?php
$activePage = 'home';
require_once __DIR__ . '/header.php';

// Safe self-healing localized copy mechanism to handle server asset permissions
$sourceImg = __DIR__ . '/../IMG_20260625_115331.jpg';
$destImg   = __DIR__ . '/profile.jpg';
if (file_exists($sourceImg)) {
    if (!file_exists($destImg) || filesize($sourceImg) !== filesize($destImg)) {
        copy($sourceImg, $destImg);
        @copy($sourceImg, __DIR__ . '/../profile.jpg');
    }
}

$totalProjects     = 0;
$totalPublications = 0;
try {
    $totalProjects     = (int) $pdo->query('SELECT COUNT(*) FROM projects')->fetchColumn();
    $totalPublications = (int) $pdo->query('SELECT COUNT(*) FROM publications')->fetchColumn();
} catch (Throwable $e) {}

$featuredPool = !empty($navProjects) ? $navProjects : ($projects ?? []);
$featuredProject = !empty($featuredPool) && is_array($featuredPool) ? $featuredPool[0] : null;
$workProjects = array_slice(is_array($featuredPool) ? $featuredPool : [], 0, 4);

$eduPreview = array_slice(is_array($education ?? null) ? $education : [], 0, 2);
$expPreview = array_slice(is_array($experience ?? null) ? $experience : [], 0, 2);
$skillPreview = array_slice(is_array($skills ?? null) ? $skills : [], 0, 8);

// Homepage Gantt tracker - LinkedIn journey (+ Transcosmos on site).
// Dates use first of start month / last day of end month.
$expTrack = [
  [
    'label' => 'Kerugoya CRH',
    'role' => 'IT Specialist',
    'company' => 'Kerugoya County Referral Hospital',
    'start' => '2022-07-01',
    'end' => '2022-08-31',
    'tone' => 'slate',
    'lane' => 0,
  ],
  [
    'label' => 'Litmed',
    'role' => 'IT Technician & Lecturer',
    'company' => 'Litmed College of Technology',
    'start' => '2022-09-09',
    'end' => '2023-03-31',
    'tone' => 'blue',
    'lane' => 0,
  ],
  [
    'label' => 'Infolink',
    'role' => 'ICT & Programming Tutor',
    'company' => 'Infolink College of Technology',
    'start' => '2023-04-10',
    'end' => '2024-09-01',
    'tone' => 'orange',
    'lane' => 0,
  ],
  [
    'label' => 'GAOTek',
    'role' => 'Graphic Designer',
    'company' => 'GAOTek Inc.',
    'start' => '2023-10-01',
    'end' => '2024-02-29',
    'tone' => 'rose',
    'lane' => 1,
  ],
  [
    'label' => 'Transcosmos',
    'role' => 'Customer Success Specialist',
    'company' => 'Transcosmos (Fossil Group)',
    'start' => '2025-10-01',
    'end' => '2026-02-01',
    'tone' => 'teal',
    'lane' => 0,
  ],
];

// Full calendar years so year columns and bar math share the same scale.
$expTrackYears = range(2022, 2026);
$expTrackStart = strtotime('2022-01-01');
$expTrackEnd = strtotime('2027-01-01');
$expTrackSpan = max(1, $expTrackEnd - $expTrackStart);
$expTrackNow = time();
$expTrackToday = min(100, max(0, ($expTrackNow - $expTrackStart) / $expTrackSpan * 100));

foreach ($expTrack as $i => $row) {
  $s = strtotime($row['start']);
  $e = strtotime($row['end']);
  if ($s === false || $e === false || $e < $s) {
    $expTrack[$i]['left'] = 0;
    $expTrack[$i]['width'] = 0;
    $expTrack[$i]['when'] = '';
    continue;
  }
  $left = (($s - $expTrackStart) / $expTrackSpan) * 100;
  $width = (($e - $s) / $expTrackSpan) * 100;
  $expTrack[$i]['left'] = round(max(0, min(100, $left)), 2);
  $expTrack[$i]['width'] = round(max(1.2, min(100 - $expTrack[$i]['left'], $width)), 2);
  $expTrack[$i]['when'] = date('M Y', $s) . ' - ' . date('M Y', $e);
}

// Chronological education path for the homepage visual (primary → graduate).
$eduPath = [
  [
    'stage' => '01',
    'label' => 'Primary',
    'title' => 'St Vincent De Paul',
    'place' => 'Karnanin',
    'when' => '2004 - 2013',
    'result' => 'KCPE · 387 marks',
    'icon' => 'fa-school',
  ],
  [
    'stage' => '02',
    'label' => 'Secondary',
    'title' => 'Chewoyet High School',
    'place' => 'West Pokot',
    'when' => '2014 - 2017',
    'result' => 'KCSE · B plain · 63 points',
    'icon' => 'fa-book-open',
  ],
];

if (!empty($education) && is_array($education)) {
  $eduChrono = array_reverse($education);
  $stage = 3;
  foreach ($eduChrono as $item) {
    $degree = trim((string) ($item['degree'] ?? 'Degree'));
    $institution = trim((string) ($item['institution'] ?? ''));
    $field = trim((string) ($item['field_name'] ?? ''));
    $when = hp_edu_years($item);
    $result = $field !== '' ? $field : 'Computer Science';
    $icon = (stripos($degree, 'MSc') !== false || stripos($degree, 'Master') !== false)
      ? 'fa-graduation-cap'
      : 'fa-user-graduate';
    $eduPath[] = [
      'stage' => str_pad((string) $stage, 2, '0', STR_PAD_LEFT),
      'label' => (stripos($degree, 'MSc') !== false || stripos($degree, 'Master') !== false) ? 'Graduate' : 'Undergraduate',
      'title' => $degree,
      'place' => $institution,
      'when' => $when,
      'result' => $result,
      'icon' => $icon,
    ];
    $stage++;
  }
}

$latestPosts = [];
try {
    $latestPosts = $pdo->query("
        SELECT title, slug, excerpt, featured_image, category, published_at
        FROM blog_posts
        WHERE status = 'published'
        ORDER BY published_at DESC, id DESC
        LIMIT 3
    ")->fetchAll(PDO::FETCH_ASSOC);
} catch (Throwable $e) {
    $latestPosts = [];
}

function hp_post_image(array $post): string {
    if (empty($post['featured_image'])) {
        return '';
    }
    return portfolioAssetUrl((string) $post['featured_image']);
}

function hp_post_href(array $post): string {
    return appUrl('post.php?slug=' . urlencode((string) ($post['slug'] ?? '')));
}

$heroSummary = trim((string) (
    $settings['hero_text']
    ?: $profile['summary']
    ?: 'Curious about AI, research, and the many ways technology can be put to good use.'
));

function hp_project_image(array $p): string {
    if (empty($p['image'])) {
        return '';
    }
    $rawImg = trim((string) $p['image']);
    if (preg_match('#^https?://#i', $rawImg)) {
        return $rawImg;
    }
    if (strpos($rawImg, '/') === 0) {
        return portfolioAssetUrl($rawImg);
    }
    if (file_exists(__DIR__ . '/../assets/images/projects/' . $rawImg)) {
        return appUrl('assets/images/projects/' . $rawImg);
    }
    return appUrl('public/' . $rawImg);
}

function hp_short(string $text, int $max = 140): string {
    $text = preg_replace('/\s+/', ' ', trim($text)) ?? '';
    if (strlen($text) <= $max) {
        return $text;
    }
    return rtrim(substr($text, 0, $max - 3)) . '...';
}

function hp_edu_years(array $item): string {
    $pickYear = static function ($value): string {
        $raw = trim((string) $value);
        if ($raw === '') {
            return '';
        }
        if (preg_match('/\b(19|20)\d{2}\b/', $raw, $m)) {
            return $m[0];
        }
        return $raw;
    };
    $start = $pickYear($item['start_year'] ?? '');
    $end = $pickYear($item['end_year'] ?? '');
    return trim($start . ' - ' . $end, ' -');
}

function hp_exp_when(array $item): string {
    $pickYear = static function ($value): string {
        $raw = trim((string) $value);
        if ($raw === '') {
            return '';
        }
        if (preg_match('/\b(19|20)\d{2}\b/', $raw, $m)) {
            return $m[0];
        }
        return $raw;
    };
    $start = $pickYear($item['start_date'] ?? '');
    $endRaw = trim((string) ($item['end_date'] ?? ''));
    $current = $endRaw === '' || strcasecmp($endRaw, 'present') === 0;
    $end = $current ? 'Present' : $pickYear($endRaw);
    return trim($start . ' - ' . $end, ' -');
}
?>

<!-- ═══════════════════════════════════════════════════════════ -->
<!-- HERO                                                       -->
<!-- ═══════════════════════════════════════════════════════════ -->
<section class="hp-hero">
  <div class="hp-hero__atmosphere" aria-hidden="true">
    <span class="hp-hero__orb hp-hero__orb--a"></span>
    <span class="hp-hero__orb hp-hero__orb--b"></span>
    <span class="hp-hero__grain"></span>
  </div>

  <div class="hp-hero__plane">
    <div class="container hp-hero__inner">

      <div class="hp-hero__copy">
        <p class="hp-hero__role reveal-on-enter">
          <span class="hp-hero__pulse" aria-hidden="true"></span>
          Computer Scientist
        </p>

        <h1 class="hp-hero__brand">
          <span class="hp-brand-line"><?php echo htmlspecialchars($profile['full_name']); ?></span>
        </h1>

        <p class="hp-hero__tagline">Building technology with purpose.</p>

        <p class="hp-hero__sub"><?php echo htmlspecialchars($heroSummary); ?></p>

        <div class="hp-hero__ctas">
          <a class="hp-cta hp-cta--primary" href="<?php echo appUrl('public/projects.php'); ?>">
            View Projects <span class="hp-cta-arrow">→</span>
          </a>
          <a class="hp-cta hp-cta--secondary" href="<?php echo appUrl('public/contact.php'); ?>">
            Get in Touch
          </a>
        </div>
      </div>

      <div class="hp-hero__visual">
        <div class="hp-photo-frame" id="hero-photo-wrap">
          <div class="hp-photo-accent" aria-hidden="true"></div>
          <div class="hp-photo-inner img-skeleton-wrap" id="hero-photo-skeleton">
            <img
              src="<?php echo appUrl('public/profile.jpg'); ?>?v=<?php echo @filemtime(__DIR__ . '/profile.jpg'); ?>"
              alt="<?php echo htmlspecialchars($profile['full_name']); ?>"
              class="hp-photo"
              fetchpriority="high"
              decoding="async"
              width="520"
              height="640"
              onload="this.closest('.img-skeleton-wrap').classList.add('loaded')"
            >
          </div>
          <p class="hp-photo-caption">
            <span>Portfolio</span>
            <span><?php echo (int) date('Y'); ?></span>
          </p>
        </div>
      </div>

    </div>
  </div>

  <div class="hp-scroll-indicator" aria-hidden="true">
    <span>Scroll</span>
    <span class="hp-scroll-line"></span>
  </div>
</section>

<main id="main-content">

  <!-- Site map / reflect other pages -->
  <section class="hp-map" aria-label="Explore the portfolio">
    <div class="container">
      <div class="hp-section-label">
        <span class="hp-section-label__text">Explore</span>
      </div>
      <div class="hp-map__head">
        <h2 class="hp-section-title">The rest of the story, one click away.</h2>
        <p class="hp-section-lede">Jump into education, experience, skills, writing, and contact - the same pages you will find in the navigation, introduced here with a bit of context.</p>
      </div>

      <div class="hp-map__grid">
        <a class="hp-map__item" href="<?php echo appUrl('public/education.php'); ?>">
          <span class="hp-map__kicker">Academic path</span>
          <strong>Education</strong>
          <span><?php echo max(count($education ?? []), 1); ?> credential<?php echo count($education ?? []) === 1 ? '' : 's'; ?> from foundations to graduate research.</span>
          <span class="hp-map__go">Open education →</span>
        </a>
        <a class="hp-map__item" href="<?php echo appUrl('public/experience.php'); ?>">
          <span class="hp-map__kicker">Professional path</span>
          <strong>Experience</strong>
          <span><?php echo max(count($experience ?? []), 1); ?> role<?php echo count($experience ?? []) === 1 ? '' : 's'; ?> across teaching, support, and customer success.</span>
          <span class="hp-map__go">Open experience →</span>
        </a>
        <a class="hp-map__item" href="<?php echo appUrl('public/projects.php'); ?>">
          <span class="hp-map__kicker">Selected work</span>
          <strong>Projects</strong>
          <span><?php echo max(count($navProjects ?: ($projects ?? [])), 1); ?> live builds with screenshots, stack notes, and launch links.</span>
          <span class="hp-map__go">Browse projects →</span>
        </a>
        <a class="hp-map__item" href="<?php echo appUrl('public/skills.php'); ?>">
          <span class="hp-map__kicker">Toolkit</span>
          <strong>Skills</strong>
          <span><?php echo max(count($skills ?? []), 1); ?> skills spanning languages, data, cloud, and delivery tools.</span>
          <span class="hp-map__go">View skills →</span>
        </a>
        <a class="hp-map__item" href="<?php echo appUrl('blog.php'); ?>">
          <span class="hp-map__kicker">Writing</span>
          <strong>Blog</strong>
          <span>Notes on reverse engineering, tooling, and how this portfolio is run.</span>
          <span class="hp-map__go">Read the blog →</span>
        </a>
        <a class="hp-map__item hp-map__item--accent" href="<?php echo appUrl('public/contact.php'); ?>">
          <span class="hp-map__kicker">Next step</span>
          <strong>Contact</strong>
          <span>Have a role, collaboration, or question? Send a message.</span>
          <span class="hp-map__go">Get in touch →</span>
        </a>
      </div>
    </div>
  </section>

  <!-- Education path + Experience -->
  <section class="hp-paths">
    <div class="container hp-paths__inner">
      <div class="hp-edu-path">
        <div class="hp-section-label">
          <span class="hp-section-label__text">Education path</span>
        </div>
        <h2 class="hp-path__title">Academic route</h2>
        <p class="hp-edu-path__lede">Primary to graduate - a clear through-line from foundations to research.</p>

        <div class="hp-edu-board">
          <ol class="hp-edu-track" aria-label="Education path">
            <?php foreach ($eduPath as $i => $step): ?>
              <li class="hp-edu-step hp-edu-step--n<?php echo (int) ($i + 1); ?>" style="--edu-i: <?php echo (int) $i; ?>">
                <div class="hp-edu-step__badge" aria-hidden="true">
                  <span class="hp-edu-step__num"><?php echo htmlspecialchars($step['stage'], ENT_QUOTES, 'UTF-8'); ?></span>
                  <i class="fa-solid <?php echo htmlspecialchars($step['icon'], ENT_QUOTES, 'UTF-8'); ?>"></i>
                </div>
                <div class="hp-edu-step__copy">
                  <p class="hp-edu-step__stage"><?php echo htmlspecialchars($step['label'], ENT_QUOTES, 'UTF-8'); ?></p>
                  <h3 class="hp-edu-step__title"><?php echo htmlspecialchars($step['title'], ENT_QUOTES, 'UTF-8'); ?></h3>
                  <p class="hp-edu-step__place"><?php echo htmlspecialchars($step['place'], ENT_QUOTES, 'UTF-8'); ?></p>
                  <p class="hp-edu-step__when"><?php echo htmlspecialchars($step['when'], ENT_QUOTES, 'UTF-8'); ?></p>
                  <p class="hp-edu-step__result"><?php echo htmlspecialchars($step['result'], ENT_QUOTES, 'UTF-8'); ?></p>
                </div>
              </li>
            <?php endforeach; ?>
          </ol>
        </div>

        <a class="hp-text-link" href="<?php echo appUrl('public/education.php'); ?>">Full education timeline <span>→</span></a>
      </div>

      <div class="hp-path__col">
        <div class="hp-section-label">
          <span class="hp-section-label__text">Experience</span>
        </div>
        <h2 class="hp-path__title">Professional path</h2>
        <p class="hp-edu-path__lede">A tracker of roles over time - including concurrent work when paths overlapped.</p>

        <div class="hp-gantt" aria-label="Professional experience tracker">
          <div class="hp-gantt__years" aria-hidden="true">
            <?php foreach ($expTrackYears as $year): ?>
              <span><?php echo (int) $year; ?></span>
            <?php endforeach; ?>
          </div>

          <div class="hp-gantt__chart">
            <div class="hp-gantt__grid" aria-hidden="true">
              <?php foreach ($expTrackYears as $year): ?>
                <span></span>
              <?php endforeach; ?>
            </div>
            <div class="hp-gantt__today" style="left: <?php echo htmlspecialchars((string) round($expTrackToday, 2), ENT_QUOTES, 'UTF-8'); ?>%;" aria-hidden="true">
              <span>Now</span>
            </div>

            <ol class="hp-gantt__lanes">
              <?php foreach ($expTrack as $i => $row):
                $barClass = 'hp-gantt__bar hp-gantt__bar--' . $row['tone'];
                $labelOutside = $row['width'] < 14;
                if ($labelOutside) {
                  $barClass .= ' hp-gantt__bar--outside';
                }
                $arrowLeft = round($row['left'] + ($row['width'] / 2), 2);
              ?>
                <li class="hp-gantt__lane hp-gantt__lane--lane<?php echo (int) $row['lane']; ?>">
                  <div
                    class="<?php echo htmlspecialchars($barClass, ENT_QUOTES, 'UTF-8'); ?>"
                    style="left: <?php echo htmlspecialchars((string) $row['left'], ENT_QUOTES, 'UTF-8'); ?>%; width: <?php echo htmlspecialchars((string) $row['width'], ENT_QUOTES, 'UTF-8'); ?>%;"
                    title="<?php echo htmlspecialchars($row['role'] . ' · ' . $row['company'] . ' · ' . $row['when'], ENT_QUOTES, 'UTF-8'); ?>"
                  >
                    <span class="hp-gantt__bar-fill" aria-hidden="true"></span>
                    <?php if (!$labelOutside): ?>
                      <span class="hp-gantt__bar-text"><?php echo htmlspecialchars($row['label'], ENT_QUOTES, 'UTF-8'); ?></span>
                    <?php endif; ?>
                  </div>
                  <?php if ($labelOutside): ?>
                    <span
                      class="hp-gantt__name"
                      style="left: <?php echo htmlspecialchars((string) round($row['left'] + $row['width'] + 0.8, 2), ENT_QUOTES, 'UTF-8'); ?>%;"
                    ><?php echo htmlspecialchars($row['label'], ENT_QUOTES, 'UTF-8'); ?></span>
                  <?php endif; ?>
                </li>
                <?php if ($i < count($expTrack) - 1): ?>
                  <li class="hp-gantt__arrow" aria-hidden="true">
                    <span style="left: <?php echo htmlspecialchars((string) $arrowLeft, ENT_QUOTES, 'UTF-8'); ?>%;">↓</span>
                  </li>
                <?php endif; ?>
              <?php endforeach; ?>
            </ol>
          </div>
        </div>

        <a class="hp-text-link" href="<?php echo appUrl('public/experience.php'); ?>">Full experience timeline <span>→</span></a>
      </div>
    </div>
  </section>

  <!-- Projects -->
  <?php if ($workProjects): ?>
  <section class="hp-work">
    <div class="container">
      <div class="hp-work__header">
        <div>
          <div class="hp-section-label">
            <span class="hp-section-label__text">Selected work</span>
          </div>
          <h2 class="hp-section-title">Live projects worth opening.</h2>
          <p class="hp-section-lede">A short cut into the gallery - each one is hosted and ready to explore.</p>
        </div>
        <a class="hp-cta hp-cta--primary" href="<?php echo appUrl('public/projects.php'); ?>">
          All projects <span class="hp-cta-arrow">→</span>
        </a>
      </div>

      <div class="hp-work__rail">
        <?php foreach ($workProjects as $i => $proj):
          $img = hp_project_image($proj);
          $url = trim((string) ($proj['url'] ?? ''));
          $href = $url !== '' ? $url : appUrl('public/projects.php');
          $external = $url !== '';
        ?>
          <a
            class="hp-work__card"
            href="<?php echo htmlspecialchars($href, ENT_QUOTES, 'UTF-8'); ?>"
            <?php if ($external): ?>target="_blank" rel="noopener noreferrer"<?php endif; ?>
          >
            <div class="hp-work__shot<?php echo $img ? '' : ' hp-work__shot--empty'; ?> img-skeleton-wrap">
              <?php if ($img): ?>
                <img
                  src="<?php echo htmlspecialchars($img, ENT_QUOTES, 'UTF-8'); ?>"
                  alt=""
                  loading="<?php echo $i === 0 ? 'eager' : 'lazy'; ?>"
                  decoding="async"
                  onload="this.closest('.img-skeleton-wrap').classList.add('loaded')"
                >
              <?php else: ?>
                <?php echo media_placeholder($proj['title'] ?? 'Project', 'media-ph--project'); ?>
              <?php endif; ?>
            </div>
            <div class="hp-work__meta">
              <strong><?php echo htmlspecialchars((string) ($proj['title'] ?? 'Project'), ENT_QUOTES, 'UTF-8'); ?></strong>
              <?php if (!empty($proj['description'])): ?>
                <span><?php echo htmlspecialchars(hp_short((string) $proj['description'], 110), ENT_QUOTES, 'UTF-8'); ?></span>
              <?php endif; ?>
              <em><?php echo $external ? 'Launch live →' : 'View in gallery →'; ?></em>
            </div>
          </a>
        <?php endforeach; ?>
      </div>
    </div>
  </section>
  <?php elseif ($featuredProject):
    $fpImg = hp_project_image($featuredProject);
  ?>
  <section class="hp-featured">
    <div class="container hp-featured__inner">
      <div class="hp-featured__copy">
        <div class="hp-section-label">
          <span class="hp-section-label__text">Selected work</span>
        </div>
        <h2 class="hp-featured__title"><?php echo htmlspecialchars($featuredProject['title'], ENT_QUOTES, 'UTF-8'); ?></h2>
        <?php if (!empty($featuredProject['description'])): ?>
          <p class="hp-featured__desc"><?php echo htmlspecialchars(hp_short((string) $featuredProject['description'], 220), ENT_QUOTES, 'UTF-8'); ?></p>
        <?php endif; ?>
        <div class="hp-featured__actions">
          <?php if (!empty($featuredProject['url'])): ?>
            <a class="hp-cta hp-cta--primary" href="<?php echo htmlspecialchars($featuredProject['url'], ENT_QUOTES, 'UTF-8'); ?>" target="_blank" rel="noopener noreferrer">
              Launch live <span class="hp-cta-arrow">→</span>
            </a>
          <?php endif; ?>
          <a class="hp-text-link" href="<?php echo appUrl('public/projects.php'); ?>">All projects <span>→</span></a>
        </div>
      </div>
      <div class="hp-featured__media">
        <?php if ($fpImg): ?>
          <div class="hp-featured__shot img-skeleton-wrap">
            <img
              src="<?php echo htmlspecialchars($fpImg, ENT_QUOTES, 'UTF-8'); ?>"
              alt="<?php echo htmlspecialchars($featuredProject['title'], ENT_QUOTES, 'UTF-8'); ?> preview"
              loading="lazy"
              decoding="async"
              onload="this.closest('.img-skeleton-wrap').classList.add('loaded')"
            >
          </div>
        <?php endif; ?>
      </div>
    </div>
  </section>
  <?php endif; ?>

  <!-- Skills -->
  <?php if ($skillPreview): ?>
  <section class="hp-skills">
    <div class="container hp-skills__inner">
      <div class="hp-skills__copy">
        <div class="hp-section-label">
          <span class="hp-section-label__text">Skills</span>
        </div>
        <h2 class="hp-section-title">A practical toolkit.</h2>
        <p class="hp-section-lede">Languages, data, cloud, and delivery tools I use when shipping systems and teaching others to do the same.</p>
        <a class="hp-text-link" href="<?php echo appUrl('public/skills.php'); ?>">See full skills list <span>→</span></a>
      </div>
      <ul class="hp-skills__cloud" aria-label="Highlighted skills">
        <?php foreach ($skillPreview as $skill): ?>
          <li>
            <span><?php echo htmlspecialchars((string) ($skill['name'] ?? ''), ENT_QUOTES, 'UTF-8'); ?></span>
            <?php if (!empty($skill['level'])): ?>
              <small><?php echo htmlspecialchars((string) $skill['level'], ENT_QUOTES, 'UTF-8'); ?></small>
            <?php endif; ?>
          </li>
        <?php endforeach; ?>
      </ul>
    </div>
  </section>
  <?php endif; ?>

  <!-- Blog previews -->
  <?php if ($latestPosts): ?>
  <section class="hp-blog">
    <div class="container">
      <div class="hp-blog__header">
        <div>
          <div class="hp-section-label">
            <span class="hp-section-label__text">From the blog</span>
          </div>
          <h2 class="hp-section-title">Recent writing</h2>
          <p class="hp-section-lede">Notes on reverse engineering, tooling, and how this portfolio is run.</p>
        </div>
        <a class="hp-cta hp-cta--primary" href="<?php echo appUrl('blog.php'); ?>">
          All posts <span class="hp-cta-arrow">→</span>
        </a>
      </div>

      <div class="hp-blog__grid">
        <?php foreach ($latestPosts as $i => $post):
          $postImg = hp_post_image($post);
          $postHref = hp_post_href($post);
          $postDate = !empty($post['published_at']) ? date('M j, Y', strtotime((string) $post['published_at'])) : '';
          $postCat = trim((string) ($post['category'] ?? ''));
        ?>
          <a class="hp-blog__card" href="<?php echo htmlspecialchars($postHref, ENT_QUOTES, 'UTF-8'); ?>">
            <div class="hp-blog__media<?php echo $postImg ? ' img-skeleton-wrap' : ' hp-blog__media--empty'; ?>">
              <?php if ($postImg): ?>
                <img
                  src="<?php echo htmlspecialchars($postImg, ENT_QUOTES, 'UTF-8'); ?>"
                  alt=""
                  loading="<?php echo $i === 0 ? 'eager' : 'lazy'; ?>"
                  decoding="async"
                  onload="this.closest('.img-skeleton-wrap') && this.closest('.img-skeleton-wrap').classList.add('loaded')"
                >
              <?php else: ?>
                <?php echo media_placeholder($post['title'] ?? 'Post', 'media-ph--card'); ?>
              <?php endif; ?>
            </div>
            <div class="hp-blog__body">
              <div class="hp-blog__meta">
                <?php if ($postCat !== ''): ?>
                  <span class="hp-blog__cat"><?php echo htmlspecialchars($postCat, ENT_QUOTES, 'UTF-8'); ?></span>
                <?php endif; ?>
                <?php if ($postDate !== ''): ?>
                  <time datetime="<?php echo htmlspecialchars(date('c', strtotime((string) $post['published_at'])), ENT_QUOTES, 'UTF-8'); ?>">
                    <?php echo htmlspecialchars($postDate, ENT_QUOTES, 'UTF-8'); ?>
                  </time>
                <?php endif; ?>
              </div>
              <strong class="hp-blog__title"><?php echo htmlspecialchars((string) $post['title'], ENT_QUOTES, 'UTF-8'); ?></strong>
              <?php if (!empty($post['excerpt'])): ?>
                <span class="hp-blog__excerpt"><?php echo htmlspecialchars(hp_short((string) $post['excerpt'], 140), ENT_QUOTES, 'UTF-8'); ?></span>
              <?php endif; ?>
              <em class="hp-blog__go">Read post →</em>
            </div>
          </a>
        <?php endforeach; ?>
      </div>
    </div>
  </section>
  <?php endif; ?>

  <!-- About -->
  <section class="hp-signal">
    <div class="container hp-signal__inner">
      <div class="hp-signal__col">
        <div class="hp-section-label">
          <span class="hp-section-label__text">About</span>
        </div>
        <h2 class="hp-path__title">Who I am</h2>
        <p class="hp-about-snippet">
          I'm a Computer Scientist, Software Developer, Researcher, and Technology Innovator.
          This site gathers projects, research, academic work, and ideas into one place -
          where I've been, and where I'm headed.
        </p>
        <div class="hp-signal__actions">
          <a class="hp-text-link" href="<?php echo appUrl('public/about.php'); ?>">Read full bio <span>→</span></a>
          <a class="hp-text-link" href="<?php echo appUrl('public/Sila_Kipng\'etich_Tanui_CV.pdf'); ?>" target="_blank" rel="noopener noreferrer">Download CV <span>→</span></a>
        </div>
        <p class="hp-signal__stats">
          <span><strong><?php echo max($totalProjects, 1); ?>+</strong> projects</span>
          <span><strong><?php echo max($totalPublications, 1); ?>+</strong> publications</span>
          <span><strong><?php echo max(count($experience ?? []), 1); ?></strong> roles</span>
        </p>
      </div>
    </div>
  </section>

  <!-- Closing CTA -->
  <section class="hp-cta-strip">
    <div class="container hp-cta-strip__inner">
      <div class="hp-cta-strip__copy">
        <p class="hp-cta-strip__kicker">Next step</p>
        <h2 class="hp-cta-strip__heading">Let's build something purposeful together.</h2>
        <p class="hp-cta-strip__sub">Whether you are hiring, collaborating, or curious about the work, I would love to hear from you.</p>
        <div class="hp-cta-strip__meta">
          <a href="<?php echo appUrl('public/projects.php'); ?>">Projects</a>
          <a href="<?php echo appUrl('public/experience.php'); ?>">Experience</a>
          <a href="<?php echo appUrl('now.php'); ?>">Now</a>
          <a href="<?php echo appUrl('lab.php'); ?>">Lab</a>
        </div>
      </div>
      <div class="hp-cta-strip__actions">
        <a class="hp-cta hp-cta--inverted" href="<?php echo appUrl('public/contact.php'); ?>">
          Get in Touch <span class="hp-cta-arrow">→</span>
        </a>
        <a class="hp-cta hp-cta--ghost-light" href="<?php echo appUrl('public/Sila_Kipng\'etich_Tanui_CV.pdf'); ?>" target="_blank" rel="noopener noreferrer">
          <i class="fa-solid fa-file-pdf"></i> Download CV
        </a>
      </div>
    </div>
  </section>
</main>

<style>
/* ── HERO ───────────────────────────────────────────────── */
.hp-hero {
  position: relative;
  min-height: calc(100vh - 116px);
  display: flex;
  flex-direction: column;
  justify-content: center;
  overflow: hidden;
  border-bottom: 1px solid var(--light-gray);
  background:
    linear-gradient(135deg, rgba(255, 247, 237, 0.9) 0%, transparent 42%),
    linear-gradient(180deg, #ffffff 0%, #f8fafc 55%, #ffffff 100%);
}

.hp-hero__atmosphere {
  position: absolute;
  inset: 0;
  pointer-events: none;
  z-index: 0;
}

.hp-hero__orb {
  position: absolute;
  border-radius: 50% !important;
  filter: blur(8px);
}

.hp-hero__orb--a {
  width: min(52vw, 560px);
  height: min(52vw, 560px);
  top: -18%;
  left: -8%;
  background: radial-gradient(circle, rgba(234, 88, 12, 0.18) 0%, transparent 68%);
  animation: hp-orb-drift 14s ease-in-out infinite alternate;
}

.hp-hero__orb--b {
  width: min(40vw, 420px);
  height: min(40vw, 420px);
  right: 8%;
  bottom: -12%;
  background: radial-gradient(circle, rgba(15, 23, 42, 0.08) 0%, transparent 70%);
  animation: hp-orb-drift 18s ease-in-out infinite alternate-reverse;
}

.hp-hero__grain {
  position: absolute;
  inset: 0;
  opacity: 0.35;
  background-image:
    linear-gradient(rgba(15, 23, 42, 0.03) 1px, transparent 1px),
    linear-gradient(90deg, rgba(15, 23, 42, 0.03) 1px, transparent 1px);
  background-size: 56px 56px;
  mask-image: radial-gradient(ellipse 70% 55% at 30% 40%, #000 15%, transparent 72%);
}

@keyframes hp-orb-drift {
  from { transform: translate3d(0, 0, 0) scale(1); }
  to { transform: translate3d(18px, 24px, 0) scale(1.06); }
}

.hp-hero__plane {
  position: relative;
  z-index: 1;
  padding: clamp(40px, 6vw, 72px) 0 clamp(36px, 5vw, 56px);
}

.hp-hero__inner {
  display: grid;
  grid-template-columns: 1.05fr 0.95fr;
  gap: clamp(28px, 4vw, 56px);
  align-items: stretch;
  min-height: min(66vh, 620px);
}

.hp-hero__copy {
  display: flex;
  flex-direction: column;
  justify-content: center;
  gap: 14px;
  max-width: 640px;
}

.hp-hero__role {
  margin: 0;
  display: inline-flex;
  align-items: center;
  gap: 10px;
  font-size: var(--text-meta, 0.72rem);
  font-weight: 800;
  letter-spacing: 0.16em;
  text-transform: uppercase;
  color: var(--accent);
}

.hp-hero__pulse {
  width: 8px;
  height: 8px;
  background: var(--accent);
  box-shadow: 0 0 0 0 rgba(234, 88, 12, 0.45);
  animation: hp-pulse 2.2s ease-out infinite;
}

@keyframes hp-pulse {
  0% { box-shadow: 0 0 0 0 rgba(234, 88, 12, 0.45); }
  70% { box-shadow: 0 0 0 10px rgba(234, 88, 12, 0); }
  100% { box-shadow: 0 0 0 0 rgba(234, 88, 12, 0); }
}

.hp-hero__brand {
  margin: 0;
  font-size: clamp(2.7rem, 6.8vw, 4.8rem);
  line-height: 0.92;
  letter-spacing: -0.055em;
  font-weight: 800;
  color: var(--text);
}

.hp-brand-line {
  display: block;
  font-family: var(--font-brand-name);
  opacity: 0;
  transform: translateY(22px);
  animation: hp-slide-up 0.7s cubic-bezier(0.16, 1, 0.3, 1) 0.05s forwards;
}

.hp-hero__tagline {
  margin: 4px 0 0;
  font-size: clamp(1.15rem, 2.2vw, 1.55rem);
  font-weight: 600;
  letter-spacing: -0.02em;
  line-height: 1.25;
  color: var(--accent);
  opacity: 0;
  transform: translateY(16px);
  animation: hp-slide-up 0.65s cubic-bezier(0.16, 1, 0.3, 1) 0.18s forwards;
}

@keyframes hp-slide-up {
  to { opacity: 1; transform: translateY(0); }
}

.hp-hero__sub {
  margin: 0;
  max-width: 34rem;
  color: var(--muted);
  font-size: clamp(1rem, 1.4vw, 1.08rem);
  line-height: 1em;
  opacity: 0;
  animation: hp-slide-up 0.65s cubic-bezier(0.16, 1, 0.3, 1) 0.32s forwards;
}

.hp-hero__ctas {
  display: flex;
  flex-wrap: wrap;
  align-items: center;
  gap: 12px;
  margin-top: 8px;
  opacity: 0;
  animation: hp-slide-up 0.65s cubic-bezier(0.16, 1, 0.3, 1) 0.46s forwards;
}

.hp-cta {
  display: inline-flex;
  align-items: center;
  gap: 8px;
  padding: 14px 24px;
  font-size: 0.8rem;
  font-weight: 800;
  letter-spacing: 0.06em;
  text-transform: uppercase;
  text-decoration: none;
  transition: background 0.2s ease, border-color 0.2s ease, color 0.2s ease, transform 0.2s ease;
  white-space: nowrap;
}

.hp-cta--primary {
  background: var(--text);
  color: #fff;
  border: 1px solid var(--text);
}

.hp-cta--primary:hover {
  background: var(--accent);
  border-color: var(--accent);
  opacity: 1;
  transform: translateY(-2px);
}

.hp-cta--secondary {
  background: rgba(255, 255, 255, 0.72);
  color: var(--text);
  border: 1px solid rgba(15, 23, 42, 0.16);
  backdrop-filter: blur(6px);
}

.hp-cta--secondary:hover {
  border-color: rgba(234, 88, 12, 0.45);
  color: var(--accent);
  opacity: 1;
}

.hp-cta--inverted {
  background: #fff;
  color: var(--text);
  border: 1px solid #fff;
}

.hp-cta--inverted:hover {
  background: var(--accent);
  border-color: var(--accent);
  color: #fff;
  opacity: 1;
}

.hp-cta--ghost-light {
  background: transparent;
  color: rgba(255, 255, 255, 0.82);
  border: 1px solid rgba(255, 255, 255, 0.28);
}

.hp-cta--ghost-light:hover { opacity: 0.75; }

.hp-cta-arrow {
  display: inline-block;
  transition: transform 0.2s ease;
}

.hp-cta:hover .hp-cta-arrow { transform: translateX(4px); }

.hp-hero__visual {
  display: flex;
  align-items: stretch;
  justify-content: flex-end;
  opacity: 0;
  animation: hp-fade-in 0.9s ease 0.28s forwards;
  min-width: 0;
}

@keyframes hp-fade-in {
  to { opacity: 1; }
}

.hp-photo-frame {
  position: relative;
  width: 100%;
  height: 100%;
  min-height: min(66vh, 620px);
}

.hp-photo-accent {
  position: absolute;
  inset: 18px -14px -14px 18px;
  border: 1px solid rgba(234, 88, 12, 0.35);
  z-index: 0;
  pointer-events: none;
}

.hp-photo-inner {
  position: relative;
  z-index: 1;
  width: 100%;
  height: 100%;
  min-height: inherit;
  overflow: hidden;
  background: #e2e8f0;
  border: 1px solid rgba(15, 23, 42, 0.12);
  box-shadow: 0 28px 60px rgba(15, 23, 42, 0.12);
}

.hp-photo {
  width: 100%;
  height: 100%;
  min-height: inherit;
  object-fit: cover;
  object-position: top center;
  display: block;
  filter: saturate(0.92) contrast(1.02);
  transition: transform 0.7s cubic-bezier(0.22, 1, 0.36, 1), filter 0.4s ease;
}

.hp-photo-frame:hover .hp-photo {
  transform: scale(1.03);
  filter: saturate(1) contrast(1.04);
}

.hp-photo-caption {
  position: absolute;
  left: 0;
  right: 0;
  bottom: 0;
  z-index: 2;
  margin: 0;
  display: flex;
  justify-content: space-between;
  padding: 12px 16px;
  font-size: 0.68rem;
  font-weight: 800;
  letter-spacing: 0.12em;
  text-transform: uppercase;
  color: #fff;
  background: linear-gradient(180deg, transparent, rgba(15, 23, 42, 0.72));
  pointer-events: none;
}

.hp-scroll-indicator {
  position: absolute;
  bottom: 22px;
  left: 50%;
  transform: translateX(-50%);
  display: flex;
  flex-direction: column;
  align-items: center;
  gap: 8px;
  font-size: 0.66rem;
  font-weight: 800;
  letter-spacing: 0.14em;
  text-transform: uppercase;
  color: var(--muted);
  opacity: 0;
  animation: hp-fade-in 1s ease 1.1s forwards;
  z-index: 2;
}

.hp-scroll-line {
  display: block;
  width: 1px;
  height: 36px;
  background: linear-gradient(to bottom, var(--accent), transparent);
  animation: hp-line-grow 1.5s ease-in-out infinite;
}

@keyframes hp-line-grow {
  0% { transform: scaleY(0); transform-origin: top; opacity: 0; }
  50% { transform: scaleY(1); transform-origin: top; opacity: 1; }
  100% { transform: scaleY(1); transform-origin: bottom; opacity: 0; }
}

/* Shared */
.hp-section-label {
  display: flex;
  align-items: center;
  gap: 12px;
  margin-bottom: 12px;
}

.hp-section-label__text {
  font-size: 0.7rem;
  font-weight: 800;
  letter-spacing: 0.16em;
  text-transform: uppercase;
  color: var(--accent);
}

.hp-section-label::after {
  content: '';
  display: block;
  flex: 1;
  height: 1px;
  background: var(--light-gray);
  max-width: 72px;
}

.hp-section-title {
  margin: 0 0 10px;
  font-size: clamp(1.7rem, 3vw, 2.5rem);
  font-weight: 800;
  letter-spacing: -0.045em;
  line-height: 1.05;
  color: var(--text);
}

#main-content p,
#main-content li,
.hp-hero__sub,
.hp-section-lede,
.hp-map__item > span:not(.hp-map__kicker):not(.hp-map__go),
.hp-work__meta span,
.hp-featured__desc,
.hp-blog__excerpt,
.hp-about-snippet,
.hp-cta-strip__sub,
.hp-edu-step__place,
.hp-edu-step__when,
.hp-edu-step__result {
  line-height: 1em;
}

.hp-section-lede {
  margin: 0;
  max-width: 46ch;
  font-size: 1.02rem;
  line-height: 1em;
  color: var(--muted);
}

.hp-text-link {
  display: inline-flex;
  align-items: center;
  gap: 6px;
  font-size: 0.78rem;
  font-weight: 800;
  letter-spacing: 0.08em;
  text-transform: uppercase;
  color: var(--text);
  border-bottom: 2px solid var(--accent);
  padding-bottom: 2px;
  text-decoration: none;
  transition: color 0.2s ease;
}

.hp-text-link:hover {
  color: var(--accent);
  opacity: 1;
}

/* Explore map */
.hp-map {
  padding: clamp(48px, 7vw, 88px) 0;
  border-bottom: 1px solid var(--light-gray);
  background:
    radial-gradient(ellipse 40% 50% at 0% 0%, rgba(234, 88, 12, 0.05), transparent 60%),
    #fff;
}

.hp-map__head {
  margin-bottom: 28px;
  max-width: 40rem;
}

.hp-map__grid {
  display: grid;
  grid-template-columns: repeat(3, 1fr);
  gap: 1px;
  background: var(--light-gray);
  border: 1px solid var(--light-gray);
}

.hp-map__item {
  display: flex;
  flex-direction: column;
  gap: 8px;
  padding: 26px 24px;
  background: #fff;
  text-decoration: none;
  color: inherit;
  min-height: 180px;
  transition: background 0.25s ease, transform 0.25s ease;
}

.hp-map__item:hover {
  background: rgba(255, 247, 237, 0.85);
  opacity: 1;
}

.hp-map__item--accent {
  background: linear-gradient(160deg, #fff7ed 0%, #fff 55%);
}

.hp-map__kicker {
  font-size: 0.68rem;
  font-weight: 800;
  letter-spacing: 0.14em;
  text-transform: uppercase;
  color: var(--accent);
}

.hp-map__item strong {
  font-size: 1.25rem;
  font-weight: 800;
  letter-spacing: -0.03em;
  color: var(--text);
}

.hp-map__item > span:not(.hp-map__kicker):not(.hp-map__go) {
  font-size: 0.92rem;
  line-height: 1em;
  color: var(--muted);
  flex: 1;
}

.hp-map__go {
  margin-top: 8px;
  font-size: 0.78rem;
  font-weight: 800;
  letter-spacing: 0.06em;
  text-transform: uppercase;
  color: var(--text);
  border-bottom: 1px solid rgba(234, 88, 12, 0.45);
  align-self: flex-start;
  padding-bottom: 2px;
  transition: color 0.2s ease, transform 0.2s ease;
}

.hp-map__item:hover .hp-map__go {
  color: var(--accent);
  transform: translateX(3px);
}

/* Education + experience row */
.hp-paths {
  padding: clamp(48px, 7vw, 88px) 0;
  border-bottom: 1px solid var(--light-gray);
  background:
    radial-gradient(ellipse 40% 60% at 8% 12%, rgba(234, 88, 12, 0.07), transparent 58%),
    radial-gradient(ellipse 30% 45% at 92% 88%, rgba(15, 23, 42, 0.04), transparent 55%),
    #f8fafc;
}

.hp-paths__inner {
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: clamp(32px, 5vw, 56px);
  align-items: stretch;
}

.hp-edu-path,
.hp-path__col {
  min-width: 0;
  display: flex;
  flex-direction: column;
}

.hp-edu-path .hp-section-label {
  margin-bottom: 14px;
}

.hp-edu-path .hp-section-label::after {
  display: none;
}

.hp-edu-path .hp-path__title {
  margin: 0 0 10px;
  line-height: 1.15;
}

.hp-edu-path__lede {
  margin: 0 0 28px;
  max-width: 38ch;
  font-size: 0.92rem;
  color: var(--muted);
  line-height: 1em;
}

.hp-edu-board {
  position: relative;
  background:
    linear-gradient(165deg, #ffffff 0%, #fffaf5 48%, #fff7ed 100%);
  border: 1px solid rgba(15, 23, 42, 0.08);
  padding: 28px 20px 22px;
  margin-bottom: 18px;
  overflow: visible;
  box-shadow:
    0 1px 0 rgba(255, 255, 255, 0.8) inset,
    0 18px 44px rgba(15, 23, 42, 0.06);
  flex: 1;
}

.hp-edu-path > .hp-text-link {
  margin-top: auto;
}

.hp-edu-board::before {
  content: '';
  position: absolute;
  inset: 0;
  background-image:
    linear-gradient(rgba(15, 23, 42, 0.03) 1px, transparent 1px),
    linear-gradient(90deg, rgba(15, 23, 42, 0.03) 1px, transparent 1px);
  background-size: 20px 20px;
  mask-image: radial-gradient(ellipse 75% 70% at 50% 42%, #000 15%, transparent 78%);
  pointer-events: none;
  z-index: 0;
}

.hp-edu-track {
  list-style: none;
  margin: 0;
  padding: 0;
  display: grid;
  grid-template-columns: 1fr 1fr;
  grid-template-rows: auto auto;
  gap: 36px 28px;
  position: relative;
  z-index: 1;
  align-items: start;
}

.hp-edu-step {
  display: flex;
  flex-direction: column;
  align-items: center;
  text-align: center;
  gap: 10px;
  min-width: 0;
  padding: 8px 6px;
  transition: transform 0.25s ease;
}

.hp-edu-step:hover {
  transform: translateY(-3px);
}

.hp-edu-step__badge {
  position: relative;
  z-index: 1;
}

.hp-edu-step__copy {
  position: relative;
  z-index: 1;
}

.hp-edu-step--n1 { grid-column: 1; grid-row: 1; }
.hp-edu-step--n2 { grid-column: 2; grid-row: 1; }
.hp-edu-step--n3 { grid-column: 2; grid-row: 2; }
.hp-edu-step--n4 { grid-column: 1; grid-row: 2; }

.hp-edu-step--n1::after,
.hp-edu-step--n2::after,
.hp-edu-step--n3::after {
  content: '→';
  position: absolute;
  z-index: 2;
  font-size: 1.35rem;
  font-weight: 900;
  line-height: 1;
  color: var(--accent);
  pointer-events: none;
  -webkit-text-stroke: 0.6px var(--accent);
  paint-order: stroke fill;
}

.hp-edu-step--n1,
.hp-edu-step--n2,
.hp-edu-step--n3 {
  position: relative;
}

.hp-edu-step--n1::after {
  top: 28px;
  right: -16px;
  transform: translateX(50%);
}

.hp-edu-step--n2::after {
  content: '↓';
  top: auto;
  bottom: -26px;
  left: 50%;
  right: auto;
  transform: translateX(-50%);
}

.hp-edu-step--n3::after {
  content: '←';
  top: 28px;
  left: -16px;
  right: auto;
  transform: translateX(-50%);
}

.hp-edu-step--n3 {
  padding-top: 6px;
}

.hp-edu-step__badge {
  width: 54px;
  height: 54px;
  display: grid;
  place-items: center;
  position: relative;
  background:
    radial-gradient(circle at 30% 25%, #fff7ed, #fff 58%),
    #fff;
  border: 2px solid rgba(234, 88, 12, 0.42);
  color: var(--accent);
  font-size: 1.1rem;
  box-shadow:
    0 0 0 4px rgba(234, 88, 12, 0.07),
    0 10px 22px rgba(15, 23, 42, 0.1);
  transition: border-color 0.25s ease;
}

.hp-edu-step:hover .hp-edu-step__badge {
  border-color: var(--accent);
}

.hp-edu-step__num {
  position: absolute;
  top: -8px;
  right: -8px;
  width: 22px;
  height: 22px;
  display: grid;
  place-items: center;
  background: var(--text);
  color: #fff;
  font-size: 0.58rem;
  font-weight: 800;
  letter-spacing: 0.04em;
  border: 2px solid #fff;
}

.hp-edu-step__copy {
  display: flex;
  flex-direction: column;
  gap: 4px;
  align-items: center;
}

.hp-edu-step__stage {
  margin: 0;
  font-size: 0.62rem;
  font-weight: 800;
  letter-spacing: 0.12em;
  text-transform: uppercase;
  color: var(--accent);
  line-height: 1em;
}

.hp-edu-step__title {
  margin: 0;
  font-size: 0.92rem;
  font-weight: 800;
  letter-spacing: -0.02em;
  color: var(--text);
  line-height: 1.15;
}

.hp-edu-step__place,
.hp-edu-step__when,
.hp-edu-step__result {
  margin: 0;
  font-size: 0.76rem;
  color: var(--muted);
  line-height: 1em;
}

.hp-edu-step__result {
  margin-top: 4px;
  padding: 5px 8px;
  font-weight: 700;
  color: var(--text);
  background: rgba(234, 88, 12, 0.06);
  border: 1px solid rgba(234, 88, 12, 0.14);
}

.hp-path__col {
  padding: 4px 0 0;
}

.hp-path__col > .hp-text-link {
  margin-top: auto;
}

.hp-path__title {
  margin: 0 0 22px;
  font-size: clamp(1.5rem, 2.4vw, 2rem);
  font-weight: 800;
  letter-spacing: -0.04em;
  color: var(--text);
}

.hp-path__col .hp-path__title {
  margin-bottom: 10px;
}

.hp-path__col .hp-edu-path__lede {
  margin-bottom: 18px;
}

/* Gantt-style experience tracker */
.hp-gantt {
  background: #fff;
  border: 1px solid rgba(15, 23, 42, 0.08);
  padding: 16px 14px 18px;
  margin-bottom: 18px;
  box-shadow: 0 14px 36px rgba(15, 23, 42, 0.04);
  overflow: visible;
  flex: 1;
  display: flex;
  flex-direction: column;
  min-height: 0;
}

.hp-gantt__years {
  display: grid;
  grid-template-columns: repeat(5, 1fr);
  margin-bottom: 10px;
  padding-bottom: 8px;
  border-bottom: 1px solid var(--light-gray);
  font-size: 0.62rem;
  font-weight: 800;
  letter-spacing: 0.06em;
  text-transform: uppercase;
  color: var(--muted);
  flex: 0 0 auto;
}

.hp-gantt__chart {
  position: relative;
  display: flex;
  flex-direction: column;
  flex: 1;
  min-height: 0;
  padding: 4px 2px 0;
}

.hp-gantt__grid {
  position: absolute;
  inset: 0;
  display: grid;
  grid-template-columns: repeat(5, 1fr);
  pointer-events: none;
  z-index: 0;
}

.hp-gantt__grid span {
  border-left: 1px solid rgba(15, 23, 42, 0.06);
}

.hp-gantt__grid span:nth-child(odd) {
  background: rgba(15, 23, 42, 0.015);
}

.hp-gantt__today {
  position: absolute;
  top: 0;
  bottom: 0;
  width: 0;
  border-left: 2px solid #2563eb;
  z-index: 3;
  pointer-events: none;
}

.hp-gantt__today span {
  position: absolute;
  top: -2px;
  left: 4px;
  font-size: 0.58rem;
  font-weight: 800;
  letter-spacing: 0.08em;
  text-transform: uppercase;
  color: #2563eb;
  background: #fff;
  padding: 0 3px;
}

.hp-gantt__lanes {
  list-style: none;
  margin: 0;
  padding: 0;
  position: relative;
  z-index: 1;
  display: flex;
  flex-direction: column;
  justify-content: space-between;
  flex: 1;
  min-height: 380px;
  gap: 0;
}

.hp-gantt__lane {
  position: relative;
  height: 48px;
  flex: 0 0 auto;
}

.hp-gantt__arrow {
  position: relative;
  flex: 1 1 auto;
  min-height: 28px;
  list-style: none;
  pointer-events: none;
}

.hp-gantt__arrow span {
  position: absolute;
  top: 50%;
  transform: translate(-50%, -50%);
  font-size: 1.35rem;
  font-weight: 900;
  line-height: 1;
  color: var(--accent);
  -webkit-text-stroke: 0.6px var(--accent);
  paint-order: stroke fill;
}

.hp-gantt__bar {
  position: absolute;
  top: 4px;
  height: 40px;
  border-radius: 8px;
  display: flex;
  align-items: center;
  overflow: hidden;
  box-shadow: 0 4px 12px rgba(15, 23, 42, 0.1);
  min-width: 16px;
  z-index: 2;
}

.hp-gantt__bar-fill {
  position: absolute;
  inset: 0 auto 0 0;
  width: 58%;
  opacity: 0.22;
  background: #0f172a;
  pointer-events: none;
}

.hp-gantt__bar-text {
  position: relative;
  z-index: 1;
  padding: 0 12px;
  font-size: 0.8rem;
  font-weight: 800;
  letter-spacing: 0.01em;
  color: #fff;
  white-space: nowrap;
  overflow: hidden;
  text-overflow: ellipsis;
  max-width: 100%;
  text-shadow: 0 1px 2px rgba(15, 23, 42, 0.35);
}

.hp-gantt__name {
  position: absolute;
  top: 50%;
  transform: translateY(-50%);
  z-index: 2;
  margin-left: 2px;
  padding: 2px 0;
  font-size: 0.8rem;
  font-weight: 800;
  letter-spacing: -0.01em;
  color: var(--text);
  white-space: nowrap;
  line-height: 1;
  pointer-events: none;
}

.hp-gantt__years span {
  text-align: left;
  padding-left: 2px;
  border-left: 1px solid rgba(15, 23, 42, 0.08);
}

.hp-gantt__bar--slate { background: linear-gradient(90deg, #334155, #64748b); }
.hp-gantt__bar--blue { background: linear-gradient(90deg, #1d4ed8, #3b82f6); }
.hp-gantt__bar--orange { background: linear-gradient(90deg, #c2410c, #ea580c); }
.hp-gantt__bar--rose { background: linear-gradient(90deg, #be123c, #fb7185); }
.hp-gantt__bar--teal { background: linear-gradient(90deg, #0f766e, #14b8a6); }

/* Work rail */
.hp-work {
  padding: clamp(48px, 7vw, 88px) 0;
  border-bottom: 1px solid var(--light-gray);
}

.hp-work__header {
  display: flex;
  align-items: flex-end;
  justify-content: space-between;
  gap: 24px;
  margin-bottom: 28px;
  flex-wrap: wrap;
}

.hp-work__rail {
  display: grid;
  grid-template-columns: repeat(4, 1fr);
  gap: 16px;
}

.hp-work__card {
  display: flex;
  flex-direction: column;
  text-decoration: none;
  color: inherit;
  min-width: 0;
  transition: transform 0.25s ease;
}

.hp-work__card:hover {
  transform: translateY(-4px);
  opacity: 1;
}

.hp-work__shot {
  aspect-ratio: 16 / 10;
  overflow: hidden;
  border: 1px solid rgba(15, 23, 42, 0.1);
  background: #0f172a;
  margin-bottom: 14px;
}

.hp-work__shot img {
  width: 100%;
  height: 100%;
  object-fit: cover;
  object-position: top center;
  transition: transform 0.55s cubic-bezier(0.22, 1, 0.36, 1);
}

.hp-work__card:hover .hp-work__shot img {
  transform: scale(1.04);
}

.hp-work__shot--empty {
  display: grid;
  place-items: center;
  background: var(--soft-bg);
}

.hp-work__meta {
  display: flex;
  flex-direction: column;
  gap: 6px;
}

.hp-work__meta strong {
  font-size: 1.02rem;
  font-weight: 800;
  letter-spacing: -0.02em;
  color: var(--text);
}

.hp-work__meta span {
  font-size: 0.88rem;
  line-height: 1em;
  color: var(--muted);
}

.hp-work__meta em {
  margin-top: 4px;
  font-style: normal;
  font-size: 0.74rem;
  font-weight: 800;
  letter-spacing: 0.08em;
  text-transform: uppercase;
  color: var(--accent);
}

/* Featured fallback */
.hp-featured {
  padding: clamp(48px, 7vw, 88px) 0;
  border-bottom: 1px solid var(--light-gray);
}

.hp-featured__inner {
  display: grid;
  grid-template-columns: 0.9fr 1.1fr;
  gap: clamp(28px, 4vw, 56px);
  align-items: center;
}

.hp-featured__title {
  margin: 0 0 14px;
  font-size: clamp(1.7rem, 3vw, 2.5rem);
  font-weight: 800;
  letter-spacing: -0.045em;
  line-height: 1.05;
  color: var(--text);
}

.hp-featured__desc {
  margin: 0 0 22px;
  max-width: 38ch;
  font-size: 1.02rem;
  line-height: 1em;
  color: var(--muted);
}

.hp-featured__actions {
  display: flex;
  flex-wrap: wrap;
  align-items: center;
  gap: 16px 20px;
}

.hp-featured__shot {
  overflow: hidden;
  border: 1px solid rgba(15, 23, 42, 0.1);
  background: #0f172a;
  aspect-ratio: 16 / 10;
}

.hp-featured__shot img {
  width: 100%;
  height: 100%;
  object-fit: cover;
  object-position: top center;
}

/* Skills */
.hp-skills {
  padding: clamp(48px, 7vw, 88px) 0;
  border-bottom: 1px solid var(--light-gray);
  background:
    linear-gradient(180deg, #fff 0%, #fff7ed 100%);
}

.hp-skills__inner {
  display: grid;
  grid-template-columns: 0.9fr 1.1fr;
  gap: clamp(28px, 4vw, 48px);
  align-items: center;
}

.hp-skills__cloud {
  list-style: none;
  margin: 0;
  padding: 0;
  display: flex;
  flex-wrap: wrap;
  gap: 10px;
}

.hp-skills__cloud li {
  display: flex;
  flex-direction: column;
  gap: 2px;
  padding: 12px 16px;
  background: #fff;
  border: 1px solid rgba(15, 23, 42, 0.1);
  min-width: 118px;
}

.hp-skills__cloud span {
  font-size: 0.92rem;
  font-weight: 800;
  color: var(--text);
}

.hp-skills__cloud small {
  font-size: 0.68rem;
  font-weight: 700;
  letter-spacing: 0.08em;
  text-transform: uppercase;
  color: var(--muted);
}

/* Blog + about */
.hp-blog {
  padding: clamp(48px, 7vw, 88px) 0;
  border-bottom: 1px solid var(--light-gray);
  background:
    radial-gradient(ellipse 45% 55% at 100% 0%, rgba(234, 88, 12, 0.05), transparent 60%),
    #fff;
}

.hp-blog__header {
  display: flex;
  align-items: flex-end;
  justify-content: space-between;
  gap: 24px;
  margin-bottom: 28px;
  flex-wrap: wrap;
}

.hp-blog__grid {
  display: grid;
  grid-template-columns: repeat(3, 1fr);
  gap: 18px;
}

.hp-blog__card {
  display: flex;
  flex-direction: column;
  text-decoration: none;
  color: inherit;
  min-width: 0;
  background: #fff;
  border: 1px solid var(--light-gray);
  transition: border-color 0.2s ease, transform 0.25s ease, box-shadow 0.25s ease;
}

.hp-blog__card:hover {
  border-color: rgba(234, 88, 12, 0.4);
  transform: translateY(-3px);
  box-shadow: 0 16px 36px rgba(15, 23, 42, 0.08);
  opacity: 1;
}

.hp-blog__media {
  aspect-ratio: 16 / 10;
  overflow: hidden;
  background: #f1f5f9;
  border-bottom: 1px solid var(--light-gray);
}

.hp-blog__media img {
  width: 100%;
  height: 100%;
  object-fit: cover;
  object-position: center;
  transition: transform 0.5s cubic-bezier(0.22, 1, 0.36, 1);
}

.hp-blog__card:hover .hp-blog__media img {
  transform: scale(1.04);
}

.hp-blog__media--empty {
  display: grid;
  place-items: center;
}

.hp-blog__body {
  display: flex;
  flex-direction: column;
  gap: 8px;
  padding: 16px 18px 18px;
  flex: 1;
}

.hp-blog__meta {
  display: flex;
  flex-wrap: wrap;
  align-items: center;
  gap: 8px 12px;
  font-size: 0.74rem;
  font-weight: 700;
  color: var(--muted);
}

.hp-blog__cat {
  color: var(--accent);
  letter-spacing: 0.06em;
  text-transform: uppercase;
}

.hp-blog__title {
  font-size: 1.08rem;
  font-weight: 800;
  letter-spacing: -0.025em;
  color: var(--text);
  line-height: 1.15;
}

.hp-blog__excerpt {
  font-size: 0.9rem;
  color: var(--muted);
  line-height: 1em;
  flex: 1;
}

.hp-blog__go {
  margin-top: 4px;
  font-style: normal;
  font-size: 0.74rem;
  font-weight: 800;
  letter-spacing: 0.08em;
  text-transform: uppercase;
  color: var(--accent);
}

.hp-signal {
  padding: clamp(48px, 7vw, 88px) 0;
  border-bottom: 1px solid var(--light-gray);
}

.hp-signal__inner {
  display: grid;
  grid-template-columns: 1fr;
  gap: clamp(28px, 5vw, 56px);
  max-width: 44rem;
}

.hp-about-snippet {
  margin: 0 0 20px;
  font-size: 1.02rem;
  line-height: 1em;
  color: var(--muted);
  max-width: 42ch;
}

.hp-signal__actions {
  display: flex;
  flex-wrap: wrap;
  gap: 14px 22px;
  margin-bottom: 22px;
}

.hp-signal__stats {
  margin: 0;
  display: flex;
  flex-wrap: wrap;
  gap: 10px 18px;
  font-size: 0.82rem;
  color: var(--muted);
}

.hp-signal__stats strong {
  color: var(--text);
  font-weight: 800;
}

/* Closing CTA */
.hp-cta-strip {
  position: relative;
  overflow: hidden;
  background:
    radial-gradient(ellipse 50% 80% at 100% 0%, rgba(234, 88, 12, 0.28), transparent 55%),
    var(--text);
  padding: clamp(56px, 8vw, 96px) 0;
}

.hp-cta-strip__inner {
  display: grid;
  grid-template-columns: 1.2fr auto;
  gap: 40px;
  align-items: center;
  position: relative;
  z-index: 1;
}

.hp-cta-strip__kicker {
  margin: 0 0 10px;
  font-size: 0.7rem;
  font-weight: 800;
  letter-spacing: 0.16em;
  text-transform: uppercase;
  color: #fdba74;
}

.hp-cta-strip__heading {
  margin: 0 0 12px;
  font-size: clamp(1.6rem, 2.8vw, 2.35rem);
  font-weight: 800;
  letter-spacing: -0.04em;
  color: #fff;
  line-height: 1.08;
}

.hp-cta-strip__sub {
  margin: 0;
  font-size: 1rem;
  color: rgba(255, 255, 255, 0.65);
  line-height: 1em;
  max-width: 42ch;
}

.hp-cta-strip__meta {
  margin-top: 22px;
  display: flex;
  flex-wrap: wrap;
  gap: 10px 18px;
}

.hp-cta-strip__meta a {
  font-size: 0.78rem;
  font-weight: 700;
  letter-spacing: 0.04em;
  color: rgba(255, 255, 255, 0.55);
  text-decoration: none;
  border-bottom: 1px solid transparent;
}

.hp-cta-strip__meta a:hover {
  color: #fdba74;
  border-bottom-color: rgba(253, 186, 116, 0.5);
  opacity: 1;
}

.hp-cta-strip__actions {
  display: flex;
  flex-direction: column;
  gap: 12px;
  align-items: flex-end;
}

/* Responsive */
@media (max-width: 1100px) {
  .hp-map__grid { grid-template-columns: 1fr 1fr; }
  .hp-work__rail { grid-template-columns: 1fr 1fr; }
  .hp-blog__grid { grid-template-columns: 1fr 1fr; }
}

@media (max-width: 900px) {
  .hp-hero { min-height: 0; }

  .hp-hero__inner {
    grid-template-columns: 1fr;
    gap: 28px;
    min-height: 0;
  }

  .hp-hero__visual { order: -1; justify-content: stretch; }

  .hp-photo-frame,
  .hp-photo-inner,
  .hp-photo {
    min-height: min(58vw, 380px);
    height: min(58vw, 380px);
  }

  .hp-photo-accent { display: none; }

  .hp-paths__inner {
    grid-template-columns: 1fr;
  }

  .hp-edu-track {
    grid-template-columns: 1fr 1fr;
    gap: 34px 24px;
  }

  .hp-edu-step--n1 { grid-column: 1; grid-row: 1; }
  .hp-edu-step--n2 { grid-column: 2; grid-row: 1; }
  .hp-edu-step--n3 { grid-column: 2; grid-row: 2; }
  .hp-edu-step--n4 { grid-column: 1; grid-row: 2; }

  .hp-edu-step--n1::after,
  .hp-edu-step--n2::after,
  .hp-edu-step--n3::after {
    font-size: 1.15rem;
  }

  .hp-edu-step--n3 {
    padding-top: 4px;
  }

  .hp-skills__inner,
  .hp-signal__inner,
  .hp-featured__inner {
    grid-template-columns: 1fr;
  }

  .hp-cta-strip__inner {
    grid-template-columns: 1fr;
    gap: 28px;
  }

  .hp-cta-strip__actions {
    align-items: flex-start;
    flex-direction: row;
    flex-wrap: wrap;
  }

  .hp-scroll-indicator { display: none; }
}

@media (max-width: 640px) {
  .hp-map__grid,
  .hp-work__rail,
  .hp-blog__grid {
    grid-template-columns: 1fr;
  }

  .hp-cta { width: 100%; justify-content: center; }

  .hp-work__header .hp-cta,
  .hp-blog__header .hp-cta { width: auto; }
}

@media (prefers-reduced-motion: reduce) {
  .hp-brand-line,
  .hp-hero__tagline,
  .hp-hero__sub,
  .hp-hero__ctas,
  .hp-hero__visual,
  .hp-scroll-indicator,
  .hp-hero__orb--a,
  .hp-hero__orb--b { animation: none; opacity: 1; transform: none; }

  .hp-scroll-line,
  .hp-hero__pulse { animation: none; }

  .hp-photo,
  .hp-work__shot img,
  .hp-work__card,
  .hp-cta { transition: none; }
}
</style>

<?php
require_once __DIR__ . '/footer.php';
?>
