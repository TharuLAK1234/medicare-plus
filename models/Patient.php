<?php
class Patient
{
    /**
     * Insert a patients row linked to an existing users row.
     * All health fields are optional — patients can complete their profile later.
     */
    public static function create(int $userId, array $data): void
    {
        Database::execute(
            "INSERT INTO patients
               (user_id, dob, gender, blood_group, allergies, address,
                emergency_contact_name, emergency_contact_phone)
             VALUES (?, ?, ?, ?, ?, ?, ?, ?)",
            [
                $userId,
                $data['dob']         ?: null,
                $data['gender']      ?: null,
                $data['blood_group'] ?: null,
                $data['allergies']   ?: null,
                $data['address']     ?: null,
                $data['ec_name']     ?: null,
                $data['ec_phone']    ?: null,
            ]
        );
    }

    /** Return a patient's combined users + patients record. */
    public static function findByUserId(int $userId): ?array
    {
        return Database::queryOne(
            "SELECT p.*, u.name, u.email, u.phone, u.status, u.created_at
               FROM patients p
               JOIN users u ON u.id = p.user_id
              WHERE p.user_id = ?",
            [$userId]
        );
    }

    /** Count of appointments for this patient. */
    public static function appointmentCount(int $userId): int
    {
        $row = Database::queryOne(
            "SELECT COUNT(*) AS cnt FROM appointments WHERE patient_id = ?",
            [$userId]
        );
        return (int)($row['cnt'] ?? 0);
    }
}
