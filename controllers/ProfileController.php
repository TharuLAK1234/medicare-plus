<?php
class ProfileController
{
    public function show(): void
    {
        Middleware::requireRole('patient');
        $user    = Database::queryOne("SELECT * FROM users WHERE id = ?", [Auth::id()]);
        $patient = Patient::findByUserId(Auth::id());

        view('patient.profile', [
            'title'   => 'My Profile — MediCare Plus',
            'user'    => $user,
            'patient' => $patient ?? [],
        ]);
    }

    public function update(): void
    {
        Middleware::requireRole('patient');
        CSRF::verifyOrFail();

        $name    = trim($_POST['name']    ?? '');
        $phone   = trim($_POST['phone']   ?? '');
        $dob     = trim($_POST['dob']     ?? '') ?: null;
        $gender  = trim($_POST['gender']  ?? '') ?: null;
        $blood   = trim($_POST['blood_group'] ?? '') ?: null;
        $allerg  = trim($_POST['allergies']   ?? '') ?: null;
        $addr    = trim($_POST['address']     ?? '') ?: null;
        $ecName  = trim($_POST['ec_name']     ?? '') ?: null;
        $ecPhone = trim($_POST['ec_phone']    ?? '') ?: null;

        Database::execute(
            "UPDATE users SET name = ?, phone = ? WHERE id = ?",
            [$name, $phone ?: null, Auth::id()]
        );

        $patient = Patient::findByUserId(Auth::id());
        if ($patient) {
            Database::execute(
                "UPDATE patients SET dob=?,gender=?,blood_group=?,allergies=?,address=?,ec_name=?,ec_phone=?
                  WHERE user_id=?",
                [$dob,$gender,$blood,$allerg,$addr,$ecName,$ecPhone,Auth::id()]
            );
        } else {
            Database::execute(
                "INSERT INTO patients (user_id,dob,gender,blood_group,allergies,address,ec_name,ec_phone)
                 VALUES (?,?,?,?,?,?,?,?)",
                [Auth::id(),$dob,$gender,$blood,$allerg,$addr,$ecName,$ecPhone]
            );
        }

        // Update session name
        $u = Auth::user();
        $u['name'] = $name;
        Session::set('user', $u);

        Session::flash('success', 'Profile updated successfully.');
        redirect(url('profile'));
    }
}
