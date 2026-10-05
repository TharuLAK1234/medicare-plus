<?php
class AuthController
{
    private const MAX_ATTEMPTS  = 5;
    private const LOCKOUT_SECS  = 900; // 15 minutes

    // ── Rate-limit helpers ────────────────────────────────────────────────

    private function rateLimitKey(string $email): string
    {
        return 'rl_' . md5(strtolower(trim($email)) . ($_SERVER['REMOTE_ADDR'] ?? ''));
    }

    private function checkRateLimit(string $email): ?string
    {
        $key  = $this->rateLimitKey($email);
        $data = $_SESSION[$key] ?? ['count' => 0, 'until' => 0];

        if ($data['until'] > time()) {
            $mins = ceil(($data['until'] - time()) / 60);
            return "Too many failed attempts. Please try again in {$mins} minute(s).";
        }
        return null;
    }

    private function recordFailure(string $email): void
    {
        $key  = $this->rateLimitKey($email);
        $data = $_SESSION[$key] ?? ['count' => 0, 'until' => 0];

        $data['count']++;
        if ($data['count'] >= self::MAX_ATTEMPTS) {
            $data['until'] = time() + self::LOCKOUT_SECS;
            $data['count'] = 0;
        }
        $_SESSION[$key] = $data;
    }

    private function clearRateLimit(string $email): void
    {
        unset($_SESSION[$this->rateLimitKey($email)]);
    }

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

        $email = $_POST['email'] ?? '';

        if ($lock = $this->checkRateLimit($email)) {
            view('public.login', ['title' => 'Patient Portal — MediCare Plus',
                'errors' => ['auth' => $lock], 'old' => ['email' => $email]]);
            return;
        }

        $result = Auth::attempt($email, $_POST['password'] ?? '', 'patient');

        if ($result !== 'ok') {
            $this->recordFailure($email);
            view('public.login', [
                'title'  => 'Patient Portal — MediCare Plus',
                'errors' => ['auth' => $this->attemptError($result, 'Patient Portal')],
                'old'    => ['email' => $email],
            ]);
            return;
        }

        $this->clearRateLimit($email);
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

        $email = $_POST['email'] ?? '';

        if ($lock = $this->checkRateLimit($email)) {
            view('doctor.login', ['title' => 'Doctor Portal — MediCare Plus',
                'errors' => ['auth' => $lock], 'old' => ['email' => $email]]);
            return;
        }

        $result = Auth::attempt($email, $_POST['password'] ?? '', 'doctor');

        if ($result !== 'ok') {
            $this->recordFailure($email);
            view('doctor.login', [
                'title'  => 'Doctor Portal — MediCare Plus',
                'errors' => ['auth' => $this->attemptError($result, 'Doctor Portal')],
                'old'    => ['email' => $email],
            ]);
            return;
        }

        $this->clearRateLimit($email);
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

        $email = $_POST['email'] ?? '';

        if ($lock = $this->checkRateLimit($email)) {
            view('admin.login', ['title' => 'Administration — MediCare Plus',
                'errors' => ['auth' => $lock], 'old' => ['email' => $email]]);
            return;
        }

        $result = Auth::attempt($email, $_POST['password'] ?? '', 'admin');

        if ($result !== 'ok') {
            $this->recordFailure($email);
            view('admin.login', [
                'title'  => 'Administration — MediCare Plus',
                'errors' => ['auth' => $this->attemptError($result, 'Admin Portal')],
                'old'    => ['email' => $email],
            ]);
            return;
        }

        $this->clearRateLimit($email);
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
