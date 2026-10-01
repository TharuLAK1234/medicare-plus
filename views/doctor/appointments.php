<?php
require VIEW_PATH . '/layouts/header.php';
require VIEW_PATH . '/layouts/navbar.php';

$user   = Auth::user();
$counts = Appointment::countsByStatus($user['id'], 'doctor');
$all    = Appointment::forDoctor($user['id'], 200);

$activeTab = $_GET['tab'] ?? 'pending';

$filtered = match($activeTab) {
    'confirmed' => array_values(array_filter($all, fn($a) => $a['status']==='confirmed')),
    'completed' => array_values(array_filter($all, fn($a) => $a['status']==='completed')),
    'cancelled' => array_values(array_filter($all, fn($a) => $a['status']==='cancelled')),
    'all'       => array_values($all),
    default     => array_values(array_filter($all, fn($a) => $a['status']==='pending')),
};
?>

<div class="mp-page-wrap">
<div class="container-xl">

  <div class="d-flex align-items-center justify-content-between mb-4">
    <div>
      <h1 class="mb-1" style="font-size:1.5rem">Appointments</h1>
      <p style="color:var(--mp-text-3);margin:0;font-size:.875rem">
        Manage your patient appointments — confirm, complete, or review history.
      </p>
    </div>
    <a href="<?= url('doctor/dashboard') ?>" class="btn btn-outline-secondary btn-sm">
      <i class="bi bi-arrow-left me-1"></i> Dashboard
    </a>
  </div>

  <!-- Stat chips -->
  <div class="d-flex flex-wrap gap-3 mb-4">
    <?php
    $chips = [
      ['pending',   'Pending',   $counts['pending'],   'amber'],
      ['confirmed', 'Confirmed', $counts['confirmed'], 'blue'],
      ['completed', 'Completed', $counts['completed'], 'green'],
      ['cancelled', 'Cancelled', $counts['cancelled'], 'red'],
      ['all',       'All',       $counts['total'],     'teal'],
    ];
    foreach ($chips as [$tab, $label, $n, $color]): ?>
    <a href="?tab=<?= $tab ?>" style="text-decoration:none">
      <div class="mp-stat-card" style="padding:.75rem 1rem;cursor:pointer;
           <?= $activeTab===$tab?'border-color:var(--mp-primary);box-shadow:0 0 0 2px rgba(10,110,130,.15)':'' ?>">
        <div class="stat-icon <?= $color ?>" style="width:32px;height:32px;font-size:.8rem">
          <i class="bi bi-calendar2"></i>
        </div>
        <div>
          <div class="stat-value" style="font-size:1.1rem"><?= $n ?></div>
          <div class="stat-label" style="font-size:.68rem"><?= $label ?></div>
        </div>
      </div>
    </a>
    <?php endforeach; ?>
  </div>

  <!-- Table card -->
  <div class="mp-card" style="padding:0;overflow:hidden">
    <div class="border-bottom px-4 pt-3 d-flex justify-content-between align-items-end">
      <ul class="nav nav-tabs border-0">
        <?php foreach ($chips as [$tab, $label, $n, $color]): ?>
        <li class="nav-item">
          <a class="nav-link <?= $activeTab===$tab?'active':'' ?>" href="?tab=<?= $tab ?>"
             style="font-family:var(--font-display);font-weight:600;font-size:.85rem">
            <?= $label ?>
            <?php if ($tab==='pending' && $n>0): ?>
              <span class="badge bg-warning text-dark ms-1" style="font-size:.65rem"><?= $n ?></span>
            <?php endif; ?>
          </a>
        </li>
        <?php endforeach; ?>
      </ul>
    </div>

    <?php if (empty($filtered)): ?>
      <div class="text-center py-5" style="color:var(--mp-text-3)">
        <i class="bi bi-inbox fs-1 d-block mb-2 opacity-25"></i>
        <div style="font-weight:600">No <?= $activeTab ?> appointments</div>
      </div>
    <?php else: ?>
    <div class="table-responsive">
      <table class="table table-hover mb-0" style="font-size:.875rem">
        <thead style="background:#f8fafc">
          <tr>
            <th class="ps-4">Ref / Date</th>
            <th>Patient</th>
            <th>Reason</th>
            <?php if ($activeTab==='completed'||$activeTab==='all'): ?><th>Notes</th><?php endif; ?>
            <th>Fee</th>
            <th>Status</th>
            <th class="pe-4 text-end">Actions</th>
          </tr>
        </thead>
        <tbody>
        <?php foreach ($filtered as $a): ?>
          <tr>
            <td class="ps-4">
              <div><code style="font-size:.78rem;color:var(--mp-primary)"><?= e($a['ref_no']) ?></code></div>
              <div style="font-size:.75rem;color:var(--mp-text-3)">
                <?= formatDate($a['appt_date']) ?> <?= formatTime($a['appt_time']) ?>
              </div>
            </td>
            <td>
              <div style="font-weight:600"><?= e($a['patient_name']) ?></div>
              <div style="font-size:.75rem;color:var(--mp-text-3)">
                <?php if ($a['patient_gender']): ?><?= ucfirst($a['patient_gender']) ?><?php endif; ?>
                <?php if ($a['blood_group']): ?> · <?= e($a['blood_group']) ?><?php endif; ?>
              </div>
            </td>
            <td style="max-width:160px">
              <div style="white-space:nowrap;overflow:hidden;text-overflow:ellipsis;color:var(--mp-text-2)">
                <?= e($a['reason'] ?: '—') ?>
              </div>
            </td>
            <?php if ($activeTab==='completed'||$activeTab==='all'): ?>
            <td style="max-width:180px">
              <?php if ($a['notes']): ?>
                <div style="font-size:.8rem;color:var(--mp-text-2);font-style:italic">
                  <?= e(mb_substr($a['notes'],0,70)) ?><?= strlen($a['notes'])>70?'…':'' ?>
                </div>
              <?php else: ?>
                <span style="color:var(--mp-text-3);font-size:.8rem">—</span>
              <?php endif; ?>
            </td>
            <?php endif; ?>
            <td style="font-weight:600;white-space:nowrap">LKR <?= number_format($a['fee']) ?></td>
            <td><span class="mp-badge <?= $a['status'] ?>"><?= ucfirst($a['status']) ?></span></td>
            <td class="pe-4 text-end">
              <div class="d-flex gap-1 justify-content-end">
              <?php if ($a['status']==='pending'): ?>
                <form method="POST" action="<?= url('appointment/confirm') ?>">
                  <?= CSRF::field() ?>
                  <input type="hidden" name="appointment_id" value="<?= $a['id'] ?>">
                  <input type="hidden" name="redirect_to" value="<?= url('doctor/appointments').'?tab=pending' ?>">
                  <button class="btn btn-sm btn-success py-0 px-2" style="font-size:.75rem" title="Confirm">
                    <i class="bi bi-check-lg"></i>
                  </button>
                </form>
                <form method="POST" action="<?= url('appointment/cancel') ?>">
                  <?= CSRF::field() ?>
                  <input type="hidden" name="appointment_id" value="<?= $a['id'] ?>">
                  <input type="hidden" name="redirect_to" value="<?= url('doctor/appointments').'?tab=pending' ?>">
                  <button class="btn btn-sm btn-outline-danger py-0 px-2" style="font-size:.75rem" title="Decline"
                          onclick="return confirm('Decline this appointment?')">
                    <i class="bi bi-x-lg"></i>
                  </button>
                </form>
              <?php elseif ($a['status']==='confirmed'): ?>
                <button class="btn btn-sm btn-outline-success py-0 px-2" style="font-size:.75rem"
                        data-bs-toggle="modal" data-bs-target="#completeModal<?= $a['id'] ?>" title="Mark complete">
                  <i class="bi bi-check2-circle me-1"></i>Complete
                </button>
              <?php else: ?>
                <span style="color:var(--mp-text-3);font-size:.8rem">—</span>
              <?php endif; ?>
              </div>
            </td>
          </tr>
        <?php endforeach; ?>
        </tbody>
      </table>
    </div>
    <?php endif; ?>
  </div>

</div>
</div>

<!-- Complete modals -->
<?php foreach ($filtered as $a):
    if ($a['status'] !== 'confirmed') continue; ?>
<div class="modal fade" id="completeModal<?= $a['id'] ?>" tabindex="-1">
  <div class="modal-dialog modal-dialog-centered modal-sm">
    <div class="modal-content">
      <div class="modal-header border-0 pb-0">
        <h6 class="modal-title">Mark as Completed</h6>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <form method="POST" action="<?= url('appointment/complete') ?>">
        <?= CSRF::field() ?>
        <input type="hidden" name="appointment_id" value="<?= $a['id'] ?>">
        <input type="hidden" name="redirect_to" value="<?= url('doctor/appointments').'?tab=confirmed' ?>">
        <div class="modal-body">
          <div style="font-size:.875rem;color:var(--mp-text-2);margin-bottom:.75rem">
            Patient: <strong><?= e($a['patient_name']) ?></strong><br>
            <span style="font-size:.78rem"><?= formatDate($a['appt_date']) ?> at <?= formatTime($a['appt_time']) ?></span>
          </div>
          <label class="form-label" style="font-size:.8rem">Clinical Notes (optional)</label>
          <textarea name="notes" class="form-control" rows="3"
                    placeholder="Diagnosis, prescription, follow-up..."></textarea>
        </div>
        <div class="modal-footer border-0 pt-0">
          <button type="button" class="btn btn-sm btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
          <button type="submit" class="btn btn-sm btn-success">Mark Complete</button>
        </div>
      </form>
    </div>
  </div>
</div>
<?php endforeach; ?>

<?php require VIEW_PATH . '/layouts/footer.php'; ?>
