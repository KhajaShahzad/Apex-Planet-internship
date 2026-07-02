<?php
// index.php
$pageTitle = "Modern E-Learning Portal";
$activePage = "home";

// Include DB to trigger auto-setup on first load
require_once 'includes/db.php';
require_once 'includes/auth.php';

// Fetch 3 featured courses from database
try {
    $stmt = $pdo->query("SELECT * FROM courses LIMIT 3");
    $featuredCourses = $stmt->fetchAll();
} catch (PDOException $e) {
    $featuredCourses = [];
}

require_once 'includes/header.php';
?>

<!-- Hero Section -->
<section class="hero">
    <div class="container hero-grid">
        <div class="hero-content">
            <h1>Master Skills with <span class="gradient-brand">Interactive Video</span> Courses</h1>
            <p>EduStream is a professional-grade learning platform offering courses in web development, data science, and UI/UX design. Track your progress in real-time, verify your credentials securely with OTP, and study at your own pace.</p>
            <div class="hero-buttons">
                <a href="courses.php" class="btn btn-primary"><i class="fa-solid fa-compass"></i> Explore Courses</a>
                <?php if (!isLoggedIn()): ?>
                    <a href="register.php" class="btn btn-secondary">Get Started</a>
                <?php else: ?>
                    <a href="dashboard.php" class="btn btn-secondary">My Dashboard</a>
                <?php endif; ?>
            </div>
        </div>
        <div class="hero-image-wrapper">
            <div class="hero-gradient-orb"></div>
            <div class="hero-card">
                <div class="hero-card-badge">HOT DEAL</div>
                <h3 style="margin-bottom: 12px; font-size: 24px;">Full Stack Web Development</h3>
                <p style="color: var(--text-muted); font-size: 14px; margin-bottom: 20px;">Get certified in HTML, CSS, JavaScript, PHP, and MySQL database management.</p>
                <div class="flex justify-between align-center" style="margin-top: 16px;">
                    <span style="font-family: var(--font-outfit); font-size: 26px; font-weight: 800; color: #fff;">$99.99</span>
                    <a href="courses.php" class="btn btn-primary btn-sm">Enroll Now <i class="fa-solid fa-arrow-right"></i></a>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Stats Bar Section -->
<section class="stats">
    <div class="container grid stats-grid">
        <div class="stat-item">
            <div class="stat-number">15,000+</div>
            <div class="stat-label">Active Learners</div>
        </div>
        <div class="stat-item">
            <div class="stat-number">12+</div>
            <div class="stat-label">Expert Mentors</div>
        </div>
        <div class="stat-item">
            <div class="stat-number">98%</div>
            <div class="stat-label">Satisfaction Rate</div>
        </div>
        <div class="stat-item">
            <div class="stat-number">24/7</div>
            <div class="stat-label">Student Support</div>
        </div>
    </div>
</section>

<!-- Features / Advantages Section -->
<section class="features">
    <div class="container">
        <div class="section-header">
            <h2>Why Choose EduStream?</h2>
            <p>Our platform is engineered from the ground up to offer the most intuitive, feature-rich, and premium online education experience possible.</p>
        </div>
        
        <div class="grid features-grid">
            <div class="feature-card">
                <div class="feature-icon"><i class="fa-solid fa-shield-halved"></i></div>
                <h3>Secure OTP Auth</h3>
                <p>Register safely with 6-digit email verification. Protect your credentials with state-of-the-art password hashing systems.</p>
            </div>
            
            <div class="feature-card">
                <div class="feature-icon"><i class="fa-solid fa-magnifying-glass"></i></div>
                <h3>Real-time AJAX Search</h3>
                <p>Find courses instantly. Filter by category, difficulty, or price without annoying page refreshes, using high-speed AJAX fetch endpoints.</p>
            </div>
            
            <div class="feature-card">
                <div class="feature-icon"><i class="fa-solid fa-chart-pie"></i></div>
                <h3>Analytics Dashboard</h3>
                <p>Instructors receive full Chart.js dashboard analytics, tracking total enrollments, monthly sales, and course popularity metrics instantly.</p>
            </div>
        </div>
    </div>
</section>

<!-- Featured Courses Section -->
<section style="padding: 60px 0; background: rgba(17,24,39,0.2);">
    <div class="container">
        <div class="section-header">
            <h2>Explore Featured Courses</h2>
            <p>Accelerate your career goals by enrolling in our highly-rated, professional-grade bootcamps today.</p>
        </div>
        
        <div class="grid course-grid">
            <?php if (!empty($featuredCourses)): ?>
                <?php foreach ($featuredCourses as $course): ?>
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
                            <p><?php echo htmlspecialchars(substr($course['description'], 0, 110)) . '...'; ?></p>
                            <div class="course-footer">
                                <div class="course-price">$<?php echo htmlspecialchars($course['price']); ?></div>
                                <a href="course-details.php?id=<?php echo $course['id']; ?>" class="btn btn-primary btn-sm">View Details</a>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <p style="text-align: center; grid-column: span 3; color: var(--text-muted);">No courses found in database.</p>
            <?php endif; ?>
        </div>
        
        <div style="text-align: center; margin-top: 48px;">
            <a href="courses.php" class="btn btn-secondary">View All Courses <i class="fa-solid fa-arrow-right" style="margin-left: 6px;"></i></a>
        </div>
    </div>
</section>

<?php require_once 'includes/footer.php'; ?>
