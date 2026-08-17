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
        } elseif ($username === ADMIN_USERNAME && $password === ADMIN_PASSWORD) {
            $_SESSION['admin_logged_in'] = true;
            unset($_SESSION[$attempt_key]);
            unset($_SESSION[$lockout_key]);
            header('Location: index.php');
            exit;
        } else {
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
?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Admin Login</title>
  <link rel="icon" type="image/jpeg" href="../Tanui-Sila-Logo-v3.jpg">
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@400;500;600;700;800&display=swap" rel="stylesheet">
  <style>
    :root {
      --bg: #f3f6fb;
      --panel: #ffffff;
      --panel-alt: #f8fafc;
      --line: #dfe7f1;
      --text: #111827;
      --muted: #5f6f86;
      --primary: #0f172a;
      --primary-soft: #e8eefc;
      --danger: #b42318;
      --shadow: rgba(15, 23, 42, 0.08);
    }

    * {
      box-sizing: border-box;
    }

    html, body {
      margin: 0;
      min-height: 100%;
      font-family: 'Outfit', 'Segoe UI', sans-serif;
      background: linear-gradient(135deg, #eef3f9 0%, #f9fafb 100%);
      color: var(--text);
    }

    body {
      min-height: 100vh;
      display: grid;
      place-items: center;
      padding: 32px 18px;
    }

    .login-shell {
      width: min(1040px, 100%);
      display: grid;
      grid-template-columns: 1.1fr 0.9fr;
      background: var(--panel);
      border: 1px solid var(--line);
      box-shadow: 0 18px 45px var(--shadow);
      overflow: hidden;
    }

    .brand-panel {
      position: relative;
      background: linear-gradient(180deg, #f7f9fc 0%, #eef4ff 100%);
      border-right: 1px solid var(--line);
      padding: 52px 46px;
      display: flex;
      flex-direction: column;
      justify-content: center;
      min-height: 620px;
    }

    .brand-panel::before {
      content: "";
      position: absolute;
      inset: 0;
      background-image: linear-gradient(rgba(15, 23, 42, 0.03) 1px, transparent 1px), linear-gradient(90deg, rgba(15, 23, 42, 0.03) 1px, transparent 1px);
      background-size: 28px 28px;
      pointer-events: none;
    }

    .brand-content {
      position: relative;
      z-index: 1;
      max-width: 380px;
    }

    .brand-mark {
      display: inline-flex;
      align-items: center;
      justify-content: center;
      background: #ffffff;
      border: 1px solid var(--line);
      padding: 18px;
      margin-bottom: 26px;
      width: fit-content;
    }

    .brand-mark img {
      display: block;
      width: 160px;
      height: auto;
    }

    .eyebrow {
      display: inline-block;
      font-size: 12px;
      letter-spacing: 0.14em;
      text-transform: uppercase;
      color: var(--muted);
      margin-bottom: 12px;
      font-weight: 600;
    }

    .brand-panel h2 {
      margin: 0 0 16px;
      font-size: clamp(2.2rem, 3vw, 3.2rem);
      line-height: 1.05;
      font-weight: 700;
      letter-spacing: -0.05em;
      color: var(--text);
    }

    .brand-panel p {
      margin: 0;
      color: var(--muted);
      font-size: 1.06rem;
      line-height: 1.4;
      max-width: 290px;
    }

    .form-panel {
      padding: 52px 42px;
      background: var(--panel-alt);
      display: flex;
      align-items: center;
      justify-content: center;
    }

    .form-card {
      width: min(100%, 420px);
    }

    .form-header {
      margin-bottom: 28px;
    }

    .form-header h1 {
      margin: 0 0 8px;
      font-size: clamp(2rem, 2.4vw, 2.5rem);
      line-height: 1.1;
      letter-spacing: -0.06em;
      font-weight: 700;
      color: var(--text);
    }

    .form-header p {
      margin: 0;
      color: var(--muted);
      font-size: 0.98rem;
    }

    .error {
      margin-bottom: 18px;
      padding: 12px 14px;
      background: #fff1f2;
      border: 1px solid #fecdd3;
      color: var(--danger);
      font-size: 0.95rem;
      font-weight: 500;
    }

    form {
      display: grid;
      gap: 18px;
    }

    .field {
      display: grid;
      gap: 8px;
    }

    label {
      font-size: 0.92rem;
      font-weight: 600;
      color: var(--text);
    }

    input {
      width: 100%;
      padding: 14px 15px;
      border: 1px solid var(--line);
      background: #ffffff;
      color: var(--text);
      font: inherit;
      border-radius: 0;
      outline: none;
      transition: border-color 0.2s ease, box-shadow 0.2s ease;
    }

    input:focus {
      border-color: var(--text);
      box-shadow: 0 0 0 3px rgba(17, 24, 39, 0.06);
    }

    button {
      margin-top: 6px;
      width: 100%;
      padding: 15px 18px;
      border: 1px solid var(--primary);
      background: var(--primary);
      color: #ffffff;
      font: inherit;
      font-weight: 600;
      letter-spacing: 0.01em;
      cursor: pointer;
      border-radius: 0;
      transition: opacity 0.2s ease, transform 0.2s ease;
    }

    button:hover {
      opacity: 0.96;
    }

    button:active {
      transform: translateY(1px);
    }

    .note {
      margin-top: 18px;
      font-size: 0.82rem;
      color: var(--muted);
      line-height: 1.6;
      border-top: 1px solid var(--line);
      padding-top: 16px;
    }

    .security-info {
      margin-top: 18px;
      font-size: 0.82rem;
      color: var(--muted);
      line-height: 1.6;
      padding: 12px;
      background: #f0f9ff;
      border-left: 3px solid #0284c7;
      border-radius: 2px;
    }

    @media (max-width: 840px) {
      .login-shell {
        grid-template-columns: 1fr;
      }

      .brand-panel {
        border-right: none;
        border-bottom: 1px solid var(--line);
        min-height: auto;
      }

      .brand-panel, .form-panel {
        padding: 34px 28px;
      }
    }
  </style>
</head>
<body>
  <div class="login-shell">
    <aside class="brand-panel">
      <div class="brand-content">
        <div class="brand-mark">
          <img src="../Tanui-Sila-Logo-v3.png" alt="Tanui Sila Logo">
        </div>
        <span class="eyebrow">Secure access</span>
        <h2>Management portal</h2>
        <p>Built for confident administration, streamlined workflow, and trusted access to your digital operations.</p>
      </div>
    </aside>

    <main class="form-panel">
      <div class="form-card">
        <div class="form-header">
          <h1>Admin Login</h1>
          <p>Sign in to continue to the dashboard.</p>
        </div>

        <?php if (!empty($error)): ?>
          <div class="error"><?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>

        <form method="post" autocomplete="off">
          <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token']); ?>">
          
          <div class="field">
            <label for="username">Username</label>
            <input id="username" type="text" name="username" required autocomplete="off" placeholder="Enter your username">
          </div>

          <div class="field">
            <label for="password">Password</label>
            <input id="password" type="password" name="password" required autocomplete="off" placeholder="Enter your password">
          </div>

          <button type="submit">Sign In</button>
        </form>
      </div>
    </main>
  </div>
</body>
</html>
