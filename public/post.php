<?php
$activePage = 'blog';
require_once __DIR__ . '/../config/config.php';

// Get post slug from URL
$slug = isset($_GET['slug']) ? trim($_GET['slug']) : '';

if (empty($slug)) {
    header('Location: blog.php');
    exit;
}

// Fetch the blog post by slug
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

// Handle Comment Submission
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

        // Anti-spam honeypot check
        if (!empty($botField)) {
            // Silently reject bot submission
            $commentFeedback = 'Your comment has been submitted.';
            $commentFeedbackType = 'success';
        } elseif (empty($formCommentText)) {
            $commentFeedback = 'Please enter your comment message.';
            $commentFeedbackType = 'error';
        } elseif (!empty($formAuthorEmail) && !filter_var($formAuthorEmail, FILTER_VALIDATE_EMAIL)) {
            $commentFeedback = 'Please provide a valid email address or leave the email field blank.';
            $commentFeedbackType = 'error';
        } else {
            try {
                // If author name is empty, default to 'Anonymous'
                $finalAuthorName = $formAuthorName !== '' ? $formAuthorName : 'Anonymous';
                $finalAuthorEmail = $formAuthorEmail;

                $insertStmt = $pdo->prepare('INSERT INTO blog_comments (post_id, author_name, author_email, comment_text, status, created_at) VALUES (?, ?, ?, ?, ?, NOW())');
                $insertStmt->execute([$post['id'], $finalAuthorName, $finalAuthorEmail, $formCommentText, 'approved']);
                
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

// Fetch Approved Comments for this Post
$commentsStmt = $pdo->prepare('SELECT * FROM blog_comments WHERE post_id = ? AND status = ? ORDER BY created_at ASC');
$commentsStmt->execute([$post['id'], 'approved']);
$comments = $commentsStmt->fetchAll(PDO::FETCH_ASSOC);
$commentsCount = count($comments);

require_once __DIR__ . '/header.php';
?>

<style>
  /* ═══════════════════════════════════════════════════════════ */
  /* ENFORCE OUTFIT FONT FOR ARTICLE & BLOG POST VIEW          */
  /* ═══════════════════════════════════════════════════════════ */
  #blog-post, 
  #blog-post *,
  .blog-article-content,
  .blog-article-content * {
    font-family: 'Outfit', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif !important;
  }

  .blog-article-content {
    max-width: 100%;
    margin-bottom: 40px;
    font-size: 1.05rem;
    line-height: 1.8;
    color: var(--text, #0f172a);
    word-break: break-word;
  }

  .blog-article-content h1,
  .blog-article-content h2,
  .blog-article-content h3,
  .blog-article-content h4,
  .blog-article-content h5,
  .blog-article-content h6 {
    font-family: 'Outfit', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif !important;
    font-weight: 800;
    color: var(--text, #0f172a);
    margin-top: 2rem;
    margin-bottom: 0.8rem;
    line-height: 1.28;
    letter-spacing: -0.02em;
  }

  .blog-article-content h1 { font-size: 1.9rem; }
  .blog-article-content h2 { font-size: 1.55rem; }
  .blog-article-content h3 { font-size: 1.3rem; }
  .blog-article-content h4 { font-size: 1.15rem; }

  .blog-article-content p {
    font-family: 'Outfit', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif !important;
    margin-bottom: 1.4rem;
    font-size: 1.05rem;
    line-height: 1.8;
    color: #1e293b;
  }

  .blog-article-content blockquote {
    font-family: 'Outfit', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif !important;
    border-left: 4px solid var(--accent, #ea580c);
    background: rgba(234, 88, 12, 0.05);
    padding: 16px 22px;
    margin: 1.8rem 0;
    font-style: italic;
    color: #334155;
  }

  .blog-article-content ul,
  .blog-article-content ol {
    font-family: 'Outfit', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif !important;
    padding-left: 28px;
    margin-bottom: 1.4rem;
    color: #1e293b;
  }

  .blog-article-content li {
    font-family: 'Outfit', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif !important;
    margin-bottom: 0.55rem;
    line-height: 1.75;
  }

  .blog-article-content img {
    max-width: 100%;
    height: auto;
    margin: 1.8rem 0;
    border: 1px solid var(--light-gray, rgba(15, 23, 42, 0.08));
    display: block;
  }

  .blog-article-content code {
    font-family: 'Courier New', Courier, monospace !important;
    background: #f1f5f9;
    padding: 2px 7px;
    font-size: 0.9em;
    color: #0f172a;
    border: 1px solid #e2e8f0;
  }

  .blog-article-content pre {
    font-family: 'Courier New', Courier, monospace !important;
    background: #0f172a;
    color: #f8fafc;
    padding: 18px 22px;
    overflow-x: auto;
    line-height: 1.55;
    margin: 1.8rem 0;
  }

  .blog-article-content pre code {
    font-family: 'Courier New', Courier, monospace !important;
    background: transparent !important;
    border: none !important;
    color: inherit !important;
    padding: 0 !important;
  }

  .blog-article-content a {
    color: var(--accent, #ea580c);
    text-decoration: underline;
    font-weight: 600;
  }

  .blog-article-content a:hover {
    color: var(--accent-muted, #c2410c);
  }

  .blog-article-content table {
    width: 100%;
    border-collapse: collapse;
    margin: 1.8rem 0;
    font-size: 0.95rem;
  }

  .blog-article-content th,
  .blog-article-content td {
    padding: 12px 14px;
    border: 1px solid #e2e8f0;
    text-align: left;
  }

  .blog-article-content th {
    background: #f8fafc;
    font-weight: 700;
    color: #0f172a;
  }
</style>

<main class="container">
  <section id="blog-post" class="section-row" style="border-bottom: none;">
    <div class="section-label">Blog</div>
    <div class="section-content">
      <!-- Featured Image -->
      <?php if (!empty($post['featured_image'])): ?>
        <div class="img-skeleton-wrap" style="width: 100%; height: 400px; margin-bottom: 32px;">
          <img 
            src="<?php echo htmlspecialchars(portfolioAssetUrl($post['featured_image'])); ?>" 
            alt="<?php echo htmlspecialchars($post['title']); ?>" 
            loading="lazy"
            decoding="async"
            style="width: 100%; height: 100%; object-fit: cover; display: block;"
            onload="this.closest('.img-skeleton-wrap').classList.add('loaded')"
          >
        </div>
      <?php endif; ?>

      <!-- Post Header -->
      <div style="margin-bottom: 32px;">
        <div style="display: flex; gap: 12px; margin-bottom: 16px; flex-wrap: wrap; align-items: center;">
          <div style="color: var(--accent); font-size: 0.85rem; font-weight: 700; letter-spacing: 0.08em; text-transform: uppercase;">
            <?php echo !empty($post['published_at']) ? date('M d, Y', strtotime($post['published_at'])) : 'Unpublished'; ?>
          </div>
          <?php if (!empty($post['category'])): ?>
            <div style="color: var(--muted); font-size: 0.85rem; font-weight: 700; letter-spacing: 0.08em; text-transform: uppercase;">
              • <?php echo htmlspecialchars($post['category']); ?>
            </div>
          <?php endif; ?>
          <?php if (!empty($post['reading_time'])): ?>
            <div style="color: var(--muted); font-size: 0.85rem; font-weight: 700; letter-spacing: 0.08em; text-transform: uppercase;">
              • <?php echo (int)$post['reading_time']; ?> min read
            </div>
          <?php endif; ?>
        </div>
        
        <h1 style="margin: 0 0 20px 0; font-size: 2.2rem; line-height: 1.2; letter-spacing: -0.03em; color: var(--text);">
          <?php echo htmlspecialchars($post['title']); ?>
        </h1>
        
        <div style="display: flex; gap: 20px; align-items: center; padding-bottom: 24px; border-bottom: 1px solid var(--light-gray); flex-wrap: wrap;">
          <div>
            <div style="font-weight: 700; color: var(--text);">
              <?php echo htmlspecialchars($post['author_name']); ?>
            </div>
            <div style="font-size: 0.85rem; color: var(--muted); display: flex; gap: 12px; margin-top: 2px;">
              <span><i class="fa-regular fa-eye" style="margin-right: 4px;"></i> <?php echo (int)($post['views'] ?? 0); ?> views</span>
              <span><i class="fa-regular fa-comment" style="color: var(--accent); margin-right: 4px;"></i> <?php echo $commentsCount; ?> <?php echo $commentsCount === 1 ? 'comment' : 'comments'; ?></span>
            </div>
          </div>
        </div>
      </div>

      <!-- Post Content (Outfit Font Enforced) -->
      <article class="blog-article-content">
        <?php 
        // Display HTML content from rich editor
        echo $post['content']; 
        ?>
      </article>

      <!-- Tags -->
      <?php if (!empty($post['tags'])): ?>
        <div style="padding: 20px; background: var(--soft-bg); border: 1px solid var(--light-gray); margin-bottom: 32px;">
          <div style="font-weight: 700; margin-bottom: 12px; color: var(--text); text-transform: uppercase; font-size: 0.8rem; letter-spacing: 0.08em;">Tags:</div>
          <div style="display: flex; gap: 8px; flex-wrap: wrap;">
            <?php foreach (array_filter(array_map('trim', explode(',', $post['tags']))) as $tag): ?>
              <span style="font-size: 0.85rem; font-weight: 600; color: var(--accent); background: rgba(234, 88, 12, 0.08); padding: 6px 12px; border: 1px solid rgba(234, 88, 12, 0.2);">
                #<?php echo htmlspecialchars($tag); ?>
              </span>
            <?php endforeach; ?>
          </div>
        </div>
      <?php endif; ?>

      <!-- Back Button -->
      <div style="padding-top: 20px; border-top: 1px solid var(--light-gray); margin-bottom: 48px;">
        <a href="blog.php" style="color: var(--accent); text-decoration: none; font-weight: 700; display: inline-flex; align-items: center; gap: 8px; font-size: 0.95rem;">
          ← Back to Blog
        </a>
      </div>

      <!-- ═══════════════════════════════════════════════════════ -->
      <!-- COMMENTS & DISCUSSION SECTION                           -->
      <!-- ═══════════════════════════════════════════════════════ -->
      <section id="discussion" style="padding-top: 24px; border-top: 2px solid var(--light-gray); margin-bottom: 48px;">
        <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 28px; flex-wrap: wrap; gap: 12px;">
          <h3 style="margin: 0; font-size: 1.5rem; font-weight: 800; letter-spacing: -0.03em; color: var(--text);">
            <i class="fa-regular fa-comments" style="color: var(--accent); margin-right: 8px;"></i> Discussion (<?php echo $commentsCount; ?>)
          </h3>
          <?php if (!empty($post['allow_comments'])): ?>
            <a href="#comment-form" style="font-size: 0.82rem; font-weight: 700; text-transform: uppercase; letter-spacing: 0.06em; color: var(--accent); text-decoration: underline;">
              Leave a response ↓
            </a>
          <?php endif; ?>
        </div>

        <!-- Feedback Alert Banner -->
        <?php if (!empty($commentFeedback)): ?>
          <div style="padding: 14px 18px; margin-bottom: 24px; border: 1px solid <?php echo $commentFeedbackType === 'success' ? '#a7f3d0' : '#fecaca'; ?>; background: <?php echo $commentFeedbackType === 'success' ? '#ecfdf5' : '#fef2f2'; ?>; color: <?php echo $commentFeedbackType === 'success' ? '#065f46' : '#991b1b'; ?>; font-weight: 600; font-size: 0.92rem; display: flex; align-items: center; gap: 10px;">
            <i class="fa-solid <?php echo $commentFeedbackType === 'success' ? 'fa-circle-check' : 'fa-circle-exclamation'; ?>"></i>
            <div><?php echo htmlspecialchars($commentFeedback); ?></div>
          </div>
        <?php endif; ?>

        <!-- Existing Comments List -->
        <div class="comments-list" style="display: grid; gap: 18px; margin-bottom: 36px;">
          <?php if ($commentsCount === 0): ?>
            <div style="padding: 28px; background: var(--soft-bg); border: 1px solid var(--light-gray); text-align: center; color: var(--muted);">
              <i class="fa-regular fa-comment-dots" style="font-size: 2rem; color: var(--accent); opacity: 0.6; margin-bottom: 10px; display: block;"></i>
              <strong style="color: var(--text); font-size: 1rem; display: block; margin-bottom: 4px;">No comments yet</strong>
              <span style="font-size: 0.9rem;">Be the first to share your thoughts on this article!</span>
            </div>
          <?php else: ?>
            <?php foreach ($comments as $comment): ?>
              <?php
                $initial = strtoupper(substr(trim($comment['author_name']), 0, 1));
                $commentDate = date('M d, Y \a\t g:i A', strtotime($comment['created_at']));
              ?>
              <div class="comment-item" style="padding: 22px; background: var(--soft-bg); border: 1px solid var(--light-gray); position: relative;">
                <div style="display: flex; gap: 14px; align-items: flex-start;">
                  <div style="width: 42px; height: 42px; background: var(--text); color: #ffffff; display: flex; align-items: center; justify-content: center; font-weight: 800; font-size: 1.1rem; flex-shrink: 0; border: 1px solid rgba(15, 23, 42, 0.2);">
                    <?php echo $initial; ?>
                  </div>
                  <div style="flex: 1;">
                    <div style="display: flex; justify-content: space-between; align-items: baseline; flex-wrap: wrap; gap: 8px; margin-bottom: 8px;">
                      <strong style="color: var(--text); font-size: 1.02rem;"><?php echo htmlspecialchars($comment['author_name']); ?></strong>
                      <span style="color: var(--muted); font-size: 0.78rem; font-family: monospace;">
                        <i class="fa-regular fa-clock" style="margin-right: 4px;"></i> <?php echo $commentDate; ?>
                      </span>
                    </div>
                    <div style="color: var(--text); font-size: 0.94rem; line-height: 1.6; white-space: pre-line;">
                      <?php echo nl2br(htmlspecialchars($comment['comment_text'])); ?>
                    </div>
                  </div>
                </div>
              </div>
            <?php endforeach; ?>
          <?php endif; ?>
        </div>

        <!-- Comment Form or Closed Notice -->
        <?php if (!empty($post['allow_comments'])): ?>
          <div id="comment-form" style="padding: 28px; background: var(--soft-bg); border: 1px solid var(--light-gray);">
            <div style="margin-bottom: 20px;">
              <h4 style="margin: 0 0 6px 0; font-size: 1.25rem; font-weight: 800; letter-spacing: -0.02em; color: var(--text);">
                Leave a Comment
              </h4>
              <p style="margin: 0; color: var(--muted); font-size: 0.88rem;">
                Your email address will not be published publicly. Name and email are optional (leave blank to comment anonymously).
              </p>
            </div>

            <form action="post.php?slug=<?php echo urlencode($post['slug']); ?>#discussion" method="POST">
              <input type="hidden" name="action" value="post_comment">
              
              <!-- Anti-spam Honeypot (hidden from human visitors) -->
              <div style="display: none !important;">
                <label for="website_hp">Leave this field blank</label>
                <input type="text" id="website_hp" name="website_hp" value="" tabindex="-1" autocomplete="off">
              </div>

              <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(240px, 1fr)); gap: 16px; margin-bottom: 16px;">
                <div>
                  <label for="author_name" style="display: block; font-size: 0.78rem; font-weight: 800; text-transform: uppercase; letter-spacing: 0.08em; color: var(--muted); margin-bottom: 6px;">
                    <i class="fa-solid fa-user" style="color: var(--accent); margin-right: 4px;"></i> Your Name <span style="font-weight: normal; text-transform: none; color: var(--muted);">(Optional)</span>
                  </label>
                  <input 
                    type="text" 
                    id="author_name" 
                    name="author_name" 
                    placeholder="e.g. Alex Doe (or leave blank for Anonymous)" 
                    value="<?php echo htmlspecialchars($formAuthorName); ?>" 
                    style="width: 100%; padding: 12px 14px; background: #ffffff; border: 1px solid var(--light-gray); color: var(--text); outline: none; font-size: 0.92rem; box-sizing: border-box;"
                  >
                </div>

                <div>
                  <label for="author_email" style="display: block; font-size: 0.78rem; font-weight: 800; text-transform: uppercase; letter-spacing: 0.08em; color: var(--muted); margin-bottom: 6px;">
                    <i class="fa-solid fa-envelope" style="color: var(--accent); margin-right: 4px;"></i> Email Address <span style="font-weight: normal; text-transform: none; color: var(--muted);">(Optional)</span>
                  </label>
                  <input 
                    type="email" 
                    id="author_email" 
                    name="author_email" 
                    placeholder="e.g. alex@example.com (optional)" 
                    value="<?php echo htmlspecialchars($formAuthorEmail); ?>" 
                    style="width: 100%; padding: 12px 14px; background: #ffffff; border: 1px solid var(--light-gray); color: var(--text); outline: none; font-size: 0.92rem; box-sizing: border-box;"
                  >
                </div>
              </div>

              <div style="margin-bottom: 20px;">
                <label for="comment_text" style="display: block; font-size: 0.78rem; font-weight: 800; text-transform: uppercase; letter-spacing: 0.08em; color: var(--muted); margin-bottom: 6px;">
                  <i class="fa-solid fa-message" style="color: var(--accent); margin-right: 4px;"></i> Your Comment *
                </label>
                <textarea 
                  id="comment_text" 
                  name="comment_text" 
                  required 
                  rows="5" 
                  placeholder="Write your thoughts, questions, or perspectives here..." 
                  style="width: 100%; padding: 12px 14px; background: #ffffff; border: 1px solid var(--light-gray); color: var(--text); outline: none; font-size: 0.92rem; line-height: 1.5; resize: vertical; box-sizing: border-box;"
                ><?php echo htmlspecialchars($formCommentText); ?></textarea>
              </div>

              <div style="display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 12px;">
                <button 
                  type="submit" 
                  class="btn primary" 
                  style="padding: 13px 26px; font-weight: 800; letter-spacing: 0.06em; text-transform: uppercase; font-size: 0.82rem; cursor: pointer; display: inline-flex; align-items: center; gap: 8px;"
                >
                  <i class="fa-solid fa-paper-plane"></i> Submit Comment
                </button>
                <span style="font-size: 0.78rem; color: var(--muted);">
                  <i class="fa-solid fa-shield-halved" style="color: var(--accent);"></i> Respectful &amp; constructive discussion
                </span>
              </div>
            </form>
          </div>
        <?php else: ?>
          <div style="padding: 18px 22px; background: var(--soft-bg); border: 1px solid var(--light-gray); color: var(--muted); font-size: 0.9rem; display: flex; align-items: center; gap: 10px;">
            <i class="fa-solid fa-lock" style="color: var(--accent);"></i>
            <span>Comments are closed for this article.</span>
          </div>
        <?php endif; ?>
      </section>

      <!-- Related Posts (Optional - Shows 3 recent posts) -->
      <?php
      $relatedPosts = $pdo->prepare("
        SELECT * FROM blog_posts 
        WHERE status = 'published' AND id != ? 
        ORDER BY published_at DESC 
        LIMIT 3
      ");
      $relatedPosts->execute([$post['id']]);
      $relatedPostsData = $relatedPosts->fetchAll(PDO::FETCH_ASSOC);
      
      if (!empty($relatedPostsData)):
      ?>
        <section style="margin-top: 40px; padding-top: 36px; border-top: 1px solid var(--light-gray);">
          <h3 style="margin-bottom: 24px; font-size: 1.35rem; font-weight: 800;">More Articles</h3>
          <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(280px, 1fr)); gap: 20px;">
            <?php foreach ($relatedPostsData as $relatedPost): ?>
              <?php
                $relatedCommentsCount = (int)$pdo->query("SELECT COUNT(*) FROM blog_comments WHERE post_id = " . (int)$relatedPost['id'] . " AND status = 'approved'")->fetchColumn();
              ?>
              <article style="padding: 20px; background: var(--soft-bg); border: 1px solid var(--light-gray);">
                <?php if (!empty($relatedPost['featured_image'])): ?>
                  <div class="img-skeleton-wrap" style="width: calc(100% + 40px); height: 150px; margin: -20px -20px 14px -20px;">
                    <img 
                      src="<?php echo htmlspecialchars(portfolioAssetUrl($relatedPost['featured_image'])); ?>" 
                      alt="<?php echo htmlspecialchars($relatedPost['title']); ?>" 
                      loading="lazy"
                      decoding="async"
                      style="width: 100%; height: 100%; object-fit: cover; display: block;"
                      onload="this.closest('.img-skeleton-wrap').classList.add('loaded')"
                    >
                  </div>
                <?php endif; ?>
                <div style="display: flex; gap: 8px; margin-bottom: 8px; flex-wrap: wrap; align-items: center;">
                  <div style="font-size: 0.75rem; color: var(--muted); font-weight: 700; text-transform: uppercase;">
                    <?php echo !empty($relatedPost['published_at']) ? date('M d', strtotime($relatedPost['published_at'])) : ''; ?>
                  </div>
                  <?php if (!empty($relatedPost['reading_time'])): ?>
                    <div style="font-size: 0.75rem; color: var(--muted); font-weight: 700; text-transform: uppercase;">
                      • <?php echo (int)$relatedPost['reading_time']; ?> min
                    </div>
                  <?php endif; ?>
                  <div style="font-size: 0.75rem; color: var(--muted); font-weight: 700;">
                    • <i class="fa-regular fa-comment" style="color: var(--accent);"></i> <?php echo $relatedCommentsCount; ?>
                  </div>
                </div>
                <h4 style="margin: 0 0 8px 0; line-height: 1.3; font-size: 1.05rem;">
                  <a href="post.php?slug=<?php echo urlencode($relatedPost['slug']); ?>" style="color: var(--text); text-decoration: none;">
                    <?php echo htmlspecialchars($relatedPost['title']); ?>
                  </a>
                </h4>
                <?php if (!empty($relatedPost['excerpt'])): ?>
                  <p style="margin: 0; font-size: 0.88rem; color: var(--muted); line-height: 1.45;">
                    <?php echo substr(htmlspecialchars($relatedPost['excerpt']), 0, 100) . '...'; ?>
                  </p>
                <?php endif; ?>
                <a href="post.php?slug=<?php echo urlencode($relatedPost['slug']); ?>" style="margin-top: 14px; display: inline-block; color: var(--accent); text-decoration: none; font-size: 0.84rem; font-weight: 700;">
                  Read more →
                </a>
              </article>
            <?php endforeach; ?>
          </div>
        </section>
      <?php endif; ?>
    </div>
  </section>
</main>

<?php
require_once __DIR__ . '/footer.php';
?>
