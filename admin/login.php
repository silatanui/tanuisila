<?php
// Set secure session cookie BEFORE session_start()
if (PHP_VERSION_ID >= 70300) {
    session_set_cookie_params([
        'httponly' => true,
        'secure' => isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on',
        'samesite' => 'Strict'
    ]);
}

session_start();
require_once __DIR__ . '/../config/config.php';

// Security headers
header('X-Content-Type-Options: nosniff');
header('X-Frame-Options: DENY');
header('X-XSS-Protection: 1; mode=block');
header('Strict-Transport-Security: max-age=31536000; includeSubDomains');

// Prevent caching of login page
header('Cache-Control: no-cache, no-store, must-revalidate');
header('Pragma: no-cache');
header('Expires: 0');

if (isset($_SESSION['admin_logged_in']) && $_SESSION['admin_logged_in']) {
    header('Location: index.php');
    exit;
}

$adminCount = (int) $pdo->query('SELECT COUNT(*) FROM admin_users')->fetchColumn();
$canCreateAdmin = $adminCount === 0;
$setupMessage = $_GET['setup'] ?? '';

$error = '';
$max_attempts = 5;
$lockout_time = 15 * 60; // 15 minutes
$attempt_key = 'login_attempts_' . $_SERVER['REMOTE_ADDR'];
$lockout_key = 'login_lockout_' . $_SERVER['REMOTE_ADDR'];

// Check if IP is locked out
if (isset($_SESSION[$lockout_key]) && time() < $_SESSION[$lockout_key]) {
    $error = 'Too many login attempts. Please try again later.';
} elseif ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Verify CSRF token
    if (!isset($_POST['csrf_token']) || !hash_equals($_SESSION['csrf_token'] ?? '', $_POST['csrf_token'] ?? '')) {
        $error = 'Security validation failed. Please try again.';
    } else {
        $username = $_POST['username'] ?? '';
        $password = $_POST['password'] ?? '';

        // Rate limiting
        $attempts = $_SESSION[$attempt_key] ?? 0;
        if ($attempts >= $max_attempts) {
            $_SESSION[$lockout_key] = time() + $lockout_time;
            $error = 'Too many login attempts. Please try again later.';
        } else {
          $isValidLogin = false;

          $stmt = $pdo->prepare('SELECT password_hash FROM admin_users WHERE username = :username AND is_active = 1 LIMIT 1');
          $stmt->execute([':username' => $username]);
          $adminUser = $stmt->fetch();

          if ($adminUser && isset($adminUser['password_hash'])) {
            $isValidLogin = password_verify($password, $adminUser['password_hash']);
          } elseif ($username === ADMIN_USERNAME && $password === ADMIN_PASSWORD) {
            // Backward compatibility when DB admin record has not been created yet.
            $isValidLogin = true;
          }

          if ($isValidLogin) {
            $_SESSION['admin_logged_in'] = true;
            unset($_SESSION[$attempt_key]);
            unset($_SESSION[$lockout_key]);
            header('Location: index.php');
            exit;
          }

          $attempts++;
          $_SESSION[$attempt_key] = $attempts;
          $error = 'Invalid credentials.';
        }
    }
}

// Generate CSRF token
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

$postedUsername = ($_SERVER['REQUEST_METHOD'] === 'POST') ? (string) ($_POST['username'] ?? '') : '';
?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Admin Login · Tanui Sila</title>
  <link rel="icon" type="image/jpeg" href="../Tanui-Sila-Logo-v3.jpg">
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css" crossorigin="anonymous" referrerpolicy="no-referrer" />
  <style>
    :root {
      --primary: #ea580c;
      --primary-hover: #c2410c;
      --ink: #0f172a;
      --muted: #64748b;
      --line: #e2e8f0;
      --panel: #ffffff;
      --danger: #dc2626;
      --sidebar-bg: #090d16;
    }

    * { box-sizing: border-box; }

    html, body {
      margin: 0;
      min-height: 100%;
      font-family: 'Outfit', -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif;
      color: var(--ink);
      -webkit-font-smoothing: antialiased;
    }

    body {
      min-height: 100vh;
      display: grid;
      grid-template-columns: minmax(0, 1.05fr) minmax(320px, 0.95fr);
      background: #f8fafc;
    }

    .stage {
      position: relative;
      min-height: 100vh;
      padding: clamp(32px, 6vw, 72px);
      display: flex;
      flex-direction: column;
      justify-content: space-between;
      color: #e2e8f0;
      background:
        radial-gradient(ellipse 90% 70% at 12% 18%, rgba(234, 88, 12, 0.22) 0%, transparent 52%),
        radial-gradient(ellipse 70% 50% at 88% 88%, rgba(56, 189, 248, 0.08) 0%, transparent 48%),
        linear-gradient(165deg, #0c1220 0%, var(--sidebar-bg) 48%, #05080f 100%);
      overflow: hidden;
    }

    .stage::before {
      content: "";
      position: absolute;
      inset: 0;
      background-image:
        linear-gradient(rgba(255, 255, 255, 0.035) 1px, transparent 1px),
        linear-gradient(90deg, rgba(255, 255, 255, 0.035) 1px, transparent 1px);
      background-size: 36px 36px;
      mask-image: linear-gradient(180deg, rgba(0,0,0,0.55), transparent 85%);
      pointer-events: none;
    }

    .stage::after {
      content: "";
      position: absolute;
      width: 420px;
      height: 420px;
      right: -120px;
      bottom: -140px;
      background: radial-gradient(circle, rgba(234, 88, 12, 0.28) 0%, transparent 68%);
      pointer-events: none;
    }

    .stage-top,
    .stage-main,
    .stage-foot {
      position: relative;
      z-index: 1;
    }

    .brand-lockup {
      display: inline-flex;
      align-items: center;
      gap: 14px;
      text-decoration: none;
      color: inherit;
    }

    .brand-lockup__mark {
      width: 52px;
      height: 52px;
      display: grid;
      place-items: center;
      background: #fff;
      border: 1px solid rgba(255, 255, 255, 0.18);
      box-shadow: 0 0 0 1px rgba(234, 88, 12, 0.2);
    }

    .brand-lockup__mark img {
      width: 38px;
      height: 38px;
      object-fit: contain;
      display: block;
    }

    .brand-lockup__copy strong {
      display: block;
      font-size: 1.15rem;
      font-weight: 800;
      letter-spacing: -0.03em;
      color: #fff;
      line-height: 1.1;
    }

    .brand-lockup__copy span {
      display: inline-block;
      margin-top: 5px;
      font-size: 0.66rem;
      font-weight: 800;
      letter-spacing: 0.14em;
      text-transform: uppercase;
      color: #fdba74;
      border: 1px solid rgba(234, 88, 12, 0.4);
      background: rgba(234, 88, 12, 0.14);
      padding: 3px 8px;
    }

    .stage-main {
      max-width: 34rem;
      margin: 48px 0;
    }

    .stage-main h1 {
      margin: 0 0 18px;
      font-size: clamp(2.6rem, 5vw, 4.2rem);
      line-height: 0.98;
      letter-spacing: -0.055em;
      font-weight: 800;
      color: #fff;
    }

    .stage-main h1 em {
      font-style: normal;
      color: #fb923c;
    }

    .stage-main p {
      margin: 0;
      max-width: 28rem;
      font-size: 1.08rem;
      line-height: 1.45;
      font-weight: 400;
      color: #94a3b8;
    }

    .stage-foot {
      display: flex;
      flex-wrap: wrap;
      gap: 18px 28px;
      font-size: 0.82rem;
      color: #64748b;
      font-weight: 600;
      letter-spacing: 0.02em;
    }

    .stage-foot span {
      display: inline-flex;
      align-items: center;
      gap: 8px;
    }

    .stage-foot i {
      color: #fdba74;
      font-size: 0.78rem;
    }

    .panel {
      min-height: 100vh;
      display: grid;
      place-items: center;
      padding: clamp(28px, 5vw, 56px);
      background:
        radial-gradient(circle at 100% 0%, rgba(234, 88, 12, 0.05), transparent 42%),
        linear-gradient(180deg, #ffffff 0%, #f8fafc 100%);
      border-left: 1px solid var(--line);
    }

    .panel-card {
      width: min(100%, 420px);
    }

    .panel-kicker {
      margin: 0 0 10px;
      font-size: 0.7rem;
      font-weight: 800;
      letter-spacing: 0.14em;
      text-transform: uppercase;
      color: var(--primary);
    }

    .panel-card h2 {
      margin: 0 0 8px;
      font-size: clamp(1.9rem, 3vw, 2.35rem);
      line-height: 1.05;
      letter-spacing: -0.045em;
      font-weight: 800;
    }

    .panel-card > .lede {
      margin: 0 0 28px;
      color: var(--muted);
      font-size: 0.98rem;
      line-height: 1.45;
    }

    .alert {
      margin: 0 0 18px;
      padding: 12px 14px;
      font-size: 0.92rem;
      font-weight: 600;
      line-height: 1.4;
      border: 1px solid transparent;
    }

    .alert--error {
      background: #fef2f2;
      border-color: #fecaca;
      color: var(--danger);
    }

    .alert--info {
      background: #fff7ed;
      border-color: #fed7aa;
      color: #9a3412;
    }

    form {
      display: grid;
      gap: 16px;
    }

    .field {
      display: grid;
      gap: 7px;
    }

    .field label {
      font-size: 0.86rem;
      font-weight: 700;
      color: var(--ink);
    }

    .field input {
      width: 100%;
      padding: 14px 15px;
      border: 1px solid var(--line);
      background: #fff;
      color: var(--ink);
      font: inherit;
      font-size: 0.98rem;
      outline: none;
      transition: border-color 0.15s ease, box-shadow 0.15s ease;
    }

    .field input::placeholder {
      color: #94a3b8;
    }

    .field input:focus {
      border-color: var(--primary);
      box-shadow: 0 0 0 3px rgba(234, 88, 12, 0.14);
    }

    .password-wrap {
      position: relative;
    }

    .password-wrap input {
      padding-right: 48px;
    }

    .password-toggle {
      position: absolute;
      top: 0;
      right: 0;
      bottom: 0;
      width: 46px;
      border: 0;
      background: transparent;
      color: #64748b;
      cursor: pointer;
      display: grid;
      place-items: center;
    }

    .password-toggle:hover {
      color: var(--primary);
    }

    .submit {
      margin-top: 4px;
      width: 100%;
      padding: 15px 18px;
      border: 1px solid var(--primary);
      background: var(--primary);
      color: #fff;
      font: inherit;
      font-weight: 800;
      letter-spacing: 0.02em;
      cursor: pointer;
      display: inline-flex;
      align-items: center;
      justify-content: center;
      gap: 10px;
      transition: background 0.15s ease, border-color 0.15s ease, transform 0.15s ease;
    }

    .submit:hover {
      background: var(--primary-hover);
      border-color: var(--primary-hover);
    }

    .submit:active {
      transform: translateY(1px);
    }

    .panel-meta {
      margin: 22px 0 0;
      padding-top: 18px;
      border-top: 1px solid var(--line);
      display: grid;
      gap: 12px;
      font-size: 0.86rem;
      color: var(--muted);
      line-height: 1.45;
    }

    .panel-meta a {
      color: var(--primary);
      font-weight: 700;
      text-decoration: none;
      border-bottom: 1px solid rgba(234, 88, 12, 0.35);
    }

    .panel-meta a:hover {
      border-bottom-color: var(--primary);
    }

    .back-link {
      display: inline-flex;
      align-items: center;
      gap: 8px;
      color: var(--muted);
      text-decoration: none;
      font-weight: 600;
    }

    .back-link:hover {
      color: var(--ink);
    }

    @media (max-width: 900px) {
      body {
        grid-template-columns: 1fr;
      }

      .stage {
        min-height: auto;
        padding: 24px 20px 28px;
      }

      .stage-main {
        margin: 28px 0 22px;
      }

      .stage-main h1 {
        font-size: clamp(2.1rem, 9vw, 2.8rem);
      }

      .stage-main p {
        font-size: 0.98rem;
      }

      .stage-foot {
        gap: 12px 18px;
        font-size: 0.75rem;
      }

      .panel {
        min-height: auto;
        border-left: 0;
        border-top: 1px solid var(--line);
        padding: 28px 20px 36px;
        place-items: stretch;
      }

      .panel-card {
        width: 100%;
      }

      .panel-card h2 {
        font-size: 1.85rem;
      }
    }

    @media (max-width: 480px) {
      .brand-lockup__copy strong {
        font-size: 1.02rem;
      }

      .stage-foot span {
        width: 100%;
      }
    }
  </style>
</head>
<body>
  <section class="stage" aria-label="Brand">
    <div class="stage-top">
      <a class="brand-lockup" href="../index.php">
        <span class="brand-lockup__mark">
          <img src="../Tanui-Sila-Logo-v3.png" alt="">
        </span>
        <span class="brand-lockup__copy">
          <strong>Tanui Sila</strong>
          <span>Admin cockpit</span>
        </span>
      </a>
    </div>

    <div class="stage-main">
      <h1>Tanui Sila<br><em>Admin</em></h1>
      <p>Sign in to manage essays, projects, messages, and the analytics that keep this portfolio honest.</p>
    </div>

    <div class="stage-foot">
      <span><i class="fa-solid fa-shield-halved" aria-hidden="true"></i> CSRF protected</span>
      <span><i class="fa-solid fa-gauge-high" aria-hidden="true"></i> Rate limited</span>
      <span><i class="fa-solid fa-lock" aria-hidden="true"></i> Session secured</span>
    </div>
  </section>

  <main class="panel">
    <div class="panel-card">
      <p class="panel-kicker">Secure access</p>
      <h2>Sign in</h2>
      <p class="lede">Use your admin credentials to open the dashboard.</p>

      <?php if ($setupMessage === 'done'): ?>
        <div class="alert alert--info">Admin signup is closed because an account already exists. Please sign in.</div>
      <?php endif; ?>

      <?php if ($error !== ''): ?>
        <div class="alert alert--error" role="alert"><?php echo htmlspecialchars($error); ?></div>
      <?php endif; ?>

      <form method="post" autocomplete="off">
        <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token']); ?>">

        <div class="field">
          <label for="username">Username</label>
          <input
            id="username"
            type="text"
            name="username"
            required
            autocomplete="username"
            autofocus
            placeholder="Admin username"
            value="<?php echo htmlspecialchars($postedUsername, ENT_QUOTES, 'UTF-8'); ?>"
          >
        </div>

        <div class="field">
          <label for="password">Password</label>
          <div class="password-wrap">
            <input
              id="password"
              type="password"
              name="password"
              required
              autocomplete="current-password"
              placeholder="Your password"
            >
            <button type="button" class="password-toggle" id="toggle-password" aria-label="Show password">
              <i class="fa-regular fa-eye" aria-hidden="true"></i>
            </button>
          </div>
        </div>

        <button class="submit" type="submit">
          <span>Enter dashboard</span>
          <i class="fa-solid fa-arrow-right" aria-hidden="true"></i>
        </button>
      </form>

      <div class="panel-meta">
        <?php if ($canCreateAdmin): ?>
          <p>No admin account yet? <a href="signup.php">Create the first account</a></p>
        <?php endif; ?>
        <a class="back-link" href="../index.php">
          <i class="fa-solid fa-arrow-left" aria-hidden="true"></i>
          Back to portfolio
        </a>
      </div>
    </div>
  </main>

  <script>
  (function () {
    var btn = document.getElementById('toggle-password');
    var input = document.getElementById('password');
    if (!btn || !input) return;
    btn.addEventListener('click', function () {
      var show = input.type === 'password';
      input.type = show ? 'text' : 'password';
      btn.setAttribute('aria-label', show ? 'Hide password' : 'Show password');
      btn.innerHTML = show
        ? '<i class="fa-regular fa-eye-slash" aria-hidden="true"></i>'
        : '<i class="fa-regular fa-eye" aria-hidden="true"></i>';
    });
  })();
  </script>
</body>
</html>
