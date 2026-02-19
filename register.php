<?php
// This file handles both login and register
$isRegister = ($page === 'register');
?>

<section style="min-height: calc(100vh - 70px); display:flex; align-items:center; justify-content:center; padding: 40px 24px; position:relative; overflow:hidden;">
  <!-- Background orbs -->
  <div style="position:absolute; top:-100px; right:-100px; width:400px; height:400px; background:rgba(37,99,235,0.1); border-radius:50%; filter:blur(80px); pointer-events:none;"></div>
  <div style="position:absolute; bottom:-100px; left:-100px; width:300px; height:300px; background:rgba(96,165,250,0.08); border-radius:50%; filter:blur(80px); pointer-events:none;"></div>

  <!-- Grid -->
  <svg style="position:absolute;inset:0;width:100%;height:100%;opacity:0.02;pointer-events:none" xmlns="http://www.w3.org/2000/svg">
    <defs><pattern id="g" width="50" height="50" patternUnits="userSpaceOnUse"><path d="M 50 0 L 0 0 0 50" fill="none" stroke="#3b82f6" stroke-width="1"/></pattern></defs>
    <rect width="100%" height="100%" fill="url(#g)"/>
  </svg>

  <div style="width:100%; max-width:420px; position:relative; z-index:1;">
    <!-- Logo -->
    <div style="text-align:center; margin-bottom:36px; animation: fadeInUp 0.5s ease forwards;">
      <a href="index.php" style="display:inline-flex; align-items:center; gap:10px; text-decoration:none;">
        <div style="width:44px;height:44px;background:linear-gradient(135deg,#2563eb,#1d4ed8);border-radius:10px;display:flex;align-items:center;justify-content:center;font-size:1.4rem;">⚡</div>
        <span style="font-family:var(--font-display);font-size:1.5rem;font-weight:800;background:linear-gradient(135deg,#f0f6ff,#93c5fd);-webkit-background-clip:text;-webkit-text-fill-color:transparent;background-clip:text;">NEXUS</span>
      </a>
    </div>

    <!-- Card -->
    <div style="
      background: rgba(13,21,41,0.8);
      backdrop-filter: blur(20px);
      border: 1px solid rgba(59,130,246,0.15);
      border-radius: 20px;
      padding: 36px 32px;
      animation: fadeInUp 0.5s ease 0.1s forwards;
      opacity: 0;
    ">
      <div style="margin-bottom:28px;">
        <h2 style="font-size:1.6rem; font-weight:800; margin-bottom:6px;">
          <?= $isRegister ? 'Buat Akun' : 'Selamat Datang' ?>
        </h2>
        <p style="font-size:0.9rem; color:var(--text-muted);">
          <?= $isRegister ? 'Daftar dan mulai top up sekarang' : 'Login ke akun Nexus Top Up kamu' ?>
        </p>
      </div>

      <form action="php/auth.php" method="POST">
        <input type="hidden" name="action" value="<?= $isRegister ? 'register' : 'login' ?>">

        <?php if ($isRegister): ?>
        <div class="form-group">
          <label class="form-label">Username</label>
          <input type="text" name="username" class="form-control" placeholder="contoh: gamerpro123" required autocomplete="username">
        </div>
        <?php endif; ?>

        <div class="form-group">
          <label class="form-label">Email</label>
          <input type="email" name="email" class="form-control" placeholder="email@kamu.com" required autocomplete="email">
        </div>

        <div class="form-group" style="position:relative;">
          <label class="form-label">Password</label>
          <input type="password" name="password" id="pw-input" class="form-control" placeholder="••••••••" required autocomplete="<?= $isRegister ? 'new-password' : 'current-password' ?>" style="padding-right:48px;">
          <button type="button" onclick="togglePw()" style="position:absolute;right:14px;top:38px;background:none;border:none;color:var(--text-muted);cursor:pointer;font-size:1rem;">👁</button>
        </div>

        <?php if ($isRegister): ?>
        <div class="form-group">
          <label class="form-label">Konfirmasi Password</label>
          <input type="password" name="confirm_password" class="form-control" placeholder="••••••••" required autocomplete="new-password">
        </div>
        <?php endif; ?>

        <button type="submit" class="btn btn-primary btn-full" style="margin-top:8px; padding:13px; font-size:0.95rem;">
          <?= $isRegister ? '🚀 Buat Akun' : '⚡ Login' ?>
        </button>
      </form>

      <div style="margin-top:24px; padding-top:24px; border-top:1px solid var(--dark-border); text-align:center;">
        <p style="font-size:0.88rem; color:var(--text-muted);">
          <?php if ($isRegister): ?>
            Sudah punya akun? <a href="index.php?page=login" style="color:var(--blue-400); font-weight:600;">Login di sini</a>
          <?php else: ?>
            Belum punya akun? <a href="index.php?page=register" style="color:var(--blue-400); font-weight:600;">Daftar gratis</a>
          <?php endif; ?>
        </p>
      </div>
    </div>
  </div>
</section>

<script>
function togglePw() {
  const input = document.getElementById('pw-input');
  input.type = input.type === 'password' ? 'text' : 'password';
}
</script>
