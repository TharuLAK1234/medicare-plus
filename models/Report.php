<?php
class Report
{
    /** All reports for a patient (newest first). */
    public static function forPatient(int $patientUserId): array
    {
        return Database::query(
            "SELECT r.*,
                    u.name  AS uploader_name,
                    d.name  AS doctor_name,
                    doc.specialization,
                    a.ref_no AS appt_ref
               FROM reports r
               JOIN users u ON u.id = r.uploaded_by
          LEFT JOIN doctors doc ON doc.id = r.doctor_id
          LEFT JOIN users   d   ON d.id   = doc.user_id
          LEFT JOIN appointments a ON a.id = r.appointment_id
              WHERE r.patient_id = ?
              ORDER BY r.created_at DESC",
            [$patientUserId]
        );
    }

    /** All reports uploaded by / for a doctor's patients. */
    public static function forDoctor(int $doctorId): array
    {
        return Database::query(
            "SELECT r.*,
                    pu.name AS patient_name,
                    a.ref_no AS appt_ref,
                    a.appt_date
               FROM reports r
               JOIN users pu ON pu.id = r.patient_id
          LEFT JOIN appointments a ON a.id = r.appointment_id
              WHERE r.doctor_id = ?
              ORDER BY r.created_at DESC",
            [$doctorId]
        );
    }

    /** Single report by ID with ownership check. */
    public static function findById(int $id): ?array
    {
        return Database::queryOne(
            "SELECT r.*, u.name AS patient_name
               FROM reports r
               JOIN users u ON u.id = r.patient_id
              WHERE r.id = ?",
            [$id]
        );
    }

    public static function create(array $data): int
    {
        Database::execute(
            "INSERT INTO reports
               (patient_id, doctor_id, appointment_id, title, type,
                file_path, file_name, file_size, mime_type, uploaded_by)
             VALUES (?,?,?,?,?,?,?,?,?,?)",
            [
                $data['patient_id'],
                $data['doctor_id']      ?? null,
                $data['appointment_id'] ?? null,
                $data['title'],
                $data['type'],
                $data['file_path'],
                $data['file_name'],
                $data['file_size']      ?? null,
                $data['mime_type']      ?? null,
                $data['uploaded_by'],
            ]
        );
        return Database::lastInsertId();
    }

    public static function delete(int $id): void
    {
        Database::execute("DELETE FROM reports WHERE id = ?", [$id]);
    }
}
