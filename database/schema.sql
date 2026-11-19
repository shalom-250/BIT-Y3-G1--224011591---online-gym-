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
    user_id INT DEFAULT NULL UNIQUE,
    full_name VARCHAR(100) NOT NULL,
    specialization VARCHAR(100) NOT NULL,
    phone VARCHAR(20) NOT NULL,
    CONSTRAINT fk_trainer_user
        FOREIGN KEY (user_id) REFERENCES users(user_id)
        ON DELETE SET NULL ON UPDATE CASCADE
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
