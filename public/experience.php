<?php
$activePage = 'experience';
require_once __DIR__ . '/header.php';

$expCount = count($experience);

function exp_logo_file(string $company): string {
  $company = strtolower($company);
  if (strpos($company, 'transcosmos') !== false) {
    return 'transcosmos.png';
  }
  if (strpos($company, 'litmed') !== false) {
    return 'litmed_college_kerugoya.png';
  }
  if (strpos($company, 'infolink') !== false) {
    return 'infolink_college_of_technology.png';
  }
  return '';
}

function exp_company_website(string $company): string {
  $company = strtolower($company);
  if (strpos($company, 'transcosmos') !== false || strpos($company, 'fossil') !== false) {
    return 'https://trans-cosmos.eu/locations/debrecen/';
  }
  if (strpos($company, 'infolink') !== false) {
    return 'https://www.infolinkcollege.com/';
  }
  if (strpos($company, 'litmed') !== false) {
    return 'https://www.litmed.ac.ke/';
  }
  return '';
}

function exp_parse_details(string $raw): array {
  $raw = trim($raw);
  if ($raw === '') {
    return ['summary' => '', 'chips' => [], 'bullets' => [], 'website' => ''];
  }

  $website = '';
  if (preg_match('#(?:Website:\s*)?(https?://[^\s]+)#i', $raw, $m)) {
    $website = rtrim($m[1], '.,);');
  }

  $raw = preg_replace('/^\s*Website:\s*https?:\/\/\S+\s*$/mi', '', $raw) ?? $raw;
  $raw = trim($raw);

  $parts = preg_split('/(?:\R|^)\s*[▪•\-]\s+/u', $raw) ?: [];
  $parts = array_values(array_filter(array_map('trim', $parts)));

  $summary = '';
  $chips = [];
  $bullets = [];

  if ($parts === []) {
    return ['summary' => $raw, 'chips' => [], 'bullets' => [], 'website' => $website];
  }

  $preamble = array_shift($parts);
  if ($preamble !== null && $preamble !== '') {
    $preLines = preg_split('/\R+/', $preamble) ?: [];
    $preLines = array_values(array_filter(array_map('trim', $preLines), static function ($line) {
      return $line !== '' && stripos($line, 'website:') !== 0 && !preg_match('#^https?://#i', $line);
    }));

    if ($preLines !== []) {
      $last = $preLines[count($preLines) - 1];
      $looksLikeChips = strpos($last, '|') !== false
        && !preg_match('/[.!?]$/', $last)
        && substr_count($last, ' ') <= 18;

      if ($looksLikeChips) {
        foreach (array_map('trim', explode('|', $last)) as $chip) {
          if ($chip !== '' && !preg_match('#^https?://#i', $chip)) {
            $chips[] = $chip;
          }
        }
        array_pop($preLines);
      }

      $summary = trim(implode(' ', $preLines));
    }
  }

  foreach ($parts as $part) {
    if ($part !== '') {
      $bullets[] = preg_replace('/\s+/', ' ', $part) ?? $part;
    }
  }

  if ($bullets === [] && $summary === '' && strpos($raw, "\n") !== false) {
    $lines = preg_split('/\R+/', $raw) ?: [];
    $bullets = array_values(array_filter(array_map('trim', $lines)));
  }

  return [
    'summary' => $summary,
    'chips' => $chips,
    'bullets' => $bullets,
    'website' => $website,
  ];
}

function exp_is_current(string $endDate): bool {
  $endDate = trim($endDate);
  return $endDate === '' || strcasecmp($endDate, 'present') === 0;
}

function exp_site_label(string $company): string {
  $company = strtolower($company);
  if (strpos($company, 'college') !== false || strpos($company, 'university') !== false) {
    return 'Visit school';
  }
  return 'Visit company';
}
?>

<main id="main-content" class="path-shell">
  <div class="path-shell__bg" aria-hidden="true"></div>

  <section class="container path-page">
    <header class="path-hero reveal-on-enter">
      <div class="path-hero__copy">
        <p class="path-hero__eyebrow">Professional path</p>
        <h1 class="path-hero__title">Experience</h1>
        <p class="path-hero__lede">
          From classroom instruction and campus IT to international customer success
          on enterprise commerce stacks - roles shaped around clear thinking,
          reliable systems, and helping people get unstuck.
        </p>
        <div class="path-hero__meta">
          <span class="path-hero__count"><?php echo (int) $expCount; ?> role<?php echo $expCount === 1 ? '' : 's'; ?></span>
          <a class="path-hero__jump" href="<?php echo appUrl('public/education.php'); ?>">View education</a>
          <a class="path-hero__jump path-hero__jump--muted" href="<?php echo appUrl('public/contact.php'); ?>">Get in touch</a>
        </div>
      </div>
    </header>

    <?php if (empty($experience)): ?>
      <div class="path-empty reveal-on-enter">
        <p>Experience details are being updated.</p>
      </div>
    <?php else: ?>
      <ol class="path-timeline">
        <?php foreach ($experience as $index => $item):
          $logo = exp_logo_file((string) ($item['company'] ?? ''));
          $logoPath = $logo !== '' ? __DIR__ . '/' . $logo : '';
          $hasLogo = $logo !== '' && is_file($logoPath);
          $start = trim((string) ($item['start_date'] ?? ''));
          $end = trim((string) ($item['end_date'] ?? ''));
          $current = exp_is_current($end);
          $when = $start . ' - ' . ($current ? 'Present' : $end);
          $parsed = exp_parse_details((string) ($item['description'] ?? ''));
          $website = $parsed['website'] !== '' ? $parsed['website'] : exp_company_website((string) ($item['company'] ?? ''));
          $initials = preg_replace('/[^A-Z]/', '', strtoupper(preg_replace('/\b(\w)\w*/', '$1', (string) ($item['company'] ?? 'EX'))));
          $initials = substr($initials !== '' ? $initials : 'EX', 0, 2);
          $hasDetail = $parsed['summary'] !== '' || !empty($parsed['bullets']);
        ?>
          <li class="path-card path-card--experience reveal-on-enter<?php echo $current ? ' path-card--current' : ''; ?>" style="--path-i: <?php echo (int) $index; ?>">
            <div class="path-card__rail" aria-hidden="true">
              <span class="path-card__dot"></span>
            </div>

            <div class="path-card__panel">
              <div class="path-card__top">
                <div class="path-card__brand">
                  <?php if ($hasLogo): ?>
                    <div class="path-card__logo">
                      <img
                        src="<?php echo htmlspecialchars(appUrl('public/' . $logo), ENT_QUOTES, 'UTF-8'); ?>?v=<?php echo (int) filemtime($logoPath); ?>"
                        alt=""
                        loading="lazy"
                        decoding="async"
                        width="64"
                        height="64"
                      >
                    </div>
                  <?php else: ?>
                    <div class="path-card__mark" aria-hidden="true"><?php echo htmlspecialchars($initials, ENT_QUOTES, 'UTF-8'); ?></div>
                  <?php endif; ?>

                  <div class="path-card__heading">
                    <div class="path-card__when-row">
                      <p class="path-card__when"><?php echo htmlspecialchars($when, ENT_QUOTES, 'UTF-8'); ?></p>
                      <?php if ($current): ?>
                        <span class="path-card__live"><i class="fa-solid fa-circle" aria-hidden="true"></i> Current</span>
                      <?php endif; ?>
                    </div>
                    <h2 class="path-card__title"><?php echo htmlspecialchars($item['role_name'] ?? 'Role', ENT_QUOTES, 'UTF-8'); ?></h2>
                    <p class="path-card__org">
                      <?php echo htmlspecialchars($item['company'] ?? '', ENT_QUOTES, 'UTF-8'); ?>
                      <?php if (!empty($item['location'])): ?>
                        <span class="path-card__sep">·</span>
                        <span class="path-card__field"><?php echo htmlspecialchars($item['location'], ENT_QUOTES, 'UTF-8'); ?></span>
                      <?php endif; ?>
                    </p>
                  </div>
                </div>

                <?php if ($website !== ''): ?>
                  <a
                    class="path-card__site"
                    href="<?php echo htmlspecialchars($website, ENT_QUOTES, 'UTF-8'); ?>"
                    target="_blank"
                    rel="noopener noreferrer"
                  >
                    <?php echo htmlspecialchars(exp_site_label((string) ($item['company'] ?? '')), ENT_QUOTES, 'UTF-8'); ?>
                    <i class="fa-solid fa-arrow-up-right-from-square" aria-hidden="true"></i>
                  </a>
                <?php endif; ?>
              </div>

              <?php if (!empty($parsed['chips'])): ?>
                <ul class="path-card__chips">
                  <?php foreach ($parsed['chips'] as $chip): ?>
                    <li><?php echo htmlspecialchars($chip, ENT_QUOTES, 'UTF-8'); ?></li>
                  <?php endforeach; ?>
                </ul>
              <?php endif; ?>

              <?php if ($hasDetail): ?>
                <div class="path-card__split">
                  <div class="path-card__detail">
                    <?php if ($parsed['summary'] !== ''): ?>
                      <div class="path-card__body">
                        <?php echo htmlspecialchars($parsed['summary'], ENT_QUOTES, 'UTF-8'); ?>
                      </div>
                    <?php endif; ?>

                    <?php if (!empty($parsed['bullets'])): ?>
                      <ul class="path-card__bullets">
                        <?php foreach ($parsed['bullets'] as $bullet): ?>
                          <li><?php echo htmlspecialchars($bullet, ENT_QUOTES, 'UTF-8'); ?></li>
                        <?php endforeach; ?>
                      </ul>
                    <?php endif; ?>
                  </div>

                  <?php if ($website !== ''): ?>
                    <aside class="path-card__aside">
                      <p class="path-card__aside-label">Institution website</p>
                      <p class="path-card__aside-name"><?php echo htmlspecialchars((string) ($item['company'] ?? ''), ENT_QUOTES, 'UTF-8'); ?></p>
                      <?php if (!empty($item['location'])): ?>
                        <p class="path-card__aside-meta"><?php echo htmlspecialchars((string) $item['location'], ENT_QUOTES, 'UTF-8'); ?></p>
                      <?php endif; ?>
                      <a
                        class="path-card__site path-card__site--block"
                        href="<?php echo htmlspecialchars($website, ENT_QUOTES, 'UTF-8'); ?>"
                        target="_blank"
                        rel="noopener noreferrer"
                      >
                        <?php echo htmlspecialchars(exp_site_label((string) ($item['company'] ?? '')), ENT_QUOTES, 'UTF-8'); ?>
                        <i class="fa-solid fa-arrow-up-right-from-square" aria-hidden="true"></i>
                      </a>
                      <p class="path-card__aside-host"><?php echo htmlspecialchars(preg_replace('#^https?://#', '', rtrim($website, '/')), ENT_QUOTES, 'UTF-8'); ?></p>
                    </aside>
                  <?php endif; ?>
                </div>
              <?php endif; ?>
            </div>
          </li>
        <?php endforeach; ?>
      </ol>
    <?php endif; ?>

    <aside class="path-cta reveal-on-enter">
      <div>
        <p class="path-cta__eyebrow">Next</p>
        <h2 class="path-cta__title">Want the academic side of the same story?</h2>
      </div>
      <div class="path-cta__actions">
        <a class="path-cta__btn path-cta__btn--primary" href="<?php echo appUrl('public/education.php'); ?>">Education</a>
        <a class="path-cta__btn" href="<?php echo appUrl('public/contact.php'); ?>">Get in touch</a>
      </div>
    </aside>
  </section>
</main>

<?php
require_once __DIR__ . '/footer.php';
?>
