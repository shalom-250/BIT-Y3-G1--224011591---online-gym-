<?php
use App\Support\View;
use App\Services\Csrf;
use App\Services\AuthGuard;

$currentUser = $user ?? AuthGuard::user();
$currentUri  = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH);
$base        = $baseUrl ?? '/online-gym/public';
?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title><?= View::e($title ?? 'BraGym Fitness — Online Gym Class Booking') ?></title>
  
  <!-- Modern Inter typography -->
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
  
  <link rel="stylesheet" href="<?= View::e($base) ?>/assets/style.css">
  <meta name="csrf-token" content="<?= View::e(Csrf::token()) ?>">
</head>
<body>

<!-- Navigation Bar -->
<header class="navbar" id="main-nav">
  <div class="nav-container">
    <a href="<?= View::e($base) ?>/" class="brand-logo">
      <span class="logo-text">Bra<span class="logo-accent">Gym</span></span>
      <span class="logo-sub">FITNESS</span>
    </a>

    <button class="nav-toggle" id="nav-toggle-btn" aria-label="Toggle navigation" aria-expanded="false">
      <span class="bar"></span>
      <span class="bar"></span>
      <span class="bar"></span>
    </button>

    <nav class="nav-menu" id="nav-menu">
      <ul class="nav-links">
        <li>
          <a href="<?= View::e($base) ?>/" class="nav-item<?= (rtrim($currentUri, '/') === rtrim($base, '/') || rtrim($currentUri, '/') === rtrim($base, '') . '/index.php') ? ' active' : '' ?>">Home</a>
        </li>
        <li>
          <a href="<?= View::e($base) ?>/classes" class="nav-item<?= str_contains($currentUri, '/classes') ? ' active' : '' ?>">Classes</a>
        </li>
        <li>
          <a href="<?= View::e($base) ?>/trainers" class="nav-item<?= str_contains($currentUri, '/trainers') ? ' active' : '' ?>">Trainers</a>
        </li>
        <?php if ($currentUser): ?>
          <li>
            <a href="<?= View::e($base) ?>/bookings" class="nav-item<?= str_contains($currentUri, '/bookings') ? ' active' : '' ?>">
              <?= $currentUser['role'] === 'member' ? 'My Bookings' : 'All Bookings' ?>
            </a>
          </li>
          <?php if ($currentUser['role'] === 'admin'): ?>
            <li>
              <a href="<?= View::e($base) ?>/users" class="nav-item<?= str_contains($currentUri, '/users') ? ' active' : '' ?>">Users</a>
            </li>
          <?php endif; ?>
        <?php endif; ?>
      </ul>

      <div class="nav-actions">
        <?php if ($currentUser): ?>
          <div class="user-pill">
            <span class="role-badge role-<?= View::e($currentUser['role']) ?>">
              <?= ucfirst(View::e($currentUser['role'])) ?>
            </span>
            <span class="username-display"><?= View::e($currentUser['username']) ?></span>
            <a href="<?= View::e($base) ?>/logout" class="btn-link-subtle" title="Sign out">
              <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"></path><polyline points="16 17 21 12 16 7"></polyline><line x1="21" y1="12" x2="9" y2="12"></line></svg>
            </a>
          </div>
        <?php else: ?>
          <a href="<?= View::e($base) ?>/login" class="btn-ghost">Sign In</a>
          <a href="<?= View::e($base) ?>/register" class="btn-orange-pill">Sign Up</a>
        <?php endif; ?>
      </div>
    </nav>
  </div>
</header>

<!-- Global Flash Alert Notification -->
<?php if (!empty($flash)): ?>
  <div class="flash-wrapper">
    <div class="flash flash-<?= View::e($flash['type'] ?? 'info') ?>" role="alert">
      <div class="flash-icon">
        <?php if ($flash['type'] === 'success'): ?>
          <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="20 6 9 17 4 12"></polyline></svg>
        <?php elseif ($flash['type'] === 'error'): ?>
          <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><circle cx="12" cy="12" r="10"></circle><line x1="12" y1="8" x2="12" y2="12"></line><line x1="12" y1="16" x2="12.01" y2="16"></line></svg>
        <?php else: ?>
          <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><circle cx="12" cy="12" r="10"></circle><line x1="12" y1="16" x2="12" y2="12"></line><line x1="12" y1="8" x2="12.01" y2="8"></line></svg>
        <?php endif; ?>
      </div>
      <div class="flash-message"><?= View::e($flash['message']) ?></div>
      <button type="button" class="flash-close" aria-label="Dismiss">&times;</button>
    </div>
  </div>
<?php endif; ?>

<main class="page-container">
