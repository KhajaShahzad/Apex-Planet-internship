<?php
// course-details.php
require_once 'includes/db.php';
require_once 'includes/auth.php';

$courseId = isset($_GET['id']) ? intval($_GET['id']) : 0;

if ($courseId <= 0) {
    header("Location: courses.php");
    exit;
}

// Fetch Course details
try {
    $stmt = $pdo->prepare("SELECT * FROM courses WHERE id = ?");
    $stmt->execute([$courseId]);
    $course = $stmt->fetch();

    if (!$course) {
        header("Location: courses.php");
        exit;
    }

    // Fetch Syllabus/Lessons
    $stmtLessons = $pdo->prepare("SELECT * FROM lessons WHERE course_id = ? ORDER BY sort_order ASC");
    $stmtLessons->execute([$courseId]);
    $lessons = $stmtLessons->fetchAll();
} catch (PDOException $e) {
    die("Error retrieving course details: " . $e->getMessage());
}

$isEnrolled = false;
$enrollmentData = null;

if (isLoggedIn()) {
    $userId = $_SESSION['user_id'];
    try {
        $stmtEnroll = $pdo->prepare("SELECT * FROM enrollments WHERE user_id = ? AND course_id = ?");
        $stmtEnroll->execute([$userId, $courseId]);
        $enrollmentData = $stmtEnroll->fetch();
        if ($enrollmentData) {
            $isEnrolled = true;
        }
    } catch (PDOException $e) {
        // Suppress or handle
    }
}

// Handle enrollment action
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'enroll') {
    requireLogin();
    $userId = $_SESSION['user_id'];

    if (!$isEnrolled) {
        try {
            $stmtInsert = $pdo->prepare("INSERT INTO enrollments (user_id, course_id, progress) VALUES (?, ?, 0)");
            $stmtInsert->execute([$userId, $courseId]);
            $isEnrolled = true;
            header("Location: course-player.php?course_id=" . $courseId);
            exit;
        } catch (PDOException $e) {
            $enrollError = "Failed to enroll: " . $e->getMessage();
        }
    }
}

$pageTitle = $course['title'];
$activePage = "courses";
require_once 'includes/header.php';
?>

<!-- Header Banner -->
<section class="details-header">
    <div class="container details-grid">
        <div class="course-info">
            <span class="course-badge" style="position: static; display: inline-block; margin-bottom: 16px; font-size: 13px;">
                <?php echo htmlspecialchars($course['category']); ?>
            </span>
            <h1><?php echo htmlspecialchars($course['title']); ?></h1>
            <p class="course-description"><?php echo htmlspecialchars($course['description']); ?></p>
            
            <div class="flex align-center" style="gap: 24px; color: var(--text-muted); font-size: 14px;">
                <span><i class="fa-solid fa-signal" style="color: var(--secondary); margin-right: 6px;"></i>Difficulty: <strong><?php echo htmlspecialchars($course['difficulty']); ?></strong></span>
                <span><i class="fa-regular fa-folder-open" style="color: var(--secondary); margin-right: 6px;"></i>Curriculum: <strong><?php echo count($lessons); ?> Lessons</strong></span>
                <span><i class="fa-regular fa-clock" style="color: var(--secondary); margin-right: 6px;"></i>Duration: <strong>
                    <?php 
                    $totalMinutes = array_sum(array_column($lessons, 'duration'));
                    echo $totalMinutes > 60 ? floor($totalMinutes / 60) . "h " . ($totalMinutes % 60) . "m" : $totalMinutes . "m"; 
                    ?></strong>
                </span>
            </div>
        </div>
        
        <div><!-- Spacer for purchase card grid mapping --></div>
    </div>
</section>

<!-- Curriculum / Purchase Area -->
<div class="container" style="margin-top: 40px;">
    <div class="details-grid">
        <!-- Curriculum Details -->
        <div>
            <div class="curriculum-section">
                <h2>Course Curriculum</h2>
                <p style="color: var(--text-muted); margin-top: 4px;">Explore the comprehensive modules engineered in this course syllabus</p>
                
                <div class="curriculum-list">
                    <?php if (!empty($lessons)): ?>
                        <?php foreach ($lessons as $index => $lesson): ?>
                            <div class="curriculum-item">
                                <div class="curriculum-title">
                                    <span style="width: 24px; height: 24px; border-radius: 50%; background: var(--bg-input); border: 1px solid var(--border-color); display: flex; align-items: center; justify-content: center; font-size: 11px; font-weight:700; color: var(--secondary);">
                                        <?php echo $index + 1; ?>
                                    </span>
                                    <span><?php echo htmlspecialchars($lesson['title']); ?></span>
                                </div>
                                <div class="curriculum-duration">
                                    <i class="fa-regular fa-circle-play" style="margin-right: 6px; color: var(--text-muted);"></i>
                                    <?php echo htmlspecialchars($lesson['duration']); ?> mins
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <p style="color: var(--text-muted);">No lessons have been uploaded for this course yet.</p>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        
        <!-- Sticky Purchase Card -->
        <div>
            <div class="purchase-card">
                <i class="fa-solid fa-graduation-cap" style="font-size: 64px; color: var(--primary); margin-bottom: 16px; display: block;"></i>
                <div style="font-size: 14px; text-transform: uppercase; letter-spacing: 1px; color: var(--text-muted); margin-bottom: 8px;">Single Purchase License</div>
                <h3>$<?php echo htmlspecialchars($course['price']); ?></h3>
                
                <?php if (isset($enrollError)): ?>
                    <div class="alert alert-danger" style="padding: 10px; font-size: 12px; margin-bottom: 16px;">
                        <?php echo htmlspecialchars($enrollError); ?>
                    </div>
                <?php endif; ?>

                <?php if ($isEnrolled): ?>
                    <a href="course-player.php?course_id=<?php echo $courseId; ?>" class="btn btn-primary btn-block">
                        <i class="fa-solid fa-play"></i> Go to Learning Portal
                    </a>
                    <div style="margin-top: 12px; font-size: 13px; color: var(--success);">
                        <i class="fa-solid fa-circle-check"></i> You are enrolled in this course
                    </div>
                <?php else: ?>
                    <?php if (isLoggedIn()): ?>
                        <form action="course-details.php?id=<?php echo $courseId; ?>" method="POST">
                            <input type="hidden" name="action" value="enroll">
                            <button type="submit" class="btn btn-primary btn-block">
                                <i class="fa-solid fa-cart-shopping"></i> Enroll Now
                            </button>
                        </form>
                    <?php else: ?>
                        <a href="login.php" class="btn btn-primary btn-block">
                            <i class="fa-solid fa-right-to-bracket"></i> Log In to Enroll
                        </a>
                    <?php endif; ?>
                    <div style="margin-top: 16px; font-size: 13px; color: var(--text-muted);">
                        <i class="fa-solid fa-infinity"></i> Lifetime Access &bull; Certificate Included
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<?php require_once 'includes/footer.php'; ?>
