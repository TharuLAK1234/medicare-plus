<?php
require VIEW_PATH . '/layouts/header.php';
require VIEW_PATH . '/layouts/navbar.php';

$user         = Auth::user();
$appointments = Appointment::forPatient($user['id'], 100);
$counts       = Appointment::countsByStatus($user['id'], 'patient');

// Group by upcoming vs past
$upcoming = array_filter($appointments, fn($a) =>
    in_array($a['status'], ['pending','confirmed']) && $a['appt_date'] >= date('Y-m-d'));
$past = array_filter($appointments, fn($a) =>
    $a['status'] === 'completed' || $a['status'] === 'cancelled' ||
    ($a['appt_date'] < date('Y-m-d') && $a['status'] !== 'confirmed'));

$activeTab = $_GET['tab'] ?? 'upcoming';
?>

<div class="mp-page-wrap">
<div class="container-xl">

  <!-- ── Header ── -->
  <div class="d-flex align-items-center justify-content-between mb-4">
    <div>
      <h1 class="mb-1" style="font-size:1.5rem">My Appointments</h1>
      <p style="color:var(--mp-text-3);margin:0;font-size:.875rem">
        Your complete appointment history and upcoming bookings.
      </p>
    </div>
    <a href="<?= url('doctors') ?>" class="btn btn-primary">
      <i class="bi bi-plus-lg me-1"></i> Book Appointment
    </a>
  </div>

  <!-- ── Stat chips ── -->
  <div class="d-flex flex-wrap gap-3 mb-4">
    <?php
    $chips = [
      ['upcoming',   'Upcoming',   $counts['upcoming'],  'amber'],
      ['completed',  'Completed',  $counts['completed'], 'green'],
      ['cancelled',  'Cancelled',  $counts['cancelled'], 'red'],
      ['all',        'All',        $counts['total'],     'teal'],
    ];
    foreach ($chips as [$tab, $label, $n, $color]): ?>
    <a href="?tab=<?= $tab ?>" style="text-decoration:none">
      <div class="mp-stat-card" style="padding:.875rem 1.25rem;cursor:pointer;
           <?= $activeTab===$tab ? 'border-color:var(--mp-primary);box-shadow:0 0 0 2px rgba(10,110,130,.15)' : '' ?>">
        <div class="stat-icon <?= $color ?>" style="width:36px;height:36px;font-size:.9rem">
          <i class="bi bi-<?= $tab==='upcoming'?'clock':'check2-circle' ?>"></i>
        </div>
        <div>
          <div class="stat-value" style="font-size:1.25rem"><?= $n ?></div>
          <div class="stat-label" style="font-size:.7rem"><?= $label ?></div>
        </div>
      </div>
    </a>
    <?php endforeach; ?>
  </div>

  <!-- ── Tabs ── -->
  <div class="mp-card" style="padding:0;overflow:hidden">
    <div class="border-bottom px-4 pt-3">
      <ul class="nav nav-tabs border-0">
        <li class="nav-item">
          <a class="nav-link <?= $activeTab==='upcoming'||!in_array($activeTab,['past','all','completed','cancelled'])?'active':'' ?>"
             href="?tab=upcoming" style="font-family:var(--font-display);font-weight:600">
            Upcoming <span class="badge bg-warning text-dark ms-1" style="font-size:.7rem"><?= $counts['upcoming'] ?></span>
          </a>
        </li>
        <li class="nav-item">
          <a class="nav-link <?= $activeTab==='past'?'active':'' ?>" href="?tab=past"
             style="font-family:var(--font-display);font-weight:600">Past Records</a>
        </li>
        <li class="nav-item">
          <a class="nav-link <?= $activeTab==='all'?'active':'' ?>" href="?tab=all"
             style="font-family:var(--font-display);font-weight:600">All History</a>
        </li>
      </ul>
    </div>

    <?php
    $show = match($activeTab) {
        'past'      => array_values($past),
        'all'       => $appointments,
        'completed' => array_values(array_filter($appointments, fn($a) => $a['status']==='completed')),
        'cancelled' => array_values(array_filter($appointments, fn($a) => $a['status']==='cancelled')),
        default     => array_values($upcoming),
    };
    ?>

    <?php if (empty($show)): ?>
      <div class="text-center py-5" style="color:var(--mp-text-3)">
        <i class="bi bi-calendar2 fs-1 d-block mb-3 opacity-25"></i>
        <div style="font-weight:600">No appointments found</div>
        <?php if ($activeTab==='upcoming'): ?>
          <a href="<?= url('doctors') ?>" class="btn btn-primary btn-sm mt-3">
            <i class="bi bi-plus-lg"></i> Book Your First Appointment
          </a>
        <?php endif; ?>
      </div>
    <?php else: ?>

    <!-- Desktop table -->
    <div class="table-responsive d-none d-md-block">
      <table class="table table-hover mb-0" style="font-size:.875rem">
        <thead style="background:#f8fafc">
          <tr>
            <th class="ps-4">Ref / Date</th>
            <th>Doctor &amp; Speciality</th>
            <th>Reason</th>
            <?php if ($activeTab==='past'||$activeTab==='all'): ?><th>Notes</th><?php endif; ?>
            <th>Fee</th>
            <th>Status</th>
            <th class="pe-4 text-end">Action</th>
          </tr>
        </thead>
        <tbody>
        <?php foreach ($show as $a): ?>
          <tr>
            <td class="ps-4">
              <div><code style="font-size:.78rem;color:var(--mp-primary)"><?= e($a['ref_no']) ?></code></div>
              <div style="font-size:.78rem;color:var(--mp-text-3)">
                <?= formatDate($a['appt_date']) ?> &nbsp;<?= formatTime($a['appt_time']) ?>
              </div>
            </td>
            <td>
              <div style="font-weight:600">Dr. <?= e($a['doctor_name']) ?></div>
              <div style="font-size:.75rem;color:var(--mp-text-3)"><?= e($a['specialization']) ?></div>
              <div style="font-size:.72rem;color:var(--mp-text-3)"><?= e($a['service_name']) ?></div>
            </td>
            <td style="max-width:180px">
              <div style="color:var(--mp-text-2)"><?= e($a['reason'] ?: '—') ?></div>
            </td>
            <?php if ($activeTab==='past'||$activeTab==='all'): ?>
            <td style="max-width:200px">
              <?php if ($a['notes']): ?>
                <div style="font-size:.8rem;color:var(--mp-text-2);font-style:italic">
                  <?= e(mb_substr($a['notes'], 0, 80)) ?><?= strlen($a['notes'])>80?'…':'' ?>
                </div>
              <?php else: ?>
                <span style="color:var(--mp-text-3);font-size:.8rem">—</span>
              <?php endif; ?>
            </td>
            <?php endif; ?>
            <td style="font-weight:600;white-space:nowrap">LKR <?= number_format($a['fee']) ?></td>
            <td><span class="mp-badge <?= $a['status'] ?>"><?= ucfirst($a['status']) ?></span></td>
            <td class="pe-4 text-end">
              <?php if (in_array($a['status'], ['pending','confirmed']) && $a['appt_date'] >= date('Y-m-d')): ?>
              <form method="POST" action="<?= url('appointment/cancel') ?>" class="d-inline">
                <?= CSRF::field() ?>
                <input type="hidden" name="appointment_id" value="<?= $a['id'] ?>">
                <input type="hidden" name="redirect_to" value="<?= url('appointments') ?>">
                <button class="btn btn-sm btn-outline-danger"
                        onclick="return confirm('Cancel this appointment with Dr. <?= e(addslashes($a['doctor_name'])) ?>?')"
                        style="font-size:.78rem">
                  <i class="bi bi-x-circle me-1"></i>Cancel
                </button>
              </form>
              <?php elseif ($a['status'] === 'completed'): ?>
                <div class="d-flex gap-1">
                  <button class="btn btn-sm btn-outline-secondary" style="font-size:.78rem"
                          data-bs-toggle="modal" data-bs-target="#apptDetail<?= $a['id'] ?>">
                    <i class="bi bi-eye me-1"></i>View
                  </button>
                  <a href="<?= url('appointments/'.$a['id'].'/rate') ?>"
                     class="btn btn-sm btn-outline-warning" style="font-size:.78rem"
                     title="Rate this appointment">
                    <i class="bi bi-star"></i>
                  </a>
                </div>
              <?php else: ?>
                <span style="color:var(--mp-text-3);font-size:.8rem">—</span>
              <?php endif; ?>
            </td>
          </tr>
        <?php endforeach; ?>
        </tbody>
      </table>
    </div>

    <!-- Mobile cards -->
    <div class="d-md-none">
      <?php foreach ($show as $a): ?>
      <div class="p-4 border-bottom">
        <div class="d-flex justify-content-between align-items-start mb-2">
          <div>
            <code style="font-size:.78rem;color:var(--mp-primary)"><?= e($a['ref_no']) ?></code>
            <span class="mp-badge <?= $a['status'] ?> ms-2"><?= ucfirst($a['status']) ?></span>
          </div>
          <div style="font-size:.78rem;color:var(--mp-text-3)"><?= formatDate($a['appt_date']) ?></div>
        </div>
        <div style="font-weight:600">Dr. <?= e($a['doctor_name']) ?></div>
        <div style="font-size:.8rem;color:var(--mp-text-3)">
          <?= e($a['specialization']) ?> &middot; <?= formatTime($a['appt_time']) ?>
        </div>
        <?php if ($a['reason']): ?>
        <div style="font-size:.8rem;color:var(--mp-text-2);margin-top:.5rem"><?= e($a['reason']) ?></div>
        <?php endif; ?>
        <div class="d-flex justify-content-between align-items-center mt-2">
          <strong style="color:var(--mp-primary)">LKR <?= number_format($a['fee']) ?></strong>
          <?php if (in_array($a['status'], ['pending','confirmed'])): ?>
          <form method="POST" action="<?= url('appointment/cancel') ?>">
            <?= CSRF::field() ?>
            <input type="hidden" name="appointment_id" value="<?= $a['id'] ?>">
            <input type="hidden" name="redirect_to" value="<?= url('appointments') ?>">
            <button class="btn btn-sm btn-outline-danger"
                    onclick="return confirm('Cancel this appointment?')"
                    style="font-size:.78rem">Cancel</button>
          </form>
          <?php elseif ($a['status'] === 'completed'): ?>
            <button class="btn btn-sm btn-outline-secondary" style="font-size:.78rem"
                    data-bs-toggle="modal" data-bs-target="#apptDetail<?= $a['id'] ?>">
              View Details
            </button>
          <?php endif; ?>
        </div>
      </div>
      <?php endforeach; ?>
    </div>

    <?php endif; ?>
  </div><!-- /.mp-card -->

</div>
</div>

<!-- ── Detail modals for completed appointments ── -->
<?php foreach ($show as $a):
    if ($a['status'] !== 'completed') continue; ?>
<div class="modal fade" id="apptDetail<?= $a['id'] ?>" tabindex="-1">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header" style="border-bottom:1px solid var(--mp-border)">
        <div>
          <h6 class="modal-title mb-0">Appointment Details</h6>
          <div style="font-size:.78rem;color:var(--mp-text-3)"><?= e($a['ref_no']) ?></div>
        </div>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body">
        <div class="row g-3">
          <div class="col-6">
            <div style="font-size:.75rem;color:var(--mp-text-3);text-transform:uppercase;letter-spacing:.06em">Doctor</div>
            <div style="font-weight:600">Dr. <?= e($a['doctor_name']) ?></div>
            <div style="font-size:.8rem;color:var(--mp-text-3)"><?= e($a['specialization']) ?></div>
          </div>
          <div class="col-6">
            <div style="font-size:.75rem;color:var(--mp-text-3);text-transform:uppercase;letter-spacing:.06em">Date &amp; Time</div>
            <div style="font-weight:600"><?= formatDate($a['appt_date']) ?></div>
            <div style="font-size:.8rem;color:var(--mp-text-3)"><?= formatTime($a['appt_time']) ?></div>
          </div>
          <div class="col-6">
            <div style="font-size:.75rem;color:var(--mp-text-3);text-transform:uppercase;letter-spacing:.06em">Status</div>
            <span class="mp-badge completed">Completed</span>
          </div>
          <div class="col-6">
            <div style="font-size:.75rem;color:var(--mp-text-3);text-transform:uppercase;letter-spacing:.06em">Consultation Fee</div>
            <div style="font-weight:700;color:var(--mp-primary)">LKR <?= number_format($a['fee']) ?></div>
          </div>
          <?php if ($a['reason']): ?>
          <div class="col-12">
            <div style="font-size:.75rem;color:var(--mp-text-3);text-transform:uppercase;letter-spacing:.06em;margin-bottom:.25rem">Reason for Visit</div>
            <div style="background:#f8fafc;border-radius:8px;padding:.75rem;font-size:.875rem">
              <?= e($a['reason']) ?>
            </div>
          </div>
          <?php endif; ?>
          <?php if ($a['notes']): ?>
          <div class="col-12">
            <div style="font-size:.75rem;color:var(--mp-text-3);text-transform:uppercase;letter-spacing:.06em;margin-bottom:.25rem">Doctor's Notes</div>
            <div style="background:#ECFDF5;border:1px solid #D1FAE5;border-radius:8px;padding:.75rem;font-size:.875rem">
              <i class="bi bi-file-earmark-medical text-success me-1"></i>
              <?= e($a['notes']) ?>
            </div>
          </div>
          <?php endif; ?>
        </div>
      </div>
      <div class="modal-footer" style="border-top:1px solid var(--mp-border)">
        <button type="button" class="btn btn-sm btn-outline-secondary" data-bs-dismiss="modal">Close</button>
        <a href="<?= url('reports') ?>" class="btn btn-sm btn-primary">
          <i class="bi bi-file-earmark-medical me-1"></i>View Reports
        </a>
      </div>
    </div>
  </div>
</div>
<?php endforeach; ?>

<?php require VIEW_PATH . '/layouts/footer.php'; ?>
