<?php
class AuthController
{
    // ── Shared helpers ────────────────────────────────────────────────────

    /**
     * Map an Auth::attempt() result to a human-readable error message
     * for the given portal.
     */
    private function attemptError(string $status, string $portalName): string
    {
        return match ($status) {
            'invalid'      => 'Invalid email or password. Please try again.',
            'inactive'     => 'Your account has been suspended. Please contact support.',
            'wrong_portal' => "This account is not registered for the {$portalName}. "
                            . "Please use the correct portal to sign in.",
            default        => 'An unexpected error occurred. Please try again.',
        };
    }

    // ── Patient Portal (/login) ───────────────────────────────────────────

    public function showLogin(): void
    {
        Middleware::requireGuest();
        view('public.login', [
            'title'  => 'Patient Portal — MediCare Plus',
            'errors' => [],
            'old'    => [],
        ]);
    }

    public function login(): void
    {
        Middleware::requireGuest();
        CSRF::verifyOrFail();

        $v = Validator::make($_POST)->validate([
            'email'    => 'required|email',
            'password' => 'required',
        ]);

        if ($v->fails()) {
            view('public.login', [
                'title'  => 'Patient Portal — MediCare Plus',
                'errors' => $v->errors(),
                'old'    => ['email' => $_POST['email'] ?? ''],
            ]);
            return;
        }

        $result = Auth::attempt($_POST['email'] ?? '', $_POST['password'] ?? '', 'patient');

        if ($result !== 'ok') {
            view('public.login', [
                'title'  => 'Patient Portal — MediCare Plus',
                'errors' => ['auth' => $this->attemptError($result, 'Patient Portal')],
                'old'    => ['email' => $_POST['email'] ?? ''],
            ]);
            return;
        }

        Session::flash('success', 'Welcome back, ' . Auth::user()['name'] . '!');
        redirect(url('patient/dashboard'));
    }

    // ── Doctor Portal (/doctor/login) ─────────────────────────────────────

    public function showDoctorLogin(): void
    {
        Middleware::requireGuest();
        view('doctor.login', [
            'title'  => 'Doctor Portal — MediCare Plus',
            'errors' => [],
            'old'    => [],
        ]);
    }

    public function doctorLogin(): void
    {
        Middleware::requireGuest();
        CSRF::verifyOrFail();

        $v = Validator::make($_POST)->validate([
            'email'    => 'required|email',
            'password' => 'required',
        ]);

        if ($v->fails()) {
            view('doctor.login', [
                'title'  => 'Doctor Portal — MediCare Plus',
                'errors' => $v->errors(),
                'old'    => ['email' => $_POST['email'] ?? ''],
            ]);
            return;
        }

        $result = Auth::attempt($_POST['email'] ?? '', $_POST['password'] ?? '', 'doctor');

        if ($result !== 'ok') {
            view('doctor.login', [
                'title'  => 'Doctor Portal — MediCare Plus',
                'errors' => ['auth' => $this->attemptError($result, 'Doctor Portal')],
                'old'    => ['email' => $_POST['email'] ?? ''],
            ]);
            return;
        }

        Session::flash('success', 'Welcome, Dr. ' . explode(' ', Auth::user()['name'])[0] . '!');
        redirect(url('doctor/dashboard'));
    }

    // ── Admin Portal (/admin/login) ───────────────────────────────────────

    public function showAdminLogin(): void
    {
        Middleware::requireGuest();
        view('admin.login', [
            'title'  => 'Administration — MediCare Plus',
            'errors' => [],
            'old'    => [],
        ]);
    }

    public function adminLogin(): void
    {
        Middleware::requireGuest();
        CSRF::verifyOrFail();

        $v = Validator::make($_POST)->validate([
            'email'    => 'required|email',
            'password' => 'required',
        ]);

        if ($v->fails()) {
            view('admin.login', [
                'title'  => 'Administration — MediCare Plus',
                'errors' => $v->errors(),
                'old'    => ['email' => $_POST['email'] ?? ''],
            ]);
            return;
        }

        $result = Auth::attempt($_POST['email'] ?? '', $_POST['password'] ?? '', 'admin');

        if ($result !== 'ok') {
            view('admin.login', [
                'title'  => 'Administration — MediCare Plus',
                'errors' => ['auth' => $this->attemptError($result, 'Admin Portal')],
                'old'    => ['email' => $_POST['email'] ?? ''],
            ]);
            return;
        }

        Session::flash('success', 'Signed in as administrator.');
        redirect(url('admin/dashboard'));
    }

    // ── Register (patients only) ──────────────────────────────────────────

    public function showRegister(): void
    {
        Middleware::requireGuest();
        view('public.register', [
            'title'  => 'Create Account — MediCare Plus',
            'errors' => [],
            'old'    => [],
        ]);
    }

    public function register(): void
    {
        Middleware::requireGuest();
        CSRF::verifyOrFail();

        $v = Validator::make($_POST)->validate([
            'name'             => 'required|min:2|max:120',
            'email'            => 'required|email|unique:users,email',
            'phone'            => 'max:20',
            'password'         => 'required|min:8|max:72',
            'password_confirm' => 'required|match:password',
            'dob'              => 'date',
            'gender'           => 'in:male,female,other',
            'blood_group'      => 'in:A+,A-,B+,B-,AB+,AB-,O+,O-',
        ]);

        if ($v->fails()) {
            view('public.register', [
                'title'  => 'Create Account — MediCare Plus',
                'errors' => $v->errors(),
                'old'    => $v->only([
                    'name','email','phone','dob',
                    'gender','blood_group','allergies','address',
                    'ec_name','ec_phone',
                ]),
            ]);
            return;
        }

        try {
            $userId = null;
            Database::transaction(function () use (&$userId) {
                $userId = User::create([
                    'name'     => $_POST['name']     ?? '',
                    'email'    => $_POST['email']    ?? '',
                    'phone'    => $_POST['phone']    ?? '',
                    'password' => $_POST['password'] ?? '',
                    'role'     => 'patient',
                ]);
                Patient::create($userId, [
                    'dob'         => $_POST['dob']         ?? '',
                    'gender'      => $_POST['gender']      ?? '',
                    'blood_group' => $_POST['blood_group'] ?? '',
                    'allergies'   => $_POST['allergies']   ?? '',
                    'address'     => $_POST['address']     ?? '',
                    'ec_name'     => $_POST['ec_name']     ?? '',
                    'ec_phone'    => $_POST['ec_phone']    ?? '',
                ]);
            });

            Auth::attempt($_POST['email'] ?? '', $_POST['password'] ?? '', 'patient');
            Session::flash('success', 'Account created! Welcome to MediCare Plus.');
            redirect(url('patient/dashboard'));

        } catch (Throwable $e) {
            logError('Registration failed', ['error' => $e->getMessage()]);
            view('public.register', [
                'title'  => 'Create Account — MediCare Plus',
                'errors' => ['general' => 'Registration failed. Please try again.'],
                'old'    => $v->only([
                    'name','email','phone','dob',
                    'gender','blood_group','allergies','address',
                    'ec_name','ec_phone',
                ]),
            ]);
        }
    }

    // ── Logout ────────────────────────────────────────────────────────────

    public function logout(): void
    {
        $role = Auth::role();
        Auth::logout();
        // Redirect back to the correct portal after logout
        $destination = match ($role) {
            'doctor' => url('doctor/login'),
            'admin'  => url('admin/login'),
            default  => url('login'),
        };
        Session::flash('success', 'You have been signed out successfully.');
        redirect($destination);
    }
}
