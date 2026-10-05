<?php
class RatingController
{
    // GET /appointments/:id/rate
    public function showForm(int $apptId): void
    {
        Middleware::requireRole('patient');

        $appt = Appointment::findById($apptId);
        if (!$appt || $appt['patient_user_id'] != Auth::id() || $appt['status'] !== 'completed') {
            http_response_code(403);
            view('errors.403', ['title' => 'Access Denied']);
            return;
        }

        // Already rated?
        $existing = Database::queryOne(
            "SELECT id FROM ratings WHERE appointment_id = ?", [$apptId]
        );
        if ($existing) {
            Session::flash('info', 'You have already rated this appointment.');
            redirect(url('appointments'));
            return;
        }

        view('patient.rate', [
            'title' => 'Rate Appointment — MediCare Plus',
            'appt'  => $appt,
        ]);
    }

    // POST /ratings/store
    public function store(): void
    {
        Middleware::requireRole('patient');
        CSRF::verifyOrFail();

        $apptId = (int)($_POST['appointment_id'] ?? 0);
        $stars  = (int)($_POST['stars'] ?? 0);
        $review = trim($_POST['review'] ?? '');

        if ($stars < 1 || $stars > 5) {
            Session::flash('error', 'Please select a star rating (1–5).');
            redirect(url('appointments/' . $apptId . '/rate'));
            return;
        }

        $appt = Appointment::findById($apptId);
        if (!$appt || $appt['patient_user_id'] != Auth::id() || $appt['status'] !== 'completed') {
            http_response_code(403); exit('Forbidden');
        }

        // Upsert — one rating per appointment
        $existing = Database::queryOne(
            "SELECT id FROM ratings WHERE appointment_id = ?", [$apptId]
        );
        if ($existing) {
            Database::execute(
                "UPDATE ratings SET stars = ?, review = ?, updated_at = NOW() WHERE id = ?",
                [$stars, $review ?: null, $existing['id']]
            );
        } else {
            $doctorRec = Doctor::findById($appt['doctor_id']);
            Database::execute(
                "INSERT INTO ratings (appointment_id, patient_id, doctor_id, stars, review)
                 VALUES (?, ?, ?, ?, ?)",
                [$apptId, $appt['patient_id'], $appt['doctor_id'], $stars, $review ?: null]
            );
        }

        Session::flash('success', 'Thank you for your rating!');
        redirect(url('appointments'));
    }
}
