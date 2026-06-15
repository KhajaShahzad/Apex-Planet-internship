<?php
/**
 * user/dashboard.php
 * Regular user's personal dashboard.
 */


require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../includes/auth.php';

require_login();

$cu = current_user();

// Fetch fresh profile data
$stmt = $conn->prepare(
    'SELECT u.username, u.email, u.bio, u.profile_picture, u.created_at, r.name AS role
     FROM users u JOIN roles r ON r.id=u.role_id
     WHERE u.id=? LIMIT 1'
);
$stmt->bind_param('i', $cu['id']);
$stmt->execute();
$profile = $stmt->get_result()->fetch_assoc();
$stmt->close();

$pageTitle = 'My Dashboard';
include __DIR__ . '/../includes/header.php';
?>

<div class="container">
  <div class="dashboard-grid">

    <!-- Profile Card -->
    <div class="card profile-summary-card">
      <div class="profile-avatar-lg">
        <?php if (!empty($profile['profile_picture'])): ?>
          <img src="<?= base_url($profile['profile_picture']) ?>" alt="Profile picture" />
        <?php else: ?>
          <div class="avatar-placeholder-lg">
            <?= strtoupper(substr($profile['username'], 0, 1)) ?>
          </div>
        <?php endif; ?>
      </div>
      <h2><?= e($profile['username']) ?></h2>
      <p class="profile-email">📧 <?= e($profile['email']) ?></p>
      <span class="badge badge-<?= $profile['role'] === 'admin' ? 'admin' : 'user' ?>">
        <?= e($profile['role']) ?>
      </span>
      <?php if (!empty($profile['bio'])): ?>
        <p class="profile-bio"><?= e($profile['bio']) ?></p>
      <?php endif; ?>
      <a href="<?= base_url('user/profile.php') ?>" class="btn-primary mt-3" id="editProfileBtn">
        ✏️ Edit Profile
      </a>
    </div>

    <!-- Info Cards -->
    <div class="info-grid">
      <div class="card info-card">
        <div class="info-icon">📅</div>
        <h3>Member Since</h3>
        <p><?= date('d F Y', strtotime($profile['created_at'])) ?></p>
      </div>
      <div class="card info-card">
        <div class="info-icon">🛡️</div>
        <h3>Account Role</h3>
        <p><?= ucfirst(e($profile['role'])) ?></p>
      </div>
      <div class="card info-card">
        <div class="info-icon">📸</div>
        <h3>Profile Picture</h3>
        <p><?= empty($profile['profile_picture']) ? 'Not set' : 'Uploaded ✅' ?></p>
      </div>
      <div class="card info-card action-card">
        <div class="info-icon">⚙️</div>
        <h3>Quick Actions</h3>
        <div class="quick-links">
          <a href="<?= base_url('user/profile.php') ?>" class="quick-link">Edit Profile</a>
          <a href="<?= base_url('auth/logout.php') ?>" class="quick-link danger">Logout</a>
        </div>
      </div>
    </div>

  </div>
</div>

<?php include __DIR__ . '/../includes/footer.php'; ?>
