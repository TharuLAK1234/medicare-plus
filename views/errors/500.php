<?php
$title = $title ?? '500 — Server Error | MediCare Plus';

require VIEW_PATH . '/layouts/header.php';
require VIEW_PATH . '/layouts/navbar.php';
?>

<div style="min-height:calc(100vh - 140px);display:flex;align-items:center;justify-content:center;
            padding:2rem 1rem">
  <div style="text-align:center;max-width:480px">
    <div style="font-size:7rem;font-weight:900;font-family:var(--font-display);
                color:var(--mp-accent);line-height:1;letter-spacing:-.04em;opacity:.18">
      500
    </div>
    <div style="margin-top:-2rem;margin-bottom:1.25rem">
      <div style="width:72px;height:72px;border-radius:50%;background:#FFFBEB;
                  display:flex;align-items:center;justify-content:center;
                  margin:0 auto 1.25rem">
        <i class="bi bi-exclamation-triangle" style="font-size:2rem;color:var(--mp-accent)"></i>
      </div>
      <h1 style="font-size:1.6rem;margin-bottom:.5rem">Something Went Wrong</h1>
      <p style="color:var(--mp-text-3);font-size:.95rem;margin-bottom:2rem">
        An unexpected error occurred. The issue has been logged and our team will look into it.
      </p>
      <div class="d-flex gap-3 justify-content-center flex-wrap">
        <a href="<?= url('') ?>" class="btn btn-primary px-4">
          <i class="bi bi-house me-2"></i>Go Home
        </a>
        <button onclick="location.reload()" class="btn btn-outline-secondary px-4">
          <i class="bi bi-arrow-clockwise me-2"></i>Try Again
        </button>
      </div>
    </div>
  </div>
</div>

<?php require VIEW_PATH . '/layouts/footer.php'; ?>
