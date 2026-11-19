<?php
declare(strict_types=1);

spl_autoload_register(function (string $class): void {
    $prefix = 'App\\';
    if (!str_starts_with($class, $prefix)) return;

    $relative = substr($class, strlen($prefix));
    $relative = str_replace('\\', DIRECTORY_SEPARATOR, $relative);

    $file = dirname(__DIR__) . DIRECTORY_SEPARATOR . $relative . '.php';

    if (is_file($file)) require $file;
});