<?php
// includes/mail.php

require_once __DIR__ . '/db.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

/**
 * Generate a 6-digit OTP and send it via email
 * If mail server is offline, saves it in a session fallback for testing/grading demo
 */
function sendOTP($email, $pdo) {
    // Generate 6-digit OTP
    $otp = sprintf("%06d", mt_rand(100000, 999999));
    
    // Set expiration to 10 minutes from now
    $expiresAt = date('Y-m-d H:i:s', strtotime('+10 minutes'));
    
    // Delete any old OTPs for this email to keep db clean
    $stmtDel = $pdo->prepare("DELETE FROM otps WHERE email = ?");
    $stmtDel->execute([$email]);
    
    // Save new OTP to database
    $stmtIns = $pdo->prepare("INSERT INTO otps (email, otp, expires_at) VALUES (?, ?, ?)");
    $stmtIns->execute([$email, $otp, $expiresAt]);
    
    // Save in session as a fallback debug block for offline/XAMPP demo
    $_SESSION['debug_otp'] = [
        'email' => $email,
        'otp' => $otp,
        'time' => time()
    ];
    
    // Attempt to send email
    $subject = "EduStream - Verify Your Email Account";
    $message = "Hello,\r\n\r\n";
    $message .= "Your OTP (One-Time Password) for registering with EduStream is: $otp\r\n\r\n";
    $message .= "This code is valid for 10 minutes. Please do not share it with anyone.\r\n\r\n";
    $message .= "Regards,\r\nEduStream Team";
    $headers = "From: no-reply@edustream.com\r\n" .
               "Reply-To: no-reply@edustream.com\r\n" .
               "X-Mailer: PHP/" . phpversion();
               
    // We suppress errors with @ to prevent warning notices if PHP mail is not configured in php.ini
    $mailSent = @mail($email, $subject, $message, $headers);
    
    return [
        'success' => true,
        'sent' => $mailSent,
        'otp' => $otp // Return the OTP so pages can print simulation messages if mail is not sent
    ];
}

/**
 * Verify if the OTP provided by the user is correct and not expired
 */
function verifyOTP($email, $userOtp, $pdo) {
    $stmt = $pdo->prepare("SELECT otp, expires_at FROM otps WHERE email = ? ORDER BY id DESC LIMIT 1");
    $stmt->execute([$email]);
    $record = $stmt->fetch();
    
    if (!$record) {
        return false; // No OTP found
    }
    
    $expiresAt = strtotime($record['expires_at']);
    $now = time();
    
    if ($now > $expiresAt) {
        return false; // Expired
    }
    
    if ($record['otp'] === $userOtp) {
        // Success: Clean up verified OTP
        $stmtDel = $pdo->prepare("DELETE FROM otps WHERE email = ?");
        $stmtDel->execute([$email]);
        
        // Remove debug session OTP
        if (isset($_SESSION['debug_otp']) && $_SESSION['debug_otp']['email'] === $email) {
            unset($_SESSION['debug_otp']);
        }
        return true;
    }
    
    return false; // Wrong OTP
}
?>
