<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Admin - <?= $pageTitle ?? 'Dashboard' ?> | Nexus Top Up</title>
  <link rel="stylesheet" href="../css/style.css">
  <link rel="stylesheet" href="../css/admin.css">
  <link rel="icon" href="data:image/svg+xml,<svg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 100 100'><text y='.9em' font-size='90'>⚡</text></svg>">
</head>
<body>

<?php $flash = getFlash(); if ($flash): ?>
  <div class="flash-message flash-<?= $flash['type'] ?>">
    <span><?= $flash['type'] === 'success' ? '✓' : '✕' ?></span>
    <span><?= htmlspecialchars($flash['message']) ?></span>
  </div>
<?php endif; ?>

<!-- TOP NAVBAR -->
<nav class="navbar scrolled">
  <div class="navbar-inner">
    <div style="display:flex;align-items:center;gap:14px;">
      <button class="mobile-menu-btn" id="sidebar-toggle" style="display:none;">☰</button>
      <a href="index.php" class="navbar-logo">
        <div class="logo-icon">⚡</div>
        <span>NEXUS <span style="font-size:0.75em;opacity:0.6;font-weight:500;">ADMIN</span></span>
      </a>
    </div>

    <div style="display:flex;align-items:center;gap:12px;">
      <a href="../index.php" target="_blank" class="btn btn-ghost btn-sm">🌐 View Site</a>
      <span style="font-size:0.85rem;color:var(--text-muted);">👤 <?= htmlspecialchars($_SESSION['username']) ?></span>
      <form action="../php/auth.php" method="POST" style="display:inline">
        <input type="hidden" name="action" value="logout">
        <button type="submit" class="btn btn-outline btn-sm">Logout</button>
      </form>
    </div>
  </div>
</nav>

<!-- ADMIN LAYOUT -->
<div class="admin-layout">
  <!-- SIDEBAR -->
  <aside class="admin-sidebar">
    <div class="admin-sidebar-inner">
      <div class="sidebar-section-label">Overview</div>
      <ul class="sidebar-nav">
        <li>
          <a href="index.php" class="<?= ($page ?? '') === 'dashboard' ? 'active' : '' ?>">
            <span class="nav-icon">📊</span> Dashboard
          </a>
        </li>
        <li>
          <a href="transactions.php" class="<?= ($page ?? '') === 'transactions' ? 'active' : '' ?>">
            <span class="nav-icon">💳</span> Transaksi
          </a>
        </li>
      </ul>

      <div class="sidebar-section-label">Konten</div>
      <ul class="sidebar-nav">
        <li>
          <a href="games.php" class="<?= ($page ?? '') === 'games' ? 'active' : '' ?>">
            <span class="nav-icon">🎮</span> Kelola Game
          </a>
        </li>
        <li>
          <a href="packages.php" class="<?= ($page ?? '') === 'packages' ? 'active' : '' ?>">
            <span class="nav-icon">💎</span> Paket Top Up
          </a>
        </li>
        <li>
          <a href="payments.php" class="<?= ($page ?? '') === 'payments' ? 'active' : '' ?>">
            <span class="nav-icon">💳</span> Metode Bayar
          </a>
        </li>
      </ul>

      <div class="sidebar-section-label">Manajemen</div>
      <ul class="sidebar-nav">
        <li>
          <a href="bank.php" class="<?= ($page ?? '') === 'bank' ? 'active' : '' ?>">
            <span class="nav-icon">🏦</span> Info Rekening
          </a>
        </li>
        <li>
          <a href="users.php" class="<?= ($page ?? '') === 'users' ? 'active' : '' ?>">
            <span class="nav-icon">👥</span> Pengguna
          </a>
        </li>
      </ul>
    </div>
  </aside>

  <!-- MAIN CONTENT -->
  <main class="admin-main">
    <div class="admin-page-header">
      <div>
        <div class="admin-page-title"><?= $pageTitle ?? 'Dashboard' ?></div>
        <div class="admin-page-sub"><?= $pageSubtitle ?? '' ?></div>
      </div>
      <?php if (isset($headerAction)): echo $headerAction; endif; ?>
    </div>
