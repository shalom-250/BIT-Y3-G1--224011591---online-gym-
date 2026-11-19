<?php
use App\Support\View;
use App\Services\Csrf;

$base = $baseUrl ?? '/online-gym/public';
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

  <div class="app-split-grid">
    
    <!-- Left Column: Classes Table / List -->
    <div class="app-main-col">
      <div class="data-card">
        <div class="data-card-header">
          <div class="card-title-box">
            <h3 class="card-title">Available Classes (<?= count($classes) ?>)</h3>
            <span class="card-sub"><?= $canManage ? 'Full Admin & Trainer Schedule Controls' : 'Instant Reservation System' ?></span>
          </div>
          <span class="badge-tag">3NF: `classes` table</span>
        </div>

        <div class="table-responsive">
          <table class="data-table" id="classes-table">
            <thead>
              <tr>
                <th>Class Name</th>
                <th>Coach / Specialization</th>
                <th>Schedule Date &amp; Time</th>
                <th>Capacity</th>
                <th>Action</th>
              </tr>
            </thead>
            <tbody>
              <?php if (!empty($classes)): ?>
                <?php foreach ($classes as $c): 
                  $isBooked = in_array((int)$c['class_id'], $userBookedClassIds, true);
                  $pct = $c['capacity'] > 0 ? round(($c['booked_count'] / $c['capacity']) * 100) : 0;
                  $searchMeta = strtolower($c['name'] . ' ' . $c['trainer_name'] . ' ' . $c['trainer_specialization']);
                ?>
                  <tr data-search="<?= View::e($searchMeta) ?>" data-class-id="<?= (int)$c['class_id'] ?>">
                    <td>
                      <div class="table-primary-text font-bold"><?= View::e($c['name']) ?></div>
                      <span class="text-xs text-muted">ID: #<?= (int)$c['class_id'] ?></span>
                    </td>
                    <td>
                      <div class="table-primary-text"><?= View::e($c['trainer_name']) ?></div>
                      <span class="text-xs text-muted"><?= View::e($c['trainer_specialization']) ?></span>
                    </td>
                    <td>
                      <div class="schedule-pill">
                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"></circle><polyline points="12 6 12 12 16 14"></polyline></svg>
                        <span><?= View::formatDateTime((string)$c['schedule_time']) ?></span>
                      </div>
                    </td>
                    <td>
                      <div class="capacity-cell">
                        <div class="capacity-numbers">
                          <strong><?= (int)$c['booked_count'] ?> / <?= (int)$c['capacity'] ?></strong>
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
                    <td>
                      <div class="table-actions-cell">
                        <?php if ($canManage): ?>
                          <!-- Edit Button -->
                          <a href="<?= View::e($base) ?>/classes?edit=<?= (int)$c['class_id'] ?>#class-form-card" class="btn-action btn-action-edit" title="Edit Class">
                            <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"></path><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"></path></svg>
                            <span>Edit</span>
                          </a>

                          <!-- Delete Form -->
                          <form action="<?= View::e($base) ?>/classes/delete" method="POST" class="delete-form d-inline" data-label="Class: <?= View::e($c['name']) ?>">
                            <?= Csrf::field() ?>
                            <input type="hidden" name="id" value="<?= (int)$c['class_id'] ?>">
                            <button type="submit" class="btn-action btn-action-delete" title="Delete Class">
                              <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="3 6 5 6 21 6"></polyline><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"></path></svg>
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
                              <button type="submit" class="btn-table-book">Book Spot</button>
                            </form>
                          <?php endif; ?>
                        <?php elseif (!$user): ?>
                          <a href="<?= View::e($base) ?>/login" class="btn-table-book">Sign in to book</a>
                        <?php endif; ?>
                      </div>
                    </td>
                  </tr>
                <?php endforeach; ?>
              <?php else: ?>
                <tr>
                  <td colspan="5" class="table-empty">No fitness classes currently scheduled.</td>
                </tr>
              <?php endif; ?>
            </tbody>
          </table>
        </div>
      </div>
    </div>

    <!-- Right Column: Add / Edit Form (For Admin & Trainers) -->
    <?php if ($canManage): ?>
      <div class="app-side-col">
        <div class="form-card" id="class-form-card">
          <div class="form-card-header">
            <h3 class="form-card-title"><?= $editing ? 'Edit Class #' . (int)$editing['class_id'] : 'Schedule New Class' ?></h3>
            <p class="form-card-sub"><?= $editing ? 'Update class parameters and trainer.' : 'Create a new fitness session for members.' ?></p>
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

            <!-- Trainer Select (FK constraint) -->
            <div class="form-group">
              <label for="class-trainer" class="form-label">Assigned Coach (FK &rarr; trainers) *</label>
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
