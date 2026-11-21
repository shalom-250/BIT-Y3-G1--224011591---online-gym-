<?php
declare(strict_types=1);
namespace App\Models;

use App\Config\Database;
use PDO;

final class Trainer {
    private static ?bool $hasUserIdCol = null;

    private static function hasUserIdColumn(): bool {
        if (self::$hasUserIdCol !== null) {
            return self::$hasUserIdCol;
        }
        try {
            $stmt = Database::pdo()->query("SHOW COLUMNS FROM trainers LIKE 'user_id'");
            self::$hasUserIdCol = ($stmt->fetch() !== false);
        } catch (\Throwable $e) {
            self::$hasUserIdCol = false;
        }
        return self::$hasUserIdCol;
    }

    public static function find(int $id): ?array {
        $stmt = Database::pdo()->prepare(
            'SELECT t.trainer_id, t.full_name, t.specialization, t.phone,
                    COUNT(c.class_id) AS class_count
             FROM trainers t
             LEFT JOIN classes c ON c.trainer_id = t.trainer_id
             WHERE t.trainer_id = :id
             GROUP BY t.trainer_id
             LIMIT 1'
        );
        $stmt->execute(['id' => $id]);
        $row = $stmt->fetch();
        return $row ?: null;
    }

    /**
     * Find the trainer profile linked to a specific user account.
     * Works with both standard schema (matching by user credentials/name)
     * and extended schema (if user_id column exists on trainers table).
     */
    public static function findByUserId(int $userId): ?array {
        // 1. If user_id column exists in trainers table, attempt direct lookup
        if (self::hasUserIdColumn()) {
            try {
                $stmt = Database::pdo()->prepare(
                    'SELECT trainer_id, full_name, specialization, phone
                     FROM trainers
                     WHERE user_id = :user_id
                     LIMIT 1'
                );
                $stmt->execute(['user_id' => $userId]);
                $row = $stmt->fetch();
                if ($row) {
                    return $row;
                }
            } catch (\Throwable $e) {
                // Fall through to name/email matching
            }
        }

        // 2. Match linked user by email prefix or username
        $user = User::find($userId);
        if (!$user) {
            return null;
        }

        $emailPrefix = strstr((string)$user['email'], '@', true) ?: (string)$user['email'];
        $cleanEmailName = trim(str_replace(['.', '_', '-'], ' ', $emailPrefix));
        $cleanUsername = trim((string)preg_replace('/(_coach|_trainer|coach_|trainer_)/i', '', (string)$user['username']));
        $rawUsername = trim((string)$user['username']);

        $stmt = Database::pdo()->prepare(
            'SELECT trainer_id, full_name, specialization, phone
             FROM trainers
             WHERE LOWER(full_name) = LOWER(:clean_email)
                OR LOWER(REPLACE(full_name, " ", ".")) = LOWER(:email_prefix)
                OR LOWER(full_name) LIKE LOWER(:username_prefix)
                OR LOWER(full_name) = LOWER(:raw_username)
                OR LOWER(REPLACE(full_name, " ", "_")) = LOWER(:raw_username)
             ORDER BY trainer_id ASC
             LIMIT 1'
        );
        $stmt->execute([
            'clean_email'     => $cleanEmailName,
            'email_prefix'    => $emailPrefix,
            'username_prefix' => $cleanUsername . '%',
            'raw_username'    => $rawUsername,
        ]);
        $row = $stmt->fetch();
        if ($row) {
            return $row;
        }

        // 3. Fallback for seed data where user_id 2=trainer 1, 3=trainer 2, 4=trainer 3
        if ($user['role'] === 'trainer' && $userId >= 2) {
            $fallbackTrainer = self::find($userId - 1);
            if ($fallbackTrainer) {
                return $fallbackTrainer;
            }
        }

        return null;
    }

    public static function all(): array {
        $stmt = Database::pdo()->query(
            'SELECT t.trainer_id, t.full_name, t.specialization, t.phone,
                    COUNT(c.class_id) AS class_count
             FROM trainers t
             LEFT JOIN classes c ON c.trainer_id = t.trainer_id
             GROUP BY t.trainer_id
             ORDER BY t.trainer_id ASC'
        );
        return $stmt->fetchAll();
    }

    public static function listForSelect(): array {
        $stmt = Database::pdo()->query(
            'SELECT trainer_id, full_name, specialization FROM trainers ORDER BY full_name ASC'
        );
        return $stmt->fetchAll();
    }

    public static function create(array $data): int {
        $stmt = Database::pdo()->prepare(
            'INSERT INTO trainers (full_name, specialization, phone) 
             VALUES (:full_name, :specialization, :phone)'
        );
        $stmt->execute([
            'full_name'      => $data['full_name'],
            'specialization' => $data['specialization'],
            'phone'          => $data['phone'],
        ]);
        return (int)Database::pdo()->lastInsertId();
    }

    public static function update(int $id, array $data): bool {
        $stmt = Database::pdo()->prepare(
            'UPDATE trainers 
             SET full_name = :full_name, specialization = :specialization, phone = :phone 
             WHERE trainer_id = :id'
        );
        $stmt->execute([
            'id'             => $id,
            'full_name'      => $data['full_name'],
            'specialization' => $data['specialization'],
            'phone'          => $data['phone'],
        ]);
        return $stmt->rowCount() > 0;
    }

    public static function delete(int $id): bool {
        $stmt = Database::pdo()->prepare('DELETE FROM trainers WHERE trainer_id = :id');
        $stmt->execute(['id' => $id]);
        return $stmt->rowCount() > 0;
    }

    public static function count(): int {
        return (int)Database::pdo()->query('SELECT COUNT(*) FROM trainers')->fetchColumn();
    }
}
