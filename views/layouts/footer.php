<footer style="background:#1E293B;color:#94A3B8;padding:2.5rem 0;margin-top:auto">
  <div class="container">
    <div class="row align-items-center">
      <div class="col-md-4 mb-3 mb-md-0">
        <div style="font-family:var(--font-display);font-weight:800;font-size:1.1rem;color:#fff;margin-bottom:.25rem">
          <i class="bi bi-hospital-fill" style="color:#0D9488"></i> MediCarePlus
        </div>
        <div style="font-size:.8rem">Compassionate care, trusted by thousands.</div>
      </div>
      <div class="col-md-4 text-center mb-3 mb-md-0">
        <div style="font-size:.8rem">
          <a href="<?= url('') ?>" style="color:#94A3B8;text-decoration:none;margin:0 .75rem">Home</a>
          <a href="<?= url('services') ?>" style="color:#94A3B8;text-decoration:none;margin:0 .75rem">Services</a>
          <a href="<?= url('doctors') ?>" style="color:#94A3B8;text-decoration:none;margin:0 .75rem">Doctors</a>
        </div>
      </div>
      <div class="col-md-4 text-md-end">
        <div style="font-size:.78rem">&copy; <?= date('Y') ?> MediCare Plus. All rights reserved.</div>
        <div style="font-size:.75rem;margin-top:.25rem;color:#475569">
          CSE5009 &mdash; Web Application Development
        </div>
      </div>
    </div>
  </div>
</footer>

<!-- Bootstrap 5 JS -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.3/js/bootstrap.bundle.min.js"></script>
<?php if (isset($extraScripts)) echo $extraScripts; ?>
</body>
</html>
