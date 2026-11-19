<?php
use App\Support\View;
use App\Services\Csrf;

$base = $baseUrl ?? '/online-gym/public';
?>

<div class="content-container app-layout-container">
  
  <!-- Header Bar -->
  <div class="page-header-row">
    <div>
      <span class="section-pill-tag">SYSTEM ENTITY 4</span>
      <h1 class="page-title">User Account Administration</h1>
      <p class="page-subtitle">Manage member profiles, trainer accounts, and administrator privileges.</p>
    </div>

    <div class="header-action-group">
      <div class="search-input-wrapper">
        <svg class="search-icon" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="11" cy="11" r="8"></circle><line x1="21" y1="21" x2="16.65" y2="16.65"></line></svg>
        <input type="text" id="users-search-input" class="search-field" placeholder="Search username, email, role..." autocomplete="off">
      </div>
      <a href="#user-form-card" class="btn-orange-pill">+ Add New User</a>
    </div>
  </div>

  <!-- Role KPI Badges -->
  <div class="stats-pills-row">
    <div class="stat-pill-card">
      <span class="stat-pill-num"><?= (int)($counts['total'] ?? count($users)) ?></span>
      <span class="stat-pill-label">Total Accounts</span>
    </div>
    <div class="stat-pill-card stat-confirmed">
      <span class="stat-pill-num"><?= (int)($counts['member'] ?? 0) ?></span>
      <span class="stat-pill-label">Gym Members</span>
    </div>
    <div class="stat-pill-card stat-attended">
      <span class="stat-pill-num"><?= (int)($counts['trainer'] ?? 0) ?></span>
      <span class="stat-pill-label">Trainer Accounts</span>
    </div>
    <div class="stat-pill-card stat-cancelled">
      <span class="stat-pill-num"><?= (int)($counts['admin'] ?? 0) ?></span>
      <span class="stat-pill-label">System Admins</span>
    </div>
  </div>

  <div class="app-split-grid<?= $editing ? ' is-editing' : '' ?>">
    
    <!-- Left Column: Users Table -->
    <div class="app-main-col">
      <div class="data-card">
        <div class="data-card-header">
          <div class="card-title-box">
            <h3 class="card-title">User Accounts (<?= count($users) ?>)</h3>
            <span class="card-sub">Secure BCRYPT Authentication &amp; RBAC</span>
          </div>
          <span class="badge-tag">3NF: `users` table</span>
        </div>

        <div class="table-responsive">
          <table class="data-table" id="users-table">
            <thead>
              <tr>
                <th>User</th>
                <th>Email Address</th>
                <th>System Role</th>
                <th>Member Since</th>
                <th>Actions</th>
              </tr>
            </thead>
            <tbody>
              <?php if (!empty($users)): ?>
                <?php foreach ($users as $u): 
                  $roleClass = match($u['role']) {
                    'admin'   => 'badge-danger',
                    'trainer' => 'badge-info',
                    'member'  => 'badge-success',
                    default   => 'badge-muted'
                  };
                  $searchMeta = strtolower($u['username'] . ' ' . $u['email'] . ' ' . $u['role']);
                  $isSelf = (int)$u['user_id'] === (int)$user['user_id'];
                ?>
                  <tr data-search="<?= View::e($searchMeta) ?>">
                    <td>
                      <div class="trainer-name-cell">
                        <div class="trainer-initials-sm">
                          <?= strtoupper(substr($u['username'], 0, 2)) ?>
                        </div>
                        <div>
                          <div class="table-primary-text font-bold">
                            <?= View::e($u['username']) ?>
                            <?php if ($isSelf): ?>
                              <span class="badge-tag-sm">(You)</span>
                            <?php endif; ?>
                          </div>
                          <span class="text-xs text-muted">ID: #<?= (int)$u['user_id'] ?></span>
                        </div>
                      </div>
                    </td>
                    <td>
                      <span class="font-mono text-sm"><?= View::e($u['email']) ?></span>
                    </td>
                    <td>
                      <span class="badge-status <?= $roleClass ?>">
                        <?= ucfirst(View::e($u['role'])) ?>
                      </span>
                    </td>
                    <td>
                      <span class="text-xs text-muted"><?= View::formatDateTime((string)$u['created_at']) ?></span>
                    </td>
                    <td>
                      <div class="table-actions-cell">
                        <!-- Edit Button -->
                        <a href="<?= View::e($base) ?>/users?edit=<?= (int)$u['user_id'] ?>#user-form-card" class="btn-action btn-action-edit" title="Edit User">
                          <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"></path><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"></path></svg>
                          <span>Edit</span>
                        </a>

                        <!-- Delete Form (Cannot delete self) -->
                        <?php if (!$isSelf): ?>
                          <form action="<?= View::e($base) ?>/users/delete" method="POST" class="delete-form d-inline" data-label="User: <?= View::e($u['username']) ?>">
                            <?= Csrf::field() ?>
                            <input type="hidden" name="id" value="<?= (int)$u['user_id'] ?>">
                            <button type="submit" class="btn-action btn-action-delete" title="Delete User">
                              <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="3 6 5 6 21 6"></polyline><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"></path></svg>
                            </button>
                          </form>
                        <?php endif; ?>
                      </div>
                    </td>
                  </tr>
                <?php endforeach; ?>
              <?php else: ?>
                <tr>
                  <td colspan="5" class="table-empty">No users registered in system.</td>
                </tr>
              <?php endif; ?>
            </tbody>
          </table>
        </div>
      </div>
    </div>

    <!-- Right Column: Add / Edit Form -->
    <div class="app-side-col">
      <div class="form-card<?= $editing ? ' is-editing-mode' : '' ?>" id="user-form-card">
        <div class="form-card-header">
          <?php if ($editing): ?>
            <div class="editing-pill-tag">
              <span>Mode: Editing User #<?= (int)$editing['user_id'] ?></span>
              <a href="<?= View::e($base) ?>/users" class="btn-cancel-edit-sm" title="Exit editing">&times; Cancel Edit</a>
            </div>
          <?php endif; ?>
          <h3 class="form-card-title"><?= $editing ? 'Edit User #' . (int)$editing['user_id'] : 'Create New Account' ?></h3>
          <p class="form-card-sub"><?= $editing ? 'Update credentials or system role.' : 'Register user with bcrypt hashed password.' ?></p>
        </div>

        <form action="<?= View::e($base) ?>/users/save" method="POST" novalidate id="user-form">
          <?= Csrf::field() ?>
          <?php if ($editing): ?>
            <input type="hidden" name="id" value="<?= (int)$editing['user_id'] ?>">
          <?php endif; ?>

          <!-- Username -->
          <div class="form-group">
            <label for="u-username" class="form-label">Username *</label>
            <input type="text" 
                   name="username" 
                   id="u-username" 
                   class="form-control<?= !empty($errors['username']) ? ' is-invalid' : '' ?>" 
                   value="<?= View::e($old['username'] ?? ($editing['username'] ?? '')) ?>" 
                   placeholder="e.g. coach_marcus" 
                   required>
            <?php if (!empty($errors['username'])): ?>
              <span class="field-error-msg"><?= View::e($errors['username']) ?></span>
            <?php endif; ?>
          </div>

          <!-- Email -->
          <div class="form-group">
            <label for="u-email" class="form-label">Email Address *</label>
            <input type="email" 
                   name="email" 
                   id="u-email" 
                   class="form-control<?= !empty($errors['email']) ? ' is-invalid' : '' ?>" 
                   value="<?= View::e($old['email'] ?? ($editing['email'] ?? '')) ?>" 
                   placeholder="e.g. user@gym.com" 
                   required>
            <?php if (!empty($errors['email'])): ?>
              <span class="field-error-msg"><?= View::e($errors['email']) ?></span>
            <?php endif; ?>
          </div>

          <!-- Password -->
          <div class="form-group">
            <label for="u-password" class="form-label">
              <?= $editing ? 'New Password (leave empty to keep current)' : 'Password *' ?>
            </label>
            <input type="password" 
                   name="password" 
                   id="u-password" 
                   class="form-control<?= !empty($errors['password']) ? ' is-invalid' : '' ?>" 
                   placeholder="<?= $editing ? 'Leave blank to preserve password' : 'Min 6 characters' ?>" 
                   <?= $editing ? '' : 'required' ?>>
            <?php if (!empty($errors['password'])): ?>
              <span class="field-error-msg"><?= View::e($errors['password']) ?></span>
            <?php endif; ?>
          </div>

          <!-- Role Select -->
          <div class="form-group">
            <label for="u-role" class="form-label">Role (RBAC) *</label>
            <?php $selRole = $old['role'] ?? ($editing['role'] ?? 'member'); ?>
            <select name="role" id="u-role" class="form-control<?= !empty($errors['role']) ? ' is-invalid' : '' ?>" required>
              <option value="member" <?= $selRole === 'member' ? 'selected' : '' ?>>Member (Books classes)</option>
              <option value="trainer" <?= $selRole === 'trainer' ? 'selected' : '' ?>>Trainer (Manages schedules)</option>
              <option value="admin" <?= $selRole === 'admin' ? 'selected' : '' ?>>Administrator (Full CRUD)</option>
            </select>
            <?php if (!empty($errors['role'])): ?>
              <span class="field-error-msg"><?= View::e($errors['role']) ?></span>
            <?php endif; ?>
          </div>

          <div class="form-actions-row">
            <button type="submit" class="btn-orange-pill">
              <?= $editing ? 'Save Changes' : 'Create Account' ?>
            </button>
            <?php if ($editing): ?>
              <a href="<?= View::e($base) ?>/users" class="btn-secondary">Cancel</a>
            <?php endif; ?>
          </div>
        </form>
      </div>
    </div>

  </div>
</div>
