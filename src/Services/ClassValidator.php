<?php
declare(strict_types=1);
namespace App\Services;

use App\Models\Trainer;

final class ClassValidator {
    public static function validate(array $input): array {
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

        // Trainer ID
        $trainerId = (int)($input['trainer_id'] ?? 0);
        if ($trainerId <= 0) {
            $errors['trainer_id'] = 'Please select a valid trainer.';
        } else {
            $trainer = Trainer::find($trainerId);
            if (!$trainer) {
                $errors['trainer_id'] = 'Selected trainer does not exist.';
            }
        }
        $data['trainer_id'] = $trainerId;

        // Schedule time
        $timeStr = trim((string)($input['schedule_time'] ?? ''));
        if ($timeStr === '') {
            $errors['schedule_time'] = 'Schedule date and time is required.';
        } else {
            $dt = \DateTime::createFromFormat('Y-m-d\TH:i', $timeStr) ?: \DateTime::createFromFormat('Y-m-d H:i:s', $timeStr);
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
