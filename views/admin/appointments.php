<?php
require VIEW_PATH . '/layouts/header.php';
require VIEW_PATH . '/layouts/navbar.php';
// $appointments, $counts, $filter passed from controller
?>

<div class="mp-page-wrap">
<div class="container-xl">

  <div class="d-flex align-items-center justify-content-between mb-4">
    <div>
      <h1 class="mb-1" style="font-size:1.5rem">All Appointments</h1>
      <p style="color:var(--mp-text-3);margin:0;font-size:.875rem">
        Manage and monitor all patient appointments.
      </p>
    </div>
    <a href="<?= url('admin/dashboard') ?>" class="btn btn-outline-secondary btn-sm">
      <i class="bi bi-speedometer2 me-1"></i> Dashboard
    </a>
  </div>

  <!-- Status filter chips -->
  <div class="d-flex flex-wrap gap-2 mb-4">
    <?php
    $tabs = [
      ['',          'All',       $counts['total'],     'teal'],
      ['pending',   'Pending',   $counts['pending'],   'amber'],
      ['confirmed', 'Confirmed', $counts['confirmed'], 'blue'],
      ['completed', 'Completed', $counts['completed'], 'green'],
      ['cancelled', 'Cancelled', $counts['cancelled'], 'red'],
    ];
    foreach ($tabs as [$val, $label, $n, $color]): ?>
    <a href="?status=<?= $val ?>" style="text-decoration:none">
      <div class="mp-stat-card" style="padding:.6rem 1rem;cursor:pointer;
           <?= $filter===$val?'border-color:var(--mp-primary);box-shadow:0 0 0 2px rgba(10,110,130,.15)':'' ?>">
        <div class="stat-icon <?= $color ?>" style="width:28px;height:28px;font-size:.75rem">
          <i class="bi bi-calendar2"></i>
        </div>
        <div>
          <div class="stat-value" style="font-size:1rem"><?= $n ?></div>
          <div class="stat-label" style="font-size:.65rem"><?= $label ?></div>
        </div>
      </div>
    </a>
    <?php endforeach; ?>
  </div>

  <div class="mp-card" style="padding:0;overflow:hidden">
    <?php if (empty($appointments)): ?>
    <div class="text-center py-5" style="color:var(--mp-text-3)">
      <i class="bi bi-calendar2 fs-1 d-block mb-2 opacity-25"></i>
      <div style="font-weight:600">No appointments found</div>
    </div>
    <?php else: ?>
    <div class="table-responsive">
      <table class="table table-hover mb-0" style="font-size:.875rem">
        <thead style="background:#f8fafc">
          <tr>
            <th class="ps-4">Ref / Date</th>
            <th>Patient</th>
            <th>Doctor</th>
            <th>Service</th>
            <th>Fee</th>
            <th>Status</th>
            <th class="pe-4 text-end">Actions</th>
          </tr>
        </thead>
        <tbody>
        <?php foreach ($appointments as $a): ?>
          <tr>
            <td class="ps-4">
              <div><code style="font-size:.78rem;color:var(--mp-primary)"><?= e($a['ref_no']) ?></code></div>
              <div style="font-size:.75rem;color:var(--mp-text-3)">
                <?= formatDate($a['appt_date']) ?> <?= formatTime($a['appt_time']) ?>
              </div>
            </td>
            <td>
              <div style="font-weight:600"><?= e($a['patient_name']) ?></div>
              <div style="font-size:.75rem;color:var(--mp-text-3)"><?= e($a['patient_email']) ?></div>
            </td>
            <td>
              <div style="font-weight:600">Dr. <?= e($a['doctor_name']) ?></div>
            </td>
            <td>
              <div style="font-size:.8rem;color:var(--mp-text-2)"><?= e($a['service_name']) ?></div>
            </td>
            <td style="font-weight:600;white-space:nowrap">LKR <?= number_format($a['fee']) ?></td>
            <td><span class="mp-badge <?= $a['status'] ?>"><?= ucfirst($a['status']) ?></span></td>
            <td class="pe-4 text-end">
              <?php if ($a['status'] === 'pending' || $a['status'] === 'confirmed'): ?>
              <form method="POST" action="<?= url('appointment/cancel') ?>" class="d-inline">
                <?= CSRF::field() ?>
                <input type="hidden" name="appointment_id" value="<?= $a['id'] ?>">
                <input type="hidden" name="redirect_to" value="<?= url('admin/appointments') ?>?status=<?= urlencode($filter) ?>">
                <button class="btn btn-sm btn-outline-danger py-0 px-2" style="font-size:.75rem"
                        onclick="return confirm('Cancel appointment <?= e($a['ref_no']) ?>?')" title="Cancel">
                  <i class="bi bi-x-lg"></i>
                </button>
              </form>
              <?php else: ?>
                <span style="color:var(--mp-text-3);font-size:.8rem">—</span>
              <?php endif; ?>
            </td>
          </tr>
        <?php endforeach; ?>
        </tbody>
      </table>
    </div>
    <div class="px-4 py-3 border-top" style="font-size:.8rem;color:var(--mp-text-3)">
      Showing <?= count($appointments) ?> appointments
      <?php if ($filter): ?> with status <strong><?= $filter ?></strong><?php endif; ?>
    </div>
    <?php endif; ?>
  </div>

</div>
</div>

<?php require VIEW_PATH . '/layouts/footer.php'; ?>
