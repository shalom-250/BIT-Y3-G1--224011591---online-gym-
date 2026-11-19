<?php
use App\Support\View;
use App\Services\Csrf;

$base      = $baseUrl ?? '/online-gym/public';
$isAdmin   = $user && $user['role'] === 'admin';
$canManage = $user && in_array($user['role'], ['admin', 'trainer'], true);
?>

<div class="content-container app-layout-container">
  
  <!-- Header Bar -->
  <div class="page-header-row">
    <div>
      <span class="section-pill-tag">DOMAIN ENTITY 1</span>
      <h1 class="page-title">Fitness Classes Schedule</h1>
      <p class="page-subtitle">Explore upcoming group classes, live capacities, and certified coach assignments.</p>
    </div>

    <!-- Live Search -->
    <div class="header-action-group">
      <div class="search-input-wrapper">
        <svg class="search-icon" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="11" cy="11" r="8"></circle><line x1="21" y1="21" x2="16.65" y2="16.65"></line></svg>
        <input type="text" id="classes-search-input" class="search-field" placeholder="Live search class or coach..." value="<?= View::e($searchQuery ?? '') ?>" autocomplete="off">
      </div>
      <?php if ($canManage): ?>
        <a href="#class-form-card" class="btn-orange-pill">+ Schedule New Class</a>
      <?php endif; ?>
    </div>
  </div>

  <div class="app-split-grid<?= $editing ? ' is-editing' : '' ?>">
    
    <!-- Left Column: Classes Table / List -->
    <div class="app-main-col">
      <div class="data-card">
        <div class="data-card-header">
          <div class="card-title-box">
            <h3 class="card-title">Available Classes (<?= count($classes) ?>)</h3>
            <span class="card-sub">
              <?php if ($isAdmin): ?>
                Full Admin Controls — Assign Coaches &amp; Manage All Classes
              <?php elseif ($canManage): ?>
                Trainer Access — Edit Class Details (Coach Assignment by Admin Only)
              <?php else: ?>
                Instant Reservation System
              <?php endif; ?>
            </span>
          </div>
          <span class="badge-tag">classes table</span>
        </div>

        <div class="table-responsive">
          <table class="data-table" id="classes-table">
            <thead>
              <tr>
                <th style="width: 38%;">Class &amp; Coach</th>
                <th style="width: 24%;">Schedule</th>
                <th style="width: 22%;">Capacity</th>
                <th style="width: 16%; text-align: right;">Actions</th>
              </tr>
            </thead>
            <tbody>
              <?php if (!empty($classes)): ?>
                <?php foreach ($classes as $c): 
                  $isBooked = in_array((int)$c['class_id'], $userBookedClassIds, true);
                  $pct = $c['capacity'] > 0 ? round(($c['booked_count'] / $c['capacity']) * 100) : 0;
                  $searchMeta = strtolower($c['name'] . ' ' . $c['trainer_name'] . ' ' . $c['trainer_specialization']);
                  $isCurrentEditing = $editing && (int)$editing['class_id'] === (int)$c['class_id'];
                ?>
                  <tr data-search="<?= View::e($searchMeta) ?>" data-class-id="<?= (int)$c['class_id'] ?>" class="<?= $isCurrentEditing ? 'row-editing-active' : '' ?>">
                    <td>
                      <div class="table-primary-text font-bold">
                        <?= View::e($c['name']) ?>
                        <?php if ($isCurrentEditing): ?>
                          <span class="badge-status badge-warning" style="font-size: 0.65rem; margin-left: 0.3rem;">Editing Now</span>
                        <?php endif; ?>
                      </div>
                      <div class="class-instructor-meta">
                        <span class="text-xs text-muted">Coach: <strong><?= View::e($c['trainer_name']) ?></strong> &bull; <?= View::e($c['trainer_specialization']) ?></span>
                        <span class="text-xs text-muted">&bull; ID: #<?= (int)$c['class_id'] ?></span>
                      </div>
                    </td>
                    <td>
                      <div class="schedule-pill">
                        <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"></circle><polyline points="12 6 12 12 16 14"></polyline></svg>
                        <span><?= View::formatDateTime((string)$c['schedule_time']) ?></span>
                      </div>
                    </td>
                    <td>
                      <div class="capacity-cell">
                        <div class="capacity-numbers">
                          <strong><?= (int)$c['booked_count'] ?>/<?= (int)$c['capacity'] ?></strong>
                          <?php if ($c['is_full']): ?>
                            <span class="badge-status badge-danger">FULL</span>
                          <?php else: ?>
                            <span class="badge-status badge-success"><?= (int)$c['remaining_spots'] ?> left</span>
                          <?php endif; ?>
                        </div>
                        <div class="progress-track-sm">
                          <div class="progress-fill <?= $pct >= 100 ? 'fill-full' : ($pct >= 80 ? 'fill-warning' : 'fill-primary') ?>" style="width: <?= min(100, $pct) ?>%;"></div>
                        </div>
                      </div>
                    </td>
                    <td style="text-align: right;">
                      <div class="table-actions-cell" style="justify-content: flex-end;">

                        <?php if ($canManage): ?>
                          <!-- Edit Button — available to both admin and trainer -->
                          <a href="<?= View::e($base) ?>/classes?edit=<?= (int)$c['class_id'] ?>#class-form-card" class="btn-action btn-action-edit" title="Edit Class">
                            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"></path><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"></path></svg>
                            <span>Edit</span>
                          </a>
                        <?php endif; ?>

                        <?php if ($isAdmin): ?>
                          <!-- Delete Form — ADMIN ONLY -->
                          <form action="<?= View::e($base) ?>/classes/delete" method="POST" class="delete-form d-inline" data-label="Class: <?= View::e($c['name']) ?>">
                            <?= Csrf::field() ?>
                            <input type="hidden" name="id" value="<?= (int)$c['class_id'] ?>">
                            <button type="submit" class="btn-action btn-action-delete" title="Delete Class">
                              <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="3 6 5 6 21 6"></polyline><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"></path></svg>
                            </button>
                          </form>
                        <?php endif; ?>

                        <!-- Member Booking Action -->
                        <?php if ($user && $user['role'] === 'member'): ?>
                          <?php if ($isBooked): ?>
                            <span class="badge-status badge-info">Booked</span>
                          <?php elseif ($c['is_full']): ?>
                            <span class="badge-status badge-disabled">Full</span>
                          <?php else: ?>
                            <form action="<?= View::e($base) ?>/bookings/book" method="POST" class="d-inline">
                              <?= Csrf::field() ?>
                              <input type="hidden" name="class_id" value="<?= (int)$c['class_id'] ?>">
                              <button type="submit" class="btn-table-book">Book</button>
                            </form>
                          <?php endif; ?>
                        <?php elseif (!$user): ?>
                          <a href="<?= View::e($base) ?>/login" class="btn-table-book">Book Gym Class</a>
                        <?php endif; ?>

                      </div>
                    </td>
                  </tr>
                <?php endforeach; ?>
              <?php else: ?>
                <tr>
                  <td colspan="4" class="table-empty">No fitness classes currently scheduled.</td>
                </tr>
              <?php endif; ?>
            </tbody>
          </table>
        </div>
      </div>
    </div>

    <!-- Right Column: Add / Edit Form -->
    <?php if ($canManage): ?>
      <div class="app-side-col">
        <div class="form-card<?= $editing ? ' is-editing-mode' : '' ?>" id="class-form-card">
          <div class="form-card-header">
            <?php if ($editing): ?>
              <div class="editing-pill-tag">
                <span>Mode: Editing Class #<?= (int)$editing['class_id'] ?></span>
                <a href="<?= View::e($base) ?>/classes" class="btn-cancel-edit-sm" title="Exit editing">&times; Cancel Edit</a>
              </div>
            <?php endif; ?>
            <h3 class="form-card-title"><?= $editing ? 'Edit Class #' . (int)$editing['class_id'] : 'Schedule New Class' ?></h3>
            <p class="form-card-sub">
              <?php if ($editing && !$isAdmin): ?>
                Update class name, schedule &amp; capacity. <strong>Coach assignment is managed by Admin.</strong>
              <?php elseif ($editing): ?>
                Update class parameters and trainer assignment.
              <?php else: ?>
                Create a new fitness session for members.
              <?php endif; ?>
            </p>
          </div>

          <form action="<?= View::e($base) ?>/classes/save" method="POST" novalidate id="class-form">
            <?= Csrf::field() ?>
            <?php if ($editing): ?>
              <input type="hidden" name="id" value="<?= (int)$editing['class_id'] ?>">
            <?php endif; ?>

            <!-- Class Name -->
            <div class="form-group">
              <label for="class-name" class="form-label">Class Name *</label>
              <input type="text" 
                     name="name" 
                     id="class-name" 
                     class="form-control<?= !empty($errors['name']) ? ' is-invalid' : '' ?>" 
                     value="<?= View::e($old['name'] ?? ($editing['name'] ?? '')) ?>" 
                     placeholder="e.g. Battle Ropes Conditioning" 
                     required>
              <?php if (!empty($errors['name'])): ?>
                <span class="field-error-msg"><?= View::e($errors['name']) ?></span>
              <?php endif; ?>
            </div>

            <!-- Trainer / Coach Assignment -->
            <div class="form-group">
              <label for="class-trainer" class="form-label">
                Assigned Coach (FK &rarr; trainers) *
                <?php if (!$isAdmin && $editing): ?>
                  <span class="badge-status badge-warning" style="margin-left:.4rem; font-size:.65rem; vertical-align:middle;">Admin-Only Field</span>
                <?php endif; ?>
              </label>

              <?php if ($isAdmin): ?>
                <!-- Admin: full dropdown to assign any trainer -->
                <select name="trainer_id" id="class-trainer" class="form-control<?= !empty($errors['trainer_id']) ? ' is-invalid' : '' ?>" required>
                  <option value="">-- Select Trainer --</option>
                  <?php 
                    $selectedTrainer = (int)($old['trainer_id'] ?? ($editing['trainer_id'] ?? 0));
                    foreach ($trainers as $t): 
                  ?>
                    <option value="<?= (int)$t['trainer_id'] ?>" <?= $selectedTrainer === (int)$t['trainer_id'] ? 'selected' : '' ?>>
                      <?= View::e($t['full_name']) ?> (<?= View::e($t['specialization']) ?>)
                    </option>
                  <?php endforeach; ?>
                </select>
                <?php if (!empty($errors['trainer_id'])): ?>
                  <span class="field-error-msg"><?= View::e($errors['trainer_id']) ?></span>
                <?php endif; ?>

              <?php elseif ($editing): ?>
                <!-- Trainer editing existing class: show current coach as locked read-only -->
                <div class="form-control-readonly" style="background:#f8fafc; border:1px solid #e2e8f0; border-radius:.5rem; padding:.6rem .9rem; display:flex; align-items:center; gap:.5rem;">
                  <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="#64748b" stroke-width="2"><rect x="3" y="11" width="18" height="11" rx="2" ry="2"></rect><path d="M7 11V7a5 5 0 0 1 10 0v4"></path></svg>
                  <span style="color:#334155; font-weight:600;"><?= View::e($editing['trainer_name']) ?></span>
                  <span style="color:#94a3b8; font-size:.8rem;">(<?= View::e($editing['trainer_specialization']) ?>)</span>
                  <input type="hidden" name="trainer_id" value="<?= (int)$editing['trainer_id'] ?>">
                </div>
                <span class="field-error-msg" style="color:#f97316; font-size:.75rem;">Only Admin can reassign the coach for this class.</span>

              <?php else: ?>
                <!-- Trainer creating new class: allow selecting a trainer -->
                <select name="trainer_id" id="class-trainer" class="form-control<?= !empty($errors['trainer_id']) ? ' is-invalid' : '' ?>" required>
                  <option value="">-- Select Trainer --</option>
                  <?php 
                    $selectedTrainer = (int)($old['trainer_id'] ?? 0);
                    foreach ($trainers as $t): 
                  ?>
                    <option value="<?= (int)$t['trainer_id'] ?>" <?= $selectedTrainer === (int)$t['trainer_id'] ? 'selected' : '' ?>>
                      <?= View::e($t['full_name']) ?> (<?= View::e($t['specialization']) ?>)
                    </option>
                  <?php endforeach; ?>
                </select>
                <?php if (!empty($errors['trainer_id'])): ?>
                  <span class="field-error-msg"><?= View::e($errors['trainer_id']) ?></span>
                <?php endif; ?>
              <?php endif; ?>
            </div>

            <!-- Schedule Time -->
            <div class="form-group">
              <label for="class-schedule" class="form-label">Date &amp; Time *</label>
              <?php
                $timeVal = $old['schedule_time'] ?? ($editing['schedule_time'] ?? '');
                if ($timeVal) {
                  $timeVal = date('Y-m-d\TH:i', strtotime($timeVal));
                }
              ?>
              <input type="datetime-local" 
                     name="schedule_time" 
                     id="class-schedule" 
                     class="form-control<?= !empty($errors['schedule_time']) ? ' is-invalid' : '' ?>" 
                     value="<?= View::e($timeVal) ?>" 
                     required>
              <?php if (!empty($errors['schedule_time'])): ?>
                <span class="field-error-msg"><?= View::e($errors['schedule_time']) ?></span>
              <?php endif; ?>
            </div>

            <!-- Capacity -->
            <div class="form-group">
              <label for="class-capacity" class="form-label">Capacity (Max Participants) *</label>
              <input type="number" 
                     name="capacity" 
                     id="class-capacity" 
                     class="form-control<?= !empty($errors['capacity']) ? ' is-invalid' : '' ?>" 
                     value="<?= View::e($old['capacity'] ?? ($editing['capacity'] ?? 15)) ?>" 
                     min="1" 
                     max="200" 
                     required>
              <?php if (!empty($errors['capacity'])): ?>
                <span class="field-error-msg"><?= View::e($errors['capacity']) ?></span>
              <?php endif; ?>
            </div>

            <!-- Role-based permission notice -->
            <?php if (!$isAdmin && $editing): ?>
              <div class="form-notice-box" style="background:#fff7ed; border:1px solid #fed7aa; border-radius:.5rem; padding:.7rem 1rem; margin-bottom:1rem; font-size:.82rem; color:#9a3412; display:flex; gap:.5rem; align-items:flex-start;">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="flex-shrink:0; margin-top:.1rem;"><circle cx="12" cy="12" r="10"></circle><line x1="12" y1="8" x2="12" y2="12"></line><line x1="12" y1="16" x2="12.01" y2="16"></line></svg>
                <span>As a <strong>Trainer</strong>, you can update the class name, schedule &amp; capacity. To reassign the coach, please contact an <strong>Admin</strong>.</span>
              </div>
            <?php endif; ?>

            <div class="form-actions-row">
              <button type="submit" class="btn-orange-pill">
                <?= $editing ? 'Save Changes' : 'Schedule Class' ?>
              </button>
              <?php if ($editing): ?>
                <a href="<?= View::e($base) ?>/classes" class="btn-secondary">Cancel</a>
              <?php endif; ?>
            </div>
          </form>
        </div>
      </div>
    <?php endif; ?>

  </div>
</div>
