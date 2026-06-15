<?php
/**
 * auth/logout.php
 * Destroy session and redirect to login.
 */


require_once __DIR__ . '/../includes/auth.php';

session_unset();
session_destroy();

// Expire session cookie
if (ini_get('session.use_cookies')) {
    $params = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000,
        $params['path'], $params['domain'],
        $params['secure'], $params['httponly']
    );
}

// Start fresh session to carry flash
session_start();
set_flash('success', 'You have been logged out successfully.');
header('Location: ' . base_url('auth/login.php'));
exit;
