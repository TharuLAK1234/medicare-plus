<?php
require VIEW_PATH . '/layouts/header.php';
require VIEW_PATH . '/layouts/navbar.php';

// ── Live data ──────────────────────────────────────────────
$apptCounts = Appointment::globalCounts();
$revenue    = Appointment::totalRevenue();
$recent     = Appointment::recent(12);
$doctors    = Doctor::allWithStats();

$userStats = Database::queryOne(
    "SELECT
        COUNT(*)                                   AS total,
        SUM(role = 'doctor')                       AS doctors,
        SUM(role = 'patient')                      AS patients,
        SUM(role = 'admin')                        AS admins,
        SUM(status = 'inactive')                   AS inactive,
        SUM(DATE(created_at) = CURDATE())          AS today_new
     FROM users"
);
$todayAppts = Database::queryOne(
    "SELECT COUNT(*) AS n FROM appointments WHERE appt_date = CURDATE()"
)['n'] ?? 0;
?>

<div class="mp-page-wrap">
<div class="container-xl">

  <!-- ── Page header ── -->
  <div class="d-flex align-items-start justify-content-between mb-4">
    <div>
      <h1 class="mb-1" style="font-size:1.5rem">Admin Dashboard</h1>
      <p style="color:var(--mp-text-3);margin:0;font-size:.875rem">
        <i class="bi bi-calendar3 me-1"></i><?= date('l, d F Y') ?>
        &nbsp;&middot;&nbsp;
        <i class="bi bi-clock me-1"></i><?= date('H:i') ?> LKT
      </p>
    </div>
    <a href="<?= url('admin/appointments') ?>" class="btn btn-primary btn-sm">
      <i class="bi bi-calendar2-check me-1"></i> All Appointments
    </a>
  </div>

  <!-- ── Stat cards: row 1 ── -->
  <div class="row g-3 mb-4">
    <?php
    $stats = [
      ['icon'=>'people-fill',      'color'=>'blue',   'val'=>$userStats['total'],         'label'=>'Total Users',         'sub'=>'+'.$userStats['today_new'].' today'],
      ['icon'=>'person-badge-fill','color'=>'teal',   'val'=>$userStats['doctors'],        'label'=>'Doctors',             'sub'=>'Active specialists'],
      ['icon'=>'person-heart-fill','color'=>'green',  'val'=>$userStats['patients'],       'label'=>'Patients',            'sub'=>'Registered'],
      ['icon'=>'calendar2-check',  'color'=>'amber',  'val'=>$apptCounts['total'],         'label'=>'Total Appointments',  'sub'=>$todayAppts.' today'],
      ['icon'=>'clock-history',    'color'=>'red',    'val'=>$apptCounts['pending'],       'label'=>'Awaiting Confirm',    'sub'=>'Needs attention'],
      ['icon'=>'currency-dollar',  'color'=>'purple', 'val'=>'LKR '.number_format($revenue),'label'=>'Total Revenue',     'sub'=>'From completed appts'],
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

  <!-- ── Appointment status bar ── -->
  <div class="mp-card mb-4" style="padding:1.25rem">
    <div class="d-flex justify-content-between align-items-center mb-3">
      <h6 class="mb-0">Appointment Status Overview</h6>
      <a href="<?= url('admin/appointments') ?>" class="btn btn-sm btn-outline-primary">View All</a>
    </div>
    <div class="row g-3">
      <?php
      $statuses = [
        'pending'   => ['Pending',   'mp-badge pending',   '#F59E0B', $apptCounts['pending']],
        'confirmed' => ['Confirmed', 'mp-badge confirmed', '#0EA5E9', $apptCounts['confirmed']],
        'completed' => ['Completed', 'mp-badge completed', '#10B981', $apptCounts['completed']],
        'cancelled' => ['Cancelled', 'mp-badge cancelled', '#EF4444', $apptCounts['cancelled']],
      ];
      $total = max($apptCounts['total'], 1);
      foreach ($statuses as [$label, $cls, $color, $n]): ?>
      <div class="col-sm-6 col-xl-3">
        <div style="background:#f8fafc;border-radius:10px;padding:1rem">
          <div class="d-flex justify-content-between mb-2">
            <span class="<?= $cls ?>"><?= $label ?></span>
            <strong><?= $n ?></strong>
          </div>
          <div style="height:6px;background:#E2E8F0;border-radius:3px;overflow:hidden">
            <div style="height:100%;width:<?= round($n/$total*100) ?>%;background:<?= $color ?>;border-radius:3px;transition:width .6s"></div>
          </div>
          <div style="font-size:.75rem;color:var(--mp-text-3);margin-top:.4rem">
            <?= round($n/$total*100) ?>% of total
          </div>
        </div>
      </div>
      <?php endforeach; ?>
    </div>
  </div>

  <div class="row g-4 mb-4">

    <!-- ── Recent appointments table ── -->
    <div class="col-xl-8">
      <div class="mp-card" style="padding:0;overflow:hidden">
        <div class="d-flex justify-content-between align-items-center p-4 border-bottom">
          <h6 class="mb-0">Recent Appointments</h6>
          <a href="<?= url('admin/appointments') ?>" class="btn btn-sm btn-outline-primary">Full List</a>
        </div>
        <div class="table-responsive">
          <table class="table table-hover mb-0" style="font-size:.875rem">
            <thead style="background:#f8fafc">
              <tr>
                <th class="ps-4">Ref</th>
                <th>Patient</th>
                <th>Doctor</th>
                <th>Date</th>
                <th>Status</th>
                <th class="text-end pe-4">Fee</th>
              </tr>
            </thead>
            <tbody>
            <?php foreach ($recent as $a): ?>
              <tr>
                <td class="ps-4">
                  <code style="font-size:.78rem;color:var(--mp-primary)"><?= e($a['ref_no']) ?></code>
                </td>
                <td>
                  <div style="font-weight:600"><?= e($a['patient_name']) ?></div>
                  <div style="font-size:.75rem;color:var(--mp-text-3)"><?= e($a['patient_email']) ?></div>
                </td>
                <td>
                  <div style="font-weight:600">Dr. <?= e($a['doctor_name']) ?></div>
                  <div style="font-size:.75rem;color:var(--mp-text-3)"><?= e($a['service_name']) ?></div>
                </td>
                <td>
                  <div><?= formatDate($a['appt_date']) ?></div>
                  <div style="font-size:.75rem;color:var(--mp-text-3)"><?= formatTime($a['appt_time']) ?></div>
                </td>
                <td><span class="mp-badge <?= $a['status'] ?>"><?= ucfirst($a['status']) ?></span></td>
                <td class="text-end pe-4" style="font-weight:600">
                  LKR <?= number_format($a['fee']) ?>
                </td>
              </tr>
            <?php endforeach; ?>
            </tbody>
          </table>
        </div>
      </div>
    </div>

    <!-- ── Doctor panel ── -->
    <div class="col-xl-4">
      <div class="mp-card" style="padding:0;overflow:hidden">
        <div class="p-4 border-bottom d-flex justify-content-between align-items-center">
          <h6 class="mb-0">Doctors</h6>
          <a href="<?= url('admin/doctors') ?>" class="btn btn-sm btn-outline-primary">Manage</a>
        </div>
        <div style="max-height:400px;overflow-y:auto">
          <?php foreach ($doctors as $d): ?>
          <div class="d-flex align-items-center gap-3 p-3 border-bottom">
            <div style="width:40px;height:40px;border-radius:50%;background:var(--mp-primary-light);
                        display:flex;align-items:center;justify-content:center;
                        color:var(--mp-primary);font-weight:700;font-size:.9rem;flex-shrink:0">
              <?= e(mb_substr($d['name'], 0, 1)) ?>
            </div>
            <div style="flex:1;min-width:0">
              <div style="font-weight:600;font-size:.875rem;white-space:nowrap;overflow:hidden;text-overflow:ellipsis">
                Dr. <?= e($d['name']) ?>
              </div>
              <div style="font-size:.75rem;color:var(--mp-text-3)"><?= e($d['service_name']) ?></div>
            </div>
            <div style="text-align:right;flex-shrink:0">
              <div style="font-size:.8rem;color:#F59E0B;font-weight:600">
                <?php $r = $d['avg_rating'] ?? 0; ?>
                <i class="bi bi-star-fill" style="font-size:.7rem"></i> <?= $r ? number_format($r,1) : '—' ?>
              </div>
              <div style="font-size:.72rem;color:var(--mp-text-3)"><?= $d['appt_count'] ?> completed</div>
            </div>
          </div>
          <?php endforeach; ?>
        </div>
      </div>
    </div>

  </div><!-- /.row -->

  <!-- ── Quick actions ── -->
  <div class="row g-3">
    <?php
    $actions = [
      ['icon'=>'person-badge-fill','color'=>'teal',   'label'=>'Manage Doctors',     'url'=>url('admin/doctors'),      'sub'=>'Add · Edit · Deactivate'],
      ['icon'=>'people-fill',      'color'=>'blue',   'label'=>'Manage Users',        'url'=>url('admin/users'),        'sub'=>'View · Activate · Suspend'],
      ['icon'=>'calendar2-check',  'color'=>'amber',  'label'=>'Appointments',        'url'=>url('admin/appointments'), 'sub'=>'View all bookings'],
      ['icon'=>'grid-3x3-gap-fill','color'=>'green',  'label'=>'Services',            'url'=>url('admin/services'),     'sub'=>'Enable · Disable'],
    ];
    foreach ($actions as $a): ?>
    <div class="col-sm-6 col-xl-3">
      <a href="<?= $a['url'] ?>" class="text-decoration-none">
        <div class="mp-card mp-card-sm d-flex align-items-center gap-3" style="transition:box-shadow .15s" onmouseover="this.style.boxShadow='var(--shadow-md)'" onmouseout="this.style.boxShadow=''">
          <div class="stat-icon <?= $a['color'] ?>" style="width:44px;height:44px;font-size:1.2rem;flex-shrink:0">
            <i class="bi bi-<?= $a['icon'] ?>"></i>
          </div>
          <div>
            <div style="font-family:var(--font-display);font-weight:700;font-size:.9rem;color:var(--mp-text-1)"><?= $a['label'] ?></div>
            <div style="font-size:.75rem;color:var(--mp-text-3)"><?= $a['sub'] ?></div>
          </div>
          <i class="bi bi-arrow-right ms-auto" style="color:var(--mp-text-3)"></i>
        </div>
      </a>
    </div>
    <?php endforeach; ?>
  </div>

</div>
</div>

<?php require VIEW_PATH . '/layouts/footer.php'; ?>
