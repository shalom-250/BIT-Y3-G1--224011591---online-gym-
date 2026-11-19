<?php
declare(strict_types=1);
namespace App\Models;

use App\Config\Database;
use PDO;

final class Booking {
    public static function find(int $id): ?array {
        $stmt = Database::pdo()->prepare(
            'SELECT b.booking_id, b.class_id, b.member_id, b.booked_at, b.status,
                    c.name AS class_name, c.schedule_time, c.capacity,
                    t.full_name AS trainer_name, t.specialization AS trainer_specialization,
                    u.username AS member_username, u.email AS member_email
             FROM bookings b
             INNER JOIN classes c ON c.class_id = b.class_id
             INNER JOIN trainers t ON t.trainer_id = c.trainer_id
             INNER JOIN users u ON u.user_id = b.member_id
             WHERE b.booking_id = :id
             LIMIT 1'
        );
        $stmt->execute(['id' => $id]);
        $row = $stmt->fetch();
        return $row ?: null;
    }

    public static function all(): array {
        $stmt = Database::pdo()->query(
            'SELECT b.booking_id, b.class_id, b.member_id, b.booked_at, b.status,
                    c.name AS class_name, c.schedule_time, c.capacity,
                    t.full_name AS trainer_name, t.specialization AS trainer_specialization,
                    u.username AS member_username, u.email AS member_email
             FROM bookings b
             INNER JOIN classes c ON c.class_id = b.class_id
             INNER JOIN trainers t ON t.trainer_id = c.trainer_id
             INNER JOIN users u ON u.user_id = b.member_id
             ORDER BY b.booking_id DESC'
        );
        return $stmt->fetchAll();
    }

    public static function forMember(int $memberId): array {
        $stmt = Database::pdo()->prepare(
            'SELECT b.booking_id, b.class_id, b.member_id, b.booked_at, b.status,
                    c.name AS class_name, c.schedule_time, c.capacity,
                    t.full_name AS trainer_name, t.specialization AS trainer_specialization
             FROM bookings b
             INNER JOIN classes c ON c.class_id = b.class_id
             INNER JOIN trainers t ON t.trainer_id = c.trainer_id
             WHERE b.member_id = :member_id
             ORDER BY c.schedule_time DESC'
        );
        $stmt->execute(['member_id' => $memberId]);
        return $stmt->fetchAll();
    }

    public static function exists(int $classId, int $memberId): bool {
        $stmt = Database::pdo()->prepare(
            'SELECT booking_id FROM bookings WHERE class_id = :class_id AND member_id = :member_id LIMIT 1'
        );
        $stmt->execute(['class_id' => $classId, 'member_id' => $memberId]);
        return (bool)$stmt->fetch();
    }

    public static function create(int $classId, int $memberId, string $status = 'confirmed'): int {
        // Double check if already booked
        $existing = Database::pdo()->prepare(
            'SELECT booking_id, status FROM bookings WHERE class_id = :class_id AND member_id = :member_id'
        );
        $existing->execute(['class_id' => $classId, 'member_id' => $memberId]);
        $row = $existing->fetch();

        if ($row) {
            // Re-activate if was cancelled
            if ($row['status'] === 'cancelled') {
                $upd = Database::pdo()->prepare(
                    'UPDATE bookings SET status = :status, booked_at = CURRENT_TIMESTAMP WHERE booking_id = :id'
                );
                $upd->execute(['status' => $status, 'id' => $row['booking_id']]);
                return (int)$row['booking_id'];
            }
            throw new \RuntimeException('You have already booked this class.');
        }

        $stmt = Database::pdo()->prepare(
            'INSERT INTO bookings (class_id, member_id, status) VALUES (:class_id, :member_id, :status)'
        );
        $stmt->execute([
            'class_id'  => $classId,
            'member_id' => $memberId,
            'status'    => $status,
        ]);
        return (int)Database::pdo()->lastInsertId();
    }

    public static function updateStatus(int $id, string $status): bool {
        $stmt = Database::pdo()->prepare(
            'UPDATE bookings SET status = :status WHERE booking_id = :id'
        );
        $stmt->execute(['status' => $status, 'id' => $id]);
        return $stmt->rowCount() > 0;
    }

    public static function update(int $id, array $data): bool {
        $stmt = Database::pdo()->prepare(
            'UPDATE bookings 
             SET class_id = :class_id, member_id = :member_id, status = :status 
             WHERE booking_id = :id'
        );
        $stmt->execute([
            'id'        => $id,
            'class_id'  => (int)$data['class_id'],
            'member_id' => (int)$data['member_id'],
            'status'    => $data['status'],
        ]);
        return $stmt->rowCount() > 0;
    }

    public static function delete(int $id): bool {
        $stmt = Database::pdo()->prepare('DELETE FROM bookings WHERE booking_id = :id');
        $stmt->execute(['id' => $id]);
        return $stmt->rowCount() > 0;
    }

    public static function counts(): array {
        $stmt = Database::pdo()->query(
            'SELECT status, COUNT(*) AS count FROM bookings GROUP BY status'
        );
        $counts = ['confirmed' => 0, 'attended' => 0, 'cancelled' => 0, 'total' => 0];
        foreach ($stmt->fetchAll() as $row) {
            $counts[$row['status']] = (int)$row['count'];
            $counts['total'] += (int)$row['count'];
        }
        return $counts;
    }
}
