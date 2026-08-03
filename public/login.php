<?php
declare(strict_types=1);
require_once __DIR__ . '/../includes/init.php';

try {
    $_orgRow = db()->query('SELECT org_name, logo_path FROM organization_settings ORDER BY id ASC LIMIT 1')->fetch() ?: [];
} catch (Throwable $e) {
    $_orgRow = [];
}
$orgName = $_orgRow['org_name'] ?? '';

$email  = trim(strtolower($_POST['email'] ?? ''));
$next   = (string)($_GET['next'] ?? $_POST['next'] ?? '/index.php?page=projects');
$errors = [];

function safe_next_local(string $next, string $fallback = '/index.php?page=projects'): string
{
    $next = trim($next);
    if ($next === '') return $fallback;
    if (strpos($next, '/') === 0 && strpos($next, '//') !== 0) return $next;
    return $fallback;
}

if (current_person()) {
    header("Location: /index.php?page=projects");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $pw = (string)($_POST['password'] ?? '');

    if ($email === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = "Enter a valid email address.";
    } elseif ($pw === '') {
        $errors[] = "Enter your password.";
    } else {
        if (login_person($email, $pw)) {
            session_write_close();
            header("Location: " . safe_next_local($next));
            exit;
        }
        $errors[] = "Invalid email or password.";
    }
}
?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Sign in – <?= h(APP_NAME) ?></title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <style>
    body { background: #f0f4f8; }
    .login-header { background: linear-gradient(90deg, #2f4f2f, #4c7a4c); }
  </style>
</head>
<body>
  <div class="login-header d-flex align-items-center justify-content-between px-4 py-3 mb-5">
    <div>
      <div class="text-white fw-bold fs-5"><?= h(APP_NAME) ?></div>
      <?php if ($orgName !== ''): ?>
        <div class="text-white opacity-75 small"><?= h($orgName) ?></div>
      <?php endif; ?>
    </div>
    <?php if (!empty($_orgRow['logo_path'])): ?>
      <img src="/<?= h($_orgRow['logo_path']) ?>" alt="<?= h($orgName) ?> logo" style="max-height:48px; max-width:160px; object-fit:contain;">
    <?php endif; ?>
  </div>

  <div class="container">
    <div class="card shadow-sm mx-auto" style="max-width:480px;">
      <div class="card-body p-4">
        <h1 class="h4 fw-bold mb-4">Sign in</h1>

        <?php if ($errors): ?>
          <div class="alert alert-danger"><ul class="mb-0">
            <?php foreach ($errors as $err): ?><li><?= h($err) ?></li><?php endforeach; ?>
          </ul></div>
        <?php endif; ?>

        <form method="post" action="/login.php" autocomplete="on">
          <input type="hidden" name="next" value="<?= h($next) ?>">
          <label class="form-label">Email</label>
          <input class="form-control" type="email" name="email" required value="<?= h($email) ?>" autocomplete="username">
          <label class="form-label mt-3">Password</label>
          <input class="form-control" type="password" name="password" required autocomplete="current-password">
          <div class="d-flex align-items-center mt-3">
            <button class="btn btn-primary" type="submit">Sign in</button>
          </div>
        </form>
      </div>
    </div>
  </div>
</body>
</html>
