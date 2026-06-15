<?php
/**
 * auth/register.php
 * User Registration — server-side validated, bcrypt hashed.
 */


require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../includes/auth.php';

// Already logged in → redirect
if (is_logged_in()) {
    header('Location: ' . base_url('index.php'));
    exit;
}

$errors   = [];
$old      = [];   // Repopulate form on error

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $old['username'] = trim($_POST['username'] ?? '');
    $old['email']    = trim($_POST['email']    ?? '');
    $password        = $_POST['password']        ?? '';
    $confirm         = $_POST['confirm_password'] ?? '';

    // --- Validation ---
    if (strlen($old['username']) < 3) {
        $errors[] = 'Username must be at least 3 characters.';
    }
    if (!filter_var($old['email'], FILTER_VALIDATE_EMAIL)) {
        $errors[] = 'Enter a valid email address.';
    }
    if (strlen($password) < 8) {
        $errors[] = 'Password must be at least 8 characters.';
    }
    if ($password !== $confirm) {
        $errors[] = 'Passwords do not match.';
    }

    // --- Uniqueness check ---
    if (empty($errors)) {
        $stmt = $conn->prepare('SELECT id FROM users WHERE email = ? OR username = ? LIMIT 1');
        $stmt->bind_param('ss', $old['email'], $old['username']);
        $stmt->execute();
        $stmt->store_result();
        if ($stmt->num_rows > 0) {
            $errors[] = 'Email or username already taken.';
        }
        $stmt->close();
    }

    // --- Insert ---
    if (empty($errors)) {
        $hash = password_hash($password, PASSWORD_BCRYPT, ['cost' => 12]);
        $role_id = 2; // default: 'user'

        $stmt = $conn->prepare(
            'INSERT INTO users (role_id, username, email, password_hash) VALUES (?, ?, ?, ?)'
        );
        $stmt->bind_param('isss', $role_id, $old['username'], $old['email'], $hash);
        $stmt->execute();
        $stmt->close();

        set_flash('success', 'Account created! Please log in.');
        header('Location: ' . base_url('auth/login.php'));
        exit;
    }
}

$pageTitle = 'Register';
include __DIR__ . '/../includes/header.php';
?>

<section class="auth-section">
  <div class="auth-card">
    <div class="auth-header">
      <div class="auth-icon">🚀</div>
      <h1>Create Account</h1>
      <p>Join UserHub — it's free and takes 30 seconds</p>
    </div>

    <?php if ($errors): ?>
      <div class="alert alert-error">
        <ul>
          <?php foreach ($errors as $e): ?>
            <li><?= htmlspecialchars($e) ?></li>
          <?php endforeach; ?>
        </ul>
      </div>
    <?php endif; ?>

    <form method="POST" action="" id="registerForm" novalidate>
      <div class="form-group">
        <label for="username">Username</label>
        <div class="input-wrap">
          <span class="input-icon">👤</span>
          <input type="text" id="username" name="username"
                 value="<?= e($old['username'] ?? '') ?>"
                 placeholder="e.g. john_doe" required minlength="3" />
        </div>
      </div>

      <div class="form-group">
        <label for="email">Email Address</label>
        <div class="input-wrap">
          <span class="input-icon">📧</span>
          <input type="email" id="email" name="email"
                 value="<?= e($old['email'] ?? '') ?>"
                 placeholder="you@example.com" required />
        </div>
      </div>

      <div class="form-group">
        <label for="password">Password</label>
        <div class="input-wrap">
          <span class="input-icon">🔒</span>
          <input type="password" id="password" name="password"
                 placeholder="Min 8 characters" required minlength="8" />
          <button type="button" class="toggle-pw" data-target="password">👁</button>
        </div>
        <div class="pw-strength" id="pwStrength"></div>
      </div>

      <div class="form-group">
        <label for="confirm_password">Confirm Password</label>
        <div class="input-wrap">
          <span class="input-icon">🔒</span>
          <input type="password" id="confirm_password" name="confirm_password"
                 placeholder="Repeat password" required />
          <button type="button" class="toggle-pw" data-target="confirm_password">👁</button>
        </div>
      </div>

      <button type="submit" class="btn-primary btn-full" id="registerBtn">
        Create Account
      </button>
    </form>

    <p class="auth-switch">
      Already have an account? <a href="<?= base_url('auth/login.php') ?>">Log in</a>
    </p>
  </div>
</section>

<?php include __DIR__ . '/../includes/footer.php'; ?>
