<?php
// dashboard.php
$pageTitle = "Student Dashboard";
$activePage = "dashboard";

require_once 'includes/db.php';
require_once 'includes/auth.php';

// Enforce login
requireLogin();

$userId = $_SESSION['user_id'];

try {
    // 1. Fetch Student general metrics
    // Enrolled courses count
    $stmtCount = $pdo->prepare("SELECT COUNT(*) FROM enrollments WHERE user_id = ?");
    $stmtCount->execute([$userId]);
    $enrolledCount = $stmtCount->fetchColumn();

    // Completed courses count
    $stmtCompCount = $pdo->prepare("SELECT COUNT(*) FROM enrollments WHERE user_id = ? AND progress = 100");
    $stmtCompCount->execute([$userId]);
    $completedCount = $stmtCompCount->fetchColumn();

    // Total lessons completed count
    $stmtLessonsComp = $pdo->prepare("SELECT COUNT(*) FROM lesson_progress WHERE user_id = ?");
    $stmtLessonsComp->execute([$userId]);
    $lessonsCompletedCount = $stmtLessonsComp->fetchColumn();

    // 2. Fetch Enrolled Courses list with details
    $stmtList = $pdo->prepare("
        SELECT c.*, e.progress, e.enrolled_at 
        FROM enrollments e 
        JOIN courses c ON e.course_id = c.id 
        WHERE e.user_id = ? 
        ORDER BY e.enrolled_at DESC
    ");
    $stmtList->execute([$userId]);
    $enrollments = $stmtList->fetchAll();

    // Fetch job application count (only if table exists)
    $myJobsCount = 0;
    try {
        $stmtJobApps = $pdo->prepare("SELECT COUNT(*) FROM job_applications WHERE user_id = ?");
        $stmtJobApps->execute([$userId]);
        $myJobsCount = $stmtJobApps->fetchColumn();
        
        // Fetch job applications details
        $stmtMyJobs = $pdo->prepare("
            SELECT ja.*, j.title as job_title, j.company, j.type, ja.status 
            FROM job_applications ja 
            JOIN jobs j ON ja.job_id = j.id 
            WHERE ja.user_id = ? 
            ORDER BY ja.applied_at DESC
        ");
        $stmtMyJobs->execute([$userId]);
        $myJobApplications = $stmtMyJobs->fetchAll();
    } catch (PDOException $e) {
        $myJobApplications = [];
    }

} catch (PDOException $e) {
    die("Error loading student dashboard: " . $e->getMessage());
}

require_once 'includes/header.php';
?>

<div class="container dashboard-title-section">
    <div class="flex justify-between align-center" style="flex-wrap: wrap; gap: 16px; margin-bottom: 24px;">
        <div>
            <h1 style="font-size: 32px;">Welcome back, <?php echo htmlspecialchars($_SESSION['user_name']); ?>!</h1>
            <p style="color: var(--text-muted); margin-top: 4px;">Track your course curriculum and resume learning right where you left off.</p>
        </div>
        <a href="courses.php" class="btn btn-primary"><i class="fa-solid fa-graduation-cap"></i> Browse More Courses</a>
    </div>

    <!-- Stats summary bar -->
    <div class="grid dashboard-grid">
        <div class="dashboard-stat-card">
            <div class="stat-card-icon"><i class="fa-solid fa-book-open"></i></div>
            <div class="stat-card-info">
                <h4>Enrolled Courses</h4>
                <p><?php echo $enrolledCount; ?></p>
            </div>
        </div>

        <div class="dashboard-stat-card">
            <div class="stat-card-icon"><i class="fa-solid fa-trophy"></i></div>
            <div class="stat-card-info">
                <h4>Completed Bootcamps</h4>
                <p><?php echo $completedCount; ?></p>
            </div>
        </div>

        <div class="dashboard-stat-card">
            <div class="stat-card-icon"><i class="fa-solid fa-circle-play"></i></div>
            <div class="stat-card-info">
                <h4>Completed Lessons</h4>
                <p><?php echo $lessonsCompletedCount; ?></p>
            </div>
        </div>

        <div class="dashboard-stat-card" style="grid-column: span 1;">
            <div class="stat-card-icon" style="background: rgba(245,158,11,0.1); color: var(--warning);"><i class="fa-solid fa-briefcase"></i></div>
            <div class="stat-card-info">
                <h4>Job Applications</h4>
                <p><?php echo $myJobsCount; ?></p>
            </div>
        </div>
    </div>

    <!-- Active courses list -->
    <div class="enrolled-courses-section">
        <h3>Your Learning Path</h3>
        
        <?php if (!empty($enrollments)): ?>
            <div class="enrolled-grid">
                <?php foreach ($enrollments as $enroll): ?>
                    <div class="enrolled-item">
                        <div class="flex align-center" style="gap: 16px;">
                            <div style="width: 56px; height: 56px; border-radius: var(--radius-sm); background: linear-gradient(135deg, var(--primary) 0%, var(--secondary) 100%); display: flex; align-items: center; justify-content: center; font-size: 20px; color: #fff;">
                                <i class="fa-solid fa-laptop-code"></i>
                            </div>
                            <div class="enrolled-info">
                                <h4><?php echo htmlspecialchars($enroll['title']); ?></h4>
                                <p><span style="color: var(--secondary); font-weight: 500;"><?php echo htmlspecialchars($enroll['category']); ?></span> &bull; Enrolled on <?php echo date('M d, Y', strtotime($enroll['enrolled_at'])); ?></p>
                            </div>
                        </div>

                        <!-- Progress Bar module -->
                        <div class="progress-bar-container">
                            <div class="progress-bar-label">
                                <span>Course Completion</span>
                                <strong><?php echo htmlspecialchars($enroll['progress']); ?>%</strong>
                            </div>
                            <div class="progress-track">
                                <div class="progress-fill" style="width: <?php echo htmlspecialchars($enroll['progress']); ?>%;"></div>
                            </div>
                        </div>

                        <!-- Action Button -->
                        <div>
                            <a href="course-player.php?course_id=<?php echo $enroll['id']; ?>" class="btn btn-secondary btn-sm" style="white-space: nowrap;">
                                <?php if ($enroll['progress'] == 100): ?>
                                    <i class="fa-solid fa-rotate-left"></i> Review Lessons
                                <?php else: ?>
                                    <i class="fa-solid fa-play"></i> Resume Lesson
                                <?php endif; ?>
                            </a>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php else: ?>
            <div style="background: var(--bg-card); border: 1px solid var(--border-color); border-radius: var(--radius-md); padding: 48px; text-align: center;">
                <i class="fa-solid fa-folder-open" style="font-size: 48px; color: var(--text-muted); margin-bottom: 16px;"></i>
                <h4 style="margin-bottom: 8px;">You haven't enrolled in any courses yet</h4>
                <p style="color: var(--text-muted); font-size: 14px; margin-bottom: 24px;">Explore our catalog, select a roadmap, and take the first step towards mastering your skills.</p>
                <a href="courses.php" class="btn btn-primary">Find a Course</a>
            </div>
        <?php endif; ?>
    </div>

    <!-- My Job Applications Section -->
    <div style="margin-top: 48px;">
        <div class="flex justify-between align-center" style="margin-bottom: 20px;">
            <h3 style="font-size: 22px;"><i class="fa-solid fa-briefcase" style="color: var(--warning); margin-right: 10px;"></i>My Job Applications</h3>
            <a href="jobs.php" class="btn btn-secondary btn-sm"><i class="fa-solid fa-plus"></i> Browse Jobs</a>
        </div>

        <?php if (!empty($myJobApplications)): ?>
            <div style="display: flex; flex-direction: column; gap: 14px;">
                <?php
                $statusColors = [
                    'pending'     => ['bg' => 'rgba(245,158,11,0.1)', 'color' => 'var(--warning)', 'border' => 'rgba(245,158,11,0.3)'],
                    'reviewed'    => ['bg' => 'rgba(14,165,233,0.1)', 'color' => 'var(--secondary)', 'border' => 'rgba(14,165,233,0.3)'],
                    'shortlisted' => ['bg' => 'rgba(16,185,129,0.1)', 'color' => 'var(--success)', 'border' => 'rgba(16,185,129,0.3)'],
                    'rejected'    => ['bg' => 'rgba(239,68,68,0.1)', 'color' => 'var(--danger)', 'border' => 'rgba(239,68,68,0.3)'],
                ];
                foreach ($myJobApplications as $app):
                    $sc = $statusColors[$app['status']] ?? $statusColors['pending'];
                ?>
                    <div style="background: var(--bg-card); border: 1px solid var(--border-color); border-radius: var(--radius-md); padding: 20px 24px; display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 12px;">
                        <div>
                            <div style="font-size: 16px; font-weight: 600; margin-bottom: 4px;"><?php echo htmlspecialchars($app['job_title']); ?></div>
                            <div style="font-size: 13px; color: var(--text-muted);">
                                <i class="fa-solid fa-building" style="margin-right: 5px;"></i><?php echo htmlspecialchars($app['company']); ?>
                                &nbsp;&bull;&nbsp;
                                <i class="fa-solid fa-tag" style="margin-right: 5px;"></i><?php echo htmlspecialchars($app['type']); ?>
                                &nbsp;&bull;&nbsp;
                                <i class="fa-regular fa-clock" style="margin-right: 5px;"></i>Applied <?php echo date('M d, Y', strtotime($app['applied_at'])); ?>
                            </div>
                        </div>
                        <div style="padding: 6px 14px; border-radius: 20px; font-size: 12px; font-weight: 700; text-transform: uppercase; letter-spacing: 0.5px; background: <?php echo $sc['bg']; ?>; color: <?php echo $sc['color']; ?>; border: 1px solid <?php echo $sc['border']; ?>;">
                            <?php echo ucfirst($app['status']); ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php else: ?>
            <div style="background: var(--bg-card); border: 1px solid var(--border-color); border-radius: var(--radius-md); padding: 36px; text-align: center;">
                <i class="fa-solid fa-briefcase" style="font-size: 40px; color: var(--text-muted); margin-bottom: 14px; display: block;"></i>
                <h4 style="margin-bottom: 8px; font-size: 17px;">No Job Applications Yet</h4>
                <p style="color: var(--text-muted); font-size: 14px; margin-bottom: 20px;">Browse our Jobs Board and apply to tech roles that match your skill set.</p>
                <a href="jobs.php" class="btn btn-primary btn-sm">Explore Jobs Board</a>
            </div>
        <?php endif; ?>
    </div>
</div>

<?php require_once 'includes/footer.php'; ?>
