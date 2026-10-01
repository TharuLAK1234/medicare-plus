<?php
require VIEW_PATH . '/layouts/header.php';
require VIEW_PATH . '/layouts/navbar.php';
?>
<div class="mp-page-wrap">
  <div class="container text-center py-5">
    <div style="max-width:560px;margin:auto">
      <i class="bi bi-hospital-fill" style="font-size:4rem;color:var(--mp-primary)"></i>
      <h1 class="mt-3 mb-2" style="font-size:2.2rem">MediCare Plus</h1>
      <p style="color:var(--mp-text-3);font-size:1.05rem;margin-bottom:2rem">
        Quality healthcare, right at your fingertips.<br>
        Phase 5 will build this page into a full landing page.
      </p>
      <?php if (!Auth::check()): ?>
        <div class="d-flex gap-3 justify-content-center">
          <a href="<?= url('register') ?>" class="btn btn-primary px-4 py-2">Get Started</a>
          <a href="<?= url('login') ?>"    class="btn btn-outline-primary px-4 py-2">Sign In</a>
        </div>
      <?php else: ?>
        <a href="<?= Middleware::dashboardUrl() ?>" class="btn btn-primary px-5 py-2">
          Go to Dashboard <i class="bi bi-arrow-right"></i>
        </a>
      <?php endif; ?>
    </div>
  </div>
</div>
<?php require VIEW_PATH . '/layouts/footer.php'; ?>
