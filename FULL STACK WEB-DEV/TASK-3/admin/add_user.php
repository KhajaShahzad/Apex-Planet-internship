<?php
/**
 * admin/add_user.php
 * Admin — Add a new user via PHP form.
 */


require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../includes/auth.php';

require_role('admin');

$errors = [];
$old    = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $old['username'] = trim($_POST['username'] ?? '');
    $old['email']    = trim($_POST['email']    ?? '');
    $old['role_id']  = (int)($_POST['role_id'] ?? 2);
    $password        = $_POST['password'] ?? '';

    // --- Validation ---
    if (strlen($old['username']) < 3) $errors[] = 'Username must be at least 3 characters.';
    if (!filter_var($old['email'], FILTER_VALIDATE_EMAIL)) $errors[] = 'Invalid email address.';
    if (strlen($password) < 8) $errors[] = 'Password must be at least 8 characters.';
    if (!in_array($old['role_id'], [1, 2], true)) $errors[] = 'Invalid role selected.';

    // Uniqueness
    if (empty($errors)) {
        $chk = $conn->prepare('SELECT id FROM users WHERE email=? OR username=? LIMIT 1');
        $chk->bind_param('ss', $old['email'], $old['username']);
        $chk->execute();
        $chk->store_result();
        if ($chk->num_rows > 0) $errors[] = 'Email or username already in use.';
        $chk->close();
    }

    if (empty($errors)) {
        $hash = password_hash($password, PASSWORD_BCRYPT, ['cost' => 12]);
        $ins  = $conn->prepare(
            'INSERT INTO users (role_id, username, email, password_hash) VALUES (?,?,?,?)'
        );
        $ins->bind_param('isss', $old['role_id'], $old['username'], $old['email'], $hash);
        $ins->execute();
        $ins->close();

        set_flash('success', "User '{$old['username']}' added successfully!");
        header('Location: ' . base_url('admin/dashboard.php'));
        exit;
    }
}

$pageTitle = 'Add User';
include __DIR__ . '/../includes/header.php';
?>

<div class="container narrow">
  <div class="page-header">
    <h1 class="page-title">➕ Add New User</h1>
    <a href="<?= base_url('admin/dashboard.php') ?>" class="btn-secondary">← Back</a>
  </div>

  <div class="card form-card">
    <?php if ($errors): ?>
      <div class="alert alert-error">
        <ul><?php foreach ($errors as $e): ?><li><?= e($e) ?></li><?php endforeach; ?></ul>
      </div>
    <?php endif; ?>

    <form method="POST" action="" id="addUserForm" novalidate>
      <div class="form-row">
        <div class="form-group">
          <label for="username">Username *</label>
          <div class="input-wrap">
            <span class="input-icon">👤</span>
            <input type="text" id="username" name="username"
                   value="<?= e($old['username'] ?? '') ?>"
                   placeholder="john_doe" required minlength="3" />
          </div>
        </div>

        <div class="form-group">
          <label for="email">Email Address *</label>
          <div class="input-wrap">
            <span class="input-icon">📧</span>
            <input type="email" id="email" name="email"
                   value="<?= e($old['email'] ?? '') ?>"
                   placeholder="john@example.com" required />
          </div>
        </div>
      </div>

      <div class="form-row">
        <div class="form-group">
          <label for="password">Password *</label>
          <div class="input-wrap">
            <span class="input-icon">🔒</span>
            <input type="password" id="password" name="password"
                   placeholder="Min 8 characters" required minlength="8" />
            <button type="button" class="toggle-pw" data-target="password">👁</button>
          </div>
          <div class="pw-strength" id="pwStrength"></div>
        </div>

        <div class="form-group">
          <label for="role_id">Role *</label>
          <div class="input-wrap">
            <span class="input-icon">🛡</span>
            <select id="role_id" name="role_id" required>
              <option value="2" <?= (($old['role_id'] ?? 2) == 2) ? 'selected' : '' ?>>User</option>
              <option value="1" <?= (($old['role_id'] ?? 2) == 1) ? 'selected' : '' ?>>Admin</option>
            </select>
          </div>
        </div>
      </div>

      <div class="form-actions">
        <button type="reset" class="btn-secondary">Reset</button>
        <button type="submit" class="btn-primary" id="addUserBtn">Add User</button>
      </div>
    </form>
  </div>
</div>

<?php include __DIR__ . '/../includes/footer.php'; ?>
