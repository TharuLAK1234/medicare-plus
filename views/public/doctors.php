<?php
require VIEW_PATH . '/layouts/header.php';
require VIEW_PATH . '/layouts/navbar.php';

$search      = trim($_GET['search'] ?? '');
$serviceFilter = trim($_GET['service'] ?? '');
$doctors     = Doctor::search($search, $serviceFilter);
$services    = Doctor::allServices();
?>

<div style="background:linear-gradient(135deg,var(--mp-primary),#0D9488);padding:3rem 0 2rem">
  <div class="container">
    <h1 style="color:#fff;font-family:var(--font-display);font-weight:800;font-size:2rem;margin-bottom:.5rem">
      Find a Doctor
    </h1>
    <p style="color:rgba(255,255,255,.8);margin-bottom:1.5rem">
      Browse our specialists and book your appointment online.
    </p>
    <!-- Search bar -->
    <form method="GET" action="" class="d-flex gap-2 flex-wrap">
      <div class="d-flex flex-grow-1 gap-2" style="max-width:700px">
        <div class="flex-grow-1" style="position:relative">
          <i class="bi bi-search" style="position:absolute;left:14px;top:50%;transform:translateY(-50%);color:#94A3B8"></i>
          <input type="text" name="search" value="<?= e($search) ?>"
                 placeholder="Search by name or speciality…"
                 class="form-control" style="padding-left:2.5rem;border-radius:10px;border:none;height:46px">
        </div>
        <select name="service" class="form-select" style="max-width:200px;border-radius:10px;border:none;height:46px">
          <option value="">All Specialities</option>
          <?php foreach ($services as $s): ?>
          <option value="<?= e($s['name']) ?>" <?= $serviceFilter===$s['name']?'selected':'' ?>>
            <?= e($s['name']) ?>
          </option>
          <?php endforeach; ?>
        </select>
        <button type="submit" class="btn" style="background:#fff;color:var(--mp-primary);font-weight:700;
                border-radius:10px;height:46px;padding:0 1.5rem;white-space:nowrap">
          Search
        </button>
      </div>
      <?php if ($search || $serviceFilter): ?>
      <a href="<?= url('doctors') ?>" class="btn btn-sm"
         style="background:rgba(255,255,255,.2);color:#fff;border:1px solid rgba(255,255,255,.3);border-radius:10px;height:46px;line-height:34px">
        <i class="bi bi-x-lg"></i> Clear
      </a>
      <?php endif; ?>
    </form>
  </div>
</div>

<div class="mp-page-wrap">
<div class="container">

  <div class="d-flex justify-content-between align-items-center mb-4">
    <div style="color:var(--mp-text-3);font-size:.875rem">
      <?= count($doctors) ?> doctor<?= count($doctors)!==1?'s':'' ?> found
      <?php if ($search): ?> for "<strong><?= e($search) ?></strong>"<?php endif; ?>
      <?php if ($serviceFilter): ?> in <strong><?= e($serviceFilter) ?></strong><?php endif; ?>
    </div>
  </div>

  <?php if (empty($doctors)): ?>
  <div class="text-center py-5" style="color:var(--mp-text-3)">
    <i class="bi bi-person-x fs-1 d-block mb-3 opacity-25"></i>
    <div style="font-weight:600;font-size:1.1rem">No doctors found</div>
    <p style="font-size:.875rem">Try a different search term or browse all specialities.</p>
    <a href="<?= url('doctors') ?>" class="btn btn-outline-primary btn-sm">Clear Filters</a>
  </div>
  <?php else: ?>
  <div class="row g-4">
    <?php foreach ($doctors as $d):
      $rating = Doctor::getRating($d['id']);
    ?>
    <div class="col-md-6 col-xl-4">
      <div class="mp-card" style="padding:0;overflow:hidden;transition:all .2s"
           onmouseover="this.style.transform='translateY(-2px)';this.style.boxShadow='var(--shadow-md)'"
           onmouseout="this.style.transform='';this.style.boxShadow=''">
        <!-- Banner -->
        <div style="height:72px;background:linear-gradient(135deg,var(--mp-primary),#0D9488);position:relative">
          <div style="position:absolute;bottom:-24px;left:1.25rem;
                      width:48px;height:48px;border-radius:50%;border:3px solid #fff;
                      background:var(--mp-primary-light);display:flex;align-items:center;
                      justify-content:center;font-size:1.1rem;font-weight:800;color:var(--mp-primary)">
            <?= mb_strtoupper(mb_substr($d['name'],0,1)) ?>
          </div>
          <div style="position:absolute;top:.75rem;right:.75rem">
            <?php if ($d['status']==='active'): ?>
            <span style="background:rgba(16,185,129,.9);color:#fff;font-size:.72rem;font-weight:700;
                         padding:.2rem .6rem;border-radius:9999px">
              <i class="bi bi-circle-fill" style="font-size:.4rem;vertical-align:middle"></i> Available
            </span>
            <?php endif; ?>
          </div>
        </div>
        <div style="padding:2.25rem 1.25rem 1.25rem">
          <div style="font-family:var(--font-display);font-weight:700;font-size:1rem">
            Dr. <?= e($d['name']) ?>
          </div>
          <div style="font-size:.8rem;color:var(--mp-primary);font-weight:600;margin:.15rem 0 .6rem">
            <?= e($d['service_name'] ?? '') ?> · <?= e($d['specialization']) ?>
          </div>
          <div class="d-flex flex-wrap gap-3 mb-3" style="font-size:.8rem;color:var(--mp-text-3)">
            <span><i class="bi bi-briefcase me-1"></i><?= $d['experience_years'] ?> yrs exp</span>
            <span><i class="bi bi-geo-alt me-1"></i><?= e($d['location'] ?? 'Colombo') ?></span>
          </div>
          <!-- Rating -->
          <div class="d-flex align-items-center gap-1 mb-3">
            <?php for ($i=1;$i<=5;$i++): ?>
            <i class="bi bi-star<?= $i<=$rating['avg']?'-fill':'' ?>"
               style="color:#F59E0B;font-size:.75rem"></i>
            <?php endfor; ?>
            <span style="font-size:.78rem;color:var(--mp-text-3);margin-left:.25rem">
              <?= $rating['avg'] ? number_format($rating['avg'],1) : 'No ratings' ?>
              <?php if ($rating['count']): ?>(<?= $rating['count'] ?>)<?php endif; ?>
            </span>
          </div>
          <!-- Availability tags -->
          <?php $avail = Doctor::getAvailability($d['id']); ?>
          <?php if (!empty($avail)): ?>
          <div class="d-flex flex-wrap gap-1 mb-3">
            <?php foreach (array_slice($avail, 0, 4) as $av): ?>
            <span style="background:var(--mp-primary-light);color:var(--mp-primary);
                         font-size:.7rem;font-weight:600;padding:.2rem .6rem;border-radius:6px">
              <?= substr($av['day_of_week'],0,3) ?>
            </span>
            <?php endforeach; ?>
            <?php if (count($avail)>4): ?>
            <span style="color:var(--mp-text-3);font-size:.72rem;padding:.2rem .4rem">+<?= count($avail)-4 ?> more</span>
            <?php endif; ?>
          </div>
          <?php endif; ?>
          <div class="d-flex justify-content-between align-items-center">
            <div>
              <div style="font-size:.72rem;color:var(--mp-text-3)">Consultation Fee</div>
              <div style="font-weight:700;color:var(--mp-primary)">LKR <?= number_format($d['fee']) ?></div>
            </div>
            <a href="<?= url('doctors/'.$d['id']) ?>" class="btn btn-primary btn-sm">
              <i class="bi bi-calendar2-plus me-1"></i>Book Now
            </a>
          </div>
        </div>
      </div>
    </div>
    <?php endforeach; ?>
  </div>
  <?php endif; ?>

</div>
</div>

<?php require VIEW_PATH . '/layouts/footer.php'; ?>
