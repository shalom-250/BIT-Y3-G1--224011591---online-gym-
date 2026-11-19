<?php
declare(strict_types=1);
namespace App\Config;

use PDO;
use PDOException;

final class Database {
    private static ?PDO $pdo = null;

    public static function pdo(): PDO {
        if (self::$pdo instanceof PDO) return self::$pdo;

        $host = getenv('DB_HOST') ?: '127.0.0.1';
        $port = getenv('DB_PORT') ?: '3306';
        $db   = getenv('DB_NAME') ?: 'gym_db';
        $user = getenv('DB_USER') ?: 'root';
        $pass = getenv('DB_PASS') !== false ? (string)getenv('DB_PASS') : '';

        $dsn = "mysql:host={$host};port={$port};dbname={$db};charset=utf8mb4";

        try {
            self::$pdo = new PDO($dsn, $user, $pass, [
                PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES   => false,
            ]);
        } catch (PDOException $e) {
            error_log('DB connection failed: ' . $e->getMessage());
            http_response_code(500);
            echo "<!doctype html><html><head><title>Database Connection Error</title><style>
                body { font-family: system-ui, sans-serif; background: #0f172a; color: #f8fafc; padding: 3rem; display: flex; justify-content: center; align-items: center; min-height: 80vh; }
                .box { background: #1e293b; padding: 2.5rem; border-radius: 1rem; border: 1px solid #334155; max-width: 600px; box-shadow: 0 10px 30px rgba(0,0,0,0.5); }
                h1 { color: #f97316; font-size: 1.5rem; margin-bottom: 1rem; }
                p { line-height: 1.6; color: #cbd5e1; }
                code { background: #0f172a; padding: 0.2rem 0.5rem; border-radius: 4px; color: #38bdf8; font-size: 0.9rem; }
                .tip { margin-top: 1.5rem; padding: 1rem; background: rgba(249,115,22,0.1); border-left: 4px solid #f97316; border-radius: 4px; }
            </style></head><body>
            <div class='box'>
                <h1>Database Connection Notice</h1>
                <p>Could not connect to the MySQL database <code>gym_db</code> on <code>{$host}:{$port}</code>.</p>
                <div class='tip'>
                    <strong>Quick Setup:</strong>
                    <ol style='margin-left: 1.2rem; margin-top: 0.5rem;'>
                        <li>Ensure <strong>Apache</strong> and <strong>MySQL</strong> are started in the XAMPP Control Panel.</li>
                        <li>Import <code>database/setup.sql</code> in phpMyAdmin (or via MySQL CLI).</li>
                    </ol>
                </div>
                <p style='margin-top: 1rem; font-size: 0.85rem; color: #94a3b8;'>Error message: " . htmlspecialchars($e->getMessage(), ENT_QUOTES, 'UTF-8') . "</p>
            </div></body></html>";
            exit;
        }

        return self::$pdo;
    }
}
