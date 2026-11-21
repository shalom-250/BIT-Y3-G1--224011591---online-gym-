<?php use App\Support\View; $base = $baseUrl ?? '/online-gym/public'; ?>
</main>

<footer class="footer">
  <div class="footer-container">
    <div class="footer-grid">
      <!-- Col 1: Brand & Bio -->
      <div class="footer-col">
        <a href="<?= View::e($base) ?>/" class="brand-logo footer-logo">
          <span class="logo-text">Bra<span class="logo-accent">Gym</span></span>
          <span class="logo-sub">FITNESS</span>
        </a>
        <p class="footer-desc">
          Empowering your transformation through world-class trainers, high-performance group classes, and seamless scheduling.
        </p>
        <div class="footer-badges">
          <span class="pill-badge-sm">Premium Gym Experience</span>
        </div>
      </div>

      <!-- Col 2: Navigation -->
      <div class="footer-col">
        <h4 class="footer-heading">Quick Links</h4>
        <ul class="footer-links">
          <li><a href="<?= View::e($base) ?>/">Home Overview</a></li>
          <li><a href="<?= View::e($base) ?>/classes">Browse Classes</a></li>
          <li><a href="<?= View::e($base) ?>/trainers">Meet The Coaches</a></li>
          <li><a href="<?= View::e($base) ?>/bookings">Reservations</a></li>
        </ul>
      </div>

      <!-- Col 3: Programs -->
      <div class="footer-col">
        <h4 class="footer-heading">Training Programs</h4>
        <ul class="footer-links">
          <li><a href="<?= View::e($base) ?>/classes?q=battle">Battle Ropes & Cardio</a></li>
          <li><a href="<?= View::e($base) ?>/classes?q=yoga">Power Vinyasa Yoga</a></li>
          <li><a href="<?= View::e($base) ?>/classes?q=strength">Barbell Strength</a></li>
          <li><a href="<?= View::e($base) ?>/classes?q=hiit">Full Body Metabolic HIIT</a></li>
        </ul>
      </div>

      <!-- Col 4: Student Project & Location Info -->
      <div class="footer-col">
        <h4 class="footer-heading">Project & Student Info</h4>
        <div class="student-info-card">
          <p><strong>Student:</strong> NIYONKURU SHALOM</p>
          <p><strong>Reg. No:</strong> 224011591</p>
          <p><strong>Course:</strong> Web Programming / Day 1 – 10</p>
          <p><strong>Location:</strong> Huye Hub · Open 05:00 - 22:00</p>
        </div>
      </div>
    </div>

    <div class="footer-bottom">
      <p>&copy; <?= date('Y') ?> BraGym Fitness. All rights reserved. Built with pure Vanilla PHP, JavaScript &amp; CSS.</p>
      <div class="footer-tech-stack">
        <span>PHP 8.2+ PDO</span> &bull; 
        <span>MySQL 3NF</span> &bull; 
        <span>Fetch API</span> &bull; 
        <span>CSRF &amp; XSS Protected</span>
      </div>
    </div>
  </div>
</footer>

<script src="<?= View::e($base) ?>/assets/app.js"></script>
</body>
</html>
