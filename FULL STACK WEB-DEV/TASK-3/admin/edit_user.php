<?php
/**
 * admin/edit_user.php
 * Admin — Edit an existing user record.
 */


require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../includes/auth.php';

require_role('admin');

$id = (int)($_GET['id'] ?? $_POST['id'] ?? 0);
if ($id <= 0) {
    set_flash('error', 'Invalid user ID.');
    header('Location: ' . base_url('admin/dashboard.php'));
    exit;
}

// Fetch existing user
$fetch = $conn->prepare(
    'SELECT u.id, u.username, u.email, u.role_id, u.bio FROM users u WHERE u.id=? LIMIT 1'
);
$fetch->bind_param('i', $id);
$fetch->execute();
$user = $fetch->get_result()->fetch_assoc();
$fetch->close();

if (!$user) {
    set_flash('error', 'User not found.');
    header('Location: ' . base_url('admin/dashboard.php'));
    exit;
}

$errors = [];
$old    = $user; // Pre-fill

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $old['username'] = trim($_POST['username'] ?? '');
    $old['email']    = trim($_POST['email']    ?? '');
    $old['role_id']  = (int)($_POST['role_id'] ?? 2);
    $old['bio']      = trim($_POST['bio']      ?? '');
    $password        = $_POST['password'] ?? '';

    // Validation
    if (strlen($old['username']) < 3) $errors[] = 'Username must be at least 3 characters.';
    if (!filter_var($old['email'], FILTER_VALIDATE_EMAIL)) $errors[] = 'Invalid email address.';
    if (!in_array($old['role_id'], [1, 2], true)) $errors[] = 'Invalid role.';
    if ($password !== '' && strlen($password) < 8) $errors[] = 'New password must be at least 8 characters.';

    // Uniqueness (exclude current user)
    if (empty($errors)) {
        $chk = $conn->prepare(
            'SELECT id FROM users WHERE (email=? OR username=?) AND id != ? LIMIT 1'
        );
        $chk->bind_param('ssi', $old['email'], $old['username'], $id);
        $chk->execute();
        $chk->store_result();
        if ($chk->num_rows > 0) $errors[] = 'Email or username already used by another account.';
        $chk->close();
    }

    if (empty($errors)) {
        if ($password !== '') {
            $hash = password_hash($password, PASSWORD_BCRYPT, ['cost' => 12]);
            $upd  = $conn->prepare(
                'UPDATE users SET username=?, email=?, role_id=?, bio=?, password_hash=? WHERE id=?'
            );
            $upd->bind_param('ssissi', $old['username'], $old['email'], $old['role_id'], $old['bio'], $hash, $id);
        } else {
            $upd  = $conn->prepare(
                'UPDATE users SET username=?, email=?, role_id=?, bio=? WHERE id=?'
            );
            $upd->bind_param('ssisi', $old['username'], $old['email'], $old['role_id'], $old['bio'], $id);
        }
        $upd->execute();
        $upd->close();

        set_flash('success', "User '{$old['username']}' updated successfully!");
        header('Location: ' . base_url('admin/dashboard.php'));
        exit;
    }
}

$pageTitle = 'Edit User';
include __DIR__ . '/../includes/header.php';
?>

<div class="container narrow">
  <div class="page-header">
    <h1 class="page-title">✏️ Edit User</h1>
    <a href="<?= base_url('admin/dashboard.php') ?>" class="btn-secondary">← Back</a>
  </div>

  <div class="card form-card">
    <?php if ($errors): ?>
      <div class="alert alert-error">
        <ul><?php foreach ($errors as $e): ?><li><?= e($e) ?></li><?php endforeach; ?></ul>
      </div>
    <?php endif; ?>

    <form method="POST" action="" id="editUserForm" novalidate>
      <input type="hidden" name="id" value="<?= $id ?>" />

      <div class="form-row">
        <div class="form-group">
          <label for="username">Username *</label>
          <div class="input-wrap">
            <span class="input-icon">👤</span>
            <input type="text" id="username" name="username"
                   value="<?= e($old['username']) ?>"
                   required minlength="3" />
          </div>
        </div>

        <div class="form-group">
          <label for="email">Email Address *</label>
          <div class="input-wrap">
            <span class="input-icon">📧</span>
            <input type="email" id="email" name="email"
                   value="<?= e($old['email']) ?>" required />
          </div>
        </div>
      </div>

      <div class="form-row">
        <div class="form-group">
          <label for="password">New Password <span class="text-muted">(leave blank to keep)</span></label>
          <div class="input-wrap">
            <span class="input-icon">🔒</span>
            <input type="password" id="password" name="password"
                   placeholder="Min 8 characters" minlength="8" />
            <button type="button" class="toggle-pw" data-target="password">👁</button>
          </div>
        </div>

        <div class="form-group">
          <label for="role_id">Role *</label>
          <div class="input-wrap">
            <span class="input-icon">🛡</span>
            <select id="role_id" name="role_id" required>
              <option value="2" <?= ($old['role_id'] == 2) ? 'selected' : '' ?>>User</option>
              <option value="1" <?= ($old['role_id'] == 1) ? 'selected' : '' ?>>Admin</option>
            </select>
          </div>
        </div>
      </div>

      <div class="form-group">
        <label for="bio">Bio</label>
        <textarea id="bio" name="bio" rows="3"
                  placeholder="A short bio about this user…"><?= e($old['bio'] ?? '') ?></textarea>
      </div>

      <div class="form-actions">
        <a href="<?= base_url('admin/dashboard.php') ?>" class="btn-secondary">Cancel</a>
        <button type="submit" class="btn-primary" id="saveUserBtn">Save Changes</button>
      </div>
    </form>
  </div>
</div>

<?php include __DIR__ . '/../includes/footer.php'; ?>
