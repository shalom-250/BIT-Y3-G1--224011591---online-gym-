<?php
declare(strict_types=1);
namespace App\Services;

use App\Support\Flash;
use App\Support\View;

final class Csrf {
    private const SESSION_KEY = 'csrf_token';

    private static function boot(): void {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
    }

    public static function token(): string {
        self::boot();
        if (empty($_SESSION[self::SESSION_KEY])) {
            $_SESSION[self::SESSION_KEY] = bin2hex(random_bytes(32));
        }
        return $_SESSION[self::SESSION_KEY];
    }

    public static function field(): string {
        $token = self::token();
        return '<input type="hidden" name="csrf_token" value="' . View::e($token) . '">';
    }

    public static function verify(?string $token = null): bool {
        self::boot();
        $stored = $_SESSION[self::SESSION_KEY] ?? '';
        if ($token === null) {
            $token = $_POST['csrf_token'] ?? $_SERVER['HTTP_X_CSRF_TOKEN'] ?? '';
        }
        if ($stored === '' || $token === '') {
            return false;
        }
        return hash_equals($stored, $token);
    }

    public static function validateOrAbort(string $redirectPath = '/'): void {
        if (!self::verify()) {
            $isAjax = (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest')
                      || str_contains($_SERVER['HTTP_ACCEPT'] ?? '', 'application/json');

            if ($isAjax) {
                View::json([
                    'success' => false,
                    'message' => 'CSRF verification failed. Please refresh the page and try again.'
                ], 403);
            }

            Flash::set('error', 'Security check failed (invalid CSRF token). Please try again.');
            header('Location: ' . $redirectPath, true, 303);
            exit;
        }
    }
}
