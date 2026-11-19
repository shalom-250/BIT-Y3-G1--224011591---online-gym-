<?php
declare(strict_types=1);
namespace App\Models;

use App\Config\Database;
use PDO;

final class User {
    public static function find(int $id): ?array {
        $stmt = Database::pdo()->prepare(
            'SELECT user_id, username, email, role, created_at FROM users WHERE user_id = :id LIMIT 1'
        );
        $stmt->execute(['id' => $id]);
        $row = $stmt->fetch();
        return $row ?: null;
    }

    public static function findForAuth(string $usernameOrEmail): ?array {
        $stmt = Database::pdo()->prepare(
            'SELECT user_id, username, email, password_hash, role, created_at 
             FROM users 
             WHERE username = :identifier OR email = :identifier 
             LIMIT 1'
        );
        $stmt->execute(['identifier' => $usernameOrEmail]);
        $row = $stmt->fetch();
        return $row ?: null;
    }

    public static function findByEmail(string $email): ?array {
        $stmt = Database::pdo()->prepare(
            'SELECT user_id, username, email, role FROM users WHERE email = :email LIMIT 1'
        );
        $stmt->execute(['email' => $email]);
        $row = $stmt->fetch();
        return $row ?: null;
    }

    public static function findByUsername(string $username): ?array {
        $stmt = Database::pdo()->prepare(
            'SELECT user_id, username, email, role FROM users WHERE username = :username LIMIT 1'
        );
        $stmt->execute(['username' => $username]);
        $row = $stmt->fetch();
        return $row ?: null;
    }

    public static function all(): array {
        $stmt = Database::pdo()->query(
            'SELECT user_id, username, email, role, created_at 
             FROM users 
             ORDER BY user_id DESC'
        );
        return $stmt->fetchAll();
    }

    public static function create(array $data): int {
        $stmt = Database::pdo()->prepare(
            'INSERT INTO users (username, email, password_hash, role) 
             VALUES (:username, :email, :password_hash, :role)'
        );
        $stmt->execute([
            'username'      => $data['username'],
            'email'         => $data['email'],
            'password_hash' => $data['password_hash'],
            'role'          => $data['role'] ?? 'member',
        ]);
        return (int)Database::pdo()->lastInsertId();
    }

    public static function update(int $id, array $data): bool {
        $fields = [
            'username = :username',
            'email = :email',
            'role = :role',
        ];
        $params = [
            'id'       => $id,
            'username' => $data['username'],
            'email'    => $data['email'],
            'role'     => $data['role'],
        ];

        if (!empty($data['password_hash'])) {
            $fields[] = 'password_hash = :password_hash';
            $params['password_hash'] = $data['password_hash'];
        }

        $sql = 'UPDATE users SET ' . implode(', ', $fields) . ' WHERE user_id = :id';
        $stmt = Database::pdo()->prepare($sql);
        $stmt->execute($params);
        return $stmt->rowCount() > 0;
    }

    public static function delete(int $id): bool {
        $stmt = Database::pdo()->prepare('DELETE FROM users WHERE user_id = :id');
        $stmt->execute(['id' => $id]);
        return $stmt->rowCount() > 0;
    }

    public static function counts(): array {
        $stmt = Database::pdo()->query(
            'SELECT role, COUNT(*) AS count FROM users GROUP BY role'
        );
        $counts = ['admin' => 0, 'trainer' => 0, 'member' => 0, 'total' => 0];
        foreach ($stmt->fetchAll() as $row) {
            $counts[$row['role']] = (int)$row['count'];
            $counts['total'] += (int)$row['count'];
        }
        return $counts;
    }
}
