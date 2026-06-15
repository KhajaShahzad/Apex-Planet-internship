<?php
/**
 * auth/login.php
 * Login — session creation, role-based redirect.
 */


require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../includes/auth.php';

if (is_logged_in()) {
    header('Location: ' . base_url('index.php'));
    exit;
}

$errors = [];
$old    = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $old['email'] = trim($_POST['email'] ?? '');
    $password     = $_POST['password'] ?? '';

    if (!filter_var($old['email'], FILTER_VALIDATE_EMAIL)) {
        $errors[] = 'Enter a valid email address.';
    }
    if (empty($password)) {
        $errors[] = 'Password is required.';
    }

    if (empty($errors)) {
        $stmt = $conn->prepare(
            'SELECT u.id, u.username, u.email, u.password_hash, u.profile_picture, r.name AS role
             FROM users u
             JOIN roles r ON r.id = u.role_id
             WHERE u.email = ?
             LIMIT 1'
        );
        $stmt->bind_param('s', $old['email']);
        $stmt->execute();
        $result = $stmt->get_result();
        $user   = $result->fetch_assoc();
        $stmt->close();

        if ($user && password_verify($password, $user['password_hash'])) {
            // Regenerate session ID to prevent session fixation
            session_regenerate_id(true);

            $_SESSION['user_id']         = $user['id'];
            $_SESSION['username']        = $user['username'];
            $_SESSION['email']           = $user['email'];
            $_SESSION['role']            = $user['role'];
            $_SESSION['profile_picture'] = $user['profile_picture'];

            set_flash('success', 'Welcome back, ' . $user['username'] . '! 👋');

            if ($user['role'] === 'admin') {
                header('Location: ' . base_url('admin/dashboard.php'));
            } else {
                header('Location: ' . base_url('user/dashboard.php'));
            }
            exit;
        } else {
            $errors[] = 'Invalid email or password.';
        }
    }
}

$pageTitle = 'Login';
include __DIR__ . '/../includes/header.php';
?>

<section class="auth-section">
  <div class="auth-card">
    <div class="auth-header">
      <div class="auth-icon">🔐</div>
      <h1>Welcome Back</h1>
      <p>Sign in to your UserHub account</p>
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

    <!-- Demo credentials hint -->
    <div class="demo-hint">
      <strong>Demo Admin:</strong> admin@apexplanet.com &nbsp;/&nbsp; Admin@1234
    </div>

    <form method="POST" action="" id="loginForm" novalidate>
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
                 placeholder="Your password" required />
          <button type="button" class="toggle-pw" data-target="password">👁</button>
        </div>
      </div>

      <button type="submit" class="btn-primary btn-full" id="loginBtn">
        Sign In
      </button>
    </form>

    <p class="auth-switch">
      Don't have an account? <a href="<?= base_url('auth/register.php') ?>">Register</a>
    </p>
  </div>
</section>

<?php include __DIR__ . '/../includes/footer.php'; ?>
