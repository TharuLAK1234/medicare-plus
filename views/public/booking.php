<?php
require VIEW_PATH . '/layouts/header.php';
require VIEW_PATH . '/layouts/navbar.php';
// $doctor, $appt_date, $appt_time, $reason passed from controller
?>

<div class="mp-page-wrap">
<div class="container" style="max-width:600px">

  <div class="text-center mb-4">
    <i class="bi bi-calendar2-check-fill" style="font-size:2.5rem;color:var(--mp-primary)"></i>
    <h1 style="font-size:1.5rem;font-family:var(--font-display);font-weight:800;margin:.75rem 0 .25rem">
      Confirm Your Appointment
    </h1>
    <p style="color:var(--mp-text-3);font-size:.875rem">Review the details below before confirming.</p>
  </div>

  <div class="mp-card mb-4">
    <h6 style="font-size:.8rem;text-transform:uppercase;letter-spacing:.07em;color:var(--mp-text-3);margin-bottom:1rem">
      Appointment Summary
    </h6>
    <div class="row g-3">
      <div class="col-6">
        <div style="font-size:.75rem;color:var(--mp-text-3)">Doctor</div>
        <div style="font-weight:700">Dr. <?= e($doctor['name']) ?></div>
        <div style="font-size:.8rem;color:var(--mp-text-3)"><?= e($doctor['specialization']) ?></div>
      </div>
      <div class="col-6">
        <div style="font-size:.75rem;color:var(--mp-text-3)">Date &amp; Time</div>
        <div style="font-weight:700"><?= formatDate($appt_date) ?></div>
        <div style="font-size:.8rem;color:var(--mp-text-3)"><?= formatTime($appt_time) ?></div>
      </div>
      <?php if ($reason): ?>
      <div class="col-12">
        <div style="font-size:.75rem;color:var(--mp-text-3)">Reason</div>
        <div style="font-size:.875rem;color:var(--mp-text-2)"><?= e($reason) ?></div>
      </div>
      <?php endif; ?>
      <div class="col-12">
        <div style="background:var(--mp-primary-light);border-radius:10px;padding:.875rem 1rem;
                    display:flex;justify-content:space-between;align-items:center">
          <span style="font-weight:600">Consultation Fee</span>
          <span style="font-weight:800;color:var(--mp-primary);font-size:1.1rem">
            LKR <?= number_format($doctor['fee']) ?>
          </span>
        </div>
      </div>
    </div>
  </div>

  <form method="POST" action="<?= url('booking/store') ?>">
    <?= CSRF::field() ?>
    <input type="hidden" name="doctor_id"  value="<?= $doctor['id'] ?>">
    <input type="hidden" name="appt_date"  value="<?= e($appt_date) ?>">
    <input type="hidden" name="appt_time"  value="<?= e($appt_time) ?>">
    <input type="hidden" name="reason"     value="<?= e($reason) ?>">

    <div class="d-flex gap-3">
      <a href="<?= url('doctors/'.$doctor['id']) ?>" class="btn btn-outline-secondary flex-fill">
        <i class="bi bi-arrow-left me-1"></i> Change
      </a>
      <button type="submit" class="btn btn-primary flex-fill" style="font-weight:700">
        <i class="bi bi-check2-circle me-1"></i> Confirm Booking
      </button>
    </div>
  </form>

</div>
</div>

<?php require VIEW_PATH . '/layouts/footer.php'; ?>
