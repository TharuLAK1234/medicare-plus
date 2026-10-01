<?php
/**
 * Application Configuration
 *
 * Central registry for app-wide constants.
 * WHY constants not variables: constants are immutable after definition,
 * so no code can accidentally overwrite APP_ENV or LOG_PATH mid-request.
 */

define('APP_NAME',    'MediCare Plus');
define('APP_VERSION', '1.0.0');
define('APP_ENV',     'development'); // change to 'production' on live server
define('APP_DEBUG',   APP_ENV === 'development');
define('APP_URL',     'http://localhost/medicare-plus/public');

// ── Absolute paths ─────────────────────────────────────────────────────────
define('ROOT_PATH',    dirname(__DIR__));
define('CONFIG_PATH',  ROOT_PATH . '/config');
define('CORE_PATH',    ROOT_PATH . '/core');
define('VIEW_PATH',    ROOT_PATH . '/views');
define('CTRL_PATH',    ROOT_PATH . '/controllers');
define('MODEL_PATH',   ROOT_PATH . '/models');
define('STORAGE_PATH', ROOT_PATH . '/storage');
define('LOG_PATH',     STORAGE_PATH . '/logs');
define('UPLOAD_PATH',  STORAGE_PATH . '/uploads');
define('REPORT_PATH',  UPLOAD_PATH  . '/reports');

// ── Session ────────────────────────────────────────────────────────────────
define('SESSION_NAME',     'medicare_sess');
define('SESSION_LIFETIME', 3600); // 1 hour idle timeout

// ── File uploads ───────────────────────────────────────────────────────────
define('MAX_UPLOAD_BYTES',   5 * 1024 * 1024);              // 5 MB
define('ALLOWED_MIME_TYPES', ['application/pdf',
                               'image/jpeg', 'image/png']); // whitelist
define('ALLOWED_EXTENSIONS', ['pdf', 'jpg', 'jpeg', 'png']);

// ── Pagination ─────────────────────────────────────────────────────────────
define('ITEMS_PER_PAGE', 10);

// ── Error reporting ────────────────────────────────────────────────────────
// Never display errors in production — log them instead.
if (APP_DEBUG) {
    ini_set('display_errors', 1);
    ini_set('display_startup_errors', 1);
    error_reporting(E_ALL);
} else {
    ini_set('display_errors', 0);
    error_reporting(0);
}
ini_set('log_errors', 1);
ini_set('error_log', LOG_PATH . '/php_errors.log');
