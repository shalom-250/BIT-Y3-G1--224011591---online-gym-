<?php
declare(strict_types=1);
namespace App\Controllers;

use App\Models\User;
use App\Services\AuthGuard;
use App\Services\Csrf;
use App\Services\UserValidator;
use App\Support\Flash;
use App\Support\View;
use PDOException;

final class UserController {
    private const BASE = '/online-gym/public';

    public function index(): void {
        AuthGuard::requireRole('admin', '/');
        $user   = AuthGuard::user();
        $flash  = Flash::get();
        $errors = Flash::errors();
        $old    = Flash::old();

        $editId  = (int)($_GET['edit'] ?? 0);
        $editing = $editId > 0 ? User::find($editId) : null;

        $users  = User::all();
        $counts = User::counts();

        View::render('users/index', [
            'title'      => 'Manage Users — BraVaa Gym',
            'baseUrl'    => self::BASE,
            'flash'      => $flash,
            'errors'     => $errors,
            'old'        => $old,
            'user'       => $user,
            'editing'    => $editing,
            'users'      => $users,
            'counts'     => $counts,
            'totalUsers' => count($users),
        ]);
    }

    public function save(): void {
        AuthGuard::requireRole('admin', '/users');

        if (($_SERVER['REQUEST_METHOD'] ?? 'GET') !== 'POST') {
            header('Location: ' . self::BASE . '/users', true, 303);
            exit;
        }

        Csrf::validateOrAbort(self::BASE . '/users');

        $id = (int)($_POST['id'] ?? 0);
        [$data, $errors] = UserValidator::validate($_POST, $id);

        if ($errors) {
            Flash::setErrors($errors);
            Flash::setOld($_POST);
            header('Location: ' . self::BASE . '/users' . ($id > 0 ? "?edit={$id}" : ''), true, 303);
            exit;
        }

        try {
            if ($id > 0) {
                $changed = User::update($id, $data);
                Flash::set(
                    $changed ? 'success' : 'info',
                    $changed ? 'User updated successfully.' : 'No changes were detected.'
                );
            } else {
                $newId = User::create($data);
                Flash::set('success', "User #{$newId} ({$data['username']}) created successfully.");
            }
        } catch (PDOException $e) {
            Flash::set('error', 'Database error: ' . $e->getMessage());
        }

        header('Location: ' . self::BASE . '/users', true, 303);
        exit;
    }

    public function delete(): void {
        AuthGuard::requireRole('admin', '/users');
        $currentUser = AuthGuard::user();

        if (($_SERVER['REQUEST_METHOD'] ?? 'GET') !== 'POST') {
            http_response_code(405);
            exit('POST request required');
        }

        Csrf::validateOrAbort(self::BASE . '/users');

        $id = (int)($_POST['id'] ?? 0);

        // Prevent self deletion
        if ($id === (int)$currentUser['user_id']) {
            Flash::set('error', 'You cannot delete your own active administrator account.');
            header('Location: ' . self::BASE . '/users', true, 303);
            exit;
        }

        try {
            $removed = $id > 0 ? User::delete($id) : false;
            Flash::set(
                $removed ? 'success' : 'error',
                $removed ? 'User account deleted successfully.' : 'User not found.'
            );
        } catch (PDOException $e) {
            Flash::set('error', 'Cannot delete user: user has active bookings or related records.');
        }

        header('Location: ' . self::BASE . '/users', true, 303);
        exit;
    }
}
