<?php
declare(strict_types=1);
namespace App\Services;

use App\Models\User;

final class UserValidator {
    public static function validate(array $input, int $ignoreId = 0): array {
        $errors = [];
        $data   = [];

        // Username
        $username = trim((string)($input['username'] ?? ''));
        if ($username === '') {
            $errors['username'] = 'Username is required.';
        } elseif (strlen($username) < 3 || strlen($username) > 50) {
            $errors['username'] = 'Username must be between 3 and 50 characters.';
        } elseif (!preg_match('/^[a-zA-Z0-9_.-]+$/', $username)) {
            $errors['username'] = 'Username may only contain letters, numbers, dots, hyphens and underscores.';
        } else {
            $existing = User::findByUsername($username);
            if ($existing && (int)$existing['user_id'] !== $ignoreId) {
                $errors['username'] = 'This username is already taken.';
            }
        }
        $data['username'] = $username;

        // Email
        $email = trim((string)($input['email'] ?? ''));
        if ($email === '') {
            $errors['email'] = 'Email is required.';
        } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $errors['email'] = 'Please enter a valid email address.';
        } elseif (strlen($email) > 100) {
            $errors['email'] = 'Email cannot exceed 100 characters.';
        } else {
            $existing = User::findByEmail($email);
            if ($existing && (int)$existing['user_id'] !== $ignoreId) {
                $errors['email'] = 'This email is already registered.';
            }
        }
        $data['email'] = $email;

        // Password
        $password = (string)($input['password'] ?? '');
        if ($ignoreId === 0 && $password === '') {
            $errors['password'] = 'Password is required.';
        } elseif ($password !== '' && strlen($password) < 6) {
            $errors['password'] = 'Password must be at least 6 characters.';
        }
        if ($password !== '') {
            $data['password_hash'] = password_hash($password, PASSWORD_BCRYPT);
        }

        // Role
        $role = trim((string)($input['role'] ?? 'member'));
        if (!in_array($role, ['admin', 'trainer', 'member'], true)) {
            $errors['role'] = 'Invalid role specified.';
        }
        $data['role'] = $role;

        return [$data, $errors];
    }
}
