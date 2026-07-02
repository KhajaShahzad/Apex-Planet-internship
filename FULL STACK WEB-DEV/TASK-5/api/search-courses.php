<?php
// api/search-courses.php
require_once __DIR__ . '/../includes/db.php';

$search = trim($_GET['search'] ?? '');
$category = trim($_GET['category'] ?? '');
$difficulty = trim($_GET['difficulty'] ?? '');

// Base SQL query
$query = "SELECT * FROM courses WHERE 1=1";
$params = [];

// Apply Search Text
if ($search !== '') {
    $query .= " AND (title LIKE ? OR description LIKE ? OR category LIKE ?)";
    $searchWildcard = '%' . $search . '%';
    $params[] = $searchWildcard;
    $params[] = $searchWildcard;
    $params[] = $searchWildcard;
}

// Apply Category Filter
if ($category !== '') {
    $query .= " AND category = ?";
    $params[] = $category;
}

// Apply Difficulty Filter
if ($difficulty !== '') {
    $query .= " AND difficulty = ?";
    $params[] = $difficulty;
}

// Order results
$query .= " ORDER BY id DESC";

try {
    $stmt = $pdo->prepare($query);
    $stmt->execute($params);
    $courses = $stmt->fetchAll();

    if (!empty($courses)) {
        foreach ($courses as $course) {
            ?>
            <div class="course-card">
                <div class="course-image">
                    <span class="course-badge"><?php echo htmlspecialchars($course['category']); ?></span>
                    <div class="course-image-overlay">
                        <i class="fa-solid fa-laptop-code" style="font-size: 48px; margin-bottom: 12px; color: var(--secondary); display: block;"></i>
                        <?php echo htmlspecialchars($course['title']); ?>
                    </div>
                </div>
                <div class="course-content">
                    <div class="course-meta">
                        <span><i class="fa-solid fa-signal"></i> <?php echo htmlspecialchars($course['difficulty']); ?></span>
                        <span><i class="fa-regular fa-clock"></i> Self-paced</span>
                    </div>
                    <h3><?php echo htmlspecialchars($course['title']); ?></h3>
                    <p><?php echo htmlspecialchars(substr($course['description'], 0, 115)) . '...'; ?></p>
                    <div class="course-footer">
                        <div class="course-price">$<?php echo htmlspecialchars($course['price']); ?></div>
                        <a href="course-details.php?id=<?php echo $course['id']; ?>" class="btn btn-primary btn-sm">View Details</a>
                    </div>
                </div>
            </div>
            <?php
        }
    } else {
        ?>
        <div style="grid-column: span 3; text-align: center; padding: 48px; color: var(--text-muted); width: 100%;">
            <i class="fa-regular fa-folder-open" style="font-size: 48px; margin-bottom: 16px; display: block; color: var(--text-muted);"></i>
            <p>No courses match your search query and filters.</p>
        </div>
        <?php
    }
} catch (PDOException $e) {
    echo '<div style="grid-column: span 3; color: var(--danger); text-align: center;">Search endpoint failed: ' . htmlspecialchars($e->getMessage()) . '</div>';
}
?>
