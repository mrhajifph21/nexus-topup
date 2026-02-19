<?php
require_once 'php/config.php';

$page = $_GET['page'] ?? 'home';
$allowed = ['home', 'login', 'register', 'games', 'topup', 'checkout', 'payment', 'history', 'profile'];

if (!in_array($page, $allowed)) {
    $page = 'home';
}

// Protect certain pages
if (in_array($page, ['checkout', 'payment', 'history', 'profile']) && !isLoggedIn()) {
    header('Location: index.php?page=login');
    exit;
}

// Redirect logged in users away from login/register
if (in_array($page, ['login', 'register']) && isLoggedIn()) {
    header('Location: index.php');
    exit;
}

$db = getDB();
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Nexus Top Up<?php echo $page !== 'home' ? ' - ' . ucfirst($page) : ''; ?></title>
  <link rel="stylesheet" href="css/style.css">
  <link rel="icon" href="data:image/svg+xml,<svg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 100 100'><text y='.9em' font-size='90'>⚡</text></svg>">
</head>
<body>
  <?php
  $flash = getFlash();
  if ($flash): ?>
    <div class="flash-message flash-<?= $flash['type'] ?>">
      <span><?= $flash['type'] === 'success' ? '✓' : '✕' ?></span>
      <span><?= htmlspecialchars($flash['message']) ?></span>
    </div>
  <?php endif; ?>

  <!-- NAVBAR -->
  <nav class="navbar">
    <div class="navbar-inner">
      <a href="index.php" class="navbar-logo">
        <div class="logo-icon">⚡</div>
        <span>NEXUS</span>
      </a>

      <ul class="navbar-nav">
        <li><a href="index.php" class="<?= $page === 'home' ? 'active' : '' ?>">Home</a></li>
        <li><a href="index.php?page=games" class="<?= $page === 'games' ? 'active' : '' ?>">Games</a></li>
        <?php if (isLoggedIn()): ?>
        <li><a href="index.php?page=history" class="<?= $page === 'history' ? 'active' : '' ?>">Riwayat</a></li>
        <?php endif; ?>
      </ul>

      <div class="navbar-actions">
        <?php if (isLoggedIn()): ?>
          <a href="index.php?page=profile" class="btn btn-ghost btn-sm">👤 <?= htmlspecialchars($_SESSION['username']) ?></a>
          <form action="php/auth.php" method="POST" style="display:inline">
            <input type="hidden" name="action" value="logout">
            <button type="submit" class="btn btn-outline btn-sm">Logout</button>
          </form>
        <?php else: ?>
          <a href="index.php?page=login" class="btn btn-ghost btn-sm">Login</a>
          <a href="index.php?page=register" class="btn btn-primary btn-sm">Daftar</a>
        <?php endif; ?>
      </div>
    </div>
  </nav>

  <!-- PAGE CONTENT -->
  <div class="page-wrapper">
    <?php include "pages/{$page}.php"; ?>
  </div>

  <!-- FOOTER -->
  <footer class="footer">
    <div class="container">
      <div class="footer-grid">
        <div>
          <div class="footer-brand-name"><span class="text-gradient">NEXUS</span> Top Up</div>
          <p class="footer-desc">Platform top up game terpercaya dengan proses cepat, aman, dan harga terjangkau.</p>
        </div>
        <div>
          <div class="footer-heading">Navigasi</div>
          <ul class="footer-links">
            <li><a href="index.php">Home</a></li>
            <li><a href="index.php?page=games">Games</a></li>
            <li><a href="index.php?page=history">Riwayat</a></li>
          </ul>
        </div>
        <div>
          <div class="footer-heading">Akun</div>
          <ul class="footer-links">
            <li><a href="index.php?page=login">Login</a></li>
            <li><a href="index.php?page=register">Daftar</a></li>
            <li><a href="index.php?page=profile">Profil</a></li>
          </ul>
        </div>
        <div>
          <div class="footer-heading">Dukungan</div>
          <ul class="footer-links">
            <li><a href="#">FAQ</a></li>
            <li><a href="#">Kontak</a></li>
            <li><a href="#">Kebijakan</a></li>
          </ul>
        </div>
      </div>
      <div class="footer-bottom">
        <span>© 2024 Nexus Top Up. All rights reserved.</span>
        <span>Made with ⚡ for gamers</span>
      </div>
    </div>
  </footer>

  <script src="js/main.js"></script>
</body>
</html>
