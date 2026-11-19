<?php
declare(strict_types=1);
namespace App\Services;

use App\Models\GymClass;
use App\Models\User;

final class BookingValidator {
    public static function validate(array $input, int $ignoreBookingId = 0): array {
        $errors = [];
        $data   = [];

        // Class ID
        $classId = (int)($input['class_id'] ?? 0);
        if ($classId <= 0) {
            $errors['class_id'] = 'Please select a valid fitness class.';
        } else {
            $gymClass = GymClass::find($classId);
            if (!$gymClass) {
                $errors['class_id'] = 'Selected class was not found.';
            } elseif ($ignoreBookingId === 0 && $gymClass['remaining_spots'] <= 0) {
                $errors['class_id'] = 'This class has reached full capacity.';
            }
        }
        $data['class_id'] = $classId;

        // Member ID
        $memberId = (int)($input['member_id'] ?? 0);
        if ($memberId <= 0) {
            $errors['member_id'] = 'Member ID is required.';
        } else {
            $user = User::find($memberId);
            if (!$user) {
                $errors['member_id'] = 'Member user account does not exist.';
            }
        }
        $data['member_id'] = $memberId;

        // Status
        $status = trim((string)($input['status'] ?? 'confirmed'));
        if (!in_array($status, ['confirmed', 'attended', 'cancelled'], true)) {
            $errors['status'] = 'Invalid booking status.';
        }
        $data['status'] = $status;

        return [$data, $errors];
    }
}
