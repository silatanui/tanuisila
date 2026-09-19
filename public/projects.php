<?php
$activePage = 'projects';
require_once __DIR__ . '/header.php';
?>

<main class="container">
  <section id="projects" class="section-row" style="border-bottom:none; padding-bottom: 20px;">
    <div class="section-label">Projects</div>
    <div class="section-content" style="width: 100%;">
      
      <div style="display: flex; justify-content: space-between; align-items: flex-start; flex-wrap: wrap; gap: 16px; margin-bottom: 30px;">
        <div>
          <h2 style="margin: 0 0 8px; font-size: clamp(1.8rem, 3vw, 2.4rem); font-weight: 800; letter-spacing: -0.04em; color: var(--text);">
            Engineering Work &amp; Software Systems
          </h2>
          <p style="margin: 0; color: var(--muted); font-size: 1rem; max-width: 680px; line-height: 1.5;">
            Production software, artificial intelligence applications, and high-impact digital tools engineered for real-world utility.
          </p>
        </div>
      </div>

      <!-- ═══════════════════════════════════════════════════════ -->
      <!-- DYNAMIC PROJECTS LIST                                   -->
      <!-- ═══════════════════════════════════════════════════════ -->
      <?php if (empty($projects)): ?>
        <div style="padding: 40px; background: var(--soft-bg); border: 1px solid var(--light-gray); text-align: center; color: var(--muted);">
          <p style="margin: 0; font-size: 1.1rem; font-weight: 600;">Projects are currently being updated. Check back shortly.</p>
        </div>
      <?php else: ?>
        <div style="display: flex; flex-direction: column; gap: 40px;">
          <?php foreach ($projects as $index => $p): ?>
            <?php
              // Resolve Image URL
              $pImg = '';
              if (!empty($p['image'])) {
                  $rawImg = trim($p['image']);
                  if (preg_match('#^https?://#i', $rawImg)) {
                      $pImg = $rawImg;
                  } elseif (strpos($rawImg, '/') === 0) {
                      $pImg = portfolioAssetUrl($rawImg);
                  } elseif (file_exists(__DIR__ . '/../assets/images/projects/' . $rawImg)) {
                      $pImg = appUrl('assets/images/projects/' . $rawImg);
                  } elseif (file_exists(__DIR__ . '/' . $rawImg)) {
                      $pImg = appUrl('public/' . $rawImg);
                  } else {
                      $pImg = appUrl('public/' . $rawImg);
                  }
              } else {
                  // Fallback to cv_studio.png if title is CV Studio
                  if (stripos($p['title'], 'cv') !== false) {
                      $pImg = appUrl('public/cv_studio.png');
                  }
              }

              // Parse Tags
              $tagList = [];
              if (!empty($p['tags'])) {
                  $tagList = array_map('trim', explode(',', $p['tags']));
              }
            ?>

            <article class="featured-project-card" style="background: var(--soft-bg); border: 1px solid var(--light-gray); padding: 36px; position: relative;">
              
              <div class="featured-project-header" style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 12px; margin-bottom: 24px; border-bottom: 1px solid var(--light-gray); padding-bottom: 16px;">
                <div style="display: flex; align-items: center; gap: 12px;">
                  <span style="display: inline-flex; align-items: center; gap: 6px; padding: 4px 10px; background: rgba(34, 197, 94, 0.1); border: 1px solid rgba(34, 197, 94, 0.3); font-size: 0.7rem; font-weight: 800; letter-spacing: 0.08em; text-transform: uppercase; color: #15803d;">
                    <span style="width: 6px; height: 6px; background: #22c55e; border-radius: 50%; display: inline-block;"></span>
                    Active Production
                  </span>
                  <span style="font-size: 0.75rem; font-weight: 700; color: var(--accent-muted); letter-spacing: 0.1em; text-transform: uppercase;">
                    Featured Project #<?php echo str_pad((string)($index + 1), 2, '0', STR_PAD_LEFT); ?>
                  </span>
                </div>
                
                <?php if (!empty($p['url'])): ?>
                  <a class="btn primary" href="<?php echo htmlspecialchars($p['url'], ENT_QUOTES, 'UTF-8'); ?>" target="_blank" rel="noopener noreferrer" style="font-size: 0.8rem; padding: 10px 20px;">
                    <i class="fa-solid fa-arrow-up-right-from-square" style="margin-right: 6px;"></i> Launch Live Application
                  </a>
                <?php endif; ?>
              </div>

              <div class="featured-project-grid" style="display: grid; grid-template-columns: <?php echo !empty($pImg) ? '1.15fr 0.85fr' : '1fr'; ?>; gap: 36px; align-items: start;">
                
                <!-- LEFT: Screenshot mockup frame if image exists -->
                <?php if (!empty($pImg)): ?>
                  <div class="project-mockup-wrap" style="position: relative;">
                    <div class="browser-mockup" style="border: 1px solid var(--light-gray); background: #ffffff; box-shadow: 0 12px 32px rgba(15, 23, 42, 0.06); overflow: hidden;">
                      
                      <!-- Browser Titlebar -->
                      <div style="display: flex; align-items: center; justify-content: space-between; padding: 10px 14px; background: #0f172a; color: #fff; border-bottom: 1px solid rgba(255,255,255,0.1); font-size: 0.72rem;">
                        <div style="display: flex; gap: 6px; align-items: center;">
                          <span style="width: 10px; height: 10px; border-radius: 50%; background: #ef4444; display: inline-block;"></span>
                          <span style="width: 10px; height: 10px; border-radius: 50%; background: #f59e0b; display: inline-block;"></span>
                          <span style="width: 10px; height: 10px; border-radius: 50%; background: #10b981; display: inline-block;"></span>
                        </div>
                        <div style="font-family: monospace; color: rgba(255,255,255,0.7); background: rgba(255,255,255,0.08); padding: 2px 16px; font-size: 0.68rem; max-width: 280px; overflow: hidden; text-overflow: ellipsis; white-space: nowrap;">
                          <?php echo htmlspecialchars($p['url'] ?: 'tanuisila.dev', ENT_QUOTES, 'UTF-8'); ?>
                        </div>
                        <div style="opacity: 0.5;"><i class="fa-solid fa-lock"></i></div>
                      </div>

                      <!-- Project Screenshot Image with Skeleton Wrapper -->
                      <a href="<?php echo htmlspecialchars($p['url'] ?: '#', ENT_QUOTES, 'UTF-8'); ?>" target="_blank" rel="noopener noreferrer" style="display: block; overflow: hidden;" title="Click to view live project">
                        <div class="img-skeleton-wrap" style="min-height: 240px; aspect-ratio: 16 / 9;">
                          <img 
                            src="<?php echo htmlspecialchars($pImg, ENT_QUOTES, 'UTF-8'); ?>?v=<?php echo time(); ?>" 
                            alt="<?php echo htmlspecialchars($p['title'], ENT_QUOTES, 'UTF-8'); ?>" 
                            loading="lazy"
                            decoding="async"
                            style="width: 100%; height: auto; display: block; object-fit: cover; transition: transform 0.3s ease, opacity 0.5s ease-out, filter 0.5s ease-out;"
                            onload="this.closest('.img-skeleton-wrap').classList.add('loaded')"
                            onmouseover="this.style.transform='scale(1.015)'"
                            onmouseout="this.style.transform='scale(1)'"
                          >
                        </div>
                      </a>
                    </div>
                  </div>
                <?php endif; ?>

                <!-- RIGHT: Project Details & Tech Specs -->
                <div class="project-details-content" style="display: flex; flex-direction: column; gap: 20px;">
                  <div>
                    <h3 style="margin: 0 0 10px; font-size: 1.6rem; font-weight: 800; color: var(--text); letter-spacing: -0.03em;">
                      <?php echo htmlspecialchars($p['title'], ENT_QUOTES, 'UTF-8'); ?>
                    </h3>
                    <p style="margin: 0; font-size: 0.96rem; line-height: 1.6; color: var(--muted);">
                      <?php echo nl2br(htmlspecialchars($p['description'], ENT_QUOTES, 'UTF-8')); ?>
                    </p>
                  </div>

                  <!-- Tech Stack Tags -->
                  <?php if (!empty($tagList)): ?>
                    <div>
                      <h4 style="margin: 0 0 8px; font-size: 0.75rem; font-weight: 800; letter-spacing: 0.12em; text-transform: uppercase; color: var(--accent-muted);">
                        Technologies &amp; Architecture
                      </h4>
                      <div style="display: flex; flex-wrap: wrap; gap: 6px;">
                        <?php foreach ($tagList as $tag): ?>
                          <span class="tech-tag"><?php echo htmlspecialchars($tag, ENT_QUOTES, 'UTF-8'); ?></span>
                        <?php endforeach; ?>
                      </div>
                    </div>
                  <?php endif; ?>

                  <!-- Launch CTA -->
                  <?php if (!empty($p['url'])): ?>
                    <div style="padding-top: 10px; border-top: 1px solid var(--light-gray); display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 12px;">
                      <a class="btn primary" href="<?php echo htmlspecialchars($p['url'], ENT_QUOTES, 'UTF-8'); ?>" target="_blank" rel="noopener noreferrer" style="display: inline-flex; align-items: center; gap: 8px;">
                        Open Live Application <i class="fa-solid fa-arrow-right"></i>
                      </a>
                      <span style="font-size: 0.78rem; color: var(--muted); font-family: monospace;">
                        <?php echo htmlspecialchars(preg_replace('#^https?://#', '', rtrim($p['url'], '/')), ENT_QUOTES, 'UTF-8'); ?>
                      </span>
                    </div>
                  <?php endif; ?>

                </div>

              </div>

            </article>
          <?php endforeach; ?>
        </div>
      <?php endif; ?>

      <!-- ═══════════════════════════════════════════════════════ -->
      <!-- UPCOMING PRODUCTION PIPELINE BANNER                     -->
      <!-- ═══════════════════════════════════════════════════════ -->
      <div class="pipeline-announcement-card" style="background: #0f172a; color: #fff; padding: 32px; border: 1px solid rgba(255,255,255,0.1); display: grid; grid-template-columns: auto 1fr auto; gap: 24px; align-items: center; margin-top: 40px;">
        <div style="width: 52px; height: 52px; background: rgba(234, 88, 12, 0.15); border: 1px solid var(--accent); display: flex; align-items: center; justify-content: center; font-size: 1.4rem; color: var(--accent); flex-shrink: 0;">
          <i class="fa-solid fa-layer-group"></i>
        </div>
        
        <div>
          <h3 style="margin: 0 0 6px; font-size: 1.15rem; font-weight: 800; letter-spacing: -0.02em; color: #fff;">
            High-Impact Projects Pipeline
          </h3>
          <p style="margin: 0; font-size: 0.88rem; color: rgba(255,255,255,0.7); line-height: 1.45; max-width: 680px;">
            I am actively designing, building, and deploying new production-grade applications, AI agents, and software tools - releasing at least one new project weekly. Stay tuned for upcoming deployments.
          </p>
        </div>

        <div>
          <a class="btn" href="<?php echo appUrl('public/contact.php'); ?>" style="background: #fff; color: #0f172a; border: 1px solid #fff; white-space: nowrap; font-size: 0.78rem;">
            Propose a Project →
          </a>
        </div>
      </div>

    </div>
  </section>
</main>

<style>
.tech-tag {
  display: inline-block;
  padding: 4px 10px;
  background: var(--soft-bg);
  border: 1px solid var(--light-gray);
  font-size: 0.74rem;
  font-weight: 700;
  color: var(--text);
  letter-spacing: 0.02em;
}

@media (max-width: 960px) {
  .featured-project-grid {
    grid-template-columns: 1fr !important;
    gap: 24px !important;
  }
  
  .featured-project-card {
    padding: 24px !important;
  }

  .pipeline-announcement-card {
    grid-template-columns: 1fr !important;
    text-align: left;
    gap: 16px !important;
  }
}
</style>

<?php
require_once __DIR__ . '/footer.php';
?>
