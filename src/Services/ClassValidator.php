<?php
declare(strict_types=1);
namespace App\Services;

use App\Models\Trainer;

final class ClassValidator {
    /**
     * Validate class form input.
     *
     * @param array $input   Raw POST data.
     * @param bool  $isAdmin When true, trainer_id is validated/required (admin can assign).
     *                       When false, trainer_id is accepted as-is without DB validation
     *                       (the controller already forced it to the existing DB value).
     */
    public static function validate(array $input, bool $isAdmin = true): array {
        $errors = [];
        $data   = [];

        // Name
        $name = trim((string)($input['name'] ?? ''));
        if ($name === '') {
            $errors['name'] = 'Class name is required.';
        } elseif (strlen($name) < 3 || strlen($name) > 100) {
            $errors['name'] = 'Class name must be between 3 and 100 characters.';
        }
        $data['name'] = $name;

        // Trainer ID — only admins can assign/change the trainer
        $trainerId = (int)($input['trainer_id'] ?? 0);
        if ($isAdmin) {
            if ($trainerId <= 0) {
                $errors['trainer_id'] = 'Please select a valid trainer.';
            } else {
                $trainer = Trainer::find($trainerId);
                if (!$trainer) {
                    $errors['trainer_id'] = 'Selected trainer does not exist.';
                }
            }
        }
        $data['trainer_id'] = $trainerId;

        // Schedule time
        $timeStr = trim((string)($input['schedule_time'] ?? ''));
        if ($timeStr === '') {
            $errors['schedule_time'] = 'Schedule date and time is required.';
        } else {
            $dt = \DateTime::createFromFormat('Y-m-d\TH:i', $timeStr)
               ?: \DateTime::createFromFormat('Y-m-d H:i:s', $timeStr);
            if (!$dt) {
                $errors['schedule_time'] = 'Invalid schedule date/time format.';
            } else {
                $data['schedule_time'] = $dt->format('Y-m-d H:i:s');
            }
        }

        // Capacity
        $capacity = (int)($input['capacity'] ?? 0);
        if ($capacity < 1 || $capacity > 200) {
            $errors['capacity'] = 'Capacity must be between 1 and 200 participants.';
        }
        $data['capacity'] = $capacity;

        return [$data, $errors];
    }
}
