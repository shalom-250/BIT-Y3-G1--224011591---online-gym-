<?php
use App\Support\View;
use App\Services\Csrf;

$base = $baseUrl ?? '/online-gym/public';
?>

<div class="auth-wrapper">
  <div class="auth-card">
    <div class="auth-header">
      <div class="auth-icon-badge">
        <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M15 3h4a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2h-4"></path><polyline points="10 17 15 12 10 7"></polyline><line x1="15" y1="12" x2="3" y2="12"></line></svg>
      </div>
      <h2 class="auth-title">Welcome Back</h2>
      <p class="auth-subtitle">Sign in to your BraVaa Fitness account to book classes and view your schedule.</p>
    </div>

    <?php if (!empty($errors['login'])): ?>
      <div class="form-alert alert-error">
        <?= View::e($errors['login']) ?>
      </div>
    <?php endif; ?>

    <form action="<?= View::e($base) ?>/login/process" method="POST" class="auth-form" novalidate id="login-form">
      <?= Csrf::field() ?>

      <div class="form-group">
        <label for="identifier" class="form-label">Username or Email</label>
        <div class="input-icon-box">
          <input type="text" 
                 name="identifier" 
                 id="identifier" 
                 class="form-control<?= !empty($errors['login']) ? ' is-invalid' : '' ?>" 
                 value="<?= View::e($old['identifier'] ?? '') ?>" 
                 placeholder="e.g. admin or member@gmail.com" 
                 required 
                 autofocus>
        </div>
      </div>

      <div class="form-group">
        <label for="password" class="form-label">Password</label>
        <div class="input-icon-box">
          <input type="password" 
                 name="password" 
                 id="password" 
                 class="form-control<?= !empty($errors['login']) ? ' is-invalid' : '' ?>" 
                 placeholder="Enter your password" 
                 required>
        </div>
      </div>

      <button type="submit" class="btn-submit-block">Sign In &rarr;</button>
    </form>

    <!-- Quick Credentials Demo Card for Markers & Evaluators -->
    <div class="demo-credentials-box">
      <h4 class="demo-title">⚡ Quick Test Accounts (Password: <code>password123</code>)</h4>
      <div class="demo-buttons-grid">
        <button type="button" class="btn-demo-fill" data-user="admin" data-pass="password123">
          <strong>Admin:</strong> admin
        </button>
        <button type="button" class="btn-demo-fill" data-user="marcus_coach" data-pass="password123">
          <strong>Trainer:</strong> marcus_coach
        </button>
        <button type="button" class="btn-demo-fill" data-user="shalom_member" data-pass="password123">
          <strong>Member:</strong> shalom_member
        </button>
      </div>
    </div>

    <div class="auth-footer-text">
      <span>Don't have an account?</span>
      <a href="<?= View::e($base) ?>/register" class="auth-link">Create one now</a>
    </div>
  </div>
</div>
