<?php
class Message
{
    /** All threads for a user (patient or doctor). */
    public static function threadsForUser(int $userId, string $role): array
    {
        if ($role === 'patient') {
            return Database::query(
                "SELECT t.*,
                        du.name AS other_name,
                        doc.specialization,
                        (SELECT body FROM messages m WHERE m.thread_id = t.id
                         ORDER BY m.created_at DESC LIMIT 1) AS last_msg,
                        (SELECT created_at FROM messages m WHERE m.thread_id = t.id
                         ORDER BY m.created_at DESC LIMIT 1) AS last_at,
                        (SELECT COUNT(*) FROM messages m
                         WHERE m.thread_id = t.id AND m.is_read = 0
                           AND m.sender_id != ?) AS unread
                   FROM message_threads t
                   JOIN doctors doc ON doc.id = t.doctor_id
                   JOIN users   du  ON du.id  = doc.user_id
                  WHERE t.patient_id = ?
                  ORDER BY last_at DESC",
                [$userId, $userId]
            );
        }

        // doctor
        $doc = Database::queryOne(
            "SELECT id FROM doctors WHERE user_id = ?", [$userId]
        );
        if (!$doc) return [];

        return Database::query(
            "SELECT t.*,
                    pu.name AS other_name,
                    (SELECT body FROM messages m WHERE m.thread_id = t.id
                     ORDER BY m.created_at DESC LIMIT 1) AS last_msg,
                    (SELECT created_at FROM messages m WHERE m.thread_id = t.id
                     ORDER BY m.created_at DESC LIMIT 1) AS last_at,
                    (SELECT COUNT(*) FROM messages m
                     WHERE m.thread_id = t.id AND m.is_read = 0
                       AND m.sender_id != ?) AS unread
               FROM message_threads t
               JOIN users pu ON pu.id = t.patient_id
              WHERE t.doctor_id = ?
              ORDER BY last_at DESC",
            [$userId, $doc['id']]
        );
    }

    /** Single thread with its messages. */
    public static function thread(int $threadId): ?array
    {
        return Database::queryOne(
            "SELECT t.*,
                    pu.name AS patient_name,
                    du.name AS doctor_name,
                    doc.specialization
               FROM message_threads t
               JOIN users pu ON pu.id = t.patient_id
               JOIN doctors doc ON doc.id = t.doctor_id
               JOIN users   du  ON du.id  = doc.user_id
              WHERE t.id = ?",
            [$threadId]
        );
    }

    /** Messages in a thread, oldest first. */
    public static function inThread(int $threadId): array
    {
        return Database::query(
            "SELECT m.*, u.name AS sender_name, u.role AS sender_role
               FROM messages m
               JOIN users u ON u.id = m.sender_id
              WHERE m.thread_id = ?
              ORDER BY m.created_at ASC",
            [$threadId]
        );
    }

    /** Mark all messages in a thread as read (except ones sent by this user). */
    public static function markRead(int $threadId, int $readerId): void
    {
        Database::execute(
            "UPDATE messages SET is_read = 1
              WHERE thread_id = ? AND sender_id != ? AND is_read = 0",
            [$threadId, $readerId]
        );
    }

    /** Unread message count for a user across all their threads. */
    public static function unreadCount(int $userId): int
    {
        $row = Database::queryOne(
            "SELECT COUNT(*) AS n
               FROM messages m
               JOIN message_threads t ON t.id = m.thread_id
              WHERE m.is_read = 0
                AND m.sender_id != ?
                AND (t.patient_id = ? OR t.doctor_id IN
                     (SELECT id FROM doctors WHERE user_id = ?))",
            [$userId, $userId, $userId]
        );
        return (int)($row['n'] ?? 0);
    }

    /** Create a new thread. */
    public static function createThread(int $patientId, int $doctorId, string $subject): int
    {
        Database::execute(
            "INSERT INTO message_threads (patient_id, doctor_id, subject) VALUES (?,?,?)",
            [$patientId, $doctorId, $subject]
        );
        return Database::lastInsertId();
    }

    /** Post a message into a thread. */
    public static function send(int $threadId, int $senderId, string $body): void
    {
        Database::execute(
            "INSERT INTO messages (thread_id, sender_id, body) VALUES (?,?,?)",
            [$threadId, $senderId, $body]
        );
    }
}
