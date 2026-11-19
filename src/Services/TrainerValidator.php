<?php
declare(strict_types=1);
namespace App\Services;

final class TrainerValidator {
    public static function validate(array $input): array {
        $errors = [];
        $data   = [];

        // Full name
        $name = trim((string)($input['full_name'] ?? ''));
        if ($name === '') {
            $errors['full_name'] = 'Full name is required.';
        } elseif (strlen($name) < 2 || strlen($name) > 100) {
            $errors['full_name'] = 'Full name must be between 2 and 100 characters.';
        }
        $data['full_name'] = $name;

        // Specialization
        $spec = trim((string)($input['specialization'] ?? ''));
        if ($spec === '') {
            $errors['specialization'] = 'Specialization is required.';
        } elseif (strlen($spec) < 2 || strlen($spec) > 100) {
            $errors['specialization'] = 'Specialization must be between 2 and 100 characters.';
        }
        $data['specialization'] = $spec;

        // Phone
        $phone = trim((string)($input['phone'] ?? ''));
        if ($phone === '') {
            $errors['phone'] = 'Phone number is required.';
        } elseif (!preg_match('/^[0-9+() \-\.]{7,25}$/', $phone)) {
            $errors['phone'] = 'Please provide a valid phone number (e.g. +250 788 123 456).';
        }
        $data['phone'] = $phone;

        return [$data, $errors];
    }
}
