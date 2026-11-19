<?php
use App\Support\View;
use App\Services\Csrf;

$base = $baseUrl ?? '/online-gym/public';
$isAdmin = $user && $user['role'] === 'admin';
$isTrainer = $user && $user['role'] === 'trainer';
$isMember = $user && $user['role'] === 'member';
?>

<div class="content-container app-layout-container">
  
  <!-- Header Bar -->
  <div class="page-header-row">
    <div>
      <span class="section-pill-tag">DOMAIN ENTITY 3</span>
      <h1 class="page-title"><?= $isMember ? 'My Class Reservations' : 'Class Bookings Management' ?></h1>
      <p class="page-subtitle"><?= $isMember ? 'View your booked workouts, verify attendance status, or cancel a session.' : 'Manage attendee reservations, verify capacities, and update attendance logs.' ?></p>
    </div>

    <div class="header-action-group">
      <div class="search-input-wrapper">
        <svg class="search-icon" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="11" cy="11" r="8"></circle><line x1="21" y1="21" x2="16.65" y2="16.65"></line></svg>
        <input type="text" id="bookings-search-input" class="search-field" placeholder="Search reservation..." autocomplete="off">
      </div>
      <?php if ($isMember): ?>
        <a href="<?= View::e($base) ?>/classes" class="btn-orange-pill">+ Book Another Class</a>
      <?php elseif ($isAdmin): ?>
        <a href="#booking-form-card" class="btn-orange-pill">+ Create Reservation</a>
      <?php endif; ?>
    </div>
  </div>

  <!-- Summary KPI Badges -->
  <div class="stats-pills-row">
    <div class="stat-pill-card">
      <span class="stat-pill-num"><?= (int)($counts['total'] ?? count($bookings)) ?></span>
      <span class="stat-pill-label">Total Reservations</span>
    </div>
    <div class="stat-pill-card stat-confirmed">
      <span class="stat-pill-num"><?= (int)($counts['confirmed'] ?? 0) ?></span>
      <span class="stat-pill-label">Confirmed Upcoming</span>
    </div>
    <div class="stat-pill-card stat-attended">
      <span class="stat-pill-num"><?= (int)($counts['attended'] ?? 0) ?></span>
      <span class="stat-pill-label">Attended</span>
    </div>
    <div class="stat-pill-card stat-cancelled">
      <span class="stat-pill-num"><?= (int)($counts['cancelled'] ?? 0) ?></span>
      <span class="stat-pill-label">Cancelled</span>
    </div>
  </div>

  <div class="app-split-grid<?= $isAdmin ? ($editing ? ' is-editing' : '') : ' single-column-grid' ?>">
    
    <!-- Left Column: Bookings Table -->
    <div class="app-main-col">
      <div class="data-card">
        <div class="data-card-header">
          <div class="card-title-box">
            <h3 class="card-title"><?= $isMember ? 'Your Scheduled Classes (' . count($bookings) . ')' : 'All Gym Reservations (' . count($bookings) . ')' ?></h3>
            <span class="card-sub"><?= $isMember ? 'Real-time check-in status' : 'Admin & Coach Reservation Roster' ?></span>
          </div>
          <span class="badge-tag">bookings table</span>
        </div>

        <div class="table-responsive">
          <table class="data-table" id="bookings-table">
            <thead>
              <tr>
                <th>Reservation ID</th>
                <th>Fitness Class</th>
                <?php if (!$isMember): ?>
                  <th>Member / User</th>
                <?php endif; ?>
                <th>Class Schedule</th>
                <th>Booking Date</th>
                <th>Status</th>
                <th>Action</th>
              </tr>
            </thead>
            <tbody>
              <?php if (!empty($bookings)): ?>
                <?php foreach ($bookings as $b): 
                  $status = (string)$b['status'];
                  $statusClass = match($status) {
                    'confirmed' => 'badge-success',
                    'attended'  => 'badge-info',
                    'cancelled' => 'badge-danger',
                    default     => 'badge-muted'
                  };
                  $searchMeta = strtolower(
                    $b['class_name'] . ' ' . 
                    ($b['member_username'] ?? '') . ' ' . 
                    ($b['member_email'] ?? '') . ' ' . 
                    $b['trainer_name'] . ' ' . 
                    $status
                  );
                ?>
                  <tr data-search="<?= View::e($searchMeta) ?>">
                    <td>
                      <span class="font-mono text-sm">#<?= (int)$b['booking_id'] ?></span>
                    </td>
                    <td>
                      <div class="table-primary-text font-bold"><?= View::e($b['class_name']) ?></div>
                      <span class="text-xs text-muted">Coach: <?= View::e($b['trainer_name']) ?></span>
                    </td>
                    <?php if (!$isMember): ?>
                      <td>
                        <div class="table-primary-text"><?= View::e($b['member_username']) ?></div>
                        <span class="text-xs text-muted"><?= View::e($b['member_email']) ?></span>
                      </td>
                    <?php endif; ?>
                    <td>
                      <span class="schedule-pill-sm"><?= View::formatDateTime((string)$b['schedule_time']) ?></span>
                    </td>
                    <td>
                      <span class="text-xs text-muted"><?= View::formatDateTime((string)$b['booked_at']) ?></span>
                    </td>
                    <td>
                      <span class="badge-status <?= $statusClass ?>">
                        <?= ucfirst($status) ?>
                      </span>
                    </td>
                    <td>
                      <div class="table-actions-cell">
                        <?php if ($isMember): ?>
                          <?php if ($status === 'confirmed'): ?>
                            <!-- Member can cancel their upcoming reservation -->
                            <form action="<?= View::e($base) ?>/bookings/status" method="POST" class="delete-form d-inline" data-label="Cancel reservation for <?= View::e($b['class_name']) ?>">
                              <?= Csrf::field() ?>
                              <input type="hidden" name="id" value="<?= (int)$b['booking_id'] ?>">
                              <input type="hidden" name="status" value="cancelled">
                              <button type="submit" class="btn-cancel-reservation" title="Cancel Reservation">Cancel</button>
                            </form>
                          <?php else: ?>
                            <span class="text-xs text-muted">&mdash;</span>
                          <?php endif; ?>
                        <?php else: ?>
                          <!-- Admin & Trainer Quick Status Changers -->
                          <?php if ($status !== 'attended'): ?>
                            <form action="<?= View::e($base) ?>/bookings/status" method="POST" class="d-inline">
                              <?= Csrf::field() ?>
                              <input type="hidden" name="id" value="<?= (int)$b['booking_id'] ?>">
                              <input type="hidden" name="status" value="attended">
                              <button type="submit" class="btn-status-toggle btn-mark-attended" title="Mark as Attended">Attended</button>
                            </form>
                          <?php endif; ?>

                          <?php if ($status !== 'cancelled'): ?>
                            <form action="<?= View::e($base) ?>/bookings/status" method="POST" class="d-inline">
                              <?= Csrf::field() ?>
                              <input type="hidden" name="id" value="<?= (int)$b['booking_id'] ?>">
                              <input type="hidden" name="status" value="cancelled">
                              <button type="submit" class="btn-status-toggle btn-mark-cancelled" title="Mark as Cancelled">Cancel</button>
                            </form>
                          <?php endif; ?>

                          <?php if ($isAdmin): ?>
                            <!-- Edit Button -->
                            <a href="<?= View::e($base) ?>/bookings?edit=<?= (int)$b['booking_id'] ?>#booking-form-card" class="btn-action btn-action-edit" title="Edit Booking">
                              <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"></path><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"></path></svg>
                            </a>

                            <!-- Delete Form -->
                            <form action="<?= View::e($base) ?>/bookings/delete" method="POST" class="delete-form d-inline" data-label="Reservation #<?= (int)$b['booking_id'] ?>">
                              <?= Csrf::field() ?>
                              <input type="hidden" name="id" value="<?= (int)$b['booking_id'] ?>">
                              <button type="submit" class="btn-action btn-action-delete" title="Permanently Delete">
                                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="3 6 5 6 21 6"></polyline><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"></path></svg>
                              </button>
                            </form>
                          <?php endif; ?>
                        <?php endif; ?>
                      </div>
                    </td>
                  </tr>
                <?php endforeach; ?>
              <?php else: ?>
                <tr>
                  <td colspan="<?= $isMember ? '5' : '7' ?>" class="table-empty">
                    <?= $isMember ? 'You haven\'t reserved any classes yet.' : 'No booking reservations on file.' ?>
                    <div style="margin-top: 0.5rem;">
                      <a href="<?= View::e($base) ?>/classes" class="btn-orange-pill">Explore Classes &rarr;</a>
                    </div>
                  </td>
                </tr>
              <?php endif; ?>
            </tbody>
          </table>
        </div>
      </div>
    </div>

    <!-- Right Column: Admin Booking Create/Edit Form -->
    <?php if ($isAdmin): ?>
      <div class="app-side-col">
        <div class="form-card<?= $editing ? ' is-editing-mode' : '' ?>" id="booking-form-card">
          <div class="form-card-header">
            <?php if ($editing): ?>
              <div class="editing-pill-tag">
                <span>Mode: Editing Reservation #<?= (int)$editing['booking_id'] ?></span>
                <a href="<?= View::e($base) ?>/bookings" class="btn-cancel-edit-sm" title="Exit editing">&times; Cancel Edit</a>
              </div>
            <?php endif; ?>
            <h3 class="form-card-title"><?= $editing ? 'Edit Reservation #' . (int)$editing['booking_id'] : 'Manual Booking' ?></h3>
            <p class="form-card-sub"><?= $editing ? 'Modify assigned member or class.' : 'Reserve a class spot on behalf of a member.' ?></p>
          </div>

          <form action="<?= View::e($base) ?>/bookings/save" method="POST" novalidate id="booking-form">
            <?= Csrf::field() ?>
            <?php if ($editing): ?>
              <input type="hidden" name="id" value="<?= (int)$editing['booking_id'] ?>">
            <?php endif; ?>

            <!-- Class Select (FK constraint) -->
            <div class="form-group">
              <label for="book-class-id" class="form-label">Fitness Class (FK &rarr; classes) *</label>
              <select name="class_id" id="book-class-id" class="form-control<?= !empty($errors['class_id']) ? ' is-invalid' : '' ?>" required>
                <option value="">-- Choose Class --</option>
                <?php 
                  $selClass = (int)($old['class_id'] ?? ($editing['class_id'] ?? 0));
                  foreach ($classes as $c): 
                ?>
                  <option value="<?= (int)$c['class_id'] ?>" <?= $selClass === (int)$c['class_id'] ? 'selected' : '' ?>>
                    <?= View::e($c['name']) ?> (<?= View::formatDateTime((string)$c['schedule_time']) ?>)
                  </option>
                <?php endforeach; ?>
              </select>
              <?php if (!empty($errors['class_id'])): ?>
                <span class="field-error-msg"><?= View::e($errors['class_id']) ?></span>
              <?php endif; ?>
            </div>

            <!-- Member Select (FK constraint) -->
            <div class="form-group">
              <label for="book-member-id" class="form-label">Member (FK &rarr; users) *</label>
              <select name="member_id" id="book-member-id" class="form-control<?= !empty($errors['member_id']) ? ' is-invalid' : '' ?>" required>
                <option value="">-- Choose Member --</option>
                <?php 
                  $selMember = (int)($old['member_id'] ?? ($editing['member_id'] ?? 0));
                  foreach ($members as $m): 
                ?>
                  <option value="<?= (int)$m['user_id'] ?>" <?= $selMember === (int)$m['user_id'] ? 'selected' : '' ?>>
                    <?= View::e($m['username']) ?> &bull; <?= View::e($m['email']) ?>
                  </option>
                <?php endforeach; ?>
              </select>
              <?php if (!empty($errors['member_id'])): ?>
                <span class="field-error-msg"><?= View::e($errors['member_id']) ?></span>
              <?php endif; ?>
            </div>

            <!-- Status -->
            <div class="form-group">
              <label for="book-status" class="form-label">Reservation Status *</label>
              <?php $selStatus = $old['status'] ?? ($editing['status'] ?? 'confirmed'); ?>
              <select name="status" id="book-status" class="form-control<?= !empty($errors['status']) ? ' is-invalid' : '' ?>">
                <option value="confirmed" <?= $selStatus === 'confirmed' ? 'selected' : '' ?>>Confirmed</option>
                <option value="attended" <?= $selStatus === 'attended' ? 'selected' : '' ?>>Attended</option>
                <option value="cancelled" <?= $selStatus === 'cancelled' ? 'selected' : '' ?>>Cancelled</option>
              </select>
              <?php if (!empty($errors['status'])): ?>
                <span class="field-error-msg"><?= View::e($errors['status']) ?></span>
              <?php endif; ?>
            </div>

            <div class="form-actions-row">
              <button type="submit" class="btn-orange-pill">
                <?= $editing ? 'Save Changes' : 'Confirm Reservation' ?>
              </button>
              <?php if ($editing): ?>
                <a href="<?= View::e($base) ?>/bookings" class="btn-secondary">Cancel</a>
              <?php endif; ?>
            </div>
          </form>
        </div>
      </div>
    <?php endif; ?>

  </div>
</div>
