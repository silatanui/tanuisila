<?php
if (PHP_VERSION_ID >= 70300) {
    session_set_cookie_params([
        'httponly' => true,
        'secure' => isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on',
        'samesite' => 'Strict'
    ]);
}

session_start();
require_once __DIR__ . '/../config/config.php';

header('X-Content-Type-Options: nosniff');
header('X-Frame-Options: DENY');
header('X-XSS-Protection: 1; mode=block');
header('Strict-Transport-Security: max-age=31536000; includeSubDomains');
header('Cache-Control: no-cache, no-store, must-revalidate');
header('Pragma: no-cache');
header('Expires: 0');

if (isset($_SESSION['admin_logged_in']) && $_SESSION['admin_logged_in']) {
    header('Location: index.php');
    exit;
}

$error = '';
$adminCount = (int) $pdo->query('SELECT COUNT(*) FROM admin_users')->fetchColumn();

if ($adminCount > 0) {
    header('Location: login.php?setup=done');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!isset($_POST['csrf_token']) || !hash_equals($_SESSION['csrf_token'] ?? '', $_POST['csrf_token'] ?? '')) {
        $error = 'Security validation failed. Please refresh and try again.';
    } else {
        $username = trim($_POST['username'] ?? '');
        $password = $_POST['password'] ?? '';
        $confirmPassword = $_POST['confirm_password'] ?? '';

        if ($username === '') {
            $error = 'Username is required.';
        } elseif (strlen($username) > 255) {
            $error = 'Username is too long.';
        } elseif (strlen($password) < 8) {
            $error = 'Password must be at least 8 characters.';
        } elseif ($password !== $confirmPassword) {
            $error = 'Passwords do not match.';
        } else {
            try {
                $insert = $pdo->prepare('INSERT INTO admin_users (username, password_hash, is_active) VALUES (:username, :password_hash, 1)');
                $insert->execute([
                    ':username' => $username,
                    ':password_hash' => password_hash($password, PASSWORD_DEFAULT),
                ]);

                $_SESSION['admin_logged_in'] = true;
                header('Location: index.php');
                exit;
            } catch (Throwable $e) {
                $error = 'Unable to create account. Try another username.';
            }
        }
    }
}

if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}
?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Create Admin Account</title>
  <link rel="icon" type="image/jpeg" href="../Tanui-Sila-Logo-v3.jpg">
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@400;500;600;700;800&display=swap" rel="stylesheet">
  <style>
    :root {
      --bg: #f3f6fb;
      --panel: #ffffff;
      --line: #dfe7f1;
      --text: #111827;
      --muted: #5f6f86;
      --primary: #0f172a;
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
      padding: 24px 14px;
    }

    .card {
      width: min(460px, 100%);
      background: var(--panel);
      border: 1px solid var(--line);
      box-shadow: 0 18px 45px var(--shadow);
      padding: 34px 30px;
    }

    h1 {
      margin: 0 0 8px;
      font-size: clamp(1.8rem, 2.4vw, 2.2rem);
      line-height: 1.1;
      letter-spacing: -0.03em;
    }

    .subtitle {
      margin: 0 0 20px;
      color: var(--muted);
      font-size: 0.96rem;
    }

    .error {
      margin-bottom: 16px;
      padding: 12px 14px;
      background: #fff1f2;
      border: 1px solid #fecdd3;
      color: var(--danger);
      font-size: 0.92rem;
      font-weight: 500;
    }

    form {
      display: grid;
      gap: 14px;
    }

    .field {
      display: grid;
      gap: 8px;
    }

    label {
      font-size: 0.9rem;
      font-weight: 600;
      color: var(--text);
    }

    input {
      width: 100%;
      padding: 13px 14px;
      border: 1px solid var(--line);
      background: #ffffff;
      color: var(--text);
      font: inherit;
      outline: none;
      transition: border-color 0.2s ease, box-shadow 0.2s ease;
    }

    input:focus {
      border-color: var(--text);
      box-shadow: 0 0 0 3px rgba(17, 24, 39, 0.06);
    }

    button {
      margin-top: 8px;
      width: 100%;
      padding: 14px 16px;
      border: 1px solid var(--primary);
      background: var(--primary);
      color: #ffffff;
      font: inherit;
      font-weight: 600;
      cursor: pointer;
    }

    button:hover {
      opacity: 0.96;
    }

    .meta {
      margin-top: 16px;
      font-size: 0.84rem;
      color: var(--muted);
      line-height: 1.6;
      border-top: 1px solid var(--line);
      padding-top: 14px;
    }

    .back {
      margin-top: 14px;
      font-size: 0.9rem;
      color: var(--muted);
      text-align: center;
    }

    .back a {
      color: var(--primary);
      text-decoration: none;
      font-weight: 600;
    }
  </style>
</head>
<body>
  <main class="card">
    <h1>Create Admin Account</h1>
    <p class="subtitle">This setup can be completed only once for security.</p>

    <?php if (!empty($error)): ?>
      <div class="error"><?php echo htmlspecialchars($error, ENT_QUOTES, 'UTF-8'); ?></div>
    <?php endif; ?>

    <form method="post" autocomplete="off">
      <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token'], ENT_QUOTES, 'UTF-8'); ?>">

      <div class="field">
        <label for="username">Admin Username</label>
        <input id="username" type="text" name="username" maxlength="255" required autocomplete="off" placeholder="e.g. admin@example.com">
      </div>

      <div class="field">
        <label for="password">Password</label>
        <input id="password" type="password" name="password" minlength="8" required autocomplete="new-password" placeholder="At least 8 characters">
      </div>

      <div class="field">
        <label for="confirm_password">Confirm Password</label>
        <input id="confirm_password" type="password" name="confirm_password" minlength="8" required autocomplete="new-password" placeholder="Repeat your password">
      </div>

      <button type="submit">Create Account</button>
    </form>

    <p class="meta">After this account is created, signup is disabled and only login is allowed.</p>
    <p class="back"><a href="login.php">Back to login</a></p>
  </main>
</body>
</html>
