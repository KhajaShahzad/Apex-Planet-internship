<?php
/**
 * index.php — Landing page / entry point
 * Redirects logged-in users to their dashboard.
 * Shows a hero landing page for guests.
 */

require_once __DIR__ . '/config/db.php';
require_once __DIR__ . '/includes/auth.php';

// Role-based redirect for logged-in users
if (is_logged_in()) {
    if (is_admin()) {
        header('Location: ' . base_url('admin/dashboard.php'));
    } else {
        header('Location: ' . base_url('user/dashboard.php'));
    }
    exit;
}

$pageTitle = 'Welcome';
include __DIR__ . '/includes/header.php';
?>

<!-- ===== HERO ===== -->
<section class="hero">
  <div>
    <div class="hero-inner">
      <div class="hero-badge">
        ⬡ Apex Planet Internship &nbsp;·&nbsp; Task 3 &nbsp;·&nbsp; Days 25–36
      </div>

      <h1>
        Secure <span>User Management</span><br />
        Built with PHP &amp; MySQL
      </h1>

      <p>
        A full-stack system featuring CRUD operations, session-based authentication,
        role-based access control, and secure profile management.
      </p>

      <div class="hero-actions">
        <a href="<?= base_url('auth/login.php') ?>"    class="btn-primary"   id="heroLoginBtn">🔐 Sign In</a>
        <a href="<?= base_url('auth/register.php') ?>" class="btn-secondary" id="heroRegisterBtn">🚀 Create Account</a>
      </div>
    </div>

    <!-- Feature Cards -->
    <div class="feature-grid container">
      <div class="feature-card">
        <div class="feature-icon">🗄️</div>
        <h3>Database Design</h3>
        <p>Normalised MySQL schema (3NF) with roles &amp; users tables and foreign key constraints.</p>
      </div>
      <div class="feature-card">
        <div class="feature-icon">⚙️</div>
        <h3>CRUD Operations</h3>
        <p>Add, view, edit, and delete users from a clean admin dashboard with search &amp; pagination.</p>
      </div>
      <div class="feature-card">
        <div class="feature-icon">🔐</div>
        <h3>Authentication</h3>
        <p>Secure login with <code>password_hash</code>, session fixation prevention, and role-based redirects.</p>
      </div>
      <div class="feature-card">
        <div class="feature-icon">🛡️</div>
        <h3>Security</h3>
        <p>Prepared statements throughout, server-side validation, XSS prevention with <code>htmlspecialchars</code>.</p>
      </div>
      <div class="feature-card">
        <div class="feature-icon">🖼️</div>
        <h3>Profile Management</h3>
        <p>Upload profile pictures with MIME-type &amp; 2 MB server-side validation. Live client preview.</p>
      </div>
      <div class="feature-card">
        <div class="feature-icon">🎨</div>
        <h3>Premium UI</h3>
        <p>Dark glassmorphism theme, teal accent, smooth animations, and fully responsive layout.</p>
      </div>
    </div>
  </div>
</section>

<?php include __DIR__ . '/includes/footer.php'; ?>
