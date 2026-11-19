<?php
declare(strict_types=1);
namespace App\Models;

use App\Config\Database;
use PDO;

final class GymClass {
    public static function find(int $id): ?array {
        $stmt = Database::pdo()->prepare(
            'SELECT c.class_id, c.name, c.trainer_id, c.schedule_time, c.capacity,
                    t.full_name AS trainer_name, t.specialization AS trainer_specialization, t.phone AS trainer_phone,
                    COUNT(CASE WHEN b.status != "cancelled" THEN b.booking_id END) AS booked_count
             FROM classes c
             INNER JOIN trainers t ON t.trainer_id = c.trainer_id
             LEFT JOIN bookings b ON b.class_id = c.class_id
             WHERE c.class_id = :id
             GROUP BY c.class_id
             LIMIT 1'
        );
        $stmt->execute(['id' => $id]);
        $row = $stmt->fetch();
        if ($row) {
            $row['remaining_spots'] = max(0, (int)$row['capacity'] - (int)$row['booked_count']);
            $row['is_full'] = $row['remaining_spots'] <= 0;
        }
        return $row ?: null;
    }

    public static function all(?string $search = null): array {
        $sql = 'SELECT c.class_id, c.name, c.trainer_id, c.schedule_time, c.capacity,
                       t.full_name AS trainer_name, t.specialization AS trainer_specialization,
                       COUNT(CASE WHEN b.status != "cancelled" THEN b.booking_id END) AS booked_count
                FROM classes c
                INNER JOIN trainers t ON t.trainer_id = c.trainer_id
                LEFT JOIN bookings b ON b.class_id = c.class_id';

        $params = [];
        if ($search !== null && trim($search) !== '') {
            $sql .= ' WHERE (c.name LIKE :q1 OR t.full_name LIKE :q2 OR t.specialization LIKE :q3)';
            $qVal = '%' . trim($search) . '%';
            $params = [
                'q1' => $qVal,
                'q2' => $qVal,
                'q3' => $qVal,
            ];
        }

        $sql .= ' GROUP BY c.class_id ORDER BY c.schedule_time ASC';

        $stmt = Database::pdo()->prepare($sql);
        $stmt->execute($params);
        $rows = $stmt->fetchAll();

        foreach ($rows as &$row) {
            $row['remaining_spots'] = max(0, (int)$row['capacity'] - (int)$row['booked_count']);
            $row['is_full'] = $row['remaining_spots'] <= 0;
        }
        unset($row);

        return $rows;
    }

    public static function listUpcoming(int $limit = 6): array {
        $stmt = Database::pdo()->prepare(
            'SELECT c.class_id, c.name, c.trainer_id, c.schedule_time, c.capacity,
                    t.full_name AS trainer_name, t.specialization AS trainer_specialization,
                    COUNT(CASE WHEN b.status != "cancelled" THEN b.booking_id END) AS booked_count
             FROM classes c
             INNER JOIN trainers t ON t.trainer_id = c.trainer_id
             LEFT JOIN bookings b ON b.class_id = c.class_id
             GROUP BY c.class_id
             ORDER BY c.schedule_time ASC
             LIMIT :limit'
        );
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->execute();
        $rows = $stmt->fetchAll();

        foreach ($rows as &$row) {
            $row['remaining_spots'] = max(0, (int)$row['capacity'] - (int)$row['booked_count']);
            $row['is_full'] = $row['remaining_spots'] <= 0;
        }
        unset($row);

        return $rows;
    }

    public static function create(array $data): int {
        $stmt = Database::pdo()->prepare(
            'INSERT INTO classes (name, trainer_id, schedule_time, capacity) 
             VALUES (:name, :trainer_id, :schedule_time, :capacity)'
        );
        $stmt->execute([
            'name'          => $data['name'],
            'trainer_id'    => (int)$data['trainer_id'],
            'schedule_time' => $data['schedule_time'],
            'capacity'      => (int)$data['capacity'],
        ]);
        return (int)Database::pdo()->lastInsertId();
    }

    public static function update(int $id, array $data): bool {
        $stmt = Database::pdo()->prepare(
            'UPDATE classes 
             SET name = :name, trainer_id = :trainer_id, schedule_time = :schedule_time, capacity = :capacity 
             WHERE class_id = :id'
        );
        $stmt->execute([
            'id'            => $id,
            'name'          => $data['name'],
            'trainer_id'    => (int)$data['trainer_id'],
            'schedule_time' => $data['schedule_time'],
            'capacity'      => (int)$data['capacity'],
        ]);
        return $stmt->rowCount() > 0;
    }

    public static function delete(int $id): bool {
        $stmt = Database::pdo()->prepare('DELETE FROM classes WHERE class_id = :id');
        $stmt->execute(['id' => $id]);
        return $stmt->rowCount() > 0;
    }

    public static function bookedCount(int $classId): int {
        $stmt = Database::pdo()->prepare(
            'SELECT COUNT(*) FROM bookings WHERE class_id = :id AND status != "cancelled"'
        );
        $stmt->execute(['id' => $classId]);
        return (int)$stmt->fetchColumn();
    }

    public static function hasCapacity(int $classId): bool {
        $class = self::find($classId);
        if (!$class) return false;
        return $class['remaining_spots'] > 0;
    }

    public static function count(): int {
        return (int)Database::pdo()->query('SELECT COUNT(*) FROM classes')->fetchColumn();
    }
}
