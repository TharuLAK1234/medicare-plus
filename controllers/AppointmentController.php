<?php
class AppointmentController
{
    // ── Patient: appointment list & cancel ────────────────────────────────

    public function patientIndex(): void
    {
        Middleware::requireRole('patient');
        $userId       = Auth::id();
        $appointments = Appointment::forPatient($userId, 100);
        $counts       = Appointment::countsByStatus($userId, 'patient');

        view('patient.appointments', [
            'title'        => 'My Appointments — MediCare Plus',
            'appointments' => $appointments,
            'counts'       => $counts,
        ]);
    }

    // ── Doctor: appointment queue ─────────────────────────────────────────

    public function doctorIndex(): void
    {
        Middleware::requireRole('doctor');
        $userId       = Auth::id();
        $appointments = Appointment::forDoctor($userId, 100);
        $counts       = Appointment::countsByStatus($userId, 'doctor');

        view('doctor.appointments', [
            'title'        => 'Appointments — MediCare Plus',
            'appointments' => $appointments,
            'counts'       => $counts,
        ]);
    }

    // ── Admin: all appointments ───────────────────────────────────────────

    public function adminIndex(): void
    {
        Middleware::requireRole('admin');
        $status = $_GET['status'] ?? '';
        $appointments = Appointment::all(
            $status ? ['status' => $status] : [],
            100
        );
        $counts = Appointment::globalCounts();

        view('admin.appointments', [
            'title'        => 'All Appointments — MediCare Plus',
            'appointments' => $appointments,
            'counts'       => $counts,
            'filter'       => $status,
        ]);
    }

    // ── Mutations (all POST) ──────────────────────────────────────────────

    public function cancel(): void
    {
        Middleware::requireAuth();
        CSRF::verifyOrFail();

        $id   = (int)($_POST['appointment_id'] ?? 0);
        $role = Auth::role();
        $uid  = Auth::id();

        if (!in_array($role, ['patient', 'admin'], true)) {
            http_response_code(403); exit('Forbidden');
        }

        $ok = Appointment::cancel($id, $uid, $role);

        if ($ok) {
            Session::flash('success', 'Appointment cancelled successfully.');
        } else {
            Session::flash('error', 'Unable to cancel that appointment.');
        }

        $back = $_POST['redirect_to'] ?? ($role === 'admin' ? url('admin/appointments') : url('appointments'));
        redirect($back);
    }

    public function confirm(): void
    {
        Middleware::requireRole('doctor');
        CSRF::verifyOrFail();

        $id = (int)($_POST['appointment_id'] ?? 0);
        $ok = Appointment::confirm($id, Auth::id());

        Session::flash($ok ? 'success' : 'error',
            $ok ? 'Appointment confirmed.' : 'Unable to confirm that appointment.');

        redirect($_POST['redirect_to'] ?? url('doctor/appointments'));
    }

    public function complete(): void
    {
        Middleware::requireRole('doctor');
        CSRF::verifyOrFail();

        $id    = (int)($_POST['appointment_id'] ?? 0);
        $notes = trim($_POST['notes'] ?? '');
        $ok    = Appointment::complete($id, Auth::id(), $notes);

        Session::flash($ok ? 'success' : 'error',
            $ok ? 'Appointment marked as completed.' : 'Unable to complete that appointment.');

        redirect($_POST['redirect_to'] ?? url('doctor/appointments'));
    }
}
