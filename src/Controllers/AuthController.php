<?php
declare(strict_types=1);
namespace App\Controllers;

use App\Models\User;
use App\Services\AuthGuard;
use App\Services\Csrf;
use App\Services\UserValidator;
use App\Support\Flash;
use App\Support\View;

final class AuthController {
    private const BASE = '/online-gym/public';

    public function login(): void {
        if (AuthGuard::check()) {
            header('Location: ' . self::BASE . '/', true, 303);
            exit;
        }

        $flash  = Flash::get();
        $errors = Flash::errors();
        $old    = Flash::old();

        View::render('auth/login', [
            'title'   => 'Sign In — BraVaa Fitness',
            'baseUrl' => self::BASE,
            'flash'   => $flash,
            'errors'  => $errors,
            'old'     => $old,
            'user'    => null,
        ]);
    }

    public function processLogin(): void {
        if (($_SERVER['REQUEST_METHOD'] ?? 'GET') !== 'POST') {
            header('Location: ' . self::BASE . '/login', true, 303);
            exit;
        }

        Csrf::validateOrAbort(self::BASE . '/login');

        $identifier = trim((string)($_POST['identifier'] ?? ''));
        $password   = (string)($_POST['password'] ?? '');

        if ($identifier === '' || $password === '') {
            Flash::setErrors(['login' => 'Please provide both username/email and password.']);
            Flash::setOld($_POST);
            header('Location: ' . self::BASE . '/login', true, 303);
            exit;
        }

        $user = User::findForAuth($identifier);

        if (!$user || !password_verify($password, $user['password_hash'])) {
            Flash::setErrors(['login' => 'Invalid username/email or password.']);
            Flash::setOld(['identifier' => $identifier]);
            header('Location: ' . self::BASE . '/login', true, 303);
            exit;
        }

        // Login successful
        AuthGuard::login($user);
        Flash::set('success', 'Welcome back, ' . $user['username'] . '! Ready to conquer your workout?');

        // Redirect based on role
        if ($user['role'] === 'admin') {
            header('Location: ' . self::BASE . '/classes', true, 303);
        } elseif ($user['role'] === 'trainer') {
            header('Location: ' . self::BASE . '/classes', true, 303);
        } else {
            header('Location: ' . self::BASE . '/bookings', true, 303);
        }
        exit;
    }

    public function register(): void {
        if (AuthGuard::check()) {
            header('Location: ' . self::BASE . '/', true, 303);
            exit;
        }

        $flash  = Flash::get();
        $errors = Flash::errors();
        $old    = Flash::old();

        View::render('auth/register', [
            'title'   => 'Create an Account — BraVaa Fitness',
            'baseUrl' => self::BASE,
            'flash'   => $flash,
            'errors'  => $errors,
            'old'     => $old,
            'user'    => null,
        ]);
    }

    public function processRegister(): void {
        if (($_SERVER['REQUEST_METHOD'] ?? 'GET') !== 'POST') {
            header('Location: ' . self::BASE . '/register', true, 303);
            exit;
        }

        Csrf::validateOrAbort(self::BASE . '/register');

        // Public registration always assigns member role
        $_POST['role'] = 'member';
        [$data, $errors] = UserValidator::validate($_POST);

        if ($errors) {
            Flash::setErrors($errors);
            Flash::setOld($_POST);
            header('Location: ' . self::BASE . '/register', true, 303);
            exit;
        }

        try {
            $newId = User::create($data);
            $user = User::find($newId);
            if ($user) {
                AuthGuard::login($user);
                Flash::set('success', "Registration successful! Welcome to BraVaa Fitness, {$user['username']}.");
                header('Location: ' . self::BASE . '/classes', true, 303);
                exit;
            }
        } catch (\PDOException $e) {
            Flash::set('error', 'Database error during registration: ' . $e->getMessage());
        }

        header('Location: ' . self::BASE . '/login', true, 303);
        exit;
    }

    public function logout(): void {
        AuthGuard::logout();
        Flash::set('info', 'You have been signed out safely. See you at your next workout!');
        header('Location: ' . self::BASE . '/', true, 303);
        exit;
    }
}
