<?php
use App\Support\View;
use App\Services\Csrf;

$base = $baseUrl ?? '/online-gym/public';
$isAdmin = $user && $user['role'] === 'admin';
?>

<div class="content-container app-layout-container">
  
  <!-- Header Bar -->
  <div class="page-header-row">
    <div>
      <span class="section-pill-tag">DOMAIN ENTITY 2</span>
      <h1 class="page-title">Coaches &amp; Trainers Roster</h1>
      <p class="page-subtitle">Manage certified fitness professionals, specialties, and contact directories.</p>
    </div>

    <div class="header-action-group">
      <div class="search-input-wrapper">
        <svg class="search-icon" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="11" cy="11" r="8"></circle><line x1="21" y1="21" x2="16.65" y2="16.65"></line></svg>
        <input type="text" id="trainers-search-input" class="search-field" placeholder="Search coach or specialization..." autocomplete="off">
      </div>
      <?php if ($isAdmin): ?>
        <a href="#trainer-form-card" class="btn-orange-pill">+ Add New Coach</a>
      <?php endif; ?>
    </div>
  </div>

  <div class="app-split-grid<?= $editing ? ' is-editing' : '' ?>">
    
    <!-- Left Column: Trainers Table / Directory -->
    <div class="app-main-col">
      <div class="data-card">
        <div class="data-card-header">
          <div class="card-title-box">
            <h3 class="card-title">Staff Directory (<?= count($trainers) ?> Coaches)</h3>
            <span class="card-sub"><?= $isAdmin ? 'Admin CRUD Roster Controls' : 'Certified Training Faculty' ?></span>
          </div>
          <span class="badge-tag">trainers table</span>
        </div>

        <div class="table-responsive">
          <table class="data-table" id="trainers-table">
            <thead>
              <tr>
                <th>Coach Name</th>
                <th>Specialization / Discipline</th>
                <th>Contact Phone</th>
                <th>Classes Led</th>
                <?php if ($isAdmin): ?>
                  <th>Actions</th>
                <?php endif; ?>
              </tr>
            </thead>
            <tbody>
              <?php if (!empty($trainers)): ?>
                <?php foreach ($trainers as $t): 
                  $searchMeta = strtolower($t['full_name'] . ' ' . $t['specialization'] . ' ' . $t['phone']);
                ?>
                  <tr data-search="<?= View::e($searchMeta) ?>">
                    <td>
                      <div class="trainer-name-cell">
                        <div class="trainer-initials-sm">
                          <?= strtoupper(substr($t['full_name'], 0, 1)) . strtoupper(substr(strstr($t['full_name'], ' ') ?: ' ', 1, 1)) ?>
                        </div>
                        <div>
                          <div class="table-primary-text font-bold"><?= View::e($t['full_name']) ?></div>
                          <span class="text-xs text-muted">ID: #<?= (int)$t['trainer_id'] ?></span>
                        </div>
                      </div>
                    </td>
                    <td>
                      <span class="spec-pill"><?= View::e($t['specialization']) ?></span>
                    </td>
                    <td>
                      <span class="phone-text"><?= View::e($t['phone']) ?></span>
                    </td>
                    <td>
                      <a href="<?= View::e($base) ?>/classes?q=<?= urlencode($t['full_name']) ?>" class="badge-status badge-info" title="View classes">
                        <?= (int)($t['class_count'] ?? 0) ?> sessions
                      </a>
                    </td>
                    <?php if ($isAdmin): ?>
                      <td>
                        <div class="table-actions-cell">
                          <!-- Edit Button -->
                          <a href="<?= View::e($base) ?>/trainers?edit=<?= (int)$t['trainer_id'] ?>#trainer-form-card" class="btn-action btn-action-edit" title="Edit Trainer">
                            <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"></path><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"></path></svg>
                            <span>Edit</span>
                          </a>

                          <!-- Delete Form -->
                          <form action="<?= View::e($base) ?>/trainers/delete" method="POST" class="delete-form d-inline" data-label="Coach: <?= View::e($t['full_name']) ?>">
                            <?= Csrf::field() ?>
                            <input type="hidden" name="id" value="<?= (int)$t['trainer_id'] ?>">
                            <button type="submit" class="btn-action btn-action-delete" title="Delete Coach">
                              <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="3 6 5 6 21 6"></polyline><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"></path></svg>
                            </button>
                          </form>
                        </div>
                      </td>
                    <?php endif; ?>
                  </tr>
                <?php endforeach; ?>
              <?php else: ?>
                <tr>
                  <td colspan="<?= $isAdmin ? '5' : '4' ?>" class="table-empty">No coaches found in system.</td>
                </tr>
              <?php endif; ?>
            </tbody>
          </table>
        </div>
      </div>
    </div>

    <!-- Right Column: Add / Edit Form (Admin Only) -->
    <?php if ($isAdmin): ?>
      <div class="app-side-col">
        <div class="form-card<?= $editing ? ' is-editing-mode' : '' ?>" id="trainer-form-card">
          <div class="form-card-header">
            <?php if ($editing): ?>
              <div class="editing-pill-tag">
                <span>Mode: Editing Coach #<?= (int)$editing['trainer_id'] ?></span>
                <a href="<?= View::e($base) ?>/trainers" class="btn-cancel-edit-sm" title="Exit editing">&times; Cancel Edit</a>
              </div>
            <?php endif; ?>
            <h3 class="form-card-title"><?= $editing ? 'Edit Coach #' . (int)$editing['trainer_id'] : 'Add New Coach' ?></h3>
            <p class="form-card-sub"><?= $editing ? 'Update coach information.' : 'Register a certified trainer to lead gym sessions.' ?></p>
          </div>

          <form action="<?= View::e($base) ?>/trainers/save" method="POST" novalidate id="trainer-form">
            <?= Csrf::field() ?>
            <?php if ($editing): ?>
              <input type="hidden" name="id" value="<?= (int)$editing['trainer_id'] ?>">
            <?php endif; ?>

            <!-- Full Name -->
            <div class="form-group">
              <label for="trainer-name" class="form-label">Full Name *</label>
              <input type="text" 
                     name="full_name" 
                     id="trainer-name" 
                     class="form-control<?= !empty($errors['full_name']) ? ' is-invalid' : '' ?>" 
                     value="<?= View::e($old['full_name'] ?? ($editing['full_name'] ?? '')) ?>" 
                     placeholder="e.g. Marcus Vance" 
                     required>
              <?php if (!empty($errors['full_name'])): ?>
                <span class="field-error-msg"><?= View::e($errors['full_name']) ?></span>
              <?php endif; ?>
            </div>

            <!-- Specialization -->
            <div class="form-group">
              <label for="trainer-spec" class="form-label">Specialization / Discipline *</label>
              <input type="text" 
                     name="specialization" 
                     id="trainer-spec" 
                     class="form-control<?= !empty($errors['specialization']) ? ' is-invalid' : '' ?>" 
                     value="<?= View::e($old['specialization'] ?? ($editing['specialization'] ?? '')) ?>" 
                     placeholder="e.g. Battle Ropes, HIIT, Yoga, Olympic Lifting" 
                     required>
              <?php if (!empty($errors['specialization'])): ?>
                <span class="field-error-msg"><?= View::e($errors['specialization']) ?></span>
              <?php endif; ?>
            </div>

            <!-- Phone -->
            <div class="form-group">
              <label for="trainer-phone" class="form-label">Phone Number *</label>
              <input type="text" 
                     name="phone" 
                     id="trainer-phone" 
                     class="form-control<?= !empty($errors['phone']) ? ' is-invalid' : '' ?>" 
                     value="<?= View::e($old['phone'] ?? ($editing['phone'] ?? '')) ?>" 
                     placeholder="e.g. +250 788 123 456" 
                     required>
              <?php if (!empty($errors['phone'])): ?>
                <span class="field-error-msg"><?= View::e($errors['phone']) ?></span>
              <?php endif; ?>
            </div>

            <div class="form-actions-row">
              <button type="submit" class="btn-orange-pill">
                <?= $editing ? 'Save Changes' : 'Add Coach' ?>
              </button>
              <?php if ($editing): ?>
                <a href="<?= View::e($base) ?>/trainers" class="btn-secondary">Cancel</a>
              <?php endif; ?>
            </div>
          </form>
        </div>
      </div>
    <?php endif; ?>

  </div>
</div>
