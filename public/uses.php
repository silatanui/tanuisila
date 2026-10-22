<?php
$activePage = 'uses';
require_once __DIR__ . '/header.php';

$stacks = [
  ['Hardware', 'Laptop workstation, dual-monitor layout, mechanical keyboard for long coding sessions.'],
  ['Editor', 'Cursor / VS Code with focused PHP, Python, and systems workflows.'],
  ['Languages', 'PHP, JavaScript, Python, SQL — with C/C++ when systems demand it.'],
  ['Backend', 'Laravel-ready PHP, MySQL/MariaDB, REST APIs, and careful schema design.'],
  ['AI stack', 'OpenAI APIs, local experimentation, evaluation loops, and prompt tooling.'],
  ['Ops', 'XAMPP locally, cPanel/Git deploy on production, GitHub as source of truth.'],
];
?>
<main id="main-content" class="container">
  <section class="section-row" style="border-bottom:none;">
    <div class="section-label">Uses</div>
    <div class="section-content">
      <div class="page-hero-mini">
        <h2>Tools I actually use</h2>
        <p>Not an affiliate dump — the hardware, editors, and stacks that show up in real projects.</p>
      </div>
      <div class="feature-grid">
        <?php foreach ($stacks as [$title, $copy]): ?>
          <article class="feature-tile reveal-on-enter">
            <div class="feature-tile__icon"><i class="fa-solid fa-wrench"></i></div>
            <h3><?php echo htmlspecialchars($title); ?></h3>
            <p><?php echo htmlspecialchars($copy); ?></p>
          </article>
        <?php endforeach; ?>
      </div>
    </div>
  </section>
</main>
<?php require_once __DIR__ . '/footer.php'; ?>
