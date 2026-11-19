<?php
declare(strict_types=1);
namespace App\Controllers;

use App\Models\GymClass;
use App\Models\Booking;
use App\Services\AuthGuard;
use App\Services\Csrf;
use App\Support\View;

final class ApiController {
    public function searchClasses(): void {
        $query   = isset($_GET['q']) ? trim((string)$_GET['q']) : null;
        $classes = GymClass::all($query);

        // Sanitize for JSON response
        $data = array_map(function ($c) {
            return [
                'class_id'               => (int)$c['class_id'],
                'name'                   => (string)$c['name'],
                'trainer_id'             => (int)$c['trainer_id'],
                'trainer_name'           => (string)$c['trainer_name'],
                'trainer_specialization' => (string)$c['trainer_specialization'],
                'schedule_time'          => (string)$c['schedule_time'],
                'formatted_time'         => View::formatDateTime((string)$c['schedule_time']),
                'capacity'               => (int)$c['capacity'],
                'booked_count'           => (int)$c['booked_count'],
                'remaining_spots'        => (int)$c['remaining_spots'],
                'is_full'                => (bool)$c['is_full'],
            ];
        }, $classes);

        View::json([
            'success' => true,
            'count'   => count($data),
            'classes' => $data,
        ]);
    }

    public function classDetails(): void {
        $id = (int)($_GET['id'] ?? 0);
        if ($id <= 0) {
            View::json(['success' => false, 'message' => 'Invalid class ID provided.'], 400);
        }

        $class = GymClass::find($id);
        if (!$class) {
            View::json(['success' => false, 'message' => 'Class not found.'], 404);
        }

        $class['formatted_time'] = View::formatDateTime((string)$class['schedule_time']);
        View::json(['success' => true, 'class' => $class]);
    }

    public function bookAsync(): void {
        if (!AuthGuard::check()) {
            View::json([
                'success' => false,
                'message' => 'Please sign in first to book a fitness class.',
                'require_login' => true
            ], 401);
        }

        // Parse JSON or POST data
        $raw = file_get_contents('php://input');
        $jsonData = json_decode($raw, true) ?: [];
        $data = !empty($jsonData) ? $jsonData : $_POST;

        $token = $data['csrf_token'] ?? $_SERVER['HTTP_X_CSRF_TOKEN'] ?? null;
        if (!Csrf::verify($token)) {
            View::json(['success' => false, 'message' => 'Security token invalid. Please refresh the page.'], 403);
        }

        $classId = (int)($data['class_id'] ?? 0);
        if ($classId <= 0) {
            View::json(['success' => false, 'message' => 'Please select a valid class.'], 400);
        }

        $class = GymClass::find($classId);
        if (!$class) {
            View::json(['success' => false, 'message' => 'Class does not exist.'], 404);
        }

        if ($class['remaining_spots'] <= 0) {
            View::json(['success' => false, 'message' => 'Sorry, this class is full!'], 409);
        }

        $memberId = AuthGuard::id();
        try {
            $bookingId = Booking::create($classId, (int)$memberId, 'confirmed');
            $updatedClass = GymClass::find($classId);

            View::json([
                'success'         => true,
                'booking_id'      => $bookingId,
                'remaining_spots' => $updatedClass['remaining_spots'] ?? 0,
                'is_full'         => $updatedClass['is_full'] ?? false,
                'message'         => "Spot successfully booked for \"{$class['name']}\"!"
            ]);
        } catch (\RuntimeException $e) {
            View::json(['success' => false, 'message' => $e->getMessage()], 409);
        } catch (\PDOException $e) {
            View::json(['success' => false, 'message' => 'Database error: ' . $e->getMessage()], 500);
        }
    }
}
