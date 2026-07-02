<?php
// jobs.php - Jobs Board & Applications Module
$pageTitle = "Jobs Board";
$activePage = "jobs";
$pageDescription = "Browse tech job listings and submit applications directly through EduStream's integrated Job Board portal.";

require_once 'includes/db.php';
require_once 'includes/auth.php';

// Create Jobs and Applications tables if they don't exist (auto-setup)
try {
    $pdo->exec("CREATE TABLE IF NOT EXISTS `jobs` (
        `id` INT AUTO_INCREMENT PRIMARY KEY,
        `title` VARCHAR(255) NOT NULL,
        `company` VARCHAR(150) NOT NULL,
        `location` VARCHAR(150) NOT NULL,
        `type` ENUM('Full-time','Part-time','Remote','Internship','Freelance') NOT NULL DEFAULT 'Full-time',
        `category` VARCHAR(100) NOT NULL,
        `description` TEXT NOT NULL,
        `requirements` TEXT NOT NULL,
        `salary_min` INT DEFAULT 0,
        `salary_max` INT DEFAULT 0,
        `is_active` TINYINT(1) DEFAULT 1,
        `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    ) ENGINE=InnoDB;");

    $pdo->exec("CREATE TABLE IF NOT EXISTS `job_applications` (
        `id` INT AUTO_INCREMENT PRIMARY KEY,
        `job_id` INT NOT NULL,
        `user_id` INT NOT NULL,
        `cover_letter` TEXT NOT NULL,
        `portfolio_url` VARCHAR(255) DEFAULT '',
        `status` ENUM('pending','reviewed','shortlisted','rejected') DEFAULT 'pending',
        `applied_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        UNIQUE KEY `user_job` (`user_id`, `job_id`),
        FOREIGN KEY (`job_id`) REFERENCES `jobs`(`id`) ON DELETE CASCADE,
        FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE
    ) ENGINE=InnoDB;");

    // Seed demo jobs if table is empty
    $stmtJobCount = $pdo->query("SELECT COUNT(*) FROM jobs");
    if ($stmtJobCount->fetchColumn() == 0) {
        $pdo->exec("INSERT INTO `jobs` (`title`, `company`, `location`, `type`, `category`, `description`, `requirements`, `salary_min`, `salary_max`) VALUES
        ('Full Stack PHP Developer', 'TechNova Solutions', 'Hyderabad, India', 'Full-time', 'Web Development', 'We are looking for a passionate Full Stack PHP Developer to join our growing engineering team. You will be responsible for building scalable web applications, REST APIs, and integrating third-party services.', 'Proficient in PHP, MySQL, JavaScript, HTML/CSS. Familiarity with Laravel or CodeIgniter is a plus.', 60000, 120000),
        ('React.js Frontend Engineer', 'CloudSpark Digital', 'Bangalore, India', 'Full-time', 'Web Development', 'Join our remote-first product team as a React.js Frontend Engineer. You will own entire UI features, work closely with designers, and help define our frontend architecture.', 'Strong experience with React.js, Redux, and TypeScript. Knowledge of RESTful API consumption and CSS-in-JS.', 70000, 140000),
        ('Data Analyst Intern', 'Insightful Analytics Co.', 'Remote', 'Internship', 'Data Science', 'Join us for a 3-month data analyst internship where you will clean, visualize, and analyze large business datasets. You will present weekly findings to stakeholders.', 'Working knowledge of Python, Pandas, and Matplotlib. Exposure to SQL databases and Power BI is beneficial.', 10000, 20000),
        ('UI/UX Designer', 'PixelForge Agency', 'Pune, India', 'Full-time', 'UI/UX Design', 'We are hiring a talented UI/UX Designer to create stunning, user-centric digital experiences. You will conduct user research, create wireframes, and deliver high-fidelity Figma prototypes.', 'Portfolio required. Proficiency in Figma, Adobe XD, or Sketch. Knowledge of design systems and accessibility standards.', 50000, 100000),
        ('Flutter Mobile Developer', 'AppWorld Technologies', 'Mumbai, India', 'Remote', 'Mobile Dev', 'We are seeking a skilled Flutter developer to build beautiful and performant cross-platform mobile apps. You will work end-to-end: from architecture and development to App Store and Play Store deployment.', 'Strong Dart/Flutter skills. Experience with state management (Provider/Bloc). Published app portfolio preferred.', 80000, 150000),
        ('Junior Backend Developer', 'StartGrid Startup Hub', 'Chennai, India', 'Full-time', 'Web Development', 'Exciting opportunity to join a fast-growing B2B startup as a Junior Backend Developer. Work with Node.js, Express, and PostgreSQL to build APIs that power our SaaS platform.', 'Proficiency in Node.js and SQL databases. Exposure to REST API design. Team player with attention to detail.', 40000, 80000)
        ");
    }

    // Fetch jobs with optional search/filter
    $search = trim($_GET['search'] ?? '');
    $category = trim($_GET['category'] ?? '');
    $type = trim($_GET['type'] ?? '');
    
    $query = "SELECT * FROM jobs WHERE is_active = 1";
    $params = [];
    
    if ($search !== '') {
        $query .= " AND (title LIKE ? OR company LIKE ? OR description LIKE ?)";
        $wc = '%' . $search . '%';
        $params[] = $wc; $params[] = $wc; $params[] = $wc;
    }
    if ($category !== '') {
        $query .= " AND category = ?";
        $params[] = $category;
    }
    if ($type !== '') {
        $query .= " AND type = ?";
        $params[] = $type;
    }
    $query .= " ORDER BY id DESC";
    
    $stmtJobs = $pdo->prepare($query);
    $stmtJobs->execute($params);
    $jobs = $stmtJobs->fetchAll();

    // Fetch user's existing applications
    $userApplicationIds = [];
    if (isLoggedIn()) {
        $stmtMyApps = $pdo->prepare("SELECT job_id FROM job_applications WHERE user_id = ?");
        $stmtMyApps->execute([$_SESSION['user_id']]);
        $userApplicationIds = $stmtMyApps->fetchAll(PDO::FETCH_COLUMN);
    }

} catch (PDOException $e) {
    die("Jobs board setup failed: " . $e->getMessage());
}

// Handle Job Application Submission
$applySuccess = '';
$applyError = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['form_action']) && $_POST['form_action'] === 'apply') {
    requireLogin();
    $jobId = intval($_POST['job_id'] ?? 0);
    $coverLetter = trim($_POST['cover_letter'] ?? '');
    $portfolioUrl = trim($_POST['portfolio_url'] ?? '');

    if ($jobId <= 0 || strlen($coverLetter) < 30) {
        $applyError = "Please write a cover letter with at least 30 characters.";
    } elseif (in_array($jobId, $userApplicationIds)) {
        $applyError = "You have already applied for this position.";
    } else {
        try {
            $stmtApply = $pdo->prepare("INSERT INTO job_applications (job_id, user_id, cover_letter, portfolio_url) VALUES (?, ?, ?, ?)");
            $stmtApply->execute([$jobId, $_SESSION['user_id'], $coverLetter, $portfolioUrl]);
            $userApplicationIds[] = $jobId;
            $applySuccess = "Your application has been submitted successfully! We'll be in touch.";
        } catch (PDOException $e) {
            $applyError = "Application failed: " . $e->getMessage();
        }
    }
}

require_once 'includes/header.php';
?>

<!-- Jobs Page Hero -->
<section class="details-header" style="padding: 50px 0 30px 0;">
    <div class="container">
        <h1 style="font-size: 36px;"><i class="fa-solid fa-briefcase" style="color: var(--primary); margin-right: 12px;"></i>Tech Job Board</h1>
        <p style="color: var(--text-muted); margin-top: 8px; max-width: 620px;">Discover top-tier opportunities at leading tech companies. Apply directly with your EduStream profile and let your skills speak for themselves.</p>
    </div>
</section>

<section style="padding: 40px 0 80px 0;">
    <div class="container">

        <!-- Feedback Alerts -->
        <?php if ($applySuccess): ?>
            <div class="alert alert-success" style="margin-bottom: 24px;">
                <i class="fa-regular fa-circle-check"></i>
                <div><?php echo htmlspecialchars($applySuccess); ?></div>
            </div>
        <?php endif; ?>
        <?php if ($applyError): ?>
            <div class="alert alert-danger" style="margin-bottom: 24px;">
                <i class="fa-solid fa-circle-exclamation"></i>
                <div><?php echo htmlspecialchars($applyError); ?></div>
            </div>
        <?php endif; ?>

        <!-- Search & Filter Bar -->
        <div class="search-filter-bar" style="margin-bottom: 36px;">
            <form method="GET" action="jobs.php" style="display: contents;">
                <div class="search-input-group">
                    <i class="fa-solid fa-magnifying-glass search-icon-fixed"></i>
                    <input type="text" name="search" placeholder="Search jobs, companies, skills..." value="<?php echo htmlspecialchars($search); ?>">
                </div>
                <div class="filter-controls">
                    <select name="category" class="filter-select" onchange="this.form.submit()">
                        <option value="">All Categories</option>
                        <option value="Web Development" <?php echo $category === 'Web Development' ? 'selected' : ''; ?>>Web Development</option>
                        <option value="Data Science" <?php echo $category === 'Data Science' ? 'selected' : ''; ?>>Data Science</option>
                        <option value="UI/UX Design" <?php echo $category === 'UI/UX Design' ? 'selected' : ''; ?>>UI/UX Design</option>
                        <option value="Mobile Dev" <?php echo $category === 'Mobile Dev' ? 'selected' : ''; ?>>Mobile Dev</option>
                    </select>
                    <select name="type" class="filter-select" onchange="this.form.submit()">
                        <option value="">All Types</option>
                        <option value="Full-time" <?php echo $type === 'Full-time' ? 'selected' : ''; ?>>Full-time</option>
                        <option value="Remote" <?php echo $type === 'Remote' ? 'selected' : ''; ?>>Remote</option>
                        <option value="Internship" <?php echo $type === 'Internship' ? 'selected' : ''; ?>>Internship</option>
                        <option value="Part-time" <?php echo $type === 'Part-time' ? 'selected' : ''; ?>>Part-time</option>
                        <option value="Freelance" <?php echo $type === 'Freelance' ? 'selected' : ''; ?>>Freelance</option>
                    </select>
                    <button type="submit" class="btn btn-primary btn-sm"><i class="fa-solid fa-search"></i> Search</button>
                    <?php if ($search || $category || $type): ?>
                        <a href="jobs.php" class="btn btn-secondary btn-sm"><i class="fa-solid fa-xmark"></i> Clear</a>
                    <?php endif; ?>
                </div>
            </form>
        </div>

        <!-- Job Listings -->
        <?php if (!empty($jobs)): ?>
            <div style="display: flex; flex-direction: column; gap: 20px;" id="jobsList">
                <?php foreach ($jobs as $job): 
                    $alreadyApplied = in_array($job['id'], $userApplicationIds);
                ?>
                <div class="admin-panel-card" style="padding: 28px; margin-bottom: 0; border-radius: var(--radius-md);">
                    <div style="display: flex; justify-content: space-between; align-items: flex-start; flex-wrap: wrap; gap: 16px;">
                        <!-- Job Info -->
                        <div style="flex: 1; min-width: 280px;">
                            <div style="display: flex; align-items: center; gap: 12px; margin-bottom: 10px; flex-wrap: wrap;">
                                <h3 style="font-size: 20px; margin: 0;"><?php echo htmlspecialchars($job['title']); ?></h3>
                                <span style="padding: 3px 10px; border-radius: 20px; font-size: 11px; font-weight: 700; background: rgba(99,102,241,0.15); color: var(--primary); border: 1px solid rgba(99,102,241,0.3);"><?php echo htmlspecialchars($job['type']); ?></span>
                                <span style="padding: 3px 10px; border-radius: 20px; font-size: 11px; font-weight: 600; background: rgba(14,165,233,0.1); color: var(--secondary); border: 1px solid rgba(14,165,233,0.2);"><?php echo htmlspecialchars($job['category']); ?></span>
                            </div>
                            <div style="display: flex; gap: 20px; color: var(--text-muted); font-size: 13px; flex-wrap: wrap; margin-bottom: 14px;">
                                <span><i class="fa-solid fa-building" style="margin-right: 5px; color: var(--secondary);"></i><?php echo htmlspecialchars($job['company']); ?></span>
                                <span><i class="fa-solid fa-location-dot" style="margin-right: 5px; color: var(--secondary);"></i><?php echo htmlspecialchars($job['location']); ?></span>
                                <?php if ($job['salary_min'] > 0): ?>
                                <span><i class="fa-solid fa-indian-rupee-sign" style="margin-right: 5px; color: var(--success);"></i>
                                    <?php echo number_format($job['salary_min']/12000, 0) . 'L – ' . number_format($job['salary_max']/12000, 0) . 'L / yr'; ?>
                                </span>
                                <?php endif; ?>
                            </div>
                            <p style="color: var(--text-muted); font-size: 14px; line-height: 1.6;"><?php echo htmlspecialchars(substr($job['description'], 0, 200)) . '...'; ?></p>
                        </div>

                        <!-- Apply CTA -->
                        <div style="display: flex; flex-direction: column; gap: 10px; min-width: 140px; text-align: center;">
                            <?php if ($alreadyApplied): ?>
                                <div style="padding: 10px 16px; border-radius: var(--radius-sm); background: rgba(16,185,129,0.1); color: var(--success); font-size: 13px; font-weight: 600; border: 1px solid rgba(16,185,129,0.3);">
                                    <i class="fa-solid fa-circle-check"></i> Applied
                                </div>
                            <?php else: ?>
                                <button class="btn btn-primary btn-sm" onclick="openApplyModal(<?php echo $job['id']; ?>, '<?php echo addslashes(htmlspecialchars($job['title'])); ?>', '<?php echo addslashes(htmlspecialchars($job['company'])); ?>')">
                                    <i class="fa-solid fa-paper-plane"></i> Apply Now
                                </button>
                                <?php if (!isLoggedIn()): ?>
                                    <span style="font-size: 11px; color: var(--text-muted); margin-top: 4px;">Login required</span>
                                <?php endif; ?>
                            <?php endif; ?>
                            <span style="font-size: 11px; color: var(--text-muted);">
                                <i class="fa-regular fa-clock" style="margin-right: 4px;"></i><?php echo date('M d, Y', strtotime($job['created_at'])); ?>
                            </span>
                        </div>
                    </div>

                    <!-- Requirements pill row -->
                    <div style="margin-top: 16px; padding-top: 16px; border-top: 1px solid var(--border-color);">
                        <span style="font-size: 12px; color: var(--text-muted); font-weight: 600; text-transform: uppercase; letter-spacing: 0.5px; margin-right: 8px;">Requirements:</span>
                        <span style="font-size: 13px; color: var(--text-main);"><?php echo htmlspecialchars($job['requirements']); ?></span>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        <?php else: ?>
            <div style="text-align: center; padding: 80px; background: var(--bg-card); border-radius: var(--radius-md); border: 1px solid var(--border-color);">
                <i class="fa-solid fa-briefcase" style="font-size: 48px; color: var(--text-muted); margin-bottom: 16px; display: block;"></i>
                <h3 style="margin-bottom: 8px;">No Jobs Found</h3>
                <p style="color: var(--text-muted);">Try adjusting your search or filter criteria.</p>
                <a href="jobs.php" class="btn btn-primary" style="margin-top: 20px;">View All Jobs</a>
            </div>
        <?php endif; ?>
    </div>
</section>

<!-- Apply Modal -->
<div id="applyModal" style="display:none; position:fixed; top:0; left:0; width:100%; height:100%; background:rgba(0,0,0,0.7); z-index:1000; justify-content:center; align-items:center;">
    <div style="background: var(--bg-card); border: 1px solid var(--border-color); border-radius: var(--radius-lg); padding: 40px; width: 90%; max-width: 580px; max-height: 90vh; overflow-y: auto;">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 24px;">
            <h2 style="font-size: 22px;" id="modalJobTitle">Apply for Position</h2>
            <button onclick="closeApplyModal()" style="background: none; border: none; color: var(--text-muted); font-size: 22px; cursor: pointer;"><i class="fa-solid fa-xmark"></i></button>
        </div>
        <div id="modalJobCompany" style="color: var(--secondary); font-size: 14px; margin-bottom: 24px;"></div>
        
        <?php if (!isLoggedIn()): ?>
            <div class="alert alert-warning">
                <i class="fa-solid fa-triangle-exclamation"></i>
                <div>You must be <a href="login.php" style="color: var(--secondary);">logged in</a> to apply for jobs.</div>
            </div>
        <?php else: ?>
            <form action="jobs.php" method="POST">
                <input type="hidden" name="form_action" value="apply">
                <input type="hidden" name="job_id" id="modalJobId" value="">
                
                <div class="form-group">
                    <label for="cover_letter"><i class="fa-solid fa-file-lines" style="color: var(--secondary); margin-right: 5px;"></i> Cover Letter <span style="color: var(--danger);">*</span></label>
                    <textarea name="cover_letter" id="cover_letter" rows="6" class="form-control" placeholder="Tell us about yourself, your experience, and why you're a great fit for this role. Minimum 30 characters..." required style="resize: vertical;"></textarea>
                </div>
                
                <div class="form-group" style="margin-top: 16px;">
                    <label for="portfolio_url"><i class="fa-solid fa-globe" style="color: var(--secondary); margin-right: 5px;"></i> Portfolio / GitHub URL <span style="color: var(--text-muted); font-weight: 400;">(Optional)</span></label>
                    <input type="url" name="portfolio_url" id="portfolio_url" class="form-control" placeholder="https://github.com/yourusername or https://portfolio.com">
                </div>
                
                <div style="display: flex; gap: 12px; margin-top: 28px;">
                    <button type="submit" class="btn btn-primary" style="flex: 1;">
                        <i class="fa-solid fa-paper-plane"></i> Submit Application
                    </button>
                    <button type="button" onclick="closeApplyModal()" class="btn btn-secondary">Cancel</button>
                </div>
            </form>
        <?php endif; ?>
    </div>
</div>

<script>
function openApplyModal(jobId, jobTitle, company) {
    <?php if (!isLoggedIn()): ?>
        window.location.href = 'login.php';
        return;
    <?php endif; ?>
    document.getElementById('applyModal').style.display = 'flex';
    document.getElementById('modalJobId').value = jobId;
    document.getElementById('modalJobTitle').textContent = 'Apply: ' + jobTitle;
    document.getElementById('modalJobCompany').innerHTML = '<i class="fa-solid fa-building" style="margin-right: 6px;"></i>' + company;
}

function closeApplyModal() {
    document.getElementById('applyModal').style.display = 'none';
}

// Close modal on backdrop click
document.getElementById('applyModal').addEventListener('click', function(e) {
    if (e.target === this) closeApplyModal();
});
</script>

<?php require_once 'includes/footer.php'; ?>
