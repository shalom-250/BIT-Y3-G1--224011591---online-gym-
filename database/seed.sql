-- ========================================================
-- Online Gym Class Booking System (BraVaa Fitness)
-- Seed Data
-- ========================================================

USE gym_db;

-- --------------------------------------------------------
-- 1. Seed Users (Default password for all users: password123)
-- Hash generated via password_hash('password123', PASSWORD_BCRYPT)
-- --------------------------------------------------------
INSERT INTO users (user_id, username, email, password_hash, role, created_at) VALUES
(1, 'admin', 'admin@bravaagym.com', '$2y$10$CoEYzmnotNCVoubK79oUOuXn4.si3JWgAZUWbLtjd0sofhYeX8gw2', 'admin', '2026-01-10 08:00:00'),
(2, 'marcus_coach', 'marcus.vance@bravaagym.com', '$2y$10$CoEYzmnotNCVoubK79oUOuXn4.si3JWgAZUWbLtjd0sofhYeX8gw2', 'trainer', '2026-01-11 09:30:00'),
(3, 'elena_coach', 'elena.rostova@bravaagym.com', '$2y$10$CoEYzmnotNCVoubK79oUOuXn4.si3JWgAZUWbLtjd0sofhYeX8gw2', 'trainer', '2026-01-12 10:15:00'),
(4, 'david_coach', 'david.kimenyi@bravaagym.com', '$2y$10$CoEYzmnotNCVoubK79oUOuXn4.si3JWgAZUWbLtjd0sofhYeX8gw2', 'trainer', '2026-01-13 11:00:00'),
(5, 'shalom_member', 'shalom.niyonkuru@student.ur.ac.rw', '$2y$10$CoEYzmnotNCVoubK79oUOuXn4.si3JWgAZUWbLtjd0sofhYeX8gw2', 'member', '2026-02-01 14:00:00'),
(6, 'alice_uwase', 'alice.uwase@gmail.com', '$2y$10$CoEYzmnotNCVoubK79oUOuXn4.si3JWgAZUWbLtjd0sofhYeX8gw2', 'member', '2026-02-02 15:30:00'),
(7, 'kevin_mugisha', 'kevin.mugisha@gmail.com', '$2y$10$CoEYzmnotNCVoubK79oUOuXn4.si3JWgAZUWbLtjd0sofhYeX8gw2', 'member', '2026-02-03 16:45:00'),
(8, 'claire_ineza', 'claire.ineza@yahoo.com', '$2y$10$CoEYzmnotNCVoubK79oUOuXn4.si3JWgAZUWbLtjd0sofhYeX8gw2', 'member', '2026-02-04 17:20:00');

-- --------------------------------------------------------
-- 2. Seed Trainers (Domain Entity)
-- --------------------------------------------------------
INSERT INTO trainers (trainer_id, full_name, specialization, phone) VALUES
(1, 'Marcus Vance', 'CrossFit & Battle Rope Conditioning', '+250 788 111 222'),
(2, 'Elena Rostova', 'Power Yoga & Core Flexibility', '+250 788 333 444'),
(3, 'David Kimenyi', 'Olympic Weightlifting & Strength', '+250 788 555 666'),
(4, 'Sarah Jenkins', 'HIIT & Cardiovascular Endurance', '+250 788 777 888'),
(5, 'Patrick Mugabo', 'Boxing & Functional Mobility', '+250 788 999 000');

-- --------------------------------------------------------
-- 3. Seed Classes (Domain Entity)
-- --------------------------------------------------------
INSERT INTO classes (class_id, name, trainer_id, schedule_time, capacity) VALUES
(1, 'High-Intensity Battle Ropes & Cardio Blast', 1, '2026-11-25 07:00:00', 15),
(2, 'Morning Power Vinyasa Yoga', 2, '2026-11-25 08:30:00', 20),
(3, 'Olympic Barbell & Deadlift Mastery', 3, '2026-11-25 10:00:00', 12),
(4, 'Full Body Metabolic HIIT Circuit', 4, '2026-11-26 17:00:00', 18),
(5, 'Boxing Footwork & Heavy Bag Conditioning', 5, '2026-11-26 18:30:00', 14),
(6, 'Core Sculpt & Postural Alignment', 2, '2026-11-27 09:00:00', 20),
(7, 'Advanced Kettlebell & Tactical Strength', 1, '2026-11-27 16:30:00', 12),
(8, 'Weekend Sunset Mobility & Recovery', 2, '2026-11-28 17:30:00', 25);

-- --------------------------------------------------------
-- 4. Seed Bookings (Domain Entity)
-- --------------------------------------------------------
INSERT INTO bookings (booking_id, class_id, member_id, booked_at, status) VALUES
(1, 1, 5, '2026-11-19 09:15:00', 'confirmed'),
(2, 2, 5, '2026-11-19 10:00:00', 'confirmed'),
(3, 1, 6, '2026-11-19 11:20:00', 'confirmed'),
(4, 3, 6, '2026-11-19 12:45:00', 'attended'),
(5, 4, 7, '2026-11-19 13:10:00', 'confirmed'),
(6, 5, 8, '2026-11-19 14:00:00', 'cancelled'),
(7, 3, 7, '2026-11-19 14:30:00', 'confirmed'),
(8, 2, 8, '2026-11-19 15:00:00', 'confirmed');
