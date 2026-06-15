<?php
/**
 * admin/dashboard.php
 * Admin CRUD Dashboard — lists all users, supports search & pagination.
 */


require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../includes/auth.php';

require_role('admin');

// --- Search ---
$search = trim($_GET['q'] ?? '');
$page   = max(1, (int)($_GET['page'] ?? 1));
$limit  = 10;
$offset = ($page - 1) * $limit;

if ($search !== '') {
    $like  = "%$search%";
    $total_stmt = $conn->prepare(
        'SELECT COUNT(*) FROM users u JOIN roles r ON r.id=u.role_id
         WHERE u.username LIKE ? OR u.email LIKE ? OR r.name LIKE ?'
    );
    $total_stmt->bind_param('sss', $like, $like, $like);
    $total_stmt->execute();
    $total_stmt->bind_result($total);
    $total_stmt->fetch();
    $total_stmt->close();

    $stmt = $conn->prepare(
        'SELECT u.id, u.username, u.email, u.profile_picture, r.name AS role, u.created_at
         FROM users u JOIN roles r ON r.id=u.role_id
         WHERE u.username LIKE ? OR u.email LIKE ? OR r.name LIKE ?
         ORDER BY u.created_at DESC
         LIMIT ? OFFSET ?'
    );
    $stmt->bind_param('sssii', $like, $like, $like, $limit, $offset);
} else {
    $total_stmt = $conn->prepare('SELECT COUNT(*) FROM users');
    $total_stmt->execute();
    $total_stmt->bind_result($total);
    $total_stmt->fetch();
    $total_stmt->close();

    $stmt = $conn->prepare(
        'SELECT u.id, u.username, u.email, u.profile_picture, r.name AS role, u.created_at
         FROM users u JOIN roles r ON r.id=u.role_id
         ORDER BY u.created_at DESC
         LIMIT ? OFFSET ?'
    );
    $stmt->bind_param('ii', $limit, $offset);
}

$stmt->execute();
$users     = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt->close();

$totalPages = (int)ceil($total / $limit);

$pageTitle = 'Admin Dashboard';
include __DIR__ . '/../includes/header.php';
?>

<div class="container">
  <!-- Page Header -->
  <div class="page-header">
    <div>
      <h1 class="page-title">👥 User Management</h1>
      <p class="page-sub">Total users: <strong><?= $total ?></strong></p>
    </div>
    <a href="<?= base_url('admin/add_user.php') ?>" class="btn-primary" id="addUserBtn">
      ➕ Add New User
    </a>
  </div>

  <!-- Search Bar -->
  <form method="GET" action="" class="search-form" id="searchForm">
    <div class="input-wrap search-wrap">
      <span class="input-icon">🔍</span>
      <input type="text" name="q" value="<?= e($search) ?>"
             placeholder="Search by name, email or role…" id="searchInput" />
    </div>
    <button type="submit" class="btn-primary">Search</button>
    <?php if ($search): ?>
      <a href="<?= base_url('admin/dashboard.php') ?>" class="btn-secondary">✖ Clear</a>
    <?php endif; ?>
  </form>

  <!-- Users Table -->
  <div class="card table-card">
    <?php if (empty($users)): ?>
      <div class="empty-state">
        <p>😕 No users found<?= $search ? ' for "<em>' . e($search) . '</em>"' : '' ?>.</p>
        <a href="<?= base_url('admin/add_user.php') ?>" class="btn-primary mt-2">Add First User</a>
      </div>
    <?php else: ?>
    <div class="table-responsive">
      <table class="data-table" id="usersTable">
        <thead>
          <tr>
            <th>#</th>
            <th>Avatar</th>
            <th>Username</th>
            <th>Email</th>
            <th>Role</th>
            <th>Joined</th>
            <th>Actions</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($users as $i => $u): ?>
          <tr>
            <td><?= $offset + $i + 1 ?></td>
            <td>
              <div class="table-avatar">
                <?php if ($u['profile_picture']): ?>
                  <img src="<?= base_url($u['profile_picture']) ?>" alt="avatar" />
                <?php else: ?>
                  <?= strtoupper(substr($u['username'], 0, 1)) ?>
                <?php endif; ?>
              </div>
            </td>
            <td><strong><?= e($u['username']) ?></strong></td>
            <td><?= e($u['email']) ?></td>
            <td>
              <span class="badge badge-<?= $u['role'] === 'admin' ? 'admin' : 'user' ?>">
                <?= e($u['role']) ?>
              </span>
            </td>
            <td><?= date('d M Y', strtotime($u['created_at'])) ?></td>
            <td class="actions">
              <a href="<?= base_url('admin/edit_user.php?id=' . $u['id']) ?>"
                 class="btn-action btn-edit" title="Edit">✏️ Edit</a>
              <button type="button"
                      class="btn-action btn-delete"
                      data-id="<?= $u['id'] ?>"
                      data-name="<?= e($u['username']) ?>"
                      title="Delete">🗑 Delete</button>
            </td>
          </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>

    <!-- Pagination -->
    <?php if ($totalPages > 1): ?>
    <div class="pagination">
      <?php for ($p = 1; $p <= $totalPages; $p++): ?>
        <a href="?page=<?= $p ?><?= $search ? '&q=' . urlencode($search) : '' ?>"
           class="page-btn <?= $p === $page ? 'active' : '' ?>"><?= $p ?></a>
      <?php endfor; ?>
    </div>
    <?php endif; ?>
    <?php endif; ?>
  </div>
</div>

<!-- Delete Confirmation Modal -->
<div class="modal-overlay" id="deleteModal">
  <div class="modal-box">
    <div class="modal-icon">🗑️</div>
    <h2>Delete User</h2>
    <p>Are you sure you want to delete <strong id="deleteUserName"></strong>?<br>
       <span class="text-muted">This action cannot be undone.</span></p>
    <div class="modal-actions">
      <button class="btn-secondary" id="cancelDelete">Cancel</button>
      <form method="POST" action="<?= base_url('admin/delete_user.php') ?>" id="deleteForm">
        <input type="hidden" name="id" id="deleteUserId" />
        <button type="submit" class="btn-danger" id="confirmDelete">Yes, Delete</button>
      </form>
    </div>
  </div>
</div>

<?php include __DIR__ . '/../includes/footer.php'; ?>
