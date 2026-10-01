<?php
class Auth
{
    /**
     * Attempt to log in, optionally restricting to a specific role portal.
     *
     * Returns a status string so the controller can show precise feedback:
     *   'ok'           — authenticated and session populated
     *   'invalid'      — email not found or password wrong
     *   'inactive'     — account suspended by admin
     *   'wrong_portal' — credentials valid but role doesn't match this portal
     */
    public static function attempt(string $email, string $password, ?string $requiredRole = null): string
    {
        $user = Database::queryOne(
            "SELECT id, name, email, password_hash, role, status
               FROM users WHERE email = ?",
            [$email]
        );

        if (!$user || !password_verify($password, $user['password_hash'])) {
            return 'invalid';
        }

        if ($user['status'] !== 'active') {
            return 'inactive';
        }

        // Portal guard: reject if the user's role doesn't match the login portal.
        if ($requiredRole !== null && $user['role'] !== $requiredRole) {
            return 'wrong_portal';
        }

        // Prevent session-fixation: regenerate ID on privilege change.
        session_regenerate_id(true);

        Session::set('user', [
            'id'    => (int)$user['id'],
            'name'  => $user['name'],
            'email' => $user['email'],
            'role'  => $user['role'],
        ]);
        Session::set('_last_activity', time());

        return 'ok';
    }

    /** Destroy the session and log the user out. */
    public static function logout(): void
    {
        Session::destroy();
    }

    /** Return true if a user is currently logged in. */
    public static function check(): bool
    {
        return Session::has('user');
    }

    /** Return the session user array, or null if not logged in. */
    public static function user(): ?array
    {
        return Session::get('user');
    }

    /** Return the current user's ID, or null. */
    public static function id(): ?int
    {
        return Session::get('user')['id'] ?? null;
    }

    /** Return the current user's role string, or null. */
    public static function role(): ?string
    {
        return Session::get('user')['role'] ?? null;
    }

    /** Return true if the current user has the given role. */
    public static function hasRole(string $role): bool
    {
        return self::role() === $role;
    }
}
