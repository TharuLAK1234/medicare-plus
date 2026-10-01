<?php require VIEW_PATH . '/layouts/header.php'; ?>

<style>
/* Admin portal — dark slate */
.ap-wrap  { min-height:100vh;display:flex;align-items:center;justify-content:center;
            background:#0F172A;padding:2rem 1rem; }
.ap-card  { background:#1E293B;border:1px solid #334155;border-radius:16px;
            box-shadow:0 25px 50px rgba(0,0,0,.5);width:100%;max-width:440px;padding:2.5rem; }
.ap-logo  { display:flex;align-items:center;gap:.625rem;font-family:var(--font-display);
            font-weight:800;font-size:1.2rem;color:#fff;margin-bottom:2rem;
            text-decoration:none; }
.ap-logo-box { width:40px;height:40px;background:#0A6E82;border-radius:10px;
               display:flex;align-items:center;justify-content:center;font-size:1.1rem;color:#fff; }
.ap-badge { display:inline-flex;align-items:center;gap:.4rem;
            background:rgba(239,68,68,.15);color:#FCA5A5;padding:.3rem .75rem;
            border-radius:9999px;font-size:.75rem;font-weight:700;letter-spacing:.05em;
            margin-bottom:1.25rem; }
.ap-heading { font-family:var(--font-display);font-size:1.5rem;font-weight:800;
              color:#F8FAFC;margin-bottom:.375rem; }
.ap-sub { color:#94A3B8;font-size:.875rem;margin-bottom:1.75rem; }
.ap-label { font-family:var(--font-display);font-weight:600;font-size:.85rem;
            color:#CBD5E1;margin-bottom:.375rem;display:block; }
.ap-input { background:#0F172A !important;border:1.5px solid #334155 !important;
            border-radius:8px !important;color:#F1F5F9 !important;
            padding:.65rem .9rem !important;width:100%; }
.ap-input::placeholder { color:#475569 !important; }
.ap-input:focus { border-color:#0A6E82 !important;
                  box-shadow:0 0 0 3px rgba(10,110,130,.2) !important;outline:none !important; }
.ap-input.is-invalid { border-color:#EF4444 !important; }
.ap-btn { background:#0A6E82;border:none;color:#fff;width:100%;padding:.7rem;
          border-radius:8px;font-family:var(--font-display);font-weight:700;
          font-size:1rem;cursor:pointer;transition:background .15s; }
.ap-btn:hover { background:#085E70; }
.ap-eye { background:#0F172A !important;border:1.5px solid #334155 !important;
          color:#64748B !important; }
.ap-eye:hover { background:#1E293B !important;color:#94A3B8 !important; }
.ap-error { background:rgba(239,68,68,.1);border:1px solid rgba(239,68,68,.3);
            color:#FCA5A5;border-radius:8px;padding:.875rem 1.1rem;
            display:flex;align-items:flex-start;gap:.65rem;font-size:.875rem;margin-bottom:1.5rem; }
.ap-portal-links { border-top:1px solid #334155;margin-top:1.75rem;padding-top:1.25rem; }
.ap-portal-links a { color:#64748B;font-size:.8rem;text-decoration:none; }
.ap-portal-links a:hover { color:#94A3B8; }
.ap-dev { background:#0F172A;border:1px dashed #334155;border-radius:8px;
          padding:.875rem;margin-top:1.25rem;font-size:.78rem;color:#64748B; }
.ap-dev code { color:#7DD3FC; }
</style>

<div class="ap-wrap">
  <div>
    <a href="<?= url('') ?>" class="ap-logo" style="justify-content:center;margin-bottom:1.5rem">
      <span class="ap-logo-box"><i class="bi bi-hospital-fill"></i></span>
      MediCarePlus
    </a>

    <div class="ap-card">
      <div class="ap-badge"><i class="bi bi-shield-fill"></i> ADMIN PORTAL</div>
      <h1 class="ap-heading">Administration</h1>
      <p class="ap-sub">Restricted access — authorised personnel only.</p>

      <!-- Error -->
      <?php if (!empty($errors['auth'])): ?>
        <div class="ap-error">
          <i class="bi bi-exclamation-triangle-fill" style="margin-top:1px"></i>
          <div><?= e($errors['auth']) ?></div>
        </div>
      <?php endif; ?>

      <!-- Flash -->
      <?php foreach (['success','info'] as $ft):
        if (!Session::hasFlash($ft)) continue;
      ?>
        <div class="ap-error" style="background:rgba(16,185,129,.1);border-color:rgba(16,185,129,.3);color:#6EE7B7">
          <i class="bi bi-check-circle-fill"></i>
          <div><?= e(Session::getFlash($ft)) ?></div>
        </div>
      <?php endforeach; ?>

      <form method="POST" action="<?= url('admin/login') ?>" novalidate>
        <?= CSRF::field() ?>

        <div style="margin-bottom:1rem">
          <label class="ap-label" for="email">Administrator Email</label>
          <input type="email" id="email" name="email"
                 class="ap-input <?= !empty($errors['email']) ? 'is-invalid' : '' ?>"
                 value="<?= e($old['email'] ?? '') ?>"
                 placeholder="admin@medicare.lk" autocomplete="email" autofocus>
          <?php if (!empty($errors['email'])): ?>
            <div style="color:#FCA5A5;font-size:.8rem;margin-top:.25rem"><?= e($errors['email']) ?></div>
          <?php endif; ?>
        </div>

        <div style="margin-bottom:1.5rem">
          <label class="ap-label" for="password">Password</label>
          <div class="input-group">
            <input type="password" id="password" name="password"
                   class="ap-input <?= !empty($errors['password']) ? 'is-invalid' : '' ?>"
                   placeholder="••••••••" autocomplete="current-password"
                   style="border-radius:8px 0 0 8px !important">
            <button class="btn ap-eye" type="button"
                    onclick="togglePwd(this,'password')" tabindex="-1">
              <i class="bi bi-eye"></i>
            </button>
          </div>
          <?php if (!empty($errors['password'])): ?>
            <div style="color:#FCA5A5;font-size:.8rem;margin-top:.25rem"><?= e($errors['password']) ?></div>
          <?php endif; ?>
        </div>

        <button type="submit" class="ap-btn">
          <i class="bi bi-lock-fill me-1"></i> Sign In Securely
        </button>
      </form>

      <div class="ap-portal-links text-center">
        <a href="<?= url('login') ?>">
          <i class="bi bi-person-heart me-1"></i> Patient Portal
        </a>
        &nbsp;&middot;&nbsp;
        <a href="<?= url('doctor/login') ?>">
          <i class="bi bi-person-badge me-1"></i> Doctor Portal
        </a>
      </div>

      <?php if (defined('APP_ENV') && APP_ENV === 'development'): ?>
        <div class="ap-dev">
          <span style="font-weight:700;color:#94A3B8"><i class="bi bi-bug"></i> Dev</span>&nbsp;&nbsp;
          <code>admin@medicare.lk</code> / <code>Password@123</code>
        </div>
      <?php endif; ?>
    </div>

    <div style="text-align:center;margin-top:1.25rem;font-size:.75rem;color:#334155">
      &copy; <?= date('Y') ?> MediCare Plus &mdash; CSE5009 &middot; ICBT Campus
    </div>
  </div>
</div>

<script>
function togglePwd(btn, id) {
  const inp = document.getElementById(id);
  const ic  = btn.querySelector('i');
  inp.type  = inp.type === 'password' ? 'text' : 'password';
  ic.className = inp.type === 'password' ? 'bi bi-eye' : 'bi bi-eye-slash';
}
</script>

<?php require VIEW_PATH . '/layouts/footer.php'; ?>
