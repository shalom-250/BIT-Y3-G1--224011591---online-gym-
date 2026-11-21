<?php
declare(strict_types=1);

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require dirname(__DIR__) . '/src/Support/autoload.php';

use App\Controllers\HomeController;
use App\Controllers\AuthController;
use App\Controllers\ClassController;
use App\Controllers\TrainerController;
use App\Controllers\BookingController;
use App\Controllers\UserController;
use App\Controllers\ApiController;

/* Base path detection — handles both /online-gym/public and direct root */
$scriptName = $_SERVER['SCRIPT_NAME'] ?? '';
$scriptDir  = rtrim(str_replace('\\', '/', dirname($scriptName)), '/');
$BASE       = $scriptDir === '/' ? '' : $scriptDir;

$uri  = $_SERVER['REQUEST_URI'] ?? '/';
$path = parse_url($uri, PHP_URL_PATH) ?: '/';

if ($BASE !== '' && str_starts_with($path, $BASE)) {
    $path = substr($path, strlen($BASE));
}

$path = rtrim($path, '/');
if ($path === '' || $path === '/index.php') {
    $path = '/';
}

$routes = [
    /* ── Home & Landing ── */
    '/'                   => [HomeController::class,    'index'],

    /* ── Authentication ── */
    '/login'              => [AuthController::class,    'login'],
    '/login/process'      => [AuthController::class,    'processLogin'],
    '/register'           => [AuthController::class,    'register'],
    '/register/process'   => [AuthController::class,    'processRegister'],
    '/logout'             => [AuthController::class,    'logout'],

    /* ── Classes (Domain Entity 1) ── */
    '/classes'            => [ClassController::class,   'index'],
    '/classes/save'       => [ClassController::class,   'save'],
    '/classes/delete'     => [ClassController::class,   'delete'],

    /* ── Trainers (Domain Entity 2) ── */
    '/trainers'           => [TrainerController::class, 'index'],
    '/trainers/save'      => [TrainerController::class, 'save'],
    '/trainers/delete'    => [TrainerController::class, 'delete'],

    /* ── Bookings (Domain Entity 3) ── */
    '/bookings'           => [BookingController::class, 'index'],
    '/bookings/book'      => [BookingController::class, 'book'],
    '/bookings/save'      => [BookingController::class, 'save'],
    '/bookings/status'    => [BookingController::class, 'updateStatus'],
    '/bookings/delete'    => [BookingController::class, 'delete'],

    /* ── Users (System Entity 4) ── */
    '/users'              => [UserController::class,    'index'],
    '/users/save'         => [UserController::class,    'save'],
    '/users/delete'       => [UserController::class,    'delete'],

    /* ── Async JSON API Endpoints (Fetch API Feature) ── */
    '/api/classes'        => [ApiController::class,     'searchClasses'],
    '/api/classes/details'=> [ApiController::class,     'classDetails'],
    '/api/bookings/create'=> [ApiController::class,     'bookAsync'],
];

if (!isset($routes[$path])) {
    http_response_code(404);
    echo '<!doctype html><html><head><title>404 Not Found</title><style>
        body { font-family: system-ui, sans-serif; background: #0f172a; color: #f8fafc; display: flex; align-items: center; justify-content: center; min-height: 100vh; margin: 0; text-align: center; }
        .card { background: #1e293b; padding: 3rem; border-radius: 1rem; border: 1px solid #334155; }
        h1 { color: #f97316; font-size: 3rem; margin: 0 0 1rem; }
        a { color: #38bdf8; text-decoration: none; font-weight: 600; }
        a:hover { text-decoration: underline; }
    </style></head><body>
    <div class="card">
        <h1>404</h1>
        <p>The requested page <code>' . htmlspecialchars($path, ENT_QUOTES, 'UTF-8') . '</code> was not found.</p>
        <p style="margin-top: 1.5rem;"><a href="' . htmlspecialchars($BASE ?: '/', ENT_QUOTES, 'UTF-8') . '">&larr; Return to BraGym Fitness</a></p>
    </div>
    </body></html>';
    exit;
}

[$class, $action] = $routes[$path];
(new $class())->$action();
