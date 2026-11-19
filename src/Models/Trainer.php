<?php
declare(strict_types=1);
namespace App\Models;

use App\Config\Database;
use PDO;

final class Trainer {
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
