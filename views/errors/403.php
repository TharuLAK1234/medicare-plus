<?php
$title = $title ?? '403 — Access Denied | MediCare Plus';

require VIEW_PATH . '/layouts/header.php';
require VIEW_PATH . '/layouts/navbar.php';
?>

<div style="min-height:calc(100vh - 140px);display:flex;align-items:center;justify-content:center;
            padding:2rem 1rem">
  <div style="text-align:center;max-width:480px">
    <div style="font-size:7rem;font-weight:900;font-family:var(--font-display);
                color:var(--mp-danger);line-height:1;letter-spacing:-.04em;opacity:.14">
      403
    </div>
    <div style="margin-top:-2rem;margin-bottom:1.25rem">
      <div style="width:72px;height:72px;border-radius:50%;background:#FEF2F2;
                  display:flex;align-items:center;justify-content:center;
                  margin:0 auto 1.25rem">
        <i class="bi bi-shield-x" style="font-size:2rem;color:var(--mp-danger)"></i>
      </div>
      <h1 style="font-size:1.6rem;margin-bottom:.5rem">Access Denied</h1>
      <p style="color:var(--mp-text-3);font-size:.95rem;margin-bottom:2rem">
        You do not have permission to view this page.
        If you believe this is an error, please sign in with the correct account.
      </p>
      <div class="d-flex gap-3 justify-content-center flex-wrap">
        <a href="<?= url('') ?>" class="btn btn-primary px-4">
          <i class="bi bi-house me-2"></i>Go Home
        </a>
        <a href="<?= url('login') ?>" class="btn btn-outline-secondary px-4">
          <i class="bi bi-box-arrow-in-right me-2"></i>Sign In
        </a>
      </div>
    </div>
  </div>
</div>

<?php require VIEW_PATH . '/layouts/footer.php'; ?>
