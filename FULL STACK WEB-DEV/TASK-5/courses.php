<?php
// courses.php
$pageTitle = "Explore Courses";
$activePage = "courses";

require_once 'includes/db.php';
require_once 'includes/auth.php';

// Initial server-side query (to render on page load before any user search/filters)
try {
    $stmt = $pdo->query("SELECT * FROM courses ORDER BY id DESC");
    $courses = $stmt->fetchAll();
} catch (PDOException $e) {
    $courses = [];
}
?>

<?php require_once 'includes/header.php'; ?>

<!-- Page Hero Header -->
<section class="details-header" style="padding: 40px 0 20px 0;">
    <div class="container">
        <h1 style="font-size: 34px;"><i class="fa-solid fa-compass" style="color: var(--primary); margin-right: 12px;"></i>Explore Course Catalog</h1>
        <p style="color: var(--text-muted); margin-top: 8px;">Filter by category, difficulty, or search terms to find your next educational pathway.</p>
    </div>
</section>

<!-- Main Course Directory -->
<section class="catalog-section">
    <div class="container">
        <!-- Interactive Search & Filtering Header -->
        <div class="search-filter-bar">
            <!-- Search field -->
            <div class="search-input-group">
                <i class="fa-solid fa-magnifying-glass search-icon-fixed"></i>
                <input type="text" id="searchInput" placeholder="Search courses by title or keyword..." autocomplete="off">
            </div>
            
            <!-- Filters -->
            <div class="filter-controls">
                <select id="categoryFilter" class="filter-select">
                    <option value="">All Categories</option>
                    <option value="Web Development">Web Development</option>
                    <option value="Data Science">Data Science</option>
                    <option value="UI/UX Design">UI/UX Design</option>
                    <option value="Mobile Dev">Mobile Dev</option>
                </select>
                
                <select id="difficultyFilter" class="filter-select">
                    <option value="">All Levels</option>
                    <option value="Beginner">Beginner</option>
                    <option value="Intermediate">Intermediate</option>
                    <option value="Advanced">Advanced</option>
                </select>
            </div>
        </div>
        
        <!-- Live Courses Grid (AJAX will target this element) -->
        <div class="grid course-grid" id="coursesGrid">
            <?php if (!empty($courses)): ?>
                <?php foreach ($courses as $course): ?>
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
                <?php endforeach; ?>
            <?php else: ?>
                <div style="grid-column: span 3; text-align: center; padding: 48px; color: var(--text-muted);">
                    <i class="fa-regular fa-folder-open" style="font-size: 48px; margin-bottom: 16px;"></i>
                    <p>No courses are currently available in the database.</p>
                </div>
            <?php endif; ?>
        </div>
    </div>
</section>

<!-- Link to interactive search controller -->
<script src="js/search.js"></script>

<?php require_once 'includes/footer.php'; ?>
