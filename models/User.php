<?php
class User
{
    public static function findByEmail(string $email): ?array
    {
        return Database::queryOne(
            "SELECT id, name, email, password_hash, role, phone, status
               FROM users WHERE email = ?",
            [$email]
        );
    }

    public static function findById(int $id): ?array
    {
        return Database::queryOne(
            "SELECT id, name, email, role, phone, status, created_at
               FROM users WHERE id = ?",
            [$id]
        );
    }

    public static function emailExists(string $email): bool
    {
        return (bool)Database::queryOne(
            "SELECT id FROM users WHERE email = ?", [$email]
        );
    }

    /**
     * Create a new user record and return the new auto-increment ID.
     * Password is hashed here — callers pass the plaintext.
     */
    public static function create(array $data): int
    {
        return (int)Database::insert(
            "INSERT INTO users (name, email, password_hash, role, phone)
             VALUES (?, ?, ?, ?, ?)",
            [
                $data['name'],
                $data['email'],
                password_hash($data['password'], PASSWORD_BCRYPT, ['cost' => 12]),
                $data['role'] ?? 'patient',
                $data['phone'] ?: null,
            ]
        );
    }

    public static function updateLastActivity(int $userId): void
    {
        Database::execute(
            "UPDATE users SET updated_at = NOW() WHERE id = ?", [$userId]
        );
    }
}
