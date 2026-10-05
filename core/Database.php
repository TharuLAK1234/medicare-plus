<?php
/**
 * Database — PDO Singleton
 *
 * WHY singleton pattern: a single PDO instance per request avoids the
 * overhead of repeatedly opening TCP connections to MySQL. Every model
 * calls Database::getInstance() and receives the same PDO object.
 *
 * WHY PDO over mysqli: PDO is database-agnostic, supports named parameters,
 * and provides a uniform exception model (Lockhart, 2015).
 */
class Database
{
    private static ?PDO $instance = null;

    /** Private constructor prevents direct instantiation via `new Database()`. */
    private function __construct() {}

    /**
     * Return the shared PDO connection, creating it on the first call.
     *
     * @throws RuntimeException on connection failure
     */
    public static function getInstance(): PDO
    {
        if (self::$instance === null) {
            self::$instance = self::connect();
        }
        return self::$instance;
    }

    /** Reads credentials from config/database.php and opens the PDO connection. */
    private static function connect(): PDO
    {
        $cfg = require CONFIG_PATH . '/database.php';

        $dsn = sprintf(
            '%s:host=%s;port=%s;dbname=%s;charset=%s',
            $cfg['driver'], $cfg['host'],
            $cfg['port'],   $cfg['database'], $cfg['charset']
        );

        try {
            return new PDO($dsn, $cfg['username'], $cfg['password'], $cfg['options']);
        } catch (PDOException $e) {
            // Log full details privately; expose nothing sensitive to the user
            error_log('[DB] ' . $e->getMessage());
            throw new RuntimeException('Database unavailable. Please try again later.');
        }
    }

    // ── Query helpers ──────────────────────────────────────────────────────

    /**
     * Run a SELECT and return all matching rows.
     *
     * @param  string $sql    Parameterised SQL with :named or ? placeholders
     * @param  array  $params Values to bind — NEVER build SQL by concatenation
     * @return array<int, array<string, mixed>>
     */
    public static function query(string $sql, array $params = []): array
    {
        $stmt = self::getInstance()->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    /**
     * Run a SELECT and return the first row, or null if no match.
     *
     * @return array<string, mixed>|null
     */
    public static function queryOne(string $sql, array $params = []): ?array
    {
        $stmt = self::getInstance()->prepare($sql);
        $stmt->execute($params);
        $row = $stmt->fetch();
        return $row !== false ? $row : null;
    }

    /**
     * Run an INSERT / UPDATE / DELETE and return the number of affected rows.
     */
    public static function execute(string $sql, array $params = []): int
    {
        $stmt = self::getInstance()->prepare($sql);
        $stmt->execute($params);
        return $stmt->rowCount();
    }

    /**
     * Run an INSERT and return the auto-increment ID of the new record.
     */
    public static function insert(string $sql, array $params = []): string
    {
        $stmt = self::getInstance()->prepare($sql);
        $stmt->execute($params);
        return self::getInstance()->lastInsertId();
    }

    /** Return the last auto-increment ID from the current connection. */
    public static function lastInsertId(): int
    {
        return (int)self::getInstance()->lastInsertId();
    }

    /**
     * Execute a callable inside a transaction.
     * If the callable throws, the transaction is rolled back automatically.
     *
     * WHY transactions: when a booking creates both an `appointments` row
     * and checks slot availability, both must succeed atomically (Date, 2004).
     *
     * @throws Throwable re-throws whatever the callback threw
     */
    public static function transaction(callable $callback): void
    {
        $pdo = self::getInstance();
        $pdo->beginTransaction();
        try {
            $callback();
            $pdo->commit();
        } catch (Throwable $e) {
            $pdo->rollBack();
            throw $e;
        }
    }

    // Prevent cloning and unserializing — both would create second instances
    private function __clone() {}
    public function __wakeup(): void
    {
        throw new RuntimeException('Cannot unserialize singleton.');
    }
}
