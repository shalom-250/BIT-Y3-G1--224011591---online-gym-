-- ========================================================
-- Online Gym Class Booking System (BraVaa Fitness)
-- Database Schema (3NF Compliant)
-- Student: NIYONKURU SHALOM (Reg. No: 224011591)
-- ========================================================

CREATE DATABASE IF NOT EXISTS gym_db CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE gym_db;

SET FOREIGN_KEY_CHECKS = 0;
DROP TABLE IF EXISTS bookings;
DROP TABLE IF EXISTS classes;
DROP TABLE IF EXISTS trainers;
DROP TABLE IF EXISTS users;
SET FOREIGN_KEY_CHECKS = 1;

-- --------------------------------------------------------
-- Table 1: users
-- 3NF: Primary key user_id uniquely identifies each user.
-- No partial dependencies, no transitive dependencies.
-- --------------------------------------------------------
CREATE TABLE users (
    user_id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) NOT NULL UNIQUE,
    email VARCHAR(100) NOT NULL UNIQUE,
    password_hash VARCHAR(255) NOT NULL,
    role ENUM('admin', 'trainer', 'member') NOT NULL DEFAULT 'member',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------
-- Table 2: trainers
-- 3NF: Primary key trainer_id uniquely identifies each trainer.
-- Attributes full_name, specialization, phone depend solely on trainer_id.
-- --------------------------------------------------------
CREATE TABLE trainers (
    trainer_id INT AUTO_INCREMENT PRIMARY KEY,
    full_name VARCHAR(100) NOT NULL,
    specialization VARCHAR(100) NOT NULL,
    phone VARCHAR(20) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------
-- Table 3: classes
-- 3NF: Primary key class_id uniquely identifies each class.
-- trainer_id is a foreign key referencing trainers(trainer_id).
-- --------------------------------------------------------
CREATE TABLE classes (
    class_id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    trainer_id INT NOT NULL,
    schedule_time DATETIME NOT NULL,
    capacity INT NOT NULL,
    CONSTRAINT fk_class_trainer
        FOREIGN KEY (trainer_id) REFERENCES trainers(trainer_id)
        ON DELETE RESTRICT ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------
-- Table 4: bookings
-- 3NF: Primary key booking_id.
-- class_id references classes(class_id).
-- member_id references users(user_id).
-- Unique constraint prevents duplicate bookings for same member and class.
-- --------------------------------------------------------
CREATE TABLE bookings (
    booking_id INT AUTO_INCREMENT PRIMARY KEY,
    class_id INT NOT NULL,
    member_id INT NOT NULL,
    booked_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    status ENUM('confirmed', 'cancelled', 'attended') NOT NULL DEFAULT 'confirmed',
    CONSTRAINT fk_booking_class
        FOREIGN KEY (class_id) REFERENCES classes(class_id)
        ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT fk_booking_member
        FOREIGN KEY (member_id) REFERENCES users(user_id)
        ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT uq_member_class_booking UNIQUE (class_id, member_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
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
