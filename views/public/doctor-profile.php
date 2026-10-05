<?php
require VIEW_PATH . '/layouts/header.php';
require VIEW_PATH . '/layouts/navbar.php';

// $doctor, $rating, $ratings, $availability, $slots passed from controller
?>

<div style="background:linear-gradient(135deg,var(--mp-primary),#0D9488);padding:3rem 0 0">
  <div class="container">
    <a href="<?= url('doctors') ?>" style="color:rgba(255,255,255,.75);font-size:.875rem;text-decoration:none">
      <i class="bi bi-arrow-left me-1"></i> Back to Doctors
    </a>
    <div class="row align-items-end g-4 mt-2 pb-4">
      <div class="col-auto">
        <div style="width:80px;height:80px;border-radius:50%;border:4px solid rgba(255,255,255,.4);
                    background:var(--mp-primary-light);display:flex;align-items:center;
                    justify-content:center;font-size:2rem;font-weight:800;color:var(--mp-primary)">
          <?= mb_strtoupper(mb_substr($doctor['name'],0,1)) ?>
        </div>
      </div>
      <div class="col">
        <h1 style="color:#fff;font-family:var(--font-display);font-size:1.75rem;font-weight:800;margin-bottom:.25rem">
          Dr. <?= e($doctor['name']) ?>
        </h1>
        <div style="color:rgba(255,255,255,.85);font-size:.95rem">
          <?= e($doctor['service_name'] ?? '') ?> · <?= e($doctor['specialization']) ?>
        </div>
        <div class="d-flex flex-wrap gap-3 mt-2" style="font-size:.85rem;color:rgba(255,255,255,.75)">
          <span><i class="bi bi-briefcase me-1"></i><?= $doctor['experience_years'] ?> years experience</span>
          <span><i class="bi bi-geo-alt me-1"></i><?= e($doctor['location'] ?? 'Colombo') ?></span>
          <span><i class="bi bi-cash-coin me-1"></i>LKR <?= number_format($doctor['fee']) ?>/visit</span>
        </div>
      </div>
      <div class="col-auto d-none d-md-block">
        <div style="background:rgba(255,255,255,.15);border:1px solid rgba(255,255,255,.25);
                    border-radius:12px;padding:1rem 1.5rem;text-align:center">
          <div style="font-size:2rem;font-weight:800;color:#FCD34D">
            <?= $rating['avg'] ? number_format($rating['avg'],1) : '—' ?>
          </div>
          <div style="color:#FCD34D;font-size:.85rem">
            <?php for ($i=1;$i<=5;$i++): ?>
            <i class="bi bi-star<?= $rating['avg']>=$i?'-fill':'' ?>" style="font-size:.75rem"></i>
            <?php endfor; ?>
          </div>
          <div style="color:rgba(255,255,255,.7);font-size:.75rem;margin-top:.25rem">
            <?= $rating['count'] ?> review<?= $rating['count']!==1?'s':'' ?>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>

<div class="mp-page-wrap">
<div class="container">
<div class="row g-4">

  <!-- ── Left: profile details ── -->
  <div class="col-lg-8">

    <!-- About -->
    <?php if ($doctor['bio']): ?>
    <div class="mp-card mb-4">
      <h5 style="font-family:var(--font-display);font-weight:700;margin-bottom:.75rem">About Dr. <?= e(explode(' ',$doctor['name'])[0]) ?></h5>
      <p style="color:var(--mp-text-2);line-height:1.8;margin:0"><?= e($doctor['bio']) ?></p>
    </div>
    <?php endif; ?>

    <!-- Availability schedule -->
    <?php if (!empty($availability)): ?>
    <div class="mp-card mb-4">
      <h5 style="font-family:var(--font-display);font-weight:700;margin-bottom:1rem">Availability</h5>
      <div class="row g-2">
        <?php foreach ($availability as $av): ?>
        <div class="col-sm-6 col-lg-4">
          <div style="background:#f8fafc;border-radius:10px;padding:.75rem 1rem;
                      display:flex;justify-content:space-between;align-items:center">
            <div style="font-weight:600;font-size:.875rem"><?= $av['day_of_week'] ?></div>
            <div style="font-size:.8rem;color:var(--mp-primary)">
              <?= substr($av['start_time'],0,5) ?> – <?= substr($av['end_time'],0,5) ?>
            </div>
          </div>
        </div>
        <?php endforeach; ?>
      </div>
    </div>
    <?php endif; ?>

    <!-- Patient ratings -->
    <div class="mp-card" style="padding:0;overflow:hidden">
      <div class="p-4 border-bottom d-flex justify-content-between align-items-center">
        <h5 style="font-family:var(--font-display);font-weight:700;margin:0">Patient Reviews</h5>
        <div style="display:flex;align-items:center;gap:.5rem">
          <span style="font-size:1.25rem;font-weight:800;color:#F59E0B">
            <?= $rating['avg'] ? number_format($rating['avg'],1) : '—' ?>
          </span>
          <div>
            <?php for ($i=1;$i<=5;$i++): ?>
            <i class="bi bi-star<?= $rating['avg']>=$i?'-fill':'' ?>" style="color:#F59E0B;font-size:.75rem"></i>
            <?php endfor; ?>
            <div style="font-size:.72rem;color:var(--mp-text-3)"><?= $rating['count'] ?> reviews</div>
          </div>
        </div>
      </div>
      <?php if (empty($ratings)): ?>
      <div class="text-center py-5" style="color:var(--mp-text-3)">
        <i class="bi bi-star fs-1 d-block mb-2 opacity-25"></i>
        <div>No reviews yet — be the first to book!</div>
      </div>
      <?php else: ?>
        <?php foreach ($ratings as $r): ?>
        <div class="p-4 border-bottom">
          <div class="d-flex justify-content-between mb-1">
            <div style="font-weight:600"><?= e($r['patient_name']) ?></div>
            <div style="color:#F59E0B;font-size:.8rem">
              <?php for ($i=1;$i<=5;$i++): ?>
              <i class="bi bi-star<?= $i<=$r['stars']?'-fill':'' ?>" style="font-size:.7rem"></i>
              <?php endfor; ?>
            </div>
          </div>
          <?php if ($r['review']): ?>
          <div style="color:var(--mp-text-2);font-size:.875rem;font-style:italic">"<?= e($r['review']) ?>"</div>
          <?php endif; ?>
          <div style="font-size:.75rem;color:var(--mp-text-3);margin-top:.4rem"><?= formatDate($r['created_at']) ?></div>
        </div>
        <?php endforeach; ?>
      <?php endif; ?>
    </div>

  </div><!-- /.col-lg-8 -->

  <!-- ── Right: booking card ── -->
  <div class="col-lg-4">
    <div class="mp-card" style="position:sticky;top:90px">
      <h5 style="font-family:var(--font-display);font-weight:700;margin-bottom:1.25rem">Book an Appointment</h5>

      <?php if (!Auth::check()): ?>
        <div style="background:var(--mp-primary-light);border-radius:10px;padding:1.25rem;
                    text-align:center;margin-bottom:1rem">
          <i class="bi bi-lock-fill" style="color:var(--mp-primary);font-size:1.5rem"></i>
          <div style="font-weight:600;margin:.5rem 0 .25rem">Sign in to book</div>
          <div style="font-size:.8rem;color:var(--mp-text-3);margin-bottom:1rem">
            Create a free account to book this doctor.
          </div>
          <a href="<?= url('register') ?>" class="btn btn-primary btn-sm w-100 mb-2">Register Free</a>
          <a href="<?= url('login') ?>" class="btn btn-outline-primary btn-sm w-100">Sign In</a>
        </div>
      <?php elseif (Auth::role()==='patient'): ?>
        <form method="POST" action="<?= url('booking/create') ?>" id="bookingForm">
          <?= CSRF::field() ?>
          <input type="hidden" name="doctor_id" value="<?= $doctor['id'] ?>">

          <div class="mb-3">
            <label class="form-label" style="font-size:.85rem;font-weight:600">Select Date</label>
            <input type="date" name="appt_date" id="apptDate" class="form-control"
                   min="<?= date('Y-m-d', strtotime('+1 day')) ?>"
                   max="<?= date('Y-m-d', strtotime('+60 days')) ?>"
                   required>
          </div>

          <div class="mb-3">
            <label class="form-label" style="font-size:.85rem;font-weight:600">Select Time</label>
            <select name="appt_time" id="apptTime" class="form-select" required disabled>
              <option value="">— pick a date first —</option>
            </select>
            <div id="slotSpinner" class="d-none mt-1" style="font-size:.8rem;color:var(--mp-text-3)">
              <i class="bi bi-arrow-repeat"></i> Loading slots…
            </div>
            <div id="noSlots" class="d-none mt-1" style="font-size:.8rem;color:#EF4444">
              No available slots for this date.
            </div>
          </div>

          <div class="mb-3">
            <label class="form-label" style="font-size:.85rem;font-weight:600">
              Reason for Visit <span style="color:var(--mp-text-3);font-weight:400">(optional)</span>
            </label>
            <textarea name="reason" class="form-control" rows="2"
                      placeholder="Briefly describe your concern…"></textarea>
          </div>

          <div style="background:#f8fafc;border-radius:10px;padding:1rem;margin-bottom:1.25rem">
            <div class="d-flex justify-content-between mb-1" style="font-size:.875rem">
              <span style="color:var(--mp-text-3)">Consultation Fee</span>
              <strong>LKR <?= number_format($doctor['fee']) ?></strong>
            </div>
            <div class="d-flex justify-content-between" style="font-size:.875rem">
              <span style="color:var(--mp-text-3)">Doctor</span>
              <span>Dr. <?= e(explode(' ',$doctor['name'])[0]) ?></span>
            </div>
          </div>

          <button type="submit" class="btn btn-primary w-100" style="font-weight:700;padding:.75rem">
            <i class="bi bi-calendar2-check me-2"></i>Confirm Booking
          </button>
        </form>
      <?php else: ?>
        <div style="background:#FEF3C7;border-radius:10px;padding:1rem;font-size:.875rem;color:#92400E">
          <i class="bi bi-info-circle me-1"></i>
          Only patients can book appointments. Please use your patient account.
        </div>
      <?php endif; ?>

      <!-- Info bits -->
      <div class="mt-3 pt-3 border-top">
        <?php
        $infos = [
          ['bi-shield-check',       'green', 'Verified specialist'],
          ['bi-clock',              'blue',  'Flexible appointment times'],
          ['bi-credit-card',        'teal',  'Pay at appointment'],
          ['bi-x-circle',           'amber', 'Free cancellation 24h before'],
        ];
        foreach ($infos as [$icon, $color, $text]): ?>
        <div class="d-flex align-items-center gap-2 mb-2" style="font-size:.8rem;color:var(--mp-text-2)">
          <i class="bi bi-<?= $icon ?>" style="color:var(--mp-<?= $color === 'teal' ? 'primary' : ($color === 'green' ? '#10B981' : ($color === 'blue' ? '#0EA5E9' : '#F59E0B')) ?>)"></i>
          <?= $text ?>
        </div>
        <?php endforeach; ?>
      </div>
    </div>
  </div>

</div>
</div>
</div>

<script>
document.getElementById('apptDate')?.addEventListener('change', function () {
  const date = this.value;
  const select = document.getElementById('apptTime');
  const spinner = document.getElementById('slotSpinner');
  const noSlots = document.getElementById('noSlots');

  select.disabled = true;
  select.innerHTML = '<option value="">Loading…</option>';
  spinner.classList.remove('d-none');
  noSlots.classList.add('d-none');

  fetch('<?= url('booking/slots') ?>?doctor_id=<?= $doctor['id'] ?>&date=' + date)
    .then(r => r.json())
    .then(slots => {
      spinner.classList.add('d-none');
      if (!slots.length) {
        select.innerHTML = '<option value="">No slots available</option>';
        noSlots.classList.remove('d-none');
      } else {
        select.innerHTML = '<option value="">— choose a time —</option>';
        slots.forEach(s => {
          const opt = document.createElement('option');
          opt.value = s.time;
          opt.textContent = s.label;
          select.appendChild(opt);
        });
        select.disabled = false;
      }
    })
    .catch(() => {
      spinner.classList.add('d-none');
      select.innerHTML = '<option value="">Error loading slots</option>';
    });
});
</script>

<?php require VIEW_PATH . '/layouts/footer.php'; ?>
