<?php
// admin.php
$pageTitle = "Instructor Admin Portal";
$activePage = "admin";

require_once 'includes/db.php';
require_once 'includes/auth.php';

// Enforce admin check
requireAdmin();

$successMsg = '';
$errorMsg = '';

// 1. Handle New Course Creation
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['form_action']) && $_POST['form_action'] === 'add_course') {
    $title = trim($_POST['title'] ?? '');
    $category = trim($_POST['category'] ?? '');
    $difficulty = $_POST['difficulty'] ?? 'Beginner';
    $description = trim($_POST['description'] ?? '');
    $price = floatval($_POST['price'] ?? 0);
    $imageUrl = trim($_POST['image_url'] ?? 'course_default.jpg');

    if (empty($title) || empty($category) || empty($description) || $price <= 0) {
        $errorMsg = "Please fill in all course details and ensure price is greater than 0.";
    } else {
        try {
            $stmt = $pdo->prepare("INSERT INTO courses (title, description, category, difficulty, image_url, price) VALUES (?, ?, ?, ?, ?, ?)");
            $stmt->execute([$title, $description, $category, $difficulty, $imageUrl, $price]);
            $successMsg = "Course '{$title}' created successfully!";
        } catch (PDOException $e) {
            $errorMsg = "Failed to add course: " . $e->getMessage();
        }
    }
}

// 2. Handle New Lesson Creation
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['form_action']) && $_POST['form_action'] === 'add_lesson') {
    $courseId = intval($_POST['course_id'] ?? 0);
    $lessonTitle = trim($_POST['lesson_title'] ?? '');
    $videoUrl = trim($_POST['video_url'] ?? '');
    $duration = intval($_POST['duration'] ?? 0);
    $sortOrder = intval($_POST['sort_order'] ?? 0);

    if ($courseId <= 0 || empty($lessonTitle) || empty($videoUrl) || $duration <= 0) {
        $errorMsg = "Please fill in all lesson details and ensure duration is positive.";
    } else {
        try {
            $stmt = $pdo->prepare("INSERT INTO lessons (course_id, title, video_url, duration, sort_order) VALUES (?, ?, ?, ?, ?)");
            $stmt->execute([$courseId, $lessonTitle, $videoUrl, $duration, $sortOrder]);
            $successMsg = "Lesson '{$lessonTitle}' successfully added!";
        } catch (PDOException $e) {
            $errorMsg = "Failed to add lesson: " . $e->getMessage();
        }
    }
}

// 3. Query Database Statistics for Metric Cards
try {
    // Total student users count
    $stmtUsers = $pdo->query("SELECT COUNT(*) FROM users WHERE role = 'student'");
    $totalStudents = $stmtUsers->fetchColumn();

    // Total courses count
    $stmtCoursesCount = $pdo->query("SELECT COUNT(*) FROM courses");
    $totalCourses = $stmtCoursesCount->fetchColumn();

    // Total enrollment records count
    $stmtEnroll = $pdo->query("SELECT COUNT(*) FROM enrollments");
    $totalEnrollments = $stmtEnroll->fetchColumn();

    // Fetch all courses for drop-downs
    $stmtDropdown = $pdo->query("SELECT id, title FROM courses ORDER BY title ASC");
    $coursesList = $stmtDropdown->fetchAll();

    // 4. Fetch Chart Analytics Data
    // Chart 1: Student enrollments per Course
    $chartCoursesQuery = $pdo->query("
        SELECT c.title, COUNT(e.id) as student_count 
        FROM courses c 
        LEFT JOIN enrollments e ON c.id = e.course_id 
        GROUP BY c.id
    ");
    $chartCoursesData = $chartCoursesQuery->fetchAll();
    
    $courseLabels = [];
    $courseEnrollments = [];
    foreach ($chartCoursesData as $data) {
        $courseLabels[] = strlen($data['title']) > 22 ? substr($data['title'], 0, 20) . '...' : $data['title'];
        $courseEnrollments[] = intval($data['student_count']);
    }

    // Chart 2: Daily Registration Trend (Last 7 active days)
    $chartRegQuery = $pdo->query("
        SELECT DATE(created_at) as reg_date, COUNT(*) as reg_count 
        FROM users 
        GROUP BY DATE(created_at) 
        ORDER BY reg_date ASC 
        LIMIT 7
    ");
    $chartRegData = $chartRegQuery->fetchAll();
    
    $regLabels = [];
    $regCounts = [];
    foreach ($chartRegData as $data) {
        $regLabels[] = date('M d', strtotime($data['reg_date']));
        $regCounts[] = intval($data['reg_count']);
    }

    // Chart 3: Course Categories Distribution
    $chartCatQuery = $pdo->query("
        SELECT category, COUNT(*) as course_count 
        FROM courses 
        GROUP BY category
    ");
    $chartCatData = $chartCatQuery->fetchAll();
    
    $categoryLabels = [];
    $categoryCounts = [];
    foreach ($chartCatData as $data) {
        $categoryLabels[] = $data['category'];
        $categoryCounts[] = intval($data['course_count']);
    }

    // 5. Fetch Enrollment log table records
    $stmtTable = $pdo->query("
        SELECT u.name as student_name, u.email as student_email, c.title as course_title, e.progress, e.enrolled_at 
        FROM enrollments e 
        JOIN users u ON e.user_id = u.id 
        JOIN courses c ON e.course_id = c.id 
        ORDER BY e.enrolled_at DESC 
        LIMIT 10
    ");
    $enrollmentLog = $stmtTable->fetchAll();

    // 6. Job Application Stats
    $totalJobApps = 0;
    $jobAppLog = [];
    try {
        $stmtJA = $pdo->query("SELECT COUNT(*) FROM job_applications");
        $totalJobApps = $stmtJA->fetchColumn();

        $stmtJobLog = $pdo->query("
            SELECT ja.id, u.name as student_name, u.email as student_email, j.title as job_title, j.company, ja.status, ja.applied_at
            FROM job_applications ja
            JOIN users u ON ja.user_id = u.id
            JOIN jobs j ON ja.job_id = j.id
            ORDER BY ja.applied_at DESC
            LIMIT 20
        ");
        $jobAppLog = $stmtJobLog->fetchAll();
    } catch (PDOException $e) { /* jobs table may not exist yet */ }

} catch (PDOException $e) {
    die("Database Analytics Queries Failed: " . $e->getMessage());
}

require_once 'includes/header.php';
?>

<!-- Chart.js CDN -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<div class="container" style="padding-top: 40px;">
    <div class="flex justify-between align-center" style="margin-bottom: 24px; flex-wrap: wrap; gap: 16px;">
        <div>
            <h1 style="font-size: 32px;"><i class="fa-solid fa-chart-line" style="color: var(--primary); margin-right: 12px;"></i>Instructor Admin Dashboard</h1>
            <p style="color: var(--text-muted); margin-top: 4px;">Monitor platform performance, review charts, manage syllabi, and track student enrollments.</p>
        </div>
    </div>

    <!-- Feedback banners -->
    <?php if (!empty($successMsg)): ?>
        <div class="alert alert-success">
            <i class="fa-regular fa-circle-check"></i>
            <div><?php echo htmlspecialchars($successMsg); ?></div>
        </div>
    <?php endif; ?>

    <?php if (!empty($errorMsg)): ?>
        <div class="alert alert-danger">
            <i class="fa-solid fa-circle-exclamation"></i>
            <div><?php echo htmlspecialchars($errorMsg); ?></div>
        </div>
    <?php endif; ?>

    <!-- Metrics Cards Row -->
    <div class="grid dashboard-grid">
        <div class="dashboard-stat-card">
            <div class="stat-card-icon" style="color: var(--secondary); background: rgba(14,165,233,0.1);"><i class="fa-solid fa-users"></i></div>
            <div class="stat-card-info">
                <h4>Total Students</h4>
                <p><?php echo $totalStudents; ?></p>
            </div>
        </div>

        <div class="dashboard-stat-card">
            <div class="stat-card-icon" style="color: var(--primary); background: rgba(99,102,241,0.1);"><i class="fa-solid fa-book-bookmark"></i></div>
            <div class="stat-card-info">
                <h4>Active Courses</h4>
                <p><?php echo $totalCourses; ?></p>
            </div>
        </div>

        <div class="dashboard-stat-card">
            <div class="stat-card-icon" style="color: var(--success); background: rgba(16,185,129,0.1);"><i class="fa-solid fa-graduation-cap"></i></div>
            <div class="stat-card-info">
                <h4>Total Enrollments</h4>
                <p><?php echo $totalEnrollments; ?></p>
            </div>
        </div>

        <div class="dashboard-stat-card">
            <div class="stat-card-icon" style="color: var(--warning); background: rgba(245,158,11,0.1);"><i class="fa-solid fa-briefcase"></i></div>
            <div class="stat-card-info">
                <h4>Job Applications</h4>
                <p><?php echo $totalJobApps; ?></p>
            </div>
        </div>
    </div>

    <!-- Analytics Dashboard Charts (Chart.js) -->
    <div class="charts-row">
        <!-- Chart 1: Enrollments per Course Bar Chart -->
        <div class="chart-container">
            <h3>Enrollments per Course</h3>
            <div style="height: 280px; position: relative;">
                <canvas id="courseBarChart"></canvas>
            </div>
        </div>

        <!-- Chart 3: Category Distribution Pie Chart -->
        <div class="chart-container">
            <h3>Categories Distribution</h3>
            <div style="height: 280px; position: relative; display: flex; justify-content: center; align-items: center;">
                <canvas id="categoryPieChart" style="max-height: 240px;"></canvas>
            </div>
        </div>
    </div>

    <div class="chart-container" style="margin-bottom: 40px;">
        <h3>Registrations History</h3>
        <div style="height: 260px; position: relative;">
            <canvas id="registrationsLineChart"></canvas>
        </div>
    </div>

    <!-- Admin Operational Management Tabs -->
    <div class="admin-tab-bar">
        <div class="admin-tab active" onclick="switchAdminTab('studentsTab', this)">Student Enrollments</div>
        <div class="admin-tab" onclick="switchAdminTab('jobAppsTab', this)">Job Applications</div>
        <div class="admin-tab" onclick="switchAdminTab('courseFormTab', this)">Add New Course</div>
        <div class="admin-tab" onclick="switchAdminTab('lessonFormTab', this)">Add Lesson</div>
        <a href="er-diagram.php" class="admin-tab" style="text-decoration: none; display: flex; align-items: center; gap: 6px;" target="_blank"><i class="fa-solid fa-diagram-project"></i> ER Diagram</a>
    </div>

    <!-- Tab 1: Enrollments Table -->
    <div id="studentsTab" class="admin-panel-card" style="display: block;">
        <h3>Active Enrollment Log</h3>
        
        <?php if (!empty($enrollmentLog)): ?>
            <div class="admin-table-container">
                <table class="admin-table">
                    <thead>
                        <tr>
                            <th>Student Name</th>
                            <th>Student Email</th>
                            <th>Enrolled Course</th>
                            <th>Progress</th>
                            <th>Enrollment Date</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($enrollmentLog as $log): ?>
                            <tr>
                                <td><strong><?php echo htmlspecialchars($log['student_name']); ?></strong></td>
                                <td><?php echo htmlspecialchars($log['student_email']); ?></td>
                                <td><?php echo htmlspecialchars($log['course_title']); ?></td>
                                <td>
                                    <div class="flex align-center" style="gap: 8px;">
                                        <div class="progress-track" style="width: 100px; height: 6px;">
                                            <div class="progress-fill" style="width: <?php echo htmlspecialchars($log['progress']); ?>%;"></div>
                                        </div>
                                        <span><?php echo htmlspecialchars($log['progress']); ?>%</span>
                                    </div>
                                </td>
                                <td><?php echo date('M d, Y H:i', strtotime($log['enrolled_at'])); ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php else: ?>
            <p style="color: var(--text-muted);">No student enrollments registered yet.</p>
        <?php endif; ?>
    </div>

    <!-- Tab 2: Job Applications Panel -->
    <div id="jobAppsTab" class="admin-panel-card" style="display: none;">
        <h3>Job Applications Log</h3>
        <?php if (!empty($jobAppLog)): ?>
            <div class="admin-table-container">
                <table class="admin-table">
                    <thead>
                        <tr>
                            <th>Student</th>
                            <th>Email</th>
                            <th>Job Applied</th>
                            <th>Company</th>
                            <th>Status</th>
                            <th>Date</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($jobAppLog as $jl):
                            $statusMap = [
                                'pending'     => ['color' => 'var(--warning)', 'bg' => 'rgba(245,158,11,0.1)'],
                                'reviewed'    => ['color' => 'var(--secondary)', 'bg' => 'rgba(14,165,233,0.1)'],
                                'shortlisted' => ['color' => 'var(--success)', 'bg' => 'rgba(16,185,129,0.1)'],
                                'rejected'    => ['color' => 'var(--danger)', 'bg' => 'rgba(239,68,68,0.1)'],
                            ];
                            $sc = $statusMap[$jl['status']] ?? $statusMap['pending'];
                        ?>
                            <tr>
                                <td><strong><?php echo htmlspecialchars($jl['student_name']); ?></strong></td>
                                <td><?php echo htmlspecialchars($jl['student_email']); ?></td>
                                <td><?php echo htmlspecialchars($jl['job_title']); ?></td>
                                <td><?php echo htmlspecialchars($jl['company']); ?></td>
                                <td>
                                    <span style="padding: 3px 10px; border-radius: 20px; font-size: 11px; font-weight: 700; text-transform: uppercase; background: <?php echo $sc['bg']; ?>; color: <?php echo $sc['color']; ?>;">
                                        <?php echo ucfirst($jl['status']); ?>
                                    </span>
                                </td>
                                <td><?php echo date('M d, Y', strtotime($jl['applied_at'])); ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php else: ?>
            <p style="color: var(--text-muted);">No job applications have been submitted yet. <a href="jobs.php" style="color: var(--secondary);">Browse Jobs Board</a></p>
        <?php endif; ?>
    </div>

    <!-- Tab 3: Create Course Form -->
    <div id="courseFormTab" class="admin-panel-card" style="display: none;">
        <h3>Add a New Professional Course</h3>
        <form action="admin.php" method="POST">
            <input type="hidden" name="form_action" value="add_course">
            
            <div class="grid" style="grid-template-columns: 1fr 1fr; gap: 20px; margin-bottom: 20px;">
                <div class="form-group">
                    <label for="title">Course Title</label>
                    <input type="text" name="title" id="title" class="form-control" placeholder="e.g. Master React in 30 Days" required>
                </div>
                
                <div class="form-group">
                    <label for="category">Category</label>
                    <input type="text" name="category" id="category" class="form-control" placeholder="e.g. Web Development" required>
                </div>
            </div>

            <div class="grid" style="grid-template-columns: 1fr 1fr; gap: 20px; margin-bottom: 20px;">
                <div class="form-group">
                    <label for="difficulty">Difficulty Level</label>
                    <select name="difficulty" id="difficulty" class="form-control">
                        <option value="Beginner">Beginner</option>
                        <option value="Intermediate">Intermediate</option>
                        <option value="Advanced">Advanced</option>
                    </select>
                </div>
                
                <div class="form-group">
                    <label for="price">Price ($ USD)</label>
                    <input type="number" step="0.01" name="price" id="price" class="form-control" placeholder="e.g. 49.99" required>
                </div>
            </div>

            <div class="form-group">
                <label for="image_url">Image Filename/URL Placeholder</label>
                <input type="text" name="image_url" id="image_url" class="form-control" value="course_default.jpg">
            </div>

            <div class="form-group">
                <label for="description">Course Description</label>
                <textarea name="description" id="description" rows="4" class="form-control" placeholder="Provide a rich curriculum description outlining features, prerequisites, and learning outcomes..." required></textarea>
            </div>

            <button type="submit" class="btn btn-primary" style="margin-top: 16px;">
                <i class="fa-solid fa-plus"></i> Publish Course
            </button>
        </form>
    </div>

    <!-- Tab 3: Create Lesson Form -->
    <div id="lessonFormTab" class="admin-panel-card" style="display: none;">
        <h3>Upload a Lesson Video to a Course</h3>
        <form action="admin.php" method="POST">
            <input type="hidden" name="form_action" value="add_lesson">
            
            <div class="form-group">
                <label for="course_id">Select Course</label>
                <select name="course_id" id="course_id" class="form-control" required>
                    <option value="">-- Choose Course --</option>
                    <?php foreach ($coursesList as $c): ?>
                        <option value="<?php echo $c['id']; ?>"><?php echo htmlspecialchars($c['title']); ?></option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="form-group" style="margin-top: 20px;">
                <label for="lesson_title">Lesson Title</label>
                <input type="text" name="lesson_title" id="lesson_title" class="form-control" placeholder="e.g. Setting up the Development Environment" required>
            </div>

            <div class="form-group" style="margin-top: 20px;">
                <label for="video_url">Video Stream URL (MP4 file or YouTube Link)</label>
                <input type="url" name="video_url" id="video_url" class="form-control" value="https://www.w3schools.com/html/mov_bbb.mp4" placeholder="e.g. https://www.youtube.com/watch?v=dQw4w9WgXcQ or direct MP4 URL" required>
            </div>

            <div class="grid" style="grid-template-columns: 1fr 1fr; gap: 20px; margin-top: 20px;">
                <div class="form-group">
                    <label for="duration">Duration (Minutes)</label>
                    <input type="number" name="duration" id="duration" class="form-control" placeholder="e.g. 15" required>
                </div>
                
                <div class="form-group">
                    <label for="sort_order">Syllabus Index (Sort Order)</label>
                    <input type="number" name="sort_order" id="sort_order" class="form-control" value="1" placeholder="e.g. 1, 2, 3..." required>
                </div>
            </div>

            <button type="submit" class="btn btn-primary" style="margin-top: 24px;">
                <i class="fa-solid fa-circle-plus"></i> Add Lesson Video
            </button>
        </form>
    </div>
</div>

<script>
    // Tab switching controller
    function switchAdminTab(tabId, el) {
        // Hide all panels
        ['studentsTab','jobAppsTab','courseFormTab','lessonFormTab'].forEach(id => {
            const el = document.getElementById(id);
            if (el) el.style.display = 'none';
        });

        // Remove active class from all tabs
        const tabs = document.querySelectorAll('.admin-tab');
        tabs.forEach(t => t.classList.remove('active'));

        // Display targeted tab panel and active class
        document.getElementById(tabId).style.display = 'block';
        el.classList.add('active');
    }

    // Chart.js initialization logic
    document.addEventListener('DOMContentLoaded', () => {
        // Theme Colors
        const primaryColor = '#6366f1';
        const secondaryColor = '#0ea5e9';
        const borderColor = '#374151';
        const textColor = '#9ca3af';

        Chart.defaults.color = textColor;
        Chart.defaults.borderColor = borderColor;

        // Chart 1: Course Enrollments Bar Chart
        const barCtx = document.getElementById('courseBarChart').getContext('2d');
        new Chart(barCtx, {
            type: 'bar',
            data: {
                labels: <?php echo json_encode($courseLabels); ?>,
                datasets: [{
                    label: 'Students Enrolled',
                    data: <?php echo json_encode($courseEnrollments); ?>,
                    backgroundColor: 'rgba(99, 102, 241, 0.75)',
                    borderColor: primaryColor,
                    borderWidth: 1,
                    borderRadius: 4
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { display: false }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: { stepSize: 1 }
                    }
                }
            }
        });

        // Chart 2: Registrations Line Chart
        const lineCtx = document.getElementById('registrationsLineChart').getContext('2d');
        new Chart(lineCtx, {
            type: 'line',
            data: {
                labels: <?php echo json_encode($regLabels); ?>,
                datasets: [{
                    label: 'Daily Registrations',
                    data: <?php echo json_encode($regCounts); ?>,
                    borderColor: secondaryColor,
                    backgroundColor: 'rgba(14, 165, 233, 0.15)',
                    fill: true,
                    tension: 0.3,
                    borderWidth: 2,
                    pointBackgroundColor: secondaryColor
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: { stepSize: 1 }
                    }
                }
            }
        });

        // Chart 3: Category Distribution Pie Chart
        const pieCtx = document.getElementById('categoryPieChart').getContext('2d');
        new Chart(pieCtx, {
            type: 'pie',
            data: {
                labels: <?php echo json_encode($categoryLabels); ?>,
                datasets: [{
                    data: <?php echo json_encode($categoryCounts); ?>,
                    backgroundColor: [
                        'rgba(99, 102, 241, 0.8)',
                        'rgba(14, 165, 233, 0.8)',
                        'rgba(16, 185, 129, 0.8)',
                        'rgba(245, 158, 11, 0.8)'
                    ],
                    borderColor: '#111827',
                    borderWidth: 2
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'right',
                        labels: { boxWidth: 12 }
                    }
                }
            }
        });
    });
</script>

<?php require_once 'includes/footer.php'; ?>
