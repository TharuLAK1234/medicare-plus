<?php
require VIEW_PATH . '/layouts/header.php';
require VIEW_PATH . '/layouts/navbar.php';
?>

<div class="mp-page-wrap">
<div class="container-xl">

  <div class="d-flex align-items-center justify-content-between mb-4">
    <div>
      <h1 class="mb-1" style="font-size:1.5rem">Patient Reports</h1>
      <p style="color:var(--mp-text-3);margin:0;font-size:.875rem">
        Upload lab results, prescriptions, and summaries for your patients.
      </p>
    </div>
    <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#uploadModal">
      <i class="bi bi-upload me-1"></i> Upload Report
    </button>
  </div>

  <!-- Recent uploads table -->
  <?php if (empty($reports)): ?>
  <div class="mp-card text-center" style="padding:3.5rem">
    <i class="bi bi-cloud-upload fs-1 d-block mb-2 opacity-25" style="color:var(--mp-text-3)"></i>
    <div style="font-weight:600">No reports uploaded yet</div>
    <button class="btn btn-primary btn-sm mt-3" data-bs-toggle="modal" data-bs-target="#uploadModal">
      Upload First Report
    </button>
  </div>
  <?php else: ?>
  <div class="mp-card" style="padding:0;overflow:hidden">
    <div class="table-responsive">
      <table class="table table-hover mb-0" style="font-size:.875rem">
        <thead style="background:#f8fafc">
          <tr>
            <th class="ps-4">Title / Type</th>
            <th>Patient</th>
            <th>Appointment</th>
            <th>Date</th>
            <th>Size</th>
            <th class="pe-4 text-end">Actions</th>
          </tr>
        </thead>
        <tbody>
        <?php foreach ($reports as $r): ?>
          <tr>
            <td class="ps-4">
              <div style="font-weight:600"><?= e($r['title']) ?></div>
              <span class="mp-badge teal" style="font-size:.7rem"><?= ucfirst($r['type']) ?></span>
            </td>
            <td style="font-weight:600"><?= e($r['patient_name']) ?></td>
            <td>
              <?= $r['appt_ref']
                  ? '<code style="font-size:.78rem;color:var(--mp-primary)">'.e($r['appt_ref']).'</code>'
                  : '—' ?>
            </td>
            <td style="font-size:.8rem;color:var(--mp-text-3)"><?= formatDate($r['created_at']) ?></td>
            <td style="font-size:.8rem;color:var(--mp-text-3)">
              <?= $r['file_size'] ? round($r['file_size']/1024, 1) . ' KB' : '—' ?>
            </td>
            <td class="pe-4 text-end">
              <div class="d-flex gap-1 justify-content-end">
                <a href="<?= url('reports/download/' . $r['id']) ?>"
                   class="btn btn-sm btn-outline-primary" style="font-size:.75rem">
                  <i class="bi bi-download"></i>
                </a>
                <form method="POST" action="<?= url('reports/delete/' . $r['id']) ?>" class="d-inline">
                  <?= CSRF::field() ?>
                  <button class="btn btn-sm btn-outline-danger" style="font-size:.75rem"
                          onclick="return confirm('Delete this report? This cannot be undone.')">
                    <i class="bi bi-trash"></i>
                  </button>
                </form>
              </div>
            </td>
          </tr>
        <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  </div>
  <?php endif; ?>

</div>
</div>

<!-- Upload Modal -->
<div class="modal fade" id="uploadModal" tabindex="-1">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header border-0 pb-0">
        <h5 class="modal-title" style="font-family:var(--font-display);font-weight:700">
          Upload Patient Report
        </h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <form method="POST" action="<?= url('doctor/reports/upload') ?>" enctype="multipart/form-data">
        <?= CSRF::field() ?>
        <div class="modal-body">

          <div class="mb-3">
            <label class="form-label" style="font-weight:600">Patient <span class="text-danger">*</span></label>
            <select name="patient_id" class="form-select" required>
              <option value="">— Select patient —</option>
              <?php foreach ($patients as $p): ?>
              <option value="<?= $p['id'] ?>"><?= e($p['name']) ?></option>
              <?php endforeach; ?>
            </select>
          </div>

          <div class="mb-3">
            <label class="form-label" style="font-weight:600">Report Title <span class="text-danger">*</span></label>
            <input type="text" name="title" class="form-control" required
                   placeholder="e.g. Blood Test Results — July 2026">
          </div>

          <div class="row g-3 mb-3">
            <div class="col-6">
              <label class="form-label" style="font-weight:600">Type</label>
              <select name="type" class="form-select">
                <option value="lab">Lab Result</option>
                <option value="prescription">Prescription</option>
                <option value="summary">Clinical Summary</option>
                <option value="imaging">Imaging / X-Ray</option>
                <option value="other">Other</option>
              </select>
            </div>
            <div class="col-6">
              <label class="form-label" style="font-weight:600">Appointment Ref (opt.)</label>
              <input type="number" name="appointment_id" class="form-control"
                     placeholder="Appointment ID">
            </div>
          </div>

          <div class="mb-3">
            <label class="form-label" style="font-weight:600">File <span class="text-danger">*</span></label>
            <input type="file" name="report_file" class="form-control" required
                   accept=".pdf,.jpg,.jpeg,.png,.doc,.docx">
            <div style="font-size:.75rem;color:var(--mp-text-3);margin-top:.25rem">
              PDF, image, or Word document. Max 10 MB.
            </div>
          </div>

        </div>
        <div class="modal-footer border-0 pt-0">
          <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
          <button type="submit" class="btn btn-primary">
            <i class="bi bi-upload me-1"></i>Upload Report
          </button>
        </div>
      </form>
    </div>
  </div>
</div>

<?php require VIEW_PATH . '/layouts/footer.php'; ?>
