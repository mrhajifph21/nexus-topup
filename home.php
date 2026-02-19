<?php
// Fetch games from database
$stmt = $db->prepare("SELECT * FROM games WHERE is_active = 1 LIMIT 5");
$stmt->execute();
$games = $stmt->fetchAll();

$gameEmojis = ['⚔️', '🔥', '🎯', '✨', '🎮'];
?>

<!-- HERO SECTION -->
<section class="hero">
  <div class="hero-bg">
    <div class="hero-orb hero-orb-1"></div>
    <div class="hero-orb hero-orb-2"></div>
    <!-- Grid pattern -->
    <svg style="position:absolute;inset:0;width:100%;height:100%;opacity:0.03" xmlns="http://www.w3.org/2000/svg">
      <defs>
        <pattern id="grid" width="60" height="60" patternUnits="userSpaceOnUse">
          <path d="M 60 0 L 0 0 0 60" fill="none" stroke="#3b82f6" stroke-width="1"/>
        </pattern>
      </defs>
      <rect width="100%" height="100%" fill="url(#grid)" />
    </svg>
  </div>

  <div class="container">
    <div style="max-width: 640px">
      <div class="hero-badge">
        <div class="glow-dot"></div>
        Platform Top Up #1 Terpercaya
      </div>

      <h1 class="hero-title">
        Top Up Game<br>
        <span class="text-gradient">Lebih Cepat,</span><br>
        Lebih Murah.
      </h1>

      <p class="hero-subtitle">
        Isi ulang diamond, UC, crystal & mata uang game favorit kamu dalam hitungan detik. Aman, mudah, dan harga terbaik.
      </p>

      <div class="hero-actions">
        <a href="index.php?page=games" class="btn btn-primary btn-lg">
          ⚡ Mulai Top Up
        </a>
        <a href="#games" class="btn btn-outline btn-lg">
          Lihat Games
        </a>
      </div>

      <div class="hero-stats">
        <div>
          <div class="hero-stat-value" data-count="50000" data-suffix="+">0+</div>
          <div class="hero-stat-label">Transaksi Sukses</div>
        </div>
        <div>
          <div class="hero-stat-value" data-count="5">0</div>
          <div class="hero-stat-label">Game Tersedia</div>
        </div>
        <div>
          <div class="hero-stat-value" data-count="99" data-suffix="%">0%</div>
          <div class="hero-stat-label">Kepuasan Pelanggan</div>
        </div>
      </div>
    </div>
  </div>
</section>

<!-- FEATURES SECTION -->
<section class="section" style="background: rgba(13,21,41,0.5); border-top: 1px solid var(--dark-border); border-bottom: 1px solid var(--dark-border);">
  <div class="container">
    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(240px, 1fr)); gap: 32px;">
      <?php
      $features = [
        ['⚡', 'Proses Instan', 'Top up diproses dalam hitungan detik, langsung masuk ke akun game kamu.'],
        ['🔒', 'Aman & Terpercaya', 'Transaksi dijamin keamanannya dengan enkripsi dan sistem verifikasi berlapis.'],
        ['💰', 'Harga Terjangkau', 'Dapatkan harga terbaik dengan berbagai pilihan paket yang sesuai budget kamu.'],
        ['🎮', 'Banyak Game', 'Mendukung berbagai game populer dengan update koleksi secara rutin.'],
      ];
      foreach ($features as $f): ?>
        <div class="card reveal" style="text-align:center; padding: 32px 24px;">
          <div style="font-size:2.5rem; margin-bottom:16px;"><?= $f[0] ?></div>
          <h3 style="font-size:1.1rem; margin-bottom:8px;"><?= $f[1] ?></h3>
          <p style="font-size:0.88rem; color:var(--text-muted); line-height:1.7;"><?= $f[2] ?></p>
        </div>
      <?php endforeach; ?>
    </div>
  </div>
</section>

<!-- GAMES SECTION -->
<section class="section" id="games">
  <div class="container">
    <div class="section-header flex justify-between items-center">
      <div>
        <div class="section-tag">🎮 Games</div>
        <h2 class="section-title">Pilih Game Favoritmu</h2>
        <p class="section-desc">Top up berbagai game populer dengan mudah dan cepat.</p>
      </div>
      <a href="index.php?page=games" class="btn btn-outline">Lihat Semua →</a>
    </div>

    <div class="games-grid">
      <?php foreach ($games as $i => $game): ?>
        <a href="index.php?page=topup&game=<?= htmlspecialchars($game['slug']) ?>" class="game-card">
          <div class="game-card-thumb">
            <?php if ($game['thumbnail']): ?>
              <img src="<?= htmlspecialchars($game['thumbnail']) ?>" alt="<?= htmlspecialchars($game['name']) ?>" loading="lazy">
            <?php else: ?>
              <div class="game-card-thumb-placeholder" style="display:flex;align-items:center;justify-content:center;width:100%;height:100%;">
                <?= $gameEmojis[$i % count($gameEmojis)] ?>
              </div>
            <?php endif; ?>
            <div class="game-card-overlay">
              <span class="btn btn-primary btn-sm">Top Up Sekarang</span>
            </div>
          </div>
          <div class="game-card-body">
            <div class="game-card-category"><?= htmlspecialchars($game['category']) ?></div>
            <div class="game-card-name"><?= htmlspecialchars($game['name']) ?></div>
            <div class="game-card-dev"><?= htmlspecialchars($game['developer']) ?></div>
          </div>
        </a>
      <?php endforeach; ?>
    </div>
  </div>
</section>

<!-- HOW TO SECTION -->
<section class="section" style="background: rgba(13,21,41,0.5); border-top: 1px solid var(--dark-border);">
  <div class="container">
    <div class="section-header text-center">
      <div class="section-tag">📋 Cara Mudah</div>
      <h2 class="section-title">Cara Top Up</h2>
    </div>

    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 24px; max-width: 900px; margin: 0 auto;">
      <?php
      $steps = [
        ['1', '🎮', 'Pilih Game', 'Pilih game yang ingin kamu top up dari daftar game tersedia'],
        ['2', '💎', 'Pilih Paket', 'Tentukan jumlah diamond/UC/crystal sesuai kebutuhanmu'],
        ['3', '🪪', 'Isi Data', 'Masukkan User ID dan informasi akun game kamu'],
        ['4', '💳', 'Bayar', 'Lakukan pembayaran dengan metode yang tersedia'],
      ];
      foreach ($steps as $s): ?>
        <div style="text-align:center;">
          <div style="
            width: 60px; height: 60px;
            background: rgba(37,99,235,0.1);
            border: 1.5px solid rgba(37,99,235,0.3);
            border-radius: 50%;
            display: flex; align-items: center; justify-content: center;
            font-size: 1.6rem;
            margin: 0 auto 16px;
          "><?= $s[2] === 'Pilih Game' ? '🎮' : ($s[2] === 'Pilih Paket' ? '💎' : ($s[2] === 'Isi Data' ? '🪪' : '💳')) ?></div>
          <div style="
            display: inline-block;
            font-size: 0.7rem;
            font-weight: 700;
            color: var(--blue-400);
            text-transform: uppercase;
            letter-spacing: 0.08em;
            margin-bottom: 8px;
          ">Langkah <?= $s[0] ?></div>
          <h4 style="font-size:1rem; margin-bottom:8px;"><?= $s[2] ?></h4>
          <p style="font-size:0.83rem; color:var(--text-muted); line-height:1.6;"><?= $s[3] ?></p>
        </div>
      <?php endforeach; ?>
    </div>
  </div>
</section>

<style>
.reveal {
  opacity: 0;
  transform: translateY(24px);
  transition: opacity 0.6s ease, transform 0.6s ease;
}
.reveal.revealed {
  opacity: 1;
  transform: translateY(0);
}
</style>
