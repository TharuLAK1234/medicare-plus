<?php
require VIEW_PATH . '/layouts/header.php';
require VIEW_PATH . '/layouts/navbar.php';
// $appt passed from controller
?>

<div class="mp-page-wrap">
<div class="container" style="max-width:520px">
  <div class="mp-card text-center" style="padding:3rem 2rem">
    <div style="width:72px;height:72px;border-radius:50%;background:#ECFDF5;
                display:flex;align-items:center;justify-content:center;
                margin:0 auto 1.25rem;font-size:2rem;color:#10B981">
      <i class="bi bi-check2-circle-fill"></i>
    </div>
    <h2 style="font-family:var(--font-display);font-weight:800;font-size:1.5rem;margin-bottom:.5rem">
      Appointment Booked!
    </h2>
    <p style="color:var(--mp-text-3);font-size:.9rem;margin-bottom:1.5rem">
      Your appointment has been submitted and is awaiting confirmation by the doctor.
    </p>

    <div style="background:#f8fafc;border-radius:12px;padding:1.25rem;margin-bottom:1.5rem;text-align:left">
      <div class="row g-2">
        <div class="col-6">
          <div style="font-size:.72rem;color:var(--mp-text-3);text-transform:uppercase;letter-spacing:.06em">Reference No.</div>
          <code style="color:var(--mp-primary);font-size:.9rem"><?= e($appt['ref_no']) ?></code>
        </div>
        <div class="col-6">
          <div style="font-size:.72rem;color:var(--mp-text-3);text-transform:uppercase;letter-spacing:.06em">Status</div>
          <span class="mp-badge pending">Pending</span>
        </div>
        <div class="col-6">
          <div style="font-size:.72rem;color:var(--mp-text-3);text-transform:uppercase;letter-spacing:.06em">Doctor</div>
          <div style="font-weight:600;font-size:.875rem">Dr. <?= e($appt['doctor_name']) ?></div>
        </div>
        <div class="col-6">
          <div style="font-size:.72rem;color:var(--mp-text-3);text-transform:uppercase;letter-spacing:.06em">Date</div>
          <div style="font-weight:600;font-size:.875rem"><?= formatDate($appt['appt_date']) ?></div>
        </div>
        <div class="col-6">
          <div style="font-size:.72rem;color:var(--mp-text-3);text-transform:uppercase;letter-spacing:.06em">Time</div>
          <div style="font-weight:600;font-size:.875rem"><?= formatTime($appt['appt_time']) ?></div>
        </div>
        <div class="col-6">
          <div style="font-size:.72rem;color:var(--mp-text-3);text-transform:uppercase;letter-spacing:.06em">Fee</div>
          <div style="font-weight:700;color:var(--mp-primary)">LKR <?= number_format($appt['fee']) ?></div>
        </div>
      </div>
    </div>

    <div class="d-flex gap-2 flex-column">
      <a href="<?= url('appointments') ?>" class="btn btn-primary">
        <i class="bi bi-calendar2-week me-1"></i>View My Appointments
      </a>
      <a href="<?= url('doctors') ?>" class="btn btn-outline-primary">Book Another</a>
    </div>
  </div>
</div>
</div>

<?php require VIEW_PATH . '/layouts/footer.php'; ?>
