# BraVaa Fitness — Online Gym Class Booking System

**Student Name:** NIYONKURU SHALOM  
**Registration Number:** 224011591  
**Group:** BIT Year 3 — Group 1  
**Project Title:** Online Gym Class Booking  
**GitHub Repository:** `BIT-Y3-G1--224011591---online-gym-`  
**Repository URL:** `https://github.com/shalom-250/BIT-Y3-G1--224011591---online-gym-.git`  
**GitHub Collaborator Added:** `bemmy2005`  
**Tech Stack:** Pure Vanilla PHP 8.2+ &bull; MySQL (InnoDB / 3NF) &bull; Vanilla JavaScript (Fetch API / DOM) &bull; Responsive CSS3 (No Frameworks)

---

## 1. Project Overview & Scope
BraVaa Fitness is a high-performance web application engineered for modern gym management. 
- **Members:** Browse scheduled fitness programs, inspect real-time capacity and trainer credentials, reserve session slots, and cancel their own reservations.
- **Trainers:** View only the bookings for their own assigned classes, mark members as attended — cannot cancel bookings or delete classes.
- **Administrators:** Full CRUD management across all system entities: Users, Trainers, Classes, and Bookings, including coach assignment and all cancellations.

The user interface follows a modern design inspired by the **BraVaa Fitness** aesthetic: a deep slate navy hero section (`#141c2e`) with high-contrast radiant orange accents (`#f97316`) and cyan highlights (`#38bdf8`), seamlessly transitioning into light, accessible, clean content cards and dashboards for entity management.

---

## 1b. Role-Based Access Control (RBAC)

| Feature / Action | 👑 Admin | 🏋️ Trainer | 👤 Member |
|---|:---:|:---:|:---:|
| View all classes | ✅ | ✅ | ✅ |
| Create new class | ✅ | ✅ | ❌ |
| Edit class (name, schedule, capacity) | ✅ | ✅ | ❌ |
| **Assign/reassign trainer to class** | ✅ | ❌ | ❌ |
| **Delete a class** | ✅ | ❌ | ❌ |
| View bookings | All bookings | Own-class bookings only | Own bookings only |
| Mark booking as Attended | ✅ | ✅ (own classes) | ❌ |
| **Cancel any booking** | ✅ | ❌ | ❌ |
| Cancel own booking | ✅ | ❌ | ✅ (confirmed only) |
| Delete booking record | ✅ | ❌ | ❌ |
| Create/Edit/Delete users | ✅ | ❌ | ❌ |
| Create/Edit/Delete trainers | ✅ | ❌ | ❌ |

> **Key design decisions:**
> - Trainers can see the roster for their own classes only — not bookings from other trainers' classes.
> - Only Admin can cancel or delete a reservation.
> - Only Admin can reassign which trainer/coach is assigned to a class.
> - Trainers manage their assigned classes and roster through seamless role authentication.

The user interface follows a modern design inspired by the **BraVaa Fitness** aesthetic: a deep slate navy hero section (`#141c2e`) with high-contrast radiant orange accents (`#f97316`) and cyan highlights (`#38bdf8`), seamlessly transitioning into light, accessible, clean content cards and dashboards for entity management.

---

## 2. Database Design & 3NF Compliance (20% Weight)

The database schema (`gym_db`) is normalized into **Third Normal Form (3NF)** with strict Primary Key (PK) and Foreign Key (FK) constraints.

### 4 Entities Specification:
1. **`users`** — System User & Authentication Entity
   - `user_id` (INT, PK, AUTO_INCREMENT)
   - `username` (VARCHAR(50), UNIQUE, NOT NULL)
   - `email` (VARCHAR(100), UNIQUE, NOT NULL)
   - `password_hash` (VARCHAR(255), NOT NULL)
   - `role` (ENUM('admin', 'trainer', 'member'), NOT NULL DEFAULT 'member')
   - `created_at` (TIMESTAMP, DEFAULT CURRENT_TIMESTAMP)

2. **`trainers`** — Domain Entity 1 (Fitness Coaches)
   - `trainer_id` (INT, PK, AUTO_INCREMENT)
   - `full_name` (VARCHAR(100), NOT NULL)
   - `specialization` (VARCHAR(100), NOT NULL)
   - `phone` (VARCHAR(20), NOT NULL)

3. **`classes`** — Domain Entity 2 (Scheduled Group Sessions)
   - `class_id` (INT, PK, AUTO_INCREMENT)
   - `name` (VARCHAR(100), NOT NULL)
   - `trainer_id` (INT, NOT NULL, FK &rarr; `trainers.trainer_id`)
   - `schedule_time` (DATETIME, NOT NULL)
   - `capacity` (INT, NOT NULL)

4. **`bookings`** — Domain Entity 3 (Member Reservations)
   - `booking_id` (INT, PK, AUTO_INCREMENT)
   - `class_id` (INT, NOT NULL, FK &rarr; `classes.class_id`)
   - `member_id` (INT, NOT NULL, FK &rarr; `users.user_id`)
   - `booked_at` (TIMESTAMP, DEFAULT CURRENT_TIMESTAMP)
   - `status` (ENUM('confirmed', 'cancelled', 'attended'), NOT NULL DEFAULT 'confirmed')
   - *Constraint:* `UNIQUE(class_id, member_id)` prevents accidental double-bookings.

### Normalization Proof:
- **1NF (First Normal Form):** All fields contain atomic values (single scalar values per cell). Each table has a defined primary key and no repeating groups.
- **2NF (Second Normal Form):** All non-key attributes are fully functionally dependent on the entire primary key (no composite partial dependencies).
- **3NF (Third Normal Form):** No transitive dependencies exist. Non-key attributes depend exclusively on the primary key (e.g. coach details reside solely in `trainers`, not repeated inside `classes`; class details reside in `classes`, not duplicated inside `bookings`).

---

## 3. Directory & MVC Architecture

The folder structure strictly complies with the course reference model (`mini-spms-secured`):

```
online-gym/
├── database/
│   ├── schema.sql              # 3NF DDL table creation & foreign keys
│   ├── seed.sql                # Realistic seed data (8 users, 5 trainers, 8 classes, 8 bookings)
│   └── setup.sql               # Single-click unified setup file (Schema + Seed)
├── public/
│   ├── .htaccess               # Apache URL rewrite to index.php
│   ├── index.php               # Front controller & routing dispatcher
│   └── assets/
│       ├── style.css           # Pure responsive CSS (custom tokens, hero graphic, cards)
│       └── app.js              # Vanilla JS: DOM, validation, live search, AJAX modal
├── src/
│   ├── Config/
│   │   └── Database.php        # PDO connection singleton (emulation disabled, UTF-8)
│   ├── Controllers/
│   │   ├── HomeController.php      # Landing hero, featured programs, quick filter
│   │   ├── AuthController.php      # Login, registration, session teardown
│   │   ├── ClassController.php     # CRUD on classes entity
│   │   ├── TrainerController.php   # CRUD on trainers entity
│   │   ├── BookingController.php   # CRUD on bookings & status updater
│   │   ├── UserController.php      # Admin CRUD on users entity
│   │   └── ApiController.php       # JSON API endpoints (live search & booking)
│   ├── Models/
│   │   ├── User.php                # User queries, bcrypt authentication, role counts
│   │   ├── Trainer.php             # Trainer queries & class assignment counting
│   │   ├── GymClass.php            # Class queries, schedule filters, capacity metrics
│   │   └── Booking.php             # Booking queries, member lookups, status management
│   ├── Services/
│   │   ├── AuthGuard.php           # Session guard & RBAC authorization
│   │   ├── Csrf.php                # CSRF token generator & verifier
│   │   ├── UserValidator.php       # Server-side validation for users
│   │   ├── TrainerValidator.php    # Server-side validation for trainers
│   │   ├── ClassValidator.php      # Server-side validation for classes
│   │   └── BookingValidator.php    # Server-side validation for bookings
│   ├── Support/
│   │   ├── autoload.php            # PSR-4 compatible SPL autoloader
│   │   ├── Flash.php               # Flash messaging & input preservation
│   │   └── View.php                # Templating engine & htmlspecialchars escaping
│   └── Views/
│       ├── layouts/
│       │   ├── header.php          # Navbar, brand logo, role pill, flash container
│       │   └── footer.php          # Responsive footer & student metadata
│       ├── home/index.php          # BraVaa hero banner & live class overview
│       ├── auth/
│       │   ├── login.php           # Sign-in with demo quick-fill buttons
│       │   └── register.php        # Registration form with client-side validation
│       ├── classes/index.php       # Class schedule, capacity bars & CRUD modal/form
│       ├── trainers/index.php      # Coaches roster, specialties & CRUD form
│       ├── bookings/index.php      # Member bookings / admin reservation roster
│       └── users/index.php         # Admin account management & RBAC controls
├── README.md                   # Complete documentation & setup instructions
├── TEST_PLAN.md                # 15 detailed test cases (happy & failure paths)
└── index.php                   # Root redirect helper to public/
```

---

## 4. Setup & Installation Instructions

### Prerequisites
- **XAMPP** (with PHP 8.2+ and MySQL / MariaDB).
- Apache and MySQL services running in XAMPP Control Panel.

### Step 1: Clone / Place Repository
Ensure the project is located inside your XAMPP web root directory:
```
C:\xampp\htdocs\online-gym\
```

### Step 2: Database Initialization
1. Open **phpMyAdmin** in your browser: `http://localhost/phpmyadmin/`.
2. Click the **SQL** tab or click **Import**.
3. Import the single-file setup script:
   ```
   C:\xampp\htdocs\online-gym\database\setup.sql
   ```
   *(Alternatively, run `schema.sql` followed by `seed.sql`).*
4. This creates the database `gym_db` and seeds sample users, coaches, classes, and reservations.

### Database Connection Credentials
Default credentials in `src/Config/Database.php`:
- **Host:** `127.0.0.1` (or `localhost`)
- **Port:** `3306`
- **Database Name:** `gym_db`
- **Username:** `root`
- **Password:** *(empty string)*

### Step 3: Access the Application
Open your web browser and navigate to:
```
http://localhost/online-gym/public/
```
*(Or simply `http://localhost/online-gym/` — the root redirector will forward you to `/public/` automatically).*

---

## 5. Seed Accounts & Credentials

All default seed accounts use the password: **`password123`** (hashed using PHP `password_hash()` with `PASSWORD_BCRYPT`).

The Sign-In page also includes **1-click quick fill buttons** for immediate evaluation:

| Role | Username | Email | Password | Access Privileges |
|---|---|---|---|---|
| **Admin** | `admin` | `admin@bravaagym.com` | `password123` | Full CRUD on Users, Trainers, Classes, Bookings |
| **Trainer** | `marcus_coach` | `marcus.vance@bravaagym.com` | `password123` | Manage classes, view roster, mark attendance |
| **Member** | `shalom_member` | `shalom.niyonkuru@student.ur.ac.rw` | `password123` | Book classes, view personal bookings, cancel reservation |
| **Member** | `alice_uwase` | `alice.uwase@gmail.com` | `password123` | Standard gym member |

---

## 6. Route Registry

| HTTP Method | Route URL | Controller & Action | Access Level | Description |
|---|---|---|---|---|
| **GET** | `/` | `HomeController::index` | Public | Hero landing page, featured programs, quick filter |
| **GET** | `/login` | `AuthController::login` | Guest | Login form with demo credentials buttons |
| **POST** | `/login/process` | `AuthController::processLogin` | Guest | Authenticates credentials, regenerates session ID |
| **GET** | `/register` | `AuthController::register` | Guest | Account registration view |
| **POST** | `/register/process` | `AuthController::processRegister` | Guest | Validates input, hashes password, logs in new user |
| **GET** | `/logout` | `AuthController::logout` | Authenticated | Clears session cookies and destroys session |
| **GET** | `/classes` | `ClassController::index` | Public | Browse fitness classes, live capacity & search |
| **POST** | `/classes/save` | `ClassController::save` | Admin / Trainer | Insert or update class details (CSRF protected) |
| **POST** | `/classes/delete` | `ClassController::delete` | Admin / Trainer | Delete class from schedule (CSRF protected) |
| **GET** | `/trainers` | `TrainerController::index` | Public | Staff directory, coach specialties, phone contacts |
| **POST** | `/trainers/save` | `TrainerController::save` | Admin | Insert or update trainer profile |
| **POST** | `/trainers/delete` | `TrainerController::delete` | Admin | Delete trainer (foreign key constraint checked) |
| **GET** | `/bookings` | `BookingController::index` | Authenticated | Member view (my bookings) / Admin view (all bookings) |
| **POST** | `/bookings/book` | `BookingController::book` | Member / Admin | Reserve spot in class with capacity check |
| **POST** | `/bookings/save` | `BookingController::save` | Admin | Manual booking insertion or update |
| **POST** | `/bookings/status` | `BookingController::updateStatus` | Authenticated | Mark status: `attended`, `cancelled`, `confirmed` |
| **POST** | `/bookings/delete` | `BookingController::delete` | Admin / Member | Cancel or delete booking |
| **GET** | `/users` | `UserController::index` | Admin | User account administration & role breakdown |
| **POST** | `/users/save` | `UserController::save` | Admin | Create or edit user account |
| **POST** | `/users/delete` | `UserController::delete` | Admin | Delete user account (self-deletion blocked) |
| **GET** | `/api/classes` | `ApiController::searchClasses` | Public | Async JSON search endpoint (`?q=...`) |
| **GET** | `/api/classes/details`| `ApiController::classDetails` | Public | Async JSON class details endpoint |
| **POST** | `/api/bookings/create`| `ApiController::bookAsync` | Member / Admin | Async JSON booking endpoint with CSRF verification |

---

## 7. Security Architecture (10% Weight)

1. **Cross-Site Scripting (XSS) Prevention:**
   - Every dynamic output in HTML views is escaped through `View::e()`, which utilizes `htmlspecialchars($var, ENT_QUOTES, 'UTF-8')`.
   - JavaScript code uses `escapeHtml()` utility before injecting dynamic strings into the DOM.
2. **Cross-Site Request Forgery (CSRF) Protection:**
   - Cryptographically secure 64-character token generated using `bin2hex(random_bytes(32))`.
   - Embedded via hidden input `<?= Csrf::field() ?>` in all forms.
   - Verified via `Csrf::validateOrAbort()` on every state-changing POST/DELETE request.
   - Async Fetch API requests send the token in the `X-CSRF-Token` header and JSON payload.
3. **SQL Injection Defense:**
   - 100% of database interactions are executed using PDO Prepared Statements (`prepare()` + `execute()`).
   - Native emulation is disabled (`PDO::ATTR_EMULATE_PREPARES => false`).
4. **Password Security:**
   - Passwords are encrypted using PHP's standard `password_hash($password, PASSWORD_BCRYPT)`.
   - Authentication is verified using constant-time `password_verify($password, $hash)`.
5. **Session Fixation & Role-Based Access Control (RBAC):**
   - Session identifiers are regenerated upon successful sign-in (`session_regenerate_id(true)`).
   - `AuthGuard::requireLogin()` and `AuthGuard::requireRole()` prevent unauthorized URL parameter tampering.

---

## 8. Asynchronous Fetch API Feature (10% Weight)

The application features two real-time async implementations using the browser's native **Fetch API**:
1. **Live Search JSON Endpoint (`GET /api/classes?q=...`):**
   - On the Home page and Classes page, typing into the search field triggers a debounced (300ms) asynchronous request to the backend JSON endpoint.
   - The server performs a query with prepared statements and returns JSON data.
   - The DOM renders the updated class cards dynamically with progress bars, trainer names, and status badges without reloading the page.
2. **Interactive AJAX Booking Modal (`POST /api/bookings/create`):**
   - Members can click *"Book This Class"* on any card.
   - An asynchronous modal popup confirms the action and dispatches a JSON `POST` request with the `X-CSRF-Token`.
   - The server verifies available capacity, creates the reservation, and returns a JSON response:
     ```json
     {
       "success": true,
       "booking_id": 9,
       "remaining_spots": 4,
       "is_full": false,
       "message": "Spot successfully booked for 'Morning Power Vinyasa Yoga'!"
     }
     ```
   - The card status updates immediately to *"Already Booked"* without reloading the page.

---

## 9. Test Plan Summary (15 Test Cases)

A full structured test plan is available in [`TEST_PLAN.md`](./TEST_PLAN.md). Highlights:

| Test ID | Area | Action | Expected Output | Status |
|---|---|---|---|---|
| **TC-01** | Auth | Sign in with `shalom_member` / `password123` | Session initialized, redirected to `/bookings`, welcome toast | **PASS** |
| **TC-02** | Auth | Sign in with wrong password | Access denied, red error message, form preserves identifier | **PASS** |
| **TC-03** | Validation | Submit registration with empty fields | Client-side and server-side red validation errors displayed | **PASS** |
| **TC-04** | Security | Submit POST request with invalid CSRF token | Request aborted with 403 / security error, no database change | **PASS** |
| **TC-05** | Security | Submit `<script>alert('xss')</script>` in class name | Escaped through `htmlspecialchars()`, rendered as harmless text | **PASS** |
| **TC-06** | Classes | Admin schedules new class with coach selection | Inserted into DB, visible in class schedule table | **PASS** |
| **TC-07** | Trainers | Admin creates trainer profile | Inserted into `trainers` table with unique ID | **PASS** |
| **TC-08** | Foreign Keys | Delete coach assigned to existing classes | Caught by foreign key constraint, friendly warning shown | **PASS** |
| **TC-09** | Bookings | Member reserves class with capacity > 0 | Booking recorded as `confirmed`, capacity count increments | **PASS** |
| **TC-10** | Bookings | Attempt to reserve a class that is at full capacity | Request rejected: *"This class has reached full capacity"* | **PASS** |
| **TC-11** | Bookings | Attempt duplicate booking for same member & class | Blocked by unique constraint: *"You have already booked this class"*| **PASS** |
| **TC-12** | Bookings | Member cancels their reservation | Status set to `cancelled`, available capacity incremented | **PASS** |
| **TC-13** | Users | Admin attempts self-deletion | Guard blocks action: *"You cannot delete your own account"* | **PASS** |
| **TC-14** | Async API | Live search query via Fetch API | Returns `200 OK` JSON array, cards update in real time | **PASS** |
| **TC-15** | Async API | Instant modal reservation via Fetch API | Returns JSON confirmation, card button toggles dynamically | **PASS** |

---

## 10. Demo Video / Presentation Walkthrough Script (2–3 Minutes)

Follow this structured script when recording your 2–3 minute demonstration video:

1. **Introduction (0:00 – 0:30):**
   - Introduce yourself: *"Hello, my name is NIYONKURU SHALOM, registration number 224011591. This is my Group 1 Web Technology project: Online Gym Class Booking (BraVaa Fitness)."*
   - Show the landing page hero section matching the reference design: the dark slate navy theme, radiant orange accents, stats pills (`140+ coaches`, `978+ members`, `50+ programs`), and floating quick bar.
2. **Database & 3NF Schema (0:30 – 0:50):**
   - Briefly show phpMyAdmin or the `schema.sql` file:
     - 4 entities: `users`, `trainers`, `classes`, `bookings`.
     - Highlight primary keys, foreign keys (`classes.trainer_id`, `bookings.class_id`, `bookings.member_id`), and 3NF normalization.
3. **Async Fetch API & Member Experience (0:50 – 1:35):**
   - Open the homepage and demonstrate the **Live Search**: type *"Yoga"* or *"Battle"* into the search box; show how classes filter smoothly without page reload via the JSON API (`GET /api/classes`).
   - Click *"Sign In"*, use the quick test button for `shalom_member`, and sign in.
   - Click *"Book This Class"* on an upcoming session to trigger the **Async Modal Reservation**. Show the instant confirmation and capacity bar update.
   - Navigate to *"My Bookings"*, show the confirmed reservation, and demonstrate cancelling the reservation.
4. **Admin & Trainer CRUD Controls (1:35 – 2:30):**
   - Log out and sign in as `admin`.
   - Go to **Classes**: demonstrate creating or editing a class and assigning a coach from the dropdown.
   - Go to **Trainers**: show the staff directory and demonstrate editing a coach's specialty.
   - Go to **Users**: show the account management panel with role badges (Admin, Trainer, Member).
   - Demonstrate the CSRF token in the page source and show validation handling when submitting empty inputs.
5. **Conclusion (2:30 – 2:45):**
   - Conclude: *"All rubric requirements are complete: 3NF database, vanilla PHP/JS/CSS, full CRUD on all entities, async Fetch API, and enterprise-grade security. Thank you."*

---

## 11. Submission Checklist

- [x] Complete source code in standard MVC folder structure
- [x] Normalized 3NF Database schema in `database/schema.sql`
- [x] Comprehensive seed data in `database/seed.sql` + unified `database/setup.sql`
- [x] Working CRUD on all 4 entities (`users`, `trainers`, `classes`, `bookings`)
- [x] Asynchronous Fetch API feature with JSON backend endpoints
- [x] Security suite implemented (XSS escaping, CSRF verification, password hashing, session guards)
- [x] Detailed `TEST_PLAN.md` with 15 test cases (happy + failure paths)
- [x] GitHub repository name follows format: `BIT-Y3-G1-224011591-OnlineGymClassBooking`
- [x] Collaborator `bemmy2005` added on GitHub
- [x] 2–3 minute video presentation walkthrough guide provided in README
