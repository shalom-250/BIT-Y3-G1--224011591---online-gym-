<?php
use App\Support\View;
use App\Services\Csrf;

$base = $baseUrl ?? '/online-gym/public';
?>

<!-- ========================================================
     HERO SECTION (Replicating BraVaa Design from Image)
     ======================================================== -->
<section class="hero-section">
  <div class="hero-container">
    
    <!-- Hero Left: Content & Metrics -->
    <div class="hero-content">
      <div class="hero-pill-badge">
        <span class="badge-tag">THE BEST</span>
        <span class="badge-text">FITNESS CLUB IN THE TOWN</span>
      </div>

      <h1 class="hero-title">
        SHAPE YOUR <span class="highlight-cyan">IDEAL BODY</span>
      </h1>

      <p class="hero-desc">
        In here will help you to shape and build your ideal body and live up your life to the fullest
      </p>

      <div class="hero-stats">
        <div class="stat-item">
          <div class="stat-number">140+</div>
          <div class="stat-label">EXPERT COACHS</div>
        </div>
        <div class="stat-item">
          <div class="stat-number">978+</div>
          <div class="stat-label">MEMBERS JOINED</div>
        </div>
        <div class="stat-item">
          <div class="stat-number">50+</div>
          <div class="stat-label">FITNESS PROGRAMS</div>
        </div>
      </div>

      <div class="hero-cta-group">
        <a href="<?= View::e($base) ?>/classes" class="btn-hero-primary">Get Started</a>
        <a href="#classes-overview" class="btn-hero-secondary">
          <span class="icon-circle">
            <svg width="14" height="14" viewBox="0 0 24 24" fill="currentColor"><polygon points="5 3 19 12 5 21 5 3"></polygon></svg>
          </span>
          <span>Learn More</span>
        </a>
      </div>
    </div>

    <!-- Hero Right: Athlete Graphic with Orange Backdrop -->
    <div class="hero-visual">
      <div class="hero-orange-backdrop"></div>
      <div class="hero-image-wrapper">
        <!-- SVG / Canvas workout graphic illustrating battle rope athlete -->
        <svg class="hero-athlete-graphic" viewBox="0 0 600 680" fill="none" xmlns="http://www.w3.org/2000/svg">
          <defs>
            <linearGradient id="gymGlow" x1="0%" y1="0%" x2="100%" y2="100%">
              <stop offset="0%" stop-color="#1e293b"/>
              <stop offset="100%" stop-color="#0f172a"/>
            </linearGradient>
            <linearGradient id="ropeGrad1" x1="0%" y1="0%" x2="100%" y2="100%">
              <stop offset="0%" stop-color="#ef4444"/>
              <stop offset="100%" stop-color="#b91c1c"/>
            </linearGradient>
            <linearGradient id="ropeGrad2" x1="0%" y1="0%" x2="100%" y2="100%">
              <stop offset="0%" stop-color="#f97316"/>
              <stop offset="100%" stop-color="#dc2626"/>
            </linearGradient>
            <radialGradient id="spotLight" cx="50%" cy="30%" r="50%">
              <stop offset="0%" stop-color="#ffffff" stop-opacity="0.15"/>
              <stop offset="100%" stop-color="#000000" stop-opacity="0.6"/>
            </radialGradient>
          </defs>

          <!-- Gym Interior Backdrop -->
          <rect width="600" height="680" rx="16" fill="url(#gymGlow)"/>
          <rect width="600" height="680" rx="16" fill="url(#spotLight)"/>

          <!-- Gym Lights & Racks Silhouette -->
          <line x1="60" y1="90" x2="540" y2="90" stroke="#334155" stroke-width="6"/>
          <line x1="120" y1="90" x2="120" y2="130" stroke="#f8fafc" stroke-width="4" opacity="0.6"/>
          <line x1="280" y1="90" x2="280" y2="130" stroke="#f8fafc" stroke-width="4" opacity="0.6"/>
          <line x1="440" y1="90" x2="440" y2="130" stroke="#f8fafc" stroke-width="4" opacity="0.6"/>
          <rect x="50" y="450" width="80" height="70" rx="6" fill="#1e293b" stroke="#334155" stroke-width="2"/>
          <rect x="470" y="440" width="85" height="85" rx="6" fill="#1e293b" stroke="#334155" stroke-width="2"/>

          <!-- Floor Shadow -->
          <ellipse cx="380" cy="560" rx="160" ry="30" fill="#000" opacity="0.5"/>

          <!-- Athletic Male Model / Athlete Silhouetted Anatomy -->
          <!-- Head -->
          <circle cx="390" cy="225" r="28" fill="#fcd34d"/>
          <!-- Hair & Beard -->
          <path d="M370 215 C370 195, 410 195, 415 210 C412 216, 405 215, 400 215" fill="#78350f"/>
          <path d="M380 235 C385 252, 405 250, 408 235" fill="#92400e"/>
          <!-- Neck & Traps -->
          <path d="M375 245 L350 270 L430 270 L405 245 Z" fill="#f59e0b"/>
          <!-- Torso Tank Top -->
          <path d="M350 270 L340 375 L435 375 L430 270 L410 270 L400 310 L380 310 L370 270 Z" fill="#475569"/>
          <!-- Muscular Arms with Tension -->
          <!-- Right Arm (holding battle rope) -->
          <path d="M350 270 Q305 300 290 340 Q310 370 330 350 Q335 315 365 290 Z" fill="#d97706"/>
          <!-- Left Arm (holding battle rope) -->
          <path d="M430 270 Q455 305 470 345 Q445 365 430 345 Q425 315 415 285 Z" fill="#b45309"/>
          <!-- Athletic Shorts -->
          <path d="M338 370 L320 445 L375 445 L390 405 L405 445 L450 445 L437 370 Z" fill="#0f172a"/>
          <!-- Legs (Squat Stance) -->
          <path d="M330 445 L340 520 L370 520 L365 445 Z" fill="#d97706"/>
          <path d="M410 445 L425 520 L450 515 L445 445 Z" fill="#b45309"/>
          <!-- Athletic Training Shoes (Red) -->
          <ellipse cx="355" cy="530" rx="24" ry="12" fill="#ef4444"/>
          <ellipse cx="440" cy="525" rx="22" ry="11" fill="#ef4444"/>

          <!-- Dynamic Heavy Red Battle Ropes in Motion Waves -->
          <!-- Left Rope Wave -->
          <path d="M290 340 C 230 360, 150 430, 80 470 C 40 495, 20 530, 0 550" 
                stroke="url(#ropeGrad1)" stroke-width="16" stroke-linecap="round" fill="none"/>
          <path d="M290 340 C 230 360, 150 430, 80 470 C 40 495, 20 530, 0 550" 
                stroke="#fee2e2" stroke-width="4" stroke-dasharray="8 8" fill="none"/>

          <!-- Right Rope Wave -->
          <path d="M470 345 C 390 375, 270 410, 180 450 C 90 490, 40 520, 0 540" 
                stroke="url(#ropeGrad2)" stroke-width="18" stroke-linecap="round" fill="none"/>
          <path d="M470 345 C 390 375, 270 410, 180 450 C 90 490, 40 520, 0 540" 
                stroke="#fff7ed" stroke-width="5" stroke-dasharray="10 6" fill="none"/>

          <!-- Motion Energy Particles -->
          <circle cx="270" cy="360" r="3" fill="#f97316" opacity="0.8"/>
          <circle cx="210" cy="400" r="4" fill="#ef4444" opacity="0.7"/>
          <circle cx="150" cy="440" r="3" fill="#f97316" opacity="0.6"/>
        </svg>
      </div>
    </div>

  </div>

  <!-- Floating Quick Bar (From Image Bottom) -->
  <div class="hero-quick-bar-wrapper">
    <div class="quick-bar-card">
      <div class="quick-bar-col">
        <div class="quick-bar-icon">
          <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"></path><circle cx="12" cy="10" r="3"></circle></svg>
        </div>
        <div class="quick-bar-text">
          <span class="quick-title">Location</span>
          <span class="quick-subtitle">Kigali City Hub &bull; 05:00 - 22:00</span>
        </div>
      </div>

      <div class="quick-bar-divider"></div>

      <div class="quick-bar-col">
        <div class="quick-bar-icon">
          <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polygon points="12 2 2 7 12 12 22 7 12 2"></polygon><polyline points="2 17 12 22 22 17"></polyline><polyline points="2 12 12 17 22 12"></polyline></svg>
        </div>
        <div class="quick-bar-text">
          <span class="quick-title">Programs</span>
          <span class="quick-subtitle"><?= (int)$classCount ?> Active Fitness Classes</span>
        </div>
      </div>

      <div class="quick-bar-divider"></div>

      <div class="quick-bar-col">
        <div class="quick-bar-icon">
          <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path><circle cx="12" cy="7" r="4"></circle></svg>
        </div>
        <div class="quick-bar-text">
          <span class="quick-title">Membership</span>
          <span class="quick-subtitle">Choose Flexible Plans</span>
        </div>
      </div>

      <div class="quick-bar-action">
        <a href="<?= View::e($base) ?>/classes" class="btn-quick-join">Join Now</a>
      </div>
    </div>
  </div>
</section>

<!-- ========================================================
     MAIN LIGHT-COLORED SECTIONS (Crisp, Clean & Professional)
     ======================================================== -->
<section class="section-light" id="classes-overview">
  <div class="content-container">

    <!-- Section Heading with Live Search Bar -->
    <div class="section-header-row">
      <div>
        <span class="section-pill-tag">DAILY SCHEDULE</span>
        <h2 class="section-heading">Featured Fitness Classes</h2>
        <p class="section-subtext">Book your class online with instant reservation and real-time capacity tracking.</p>
      </div>

      <div class="live-search-container">
        <div class="search-input-wrapper">
          <svg class="search-icon" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="11" cy="11" r="8"></circle><line x1="21" y1="21" x2="16.65" y2="16.65"></line></svg>
          <input type="text" id="home-live-search" class="search-field" placeholder="Search class or coach..." autocomplete="off">
          <span id="home-search-spinner" class="spinner-small" style="display: none;"></span>
        </div>
      </div>
    </div>

    <!-- Live Classes Grid -->
    <div class="classes-grid" id="home-classes-grid">
      <?php if (!empty($upcomingClasses)): ?>
        <?php foreach ($upcomingClasses as $c): 
          $isBooked = in_array((int)$c['class_id'], $userBookedClassIds, true);
          $pctBooked = $c['capacity'] > 0 ? round(($c['booked_count'] / $c['capacity']) * 100) : 0;
        ?>
          <div class="class-card" data-class-id="<?= (int)$c['class_id'] ?>">
            <div class="class-card-header">
              <span class="class-category-badge"><?= View::e($c['trainer_specialization']) ?></span>
              <?php if ($c['is_full']): ?>
                <span class="badge-status badge-danger">FULL</span>
              <?php else: ?>
                <span class="badge-status badge-success"><?= (int)$c['remaining_spots'] ?> SPOTS LEFT</span>
              <?php endif; ?>
            </div>

            <h3 class="class-title"><?= View::e($c['name']) ?></h3>

            <div class="class-meta-info">
              <div class="meta-row">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="4" width="18" height="18" rx="2" ry="2"></rect><line x1="16" y1="2" x2="16" y2="6"></line><line x1="8" y1="2" x2="8" y2="6"></line><line x1="3" y1="10" x2="21" y2="10"></line></svg>
                <span><?= View::formatDateTime((string)$c['schedule_time']) ?></span>
              </div>
              <div class="meta-row">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path><circle cx="12" cy="7" r="4"></circle></svg>
                <span>Coach: <strong><?= View::e($c['trainer_name']) ?></strong></span>
              </div>
            </div>

            <!-- Capacity Progress Bar -->
            <div class="capacity-bar-container">
              <div class="capacity-bar-labels">
                <span>Booked: <?= (int)$c['booked_count'] ?> / <?= (int)$c['capacity'] ?></span>
                <span><?= $pctBooked ?>%</span>
              </div>
              <div class="progress-track">
                <div class="progress-fill <?= $pctBooked >= 100 ? 'fill-full' : ($pctBooked >= 80 ? 'fill-warning' : 'fill-primary') ?>" style="width: <?= min(100, $pctBooked) ?>%;"></div>
              </div>
            </div>

            <!-- Card Action (Fetch API AJAX or Form) -->
            <div class="class-card-footer">
              <?php if ($isBooked): ?>
                <span class="btn-card btn-already-booked">
                  <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="20 6 9 17 4 12"></polyline></svg>
                  Already Booked
                </span>
              <?php elseif ($c['is_full']): ?>
                <button type="button" class="btn-card btn-disabled" disabled>Fully Booked</button>
              <?php else: ?>
                <button type="button" 
                        class="btn-card btn-book-ajax" 
                        data-class-id="<?= (int)$c['class_id'] ?>" 
                        data-class-name="<?= View::e($c['name']) ?>">
                  Book This Class &rarr;
                </button>
              <?php endif; ?>
            </div>
          </div>
        <?php endforeach; ?>
      <?php else: ?>
        <div class="empty-state-box">
          <p>No classes scheduled yet. Please run database seed or add a class.</p>
        </div>
      <?php endif; ?>
    </div>

    <div class="section-footer-cta">
      <a href="<?= View::e($base) ?>/classes" class="btn-outline-pill">View All <?= (int)$classCount ?> Scheduled Classes &rarr;</a>
    </div>

  </div>
</section>

<!-- ========================================================
     TRAINERS SHOWCASE SECTION
     ======================================================== -->
<section class="section-trainers">
  <div class="content-container">
    <div class="text-center-wrapper">
      <span class="section-pill-tag">MEET THE MASTERS</span>
      <h2 class="section-heading">Certified Elite Coaches</h2>
      <p class="section-subtext">Passionate fitness specialists committed to helping you reach peak health and performance.</p>
    </div>

    <div class="trainers-grid">
      <?php foreach ($trainers as $t): ?>
        <div class="trainer-card">
          <div class="trainer-avatar-box">
            <!-- Initials Avatar with dynamic accent -->
            <div class="trainer-initials">
              <?= strtoupper(substr($t['full_name'], 0, 1)) . strtoupper(substr(strstr($t['full_name'], ' ') ?: ' ', 1, 1)) ?>
            </div>
          </div>
          <div class="trainer-body">
            <h3 class="trainer-name"><?= View::e($t['full_name']) ?></h3>
            <span class="trainer-spec"><?= View::e($t['specialization']) ?></span>
            <div class="trainer-phone">
              <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72 12.84 12.84 0 0 0 .7 2.81 2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45 12.84 12.84 0 0 0 2.81.7A2 2 0 0 1 22 16.92z"></path></svg>
              <span><?= View::e($t['phone']) ?></span>
            </div>
            <div class="trainer-footer">
              <span class="class-tally"><?= (int)($t['class_count'] ?? 0) ?> active classes</span>
              <a href="<?= View::e($base) ?>/classes?q=<?= urlencode($t['full_name']) ?>" class="btn-link-coach">View Schedule &rarr;</a>
            </div>
          </div>
        </div>
      <?php endforeach; ?>
    </div>
  </div>
</section>


<!-- Quick Booking Modal (Handled by app.js via Fetch API) -->
<div class="modal-backdrop" id="booking-modal" style="display: none;">
  <div class="modal-dialog">
    <div class="modal-header">
      <h3 class="modal-title" id="modal-class-title">Confirm Class Reservation</h3>
      <button type="button" class="modal-close-btn" id="modal-close-btn">&times;</button>
    </div>
    <div class="modal-body">
      <p id="modal-class-details" class="modal-info-text">Please confirm your reservation for this class.</p>
      <div class="modal-alert" id="modal-alert-box" style="display: none;"></div>
    </div>
    <div class="modal-footer">
      <button type="button" class="btn-secondary" id="modal-cancel-btn">Cancel</button>
      <button type="button" class="btn-orange-pill" id="modal-confirm-btn">
        <span class="btn-text">Confirm Booking</span>
        <span class="spinner-small" id="modal-spinner" style="display: none;"></span>
      </button>
    </div>
  </div>
</div>
