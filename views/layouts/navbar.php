<?php
$currentUser = Auth::user();
$role = $currentUser['role'] ?? 'guest';

// Determine the active route segment for highlighting
$uri = trim(parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH), '/');
$seg = explode('/', str_replace(
    trim(parse_url(APP_URL, PHP_URL_PATH), '/') . '/',
    '',
    $uri . '/'
))[0] ?? '';
?>
<nav class="navbar navbar-expand-lg mp-navbar sticky-top">
  <div class="container">

    <!-- Brand -->
    <a class="navbar-brand" href="<?= url('') ?>">
      <span class="brand-icon"><i class="bi bi-hospital-fill"></i></span>
      MediCare<span style="color:var(--mp-secondary)">Plus</span>
    </a>

    <!-- Mobile toggle -->
    <button class="navbar-toggler border-0" type="button"
            data-bs-toggle="collapse" data-bs-target="#mpNav">
      <span class="navbar-toggler-icon"></span>
    </button>

    <div class="collapse navbar-collapse" id="mpNav">

      <!-- ── Left nav links ── -->
      <ul class="navbar-nav me-auto">
        <li class="nav-item">
          <a class="nav-link <?= $seg === '' || $seg === 'home' ? 'active' : '' ?>"
             href="<?= url('') ?>">Home</a>
        </li>
        <li class="nav-item">
          <a class="nav-link <?= $seg === 'services' ? 'active' : '' ?>"
             href="<?= url('services') ?>">Services</a>
        </li>
        <li class="nav-item">
          <a class="nav-link <?= $seg === 'doctors' ? 'active' : '' ?>"
             href="<?= url('doctors') ?>">Doctors</a>
        </li>

        <?php if ($role === 'patient'): ?>
          <li class="nav-item">
            <a class="nav-link <?= $seg === 'appointments' ? 'active' : '' ?>"
               href="<?= url('appointments') ?>">My Appointments</a>
          </li>
        <?php endif; ?>

        <?php if ($role === 'doctor'): ?>
          <li class="nav-item">
            <a class="nav-link <?= $seg === 'appointments' ? 'active' : '' ?>"
               href="<?= url('appointments') ?>">Appointments</a>
          </li>
          <li class="nav-item">
            <a class="nav-link <?= $seg === 'messages' ? 'active' : '' ?>"
               href="<?= url('messages') ?>">Messages</a>
          </li>
        <?php endif; ?>

        <?php if ($role === 'admin'): ?>
          <li class="nav-item">
            <a class="nav-link <?= $seg === 'admin' ? 'active' : '' ?>"
               href="<?= url('admin/dashboard') ?>">Dashboard</a>
          </li>
          <li class="nav-item dropdown">
            <a class="nav-link dropdown-toggle" href="#" data-bs-toggle="dropdown">Manage</a>
            <ul class="dropdown-menu">
              <li><a class="dropdown-item" href="<?= url('admin/doctors') ?>">
                <i class="bi bi-person-badge"></i> Doctors</a></li>
              <li><a class="dropdown-item" href="<?= url('admin/users') ?>">
                <i class="bi bi-people"></i> Users</a></li>
              <li><a class="dropdown-item" href="<?= url('admin/appointments') ?>">
                <i class="bi bi-calendar2-check"></i> Appointments</a></li>
              <li><a class="dropdown-item" href="<?= url('admin/services') ?>">
                <i class="bi bi-grid-3x3-gap"></i> Services</a></li>
            </ul>
          </li>
        <?php endif; ?>
      </ul>

      <!-- ── Right nav ── -->
      <ul class="navbar-nav align-items-center gap-2">
        <?php if (!$currentUser): ?>
          <!-- Guest — portal dropdown -->
          <li class="nav-item dropdown">
            <a class="nav-link dropdown-toggle" href="#" data-bs-toggle="dropdown">Sign In</a>
            <ul class="dropdown-menu dropdown-menu-end shadow-sm">
              <li class="px-3 py-1">
                <div style="font-size:.72rem;text-transform:uppercase;letter-spacing:.08em;
                            color:var(--mp-text-3);font-weight:700">Select your portal</div>
              </li>
              <li><hr class="dropdown-divider my-1"></li>
              <li>
                <a class="dropdown-item" href="<?= url('login') ?>">
                  <i class="bi bi-person-heart-fill text-success"></i>
                  <span style="font-weight:600">Patient Portal</span>
                  <div style="font-size:.75rem;color:var(--mp-text-3);margin-left:22px">Book appointments &amp; records</div>
                </a>
              </li>
              <li>
                <a class="dropdown-item" href="<?= url('doctor/login') ?>">
                  <i class="bi bi-person-badge-fill" style="color:#1D4ED8"></i>
                  <span style="font-weight:600">Doctor Portal</span>
                  <div style="font-size:.75rem;color:var(--mp-text-3);margin-left:22px">Schedule &amp; patient management</div>
                </a>
              </li>
              <li>
                <a class="dropdown-item" href="<?= url('admin/login') ?>">
                  <i class="bi bi-shield-fill" style="color:#EF4444"></i>
                  <span style="font-weight:600">Admin Portal</span>
                  <div style="font-size:.75rem;color:var(--mp-text-3);margin-left:22px">System administration</div>
                </a>
              </li>
            </ul>
          </li>
          <li class="nav-item">
            <a class="btn btn-primary btn-sm px-4" href="<?= url('register') ?>">
              Get Started
            </a>
          </li>

        <?php else: ?>
          <!-- Authenticated user chip / dropdown -->
          <li class="nav-item dropdown">
            <button class="mp-user-chip dropdown-toggle border-0"
                    data-bs-toggle="dropdown" aria-expanded="false">
              <span class="avatar"><?= e(mb_substr($currentUser['name'], 0, 1)) ?></span>
              <span class="d-none d-md-inline"><?= e(explode(' ', $currentUser['name'])[0]) ?></span>
            </button>
            <ul class="dropdown-menu dropdown-menu-end shadow-sm">
              <li class="px-3 py-2">
                <div style="font-family:var(--font-display);font-weight:700;font-size:.9rem;color:var(--mp-text-1)">
                  <?= e($currentUser['name']) ?>
                </div>
                <div style="font-size:.78rem;color:var(--mp-text-3)"><?= e($currentUser['email']) ?></div>
                <span class="mp-badge <?= $role ?> mt-1"><?= e(ucfirst($role)) ?></span>
              </li>
              <li><hr class="dropdown-divider"></li>

              <?php if ($role === 'patient'): ?>
                <li><a class="dropdown-item" href="<?= url('patient/dashboard') ?>">
                  <i class="bi bi-speedometer2"></i> Dashboard</a></li>
                <li><a class="dropdown-item" href="<?= url('appointments') ?>">
                  <i class="bi bi-calendar2-check"></i> My Appointments</a></li>
                <li><a class="dropdown-item" href="<?= url('reports') ?>">
                  <i class="bi bi-file-earmark-medical"></i> My Reports</a></li>
                <li><a class="dropdown-item" href="<?= url('messages') ?>">
                  <i class="bi bi-chat-dots"></i> Messages</a></li>

              <?php elseif ($role === 'doctor'): ?>
                <li><a class="dropdown-item" href="<?= url('doctor/dashboard') ?>">
                  <i class="bi bi-speedometer2"></i> Dashboard</a></li>
                <li><a class="dropdown-item" href="<?= url('doctor/schedule') ?>">
                  <i class="bi bi-calendar3"></i> My Schedule</a></li>
                <li><a class="dropdown-item" href="<?= url('doctor/patients') ?>">
                  <i class="bi bi-people"></i> My Patients</a></li>

              <?php elseif ($role === 'admin'): ?>
                <li><a class="dropdown-item" href="<?= url('admin/dashboard') ?>">
                  <i class="bi bi-speedometer2"></i> Dashboard</a></li>
                <li><a class="dropdown-item" href="<?= url('admin/reports') ?>">
                  <i class="bi bi-bar-chart-line"></i> Reports</a></li>
              <?php endif; ?>

              <li><a class="dropdown-item" href="<?= url('profile') ?>">
                <i class="bi bi-person-circle"></i> My Profile</a></li>

              <li><hr class="dropdown-divider"></li>
              <li>
                <a class="dropdown-item text-danger" href="<?= url('logout') ?>">
                  <i class="bi bi-box-arrow-right"></i> Sign Out
                </a>
              </li>
            </ul>
          </li>
        <?php endif; ?>
      </ul>

    </div><!-- /.collapse -->
  </div><!-- /.container -->
</nav>

<?php
// ── Flash messages rendered just below the navbar ──
$flashTypes = ['success' => 'bi-check-circle-fill', 'error' => 'bi-x-circle-fill',
               'warning' => 'bi-exclamation-triangle-fill', 'info' => 'bi-info-circle-fill'];
foreach ($flashTypes as $type => $icon):
    if (!Session::hasFlash($type)) continue;
    $msg = Session::getFlash($type);
?>
<div class="container mt-3">
  <div class="mp-flash <?= $type ?> alert alert-dismissible" role="alert">
    <i class="bi <?= $icon ?>"></i>
    <div><?= e($msg) ?></div>
    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
  </div>
</div>
<?php endforeach; ?>
