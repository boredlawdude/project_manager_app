<?php
declare(strict_types=1);
?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <title><?= isset($pageTitle) ? h($pageTitle) . ' — ' : '' ?><?= h(APP_NAME) ?></title>
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <style>
    .app-navbar { background: linear-gradient(90deg, #2f4f2f, #4c7a4c); }
    .app-navbar .navbar-brand { color: #fff; font-weight: 600; }
    .app-navbar .nav-link { color: rgba(255,255,255,0.85); }
    .app-navbar .nav-link:hover { color: #fff; }
    .workspace-tabs .nav-link { color: #333; }
    .workspace-tabs .nav-link.active { font-weight: 600; }
  </style>
</head>
<body class="bg-light">

<?php
  try {
      $_orgRow = db()->query('SELECT org_name, logo_path FROM organization_settings ORDER BY id ASC LIMIT 1')->fetch() ?: [];
  } catch (Throwable $e) {
      $_orgRow = [];
  }
  $_orgName = $_orgRow['org_name'] ?? '';
?>
<nav class="navbar navbar-expand-lg app-navbar shadow-sm mb-4">
  <div class="container">
    <a class="navbar-brand d-flex align-items-center gap-2" href="/index.php?page=projects">
      <?php if (!empty($_orgRow['logo_path'])): ?>
        <img src="/<?= h($_orgRow['logo_path']) ?>" alt="logo" style="max-height:28px; max-width:70px; object-fit:contain;">
      <?php endif; ?>
      Project Manager<?= $_orgName !== '' ? ' for ' . h($_orgName) : '' ?>
    </a>
    <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navMain">
      <span class="navbar-toggler-icon"></span>
    </button>
    <div class="collapse navbar-collapse" id="navMain">
      <ul class="navbar-nav me-auto">
        <li class="nav-item"><a class="nav-link" href="/index.php?page=projects">Projects</a></li>
        <?php if (function_exists('is_system_admin') && is_system_admin()): ?>
          <li class="nav-item"><a class="nav-link" href="/index.php?page=admin_settings">Admin</a></li>
        <?php endif; ?>
      </ul>
      <ul class="navbar-nav">
        <?php if (function_exists('current_person') && ($p = current_person())): ?>
          <li class="nav-item"><span class="nav-link"><?= h($p['name'] ?? '') ?></span></li>
          <li class="nav-item"><a class="nav-link" href="/logout.php">Log out</a></li>
        <?php endif; ?>
      </ul>
    </div>
  </div>
</nav>

<div class="container pb-5">
