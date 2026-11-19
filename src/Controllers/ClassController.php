<?php
declare(strict_types=1);
namespace App\Controllers;

use App\Models\GymClass;
use App\Models\Trainer;
use App\Models\Booking;
use App\Services\AuthGuard;
use App\Services\ClassValidator;
use App\Services\Csrf;
use App\Support\Flash;
use App\Support\View;
use PDOException;

final class ClassController {
    private const BASE = '/online-gym/public';

    public function index(): void {
        $user   = AuthGuard::user();
        $flash  = Flash::get();
        $errors = Flash::errors();
        $old    = Flash::old();

        $editId  = (int)($_GET['edit'] ?? 0);
        $editing = $editId > 0 ? GymClass::find($editId) : null;

        $search  = isset($_GET['q']) ? trim((string)$_GET['q']) : null;
        $classes = GymClass::all($search);

        // Check booked classes for current member
        $userBookedClassIds = [];
        if ($user && $user['role'] === 'member') {
            $userBookings = Booking::forMember((int)$user['user_id']);
            foreach ($userBookings as $ub) {
                if ($ub['status'] !== 'cancelled') {
                    $userBookedClassIds[] = (int)$ub['class_id'];
                }
            }
        }

        View::render('classes/index', [
            'title'              => 'Fitness Classes – BraVaa Gym',
            'baseUrl'            => self::BASE,
            'flash'              => $flash,
            'errors'             => $errors,
            'old'                => $old,
            'user'               => $user,
            'editing'            => $editing,
            'classes'            => $classes,
            'trainers'           => Trainer::listForSelect(),
            'totalClasses'       => count($classes),
            'searchQuery'        => $search ?? '',
            'userBookedClassIds' => $userBookedClassIds,
        ]);
    }

    public function save(): void {
        AuthGuard::requireRole(['admin', 'trainer'], '/classes');

        if (($_SERVER['REQUEST_METHOD'] ?? 'GET') !== 'POST') {
            header('Location: ' . self::BASE . '/classes', true, 303);
            exit;
        }

        Csrf::validateOrAbort(self::BASE . '/classes');

        $user    = AuthGuard::user();
        $isAdmin = $user && $user['role'] === 'admin';
        $id      = (int)($_POST['id'] ?? 0);

        // Trainers cannot change the assigned coach on existing classes — restore existing trainer_id from DB
        if (!$isAdmin && $id > 0) {
            $existing = GymClass::find($id);
            if ($existing) {
                $_POST['trainer_id'] = (string)$existing['trainer_id'];
            }
        }

        [$data, $errors] = ClassValidator::validate($_POST, $isAdmin);

        if ($errors) {
            Flash::setErrors($errors);
            Flash::setOld($_POST);
            header('Location: ' . self::BASE . '/classes' . ($id > 0 ? "?edit={$id}" : ''), true, 303);
            exit;
        }

        try {
            if ($id > 0) {
                // Admins update all 4 fields; trainers update name/schedule/capacity only
                $changed = $isAdmin
                    ? GymClass::update($id, $data)
                    : GymClass::updateWithoutTrainer($id, $data);

                Flash::set(
                    $changed ? 'success' : 'info',
                    $changed ? 'Fitness class updated successfully.' : 'No changes were detected.'
                );
            } else {
                $newId = GymClass::create($data);
                Flash::set('success', "New class #{$newId} (\"{$data['name']}\") scheduled successfully!");
            }
        } catch (PDOException $e) {
            Flash::set('error', 'Database error: ' . $e->getMessage());
        }

        header('Location: ' . self::BASE . '/classes', true, 303);
        exit;
    }

    public function delete(): void {
        // ONLY admins may delete classes — trainers are blocked
        AuthGuard::requireRole(['admin'], '/classes');

        if (($_SERVER['REQUEST_METHOD'] ?? 'GET') !== 'POST') {
            http_response_code(405);
            exit('POST request required');
        }

        Csrf::validateOrAbort(self::BASE . '/classes');

        $id = (int)($_POST['id'] ?? 0);
        try {
            $removed = $id > 0 ? GymClass::delete($id) : false;
            Flash::set(
                $removed ? 'success' : 'error',
                $removed ? 'Fitness class deleted successfully.' : 'Class not found.'
            );
        } catch (PDOException $e) {
            Flash::set('error', 'Cannot delete class: active bookings may be linked. Please cancel bookings first.');
        }

        header('Location: ' . self::BASE . '/classes', true, 303);
        exit;
    }
}
