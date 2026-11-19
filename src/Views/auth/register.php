<?php
use App\Support\View;
use App\Services\Csrf;

$base = $baseUrl ?? '/online-gym/public';
?>

<div class="auth-wrapper">
  <div class="auth-card">
    <div class="auth-header">
      <div class="auth-icon-badge">
        <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M16 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path><circle cx="8.5" cy="7" r="4"></circle><line x1="20" y1="8" x2="20" y2="14"></line><line x1="23" y1="11" x2="17" y2="11"></line></svg>
      </div>
      <h2 class="auth-title">Create Member Account</h2>
      <p class="auth-subtitle">Join the BraVaa Fitness community and reserve high-energy fitness classes.</p>
    </div>

    <form action="<?= View::e($base) ?>/register/process" method="POST" class="auth-form" novalidate id="register-form">
      <?= Csrf::field() ?>

      <div class="form-group">
        <label for="reg-username" class="form-label">Username</label>
        <input type="text" 
               name="username" 
               id="reg-username" 
               class="form-control<?= !empty($errors['username']) ? ' is-invalid' : '' ?>" 
               value="<?= View::e($old['username'] ?? '') ?>" 
               placeholder="e.g. shalom_fit" 
               required 
               minlength="3" 
               maxlength="50">
        <?php if (!empty($errors['username'])): ?>
          <span class="field-error-msg"><?= View::e($errors['username']) ?></span>
        <?php endif; ?>
        <span class="client-error-msg" id="reg-username-error"></span>
      </div>

      <div class="form-group">
        <label for="reg-email" class="form-label">Email Address</label>
        <input type="email" 
               name="email" 
               id="reg-email" 
               class="form-control<?= !empty($errors['email']) ? ' is-invalid' : '' ?>" 
               value="<?= View::e($old['email'] ?? '') ?>" 
               placeholder="e.g. yourname@example.com" 
               required>
        <?php if (!empty($errors['email'])): ?>
          <span class="field-error-msg"><?= View::e($errors['email']) ?></span>
        <?php endif; ?>
        <span class="client-error-msg" id="reg-email-error"></span>
      </div>

      <div class="form-group">
        <label for="reg-password" class="form-label">Password (min 6 characters)</label>
        <input type="password" 
               name="password" 
               id="reg-password" 
               class="form-control<?= !empty($errors['password']) ? ' is-invalid' : '' ?>" 
               placeholder="Choose a secure password" 
               required 
               minlength="6">
        <?php if (!empty($errors['password'])): ?>
          <span class="field-error-msg"><?= View::e($errors['password']) ?></span>
        <?php endif; ?>
        <span class="client-error-msg" id="reg-password-error"></span>
      </div>

      <button type="submit" class="btn-submit-block">Register &amp; Get Started &rarr;</button>
    </form>

    <div class="auth-footer-text">
      <span>Already have an account?</span>
      <a href="<?= View::e($base) ?>/login" class="auth-link">Sign in here</a>
    </div>
  </div>
</div>
