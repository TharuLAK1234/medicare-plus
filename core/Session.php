<?php
/**
 * Session — centralised session management.
 *
 * WHY a dedicated class: every security measure (secure cookies, timeout,
 * flash messages) lives in one place rather than being scattered across
 * every controller, preventing accidental omissions.
 */
class Session
{
    /**
     * Start the session with hardened cookie parameters.
     * Safe to call multiple times — checks session status first.
     */
    public static function start(): void
    {
        if (session_status() === PHP_SESSION_ACTIVE) return;

        session_name(SESSION_NAME);

        // HttpOnly: JS cannot read the cookie (mitigates XSS token theft).
        // SameSite Strict: cookie not sent on cross-site requests (mitigates CSRF).
        // Secure should be true on HTTPS production servers.
        session_set_cookie_params([
            'lifetime' => 0,        // session cookie — expires when browser closes
            'path'     => '/',
            'domain'   => '',
            'secure'   => false,    // set to true when serving over HTTPS
            'httponly' => true,
            'samesite' => 'Strict',
        ]);

        session_start();

        // ── Idle timeout ──────────────────────────────────────────────────
        // If the user has been inactive longer than SESSION_LIFETIME seconds,
        // destroy the session — prevents hijacking of unattended computers.
        if (isset($_SESSION['_last_activity'])) {
            if (time() - $_SESSION['_last_activity'] > SESSION_LIFETIME) {
                self::destroy();
                return;
            }
        }
        $_SESSION['_last_activity'] = time();
    }

    /** Store a value in the session. */
    public static function set(string $key, mixed $value): void
    {
        $_SESSION[$key] = $value;
    }

    /** Retrieve a value from the session, or $default if missing. */
    public static function get(string $key, mixed $default = null): mixed
    {
        return $_SESSION[$key] ?? $default;
    }

    /** Check whether a session key exists. */
    public static function has(string $key): bool
    {
        return isset($_SESSION[$key]);
    }

    /** Remove a key from the session. */
    public static function remove(string $key): void
    {
        unset($_SESSION[$key]);
    }

    /** Completely destroy the session and its cookie. */
    public static function destroy(): void
    {
        $_SESSION = [];
        if (ini_get('session.use_cookies')) {
            $p = session_get_cookie_params();
            setcookie(session_name(), '', time() - 42000,
                $p['path'], $p['domain'], $p['secure'], $p['httponly']);
        }
        session_destroy();
    }

    // ── Flash messages ────────────────────────────────────────────────────
    // Flash messages are written once and deleted on first read — ideal for
    // "success" / "error" banners after a redirect.

    /** Write a flash message. */
    public static function flash(string $key, string $message): void
    {
        $_SESSION['_flash'][$key] = $message;
    }

    /** Read a flash message and immediately delete it. */
    public static function getFlash(string $key): ?string
    {
        $msg = $_SESSION['_flash'][$key] ?? null;
        unset($_SESSION['_flash'][$key]);
        return $msg;
    }

    /** Check whether a flash message exists (without deleting it). */
    public static function hasFlash(string $key): bool
    {
        return isset($_SESSION['_flash'][$key]);
    }
}
