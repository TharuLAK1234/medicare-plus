<?php
class CSRF
{
    private const KEY = '_csrf_token';

    /** Return the current CSRF token, generating one if it does not exist. */
    public static function token(): string
    {
        if (!Session::has(self::KEY)) {
            Session::set(self::KEY, bin2hex(random_bytes(32)));
        }
        return Session::get(self::KEY);
    }

    /** Render a hidden <input> element containing the CSRF token. */
    public static function field(): string
    {
        return '<input type="hidden" name="_csrf_token" value="' . e(self::token()) . '">';
    }

    /**
     * Verify the submitted token against the session token.
     * Uses hash_equals() to prevent timing-based attacks.
     * Always invalidates the old token so it cannot be replayed.
     */
    public static function verify(): bool
    {
        $submitted = $_POST['_csrf_token'] ?? '';
        $expected  = Session::get(self::KEY, '');

        $valid = ($expected !== '') && hash_equals($expected, $submitted);

        // Rotate token after each POST regardless of outcome.
        Session::remove(self::KEY);

        return $valid;
    }

    /** Verify the token and immediately abort with 403 if invalid. */
    public static function verifyOrFail(): void
    {
        if (!self::verify()) {
            http_response_code(403);
            exit('Invalid or expired form token. Please go back and try again.');
        }
    }
}
