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

$postTags = !empty($post['tags'])
  ? array_values(array_filter(array_map('trim', explode(',', $post['tags']))))
  : [];

require_once __DIR__ . '/header.php';
?>

<main id="main-content" class="container bp-page">
  <article id="blog-post" class="bp-article">

    <nav class="bp-breadcrumb" aria-label="Breadcrumb">
      <a href="<?php echo appUrl('public/index.php'); ?>">Home</a>
      <span aria-hidden="true">/</span>
      <a href="blog.php">Blog</a>
      <span aria-hidden="true">/</span>
      <span><?php echo htmlspecialchars($post['category'] ?: 'Article', ENT_QUOTES, 'UTF-8'); ?></span>
    </nav>

    <div class="bp-layout">
      <div class="bp-main">

        <header class="bp-header reveal-on-enter">
          <div class="bp-header__meta">
            <time datetime="<?php echo !empty($post['published_at']) ? date('c', strtotime($post['published_at'])) : ''; ?>">
              <?php echo !empty($post['published_at']) ? date('M d, Y', strtotime($post['published_at'])) : 'Unpublished'; ?>
            </time>
            <?php if (!empty($post['category'])): ?>
              <span class="bp-dot" aria-hidden="true">·</span>
              <span><?php echo htmlspecialchars($post['category'], ENT_QUOTES, 'UTF-8'); ?></span>
            <?php endif; ?>
            <?php if (!empty($post['reading_time'])): ?>
              <span class="bp-dot" aria-hidden="true">·</span>
              <span><?php echo (int) $post['reading_time']; ?> min read</span>
            <?php endif; ?>
          </div>

          <h1 class="bp-header__title"><?php echo htmlspecialchars($post['title'], ENT_QUOTES, 'UTF-8'); ?></h1>

          <?php if (!empty($post['excerpt'])): ?>
            <p class="bp-header__dek"><?php echo htmlspecialchars($post['excerpt'], ENT_QUOTES, 'UTF-8'); ?></p>
          <?php endif; ?>

          <div class="bp-header__byline">
            <div class="bp-author-chip">
              <span class="bp-author-chip__avatar" aria-hidden="true"><?php echo strtoupper(substr($post['author_name'] ?? 'T', 0, 1)); ?></span>
              <div>
                <strong><?php echo htmlspecialchars($post['author_name'] ?? '', ENT_QUOTES, 'UTF-8'); ?></strong>
                <div class="bp-header__stats">
                  <span><i class="fa-regular fa-eye" aria-hidden="true"></i> <?php echo (int) ($post['views'] ?? 0); ?> views</span>
                  <span><i class="fa-regular fa-comment" aria-hidden="true"></i> <?php echo $commentsCount; ?> <?php echo $commentsCount === 1 ? 'comment' : 'comments'; ?></span>
                </div>
              </div>
            </div>
          </div>
        </header>

        <?php if (!empty($post['featured_image'])): ?>
          <figure class="bp-hero-image img-skeleton-wrap reveal-on-enter">
            <img
              src="<?php echo htmlspecialchars(portfolioAssetUrl($post['featured_image']), ENT_QUOTES, 'UTF-8'); ?>?v=<?php echo time(); ?>"
              alt=""
              loading="eager"
              decoding="async"
              onload="var w=this.closest('.img-skeleton-wrap'); if(w) w.classList.add('loaded')"
            >
          </figure>
        <?php endif; ?>

        <div class="bp-prose reveal-on-enter">
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
            <div class="bp-widget">
              <div class="bp-widget__head">
                <h2>More articles</h2>
                <a href="blog.php">View all →</a>
              </div>
              <div class="bp-related">
                <?php foreach ($relatedPostsData as $relatedPost): ?>
                  <a class="bp-related__item" href="post.php?slug=<?php echo urlencode($relatedPost['slug']); ?>">
                    <?php if (!empty($relatedPost['featured_image'])): ?>
                      <span class="bp-related__thumb img-skeleton-wrap">
                        <img
                          src="<?php echo htmlspecialchars(portfolioAssetUrl($relatedPost['featured_image']), ENT_QUOTES, 'UTF-8'); ?>"
                          alt=""
                          loading="lazy"
                          decoding="async"
                          onload="var w=this.closest('.img-skeleton-wrap'); if(w) w.classList.add('loaded')"
                        >
                      </span>
                    <?php endif; ?>
                    <span class="bp-related__copy">
                      <?php if (!empty($relatedPost['category'])): ?>
                        <span class="bp-related__cat"><?php echo htmlspecialchars($relatedPost['category'], ENT_QUOTES, 'UTF-8'); ?></span>
                      <?php endif; ?>
                      <span class="bp-related__title"><?php echo htmlspecialchars($relatedPost['title'], ENT_QUOTES, 'UTF-8'); ?></span>
                      <span class="bp-related__meta">
                        <?php echo !empty($relatedPost['published_at']) ? date('M d, Y', strtotime($relatedPost['published_at'])) : ''; ?>
                        · <?php echo (int) ($relatedPost['comments_count'] ?? 0); ?> comments
                      </span>
                    </span>
                  </a>
                <?php endforeach; ?>
              </div>
            </div>
          <?php endif; ?>

          <div class="bp-widget bp-widget--author">
            <div class="bp-author-chip">
              <span class="bp-author-chip__avatar" aria-hidden="true"><?php echo strtoupper(substr($post['author_name'] ?? 'T', 0, 1)); ?></span>
              <div>
                <strong><?php echo htmlspecialchars($post['author_name'] ?? '', ENT_QUOTES, 'UTF-8'); ?></strong>
                <div class="bp-widget__role">Author &amp; researcher</div>
              </div>
            </div>
            <p>Writing on AI, systems engineering, and building durable software.</p>
            <a class="bp-widget__link" href="blog.php">Explore all posts →</a>
          </div>
        </div>
      </aside>
    </div>
  </article>
</main>

<script>
(function () {
  var btn = document.getElementById('copy-post-link');
  if (!btn) return;
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
})();
</script>

<?php
require_once __DIR__ . '/footer.php';
?>
