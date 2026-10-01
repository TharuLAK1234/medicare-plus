<?php require VIEW_PATH . '/layouts/header.php'; ?>

<div class="mp-auth-wrap">

  <!-- ── Left panel ── -->
  <div class="mp-auth-left" style="background:linear-gradient(155deg,#0A6E82 0%,#0D9488 100%)">
    <div>
      <a href="<?= url('') ?>" class="auth-brand-logo" style="text-decoration:none;color:#fff">
        <span class="logo-box"><i class="bi bi-hospital-fill"></i></span>
        MediCarePlus
      </a>

      <div style="margin-top:2rem;display:inline-flex;align-items:center;gap:.5rem;
                  background:rgba(255,255,255,.15);padding:.4rem .9rem;border-radius:9999px;
                  font-size:.8rem;font-weight:700;letter-spacing:.05em;color:rgba(255,255,255,.9)">
        <i class="bi bi-person-heart-fill"></i> PATIENT PORTAL
      </div>

      <p class="auth-tagline" style="margin-top:1.5rem">
        Manage your health,<br>your way.
      </p>
      <p class="auth-sub">
        Book appointments with top specialists, access your medical reports,
        and message your care team — all in one secure place.
      </p>

      <ul class="auth-trust-list">
        <li><i class="bi bi-calendar2-check-fill"></i> Online appointment booking</li>
        <li><i class="bi bi-file-earmark-medical-fill"></i> Secure health reports</li>
        <li><i class="bi bi-chat-dots-fill"></i> Direct doctor messaging</li>
        <li><i class="bi bi-shield-lock-fill"></i> HIPAA-standard data security</li>
      </ul>

      <div style="margin-top:2rem;padding:1rem;background:rgba(255,255,255,.1);border-radius:12px">
        <div style="font-size:.8rem;color:rgba(255,255,255,.7);margin-bottom:.5rem">
          Other portals
        </div>
        <a href="<?= url('doctor/login') ?>" style="display:block;color:rgba(255,255,255,.85);font-size:.875rem;text-decoration:none;padding:.35rem 0;border-bottom:1px solid rgba(255,255,255,.1)">
          <i class="bi bi-person-badge-fill me-2"></i> Doctor Portal →
        </a>
        <a href="<?= url('admin/login') ?>" style="display:block;color:rgba(255,255,255,.85);font-size:.875rem;text-decoration:none;padding:.35rem 0">
          <i class="bi bi-shield-fill me-2"></i> Admin Portal →
        </a>
      </div>
    </div>

    <div class="auth-foot">&copy; <?= date('Y') ?> MediCare Plus &mdash; CSE5009</div>
  </div>

  <!-- ── Right panel: form ── -->
  <div class="mp-auth-right">
    <div class="mp-auth-box">

      <div style="margin-bottom:1.75rem">
        <div style="display:inline-flex;align-items:center;gap:.4rem;
                    background:#E8F6F9;color:#0A6E82;padding:.3rem .75rem;
                    border-radius:9999px;font-size:.75rem;font-weight:700;margin-bottom:.875rem">
          <i class="bi bi-person-heart-fill"></i> Patient Portal
        </div>
        <h1 class="auth-heading">Patient Sign In</h1>
        <p class="auth-sub-heading">Access your MediCare Plus patient account</p>
      </div>

      <!-- Error banner -->
      <?php if (!empty($errors['auth'])): ?>
        <div class="mp-flash error mb-4">
          <i class="bi bi-x-circle-fill"></i>
          <div><?= e($errors['auth']) ?></div>
        </div>
      <?php endif; ?>

      <!-- Flash messages -->
      <?php foreach (['success','info'] as $ft):
        if (!Session::hasFlash($ft)) continue;
        $icon = $ft === 'success' ? 'check-circle-fill' : 'info-circle-fill';
      ?>
        <div class="mp-flash <?= $ft ?> mb-4">
          <i class="bi bi-<?= $icon ?>"></i>
          <div><?= e(Session::getFlash($ft)) ?></div>
        </div>
      <?php endforeach; ?>

      <form method="POST" action="<?= url('login') ?>" novalidate>
        <?= CSRF::field() ?>

        <div class="mb-3">
          <label class="form-label" for="email">Email Address</label>
          <input type="email" id="email" name="email"
                 class="form-control <?= !empty($errors['email']) ? 'is-invalid' : '' ?>"
                 value="<?= e($old['email'] ?? '') ?>"
                 placeholder="your@email.com" autocomplete="email" autofocus>
          <?php if (!empty($errors['email'])): ?>
            <div class="invalid-feedback"><?= e($errors['email']) ?></div>
          <?php endif; ?>
        </div>

        <div class="mb-4">
          <label class="form-label" for="password">Password</label>
          <div class="input-group">
            <input type="password" id="password" name="password"
                   class="form-control <?= !empty($errors['password']) ? 'is-invalid' : '' ?>"
                   placeholder="••••••••" autocomplete="current-password">
            <button class="btn btn-outline-secondary" type="button"
                    onclick="togglePwd(this,'password')" tabindex="-1">
              <i class="bi bi-eye"></i>
            </button>
          </div>
          <?php if (!empty($errors['password'])): ?>
            <div class="invalid-feedback d-block"><?= e($errors['password']) ?></div>
          <?php endif; ?>
        </div>

        <button type="submit" class="btn btn-primary w-100 py-2 mb-4" style="font-size:1rem">
          Sign In to Patient Portal <i class="bi bi-arrow-right-short"></i>
        </button>
      </form>

      <div class="text-center" style="font-size:.875rem;color:var(--mp-text-3)">
        New patient?
        <a href="<?= url('register') ?>" style="color:var(--mp-primary);font-weight:600">Create a free account</a>
      </div>

      <?php if (defined('APP_ENV') && APP_ENV === 'development'): ?>
        <div class="mt-4 p-3 rounded-3" style="background:#f8fafc;border:1px dashed #cbd5e1;font-size:.78rem">
          <div style="font-weight:700;color:var(--mp-text-1);margin-bottom:.4rem">
            <i class="bi bi-bug"></i> Dev — Patient credentials
          </div>
          <code>kasun@gmail.com</code> &nbsp;|&nbsp;
          <code>amali@gmail.com</code> &nbsp;|&nbsp;
          <code>ruwan@gmail.com</code><br>
          Password: <code>Password@123</code>
        </div>
      <?php endif; ?>

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
