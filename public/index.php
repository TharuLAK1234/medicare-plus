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

// ── Controllers ───────────────────────────────────────────
require_once ROOT_PATH . '/controllers/AuthController.php';

// ── Session ───────────────────────────────────────────────
Session::start();

// ── Routing ───────────────────────────────────────────────
$method  = $_SERVER['REQUEST_METHOD'];

// Strip the APP_URL base path so we work with just the route segment.
// APP_URL = http://localhost/medicare-plus/public
$basePath = rtrim(parse_url(APP_URL, PHP_URL_PATH), '/');            // /medicare-plus/public
$requestPath = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);     // /medicare-plus/public/login
$route = trim(substr($requestPath, strlen($basePath)), '/') ?: 'home'; // login

// Normalise trailing slashes
$route = rtrim($route, '/');

$auth = new AuthController();

// ── Route table ───────────────────────────────────────────
try {
    if ($method === 'GET') {
        switch ($route) {
            case 'home':
            case '':
                view('public.home', ['title' => 'MediCare Plus — Quality Healthcare Online']);
                break;

            // ── Patient portal ──
            case 'login':
                $auth->showLogin();
                break;

            case 'register':
                $auth->showRegister();
                break;

            // ── Doctor portal ──
            case 'doctor/login':
                $auth->showDoctorLogin();
                break;

            // ── Admin portal ──
            case 'admin/login':
                $auth->showAdminLogin();
                break;

            case 'logout':
                $auth->logout();
                break;

            case 'patient/dashboard':
                Middleware::requireRole('patient');
                view('patient.dashboard', ['title' => 'Patient Dashboard — MediCare Plus']);
                break;

            case 'doctor/dashboard':
                Middleware::requireRole('doctor');
                view('doctor.dashboard', ['title' => 'Doctor Dashboard — MediCare Plus']);
                break;

            case 'admin/dashboard':
                Middleware::requireRole('admin');
                view('admin.dashboard', ['title' => 'Admin Dashboard — MediCare Plus']);
                break;

            // ── Future routes (Phase 5–8) — 404 until built ──
            case 'services':
            case 'doctors':
            case 'appointments':
            case 'reports':
            case 'messages':
            case 'profile':
            case 'admin/doctors':
            case 'admin/users':
            case 'admin/appointments':
            case 'admin/services':
            case 'admin/reports':
            case 'doctor/schedule':
            case 'doctor/patients':
                Middleware::requireAuth();
                http_response_code(501);
                view('errors.404', [
                    'title'   => 'Coming Soon — MediCare Plus',
                    'heading' => '🚧 Coming Soon',
                    'message' => 'This section is being built. Check back shortly.',
                ]);
                break;

            default:
                http_response_code(404);
                view('errors.404', ['title' => '404 — Page Not Found']);
        }

    } elseif ($method === 'POST') {
        switch ($route) {
            case 'login':
                $auth->login();
                break;

            case 'register':
                $auth->register();
                break;

            case 'doctor/login':
                $auth->doctorLogin();
                break;

            case 'admin/login':
                $auth->adminLogin();
                break;

            default:
                http_response_code(404);
                view('errors.404', ['title' => '404 — Page Not Found']);
        }

    } else {
        // HEAD, OPTIONS, etc.
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
