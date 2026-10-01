<?php
require VIEW_PATH . '/layouts/header.php';
require VIEW_PATH . '/layouts/navbar.php';

$user    = Auth::user();
$counts  = Appointment::countsByStatus($user['id'], 'patient');
$next    = Appointment::nextForPatient($user['id']);
$recent  = Appointment::forPatient($user['id'], 6);
$patient = Patient::findByUserId($user['id']);

$greeting = date('H') < 12 ? 'Good morning' : (date('H') < 17 ? 'Good afternoon' : 'Good evening');
?>

<div class="mp-page-wrap">
<div class="container-xl">

  <!-- ── Header ── -->
  <div class="d-flex align-items-start justify-content-between mb-4 flex-wrap gap-3">
    <div>
      <h1 class="mb-1" style="font-size:1.5rem">
        <?= $greeting ?>, <?= e(explode(' ', $user['name'])[0]) ?> 👋
      </h1>
      <p style="color:var(--mp-text-3);margin:0;font-size:.875rem">
        Manage your health and appointments from here.
      </p>
    </div>
    <a href="<?= url('doctors') ?>" class="btn btn-primary">
      <i class="bi bi-plus-lg me-1"></i> Book Appointment
    </a>
  </div>

  <!-- ── Stat cards ── -->
  <div class="row g-3 mb-4">
    <?php
    $stats = [
      ['icon'=>'calendar2-check', 'color'=>'teal',   'val'=>$counts['total'],     'label'=>'Total Appointments'],
      ['icon'=>'clock',           'color'=>'amber',  'val'=>$counts['upcoming'],  'label'=>'Upcoming'],
      ['icon'=>'check2-circle',   'color'=>'green',  'val'=>$counts['completed'], 'label'=>'Completed'],
      ['icon'=>'x-circle',        'color'=>'red',    'val'=>$counts['cancelled'], 'label'=>'Cancelled'],
    ];
    foreach ($stats as $s): ?>
    <div class="col-sm-6 col-xl-3">
      <div class="mp-stat-card">
        <div class="stat-icon <?= $s['color'] ?>"><i class="bi bi-<?= $s['icon'] ?>"></i></div>
        <div>
          <div class="stat-value"><?= $s['val'] ?></div>
          <div class="stat-label"><?= $s['label'] ?></div>
        </div>
      </div>
    </div>
    <?php endforeach; ?>
  </div>

  <div class="row g-4 mb-4">

    <!-- ── Next appointment ── -->
    <div class="col-lg-8">
      <?php if ($next): ?>
      <div class="mp-card" style="background:linear-gradient(135deg,var(--mp-primary) 0%,#0D9488 100%);color:#fff;border:none">
        <div class="d-flex justify-content-between align-items-start mb-3">
          <div>
            <div style="font-size:.75rem;font-weight:700;text-transform:uppercase;
                        letter-spacing:.1em;color:rgba(255,255,255,.75);margin-bottom:.5rem">
              Next Appointment
            </div>
            <h5 style="color:#fff;margin:0">Dr. <?= e($next['doctor_name']) ?></h5>
            <div style="color:rgba(255,255,255,.8);font-size:.875rem">
              <?= e($next['specialization']) ?> &middot; <?= e($next['service_name']) ?>
            </div>
          </div>
          <span class="mp-badge <?= $next['status'] ?>" style="background:rgba(255,255,255,.2);color:#fff;border:1px solid rgba(255,255,255,.3)">
            <?= ucfirst($next['status']) ?>
          </span>
        </div>
        <div class="row g-3 mt-1">
          <div class="col-auto">
            <div style="background:rgba(255,255,255,.15);border-radius:10px;padding:.75rem 1.25rem;text-align:center">
              <div style="font-size:1.5rem;font-weight:800;line-height:1"><?= date('d', strtotime($next['appt_date'])) ?></div>
              <div style="font-size:.75rem;text-transform:uppercase;letter-spacing:.06em;color:rgba(255,255,255,.75)">
                <?= date('M Y', strtotime($next['appt_date'])) ?>
              </div>
            </div>
          </div>
          <div class="col-auto">
            <div style="background:rgba(255,255,255,.15);border-radius:10px;padding:.75rem 1.25rem;text-align:center">
              <div style="font-size:1.4rem;font-weight:800;line-height:1"><?= date('H:i', strtotime($next['appt_time'])) ?></div>
              <div style="font-size:.75rem;text-transform:uppercase;letter-spacing:.06em;color:rgba(255,255,255,.75)">
                <?= date('A', strtotime($next['appt_time'])) ?>
              </div>
            </div>
          </div>
          <div class="col">
            <div style="font-size:.8rem;color:rgba(255,255,255,.75);margin-bottom:.25rem">Reason</div>
            <div style="font-size:.9rem;color:#fff"><?= e($next['reason'] ?: 'General consultation') ?></div>
            <div style="font-size:.78rem;color:rgba(255,255,255,.7);margin-top:.25rem">
              <i class="bi bi-tag me-1"></i>Ref: <?= e($next['ref_no']) ?>
            </div>
          </div>
        </div>
        <?php if (in_array($next['status'], ['pending','confirmed'])): ?>
        <div class="mt-3 pt-3" style="border-top:1px solid rgba(255,255,255,.2)">
          <form method="POST" action="<?= url('appointment/cancel') ?>" class="d-inline">
            <?= CSRF::field() ?>
            <input type="hidden" name="appointment_id" value="<?= $next['id'] ?>">
            <input type="hidden" name="redirect_to" value="<?= url('patient/dashboard') ?>">
            <button class="btn btn-sm" style="background:rgba(255,255,255,.15);color:#fff;border:1px solid rgba(255,255,255,.3)"
                    onclick="return confirm('Are you sure you want to cancel this appointment?')">
              <i class="bi bi-x-circle me-1"></i> Cancel Appointment
            </button>
          </form>
          <a href="<?= url('appointments') ?>" class="btn btn-sm ms-2"
             style="background:rgba(255,255,255,.2);color:#fff;border:1px solid rgba(255,255,255,.3)">
            View All <i class="bi bi-arrow-right"></i>
          </a>
        </div>
        <?php endif; ?>
      </div>

      <?php else: ?>
      <!-- No upcoming appointment -->
      <div class="mp-card text-center" style="padding:3rem">
        <i class="bi bi-calendar-x fs-1 mb-3" style="color:var(--mp-text-3);opacity:.4"></i>
        <h5 style="color:var(--mp-text-1)">No Upcoming Appointments</h5>
        <p style="color:var(--mp-text-3)">Book an appointment with one of our specialists.</p>
        <a href="<?= url('doctors') ?>" class="btn btn-primary">
          <i class="bi bi-search me-1"></i> Find a Doctor
        </a>
      </div>
      <?php endif; ?>
    </div>

    <!-- ── Patient health card + quick actions ── -->
    <div class="col-lg-4 d-flex flex-column gap-4">

      <!-- Health profile snippet -->
      <div class="mp-card" style="padding:1.25rem">
        <h6 class="mb-3" style="font-size:.85rem;text-transform:uppercase;letter-spacing:.07em;color:var(--mp-text-3)">
          Health Profile
        </h6>
        <?php
        $fields = [
          ['bi-droplet-fill',     'Blood Group', $patient['blood_group'] ?? 'Not set'],
          ['bi-person-fill',      'Gender',      ucfirst($patient['gender'] ?? 'Not set')],
          ['bi-calendar-heart',   'DOB',         $patient['dob'] ? formatDate($patient['dob']) : 'Not set'],
          ['bi-exclamation-triangle-fill', 'Allergies', $patient['allergies'] ?? 'None reported'],
        ];
        foreach ($fields as [$icon, $label, $val]): ?>
        <div class="d-flex align-items-center gap-3 mb-2">
          <i class="bi bi-<?= $icon ?>" style="color:var(--mp-primary);width:16px;font-size:.9rem"></i>
          <div>
            <div style="font-size:.72rem;color:var(--mp-text-3);line-height:1"><?= $label ?></div>
            <div style="font-size:.875rem;font-weight:600;color:var(--mp-text-1)"><?= e($val) ?></div>
          </div>
        </div>
        <?php endforeach; ?>
        <a href="<?= url('profile') ?>" class="btn btn-sm btn-outline-primary w-100 mt-2">
          <i class="bi bi-pencil me-1"></i> Update Profile
        </a>
      </div>

      <!-- Quick actions -->
      <div class="mp-card" style="padding:1.25rem">
        <h6 class="mb-3" style="font-size:.85rem;text-transform:uppercase;letter-spacing:.07em;color:var(--mp-text-3)">
          Quick Actions
        </h6>
        <div class="d-grid gap-2">
          <a href="<?= url('doctors') ?>" class="btn btn-outline-primary text-start btn-sm">
            <i class="bi bi-search me-2"></i> Find a Specialist
          </a>
          <a href="<?= url('appointments') ?>" class="btn btn-outline-primary text-start btn-sm">
            <i class="bi bi-calendar2-week me-2"></i> My Appointment History
          </a>
          <a href="<?= url('reports') ?>" class="btn btn-outline-primary text-start btn-sm">
            <i class="bi bi-file-earmark-medical me-2"></i> Medical Reports
          </a>
          <a href="<?= url('messages') ?>" class="btn btn-outline-secondary text-start btn-sm">
            <i class="bi bi-chat-dots me-2"></i> Message a Doctor
          </a>
        </div>
      </div>
    </div>

  </div><!-- /.row -->

  <!-- ── Recent appointment history ── -->
  <div class="mp-card" style="padding:0;overflow:hidden">
    <div class="p-4 border-bottom d-flex justify-content-between align-items-center">
      <h6 class="mb-0">Recent Appointments</h6>
      <a href="<?= url('appointments') ?>" class="btn btn-sm btn-outline-primary">View All History</a>
    </div>

    <?php if (empty($recent)): ?>
      <div class="text-center py-5" style="color:var(--mp-text-3)">
        <i class="bi bi-calendar2 fs-1 d-block mb-2 opacity-25"></i>
        <div>No appointment history yet.</div>
      </div>
    <?php else: ?>
    <div class="table-responsive">
      <table class="table table-hover mb-0" style="font-size:.875rem">
        <thead style="background:#f8fafc">
          <tr>
            <th class="ps-4">Ref</th>
            <th>Doctor</th>
            <th>Date &amp; Time</th>
            <th>Reason</th>
            <th>Fee</th>
            <th>Status</th>
            <th class="pe-4"></th>
          </tr>
        </thead>
        <tbody>
        <?php foreach ($recent as $a): ?>
          <tr>
            <td class="ps-4">
              <code style="font-size:.78rem;color:var(--mp-primary)"><?= e($a['ref_no']) ?></code>
            </td>
            <td>
              <div style="font-weight:600">Dr. <?= e($a['doctor_name']) ?></div>
              <div style="font-size:.75rem;color:var(--mp-text-3)"><?= e($a['service_name']) ?></div>
            </td>
            <td>
              <div><?= formatDate($a['appt_date']) ?></div>
              <div style="font-size:.75rem;color:var(--mp-text-3)"><?= formatTime($a['appt_time']) ?></div>
            </td>
            <td style="max-width:160px">
              <div style="white-space:nowrap;overflow:hidden;text-overflow:ellipsis;color:var(--mp-text-2)">
                <?= e($a['reason'] ?: '—') ?>
              </div>
            </td>
            <td style="font-weight:600">LKR <?= number_format($a['fee']) ?></td>
            <td><span class="mp-badge <?= $a['status'] ?>"><?= ucfirst($a['status']) ?></span></td>
            <td class="pe-4">
              <?php if (in_array($a['status'], ['pending','confirmed'])): ?>
              <form method="POST" action="<?= url('appointment/cancel') ?>">
                <?= CSRF::field() ?>
                <input type="hidden" name="appointment_id" value="<?= $a['id'] ?>">
                <input type="hidden" name="redirect_to" value="<?= url('patient/dashboard') ?>">
                <button class="btn btn-sm btn-outline-danger py-0"
                        onclick="return confirm('Cancel this appointment?')"
                        style="font-size:.75rem">Cancel</button>
              </form>
              <?php elseif ($a['status'] === 'completed'): ?>
                <a href="<?= url('appointments') ?>" class="btn btn-sm btn-outline-secondary py-0" style="font-size:.75rem">
                  Details
                </a>
              <?php endif; ?>
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

<?php require VIEW_PATH . '/layouts/footer.php'; ?>
