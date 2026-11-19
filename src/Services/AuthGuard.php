<?php
declare(strict_types=1);
namespace App\Services;

use App\Support\Flash;

final class AuthGuard {
    private const BASE = '/online-gym/public';

    private static function boot(): void {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
    }

    public static function check(): bool {
        self::boot();
        return !empty($_SESSION['user_id']);
    }

    public static function user(): ?array {
        self::boot();
        if (!self::check()) {
            return null;
        }
        return [
            'user_id'  => (int)$_SESSION['user_id'],
            'username' => (string)($_SESSION['username'] ?? ''),
            'email'    => (string)($_SESSION['email'] ?? ''),
            'role'     => (string)($_SESSION['role'] ?? 'member'),
        ];
    }

    public static function id(): ?int {
        self::boot();
        return isset($_SESSION['user_id']) ? (int)$_SESSION['user_id'] : null;
    }

    public static function role(): ?string {
        self::boot();
        return $_SESSION['role'] ?? null;
    }

    public static function isAdmin(): bool {
        return self::role() === 'admin';
    }

    public static function isTrainer(): bool {
        return self::role() === 'trainer' || self::role() === 'admin';
    }

    public static function isMember(): bool {
        return self::role() === 'member';
    }

    public static function login(array $user): void {
        self::boot();
        // Prevent session fixation attack
        session_regenerate_id(true);
        $_SESSION['user_id']  = (int)$user['user_id'];
        $_SESSION['username'] = (string)$user['username'];
        $_SESSION['email']    = (string)$user['email'];
        $_SESSION['role']     = (string)$user['role'];
    }

    public static function logout(): void {
        self::boot();
        $_SESSION = [];
        if (ini_get('session.use_cookies')) {
            $params = session_get_cookie_params();
            setcookie(session_name(), '', time() - 42000,
                $params['path'], $params['domain'],
                $params['secure'], $params['httponly']
            );
        }
        session_destroy();
    }

    public static function requireLogin(string $redirectPath = '/login'): void {
        if (!self::check()) {
            Flash::set('info', 'Please sign in to access this page.');
            header('Location: ' . self::BASE . $redirectPath, true, 303);
            exit;
        }
    }

    public static function requireRole(string|array $roles, string $redirectPath = '/'): void {
        self::requireLogin();
        $allowed = is_array($roles) ? $roles : [$roles];
        $currentRole = self::role();

        if (!in_array($currentRole, $allowed, true)) {
            Flash::set('error', 'Access denied. You do not have permission to view that resource.');
            header('Location: ' . self::BASE . $redirectPath, true, 303);
            exit;
        }
    }
}
