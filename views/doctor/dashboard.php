<?php
require VIEW_PATH . '/layouts/header.php';
require VIEW_PATH . '/layouts/navbar.php';

$user      = Auth::user();
$doctorRec = Doctor::findByUserId($user['id']);
$doctorId  = $doctorRec['id'] ?? 0;

$counts   = Appointment::countsByStatus($user['id'], 'doctor');
$today    = Appointment::todayForDoctor($user['id']);
$pending  = Appointment::pendingForDoctor($user['id'], 8);
$ratings  = Doctor::getRecentRatings($doctorId, 4);
$ratingInfo = Doctor::getRating($doctorId);
$patCount = Appointment::uniquePatientCount($user['id']);
?>

<div class="mp-page-wrap">
<div class="container-xl">

  <!-- ── Header ── -->
  <div class="d-flex align-items-start justify-content-between mb-4 flex-wrap gap-3">
    <div>
      <h1 class="mb-1" style="font-size:1.5rem">Dr. <?= e(explode(' ', $user['name'])[0]) ?>'s Dashboard</h1>
      <p style="color:var(--mp-text-3);margin:0;font-size:.875rem">
        <?= e($doctorRec['specialization'] ?? '') ?>
        <?php if ($doctorRec): ?>&nbsp;&middot;&nbsp; <?= e($doctorRec['service_name'] ?? '') ?><?php endif; ?>
      </p>
    </div>
    <div class="d-flex gap-2 align-items-center">
      <?php if ($ratingInfo['avg'] > 0): ?>
      <div style="background:#FFFBEB;border:1px solid #FDE68A;padding:.4rem .9rem;border-radius:9999px;font-size:.875rem;font-weight:700;color:#92400E">
        <i class="bi bi-star-fill" style="color:#F59E0B"></i>
        <?= number_format($ratingInfo['avg'], 1) ?>
        <span style="font-weight:400;color:#B45309">(<?= $ratingInfo['count'] ?>)</span>
      </div>
      <?php endif; ?>
      <span class="mp-badge completed" style="font-size:.875rem;padding:.4rem .9rem">
        <i class="bi bi-circle-fill" style="font-size:.5rem"></i> On Duty
      </span>
    </div>
  </div>

  <!-- ── Stat cards ── -->
  <div class="row g-3 mb-4">
    <?php
    $stats = [
      ['icon'=>'calendar2-check',  'color'=>'teal',   'val'=>count($today),         'label'=>"Today's Appointments", 'sub'=>'Pending + Confirmed'],
      ['icon'=>'people',           'color'=>'blue',   'val'=>$patCount,             'label'=>'Total Patients',       'sub'=>'Treated successfully'],
      ['icon'=>'clock-history',    'color'=>'amber',  'val'=>$counts['pending'],    'label'=>'Awaiting Confirm',     'sub'=>'Needs your action'],
      ['icon'=>'check2-circle',    'color'=>'green',  'val'=>$counts['completed'],  'label'=>'Completed',            'sub'=>'Total sessions'],
      ['icon'=>'star-fill',        'color'=>'purple', 'val'=>$ratingInfo['avg'] ?: '—', 'label'=>'Avg Rating',      'sub'=>$ratingInfo['count'].' reviews'],
      ['icon'=>'cash-coin',        'color'=>'red',    'val'=>'LKR '.number_format($doctorRec['fee'] ?? 0), 'label'=>'Consultation Fee', 'sub'=>'Per appointment'],
    ];
    foreach ($stats as $s): ?>
    <div class="col-sm-6 col-xl-2">
      <div class="mp-stat-card flex-column align-items-start gap-2" style="padding:1.25rem">
        <div class="stat-icon <?= $s['color'] ?>" style="width:42px;height:42px;font-size:1.2rem">
          <i class="bi bi-<?= $s['icon'] ?>"></i>
        </div>
        <div>
          <div class="stat-value" style="font-size:1.4rem"><?= $s['val'] ?></div>
          <div class="stat-label"><?= $s['label'] ?></div>
          <div style="font-size:.72rem;color:var(--mp-text-3);margin-top:2px"><?= $s['sub'] ?></div>
        </div>
      </div>
    </div>
    <?php endforeach; ?>
  </div>

  <div class="row g-4 mb-4">

    <!-- ── Today's schedule ── -->
    <div class="col-xl-7">
      <div class="mp-card" style="padding:0;overflow:hidden">
        <div class="p-4 border-bottom d-flex justify-content-between align-items-center">
          <div>
            <h6 class="mb-0">Today's Schedule</h6>
            <div style="font-size:.78rem;color:var(--mp-text-3)"><?= date('l, d F Y') ?></div>
          </div>
          <a href="<?= url('doctor/appointments') ?>" class="btn btn-sm btn-outline-primary">Full List</a>
        </div>

        <?php if (empty($today)): ?>
          <div class="text-center py-5" style="color:var(--mp-text-3)">
            <i class="bi bi-calendar-check fs-1 d-block mb-2 opacity-25"></i>
            <div style="font-weight:600">No appointments today</div>
            <div style="font-size:.85rem">Your schedule is clear for today.</div>
          </div>
        <?php else: ?>
          <?php foreach ($today as $a): ?>
          <div class="d-flex align-items-center gap-3 p-3 border-bottom" style="transition:background .1s" onmouseover="this.style.background='#f8fafc'" onmouseout="this.style.background=''">
            <!-- Time -->
            <div style="width:60px;text-align:center;flex-shrink:0">
              <div style="font-family:var(--font-display);font-weight:700;font-size:.95rem;color:var(--mp-primary)">
                <?= date('H:i', strtotime($a['appt_time'])) ?>
              </div>
              <div style="font-size:.7rem;color:var(--mp-text-3)">
                <?= date('A', strtotime($a['appt_time'])) ?>
              </div>
            </div>
            <!-- Divider -->
            <div style="width:3px;height:44px;border-radius:2px;background:<?= $a['status']==='confirmed'?'#0EA5E9':'#F59E0B' ?>;flex-shrink:0"></div>
            <!-- Patient info -->
            <div style="flex:1;min-width:0">
              <div style="font-weight:600;font-size:.9rem"><?= e($a['patient_name']) ?></div>
              <div style="font-size:.78rem;color:var(--mp-text-3)">
                <?php if ($a['patient_gender']): ?><?= ucfirst($a['patient_gender']) ?> · <?php endif; ?>
                <?php if ($a['blood_group']): ?><?= e($a['blood_group']) ?> · <?php endif; ?>
                <?= e($a['reason'] ?: 'No reason provided') ?>
              </div>
            </div>
            <!-- Status + action -->
            <div class="d-flex flex-column align-items-end gap-1" style="flex-shrink:0">
              <span class="mp-badge <?= $a['status'] ?>"><?= ucfirst($a['status']) ?></span>
              <?php if ($a['status'] === 'pending'): ?>
                <form method="POST" action="<?= url('appointment/confirm') ?>">
                  <?= CSRF::field() ?>
                  <input type="hidden" name="appointment_id" value="<?= $a['id'] ?>">
                  <input type="hidden" name="redirect_to" value="<?= url('doctor/dashboard') ?>">
                  <button class="btn btn-success btn-sm py-0 px-2" style="font-size:.75rem">
                    <i class="bi bi-check-lg"></i> Confirm
                  </button>
                </form>
              <?php elseif ($a['status'] === 'confirmed'): ?>
                <button class="btn btn-sm btn-outline-success py-0 px-2" style="font-size:.75rem"
                        data-bs-toggle="modal" data-bs-target="#completeModal<?= $a['id'] ?>">
                  <i class="bi bi-check2-circle"></i> Complete
                </button>
              <?php endif; ?>
            </div>
          </div>

          <!-- Complete modal -->
          <?php if ($a['status'] === 'confirmed'): ?>
          <div class="modal fade" id="completeModal<?= $a['id'] ?>" tabindex="-1">
            <div class="modal-dialog modal-sm">
              <div class="modal-content">
                <div class="modal-header border-0 pb-0">
                  <h6 class="modal-title">Mark as Completed</h6>
                  <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form method="POST" action="<?= url('appointment/complete') ?>">
                  <?= CSRF::field() ?>
                  <input type="hidden" name="appointment_id" value="<?= $a['id'] ?>">
                  <input type="hidden" name="redirect_to" value="<?= url('doctor/dashboard') ?>">
                  <div class="modal-body">
                    <div style="font-size:.875rem;color:var(--mp-text-2);margin-bottom:.75rem">
                      Patient: <strong><?= e($a['patient_name']) ?></strong>
                    </div>
                    <label class="form-label" style="font-size:.8rem">Clinical Notes (optional)</label>
                    <textarea name="notes" class="form-control" rows="3"
                              placeholder="Diagnosis, prescription, follow-up instructions..."></textarea>
                  </div>
                  <div class="modal-footer border-0 pt-0">
                    <button type="button" class="btn btn-sm btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-sm btn-success">Mark Complete</button>
                  </div>
                </form>
              </div>
            </div>
          </div>
          <?php endif; ?>

          <?php endforeach; ?>
        <?php endif; ?>
      </div>
    </div>

    <!-- ── Right column ── -->
    <div class="col-xl-5 d-flex flex-column gap-4">

      <!-- Pending confirmations -->
      <div class="mp-card" style="padding:0;overflow:hidden">
        <div class="p-3 border-bottom d-flex justify-content-between align-items-center">
          <h6 class="mb-0">Pending Confirmations
            <?php if ($counts['pending'] > 0): ?>
              <span class="badge bg-warning text-dark ms-1" style="font-size:.7rem"><?= $counts['pending'] ?></span>
            <?php endif; ?>
          </h6>
          <a href="<?= url('doctor/appointments') ?>" class="btn btn-sm btn-outline-primary">View All</a>
        </div>
        <?php if (empty($pending)): ?>
          <div class="text-center py-4" style="color:var(--mp-text-3);font-size:.875rem">
            <i class="bi bi-inbox d-block mb-1 fs-4 opacity-25"></i>No pending requests
          </div>
        <?php else: ?>
          <?php foreach ($pending as $a): ?>
          <div class="d-flex align-items-center gap-3 p-3 border-bottom">
            <div style="flex:1;min-width:0">
              <div style="font-weight:600;font-size:.875rem"><?= e($a['patient_name']) ?></div>
              <div style="font-size:.75rem;color:var(--mp-text-3)">
                <?= formatDate($a['appt_date']) ?> at <?= formatTime($a['appt_time']) ?>
              </div>
            </div>
            <form method="POST" action="<?= url('appointment/confirm') ?>" class="d-flex gap-1">
              <?= CSRF::field() ?>
              <input type="hidden" name="appointment_id" value="<?= $a['id'] ?>">
              <input type="hidden" name="redirect_to" value="<?= url('doctor/dashboard') ?>">
              <button class="btn btn-success btn-sm py-0" style="font-size:.75rem">
                <i class="bi bi-check-lg"></i>
              </button>
            </form>
            <form method="POST" action="<?= url('appointment/cancel') ?>">
              <?= CSRF::field() ?>
              <input type="hidden" name="appointment_id" value="<?= $a['id'] ?>">
              <input type="hidden" name="redirect_to" value="<?= url('doctor/dashboard') ?>">
              <button class="btn btn-outline-danger btn-sm py-0" style="font-size:.75rem"
                      onclick="return confirm('Decline this appointment?')">
                <i class="bi bi-x-lg"></i>
              </button>
            </form>
          </div>
          <?php endforeach; ?>
        <?php endif; ?>
      </div>

      <!-- Recent ratings -->
      <div class="mp-card" style="padding:0;overflow:hidden">
        <div class="p-3 border-bottom">
          <h6 class="mb-0">Patient Ratings</h6>
        </div>
        <?php if (empty($ratings)): ?>
          <div class="text-center py-4" style="color:var(--mp-text-3);font-size:.875rem">
            <i class="bi bi-star d-block mb-1 fs-4 opacity-25"></i>No ratings yet
          </div>
        <?php else: ?>
          <?php foreach ($ratings as $r): ?>
          <div class="p-3 border-bottom">
            <div class="d-flex justify-content-between align-items-start mb-1">
              <div style="font-weight:600;font-size:.85rem"><?= e($r['patient_name']) ?></div>
              <div style="color:#F59E0B;font-size:.8rem">
                <?php for ($i=1;$i<=5;$i++): ?>
                  <i class="bi bi-star<?= $i<=$r['stars']?'-fill':'' ?>" style="font-size:.7rem"></i>
                <?php endfor; ?>
              </div>
            </div>
            <?php if ($r['review']): ?>
              <div style="font-size:.8rem;color:var(--mp-text-2);font-style:italic">
                "<?= e(mb_substr($r['review'], 0, 100)) ?><?= strlen($r['review'])>100 ? '…' : '' ?>"
              </div>
            <?php endif; ?>
            <div style="font-size:.72rem;color:var(--mp-text-3);margin-top:.25rem">
              <?= formatDate($r['created_at']) ?>
            </div>
          </div>
          <?php endforeach; ?>
        <?php endif; ?>
      </div>

    </div><!-- /.col-xl-5 -->
  </div><!-- /.row -->

  <!-- ── Doctor profile info ── -->
  <?php if ($doctorRec): ?>
  <div class="mp-card" style="padding:1.5rem">
    <div class="row g-4 align-items-center">
      <div class="col-auto">
        <div style="width:72px;height:72px;border-radius:50%;background:var(--mp-primary-light);
                    display:flex;align-items:center;justify-content:center;
                    color:var(--mp-primary);font-size:1.8rem;font-weight:800">
          <?= mb_substr($user['name'], 0, 1) ?>
        </div>
      </div>
      <div class="col">
        <div style="font-family:var(--font-display);font-weight:800;font-size:1.1rem">Dr. <?= e($user['name']) ?></div>
        <div style="color:var(--mp-text-3);font-size:.875rem">
          <?= e($doctorRec['specialization']) ?> &middot; <?= e($doctorRec['service_name']) ?>
        </div>
        <div style="font-size:.8rem;color:var(--mp-text-2);margin-top:.25rem">
          <i class="bi bi-briefcase me-1"></i><?= $doctorRec['experience_years'] ?> years experience
          &nbsp;&middot;&nbsp;
          <i class="bi bi-geo-alt me-1"></i><?= e($doctorRec['location'] ?? 'N/A') ?>
        </div>
      </div>
      <div class="col-auto">
        <div class="text-end">
          <div style="font-size:1.1rem;font-weight:800;color:var(--mp-primary)">
            LKR <?= number_format($doctorRec['fee']) ?>
          </div>
          <div style="font-size:.75rem;color:var(--mp-text-3)">Consultation Fee</div>
        </div>
      </div>
      <div class="col-auto">
        <a href="<?= url('profile') ?>" class="btn btn-outline-primary btn-sm">Edit Profile</a>
      </div>
    </div>
  </div>
  <?php endif; ?>

</div>
</div>

<?php require VIEW_PATH . '/layouts/footer.php'; ?>
