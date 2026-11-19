<?php
declare(strict_types=1);
namespace App\Controllers;

use App\Models\Trainer;
use App\Services\AuthGuard;
use App\Services\Csrf;
use App\Services\TrainerValidator;
use App\Support\Flash;
use App\Support\View;
use PDOException;

final class TrainerController {
    private const BASE = '/online-gym/public';

    public function index(): void {
        $user   = AuthGuard::user();
        $flash  = Flash::get();
        $errors = Flash::errors();
        $old    = Flash::old();

        $editId  = (int)($_GET['edit'] ?? 0);
        $editing = $editId > 0 ? Trainer::find($editId) : null;

        $trainers = Trainer::all();

        View::render('trainers/index', [
            'title'         => 'Expert Coaches & Trainers — BraVaa Gym',
            'baseUrl'       => self::BASE,
            'flash'         => $flash,
            'errors'        => $errors,
            'old'           => $old,
            'user'          => $user,
            'editing'       => $editing,
            'trainers'      => $trainers,
            'totalTrainers' => count($trainers),
        ]);
    }

    public function save(): void {
        AuthGuard::requireRole('admin', '/trainers');

        if (($_SERVER['REQUEST_METHOD'] ?? 'GET') !== 'POST') {
            header('Location: ' . self::BASE . '/trainers', true, 303);
            exit;
        }

        Csrf::validateOrAbort(self::BASE . '/trainers');

        $id = (int)($_POST['id'] ?? 0);
        [$data, $errors] = TrainerValidator::validate($_POST);

        if ($errors) {
            Flash::setErrors($errors);
            Flash::setOld($_POST);
            header('Location: ' . self::BASE . '/trainers' . ($id > 0 ? "?edit={$id}" : ''), true, 303);
            exit;
        }

        try {
            if ($id > 0) {
                $changed = Trainer::update($id, $data);
                Flash::set(
                    $changed ? 'success' : 'info',
                    $changed ? 'Trainer profile updated successfully.' : 'No changes were detected.'
                );
            } else {
                $newId = Trainer::create($data);
                Flash::set('success', "Trainer #{$newId} (\"{$data['full_name']}\") added to the roster.");
            }
        } catch (PDOException $e) {
            Flash::set('error', 'Database error: ' . $e->getMessage());
        }

        header('Location: ' . self::BASE . '/trainers', true, 303);
        exit;
    }

    public function delete(): void {
        AuthGuard::requireRole('admin', '/trainers');

        if (($_SERVER['REQUEST_METHOD'] ?? 'GET') !== 'POST') {
            http_response_code(405);
            exit('POST request required');
        }

        Csrf::validateOrAbort(self::BASE . '/trainers');

        $id = (int)($_POST['id'] ?? 0);
        try {
            $removed = $id > 0 ? Trainer::delete($id) : false;
            Flash::set(
                $removed ? 'success' : 'error',
                $removed ? 'Trainer removed successfully.' : 'Trainer not found.'
            );
        } catch (PDOException $e) {
            Flash::set('error', 'Cannot delete trainer: classes are assigned to this coach. Please reassign or delete the classes first.');
        }

        header('Location: ' . self::BASE . '/trainers', true, 303);
        exit;
    }
}
