<?php
// ── Bootstrap ─────────────────────────────────────────────
require_once __DIR__ . '/../config/config.php';
require_once CORE_PATH . '/helpers.php';
require_once CORE_PATH . '/Database.php';
require_once CORE_PATH . '/Session.php';
require_once CORE_PATH . '/Auth.php';
require_once CORE_PATH . '/Middleware.php';
require_once CORE_PATH . '/CSRF.php';
require_once CORE_PATH . '/Validator.php';

// ── Models ────────────────────────────────────────────────
require_once ROOT_PATH . '/models/User.php';
require_once ROOT_PATH . '/models/Patient.php';
require_once ROOT_PATH . '/models/Doctor.php';
require_once ROOT_PATH . '/models/Appointment.php';
require_once ROOT_PATH . '/models/Report.php';
require_once ROOT_PATH . '/models/Message.php';

// ── Controllers ───────────────────────────────────────────
require_once ROOT_PATH . '/controllers/AuthController.php';
require_once ROOT_PATH . '/controllers/AppointmentController.php';
require_once ROOT_PATH . '/controllers/BookingController.php';
require_once ROOT_PATH . '/controllers/RatingController.php';
require_once ROOT_PATH . '/controllers/ProfileController.php';
require_once ROOT_PATH . '/controllers/ReportController.php';
require_once ROOT_PATH . '/controllers/MessageController.php';

// ── Security headers ──────────────────────────────────────
header('X-Frame-Options: SAMEORIGIN');
header('X-Content-Type-Options: nosniff');
header('Referrer-Policy: strict-origin-when-cross-origin');
header('X-XSS-Protection: 1; mode=block');

// ── Session ───────────────────────────────────────────────
Session::start();

// ── Routing ───────────────────────────────────────────────
$method   = $_SERVER['REQUEST_METHOD'];
$basePath = rtrim(parse_url(APP_URL, PHP_URL_PATH), '/');
$reqPath  = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$route    = rtrim(trim(substr($reqPath, strlen($basePath)), '/'), '/') ?: 'home';

// Instantiate controllers
$auth       = new AuthController();
$apptCtrl   = new AppointmentController();
$booking    = new BookingController();
$rating     = new RatingController();
$profile    = new ProfileController();
$reportCtrl = new ReportController();
$msgCtrl    = new MessageController();

// ── Dynamic segment helpers ───────────────────────────────
// Match /doctors/123  → $segments = ['doctors','123']
$segments = explode('/', $route);

try {
    // ── GET ───────────────────────────────────────────────
    if ($method === 'GET') {

        // /doctors/123 — doctor profile page
        if (count($segments) === 2 && $segments[0] === 'doctors' && ctype_digit($segments[1])) {
            $booking->showProfile((int)$segments[1]);
            exit;
        }

        // /appointments/123/rate — rating form
        if (count($segments) === 3 && $segments[0] === 'appointments' && ctype_digit($segments[1]) && $segments[2] === 'rate') {
            $rating->showForm((int)$segments[1]);
            exit;
        }

        // /reports/download/:id
        if (count($segments) === 3 && $segments[0] === 'reports' && $segments[1] === 'download' && ctype_digit($segments[2])) {
            $reportCtrl->download((int)$segments[2]);
            exit;
        }

        // /messages/:id — open a thread
        if (count($segments) === 2 && $segments[0] === 'messages' && ctype_digit($segments[1])) {
            $msgCtrl->show((int)$segments[1]);
            exit;
        }

        switch ($route) {
            // ── Public ──
            case 'home':
            case '':
                view('public.home', ['title' => 'MediCare Plus — Quality Healthcare Online']);
                break;

            case 'doctors':
            case 'services':
                $booking->index();
                break;

            // ── Auth ──
            case 'login':       $auth->showLogin();       break;
            case 'register':    $auth->showRegister();    break;
            case 'doctor/login':$auth->showDoctorLogin(); break;
            case 'admin/login': $auth->showAdminLogin();  break;
            case 'logout':      $auth->logout();          break;

            // ── Booking ──
            case 'booking/slots':
                $booking->slots();
                break;

            // ── Patient ──
            case 'patient/dashboard':
                Middleware::requireRole('patient');
                view('patient.dashboard', ['title' => 'Dashboard — MediCare Plus']);
                break;

            case 'appointments':
                $apptCtrl->patientIndex();
                break;

            case 'profile':
                $profile->show();
                break;

            // ── Doctor ──
            case 'doctor/dashboard':
                Middleware::requireRole('doctor');
                view('doctor.dashboard', ['title' => 'Dashboard — MediCare Plus']);
                break;

            case 'doctor/appointments':
                $apptCtrl->doctorIndex();
                break;

            // ── Admin ──
            case 'admin/dashboard':
                Middleware::requireRole('admin');
                view('admin.dashboard', ['title' => 'Admin Dashboard — MediCare Plus']);
                break;

            case 'admin/appointments':
                $apptCtrl->adminIndex();
                break;

            case 'reports':
                $reportCtrl->patientIndex();
                break;

            case 'messages':
                $msgCtrl->index();
                break;

            case 'doctor/reports':
                $reportCtrl->doctorIndex();
                break;

            case 'doctor/messages':
                $msgCtrl->index();
                break;

            case 'admin/doctors':
            case 'admin/users':
            case 'admin/services':
            case 'admin/reports':
            case 'doctor/schedule':
            case 'doctor/patients':
                Middleware::requireAuth();
                http_response_code(501);
                view('errors.404', [
                    'title'   => 'Coming Soon — MediCare Plus',
                    'heading' => 'Coming Soon',
                    'message' => 'This section is under construction.',
                ]);
                break;

            default:
                http_response_code(404);
                view('errors.404', ['title' => '404 — Page Not Found']);
        }

    // ── POST ──────────────────────────────────────────────
    } elseif ($method === 'POST') {

        // /reports/delete/:id
        if (count($segments) === 3 && $segments[0] === 'reports' && $segments[1] === 'delete' && ctype_digit($segments[2])) {
            $reportCtrl->delete((int)$segments[2]);
            exit;
        }

        // /messages/:id/reply
        if (count($segments) === 3 && $segments[0] === 'messages' && ctype_digit($segments[1]) && $segments[2] === 'reply') {
            $msgCtrl->reply((int)$segments[1]);
            exit;
        }

        switch ($route) {
            // ── Auth ──
            case 'login':        $auth->login();        break;
            case 'register':     $auth->register();     break;
            case 'doctor/login': $auth->doctorLogin();  break;
            case 'admin/login':  $auth->adminLogin();   break;

            // ── Booking ──
            case 'booking/create': $booking->create(); break;
            case 'booking/store':  $booking->store();  break;

            // ── Appointment mutations ──
            case 'appointment/cancel':  $apptCtrl->cancel();  break;
            case 'appointment/confirm': $apptCtrl->confirm(); break;
            case 'appointment/complete':$apptCtrl->complete();break;

            // ── Ratings ──
            case 'ratings/store': $rating->store(); break;

            // ── Profile ──
            case 'profile/update': $profile->update(); break;

            // ── Reports ──
            case 'doctor/reports/upload': $reportCtrl->upload(); break;

            // ── Messages ──
            case 'messages/new': $msgCtrl->newThread(); break;

            default:
                http_response_code(404);
                view('errors.404', ['title' => '404 — Page Not Found']);
        }

    } else {
        http_response_code(405);
        header('Allow: GET, POST');
    }

} catch (Throwable $e) {
    logError('Unhandled exception', [
        'message' => $e->getMessage(),
        'file'    => $e->getFile(),
        'line'    => $e->getLine(),
        'trace'   => $e->getTraceAsString(),
    ]);
    http_response_code(500);
    view('errors.500', ['title' => '500 — Server Error']);
}
