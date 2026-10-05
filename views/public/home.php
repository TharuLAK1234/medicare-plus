<?php
require VIEW_PATH . '/layouts/header.php';
require VIEW_PATH . '/layouts/navbar.php';
?>

<!-- ═══════════════════════════ HERO ═══════════════════════════ -->
<section style="background:linear-gradient(135deg,#0A6E82 0%,#0D9488 60%,#0891B2 100%);
                padding:6rem 0 4rem;position:relative;overflow:hidden">
  <div style="position:absolute;inset:0;background:url('data:image/svg+xml,<svg xmlns=%22http://www.w3.org/2000/svg%22 viewBox=%220 0 100 100%22><circle cx=%2280%22 cy=%2220%22 r=%2240%22 fill=%22rgba(255,255,255,.04)%22/><circle cx=%2210%22 cy=%2280%22 r=%2260%22 fill=%22rgba(255,255,255,.03)%22/></svg>') no-repeat;background-size:cover"></div>
  <div class="container position-relative">
    <div class="row align-items-center g-5">
      <div class="col-lg-6">
        <div style="display:inline-flex;align-items:center;gap:.5rem;background:rgba(255,255,255,.15);
                    border:1px solid rgba(255,255,255,.25);border-radius:9999px;
                    padding:.35rem 1rem;font-size:.8rem;color:#fff;margin-bottom:1.5rem;font-weight:600">
          <i class="bi bi-patch-check-fill" style="color:#34D399"></i> Trusted by 10,000+ patients in Sri Lanka
        </div>
        <h1 style="color:#fff;font-family:var(--font-display);font-size:clamp(2rem,4vw,3rem);
                   font-weight:800;line-height:1.15;margin-bottom:1.25rem">
          Your Health,<br>Our <span style="color:#34D399">Priority</span>
        </h1>
        <p style="color:rgba(255,255,255,.85);font-size:1.1rem;max-width:480px;margin-bottom:2rem;line-height:1.7">
          Book appointments with top specialists, manage your health records, and get quality care — all from one platform.
        </p>
        <div class="d-flex flex-wrap gap-3">
          <?php if (!Auth::check()): ?>
          <a href="<?= url('register') ?>" class="btn btn-lg"
             style="background:#fff;color:var(--mp-primary);font-weight:700;border:none;
                    padding:.75rem 2rem;border-radius:12px">
            <i class="bi bi-person-plus me-2"></i>Get Started Free
          </a>
          <a href="<?= url('doctors') ?>" class="btn btn-lg"
             style="background:rgba(255,255,255,.15);color:#fff;border:1px solid rgba(255,255,255,.35);
                    padding:.75rem 2rem;border-radius:12px;font-weight:600">
            <i class="bi bi-search me-2"></i>Find a Doctor
          </a>
          <?php else: ?>
          <a href="<?= Middleware::dashboardUrl() ?>" class="btn btn-lg"
             style="background:#fff;color:var(--mp-primary);font-weight:700;border:none;
                    padding:.75rem 2rem;border-radius:12px">
            Go to Dashboard <i class="bi bi-arrow-right ms-1"></i>
          </a>
          <a href="<?= url('doctors') ?>" class="btn btn-lg"
             style="background:rgba(255,255,255,.15);color:#fff;border:1px solid rgba(255,255,255,.35);
                    padding:.75rem 2rem;border-radius:12px;font-weight:600">
            <i class="bi bi-search me-2"></i>Find a Doctor
          </a>
          <?php endif; ?>
        </div>
      </div>
      <div class="col-lg-6 d-none d-lg-block">
        <div class="row g-3">
          <?php
          $heroStats = [
            ['bi-people-fill',        '10,000+', 'Happy Patients',    '#34D399'],
            ['bi-person-badge-fill',  '50+',     'Specialists',       '#60A5FA'],
            ['bi-calendar2-check',    '500+',    'Appointments/month','#FBBF24'],
            ['bi-star-fill',          '4.9',     'Average Rating',    '#F472B6'],
          ];
          foreach ($heroStats as [$icon, $val, $label, $color]): ?>
          <div class="col-6">
            <div style="background:rgba(255,255,255,.12);backdrop-filter:blur(10px);
                        border:1px solid rgba(255,255,255,.2);border-radius:16px;
                        padding:1.5rem;text-align:center">
              <i class="bi bi-<?= $icon ?>" style="font-size:1.5rem;color:<?= $color ?>"></i>
              <div style="font-size:1.75rem;font-weight:800;color:#fff;margin:.5rem 0 .25rem"><?= $val ?></div>
              <div style="font-size:.8rem;color:rgba(255,255,255,.75)"><?= $label ?></div>
            </div>
          </div>
          <?php endforeach; ?>
        </div>
      </div>
    </div>
  </div>
</section>

<!-- ═══════════════════════════ SERVICES ═══════════════════════════ -->
<section style="padding:5rem 0;background:#fff">
  <div class="container">
    <div class="text-center mb-5">
      <div style="color:var(--mp-primary);font-weight:700;font-size:.85rem;text-transform:uppercase;
                  letter-spacing:.1em;margin-bottom:.5rem">Our Specialities</div>
      <h2 style="font-family:var(--font-display);font-weight:800;font-size:2rem">
        World-Class Medical Services
      </h2>
      <p style="color:var(--mp-text-3);max-width:500px;margin:.75rem auto 0">
        Access a wide range of healthcare specialities delivered by experienced professionals.
      </p>
    </div>
    <div class="row g-4">
      <?php
      $services = [
        ['bi-heart-pulse-fill',   'Cardiology',        '#EF4444', 'Heart disease prevention, diagnosis & treatment.'],
        ['bi-brain',              'Neurology',          '#8B5CF6', 'Brain, spine and nervous system care.'],
        ['bi-person-standing',   'Orthopaedics',       '#F59E0B', 'Bone, joint, and muscle specialists.'],
        ['bi-eye-fill',           'Ophthalmology',      '#0EA5E9', 'Eye exams, surgery and vision care.'],
        ['bi-lungs-fill',         'Pulmonology',        '#10B981', 'Respiratory and lung health services.'],
        ['bi-gender-female',      'Gynaecology',        '#EC4899', 'Women\'s health and maternity care.'],
        ['bi-droplet-fill',       'Dermatology',        '#F97316', 'Skin, hair and nail treatments.'],
        ['bi-virus2',             'General Medicine',   '#6366F1', 'Preventive care and general consultations.'],
      ];
      foreach ($services as [$icon, $name, $color, $desc]): ?>
      <div class="col-sm-6 col-lg-3">
        <a href="<?= url('doctors') ?>?service=<?= urlencode($name) ?>" style="text-decoration:none">
          <div style="border:1px solid var(--mp-border);border-radius:16px;padding:1.75rem;
                      text-align:center;transition:all .2s;cursor:pointer;background:#fff"
               onmouseover="this.style.boxShadow='0 8px 30px rgba(0,0,0,.10)';this.style.transform='translateY(-3px)';this.style.borderColor='<?= $color ?>'"
               onmouseout="this.style.boxShadow='';this.style.transform='';this.style.borderColor='var(--mp-border)'">
            <div style="width:56px;height:56px;border-radius:14px;
                        background:<?= $color ?>18;display:flex;align-items:center;
                        justify-content:center;margin:0 auto 1rem;font-size:1.4rem;color:<?= $color ?>">
              <i class="bi bi-<?= $icon ?>"></i>
            </div>
            <div style="font-family:var(--font-display);font-weight:700;color:var(--mp-text-1);
                        margin-bottom:.4rem"><?= $name ?></div>
            <div style="font-size:.8rem;color:var(--mp-text-3);line-height:1.5"><?= $desc ?></div>
          </div>
        </a>
      </div>
      <?php endforeach; ?>
    </div>
  </div>
</section>

<!-- ═══════════════════════════ HOW IT WORKS ═══════════════════════════ -->
<section style="padding:5rem 0;background:#F8FAFC">
  <div class="container">
    <div class="text-center mb-5">
      <div style="color:var(--mp-primary);font-weight:700;font-size:.85rem;text-transform:uppercase;
                  letter-spacing:.1em;margin-bottom:.5rem">Simple Process</div>
      <h2 style="font-family:var(--font-display);font-weight:800;font-size:2rem">How It Works</h2>
    </div>
    <div class="row g-4 justify-content-center">
      <?php
      $steps = [
        ['1', 'bi-person-plus-fill', 'Create Account',     'Register as a patient in under 2 minutes.'],
        ['2', 'bi-search',           'Find a Doctor',      'Browse specialists by department or name.'],
        ['3', 'bi-calendar2-check',  'Book Appointment',   'Pick your preferred date and time slot.'],
        ['4', 'bi-chat-dots-fill',   'Get Consultation',   'Visit your doctor and receive expert care.'],
      ];
      foreach ($steps as [$num, $icon, $title, $desc]): ?>
      <div class="col-sm-6 col-lg-3 text-center">
        <div style="position:relative;display:inline-block">
          <div style="width:64px;height:64px;border-radius:50%;
                      background:linear-gradient(135deg,var(--mp-primary),#0D9488);
                      display:flex;align-items:center;justify-content:center;
                      margin:0 auto 1rem;font-size:1.4rem;color:#fff">
            <i class="bi bi-<?= $icon ?>"></i>
          </div>
          <div style="position:absolute;top:-8px;right:-8px;width:24px;height:24px;
                      border-radius:50%;background:#FCD34D;color:#92400E;font-weight:800;
                      font-size:.75rem;display:flex;align-items:center;justify-content:center">
            <?= $num ?>
          </div>
        </div>
        <div style="font-family:var(--font-display);font-weight:700;margin-bottom:.4rem"><?= $title ?></div>
        <div style="font-size:.875rem;color:var(--mp-text-3);line-height:1.6"><?= $desc ?></div>
      </div>
      <?php endforeach; ?>
    </div>
  </div>
</section>

<!-- ═══════════════════════════ FEATURED DOCTORS ═══════════════════════════ -->
<?php
$featuredDoctors = Doctor::allWithStats(6);
if (!empty($featuredDoctors)): ?>
<section style="padding:5rem 0;background:#fff">
  <div class="container">
    <div class="d-flex justify-content-between align-items-end mb-5">
      <div>
        <div style="color:var(--mp-primary);font-weight:700;font-size:.85rem;text-transform:uppercase;
                    letter-spacing:.1em;margin-bottom:.5rem">Our Team</div>
        <h2 style="font-family:var(--font-display);font-weight:800;font-size:2rem;margin:0">
          Meet Our Specialists
        </h2>
      </div>
      <a href="<?= url('doctors') ?>" class="btn btn-outline-primary d-none d-md-inline-flex">
        View All Doctors <i class="bi bi-arrow-right ms-1"></i>
      </a>
    </div>
    <div class="row g-4">
      <?php foreach ($featuredDoctors as $d): ?>
      <div class="col-sm-6 col-lg-4">
        <div style="border:1px solid var(--mp-border);border-radius:16px;overflow:hidden;
                    transition:all .2s;background:#fff"
             onmouseover="this.style.boxShadow='0 8px 30px rgba(0,0,0,.10)';this.style.transform='translateY(-2px)'"
             onmouseout="this.style.boxShadow='';this.style.transform=''">
          <!-- Avatar banner -->
          <div style="height:80px;background:linear-gradient(135deg,var(--mp-primary),#0D9488);position:relative">
            <div style="position:absolute;bottom:-28px;left:1.5rem;
                        width:56px;height:56px;border-radius:50%;border:3px solid #fff;
                        background:var(--mp-primary-light);display:flex;align-items:center;
                        justify-content:center;font-size:1.25rem;font-weight:800;color:var(--mp-primary)">
              <?= mb_strtoupper(mb_substr($d['name'],0,1)) ?>
            </div>
          </div>
          <div style="padding:2.5rem 1.5rem 1.5rem">
            <div style="font-family:var(--font-display);font-weight:700;font-size:1rem">
              Dr. <?= e($d['name']) ?>
            </div>
            <div style="font-size:.8rem;color:var(--mp-primary);font-weight:600;margin:.2rem 0 .5rem">
              <?= e($d['service_name'] ?? $d['specialization']) ?>
            </div>
            <div class="d-flex align-items-center gap-3 mb-1" style="font-size:.8rem;color:var(--mp-text-3)">
              <span><i class="bi bi-briefcase me-1"></i><?= $d['experience_years'] ?> yrs exp</span>
              <span><i class="bi bi-geo-alt me-1"></i><?= e($d['location'] ?? 'Colombo') ?></span>
            </div>
            <?php $r = $d['avg_rating'] ?? 0; ?>
            <div class="d-flex align-items-center gap-1 mb-3" style="font-size:.8rem">
              <?php for ($i=1;$i<=5;$i++): ?>
              <i class="bi bi-star<?= $i<=$r?'-fill':'' ?>" style="color:#F59E0B;font-size:.7rem"></i>
              <?php endfor; ?>
              <span style="color:var(--mp-text-3);margin-left:.25rem">
                (<?= $d['appt_count'] ?? 0 ?> reviews)
              </span>
            </div>
            <div class="d-flex justify-content-between align-items-center">
              <div>
                <div style="font-size:.72rem;color:var(--mp-text-3)">Consultation</div>
                <div style="font-weight:700;color:var(--mp-primary)">
                  LKR <?= number_format($d['fee']) ?>
                </div>
              </div>
              <a href="<?= url('doctors/'.$d['id']) ?>" class="btn btn-primary btn-sm">
                Book Now
              </a>
            </div>
          </div>
        </div>
      </div>
      <?php endforeach; ?>
    </div>
    <div class="text-center mt-4 d-md-none">
      <a href="<?= url('doctors') ?>" class="btn btn-outline-primary">View All Doctors</a>
    </div>
  </div>
</section>
<?php endif; ?>

<!-- ═══════════════════════════ CTA ═══════════════════════════ -->
<?php if (!Auth::check()): ?>
<section style="padding:5rem 0;background:linear-gradient(135deg,#0A6E82,#0D9488)">
  <div class="container text-center">
    <h2 style="color:#fff;font-family:var(--font-display);font-weight:800;font-size:2rem;margin-bottom:1rem">
      Ready to Take Control of Your Health?
    </h2>
    <p style="color:rgba(255,255,255,.85);font-size:1.05rem;margin-bottom:2rem;max-width:480px;margin-left:auto;margin-right:auto">
      Join thousands of patients who trust MediCare Plus for their healthcare needs.
    </p>
    <div class="d-flex gap-3 justify-content-center flex-wrap">
      <a href="<?= url('register') ?>" class="btn btn-lg"
         style="background:#fff;color:var(--mp-primary);font-weight:700;padding:.75rem 2rem;border-radius:12px">
        <i class="bi bi-person-plus me-2"></i>Register Now — It's Free
      </a>
      <a href="<?= url('doctors') ?>" class="btn btn-lg"
         style="background:rgba(255,255,255,.15);color:#fff;border:1px solid rgba(255,255,255,.35);
                padding:.75rem 2rem;border-radius:12px;font-weight:600">
        Browse Doctors
      </a>
    </div>
  </div>
</section>
<?php endif; ?>

<?php require VIEW_PATH . '/layouts/footer.php'; ?>
