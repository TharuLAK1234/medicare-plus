<?php require VIEW_PATH . '/layouts/header.php'; ?>

<style>
/* Doctor portal — deep blue palette */
.dp-left  { background: linear-gradient(155deg, #1E3A8A 0%, #1D4ED8 100%); }
.dp-badge { background: rgba(255,255,255,.15); color: rgba(255,255,255,.95);
            display:inline-flex;align-items:center;gap:.4rem;padding:.35rem .85rem;
            border-radius:9999px;font-size:.75rem;font-weight:700;letter-spacing:.06em;
            margin-top:1.75rem; }
.dp-link  { display:block;color:rgba(255,255,255,.8);font-size:.875rem;
            text-decoration:none;padding:.35rem 0; }
.dp-link:hover { color:#fff; }
.dp-pill  { display:inline-flex;align-items:center;gap:.4rem;
            background:#EEF2FF;color:#3730A3;padding:.3rem .75rem;
            border-radius:9999px;font-size:.75rem;font-weight:700;margin-bottom:.875rem; }
.btn-doctor {
  background: #1D4ED8 !important;
  border-color: #1D4ED8 !important;
  color: #fff !important;
}
.btn-doctor:hover { background: #1E3A8A !important; border-color: #1E3A8A !important; }
.form-control:focus { border-color: #1D4ED8; box-shadow: 0 0 0 3px rgba(29,78,216,.15); }
a { color: #1D4ED8; }
</style>

<div class="mp-auth-wrap">

  <!-- ── Left panel: deep blue ── -->
  <div class="mp-auth-left dp-left">
    <div>
      <a href="<?= url('') ?>" class="auth-brand-logo" style="text-decoration:none;color:#fff">
        <span class="logo-box" style="background:rgba(255,255,255,.2)">
          <i class="bi bi-hospital-fill"></i>
        </span>
        MediCarePlus
      </a>

      <div class="dp-badge"><i class="bi bi-person-badge-fill"></i> DOCTOR PORTAL</div>

      <p class="auth-tagline" style="margin-top:1.5rem">
        Empowering clinicians<br>to deliver better care.
      </p>
      <p class="auth-sub">
        Access your appointment schedule, communicate with patients securely,
        and manage your clinical profile — all from one dashboard.
      </p>

      <ul class="auth-trust-list">
        <li><i class="bi bi-calendar3-fill"></i> Real-time appointment schedule</li>
        <li><i class="bi bi-people-fill"></i> Patient management tools</li>
        <li><i class="bi bi-chat-square-dots-fill"></i> Secure patient messaging</li>
        <li><i class="bi bi-file-earmark-medical-fill"></i> Report upload &amp; review</li>
      </ul>

      <div style="margin-top:2rem;padding:1rem;background:rgba(255,255,255,.08);border-radius:12px">
        <div style="font-size:.8rem;color:rgba(255,255,255,.6);margin-bottom:.5rem">
          Other portals
        </div>
        <a href="<?= url('login') ?>" class="dp-link" style="border-bottom:1px solid rgba(255,255,255,.1)">
          <i class="bi bi-person-heart-fill me-2"></i> Patient Portal →
        </a>
        <a href="<?= url('admin/login') ?>" class="dp-link">
          <i class="bi bi-shield-fill me-2"></i> Admin Portal →
        </a>
      </div>
    </div>

    <div class="auth-foot">&copy; <?= date('Y') ?> MediCare Plus &mdash; CSE5009</div>
  </div>

  <!-- ── Right panel ── -->
  <div class="mp-auth-right">
    <div class="mp-auth-box">

      <div style="margin-bottom:1.75rem">
        <div class="dp-pill"><i class="bi bi-person-badge-fill"></i> Doctor Portal</div>
        <h1 class="auth-heading">Doctor Sign In</h1>
        <p class="auth-sub-heading">Access your MediCare Plus clinical dashboard</p>
      </div>

      <!-- Error banner -->
      <?php if (!empty($errors['auth'])): ?>
        <div class="mp-flash error mb-4">
          <i class="bi bi-x-circle-fill"></i>
          <div><?= e($errors['auth']) ?></div>
        </div>
      <?php endif; ?>

      <!-- Flash -->
      <?php foreach (['success','info'] as $ft):
        if (!Session::hasFlash($ft)) continue;
      ?>
        <div class="mp-flash <?= $ft ?> mb-4">
          <i class="bi bi-<?= $ft === 'success' ? 'check-circle-fill' : 'info-circle-fill' ?>"></i>
          <div><?= e(Session::getFlash($ft)) ?></div>
        </div>
      <?php endforeach; ?>

      <form method="POST" action="<?= url('doctor/login') ?>" novalidate>
        <?= CSRF::field() ?>

        <div class="mb-3">
          <label class="form-label" for="email">Registered Email</label>
          <input type="email" id="email" name="email"
                 class="form-control <?= !empty($errors['email']) ? 'is-invalid' : '' ?>"
                 value="<?= e($old['email'] ?? '') ?>"
                 placeholder="doctor@medicare.lk" autocomplete="email" autofocus>
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

        <button type="submit" class="btn btn-doctor w-100 py-2 mb-4" style="font-size:1rem;font-family:var(--font-display);font-weight:600;border-radius:8px">
          Sign In to Doctor Portal <i class="bi bi-arrow-right-short"></i>
        </button>
      </form>

      <div class="text-center" style="font-size:.875rem;color:var(--mp-text-3)">
        Not a registered doctor?
        <a href="<?= url('') ?>" style="font-weight:600">Contact admin</a>
        to get access.
      </div>

      <?php if (defined('APP_ENV') && APP_ENV === 'development'): ?>
        <div class="mt-4 p-3 rounded-3" style="background:#f8fafc;border:1px dashed #cbd5e1;font-size:.78rem">
          <div style="font-weight:700;color:var(--mp-text-1);margin-bottom:.4rem">
            <i class="bi bi-bug"></i> Dev — Doctor credentials
          </div>
          <code>amara@medicare.lk</code> &nbsp;|&nbsp;
          <code>nimal@medicare.lk</code> &nbsp;|&nbsp;
          <code>kumari@medicare.lk</code><br>
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
