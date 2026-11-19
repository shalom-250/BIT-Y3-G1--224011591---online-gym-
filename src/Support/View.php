<?php
declare(strict_types=1);
namespace App\Support;

final class View {
    public static function render(string $template, array $data = []): void {
        extract($data, EXTR_SKIP);
        require dirname(__DIR__) . '/Views/layouts/header.php';
        require dirname(__DIR__) . '/Views/' . $template . '.php';
        require dirname(__DIR__) . '/Views/layouts/footer.php';
    }

    public static function renderStandalone(string $template, array $data = []): void {
        extract($data, EXTR_SKIP);
        require dirname(__DIR__) . '/Views/' . $template . '.php';
    }

    public static function e(mixed $v): string {
        return htmlspecialchars((string)($v ?? ''), ENT_QUOTES, 'UTF-8');
    }

    public static function json(mixed $data, int $statusCode = 200): void {
        http_response_code($statusCode);
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode($data, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
        exit;
    }

    public static function formatDateTime(string $datetime): string {
        try {
            $dt = new \DateTime($datetime);
            return $dt->format('M d, Y · h:i A');
        } catch (\Exception) {
            return $datetime;
        }
    }
}
