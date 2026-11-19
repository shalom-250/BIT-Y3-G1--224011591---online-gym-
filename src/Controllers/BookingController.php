<?php
declare(strict_types=1);
namespace App\Controllers;

use App\Models\Booking;
use App\Models\GymClass;
use App\Models\Trainer;
use App\Models\User;
use App\Services\AuthGuard;
use App\Services\BookingValidator;
use App\Services\Csrf;
use App\Support\Flash;
use App\Support\View;
use PDOException;

final class BookingController {
    private const BASE = '/online-gym/public';

    public function index(): void {
        AuthGuard::requireLogin('/login');
        $user   = AuthGuard::user();
        $flash  = Flash::get();
        $errors = Flash::errors();
        $old    = Flash::old();

        $editId  = (int)($_GET['edit'] ?? 0);
        $editing = $editId > 0 ? Booking::find($editId) : null;

        // ── Scope bookings and KPI counts by role ─────────────────────
        // Admin   → all bookings in the system
        // Trainer → only bookings for classes they teach (via trainers.user_id)
        // Member  → only their own bookings
        if ($user['role'] === 'admin') {
            $bookings = Booking::all();
            $counts   = Booking::counts();
        } elseif ($user['role'] === 'trainer') {
            $trainerProfile = Trainer::findByUserId((int)$user['user_id']);
            if ($trainerProfile) {
                $trainerId = (int)$trainerProfile['trainer_id'];
                $bookings  = Booking::forTrainerClasses($trainerId);
                $counts    = Booking::countsForTrainer($trainerId);
            } else {
                $bookings = [];
                $counts   = ['confirmed' => 0, 'attended' => 0, 'cancelled' => 0, 'total' => 0];
            }
        } else {
            $memberId = (int)$user['user_id'];
            $bookings = Booking::forMember($memberId);
            $counts   = Booking::countsForMember($memberId);
        }

        $allClasses = GymClass::all();
        $allUsers   = User::all();

        View::render('bookings/index', [
            'title'         => 'Class Bookings – BraVaa Gym',
            'baseUrl'       => self::BASE,
            'flash'         => $flash,
            'errors'        => $errors,
            'old'           => $old,
            'user'          => $user,
            'editing'       => $editing,
            'bookings'      => $bookings,
            'classes'       => $allClasses,
            'members'       => array_filter($allUsers, fn($u) => $u['role'] === 'member'),
            'counts'        => $counts,
            'totalBookings' => count($bookings),
        ]);
    }

    public function book(): void {
        AuthGuard::requireLogin('/login');
        $user = AuthGuard::user();

        if (($_SERVER['REQUEST_METHOD'] ?? 'GET') !== 'POST') {
            header('Location: ' . self::BASE . '/classes', true, 303);
            exit;
        }

        Csrf::validateOrAbort(self::BASE . '/classes');

        $classId  = (int)($_POST['class_id'] ?? 0);
        $memberId = ($user['role'] === 'admin' && !empty($_POST['member_id']))
                    ? (int)$_POST['member_id']
                    : (int)$user['user_id'];

        $gymClass = GymClass::find($classId);
        if (!$gymClass) {
            Flash::set('error', 'The selected fitness class was not found.');
            header('Location: ' . self::BASE . '/classes', true, 303);
            exit;
        }

        if ($gymClass['remaining_spots'] <= 0) {
            Flash::set('error', "Class \"{$gymClass['name']}\" is fully booked. Please choose another session.");
            header('Location: ' . self::BASE . '/classes', true, 303);
            exit;
        }

        try {
            $newId = Booking::create($classId, $memberId, 'confirmed');
            Flash::set('success', "Booking confirmed! You have reserved a spot for \"{$gymClass['name']}\".");
        } catch (\RuntimeException $e) {
            Flash::set('warning', $e->getMessage());
        } catch (PDOException $e) {
            Flash::set('error', 'Booking failed: ' . $e->getMessage());
        }

        header('Location: ' . self::BASE . '/bookings', true, 303);
        exit;
    }

    public function save(): void {
        AuthGuard::requireRole('admin', '/bookings');

        if (($_SERVER['REQUEST_METHOD'] ?? 'GET') !== 'POST') {
            header('Location: ' . self::BASE . '/bookings', true, 303);
            exit;
        }

        Csrf::validateOrAbort(self::BASE . '/bookings');

        $id = (int)($_POST['id'] ?? 0);
        [$data, $errors] = BookingValidator::validate($_POST, $id);

        if ($errors) {
            Flash::setErrors($errors);
            Flash::setOld($_POST);
            header('Location: ' . self::BASE . '/bookings' . ($id > 0 ? "?edit={$id}" : ''), true, 303);
            exit;
        }

        try {
            if ($id > 0) {
                $changed = Booking::update($id, $data);
                Flash::set(
                    $changed ? 'success' : 'info',
                    $changed ? 'Booking details updated successfully.' : 'No changes were made.'
                );
            } else {
                $newId = Booking::create($data['class_id'], $data['member_id'], $data['status']);
                Flash::set('success', "Booking #{$newId} recorded successfully.");
            }
        } catch (PDOException $e) {
            Flash::set('error', 'Database error: ' . $e->getMessage());
        }

        header('Location: ' . self::BASE . '/bookings', true, 303);
        exit;
    }

    public function updateStatus(): void {
        AuthGuard::requireLogin('/login');
        $user = AuthGuard::user();

        if (($_SERVER['REQUEST_METHOD'] ?? 'GET') !== 'POST') {
            http_response_code(405);
            exit('POST request required');
        }

        Csrf::validateOrAbort(self::BASE . '/bookings');

        $id     = (int)($_POST['id'] ?? 0);
        $status = trim((string)($_POST['status'] ?? ''));

        if (!in_array($status, ['confirmed', 'attended', 'cancelled'], true)) {
            Flash::set('error', 'Invalid booking status.');
            header('Location: ' . self::BASE . '/bookings', true, 303);
            exit;
        }

        $booking = Booking::find($id);
        if (!$booking) {
            Flash::set('error', 'Booking record not found.');
            header('Location: ' . self::BASE . '/bookings', true, 303);
            exit;
        }

        // ── Permission rules ───────────────────────────────────────────
        // Admin   → can set any status on any booking
        // Trainer → can mark 'attended' ONLY on bookings for their own classes; CANNOT cancel
        // Member  → can cancel ONLY their own confirmed bookings; no other status changes
        if ($user['role'] === 'member') {
            if ((int)$booking['member_id'] !== (int)$user['user_id']) {
                Flash::set('error', 'You are not authorized to modify this booking.');
                header('Location: ' . self::BASE . '/bookings', true, 303);
                exit;
            }
            if ($status !== 'cancelled') {
                Flash::set('error', 'Members can only cancel their own bookings.');
                header('Location: ' . self::BASE . '/bookings', true, 303);
                exit;
            }
        } elseif ($user['role'] === 'trainer') {
            // Trainer can only mark 'attended' — cancellation is admin-only
            if ($status === 'cancelled') {
                Flash::set('error', 'Trainers cannot cancel bookings. Only Admin can cancel reservations.');
                header('Location: ' . self::BASE . '/bookings', true, 303);
                exit;
            }
            // Verify this booking belongs to one of the trainer's own classes
            $trainerProfile = Trainer::findByUserId((int)$user['user_id']);
            if (!$trainerProfile) {
                Flash::set('error', 'No trainer profile found for your account. Please contact Admin.');
                header('Location: ' . self::BASE . '/bookings', true, 303);
                exit;
            }
            // Load the class to confirm it belongs to this trainer
            $gymClass = GymClass::find((int)$booking['class_id']);
            if (!$gymClass || (int)$gymClass['trainer_id'] !== (int)$trainerProfile['trainer_id']) {
                Flash::set('error', 'You can only update attendance for bookings in your own classes.');
                header('Location: ' . self::BASE . '/bookings', true, 303);
                exit;
            }
        }
        // Admin: no restrictions — falls through

        try {
            Booking::updateStatus($id, $status);
            Flash::set('success', "Booking #{$id} marked as " . ucfirst($status) . '.');
        } catch (PDOException $e) {
            Flash::set('error', 'Failed to update booking status: ' . $e->getMessage());
        }

        header('Location: ' . self::BASE . '/bookings', true, 303);
        exit;
    }

    public function delete(): void {
        // Only admin can permanently delete bookings
        AuthGuard::requireRole(['admin'], '/bookings');

        if (($_SERVER['REQUEST_METHOD'] ?? 'GET') !== 'POST') {
            http_response_code(405);
            exit('POST request required');
        }

        Csrf::validateOrAbort(self::BASE . '/bookings');

        $id      = (int)($_POST['id'] ?? 0);
        $booking = Booking::find($id);

        if (!$booking) {
            Flash::set('error', 'Booking record not found.');
            header('Location: ' . self::BASE . '/bookings', true, 303);
            exit;
        }

        try {
            Booking::delete($id);
            Flash::set('success', 'Booking reservation cancelled and removed.');
        } catch (PDOException $e) {
            Flash::set('error', 'Failed to remove booking: ' . $e->getMessage());
        }

        header('Location: ' . self::BASE . '/bookings', true, 303);
        exit;
    }
}
