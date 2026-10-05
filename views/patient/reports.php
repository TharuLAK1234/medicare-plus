<?php
require VIEW_PATH . '/layouts/header.php';
require VIEW_PATH . '/layouts/navbar.php';
?>

<div class="mp-page-wrap">
<div class="container-xl">

  <div class="d-flex align-items-center justify-content-between mb-4">
    <div>
      <h1 class="mb-1" style="font-size:1.5rem">Medical Reports</h1>
      <p style="color:var(--mp-text-3);margin:0;font-size:.875rem">
        Reports uploaded by your doctors.
      </p>
    </div>
    <a href="<?= url('patient/dashboard') ?>" class="btn btn-outline-secondary btn-sm">
      <i class="bi bi-arrow-left me-1"></i> Dashboard
    </a>
  </div>

  <?php if (empty($reports)): ?>
  <div class="mp-card text-center" style="padding:4rem">
    <i class="bi bi-file-earmark-medical fs-1 d-block mb-3 opacity-25" style="color:var(--mp-text-3)"></i>
    <div style="font-weight:600;font-size:1.1rem">No reports yet</div>
    <p style="color:var(--mp-text-3);font-size:.875rem">
      Your doctors will upload lab results, prescriptions, and summaries here.
    </p>
  </div>
  <?php else: ?>

  <!-- Group by type -->
  <?php
  $grouped = [];
  foreach ($reports as $r) {
      $grouped[$r['type']][] = $r;
  }
  $typeColors = [
    'lab'          => ['green',  'bi-clipboard2-pulse-fill', 'Lab Results'],
    'prescription' => ['blue',   'bi-capsule-pill',          'Prescriptions'],
    'summary'      => ['teal',   'bi-file-earmark-text-fill','Summaries'],
    'imaging'      => ['purple', 'bi-image-fill',            'Imaging'],
    'other'        => ['amber',  'bi-file-earmark-fill',     'Other'],
  ];
  ?>

  <div class="row g-4">
    <!-- Filter pills -->
    <div class="col-12">
      <div class="d-flex flex-wrap gap-2 mb-2">
        <a href="?type=" class="btn btn-sm <?= !isset($_GET['type'])?'btn-primary':'btn-outline-secondary' ?>">
          All (<?= count($reports) ?>)
        </a>
        <?php foreach ($typeColors as $t => [$color, $icon, $label]): ?>
          <?php $cnt = count($grouped[$t] ?? []); if (!$cnt) continue; ?>
          <a href="?type=<?= $t ?>" class="btn btn-sm <?= ($_GET['type']??'')===$t?'btn-primary':'btn-outline-secondary' ?>">
            <i class="bi bi-<?= $icon ?> me-1"></i><?= $label ?> (<?= $cnt ?>)
          </a>
        <?php endforeach; ?>
      </div>
    </div>

    <?php
    $show = isset($_GET['type']) && $_GET['type']
        ? array_filter($reports, fn($r) => $r['type'] === $_GET['type'])
        : $reports;
    ?>

    <div class="col-12">
      <div class="mp-card" style="padding:0;overflow:hidden">
        <div class="table-responsive">
          <table class="table table-hover mb-0" style="font-size:.875rem">
            <thead style="background:#f8fafc">
              <tr>
                <th class="ps-4">Report</th>
                <th>Type</th>
                <th>Doctor</th>
                <th>Appointment</th>
                <th>Date</th>
                <th>Size</th>
                <th class="pe-4 text-end">Download</th>
              </tr>
            </thead>
            <tbody>
            <?php foreach ($show as $r):
              [$color, $icon] = $typeColors[$r['type']] ?? ['teal', 'bi-file-earmark'];
            ?>
              <tr>
                <td class="ps-4">
                  <div class="d-flex align-items-center gap-2">
                    <div class="stat-icon <?= $color ?>" style="width:32px;height:32px;font-size:.8rem;flex-shrink:0">
                      <i class="bi bi-<?= $icon ?>"></i>
                    </div>
                    <div style="font-weight:600"><?= e($r['title']) ?></div>
                  </div>
                </td>
                <td>
                  <span class="mp-badge <?= $color ?>">
                    <?= ucfirst($r['type']) ?>
                  </span>
                </td>
                <td>
                  <?php if ($r['doctor_name']): ?>
                  <div style="font-weight:600;font-size:.85rem">Dr. <?= e($r['doctor_name']) ?></div>
                  <div style="font-size:.75rem;color:var(--mp-text-3)"><?= e($r['specialization'] ?? '') ?></div>
                  <?php else: ?><span style="color:var(--mp-text-3)">—</span><?php endif; ?>
                </td>
                <td>
                  <?= $r['appt_ref'] ? '<code style="font-size:.78rem;color:var(--mp-primary)">'.e($r['appt_ref']).'</code>' : '—' ?>
                </td>
                <td style="font-size:.8rem;color:var(--mp-text-3);white-space:nowrap">
                  <?= formatDate($r['created_at']) ?>
                </td>
                <td style="font-size:.8rem;color:var(--mp-text-3)">
                  <?= $r['file_size'] ? round($r['file_size']/1024, 1) . ' KB' : '—' ?>
                </td>
                <td class="pe-4 text-end">
                  <a href="<?= url('reports/download/' . $r['id']) ?>"
                     class="btn btn-sm btn-primary" style="font-size:.78rem">
                    <i class="bi bi-download me-1"></i>Download
                  </a>
                </td>
              </tr>
            <?php endforeach; ?>
            </tbody>
          </table>
        </div>
      </div>
    </div>
  </div>
  <?php endif; ?>

</div>
</div>

<?php require VIEW_PATH . '/layouts/footer.php'; ?>
