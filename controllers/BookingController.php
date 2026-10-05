<?php
class BookingController
{
    // GET /doctors/:id — doctor profile + booking form
    public function showProfile(int $doctorId): void
    {
        $doctor = Doctor::findById($doctorId);
        if (!$doctor || $doctor['status'] !== 'active') {
            http_response_code(404);
            view('errors.404', ['title' => 'Doctor Not Found']);
            return;
        }

        $rating       = Doctor::getRating($doctorId);
        $ratings      = Doctor::getRecentRatings($doctorId, 10);
        $availability = Doctor::getAvailability($doctorId);

        view('public.doctor-profile', [
            'title'        => 'Dr. ' . $doctor['name'] . ' — MediCare Plus',
            'doctor'       => $doctor,
            'rating'       => $rating,
            'ratings'      => $ratings,
            'availability' => $availability,
        ]);
    }

    // GET /booking/slots — AJAX: returns JSON array of available time slots
    public function slots(): void
    {
        header('Content-Type: application/json');

        $doctorId = (int)($_GET['doctor_id'] ?? 0);
        $date     = $_GET['date'] ?? '';

        if (!$doctorId || !$date || !strtotime($date)) {
            echo json_encode([]); return;
        }

        // Must be future date
        if ($date <= date('Y-m-d')) {
            echo json_encode([]); return;
        }

        $day = date('l', strtotime($date)); // Monday, Tuesday, …
        $avail = Database::queryAll(
            "SELECT start_time, end_time FROM doctor_availability
              WHERE doctor_id = ? AND day_of_week = ? AND is_available = 1",
            [$doctorId, $day]
        );

        if (empty($avail)) { echo json_encode([]); return; }

        // Generate 30-min slots and remove already-booked ones
        $booked = array_column(
            Database::queryAll(
                "SELECT appt_time FROM appointments
                  WHERE doctor_id = ? AND appt_date = ?
                    AND status IN ('pending','confirmed')",
                [$doctorId, $date]
            ),
            'appt_time'
        );

        $slots = [];
        foreach ($avail as $a) {
            $cur  = strtotime($a['start_time']);
            $end  = strtotime($a['end_time']);
            while ($cur + 1800 <= $end) {
                $t = date('H:i:s', $cur);
                if (!in_array($t, $booked)) {
                    $slots[] = ['time' => $t, 'label' => date('h:i A', $cur)];
                }
                $cur += 1800;
            }
        }

        echo json_encode($slots);
    }

    // POST /booking/create — show confirmation page
    public function create(): void
    {
        Middleware::requireRole('patient');
        CSRF::verifyOrFail();

        $doctorId  = (int)($_POST['doctor_id'] ?? 0);
        $apptDate  = trim($_POST['appt_date'] ?? '');
        $apptTime  = trim($_POST['appt_time'] ?? '');
        $reason    = trim($_POST['reason'] ?? '');

        $doctor = Doctor::findById($doctorId);
        if (!$doctor) { redirect(url('doctors')); return; }

        if (!$apptDate || !$apptTime) {
            Session::flash('error', 'Please select a date and time.');
            redirect(url('doctors/' . $doctorId));
            return;
        }

        view('public.booking', [
            'title'     => 'Confirm Booking — MediCare Plus',
            'doctor'    => $doctor,
            'appt_date' => $apptDate,
            'appt_time' => $apptTime,
            'reason'    => $reason,
        ]);
    }

    // POST /booking/store — save to DB
    public function store(): void
    {
        Middleware::requireRole('patient');
        CSRF::verifyOrFail();

        $doctorId  = (int)($_POST['doctor_id'] ?? 0);
        $apptDate  = trim($_POST['appt_date'] ?? '');
        $apptTime  = trim($_POST['appt_time'] ?? '');
        $reason    = trim($_POST['reason'] ?? '');

        $doctor  = Doctor::findById($doctorId);
        $patient = Patient::findByUserId(Auth::id());

        if (!$doctor || !$patient) {
            Session::flash('error', 'Invalid booking request.');
            redirect(url('doctors'));
            return;
        }

        // Validate date is future
        if ($apptDate <= date('Y-m-d')) {
            Session::flash('error', 'Please choose a future date.');
            redirect(url('doctors/' . $doctorId));
            return;
        }

        // Double-booking check
        $clash = Database::queryOne(
            "SELECT id FROM appointments
              WHERE doctor_id = ? AND appt_date = ? AND appt_time = ?
                AND status IN ('pending','confirmed')",
            [$doctorId, $apptDate, $apptTime]
        );
        if ($clash) {
            Session::flash('error', 'That time slot was just taken. Please pick another.');
            redirect(url('doctors/' . $doctorId));
            return;
        }

        // Determine service_id from doctor
        $serviceId = $doctor['service_id'] ?? null;

        // Generate ref number
        $ref = 'MP-' . strtoupper(substr(md5(uniqid('', true)), 0, 8));

        Database::execute(
            "INSERT INTO appointments
               (ref_no, patient_id, doctor_id, service_id, appt_date, appt_time, reason, fee, status)
             VALUES (?, ?, ?, ?, ?, ?, ?, ?, 'pending')",
            [$ref, $patient['id'], $doctorId, $serviceId, $apptDate, $apptTime, $reason, $doctor['fee']]
        );

        $apptId = Database::lastInsertId();
        $appt   = Appointment::findById($apptId);

        Session::flash('success', 'Appointment booked! Ref: ' . $ref);
        view('public.booking-success', [
            'title' => 'Booking Confirmed — MediCare Plus',
            'appt'  => $appt,
        ]);
    }

    // GET /doctors — listing
    public function index(): void
    {
        $search  = trim($_GET['search'] ?? '');
        $service = trim($_GET['service'] ?? '');
        $doctors  = Doctor::search($search, $service);
        $services = Doctor::allServices();

        view('public.doctors', [
            'title'    => 'Find a Doctor — MediCare Plus',
            'doctors'  => $doctors,
            'services' => $services,
        ]);
    }
}
