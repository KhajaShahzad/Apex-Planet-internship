<?php
/**
 * admin/delete_user.php
 * Admin — Delete a user record (POST only, with CSRF-style confirmation).
 */


require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../includes/auth.php';

require_role('admin');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ' . base_url('admin/dashboard.php'));
    exit;
}

$id = (int)($_POST['id'] ?? 0);

if ($id <= 0) {
    set_flash('error', 'Invalid user ID.');
    header('Location: ' . base_url('admin/dashboard.php'));
    exit;
}

// Prevent admin from deleting themselves
if ($id === (int)$_SESSION['user_id']) {
    set_flash('error', 'You cannot delete your own account.');
    header('Location: ' . base_url('admin/dashboard.php'));
    exit;
}

// Fetch username for the flash message
$fetch = $conn->prepare('SELECT username, profile_picture FROM users WHERE id=? LIMIT 1');
$fetch->bind_param('i', $id);
$fetch->execute();
$row = $fetch->get_result()->fetch_assoc();
$fetch->close();

if (!$row) {
    set_flash('error', 'User not found.');
    header('Location: ' . base_url('admin/dashboard.php'));
    exit;
}

// Delete profile picture file if it exists
if (!empty($row['profile_picture'])) {
    $filePath = __DIR__ . '/../' . $row['profile_picture'];
    if (file_exists($filePath)) {
        unlink($filePath);
    }
}

$del = $conn->prepare('DELETE FROM users WHERE id=?');
$del->bind_param('i', $id);
$del->execute();
$del->close();

set_flash('success', "User '{$row['username']}' deleted successfully.");
header('Location: ' . base_url('admin/dashboard.php'));
exit;
