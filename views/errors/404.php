<?php
$title   = $title   ?? '404 — Page Not Found | MediCare Plus';
$heading = $heading ?? 'Page Not Found';
$message = $message ?? 'The page you are looking for does not exist or has been moved.';

require VIEW_PATH . '/layouts/header.php';
require VIEW_PATH . '/layouts/navbar.php';
?>

<div style="min-height:calc(100vh - 140px);display:flex;align-items:center;justify-content:center;
            padding:2rem 1rem">
  <div style="text-align:center;max-width:480px">
    <div style="font-size:7rem;font-weight:900;font-family:var(--font-display);
                color:var(--mp-primary);line-height:1;letter-spacing:-.04em;opacity:.18">
      404
    </div>
    <div style="margin-top:-2rem;margin-bottom:1.25rem">
      <div style="width:72px;height:72px;border-radius:50%;background:var(--mp-primary-light);
                  display:flex;align-items:center;justify-content:center;
                  margin:0 auto 1.25rem">
        <i class="bi bi-search" style="font-size:2rem;color:var(--mp-primary)"></i>
      </div>
      <h1 style="font-size:1.6rem;margin-bottom:.5rem"><?= e($heading) ?></h1>
      <p style="color:var(--mp-text-3);font-size:.95rem;margin-bottom:2rem"><?= e($message) ?></p>
      <div class="d-flex gap-3 justify-content-center flex-wrap">
        <a href="<?= url('') ?>" class="btn btn-primary px-4">
          <i class="bi bi-house me-2"></i>Go Home
        </a>
        <button onclick="history.back()" class="btn btn-outline-secondary px-4">
          <i class="bi bi-arrow-left me-2"></i>Go Back
        </button>
      </div>
    </div>
  </div>
</div>

<?php require VIEW_PATH . '/layouts/footer.php'; ?>
