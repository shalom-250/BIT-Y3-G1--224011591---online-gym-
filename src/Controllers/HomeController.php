<?php
declare(strict_types=1);
namespace App\Controllers;

use App\Models\GymClass;
use App\Models\Trainer;
use App\Models\Booking;
use App\Services\AuthGuard;
use App\Support\Flash;
use App\Support\View;

final class HomeController {
    private const BASE = '/online-gym/public';

    public function index(): void {
        $flash = Flash::get();
        $user  = AuthGuard::user();

        // Get upcoming classes and top trainers
        $upcomingClasses = GymClass::listUpcoming(6);
        $trainers        = Trainer::all();
        $classCount      = GymClass::count();
        $trainerCount    = Trainer::count();
        $bookingCounts   = Booking::counts();

        // If logged in as member, check which classes they have already booked
        $userBookedClassIds = [];
        if ($user && $user['role'] === 'member') {
            $userBookings = Booking::forMember((int)$user['user_id']);
            foreach ($userBookings as $ub) {
                if ($ub['status'] !== 'cancelled') {
                    $userBookedClassIds[] = (int)$ub['class_id'];
                }
            }
        }

        View::render('home/index', [
            'title'              => 'BraVaa Fitness — Shape Your Ideal Body',
            'baseUrl'            => self::BASE,
            'flash'              => $flash,
            'user'               => $user,
            'upcomingClasses'    => $upcomingClasses,
            'trainers'           => $trainers,
            'classCount'         => $classCount,
            'trainerCount'       => $trainerCount,
            'totalBookings'      => $bookingCounts['total'] ?? 0,
            'userBookedClassIds' => $userBookedClassIds,
        ]);
    }
}
