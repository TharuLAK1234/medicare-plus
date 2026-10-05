<?php
class ReportController
{
    private const UPLOAD_DIR  = ROOT_PATH . '/storage/reports/';
    private const MAX_SIZE    = 10 * 1024 * 1024; // 10 MB
    private const ALLOWED_MIME = [
        'application/pdf',
        'image/jpeg', 'image/png', 'image/gif',
        'application/msword',
        'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
    ];

    // GET /reports  — patient view
    public function patientIndex(): void
    {
        Middleware::requireRole('patient');
        $reports = Report::forPatient(Auth::id());

        view('patient.reports', [
            'title'   => 'My Reports — MediCare Plus',
            'reports' => $reports,
        ]);
    }

    // GET /doctor/reports  — doctor view + upload form
    public function doctorIndex(): void
    {
        Middleware::requireRole('doctor');
        $doctorRec = Doctor::findByUserId(Auth::id());
        $reports   = $doctorRec ? Report::forDoctor($doctorRec['id']) : [];
        $patients  = $doctorRec ? Doctor::getPatients(Auth::id(), 50) : [];

        view('doctor.reports', [
            'title'    => 'Reports — MediCare Plus',
            'reports'  => $reports,
            'patients' => $patients,
            'doctor'   => $doctorRec,
        ]);
    }

    // POST /doctor/reports/upload
    public function upload(): void
    {
        Middleware::requireRole('doctor');
        CSRF::verifyOrFail();

        $doctorRec = Doctor::findByUserId(Auth::id());
        if (!$doctorRec) { redirect(url('doctor/reports')); return; }

        $patientId = (int)($_POST['patient_id'] ?? 0);
        $title     = trim($_POST['title'] ?? '');
        $type      = trim($_POST['type']  ?? 'lab');
        $apptId    = (int)($_POST['appointment_id'] ?? 0) ?: null;

        if (!$patientId || !$title) {
            Session::flash('error', 'Patient and report title are required.');
            redirect(url('doctor/reports'));
            return;
        }

        if (empty($_FILES['report_file']['name'])) {
            Session::flash('error', 'Please select a file to upload.');
            redirect(url('doctor/reports'));
            return;
        }

        $file = $_FILES['report_file'];

        if ($file['size'] > self::MAX_SIZE) {
            Session::flash('error', 'File too large. Maximum size is 10 MB.');
            redirect(url('doctor/reports'));
            return;
        }

        // Validate MIME (use finfo, not extension)
        $finfo = new finfo(FILEINFO_MIME_TYPE);
        $mime  = $finfo->file($file['tmp_name']);
        if (!in_array($mime, self::ALLOWED_MIME, true)) {
            Session::flash('error', 'File type not allowed. Upload PDF, image, or Word document.');
            redirect(url('doctor/reports'));
            return;
        }

        // Ensure upload dir exists
        if (!is_dir(self::UPLOAD_DIR)) {
            mkdir(self::UPLOAD_DIR, 0755, true);
        }

        // Generate safe filename
        $ext      = pathinfo($file['name'], PATHINFO_EXTENSION);
        $safeName = uniqid('rpt_', true) . '.' . strtolower($ext);
        $dest     = self::UPLOAD_DIR . $safeName;

        if (!move_uploaded_file($file['tmp_name'], $dest)) {
            Session::flash('error', 'Upload failed. Please try again.');
            redirect(url('doctor/reports'));
            return;
        }

        Report::create([
            'patient_id'     => $patientId,
            'doctor_id'      => $doctorRec['id'],
            'appointment_id' => $apptId,
            'title'          => $title,
            'type'           => $type,
            'file_path'      => $dest,
            'file_name'      => $file['name'],
            'file_size'      => $file['size'],
            'mime_type'      => $mime,
            'uploaded_by'    => Auth::id(),
        ]);

        Session::flash('success', 'Report uploaded successfully.');
        redirect(url('doctor/reports'));
    }

    // GET /reports/download/:id
    public function download(int $reportId): void
    {
        Middleware::requireAuth();

        $report = Report::findById($reportId);
        if (!$report) {
            http_response_code(404);
            view('errors.404', ['title' => 'Report Not Found']);
            return;
        }

        // Ownership: patient sees own reports; doctor sees reports they uploaded
        $role = Auth::role();
        $uid  = Auth::id();
        $allowed = false;

        if ($role === 'patient' && $report['patient_id'] == $uid) {
            $allowed = true;
        } elseif ($role === 'doctor') {
            $doc = Doctor::findByUserId($uid);
            if ($doc && $report['uploaded_by'] == $uid) $allowed = true;
        } elseif ($role === 'admin') {
            $allowed = true;
        }

        if (!$allowed) {
            http_response_code(403);
            view('errors.403', ['title' => 'Access Denied']);
            return;
        }

        $path = $report['file_path'];
        if (!file_exists($path)) {
            http_response_code(404);
            view('errors.404', ['title' => 'File Not Found']);
            return;
        }

        // Serve the file
        header('Content-Type: ' . ($report['mime_type'] ?: 'application/octet-stream'));
        header('Content-Disposition: attachment; filename="' . addslashes($report['file_name']) . '"');
        header('Content-Length: ' . filesize($path));
        header('Cache-Control: private, no-cache');
        readfile($path);
        exit;
    }

    // POST /reports/delete/:id  (doctor or admin)
    public function delete(int $reportId): void
    {
        Middleware::requireAuth();
        CSRF::verifyOrFail();

        $report = Report::findById($reportId);
        if (!$report) { redirect(url('reports')); return; }

        $role = Auth::role();
        $uid  = Auth::id();

        $canDelete = ($role === 'admin') ||
                     ($role === 'doctor' && $report['uploaded_by'] == $uid);

        if (!$canDelete) {
            Session::flash('error', 'You cannot delete this report.');
            redirect($role === 'doctor' ? url('doctor/reports') : url('reports'));
            return;
        }

        // Remove file from disk
        if (file_exists($report['file_path'])) {
            @unlink($report['file_path']);
        }

        Report::delete($reportId);
        Session::flash('success', 'Report deleted.');
        redirect($role === 'doctor' ? url('doctor/reports') : url('reports'));
    }
}
