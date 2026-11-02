<?php
$activePage = 'education';
require_once __DIR__ . '/header.php';

$eduCount = count($education);

function edu_logo_file(string $institution): string {
  $institution = strtolower($institution);
  if (strpos($institution, 'debrecen') !== false) {
    return 'DE-cimer.jpg';
  }
  if (strpos($institution, 'kirinyaga') !== false) {
    return 'kyu_logo.png';
  }
  return '';
}

function edu_parse_details(string $raw): array {
  $raw = trim($raw);
  $website = '';
  if (preg_match('#https?://[^\s]+#i', $raw, $m)) {
    $website = rtrim($m[0], '.,);');
  }

  $lines = preg_split('/\R+/', $raw) ?: [];
  $lines = array_values(array_filter(array_map('trim', $lines), static function ($line) {
    return $line !== '' && stripos($line, 'website:') !== 0;
  }));

  $metaLine = $lines[0] ?? '';
  $chips = [];
  if ($metaLine !== '') {
    foreach (array_map('trim', explode('|', $metaLine)) as $chip) {
      if ($chip !== '' && !preg_match('#^https?://#i', $chip)) {
        $chips[] = $chip;
      }
    }
  }

  $bodyLines = array_slice($lines, 1);
  $body = trim(implode("\n", $bodyLines));

  return [
    'chips' => $chips,
    'website' => $website,
    'body' => $body,
  ];
}
?>

<main id="main-content" class="path-shell">
  <div class="path-shell__bg" aria-hidden="true"></div>

  <section class="container path-page">
    <header class="path-hero reveal-on-enter">
      <div class="path-hero__copy">
        <p class="path-hero__eyebrow">Academic path</p>
        <h1 class="path-hero__title">Education</h1>
        <p class="path-hero__lede">
          From undergraduate foundations to graduate research in computer science,
          with a clear through-line from theory to shipped systems.
        </p>
        <div class="path-hero__meta">
          <span class="path-hero__count"><?php echo (int) $eduCount; ?> credential<?php echo $eduCount === 1 ? '' : 's'; ?></span>
          <a class="path-hero__jump" href="<?php echo appUrl('public/experience.php'); ?>">View experience</a>
          <a class="path-hero__jump path-hero__jump--muted" href="<?php echo appUrl('public/projects.php'); ?>">See projects</a>
        </div>
      </div>
    </header>

    <?php if (empty($education)): ?>
      <div class="path-empty reveal-on-enter">
        <p>Education details are being updated.</p>
      </div>
    <?php else: ?>
      <ol class="path-timeline">
        <?php foreach ($education as $index => $item):
          $logo = edu_logo_file((string) ($item['institution'] ?? ''));
          $logoPath = $logo !== '' ? __DIR__ . '/' . $logo : '';
          $hasLogo = $logo !== '' && is_file($logoPath);
          $rawDetails = trim((string) ($item['education_details'] ?: ($item['description'] ?? '')));
          $parsed = edu_parse_details($rawDetails);
          $years = trim(($item['start_year'] ?? '') . ' - ' . ($item['end_year'] ?? ''), ' -');
          $initials = preg_replace('/[^A-Z]/', '', strtoupper(preg_replace('/\b(\w)\w*/', '$1', (string) ($item['institution'] ?? 'ED'))));
          $initials = substr($initials !== '' ? $initials : 'ED', 0, 2);
        ?>
          <li class="path-card reveal-on-enter" style="--path-i: <?php echo (int) $index; ?>">
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
                    <p class="path-card__when"><?php echo htmlspecialchars($years, ENT_QUOTES, 'UTF-8'); ?></p>
                    <h2 class="path-card__title"><?php echo htmlspecialchars($item['degree'] ?: 'Education', ENT_QUOTES, 'UTF-8'); ?></h2>
                    <p class="path-card__org">
                      <?php echo htmlspecialchars($item['institution'] ?? '', ENT_QUOTES, 'UTF-8'); ?>
                      <?php if (!empty($item['field_name'])): ?>
                        <span class="path-card__sep">·</span>
                        <span class="path-card__field"><?php echo htmlspecialchars($item['field_name'], ENT_QUOTES, 'UTF-8'); ?></span>
                      <?php endif; ?>
                    </p>
                  </div>
                </div>

                <?php if (!empty($parsed['website'])): ?>
                  <a
                    class="path-card__site"
                    href="<?php echo htmlspecialchars($parsed['website'], ENT_QUOTES, 'UTF-8'); ?>"
                    target="_blank"
                    rel="noopener noreferrer"
                  >
                    Visit school <i class="fa-solid fa-arrow-up-right-from-square" aria-hidden="true"></i>
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

              <?php if ($parsed['body'] !== ''): ?>
                <div class="path-card__body">
                  <?php echo nl2br(htmlspecialchars($parsed['body'], ENT_QUOTES, 'UTF-8')); ?>
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
        <h2 class="path-cta__title">See how the academic path shows up in work.</h2>
      </div>
      <div class="path-cta__actions">
        <a class="path-cta__btn path-cta__btn--primary" href="<?php echo appUrl('public/experience.php'); ?>">Experience</a>
        <a class="path-cta__btn" href="<?php echo appUrl('public/Sila_Kipng\'etich_Tanui_CV.pdf'); ?>" target="_blank" rel="noopener noreferrer">Download CV</a>
      </div>
    </aside>
  </section>
</main>

<?php
require_once __DIR__ . '/footer.php';
?>
