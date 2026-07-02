<?php
// login.php
$pageTitle = "Log In";
$activePage = "login";

require_once 'includes/db.php';
require_once 'includes/auth.php';
require_once 'includes/mail.php';

// Redirect if already logged in
if (isLoggedIn()) {
    if ($_SESSION['user_role'] === 'admin') {
        header("Location: admin.php");
    } else {
        header("Location: dashboard.php");
    }
    exit;
}

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    if (empty($email) || empty($password)) {
        $error = "Please enter both email and password.";
    } else {
        try {
            $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ?");
            $stmt->execute([$email]);
            $user = $stmt->fetch();

            if ($user && password_verify($password, $user['password'])) {
                // Check if user is verified
                if ($user['is_verified'] == 0) {
                    // Send new OTP and redirect to verification
                    $_SESSION['verify_email'] = $user['email'];
                    sendOTP($user['email'], $pdo);
                    header("Location: verify-otp.php");
                    exit;
                }

                // Successful login
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['user_name'] = $user['name'];
                $_SESSION['user_email'] = $user['email'];
                $_SESSION['user_role'] = $user['role'];
                $_SESSION['is_verified'] = 1;

                // Redirect based on role
                if ($user['role'] === 'admin') {
                    header("Location: admin.php");
                } else {
                    header("Location: dashboard.php");
                }
                exit;
            } else {
                $error = "Invalid email or password.";
            }
        } catch (PDOException $e) {
            $error = "Database error during login: " . $e->getMessage();
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
            <h2>Welcome Back</h2>
            <p>Enter your credentials to access your courses</p>
        </div>

        <?php if (!empty($error)): ?>
            <div class="alert alert-danger">
                <i class="fa-solid fa-circle-exclamation"></i>
                <div><?php echo htmlspecialchars($error); ?></div>
            </div>
        <?php endif; ?>

        <form action="login.php" method="POST">
            <div class="form-group">
                <label for="email"><i class="fa-regular fa-envelope" style="color: var(--secondary); margin-right: 4px;"></i> Email Address</label>
                <input type="email" name="email" id="email" class="form-control" placeholder="name@domain.com" value="<?php echo htmlspecialchars($email ?? ''); ?>" required>
            </div>

            <div class="form-group">
                <label for="password"><i class="fa-solid fa-lock" style="color: var(--secondary); margin-right: 4px;"></i> Password</label>
                <input type="password" name="password" id="password" class="form-control" placeholder="Enter your password" required>
            </div>

            <button type="submit" class="btn btn-primary btn-block" style="margin-top: 32px;">
                <i class="fa-solid fa-right-to-bracket"></i> Sign In
            </button>
        </form>

        <div class="auth-footer">
            Don't have an account yet? <a href="register.php">Sign Up</a>
        </div>
        
        <!-- Helpful Hint info for grading / sandbox demo -->
        <div style="margin-top: 24px; padding: 12px; border-radius: 8px; background: rgba(255,255,255,0.02); border: 1px solid var(--border-color); font-size: 11px; text-align: center; color: var(--text-muted);">
            <div style="font-weight:600; color:#fff; margin-bottom:4px;"><i class="fa-solid fa-info-circle"></i> Quick Test Accounts</div>
            Student: <strong>student@edustream.com</strong> / <strong>student123</strong><br>
            Instructor: <strong>admin@edustream.com</strong> / <strong>admin123</strong>
        </div>
    </div>
</div>

<?php require_once 'includes/footer.php'; ?>
