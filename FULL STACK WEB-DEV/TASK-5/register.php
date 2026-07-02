<?php
// register.php
$pageTitle = "Sign Up";
$activePage = "register";

require_once 'includes/db.php';
require_once 'includes/auth.php';
require_once 'includes/mail.php';

// Redirect if already logged in
if (isLoggedIn()) {
    header("Location: dashboard.php");
    exit;
}

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $role = $_POST['role'] ?? 'student';

    // Basic Validations
    if (empty($name) || empty($email) || empty($password)) {
        $error = "Please fill in all fields.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = "Please enter a valid email address.";
    } elseif (strlen($password) < 6) {
        $error = "Password must be at least 6 characters long.";
    } elseif (!in_array($role, ['student', 'admin'])) {
        $error = "Invalid user role selected.";
    } else {
        try {
            // Check if user already exists
            $stmt = $pdo->prepare("SELECT id, is_verified FROM users WHERE email = ?");
            $stmt->execute([$email]);
            $existing = $stmt->fetch();

            if ($existing) {
                if ($existing['is_verified'] == 0) {
                    // User exists but is not verified yet. Delete temporary unverified record to allow fresh sign up.
                    $stmtDel = $pdo->prepare("DELETE FROM users WHERE id = ?");
                    $stmtDel->execute([$existing['id']]);
                } else {
                    $error = "An account with this email address already exists.";
                }
            }

            if (empty($error)) {
                // Hash password
                $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

                // Insert unverified user
                $stmtIns = $pdo->prepare("INSERT INTO users (name, email, password, role, is_verified) VALUES (?, ?, ?, ?, 0)");
                $stmtIns->execute([$name, $email, $hashedPassword, $role]);

                // Generate and send OTP
                $otpResult = sendOTP($email, $pdo);
                
                if ($otpResult['success']) {
                    $_SESSION['verify_email'] = $email;
                    header("Location: verify-otp.php");
                    exit;
                } else {
                    $error = "Failed to generate verification code. Please try again.";
                }
            }
        } catch (PDOException $e) {
            $error = "Registration failed: " . $e->getMessage();
        }
    }
}

require_once 'includes/header.php';
?>

<div class="auth-page">
    <div class="hero-gradient-orb" style="top: 10%; left: 20%;"></div>
    <div class="hero-gradient-orb" style="bottom: 10%; right: 20%; background: radial-gradient(circle, rgba(14, 165, 233, 0.4) 0%, rgba(99, 102, 241, 0.1) 80%);"></div>

    <div class="auth-card">
        <div class="auth-header">
            <h2>Create Account</h2>
            <p>Join EduStream today and start learning</p>
        </div>

        <?php if (!empty($error)): ?>
            <div class="alert alert-danger">
                <i class="fa-solid fa-circle-exclamation"></i>
                <div><?php echo htmlspecialchars($error); ?></div>
            </div>
        <?php endif; ?>

        <form action="register.php" method="POST">
            <div class="form-group">
                <label for="name"><i class="fa-regular fa-user" style="color: var(--secondary); margin-right: 4px;"></i> Full Name</label>
                <input type="text" name="name" id="name" class="form-control" placeholder="e.g. John Doe" value="<?php echo htmlspecialchars($name ?? ''); ?>" required>
            </div>

            <div class="form-group">
                <label for="email"><i class="fa-regular fa-envelope" style="color: var(--secondary); margin-right: 4px;"></i> Email Address</label>
                <input type="email" name="email" id="email" class="form-control" placeholder="name@domain.com" value="<?php echo htmlspecialchars($email ?? ''); ?>" required>
            </div>

            <div class="form-group">
                <label for="password"><i class="fa-solid fa-lock" style="color: var(--secondary); margin-right: 4px;"></i> Password</label>
                <input type="password" name="password" id="password" class="form-control" placeholder="At least 6 characters" required>
            </div>

            <div class="form-group">
                <label for="role"><i class="fa-solid fa-users" style="color: var(--secondary); margin-right: 4px;"></i> Register As</label>
                <select name="role" id="role" class="form-control">
                    <option value="student" <?php echo (isset($role) && $role === 'student') ? 'selected' : ''; ?>>Student (Explore & learn courses)</option>
                    <option value="admin" <?php echo (isset($role) && $role === 'admin') ? 'selected' : ''; ?>>Instructor / Admin (Create & manage courses)</option>
                </select>
            </div>

            <button type="submit" class="btn btn-primary btn-block" style="margin-top: 32px;">
                <i class="fa-solid fa-user-plus"></i> Register & Send OTP
            </button>
        </form>

        <div class="auth-footer">
            Already have an account? <a href="login.php">Log In</a>
        </div>
    </div>
</div>

<?php require_once 'includes/footer.php'; ?>
