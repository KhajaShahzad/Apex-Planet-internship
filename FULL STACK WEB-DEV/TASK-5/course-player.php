<?php
// course-player.php
require_once 'includes/db.php';
require_once 'includes/auth.php';

/**
 * Check if the given video URL is a YouTube URL
 */
function isYouTubeUrl($url) {
    return preg_match('%(?:youtube(?:-nocookie)?\.com/(?:[^/]+/.+/|(?:v|e(?:mbed)?)/|.*[?&]v=)|youtu\.be/)([^"&?/ ]{11})%i', $url);
}

/**
 * Convert standard YouTube URL to Embed format
 */
function getYouTubeEmbedUrl($url) {
    if (preg_match('%(?:youtube(?:-nocookie)?\.com/(?:[^/]+/.+/|(?:v|e(?:mbed)?)/|.*[?&]v=)|youtu\.be/)([^"&?/ ]{11})%i', $url, $match)) {
        return "https://www.youtube.com/embed/" . $match[1];
    }
    return $url;
}

// Enforce login
requireLogin();

$userId = $_SESSION['user_id'];
$courseId = isset($_GET['course_id']) ? intval($_GET['course_id']) : 0;

if ($courseId <= 0) {
    header("Location: dashboard.php");
    exit;
}

// 1. Verify student enrollment
try {
    $stmtEnroll = $pdo->prepare("SELECT * FROM enrollments WHERE user_id = ? AND course_id = ?");
    $stmtEnroll->execute([$userId, $courseId]);
    $enrollment = $stmtEnroll->fetch();

    if (!$enrollment) {
        // Redirect to details page to enroll
        header("Location: course-details.php?id=" . $courseId);
        exit;
    }

    // 2. Fetch Course Details
    $stmtCourse = $pdo->prepare("SELECT title FROM courses WHERE id = ?");
    $stmtCourse->execute([$courseId]);
    $course = $stmtCourse->fetch();

    // 3. Fetch Lessons
    $stmtLessons = $pdo->prepare("SELECT * FROM lessons WHERE course_id = ? ORDER BY sort_order ASC");
    $stmtLessons->execute([$courseId]);
    $lessons = $stmtLessons->fetchAll();

    if (empty($lessons)) {
        die("No lessons available for this course yet.");
    }

    // 4. Fetch Completed Lessons List
    $stmtCompleted = $pdo->prepare("SELECT lesson_id FROM lesson_progress WHERE user_id = ? AND lesson_id IN (SELECT id FROM lessons WHERE course_id = ?)");
    $stmtCompleted->execute([$userId, $courseId]);
    $completedLessonIds = $stmtCompleted->fetchAll(PDO::FETCH_COLUMN);

} catch (PDOException $e) {
    die("Database query error: " . $e->getMessage());
}

// Determine active lesson
$activeLessonId = isset($_GET['lesson_id']) ? intval($_GET['lesson_id']) : 0;
$activeLesson = null;

if ($activeLessonId > 0) {
    foreach ($lessons as $l) {
        if ($l['id'] === $activeLessonId) {
            $activeLesson = $l;
            break;
        }
    }
}

// Fallback to first lesson if none specified or invalid
if (!$activeLesson) {
    $activeLesson = $lessons[0];
    $activeLessonId = $activeLesson['id'];
}

// Handle Lesson Completion Toggle Submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'toggle_complete') {
    $targetLessonId = intval($_POST['lesson_id'] ?? 0);
    $markCompleted = intval($_POST['completed'] ?? 0);

    if ($targetLessonId > 0) {
        try {
            if ($markCompleted === 1) {
                // Insert progress record
                $stmtMark = $pdo->prepare("INSERT IGNORE INTO lesson_progress (user_id, lesson_id) VALUES (?, ?)");
                $stmtMark->execute([$userId, $targetLessonId]);
            } else {
                // Delete progress record
                $stmtUnmark = $pdo->prepare("DELETE FROM lesson_progress WHERE user_id = ? AND lesson_id = ?");
                $stmtUnmark->execute([$userId, $targetLessonId]);
            }

            // Recalculate and update overall course progress percentage
            $stmtCompletedCount = $pdo->prepare("SELECT COUNT(*) FROM lesson_progress WHERE user_id = ? AND lesson_id IN (SELECT id FROM lessons WHERE course_id = ?)");
            $stmtCompletedCount->execute([$userId, $courseId]);
            $completedCount = $stmtCompletedCount->fetchColumn();

            $totalLessons = count($lessons);
            $progressPercent = $totalLessons > 0 ? round(($completedCount / $totalLessons) * 100) : 0;

            // Update enrollment progress
            $completedAt = ($progressPercent === 100) ? date('Y-m-d H:i:s') : null;
            $stmtUpdateEnroll = $pdo->prepare("UPDATE enrollments SET progress = ?, completed_at = ? WHERE user_id = ? AND course_id = ?");
            $stmtUpdateEnroll->execute([$progressPercent, $completedAt, $userId, $courseId]);

            // Redirect back to avoid form resubmission
            header("Location: course-player.php?course_id={$courseId}&lesson_id={$targetLessonId}");
            exit;

        } catch (PDOException $e) {
            $toggleError = "Progress update failed: " . $e->getMessage();
        }
    }
}

$isCurrentLessonCompleted = in_array($activeLessonId, $completedLessonIds);

$pageTitle = "Learning - " . $course['title'];
$activePage = "dashboard";
require_once 'includes/header.php';
?>

<div class="container player-page">
    <!-- Breadcrumb back link -->
    <div style="margin-bottom: 24px;">
        <a href="dashboard.php" class="btn btn-secondary btn-sm">
            <i class="fa-solid fa-arrow-left"></i> Back to Dashboard
        </a>
    </div>

    <div class="player-grid">
        <!-- Left: Video Player & Controls -->
        <div>
            <div class="video-container">
                <?php if (isYouTubeUrl($activeLesson['video_url'])): ?>
                    <!-- Responsive YouTube Iframe Embed -->
                    <iframe src="<?php echo htmlspecialchars(getYouTubeEmbedUrl($activeLesson['video_url'])); ?>" 
                            style="width: 100%; height: 100%; border: none;" 
                            allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share" 
                            allowfullscreen>
                    </iframe>
                <?php else: ?>
                    <!-- HTML5 video tags supporting generic direct video files -->
                    <video src="<?php echo htmlspecialchars($activeLesson['video_url']); ?>" controls autoplay muted></video>
                <?php endif; ?>
            </div>
            
            <div class="lesson-player-info">
                <div class="flex justify-between align-center" style="flex-wrap: wrap; gap: 16px; margin-bottom: 12px;">
                    <h2><?php echo htmlspecialchars($activeLesson['title']); ?></h2>
                    
                    <!-- Progress Toggle Form -->
                    <form action="course-player.php?course_id=<?php echo $courseId; ?>&lesson_id=<?php echo $activeLessonId; ?>" method="POST">
                        <input type="hidden" name="lesson_id" value="<?php echo $activeLessonId; ?>">
                        <?php if ($isCurrentLessonCompleted): ?>
                            <input type="hidden" name="completed" value="0">
                            <input type="hidden" name="action" value="toggle_complete">
                            <button type="submit" class="btn btn-secondary btn-sm" style="color: var(--success); border-color: var(--success);">
                                <i class="fa-solid fa-circle-check"></i> Completed (Undo)
                            </button>
                        <?php else: ?>
                            <input type="hidden" name="completed" value="1">
                            <input type="hidden" name="action" value="toggle_complete">
                            <button type="submit" class="btn btn-primary btn-sm">
                                <i class="fa-regular fa-circle"></i> Mark as Completed
                            </button>
                        <?php endif; ?>
                    </form>
                </div>
                
                <div class="flex align-center" style="gap: 16px; color: var(--text-muted); font-size: 14px; border-top: 1px solid var(--border-color); padding-top: 16px;">
                    <span><i class="fa-solid fa-graduation-cap" style="color: var(--secondary); margin-right: 6px;"></i>Course: <strong><?php echo htmlspecialchars($course['title']); ?></strong></span>
                    <span><i class="fa-regular fa-clock" style="color: var(--secondary); margin-right: 6px;"></i>Duration: <strong><?php echo htmlspecialchars($activeLesson['duration']); ?> mins</strong></span>
                </div>
                
                <div style="margin-top: 24px; color: var(--text-muted); font-size: 14px; line-height: 1.7; background: var(--bg-card); padding: 20px; border-radius: var(--radius-sm); border: 1px solid var(--border-color);">
                    <h4 style="margin-bottom: 8px; color: #fff;">Lesson Notes</h4>
                    In this lesson, we cover essential paradigms and practical foundations. Watch the complete video lesson and practice with the code details provided in your local sandbox environment. Ensure you check "Mark as Completed" to track progress on your student dashboard.
                </div>
            </div>
        </div>

        <!-- Right: Playlist Outline & Current Progress -->
        <div>
            <div class="playlist-card">
                <h3>Syllabus Progress</h3>
                
                <!-- Overall progress details -->
                <div style="margin-bottom: 24px;">
                    <div class="progress-bar-label">
                        <span>Overall Course Completion</span>
                        <strong><?php echo htmlspecialchars($enrollment['progress']); ?>%</strong>
                    </div>
                    <div class="progress-track" style="height: 10px;">
                        <div class="progress-fill" style="width: <?php echo htmlspecialchars($enrollment['progress']); ?>%;"></div>
                    </div>
                </div>

                <h3>Lessons</h3>
                <ul class="playlist-items">
                    <?php foreach ($lessons as $index => $lesson): 
                        $isCompleted = in_array($lesson['id'], $completedLessonIds);
                        $isActive = ($lesson['id'] === $activeLessonId);
                    ?>
                        <li class="playlist-item <?php echo $isActive ? 'active' : ''; ?> <?php echo $isCompleted ? 'completed' : ''; ?>">
                            <a href="course-player.php?course_id=<?php echo $courseId; ?>&lesson_id=<?php echo $lesson['id']; ?>">
                                <div class="playlist-item-left">
                                    <span>
                                        <?php if ($isCompleted): ?>
                                            <i class="fa-solid fa-circle-check"></i>
                                        <?php else: ?>
                                            <i class="fa-regular fa-circle-play"></i>
                                        <?php endif; ?>
                                    </span>
                                    <div>
                                        <div style="font-weight: 500; font-size: 13px;"><?php echo ($index + 1) . '. ' . htmlspecialchars($lesson['title']); ?></div>
                                        <div style="font-size: 11px; color: var(--text-muted);"><?php echo htmlspecialchars($lesson['duration']); ?> mins</div>
                                    </div>
                                </div>
                            </a>
                        </li>
                    <?php endforeach; ?>
                </ul>
            </div>
        </div>
    </div>
</div>

<?php require_once 'includes/footer.php'; ?>
