# BraVaa Fitness — Comprehensive Test Plan
**Project:** Online Gym Class Booking System  
**Student:** NIYONKURU SHALOM (Reg. No: 224011591)  
**Academic Year:** Year 3 — Group 1  
**GitHub Repository:** `BIT-Y3-G1-224011591-OnlineGymClassBooking`  
**Collaborator Added:** `bemmy2005`

---

## 1. Overview & Test Scope
This test plan validates all critical functional and security requirements of the Online Gym Class Booking application across its 4 relational database entities (`users`, `trainers`, `classes`, `bookings`). The testing covers:
- User Authentication & Role-Based Access Control (Admin, Trainer, Member)
- CRUD operations for all 4 entities
- Business Logic (Capacity checking, duplicate prevention)
- Security protections (CSRF tokens, XSS HTML escaping, SQL Injection prevention)
- Asynchronous Fetch API / JSON endpoints (live search, instant booking modal)

---

## 2. Test Cases Matrix (>= 10 Comprehensive Cases)

| TC # | Category | Test Case Description | Test Input / Action | Expected Result | Type | Status |
|---|---|---|---|---|---|---|
| **TC-01** | Authentication | Valid Member Sign-In | `identifier`: `shalom_member`<br>`password`: `password123` | Password verified with `password_verify()`. Session initialized with `role='member'`. Flash toast appears: *"Welcome back, shalom_member!"*. Redirects to `/bookings`. | Happy Path | Passed |
| **TC-02** | Authentication | Invalid Credentials Handling | `identifier`: `shalom_member`<br>`password`: `wrongpassword` | Access denied. Red error alert displayed: *"Invalid username/email or password."* Session is NOT created. Form preserves entered identifier. | Failure Path | Passed |
| **TC-03** | User Registration | Client & Server Validation | Input blank username or password < 6 chars. | Form submission blocked. Red helper message displayed under input: *"Password must be at least 6 characters."* No invalid record created in DB. | Failure Path | Passed |
| **TC-04** | Security | CSRF Protection on Form POST | Submit `POST /classes/save` with missing or altered `csrf_token`. | Request rejected via `Csrf::validateOrAbort()`. Flash error: *"Security check failed (invalid CSRF token)."* No DB mutation occurs. | Failure Path | Passed |
| **TC-05** | Security | XSS Output Sanitization | Enter class name with script tag: `<script>alert('xss')</script>`. | Text safely escaped via `View::e()` (`htmlspecialchars(..., ENT_QUOTES, 'UTF-8')`). Rendered in DOM as plain text, no JavaScript executes. | Edge Case | Passed |
| **TC-06** | Domain Entity 1 (Classes) | Schedule New Class (Admin/Trainer) | Name: *"Sunset Power Yoga"*<br>Coach: Marcus Vance<br>Date: `2026-11-28 17:00`<br>Capacity: `20` | New class inserted with valid foreign key `trainer_id`. Flash message: *"New class scheduled successfully!"*. Row appears in classes table. | Happy Path | Passed |
| **TC-07** | Domain Entity 2 (Trainers) | Add & Validate Coach Roster | `full_name`: *"Sarah Jenkins"*<br>`specialization`: *"HIIT Endurance"*<br>`phone`: `+250 788 777 888` | Validated by `TrainerValidator`. Record added to `trainers` table with unique `trainer_id`. Appears in staff directory. | Happy Path | Passed |
| **TC-08** | Domain Entity 2 (Trainers) | Foreign Key Delete Restriction | Attempt to delete trainer currently assigned to active classes. | PDO foreign key exception caught gracefully. User alerted: *"Cannot delete trainer: classes are assigned to this coach."* Referential integrity preserved. | Failure Path | Passed |
| **TC-09** | Domain Entity 3 (Bookings) | Member Books Class with Capacity | Member clicks *"Book Spot"* on class with remaining capacity > 0. | Booking created in `bookings` table (`status='confirmed'`). Class booked count increments. Flash confirmation: *"Booking confirmed! You have reserved a spot..."*. | Happy Path | Passed |
| **TC-10** | Domain Entity 3 (Bookings) | Full Capacity Rejection | Member attempts to book class where `booked_count == capacity`. | Booking rejected by `ClassValidator` and `BookingController`. Flash warning: *"This class has reached full capacity. Please choose another session."* DB unmutated. | Failure Path | Passed |
| **TC-11** | Domain Entity 3 (Bookings) | Duplicate Booking Prevention | Member attempts to book the same class twice. | Unique constraint `uq_member_class_booking(class_id, member_id)` enforced. User alerted: *"You have already booked this class."* No duplicate entries. | Failure Path | Passed |
| **TC-12** | Domain Entity 3 (Bookings) | Member Cancels Own Reservation | Member clicks *"Cancel"* on their confirmed booking in `/bookings`. | Status updated to `'cancelled'`. Available spots for that class increment by 1 in real time. Flash message confirms cancellation. | Happy Path | Passed |
| **TC-13** | Domain Entity 4 (Users) | Admin CRUD & Self-Delete Guard | Admin attempts to delete their own active user ID. | Guard checks `(int)$id === (int)$currentUser['user_id']`. Action blocked: *"You cannot delete your own active administrator account."* | Failure Path | Passed |
| **TC-14** | Async Fetch API | Live Search via JSON Endpoint | User types *"Yoga"* in the search input on the Home or Classes page. | Fetch request dispatched to `GET /api/classes?q=Yoga`. Returns status `200 OK` with JSON array. DOM updates dynamically without full page reload. | Happy Path | Passed |
| **TC-15** | Async Fetch API | Modal Instant Booking via Fetch | Logged-in member confirms booking from the modal popup. | `POST /api/bookings/create` sent with JSON payload and `X-CSRF-Token`. Returns `{ success: true, booking_id: ..., remaining_spots: ... }`. Button updates to *"Already Booked"* live. | Happy Path | Passed |

---

## 3. Test Execution Verification

### Environment Setup for Testing
1. **PHP:** 8.2+ with PDO MySQL extension enabled.
2. **Database:** MySQL 8.0 / MariaDB 10.4 (XAMPP).
3. **Web Server:** Apache (XAMPP) at `http://localhost/online-gym/public/`.

### Automated Unit Test Run
The project includes automated validation checks verifying `ClassValidator`, `TrainerValidator`, `Csrf`, and `View::e()`. All test routines return status `0 (ALL TESTS PASSED)`.
