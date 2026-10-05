<?php
require VIEW_PATH . '/layouts/header.php';
require VIEW_PATH . '/layouts/navbar.php';
// $patient, $user passed from controller
?>

<div class="mp-page-wrap">
<div class="container" style="max-width:720px">

  <div class="d-flex align-items-center justify-content-between mb-4">
    <h1 style="font-size:1.5rem;margin:0">My Profile</h1>
    <a href="<?= url('patient/dashboard') ?>" class="btn btn-outline-secondary btn-sm">
      <i class="bi bi-arrow-left me-1"></i> Dashboard
    </a>
  </div>

  <?php if (Session::hasFlash('success')): ?>
  <div class="mp-flash success mb-4"><?= e(Session::getFlash('success')) ?></div>
  <?php endif; ?>

  <form method="POST" action="<?= url('profile/update') ?>">
    <?= CSRF::field() ?>

    <!-- Account info -->
    <div class="mp-card mb-4">
      <h6 style="font-size:.85rem;text-transform:uppercase;letter-spacing:.07em;color:var(--mp-text-3);margin-bottom:1.25rem">
        Account Information
      </h6>
      <div class="row g-3">
        <div class="col-md-6">
          <label class="form-label">Full Name</label>
          <input type="text" name="name" class="form-control" value="<?= e($user['name']) ?>" required>
        </div>
        <div class="col-md-6">
          <label class="form-label">Email</label>
          <input type="email" class="form-control" value="<?= e($user['email']) ?>" disabled>
          <div style="font-size:.75rem;color:var(--mp-text-3);margin-top:.25rem">Email cannot be changed.</div>
        </div>
        <div class="col-md-6">
          <label class="form-label">Phone</label>
          <input type="text" name="phone" class="form-control" value="<?= e($user['phone'] ?? '') ?>"
                 placeholder="+94 7X XXX XXXX">
        </div>
      </div>
    </div>

    <!-- Health profile -->
    <div class="mp-card mb-4">
      <h6 style="font-size:.85rem;text-transform:uppercase;letter-spacing:.07em;color:var(--mp-text-3);margin-bottom:1.25rem">
        Health Profile
      </h6>
      <div class="row g-3">
        <div class="col-md-6">
          <label class="form-label">Date of Birth</label>
          <input type="date" name="dob" class="form-control" value="<?= e($patient['dob'] ?? '') ?>">
        </div>
        <div class="col-md-6">
          <label class="form-label">Gender</label>
          <select name="gender" class="form-select">
            <option value="">Not specified</option>
            <?php foreach (['male','female','other'] as $g): ?>
            <option value="<?= $g ?>" <?= ($patient['gender'] ?? '')===$g?'selected':'' ?>><?= ucfirst($g) ?></option>
            <?php endforeach; ?>
          </select>
        </div>
        <div class="col-md-6">
          <label class="form-label">Blood Group</label>
          <select name="blood_group" class="form-select">
            <option value="">Unknown</option>
            <?php foreach (['A+','A-','B+','B-','AB+','AB-','O+','O-'] as $bg): ?>
            <option value="<?= $bg ?>" <?= ($patient['blood_group'] ?? '')===$bg?'selected':'' ?>><?= $bg ?></option>
            <?php endforeach; ?>
          </select>
        </div>
        <div class="col-12">
          <label class="form-label">Known Allergies</label>
          <textarea name="allergies" class="form-control" rows="2"
                    placeholder="Penicillin, pollen, dust…"><?= e($patient['allergies'] ?? '') ?></textarea>
        </div>
        <div class="col-12">
          <label class="form-label">Home Address</label>
          <textarea name="address" class="form-control" rows="2"><?= e($patient['address'] ?? '') ?></textarea>
        </div>
      </div>
    </div>

    <!-- Emergency contact -->
    <div class="mp-card mb-4">
      <h6 style="font-size:.85rem;text-transform:uppercase;letter-spacing:.07em;color:var(--mp-text-3);margin-bottom:1.25rem">
        Emergency Contact
      </h6>
      <div class="row g-3">
        <div class="col-md-6">
          <label class="form-label">Contact Name</label>
          <input type="text" name="ec_name" class="form-control" value="<?= e($patient['ec_name'] ?? '') ?>">
        </div>
        <div class="col-md-6">
          <label class="form-label">Contact Phone</label>
          <input type="text" name="ec_phone" class="form-control" value="<?= e($patient['ec_phone'] ?? '') ?>">
        </div>
      </div>
    </div>

    <button type="submit" class="btn btn-primary px-4" style="font-weight:700">
      <i class="bi bi-check2 me-1"></i> Save Changes
    </button>
  </form>

</div>
</div>

<?php require VIEW_PATH . '/layouts/footer.php'; ?>
