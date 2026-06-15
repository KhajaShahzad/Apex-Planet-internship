<?php
/**
 * includes/header.php
 * Common HTML head + navigation bar.
 * Expects $pageTitle to be set by the including page.
 */

if (session_status() === PHP_SESSION_NONE) session_start();
if (!isset($pageTitle)) $pageTitle = 'UserHub';

$user = function_exists('current_user') ? current_user() : null;
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <meta name="description" content="UserHub — A secure PHP/MySQL User Management System with CRUD, authentication, and profile management." />
  <title><?= htmlspecialchars($pageTitle) ?> | UserHub</title>

  <!-- Google Fonts -->
  <link rel="preconnect" href="https://fonts.googleapis.com" />
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&family=Outfit:wght@400;600;700;800&display=swap" rel="stylesheet" />

  <!-- Main Stylesheet -->
  <link rel="stylesheet" href="<?= base_url('assets/css/style.css') ?>" />
</head>
<body>

<!-- ===== NAVBAR ===== -->
<nav class="navbar" id="main-nav">
  <div class="nav-container">
    <a href="<?= base_url('index.php') ?>" class="nav-brand">
      <span class="brand-icon">⬡</span>
      <span>User<strong>Hub</strong></span>
    </a>

    <button class="nav-toggle" id="navToggle" aria-label="Toggle navigation">
      <span></span><span></span><span></span>
    </button>

    <ul class="nav-links" id="navLinks">
      <?php if ($user): ?>
        <?php if ($user['role'] === 'admin'): ?>
          <li><a href="<?= base_url('admin/dashboard.php') ?>">🏠 Dashboard</a></li>
          <li><a href="<?= base_url('admin/add_user.php') ?>">➕ Add User</a></li>
        <?php else: ?>
          <li><a href="<?= base_url('user/dashboard.php') ?>">🏠 Dashboard</a></li>
        <?php endif; ?>
        <li><a href="<?= base_url('user/profile.php') ?>">👤 Profile</a></li>
        <li class="nav-user">
          <span class="avatar-chip">
            <?php if (!empty($user['picture'])): ?>
              <img src="<?= base_url($user['picture']) ?>" alt="avatar" />
            <?php else: ?>
              <?= strtoupper(substr($user['username'], 0, 1)) ?>
            <?php endif; ?>
            <?= htmlspecialchars($user['username']) ?>
          </span>
        </li>
        <li><a href="<?= base_url('auth/logout.php') ?>" class="btn-nav-logout">Logout</a></li>
      <?php else: ?>
        <li><a href="<?= base_url('auth/login.php') ?>">Login</a></li>
        <li><a href="<?= base_url('auth/register.php') ?>" class="btn-nav-cta">Register</a></li>
      <?php endif; ?>
    </ul>
  </div>
</nav>

<!-- Flash messages rendered right after nav -->
<div class="flash-container">
  <?php if (function_exists('render_flash')) render_flash(); ?>
</div>

<main class="main-content">
