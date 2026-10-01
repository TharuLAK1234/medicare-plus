<?php
class Appointment
{
    // ── Queries ───────────────────────────────────────────────────────────

    /** Full appointment row with doctor + patient names and service. */
    private static function baseSelect(): string
    {
        return "SELECT a.*,
                       p_user.name  AS patient_name,
                       p_user.email AS patient_email,
                       p_user.phone AS patient_phone,
                       pat.gender   AS patient_gender,
                       pat.blood_group,
                       d_user.name  AS doctor_name,
                       doc.specialization, doc.fee, doc.photo AS doctor_photo,
                       svc.name     AS service_name
                  FROM appointments a
                  JOIN users    p_user ON p_user.id  = a.patient_id
             LEFT JOIN patients pat    ON pat.user_id = a.patient_id
                  JOIN doctors  doc    ON doc.id      = a.doctor_id
                  JOIN users    d_user ON d_user.id   = doc.user_id
                  JOIN services svc    ON svc.id      = doc.service_id";
    }

    /** Single appointment by ID. */
    public static function findById(int $id): ?array
    {
        return Database::queryOne(self::baseSelect() . " WHERE a.id = ?", [$id]);
    }

    // ── Patient queries ───────────────────────────────────────────────────

    /** All appointments for a patient, newest first. */
    public static function forPatient(int $userId, int $limit = 50): array
    {
        return Database::query(
            self::baseSelect() . " WHERE a.patient_id = ?
             ORDER BY a.appt_date DESC, a.appt_time DESC LIMIT ?",
            [$userId, $limit]
        );
    }

    /** Upcoming (pending/confirmed) appointments for a patient. */
    public static function upcomingForPatient(int $userId, int $limit = 5): array
    {
        return Database::query(
            self::baseSelect() . " WHERE a.patient_id = ?
              AND a.status IN ('pending','confirmed')
              AND a.appt_date >= CURDATE()
             ORDER BY a.appt_date ASC, a.appt_time ASC LIMIT ?",
            [$userId, $limit]
        );
    }

    /** Next single upcoming appointment for a patient. */
    public static function nextForPatient(int $userId): ?array
    {
        return Database::queryOne(
            self::baseSelect() . " WHERE a.patient_id = ?
              AND a.status IN ('pending','confirmed')
              AND a.appt_date >= CURDATE()
             ORDER BY a.appt_date ASC, a.appt_time ASC LIMIT 1",
            [$userId]
        );
    }

    /** Count appointments by status for a patient. */
    public static function countsByStatus(int $userId, string $role = 'patient'): array
    {
        $col = $role === 'doctor' ? 'doc.id' : 'a.patient_id';
        if ($role === 'doctor') {
            $rows = Database::query(
                "SELECT a.status, COUNT(*) AS n
                   FROM appointments a
                   JOIN doctors doc ON doc.id = a.doctor_id
                  WHERE doc.user_id = ?
                  GROUP BY a.status",
                [$userId]
            );
        } else {
            $rows = Database::query(
                "SELECT status, COUNT(*) AS n
                   FROM appointments
                  WHERE patient_id = ?
                  GROUP BY status",
                [$userId]
            );
        }
        $out = ['pending' => 0, 'confirmed' => 0, 'completed' => 0, 'cancelled' => 0];
        foreach ($rows as $r) $out[$r['status']] = (int)$r['n'];
        $out['upcoming'] = $out['pending'] + $out['confirmed'];
        $out['total']    = array_sum(array_slice($out, 0, 4));
        return $out;
    }

    // ── Doctor queries ────────────────────────────────────────────────────

    /** All appointments for a doctor (by doctors.user_id). */
    public static function forDoctor(int $doctorUserId, int $limit = 50): array
    {
        return Database::query(
            self::baseSelect() . " WHERE doc.user_id = ?
             ORDER BY a.appt_date DESC, a.appt_time DESC LIMIT ?",
            [$doctorUserId, $limit]
        );
    }

    /** Today's appointments for a doctor (pending + confirmed). */
    public static function todayForDoctor(int $doctorUserId): array
    {
        return Database::query(
            self::baseSelect() . " WHERE doc.user_id = ?
              AND a.appt_date = CURDATE()
              AND a.status IN ('pending','confirmed')
             ORDER BY a.appt_time ASC",
            [$doctorUserId]
        );
    }

    /** Pending appointments awaiting doctor confirmation. */
    public static function pendingForDoctor(int $doctorUserId, int $limit = 10): array
    {
        return Database::query(
            self::baseSelect() . " WHERE doc.user_id = ?
              AND a.status = 'pending'
              AND a.appt_date >= CURDATE()
             ORDER BY a.appt_date ASC, a.appt_time ASC LIMIT ?",
            [$doctorUserId, $limit]
        );
    }

    /** Count of unique patients who have had appointments with this doctor. */
    public static function uniquePatientCount(int $doctorUserId): int
    {
        $row = Database::queryOne(
            "SELECT COUNT(DISTINCT a.patient_id) AS n
               FROM appointments a
               JOIN doctors doc ON doc.id = a.doctor_id
              WHERE doc.user_id = ? AND a.status = 'completed'",
            [$doctorUserId]
        );
        return (int)($row['n'] ?? 0);
    }

    // ── Admin queries ─────────────────────────────────────────────────────

    /** Recent appointments across all users. */
    public static function recent(int $limit = 15): array
    {
        return Database::query(
            self::baseSelect() . " ORDER BY a.created_at DESC LIMIT ?",
            [$limit]
        );
    }

    /** All appointments (optionally filtered). */
    public static function all(array $filters = [], int $limit = 50): array
    {
        $where  = ['1=1'];
        $params = [];
        if (!empty($filters['status'])) {
            $where[]  = 'a.status = ?';
            $params[] = $filters['status'];
        }
        if (!empty($filters['date'])) {
            $where[]  = 'a.appt_date = ?';
            $params[] = $filters['date'];
        }
        $params[] = $limit;
        return Database::query(
            self::baseSelect()
            . ' WHERE ' . implode(' AND ', $where)
            . ' ORDER BY a.appt_date DESC, a.appt_time DESC LIMIT ?',
            $params
        );
    }

    /** Global counts grouped by status. */
    public static function globalCounts(): array
    {
        $rows = Database::query(
            "SELECT status, COUNT(*) AS n FROM appointments GROUP BY status"
        );
        $out = ['pending' => 0, 'confirmed' => 0, 'completed' => 0, 'cancelled' => 0];
        foreach ($rows as $r) $out[$r['status']] = (int)$r['n'];
        $out['total'] = array_sum($out);
        return $out;
    }

    /** Total revenue from completed appointments. */
    public static function totalRevenue(): float
    {
        $row = Database::queryOne(
            "SELECT COALESCE(SUM(doc.fee), 0) AS total
               FROM appointments a
               JOIN doctors doc ON doc.id = a.doctor_id
              WHERE a.status = 'completed'"
        );
        return (float)($row['total'] ?? 0);
    }

    /** Appointments created in the last N days, grouped by date. */
    public static function dailyCounts(int $days = 7): array
    {
        return Database::query(
            "SELECT DATE(created_at) AS day, COUNT(*) AS n
               FROM appointments
              WHERE created_at >= DATE_SUB(CURDATE(), INTERVAL ? DAY)
              GROUP BY day ORDER BY day ASC",
            [$days]
        );
    }

    // ── Mutations ─────────────────────────────────────────────────────────

    /**
     * Cancel an appointment.
     * Ownership check: patient can only cancel their own; doctor their own.
     */
    public static function cancel(int $id, int $actingUserId, string $role): bool
    {
        $appt = self::findById($id);
        if (!$appt) return false;
        if ($appt['status'] === 'completed' || $appt['status'] === 'cancelled') return false;

        // Ownership
        if ($role === 'patient' && (int)$appt['patient_id'] !== $actingUserId) return false;

        Database::execute(
            "UPDATE appointments SET status = 'cancelled' WHERE id = ?", [$id]
        );
        return true;
    }

    /**
     * Doctor confirms a pending appointment.
     */
    public static function confirm(int $id, int $doctorUserId): bool
    {
        $appt = self::findById($id);
        if (!$appt || $appt['status'] !== 'pending') return false;

        // Verify this doctor owns the appointment
        $doc = Database::queryOne(
            "SELECT id FROM doctors WHERE user_id = ?", [$doctorUserId]
        );
        if (!$doc || (int)$appt['doctor_id'] !== (int)$doc['id']) return false;

        Database::execute(
            "UPDATE appointments SET status = 'confirmed' WHERE id = ?", [$id]
        );
        return true;
    }

    /**
     * Doctor marks an appointment as completed.
     */
    public static function complete(int $id, int $doctorUserId, string $notes = ''): bool
    {
        $appt = self::findById($id);
        if (!$appt || $appt['status'] !== 'confirmed') return false;

        $doc = Database::queryOne(
            "SELECT id FROM doctors WHERE user_id = ?", [$doctorUserId]
        );
        if (!$doc || (int)$appt['doctor_id'] !== (int)$doc['id']) return false;

        Database::execute(
            "UPDATE appointments SET status = 'completed', notes = ? WHERE id = ?",
            [$notes ?: null, $id]
        );
        return true;
    }
}
