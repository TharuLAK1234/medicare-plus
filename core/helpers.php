<?php
/**
 * Global helper functions
 *
 * Small, stateless utilities used across the entire app.
 * Procedural (not static methods) so views can call e($value)
 * instead of the more verbose Helper::e($value).
 */

/**
 * Escape output for HTML context — call on EVERY user-supplied value.
 * Prevents Cross-Site Scripting (XSS) (OWASP, 2021).
 */
function e(mixed $value): string
{
    return htmlspecialchars((string)$value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

/** Redirect and halt execution. */
function redirect(string $url): void
{
    header('Location: ' . $url);
    exit;
}

/** Build an absolute URL from a path relative to APP_URL. */
function url(string $path = ''): string
{
    return rtrim(APP_URL, '/') . '/' . ltrim($path, '/');
}

/**
 * Render a view file with optional data extracted into local variables.
 *
 * @param string $view Dot-notation: 'public.home' → views/public/home.php
 * @param array  $data Variables made available inside the view
 */
function view(string $view, array $data = []): void
{
    $file = VIEW_PATH . '/' . str_replace('.', DIRECTORY_SEPARATOR, $view) . '.php';

    if (!file_exists($file)) {
        throw new RuntimeException("View not found: {$file}");
    }

    extract($data, EXTR_SKIP); // EXTR_SKIP prevents overwriting existing vars
    require $file;
}

/**
 * Generate a unique appointment reference number.
 * Format: MP-YYYY-NNNNN  e.g. MP-2024-00847
 */
function generateRefNo(): string
{
    return sprintf('MP-%s-%05d', date('Y'), random_int(1, 99999));
}

/** Format a Y-m-d date string for display. */
function formatDate(string $date, string $fmt = 'd M Y'): string
{
    return (new DateTime($date))->format($fmt);
}

/** Format H:i:s time string as 12-hour e.g. 10:00 AM. */
function formatTime(string $time): string
{
    return (new DateTime("1970-01-01 {$time}"))->format('h:i A');
}

/** Human-readable file size: 2097152 → "2.0 MB". */
function formatFileSize(int $bytes): string
{
    if ($bytes >= 1048576) return round($bytes / 1048576, 1) . ' MB';
    if ($bytes >= 1024)    return round($bytes / 1024, 1)    . ' KB';
    return $bytes . ' B';
}

/**
 * Return the Bootstrap badge CSS classes for a given appointment status.
 * Centralising this means a status colour change is a one-line edit.
 */
function statusBadge(string $status): string
{
    $map = [
        'confirmed' => 'badge bg-success',
        'pending'   => 'badge bg-warning text-dark',
        'completed' => 'badge bg-primary',
        'cancelled' => 'badge bg-danger',
    ];
    return $map[$status] ?? 'badge bg-secondary';
}

/**
 * Render read-only HTML star icons for a given average rating.
 * Uses Bootstrap Icons (bi-star, bi-star-half, bi-star-fill).
 */
function starRating(float $avg, int $count = 0): string
{
    $html = '<span class="stars" aria-label="' . round($avg, 1) . ' out of 5 stars">';
    for ($i = 1; $i <= 5; $i++) {
        if ($avg >= $i) {
            $html .= '<i class="bi bi-star-fill text-warning"></i>';
        } elseif ($avg >= $i - 0.5) {
            $html .= '<i class="bi bi-star-half text-warning"></i>';
        } else {
            $html .= '<i class="bi bi-star text-muted"></i>';
        }
    }
    $html .= '</span>';
    if ($count > 0) {
        $html .= ' <small class="text-muted">(' . e($count) . ')</small>';
    }
    return $html;
}

/**
 * Append a timestamped entry to the application log file.
 */
function logError(string $message, array $context = []): void
{
    $entry = date('[Y-m-d H:i:s]') . ' ERROR: ' . $message;
    if (!empty($context)) {
        $entry .= ' | ' . json_encode($context, JSON_UNESCAPED_UNICODE);
    }
    @file_put_contents(LOG_PATH . '/app.log', $entry . PHP_EOL, FILE_APPEND | LOCK_EX);
}

/** Validate a date string matches the expected format. */
function isValidDate(string $date, string $format = 'Y-m-d'): bool
{
    $d = DateTime::createFromFormat($format, $date);
    return $d && $d->format($format) === $date;
}

/**
 * Strip dangerous characters from a filename (prevents directory traversal).
 */
function sanitiseFilename(string $filename): string
{
    return preg_replace('/[^\w.\-]/', '_', basename($filename));
}

/** Return the currently authenticated user array from the session, or null. */
function auth(): ?array
{
    return $_SESSION['user'] ?? null;
}

/** Check whether the current user has a specific role. */
function hasRole(string $role): bool
{
    return (auth()['role'] ?? '') === $role;
}
