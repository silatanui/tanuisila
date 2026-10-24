<?php
$activePage = 'blog';
require_once __DIR__ . '/../config/config.php';

header('Cache-Control: private, no-cache, must-revalidate, max-age=0');
header('Pragma: no-cache');

$slug = isset($_GET['slug']) ? trim($_GET['slug']) : '';

if ($slug === '') {
  header('Location: blog.php');
  exit;
}

$stmt = $pdo->prepare('SELECT * FROM blog_posts WHERE slug = ? AND status = ?');
$stmt->execute([$slug, 'published']);
$post = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$post) {
  http_response_code(404);
  $activePage = 'blog';
  require_once __DIR__ . '/header.php';
  echo '<main class="container"><section class="section-row"><div class="section-content"><h2>Post Not Found</h2><p>Sorry, this blog post does not exist.</p><p><a href="blog.php">← Back to Blog</a></p></div></section></main>';
  require_once __DIR__ . '/footer.php';
  exit;
}

require_once __DIR__ . '/../includes/analytics.php';
trackPostViewDeviceSpecific($pdo, $post);

$commentFeedback = '';
$commentFeedbackType = '';
$formAuthorName = '';
$formAuthorEmail = '';
$formCommentText = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'post_comment') {
  if (empty($post['allow_comments'])) {
    $commentFeedback = 'Comments are closed for this article.';
    $commentFeedbackType = 'error';
  } else {
    $botField = trim($_POST['website_hp'] ?? '');
    $formAuthorName = trim($_POST['author_name'] ?? '');
    $formAuthorEmail = trim($_POST['author_email'] ?? '');
    $formCommentText = trim($_POST['comment_text'] ?? '');

    if ($botField !== '') {
      $commentFeedback = 'Your comment has been submitted.';
      $commentFeedbackType = 'success';
    } elseif ($formCommentText === '') {
      $commentFeedback = 'Please enter your comment message.';
      $commentFeedbackType = 'error';
    } elseif ($formAuthorEmail !== '' && !filter_var($formAuthorEmail, FILTER_VALIDATE_EMAIL)) {
      $commentFeedback = 'Please provide a valid email address or leave the email field blank.';
      $commentFeedbackType = 'error';
    } else {
      try {
        $finalAuthorName = $formAuthorName !== '' ? $formAuthorName : 'Anonymous';
        $insertStmt = $pdo->prepare('INSERT INTO blog_comments (post_id, author_name, author_email, comment_text, status, created_at) VALUES (?, ?, ?, ?, ?, NOW())');
        $insertStmt->execute([$post['id'], $finalAuthorName, $formAuthorEmail, $formCommentText, 'approved']);
        $commentFeedback = 'Thank you! Your comment has been posted successfully.';
        $commentFeedbackType = 'success';
        $formAuthorName = '';
        $formAuthorEmail = '';
        $formCommentText = '';
      } catch (Throwable $e) {
        $commentFeedback = 'An unexpected error occurred while posting your comment. Please try again.';
        $commentFeedbackType = 'error';
      }
    }
  }
}

$commentsStmt = $pdo->prepare('SELECT * FROM blog_comments WHERE post_id = ? AND status = ? ORDER BY created_at ASC');
$commentsStmt->execute([$post['id'], 'approved']);
$comments = $commentsStmt->fetchAll(PDO::FETCH_ASSOC);
$commentsCount = count($comments);

$relatedPosts = $pdo->prepare("
  SELECT p.*,
         (SELECT COUNT(*) FROM blog_comments c WHERE c.post_id = p.id AND c.status = 'approved') AS comments_count
  FROM blog_posts p
  WHERE p.status = 'published' AND p.id != ?
  ORDER BY p.published_at DESC, p.id DESC
  LIMIT 5
");
$relatedPosts->execute([$post['id']]);
$relatedPostsData = $relatedPosts->fetchAll(PDO::FETCH_ASSOC);

$prevStmt = $pdo->prepare("
  SELECT slug, title FROM blog_posts
  WHERE status = 'published' AND published_at < ?
  ORDER BY published_at DESC LIMIT 1
");
$prevStmt->execute([$post['published_at'] ?? $post['created_at'] ?? null]);
$prevPost = $prevStmt->fetch(PDO::FETCH_ASSOC) ?: null;

$nextStmt = $pdo->prepare("
  SELECT slug, title FROM blog_posts
  WHERE status = 'published' AND published_at > ?
  ORDER BY published_at ASC LIMIT 1
");
$nextStmt->execute([$post['published_at'] ?? $post['created_at'] ?? null]);
$nextPost = $nextStmt->fetch(PDO::FETCH_ASSOC) ?: null;

$postTags = !empty($post['tags'])
  ? array_values(array_filter(array_map('trim', explode(',', $post['tags']))))
  : [];

$authorInitial = strtoupper(substr($post['author_name'] ?? 'T', 0, 1));
$publishedLabel = !empty($post['published_at']) ? date('F j, Y', strtotime($post['published_at'])) : '';

require_once __DIR__ . '/header.php';

// URL helpers live in header.php — resolve after include
$heroImage = !empty($post['featured_image']) ? portfolioAssetUrl($post['featured_image']) : '';
$authorPhoto = file_exists(__DIR__ . '/profile.jpg') ? appUrl('public/profile.jpg') : '';
?>

<main id="main-content" class="bp-shell">
  <article id="blog-post">

    <header class="bp-banner<?php echo $heroImage ? '' : ' bp-banner--placeholder'; ?>">
      <div class="bp-banner__media<?php echo $heroImage ? '' : ' bp-banner__media--ph'; ?>" aria-hidden="true">
        <?php if ($heroImage): ?>
          <img
            src="<?php echo htmlspecialchars($heroImage, ENT_QUOTES, 'UTF-8'); ?>?v=<?php echo time(); ?>"
            alt="<?php echo htmlspecialchars($post['title'] ?? '', ENT_QUOTES, 'UTF-8'); ?>"
            fetchpriority="high"
            decoding="async"
          >
        <?php else: ?>
          <?php echo media_placeholder($post['title'] ?? '', 'media-ph--banner'); ?>
        <?php endif; ?>
      </div>
      <div class="bp-banner__veil" aria-hidden="true"></div>

      <?php if ($prevPost): ?>
        <a class="bp-banner__nav bp-banner__nav--prev" href="post.php?slug=<?php echo urlencode($prevPost['slug']); ?>" title="<?php echo htmlspecialchars($prevPost['title'], ENT_QUOTES, 'UTF-8'); ?>">
          <i class="fa-solid fa-chevron-left" aria-hidden="true"></i>
          <span class="visually-hidden">Previous article</span>
        </a>
      <?php endif; ?>
      <?php if ($nextPost): ?>
        <a class="bp-banner__nav bp-banner__nav--next" href="post.php?slug=<?php echo urlencode($nextPost['slug']); ?>" title="<?php echo htmlspecialchars($nextPost['title'], ENT_QUOTES, 'UTF-8'); ?>">
          <i class="fa-solid fa-chevron-right" aria-hidden="true"></i>
          <span class="visually-hidden">Next article</span>
        </a>
      <?php endif; ?>

      <div class="bp-banner__inner container">
        <h1 class="bp-banner__title"><?php echo htmlspecialchars($post['title'], ENT_QUOTES, 'UTF-8'); ?></h1>
        <div class="bp-banner__byline">
          <span class="bp-banner__avatar" aria-hidden="true">
            <?php if ($authorPhoto): ?>
              <img src="<?php echo htmlspecialchars($authorPhoto, ENT_QUOTES, 'UTF-8'); ?>?v=<?php echo @filemtime(__DIR__ . '/profile.jpg'); ?>" alt="">
            <?php else: ?>
              <?php echo htmlspecialchars($authorInitial, ENT_QUOTES, 'UTF-8'); ?>
            <?php endif; ?>
          </span>
          <div class="bp-banner__byline-text">
            <span>by <?php echo htmlspecialchars($post['author_name'] ?? '', ENT_QUOTES, 'UTF-8'); ?></span>
            <?php if ($publishedLabel !== ''): ?>
              <time datetime="<?php echo date('c', strtotime($post['published_at'])); ?>"><?php echo $publishedLabel; ?></time>
            <?php endif; ?>
          </div>
        </div>
      </div>
    </header>

    <div class="bp-crumb-bar">
      <div class="container bp-crumb-bar__inner" aria-label="Breadcrumb">
        <a href="<?php echo appUrl('public/index.php'); ?>">Home</a>
        <span aria-hidden="true">/</span>
        <a href="blog.php">Blog</a>
        <span aria-hidden="true">/</span>
        <span><?php echo htmlspecialchars($post['category'] ?: $post['title'], ENT_QUOTES, 'UTF-8'); ?></span>
      </div>
    </div>

    <div class="container bp-page">
      <div class="bp-layout">
        <div class="bp-main">

          <?php if (!empty($post['excerpt'])): ?>
            <p class="bp-dek"><?php echo htmlspecialchars($post['excerpt'], ENT_QUOTES, 'UTF-8'); ?></p>
          <?php endif; ?>

          <div class="bp-prose">
            <?php echo $post['content']; ?>
          </div>

          <?php if (!empty($postTags)): ?>
            <div class="bp-tags">
              <span class="bp-tags__label">Tagged</span>
              <ul>
                <?php foreach ($postTags as $tag): ?>
                  <li>#<?php echo htmlspecialchars($tag, ENT_QUOTES, 'UTF-8'); ?></li>
                <?php endforeach; ?>
              </ul>
            </div>
          <?php endif; ?>

          <div class="bp-footer-bar">
            <a class="bp-back" href="blog.php"><i class="fa-solid fa-arrow-left" aria-hidden="true"></i> Back to Blog</a>
            <button type="button" id="copy-post-link" class="btn bp-copy">
              <i class="fa-solid fa-link" aria-hidden="true"></i> Copy link
            </button>
          </div>

          <section id="discussion" class="bp-discussion">
            <div class="bp-discussion__head">
              <h2>
                <i class="fa-regular fa-comments" aria-hidden="true"></i>
                Discussion (<?php echo $commentsCount; ?>)
              </h2>
              <?php if (!empty($post['allow_comments'])): ?>
                <a href="#comment-form">Leave a response ↓</a>
              <?php endif; ?>
            </div>

            <?php if ($commentFeedback !== ''): ?>
              <div class="bp-alert bp-alert--<?php echo $commentFeedbackType === 'success' ? 'success' : 'error'; ?>">
                <i class="fa-solid <?php echo $commentFeedbackType === 'success' ? 'fa-circle-check' : 'fa-circle-exclamation'; ?>" aria-hidden="true"></i>
                <?php echo htmlspecialchars($commentFeedback, ENT_QUOTES, 'UTF-8'); ?>
              </div>
            <?php endif; ?>

            <div class="bp-comments">
              <?php if ($commentsCount === 0): ?>
                <div class="bp-comments__empty">
                  <i class="fa-regular fa-comment-dots" aria-hidden="true"></i>
                  <strong>No comments yet</strong>
                  <span>Be the first to share your thoughts.</span>
                </div>
              <?php else: ?>
                <?php foreach ($comments as $comment):
                  $initial = strtoupper(substr(trim($comment['author_name']), 0, 1));
                  $commentDate = date('M d, Y \a\t g:i A', strtotime($comment['created_at']));
                ?>
                  <div class="bp-comment">
                    <div class="bp-comment__avatar" aria-hidden="true"><?php echo htmlspecialchars($initial, ENT_QUOTES, 'UTF-8'); ?></div>
                    <div class="bp-comment__body">
                      <div class="bp-comment__meta">
                        <strong><?php echo htmlspecialchars($comment['author_name'], ENT_QUOTES, 'UTF-8'); ?></strong>
                        <time datetime="<?php echo date('c', strtotime($comment['created_at'])); ?>"><?php echo $commentDate; ?></time>
                      </div>
                      <div class="bp-comment__text"><?php echo nl2br(htmlspecialchars($comment['comment_text'], ENT_QUOTES, 'UTF-8')); ?></div>
                    </div>
                  </div>
                <?php endforeach; ?>
              <?php endif; ?>
            </div>

            <?php if (!empty($post['allow_comments'])): ?>
              <div id="comment-form" class="bp-form">
                <h3>Leave a comment</h3>
                <p class="bp-form__hint">Email stays private. Name and email are optional.</p>

                <form action="post.php?slug=<?php echo urlencode($post['slug']); ?>#discussion" method="POST">
                  <input type="hidden" name="action" value="post_comment">
                  <div class="bp-honeypot" aria-hidden="true">
                    <label for="website_hp">Leave blank</label>
                    <input type="text" id="website_hp" name="website_hp" value="" tabindex="-1" autocomplete="off">
                  </div>

                  <div class="bp-form__row">
                    <div class="bp-field">
                      <label for="author_name">Name <span>(optional)</span></label>
                      <input type="text" id="author_name" name="author_name" placeholder="Alex Doe" value="<?php echo htmlspecialchars($formAuthorName, ENT_QUOTES, 'UTF-8'); ?>">
                    </div>
                    <div class="bp-field">
                      <label for="author_email">Email <span>(optional)</span></label>
                      <input type="email" id="author_email" name="author_email" placeholder="alex@example.com" value="<?php echo htmlspecialchars($formAuthorEmail, ENT_QUOTES, 'UTF-8'); ?>">
                    </div>
                  </div>

                  <div class="bp-field">
                    <label for="comment_text">Comment *</label>
                    <textarea id="comment_text" name="comment_text" required rows="5" placeholder="Share your thoughts…"><?php echo htmlspecialchars($formCommentText, ENT_QUOTES, 'UTF-8'); ?></textarea>
                  </div>

                  <div class="bp-form__actions">
                    <button type="submit" class="btn primary">
                      <i class="fa-solid fa-paper-plane" aria-hidden="true"></i> Submit comment
                    </button>
                    <span class="bp-form__note"><i class="fa-solid fa-shield-halved" aria-hidden="true"></i> Keep it constructive</span>
                  </div>
                </form>
              </div>
            <?php else: ?>
              <div class="bp-closed">
                <i class="fa-solid fa-lock" aria-hidden="true"></i>
                Comments are closed for this article.
              </div>
            <?php endif; ?>
          </section>
        </div>

        <aside class="bp-sidebar" aria-label="Related reading">
          <div class="bp-sidebar__sticky">
            <?php if (!empty($relatedPostsData)): ?>
              <div class="bp-related-panel">
                <h2 class="bp-related-panel__title">More articles</h2>
                <div class="bp-related-stack">
                  <?php foreach ($relatedPostsData as $relatedPost):
                    $relExcerpt = trim((string) ($relatedPost['excerpt'] ?? ''));
                    if ($relExcerpt === '') {
                      $relExcerpt = strip_tags((string) ($relatedPost['content'] ?? ''));
                    }
                    if (strlen($relExcerpt) > 110) {
                      $relExcerpt = substr($relExcerpt, 0, 107) . '…';
                    }
                  ?>
                    <article class="bp-related-card">
                      <h3 class="bp-related-card__title">
                        <a href="post.php?slug=<?php echo urlencode($relatedPost['slug']); ?>">
                          <?php echo htmlspecialchars($relatedPost['title'], ENT_QUOTES, 'UTF-8'); ?>
                        </a>
                      </h3>
                      <a class="bp-related-card__thumb<?php echo !empty($relatedPost['featured_image']) ? ' img-skeleton-wrap' : ' bp-related-card__thumb--empty'; ?>" href="post.php?slug=<?php echo urlencode($relatedPost['slug']); ?>" tabindex="-1" aria-hidden="true">
                        <?php if (!empty($relatedPost['featured_image'])): ?>
                          <img
                            src="<?php echo htmlspecialchars(portfolioAssetUrl($relatedPost['featured_image']), ENT_QUOTES, 'UTF-8'); ?>"
                            alt="<?php echo htmlspecialchars($relatedPost['title'] ?? '', ENT_QUOTES, 'UTF-8'); ?>"
                            loading="lazy"
                            decoding="async"
                            onload="var w=this.closest('.img-skeleton-wrap'); if(w) w.classList.add('loaded')"
                          >
                        <?php else: ?>
                          <?php echo media_placeholder($relatedPost['title'] ?? '', 'media-ph--thumb'); ?>
                        <?php endif; ?>
                      </a>
                      <?php if ($relExcerpt !== ''): ?>
                        <p class="bp-related-card__excerpt"><?php echo htmlspecialchars($relExcerpt, ENT_QUOTES, 'UTF-8'); ?></p>
                      <?php endif; ?>
                    </article>
                  <?php endforeach; ?>
                </div>
                <a class="bp-related-panel__all" href="blog.php">View all posts →</a>
              </div>
            <?php endif; ?>
          </div>
        </aside>
      </div>
    </div>
  </article>
</main>

<script>
(function () {
  var btn = document.getElementById('copy-post-link');
  if (btn) {
    btn.addEventListener('click', async function () {
      try {
        await navigator.clipboard.writeText(window.location.href);
        btn.innerHTML = '<i class="fa-solid fa-check" aria-hidden="true"></i> Copied';
        setTimeout(function () {
          btn.innerHTML = '<i class="fa-solid fa-link" aria-hidden="true"></i> Copy link';
        }, 1600);
      } catch (e) {
        prompt('Copy this link:', window.location.href);
      }
    });
  }

  function guessLabel(pre) {
    var explicit = pre.getAttribute('data-label');
    if (explicit) return explicit;
    var text = (pre.textContent || '').trim();
    if (/^npx |^npm |^rea |^bun |^bunx /m.test(text)) return 'Terminal';
    if (/^Understand |^Use REA |^Ask |^Clone /m.test(text)) return 'Agent prompt';
    if (/^\{[\s\S]*\}$/.test(text) || /"type"\s*:/.test(text)) return 'JSON';
    if (/^(import |const |function |export |export type )/m.test(text)) return 'TypeScript';
    return 'Code';
  }

  async function copyText(text, button) {
    try {
      await navigator.clipboard.writeText(text);
    } catch (err) {
      var area = document.createElement('textarea');
      area.value = text;
      area.setAttribute('readonly', '');
      area.style.position = 'fixed';
      area.style.left = '-9999px';
      document.body.appendChild(area);
      area.select();
      try { document.execCommand('copy'); } catch (e) {}
      document.body.removeChild(area);
    }
    var original = button.innerHTML;
    button.classList.add('is-copied');
    button.innerHTML = '<i class="fa-solid fa-check" aria-hidden="true"></i> Copied';
    setTimeout(function () {
      button.classList.remove('is-copied');
      button.innerHTML = original;
    }, 1600);
  }

  var prose = document.querySelector('.bp-prose');
  if (!prose) return;
  prose.querySelectorAll('pre').forEach(function (pre) {
    if (pre.closest('.bp-codeblock')) return;
    var wrap = document.createElement('div');
    wrap.className = 'bp-codeblock';
    var bar = document.createElement('div');
    bar.className = 'bp-codeblock__bar';
    var label = document.createElement('span');
    label.className = 'bp-codeblock__label';
    label.textContent = guessLabel(pre);
    var copyBtn = document.createElement('button');
    copyBtn.type = 'button';
    copyBtn.className = 'bp-codeblock__copy';
    copyBtn.setAttribute('aria-label', 'Copy code');
    copyBtn.innerHTML = '<i class="fa-regular fa-copy" aria-hidden="true"></i> Copy';
    copyBtn.addEventListener('click', function () {
      var code = pre.querySelector('code');
      var text = (code ? code.textContent : pre.textContent) || '';
      copyText(text.replace(/\n$/, ''), copyBtn);
    });
    bar.appendChild(label);
    bar.appendChild(copyBtn);
    pre.parentNode.insertBefore(wrap, pre);
    wrap.appendChild(bar);
    wrap.appendChild(pre);
  });
})();
</script>

<?php
require_once __DIR__ . '/footer.php';
?>
