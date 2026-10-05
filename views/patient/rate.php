<?php
require VIEW_PATH . '/layouts/header.php';
require VIEW_PATH . '/layouts/navbar.php';
// $appt passed from controller
?>

<div class="mp-page-wrap">
<div class="container" style="max-width:520px">
  <div class="mp-card">
    <div class="text-center mb-4">
      <i class="bi bi-star-fill" style="font-size:2rem;color:#F59E0B"></i>
      <h2 style="font-family:var(--font-display);font-weight:800;font-size:1.4rem;margin:.75rem 0 .25rem">
        Rate Your Appointment
      </h2>
      <p style="color:var(--mp-text-3);font-size:.875rem;margin:0">
        With Dr. <?= e($appt['doctor_name']) ?> on <?= formatDate($appt['appt_date']) ?>
      </p>
    </div>

    <form method="POST" action="<?= url('ratings/store') ?>">
      <?= CSRF::field() ?>
      <input type="hidden" name="appointment_id" value="<?= $appt['id'] ?>">

      <!-- Star picker -->
      <div class="mb-4 text-center">
        <label class="form-label" style="font-weight:600;display:block;margin-bottom:.75rem">Your Rating</label>
        <div class="d-flex justify-content-center gap-2" id="starPicker" style="font-size:2.5rem;cursor:pointer">
          <?php for ($i=1;$i<=5;$i++): ?>
          <i class="bi bi-star" data-val="<?= $i ?>" style="color:#E2E8F0;transition:color .1s"></i>
          <?php endfor; ?>
        </div>
        <input type="hidden" name="stars" id="starsInput" value="">
        <div id="starError" class="text-danger mt-1" style="font-size:.8rem;display:none">Please select a rating.</div>
      </div>

      <div class="mb-4">
        <label class="form-label" style="font-weight:600">Review <span style="color:var(--mp-text-3);font-weight:400">(optional)</span></label>
        <textarea name="review" class="form-control" rows="3"
                  placeholder="Share your experience with other patients…"></textarea>
      </div>

      <button type="submit" id="submitBtn" class="btn btn-primary w-100" style="font-weight:700">
        <i class="bi bi-star-fill me-1"></i> Submit Rating
      </button>
      <a href="<?= url('appointments') ?>" class="btn btn-outline-secondary w-100 mt-2">Skip for now</a>
    </form>
  </div>
</div>
</div>

<script>
const stars = document.querySelectorAll('#starPicker i');
const input = document.getElementById('starsInput');
let selected = 0;

stars.forEach(s => {
  s.addEventListener('mouseenter', () => highlightTo(+s.dataset.val));
  s.addEventListener('mouseleave', () => highlightTo(selected));
  s.addEventListener('click', () => {
    selected = +s.dataset.val;
    input.value = selected;
    highlightTo(selected);
    document.getElementById('starError').style.display = 'none';
  });
});

function highlightTo(n) {
  stars.forEach((s, i) => {
    s.classList.toggle('bi-star-fill', i < n);
    s.classList.toggle('bi-star', i >= n);
    s.style.color = i < n ? '#F59E0B' : '#E2E8F0';
  });
}

document.querySelector('form').addEventListener('submit', function(e) {
  if (!input.value) {
    e.preventDefault();
    document.getElementById('starError').style.display = 'block';
  }
});
</script>

<?php require VIEW_PATH . '/layouts/footer.php'; ?>
