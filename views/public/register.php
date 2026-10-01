<?php require VIEW_PATH . '/layouts/header.php'; ?>

<div class="mp-register-wrap">
  <div class="mp-register-card">

    <!-- Header -->
    <div class="text-center mb-4">
      <a href="<?= url('') ?>" style="text-decoration:none">
        <div style="font-family:var(--font-display);font-weight:800;font-size:1.5rem;color:var(--mp-primary)">
          <i class="bi bi-hospital-fill"></i> MediCarePlus
        </div>
      </a>
      <h1 style="font-size:1.5rem;margin-top:.75rem">Create your account</h1>
      <p style="color:var(--mp-text-3);font-size:.9rem">
        Join thousands of patients managing their healthcare online.
      </p>
    </div>

    <!-- General error banner -->
    <?php if (!empty($errors['general'])): ?>
      <div class="mp-flash error mb-4">
        <i class="bi bi-x-circle-fill"></i>
        <div><?= e($errors['general']) ?></div>
      </div>
    <?php endif; ?>

    <form method="POST" action="<?= url('register') ?>" novalidate>
      <?= CSRF::field() ?>

      <!-- ── Section 1: Account ── -->
      <div class="section-divider"><i class="bi bi-person-fill"></i> &nbsp;Account Details</div>

      <div class="row g-3 mb-4">
        <div class="col-md-6">
          <label class="form-label" for="name">Full Name <span class="text-danger">*</span></label>
          <input type="text" id="name" name="name"
                 class="form-control <?= !empty($errors['name']) ? 'is-invalid' : '' ?>"
                 value="<?= e($old['name'] ?? '') ?>"
                 placeholder="e.g. Kasun Perera" autocomplete="name" autofocus>
          <?php if (!empty($errors['name'])): ?>
            <div class="invalid-feedback"><?= e($errors['name']) ?></div>
          <?php endif; ?>
        </div>

        <div class="col-md-6">
          <label class="form-label" for="email">Email Address <span class="text-danger">*</span></label>
          <input type="email" id="email" name="email"
                 class="form-control <?= !empty($errors['email']) ? 'is-invalid' : '' ?>"
                 value="<?= e($old['email'] ?? '') ?>"
                 placeholder="you@example.com" autocomplete="email">
          <?php if (!empty($errors['email'])): ?>
            <div class="invalid-feedback"><?= e($errors['email']) ?></div>
          <?php endif; ?>
        </div>

        <div class="col-md-6">
          <label class="form-label" for="phone">Phone Number</label>
          <input type="tel" id="phone" name="phone"
                 class="form-control <?= !empty($errors['phone']) ? 'is-invalid' : '' ?>"
                 value="<?= e($old['phone'] ?? '') ?>"
                 placeholder="+94 77 123 4567" autocomplete="tel">
          <?php if (!empty($errors['phone'])): ?>
            <div class="invalid-feedback"><?= e($errors['phone']) ?></div>
          <?php endif; ?>
        </div>

        <div class="col-md-6">
          <!-- empty col for grid alignment on desktop -->
        </div>

        <div class="col-md-6">
          <label class="form-label" for="password">Password <span class="text-danger">*</span></label>
          <div class="input-group">
            <input type="password" id="password" name="password"
                   class="form-control <?= !empty($errors['password']) ? 'is-invalid' : '' ?>"
                   placeholder="Min 8 characters" autocomplete="new-password">
            <button class="btn btn-outline-secondary" type="button"
                    onclick="togglePwd(this,'password')" tabindex="-1">
              <i class="bi bi-eye"></i>
            </button>
          </div>
          <?php if (!empty($errors['password'])): ?>
            <div class="invalid-feedback d-block"><?= e($errors['password']) ?></div>
          <?php endif; ?>
          <div class="form-text">Use at least 8 characters with letters and numbers.</div>
        </div>

        <div class="col-md-6">
          <label class="form-label" for="password_confirm">Confirm Password <span class="text-danger">*</span></label>
          <div class="input-group">
            <input type="password" id="password_confirm" name="password_confirm"
                   class="form-control <?= !empty($errors['password_confirm']) ? 'is-invalid' : '' ?>"
                   placeholder="Repeat password" autocomplete="new-password">
            <button class="btn btn-outline-secondary" type="button"
                    onclick="togglePwd(this,'password_confirm')" tabindex="-1">
              <i class="bi bi-eye"></i>
            </button>
          </div>
          <?php if (!empty($errors['password_confirm'])): ?>
            <div class="invalid-feedback d-block"><?= e($errors['password_confirm']) ?></div>
          <?php endif; ?>
        </div>
      </div>

      <!-- ── Section 2: Health Profile ── -->
      <div class="section-divider"><i class="bi bi-heart-pulse-fill"></i> &nbsp;Health Profile <span style="font-weight:400;text-transform:none;letter-spacing:0;font-size:.75rem;color:var(--mp-text-3)">(optional — can be updated later)</span></div>

      <div class="row g-3 mb-4">
        <div class="col-md-4">
          <label class="form-label" for="dob">Date of Birth</label>
          <input type="date" id="dob" name="dob"
                 class="form-control <?= !empty($errors['dob']) ? 'is-invalid' : '' ?>"
                 value="<?= e($old['dob'] ?? '') ?>">
          <?php if (!empty($errors['dob'])): ?>
            <div class="invalid-feedback"><?= e($errors['dob']) ?></div>
          <?php endif; ?>
        </div>

        <div class="col-md-4">
          <label class="form-label" for="gender">Gender</label>
          <select id="gender" name="gender"
                  class="form-select <?= !empty($errors['gender']) ? 'is-invalid' : '' ?>">
            <option value="">-- Select --</option>
            <?php foreach (['male' => 'Male','female' => 'Female','other' => 'Other'] as $v => $l): ?>
              <option value="<?= $v ?>" <?= ($old['gender'] ?? '') === $v ? 'selected' : '' ?>><?= $l ?></option>
            <?php endforeach; ?>
          </select>
          <?php if (!empty($errors['gender'])): ?>
            <div class="invalid-feedback"><?= e($errors['gender']) ?></div>
          <?php endif; ?>
        </div>

        <div class="col-md-4">
          <label class="form-label" for="blood_group">Blood Group</label>
          <select id="blood_group" name="blood_group"
                  class="form-select <?= !empty($errors['blood_group']) ? 'is-invalid' : '' ?>">
            <option value="">-- Select --</option>
            <?php foreach (['A+','A-','B+','B-','AB+','AB-','O+','O-'] as $bg): ?>
              <option value="<?= $bg ?>" <?= ($old['blood_group'] ?? '') === $bg ? 'selected' : '' ?>><?= $bg ?></option>
            <?php endforeach; ?>
          </select>
          <?php if (!empty($errors['blood_group'])): ?>
            <div class="invalid-feedback"><?= e($errors['blood_group']) ?></div>
          <?php endif; ?>
        </div>

        <div class="col-12">
          <label class="form-label" for="allergies">Known Allergies</label>
          <input type="text" id="allergies" name="allergies"
                 class="form-control"
                 value="<?= e($old['allergies'] ?? '') ?>"
                 placeholder="e.g. Penicillin, Peanuts (leave blank if none)">
        </div>

        <div class="col-12">
          <label class="form-label" for="address">Address</label>
          <input type="text" id="address" name="address"
                 class="form-control"
                 value="<?= e($old['address'] ?? '') ?>"
                 placeholder="No. 12, Galle Road, Colombo 03">
        </div>
      </div>

      <!-- ── Section 3: Emergency Contact ── -->
      <div class="section-divider"><i class="bi bi-telephone-fill"></i> &nbsp;Emergency Contact <span style="font-weight:400;text-transform:none;letter-spacing:0;font-size:.75rem;color:var(--mp-text-3)">(optional)</span></div>

      <div class="row g-3 mb-5">
        <div class="col-md-6">
          <label class="form-label" for="ec_name">Contact Name</label>
          <input type="text" id="ec_name" name="ec_name"
                 class="form-control"
                 value="<?= e($old['ec_name'] ?? '') ?>"
                 placeholder="Parent / Spouse / Sibling">
        </div>
        <div class="col-md-6">
          <label class="form-label" for="ec_phone">Contact Phone</label>
          <input type="tel" id="ec_phone" name="ec_phone"
                 class="form-control"
                 value="<?= e($old['ec_phone'] ?? '') ?>"
                 placeholder="+94 77 123 4567">
        </div>
      </div>

      <!-- Submit -->
      <button type="submit" class="btn btn-primary w-100 py-2 mb-4" style="font-size:1rem">
        <i class="bi bi-person-check-fill"></i> Create Account
      </button>

    </form>

    <p class="text-center mb-0" style="font-size:.875rem;color:var(--mp-text-3)">
      Already have an account?
      <a href="<?= url('login') ?>" style="color:var(--mp-primary);font-weight:600">Sign in</a>
    </p>

  </div><!-- /.mp-register-card -->
</div><!-- /.mp-register-wrap -->

<script>
function togglePwd(btn, inputId) {
  const inp = document.getElementById(inputId);
  const icon = btn.querySelector('i');
  inp.type = inp.type === 'password' ? 'text' : 'password';
  icon.className = inp.type === 'password' ? 'bi bi-eye' : 'bi bi-eye-slash';
}
</script>

<?php require VIEW_PATH . '/layouts/footer.php'; ?>
