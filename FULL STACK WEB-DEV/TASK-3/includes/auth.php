<?php
/**
 * includes/auth.php
 * Session helpers, role guards, and current-user utilities.
 */

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

/** Redirect to login if not authenticated. */
function require_login(): void
{
    if (empty($_SESSION['user_id'])) {
        header('Location: ' . base_url('auth/login.php'));
        exit;
    }
}

/**
 * Redirect with a 403 if the logged-in user doesn't hold the required role.
 * @param string $role  'admin' | 'user'
 */
function require_role(string $role): void
{
    require_login();
    if (($_SESSION['role'] ?? '') !== $role) {
        http_response_code(403);
        include __DIR__ . '/../includes/header.php';
        echo '<div class="container mt-5 text-center">
                <div class="card glass p-5">
                  <h2 class="text-danger">⛔ Access Denied</h2>
                  <p>You do not have permission to view this page.</p>
                  <a href="' . base_url('index.php') . '" class="btn btn-primary mt-3">Go Home</a>
                </div>
              </div>';
        include __DIR__ . '/../includes/footer.php';
        exit;
    }
}

/** Return the current user's session data or null. */
function current_user(): ?array
{
    if (empty($_SESSION['user_id'])) {
        return null;
    }
    return [
        'id'       => $_SESSION['user_id'],
        'username' => $_SESSION['username'],
        'email'    => $_SESSION['email'],
        'role'     => $_SESSION['role'],
        'picture'  => $_SESSION['profile_picture'] ?? null,
    ];
}

/** True if the current user is an admin. */
function is_admin(): bool
{
    return ($_SESSION['role'] ?? '') === 'admin';
}

/** True if any user is logged in. */
function is_logged_in(): bool
{
    return !empty($_SESSION['user_id']);
}

/**
 * Build an absolute-root URL from a relative path.
 * Auto-detects the actual folder name from the filesystem —
 * works regardless of casing (TASK-3, task3, Task3, etc.).
 */
function base_url(string $path = ''): string
{
    static $base = null;
    if ($base === null) {
        if (defined('APP_ROOT')) {
            $base = APP_ROOT;
        } else {
            // __DIR__ = .../htdocs/TASK-3/includes
            // Go up one level to reach the app root
            $appRoot = realpath(__DIR__ . DIRECTORY_SEPARATOR . '..');
            $docRoot = realpath($_SERVER['DOCUMENT_ROOT'] ?? '');

            if ($docRoot && $appRoot && strpos($appRoot, $docRoot) === 0) {
                $relative = substr($appRoot, strlen($docRoot));
                $base     = str_replace(DIRECTORY_SEPARATOR, '/', $relative);
            } else {
                // Fallback: just use the folder name
                $base = '/' . basename($appRoot);
            }
            if (empty($base)) $base = '/';
        }
    }
    return rtrim($base, '/') . '/' . ltrim($path, '/');
}

/**
 * Flash-message helpers (one-time session messages).
 */
function set_flash(string $type, string $message): void
{
    $_SESSION['flash'] = ['type' => $type, 'message' => $message];
}

function get_flash(): ?array
{
    if (!isset($_SESSION['flash'])) return null;
    $flash = $_SESSION['flash'];
    unset($_SESSION['flash']);
    return $flash;
}

function render_flash(): void
{
    $f = get_flash();
    if (!$f) return;
    $icon = match($f['type']) {
        'success' => '✅', 'error' => '❌', 'warning' => '⚠️', default => 'ℹ️'
    };
    echo "<div class=\"flash flash-{$f['type']}\">$icon {$f['message']}</div>";
}

/** Sanitise output (XSS prevention). */
function e(string $val): string
{
    return htmlspecialchars($val, ENT_QUOTES | ENT_HTML5, 'UTF-8');
}
