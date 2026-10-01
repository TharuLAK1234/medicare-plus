<?php
class Middleware
{
    /** Redirect to login if the user is not authenticated. */
    public static function requireAuth(): void
    {
        if (!Auth::check()) {
            Session::flash('error', 'Please log in to access that page.');
            redirect(url('login'));
        }
    }

    /** Abort with 403 if the authenticated user does not have the required role. */
    public static function requireRole(string $role): void
    {
        self::requireAuth();
        if (!Auth::hasRole($role)) {
            http_response_code(403);
            view('errors.403', ['title' => '403 — Access Denied']);
            exit;
        }
    }

    /**
     * Redirect authenticated users away from guest-only pages
     * (login, register) to their role dashboard.
     */
    public static function requireGuest(): void
    {
        if (Auth::check()) {
            redirect(self::dashboardUrl());
        }
    }

    /** Return the home dashboard URL for the current user's role. */
    public static function dashboardUrl(): string
    {
        return match (Auth::role()) {
            'admin'   => url('admin/dashboard'),
            'doctor'  => url('doctor/dashboard'),
            'patient' => url('patient/dashboard'),
            default   => url(''),
        };
    }
}
