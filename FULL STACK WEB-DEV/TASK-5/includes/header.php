<?php
// includes/header.php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once __DIR__ . '/auth.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="<?php echo isset($pageDescription) ? htmlspecialchars($pageDescription) : 'EduStream is a premium E-Learning portal with courses in web development, data science, and UI/UX design. Learn online with real-time progress tracking and email OTP verification.'; ?>">
    <meta name="keywords" content="e-learning, online courses, web development, data science, UI/UX, education portal, coding bootcamp">
    <meta name="author" content="EduStream - Apex Planet Capstone Project">
    <meta name="robots" content="index, follow">
    <title><?php echo isset($pageTitle) ? $pageTitle . " | EduStream" : "EduStream - Premium E-Learning Portal"; ?></title>
    <!-- FontAwesome Icon CDN -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <!-- Main Stylesheet -->
    <link rel="stylesheet" href="css/style.css">
</head>
<body>

<header>
    <div class="container navbar">
        <a href="index.php" class="logo">
            <i class="fa-solid fa-graduation-cap gradient-brand"></i>
            <span>EduStream</span>
        </a>
        
        <ul class="nav-links" id="navLinks">
            <li><a href="index.php" class="<?php echo ($activePage === 'home') ? 'active' : ''; ?>">Home</a></li>
            <li><a href="courses.php" class="<?php echo ($activePage === 'courses') ? 'active' : ''; ?>">Courses</a></li>
            <li><a href="jobs.php" class="<?php echo ($activePage === 'jobs') ? 'active' : ''; ?>">Jobs Board</a></li>
            <?php if (isLoggedIn()): ?>
                <li><a href="dashboard.php" class="<?php echo ($activePage === 'dashboard') ? 'active' : ''; ?>">Dashboard</a></li>
                <?php if (isAdmin()): ?>
                    <li><a href="admin.php" class="<?php echo ($activePage === 'admin') ? 'active' : ''; ?>">Admin Panel</a></li>
                <?php endif; ?>
            <?php endif; ?>
        </ul>
        
        <div class="nav-buttons">
            <?php if (isLoggedIn()): ?>
                <div class="flex align-center" style="gap: 16px;">
                    <span style="font-size: 14px; font-weight: 500; color: #ffffff;" class="hide-mobile">
                        <i class="fa-regular fa-user" style="margin-right: 6px; color: var(--secondary);"></i>
                        <?php echo htmlspecialchars($_SESSION['user_name']); ?>
                        <span style="font-size: 10px; padding: 2px 6px; border-radius: 10px; background: rgba(255,255,255,0.1); margin-left: 4px;">
                            <?php echo ucfirst($_SESSION['user_role']); ?>
                        </span>
                    </span>
                    <a href="logout.php" class="btn btn-secondary btn-sm"><i class="fa-solid fa-right-from-bracket"></i> Logout</a>
                </div>
            <?php else: ?>
                <a href="login.php" class="btn btn-secondary btn-sm">Log In</a>
                <a href="register.php" class="btn btn-primary btn-sm">Sign Up</a>
            <?php endif; ?>
            
            <button class="mobile-toggle" id="mobileMenuBtn" aria-label="Toggle Navigation">
                <i class="fa-solid fa-bars"></i>
            </button>
        </div>
    </div>
</header>

<!-- Mobile Menu Overlay -->
<div id="mobileMenu" style="display: none; position: fixed; top: 80px; left: 0; width: 100%; background: var(--bg-card); border-bottom: 1px solid var(--border-color); z-index: 99; padding: 24px;">
    <ul style="list-style: none; display: flex; flex-direction: column; gap: 16px;">
        <li><a href="index.php" style="display: block; font-size: 16px; font-weight: 500;" class="<?php echo ($activePage === 'home') ? 'active' : ''; ?>">Home</a></li>
        <li><a href="courses.php" style="display: block; font-size: 16px; font-weight: 500;" class="<?php echo ($activePage === 'courses') ? 'active' : ''; ?>">Courses</a></li>
        <li><a href="jobs.php" style="display: block; font-size: 16px; font-weight: 500;" class="<?php echo ($activePage === 'jobs') ? 'active' : ''; ?>">Jobs Board</a></li>
        <?php if (isLoggedIn()): ?>
            <li><a href="dashboard.php" style="display: block; font-size: 16px; font-weight: 500;" class="<?php echo ($activePage === 'dashboard') ? 'active' : ''; ?>">Dashboard</a></li>
            <?php if (isAdmin()): ?>
                <li><a href="admin.php" style="display: block; font-size: 16px; font-weight: 500;" class="<?php echo ($activePage === 'admin') ? 'active' : ''; ?>">Admin Panel</a></li>
            <?php endif; ?>
        <?php endif; ?>
    </ul>
</div>

<script>
    // Simple responsive mobile navigation toggle logic
    const mobileMenuBtn = document.getElementById('mobileMenuBtn');
    const mobileMenu = document.getElementById('mobileMenu');
    
    if (mobileMenuBtn && mobileMenu) {
        mobileMenuBtn.addEventListener('click', () => {
            const isVisible = mobileMenu.style.display === 'block';
            mobileMenu.style.display = isVisible ? 'none' : 'block';
            mobileMenuBtn.innerHTML = isVisible ? '<i class="fa-solid fa-bars"></i>' : '<i class="fa-solid fa-xmark"></i>';
        });
    }
</script>
<main style="min-height: calc(100vh - 280px);">
