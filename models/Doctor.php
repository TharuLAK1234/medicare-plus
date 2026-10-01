<?php
class Doctor
{
    /** Doctor profile by their users.id. */
    public static function findByUserId(int $userId): ?array
    {
        return Database::queryOne(
            "SELECT doc.*, u.name, u.email, u.phone, u.status AS user_status,
                    s.name AS service_name, s.slug AS service_slug
               FROM doctors doc
               JOIN users    u ON u.id   = doc.user_id
               JOIN services s ON s.id   = doc.service_id
              WHERE doc.user_id = ?",
            [$userId]
        );
    }

    /** Doctor profile by doctors.id. */
    public static function findById(int $doctorId): ?array
    {
        return Database::queryOne(
            "SELECT doc.*, u.name, u.email, u.phone,
                    s.name AS service_name, s.slug AS service_slug
               FROM doctors doc
               JOIN users    u ON u.id = doc.user_id
               JOIN services s ON s.id = doc.service_id
              WHERE doc.id = ?",
            [$doctorId]
        );
    }

    /** All doctors with their service and average rating. */
    public static function allWithStats(): array
    {
        return Database::query(
            "SELECT doc.*, u.name, u.email, u.status AS user_status,
                    s.name AS service_name,
                    ROUND(AVG(r.stars), 1) AS avg_rating,
                    COUNT(DISTINCT r.id)   AS rating_count,
                    COUNT(DISTINCT a.id)   AS appt_count
               FROM doctors doc
               JOIN users    u   ON u.id   = doc.user_id
               JOIN services s   ON s.id   = doc.service_id
          LEFT JOIN ratings  r   ON r.doctor_id = doc.id AND r.is_approved = 1
          LEFT JOIN appointments a ON a.doctor_id = doc.id AND a.status = 'completed'
              GROUP BY doc.id
              ORDER BY doc.is_featured DESC, avg_rating DESC"
        );
    }

    /** Average rating and count for a doctor. */
    public static function getRating(int $doctorId): array
    {
        $row = Database::queryOne(
            "SELECT ROUND(AVG(stars), 1) AS avg_stars, COUNT(*) AS total
               FROM ratings
              WHERE doctor_id = ? AND is_approved = 1",
            [$doctorId]
        );
        return [
            'avg'   => (float)($row['avg_stars'] ?? 0),
            'count' => (int)($row['total'] ?? 0),
        ];
    }

    /** Recent approved ratings for a doctor. */
    public static function getRecentRatings(int $doctorId, int $limit = 5): array
    {
        return Database::query(
            "SELECT r.*, u.name AS patient_name
               FROM ratings r
               JOIN users u ON u.id = r.patient_id
              WHERE r.doctor_id = ? AND r.is_approved = 1
              ORDER BY r.created_at DESC LIMIT ?",
            [$doctorId, $limit]
        );
    }

    /** Availability slots for a doctor. */
    public static function getAvailability(int $doctorId): array
    {
        return Database::query(
            "SELECT * FROM doctor_availability
              WHERE doctor_id = ?
              ORDER BY FIELD(day_of_week,'Monday','Tuesday','Wednesday',
                             'Thursday','Friday','Saturday','Sunday')",
            [$doctorId]
        );
    }

    /** Unique patients treated by this doctor. */
    public static function getPatients(int $doctorUserId, int $limit = 20): array
    {
        return Database::query(
            "SELECT DISTINCT u.id, u.name, u.email, u.phone,
                    p.gender, p.blood_group, p.dob, p.allergies,
                    COUNT(a.id)  AS visit_count,
                    MAX(a.appt_date) AS last_visit
               FROM appointments a
               JOIN users   u ON u.id   = a.patient_id
          LEFT JOIN patients p ON p.user_id = a.patient_id
               JOIN doctors doc ON doc.id = a.doctor_id
              WHERE doc.user_id = ? AND a.status = 'completed'
              GROUP BY u.id
              ORDER BY last_visit DESC LIMIT ?",
            [$doctorUserId, $limit]
        );
    }
}
