<?php
// verify-otp.php
$pageTitle = "Verify Email";
$activePage = "register";

require_once 'includes/db.php';
require_once 'includes/auth.php';
require_once 'includes/mail.php';

// Redirect if email not in session
if (!isset($_SESSION['verify_email'])) {
    header("Location: register.php");
    exit;
}

$email = $_SESSION['verify_email'];
$error = '';
$success = '';

// Handle OTP Resend
if (isset($_GET['action']) && $_GET['action'] === 'resend') {
    $resendResult = sendOTP($email, $pdo);
    if ($resendResult['success']) {
        $success = "A new verification code has been generated and sent.";
    } else {
        $error = "Failed to resend verification code. Please try again.";
    }
}

// Handle OTP Verification Submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Concatenate the 6 digits from the individual inputs
    $otpArray = $_POST['otp'] ?? [];
    $userOtp = implode('', $otpArray);

    if (strlen($userOtp) !== 6 || !ctype_digit($userOtp)) {
        $error = "Please enter a valid 6-digit code.";
    } else {
        $verified = verifyOTP($email, $userOtp, $pdo);

        if ($verified) {
            try {
                // Update user verification status in database
                $stmt = $pdo->prepare("UPDATE users SET is_verified = 1 WHERE email = ?");
                $stmt->execute([$email]);

                // Fetch user data to auto-login
                $stmtUser = $pdo->prepare("SELECT id, name, role FROM users WHERE email = ?");
                $stmtUser->execute([$email]);
                $user = $stmtUser->fetch();

                if ($user) {
                    // Set login session variables
                    $_SESSION['user_id'] = $user['id'];
                    $_SESSION['user_name'] = $user['name'];
                    $_SESSION['user_email'] = $email;
                    $_SESSION['user_role'] = $user['role'];
                    $_SESSION['is_verified'] = 1;

                    // Clean registration variables
                    unset($_SESSION['verify_email']);

                    // Redirect based on role
                    if ($user['role'] === 'admin') {
                        header("Location: admin.php");
                    } else {
                        header("Location: dashboard.php");
                    }
                    exit;
                }
            } catch (PDOException $e) {
                $error = "Verification succeeded, but login session creation failed: " . $e->getMessage();
            }
        } else {
            $error = "Invalid or expired OTP. Please try again.";
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
            <h2>Verify Your Email</h2>
            <p>We've sent a 6-digit verification code to <br><strong><?php echo htmlspecialchars($email); ?></strong></p>
        </div>

        <!-- Dev/Debug Notification for offline XAMPP testing -->
        <?php if (isset($_SESSION['debug_otp']) && $_SESSION['debug_otp']['email'] === $email): ?>
            <div class="alert alert-debug">
                <div><i class="fa-solid fa-code" style="margin-right: 6px;"></i><strong>Developer Sandbox Simulation:</strong></div>
                <div style="font-size: 13px; margin-top: 4px;">Since local mail settings are offline, the generated verification OTP is:</div>
                <div style="margin-top: 8px; align-self: center;">
                    <code><?php echo htmlspecialchars($_SESSION['debug_otp']['otp']); ?></code>
                </div>
            </div>
        <?php endif; ?>

        <?php if (!empty($success)): ?>
            <div class="alert alert-success">
                <i class="fa-regular fa-circle-check"></i>
                <div><?php echo htmlspecialchars($success); ?></div>
            </div>
        <?php endif; ?>

        <?php if (!empty($error)): ?>
            <div class="alert alert-danger">
                <i class="fa-solid fa-circle-exclamation"></i>
                <div><?php echo htmlspecialchars($error); ?></div>
            </div>
        <?php endif; ?>

        <form action="verify-otp.php" method="POST" id="otpForm">
            <div class="otp-container">
                <input type="text" name="otp[]" class="otp-box" maxlength="1" required pattern="[0-9]" inputmode="numeric" autofocus>
                <input type="text" name="otp[]" class="otp-box" maxlength="1" required pattern="[0-9]" inputmode="numeric">
                <input type="text" name="otp[]" class="otp-box" maxlength="1" required pattern="[0-9]" inputmode="numeric">
                <input type="text" name="otp[]" class="otp-box" maxlength="1" required pattern="[0-9]" inputmode="numeric">
                <input type="text" name="otp[]" class="otp-box" maxlength="1" required pattern="[0-9]" inputmode="numeric">
                <input type="text" name="otp[]" class="otp-box" maxlength="1" required pattern="[0-9]" inputmode="numeric">
            </div>

            <button type="submit" class="btn btn-primary btn-block" style="margin-top: 24px;">
                <i class="fa-solid fa-shield-check"></i> Verify & Sign In
            </button>
        </form>

        <div class="auth-footer" style="margin-top: 32px;">
            Didn't receive the code? <a href="verify-otp.php?action=resend">Resend OTP</a>
        </div>
    </div>
</div>

<script>
    // JS auto-focus next input box logic
    const inputs = document.querySelectorAll('.otp-box');
    const form = document.getElementById('otpForm');

    inputs.forEach((input, index) => {
        // Handle input events
        input.addEventListener('input', (e) => {
            const value = e.target.value;
            if (value.length > 0 && index < inputs.length - 1) {
                inputs[index + 1].focus();
            }
        });

        // Handle keydown for backspaces
        input.addEventListener('keydown', (e) => {
            if (e.key === 'Backspace' && input.value.length === 0 && index > 0) {
                inputs[index - 1].focus();
            }
        });

        // Handle paste events
        input.addEventListener('paste', (e) => {
            e.preventDefault();
            const pasteData = e.clipboardData.getData('text').trim();
            if (pasteData.length === 6 && /^\d+$/.test(pasteData)) {
                for (let i = 0; i < 6; i++) {
                    inputs[i].value = pasteData[i];
                }
                // Focus the last input box
                inputs[5].focus();
            }
        });
    });
</script>

<?php require_once 'includes/footer.php'; ?>
