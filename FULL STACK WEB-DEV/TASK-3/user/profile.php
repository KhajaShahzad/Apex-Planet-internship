<?php
/**
 * user/profile.php
 * Profile Management — edit info + upload profile picture.
 * Accessible by any logged-in user (edit own profile).
 * Admin may also use this to edit their own profile.
 */


require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../includes/auth.php';

require_login();

$cu = current_user();
$errors   = [];
$success  = false;

// Fetch current profile
$stmt = $conn->prepare(
    'SELECT username, email, bio, profile_picture FROM users WHERE id=? LIMIT 1'
);
$stmt->bind_param('i', $cu['id']);
$stmt->execute();
$profile = $stmt->get_result()->fetch_assoc();
$stmt->close();

// ---------------------------------------------------------
// Handle POST
// ---------------------------------------------------------
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username    = trim($_POST['username']    ?? '');
    $email       = trim($_POST['email']       ?? '');
    $bio         = trim($_POST['bio']         ?? '');
    $password    = $_POST['password']         ?? '';
    $confirm     = $_POST['confirm_password'] ?? '';

    // Validate text fields
    if (strlen($username) < 3) $errors[] = 'Username must be at least 3 characters.';
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) $errors[] = 'Invalid email address.';
    if ($password !== '' && strlen($password) < 8) $errors[] = 'New password must be at least 8 characters.';
    if ($password !== '' && $password !== $confirm) $errors[] = 'Passwords do not match.';

    // Uniqueness (exclude self)
    if (empty($errors)) {
        $chk = $conn->prepare(
            'SELECT id FROM users WHERE (email=? OR username=?) AND id != ? LIMIT 1'
        );
        $chk->bind_param('ssi', $email, $username, $cu['id']);
        $chk->execute();
        $chk->store_result();
        if ($chk->num_rows > 0) $errors[] = 'Email or username already taken.';
        $chk->close();
    }

    // Profile picture upload
    $picturePath = $profile['profile_picture']; // Keep old if no new upload
    if (!empty($_FILES['profile_picture']['name'])) {
        $file      = $_FILES['profile_picture'];
        $maxSize   = 2 * 1024 * 1024; // 2 MB
        $allowMime = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
        $ext_map   = ['image/jpeg' => 'jpg', 'image/png' => 'png',
                      'image/gif'  => 'gif', 'image/webp' => 'webp'];

        if ($file['error'] !== UPLOAD_ERR_OK) {
            $errors[] = 'File upload error (code ' . $file['error'] . ').';
        } elseif ($file['size'] > $maxSize) {
            $errors[] = 'Profile picture must be smaller than 2 MB.';
        } else {
            // Validate MIME type via finfo (server-side, not just extension)
            $finfo    = new finfo(FILEINFO_MIME_TYPE);
            $mimeType = $finfo->file($file['tmp_name']);

            if (!in_array($mimeType, $allowMime, true)) {
                $errors[] = 'Only JPG, PNG, GIF, and WEBP images are allowed.';
            } else {
                $uploadDir = __DIR__ . '/../assets/uploads/';
                if (!is_dir($uploadDir)) mkdir($uploadDir, 0755, true);

                $ext      = $ext_map[$mimeType];
                $filename = 'user_' . $cu['id'] . '_' . time() . '.' . $ext;
                $dest     = $uploadDir . $filename;

                if (move_uploaded_file($file['tmp_name'], $dest)) {
                    // Delete old picture
                    if (!empty($profile['profile_picture'])) {
                        $old_file = __DIR__ . '/../' . $profile['profile_picture'];
                        if (file_exists($old_file)) unlink($old_file);
                    }
                    $picturePath = 'assets/uploads/' . $filename;
                } else {
                    $errors[] = 'Failed to save the uploaded image.';
                }
            }
        }
    }

    // Save to DB
    if (empty($errors)) {
        if ($password !== '') {
            $hash = password_hash($password, PASSWORD_BCRYPT, ['cost' => 12]);
            $upd  = $conn->prepare(
                'UPDATE users SET username=?, email=?, bio=?, profile_picture=?, password_hash=? WHERE id=?'
            );
            $upd->bind_param('sssssi', $username, $email, $bio, $picturePath, $hash, $cu['id']);
        } else {
            $upd  = $conn->prepare(
                'UPDATE users SET username=?, email=?, bio=?, profile_picture=? WHERE id=?'
            );
            $upd->bind_param('ssssi', $username, $email, $bio, $picturePath, $cu['id']);
        }
        $upd->execute();
        $upd->close();

        // Refresh session
        $_SESSION['username']        = $username;
        $_SESSION['email']           = $email;
        $_SESSION['profile_picture'] = $picturePath;

        // Reload profile data
        $profile['username']        = $username;
        $profile['email']           = $email;
        $profile['bio']             = $bio;
        $profile['profile_picture'] = $picturePath;

        set_flash('success', 'Profile updated successfully! ✅');
        header('Location: ' . base_url('user/profile.php'));
        exit;
    }
}

$pageTitle = 'My Profile';
include __DIR__ . '/../includes/header.php';
?>

<div class="container narrow">
  <div class="page-header">
    <h1 class="page-title">👤 My Profile</h1>
    <a href="<?= base_url(is_admin() ? 'admin/dashboard.php' : 'user/dashboard.php') ?>"
       class="btn-secondary">← Dashboard</a>
  </div>

  <div class="card form-card profile-form-card">
    <!-- Current avatar preview -->
    <div class="current-avatar-wrap">
      <div class="current-avatar" id="avatarPreview">
        <?php if (!empty($profile['profile_picture'])): ?>
          <img src="<?= base_url($profile['profile_picture']) ?>"
               alt="Profile picture" id="avatarImg" />
        <?php else: ?>
          <div class="avatar-placeholder-lg" id="avatarInitial">
            <?= strtoupper(substr($profile['username'], 0, 1)) ?>
          </div>
        <?php endif; ?>
      </div>
      <p class="avatar-hint">Click below to change your picture</p>
    </div>

    <?php if ($errors): ?>
      <div class="alert alert-error">
        <ul><?php foreach ($errors as $e): ?><li><?= e($e) ?></li><?php endforeach; ?></ul>
      </div>
    <?php endif; ?>

    <form method="POST" action="" enctype="multipart/form-data" id="profileForm" novalidate>

      <!-- Profile picture upload -->
      <div class="form-group">
        <label for="profile_picture">Profile Picture
          <span class="text-muted">(JPG/PNG/GIF/WEBP · max 2 MB)</span>
        </label>
        <div class="file-upload-wrap">
          <label class="file-upload-label" for="profile_picture" id="fileLabel">
            📁 Choose File
          </label>
          <input type="file" id="profile_picture" name="profile_picture"
                 accept="image/jpeg,image/png,image/gif,image/webp"
                 class="file-input" />
          <span class="file-name" id="fileName">No file chosen</span>
        </div>
      </div>

      <div class="form-row">
        <div class="form-group">
          <label for="username">Username *</label>
          <div class="input-wrap">
            <span class="input-icon">👤</span>
            <input type="text" id="username" name="username"
                   value="<?= e($profile['username']) ?>"
                   required minlength="3" />
          </div>
        </div>

        <div class="form-group">
          <label for="email">Email Address *</label>
          <div class="input-wrap">
            <span class="input-icon">📧</span>
            <input type="email" id="email" name="email"
                   value="<?= e($profile['email']) ?>" required />
          </div>
        </div>
      </div>

      <div class="form-group">
        <label for="bio">Bio</label>
        <textarea id="bio" name="bio" rows="3"
                  placeholder="Tell us a bit about yourself…"><?= e($profile['bio'] ?? '') ?></textarea>
      </div>

      <hr class="divider" />
      <p class="section-label">Change Password <span class="text-muted">(optional)</span></p>

      <div class="form-row">
        <div class="form-group">
          <label for="password">New Password</label>
          <div class="input-wrap">
            <span class="input-icon">🔒</span>
            <input type="password" id="password" name="password"
                   placeholder="Leave blank to keep current" minlength="8" />
            <button type="button" class="toggle-pw" data-target="password">👁</button>
          </div>
          <div class="pw-strength" id="pwStrength"></div>
        </div>

        <div class="form-group">
          <label for="confirm_password">Confirm Password</label>
          <div class="input-wrap">
            <span class="input-icon">🔒</span>
            <input type="password" id="confirm_password" name="confirm_password"
                   placeholder="Repeat new password" minlength="8" />
            <button type="button" class="toggle-pw" data-target="confirm_password">👁</button>
          </div>
        </div>
      </div>

      <div class="form-actions">
        <a href="<?= base_url(is_admin() ? 'admin/dashboard.php' : 'user/dashboard.php') ?>"
           class="btn-secondary">Cancel</a>
        <button type="submit" class="btn-primary" id="saveProfileBtn">Save Profile</button>
      </div>
    </form>
  </div>
</div>

<?php include __DIR__ . '/../includes/footer.php'; ?>
