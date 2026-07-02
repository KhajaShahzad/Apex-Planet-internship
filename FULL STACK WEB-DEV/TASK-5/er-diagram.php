<?php
// er-diagram.php - Database ER Diagram & Wireframe Design Documentation
$pageTitle = "ER Diagram & Design";
$activePage = "erdiagram";
$pageDescription = "EduStream database Entity-Relationship diagram showing all tables, relationships, foreign keys, and wireframe page blueprints.";
require_once 'includes/auth.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="<?php echo htmlspecialchars($pageDescription); ?>">
    <title><?php echo $pageTitle; ?> | EduStream</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="css/style.css">
    <!-- Mermaid.js for rendering ER diagrams -->
    <script src="https://cdn.jsdelivr.net/npm/mermaid@10/dist/mermaid.min.js"></script>
    <script>
        mermaid.initialize({
            startOnLoad: true,
            theme: 'dark',
            themeVariables: {
                primaryColor: '#6366f1',
                primaryTextColor: '#f3f4f6',
                primaryBorderColor: '#374151',
                lineColor: '#0ea5e9',
                secondaryColor: '#111827',
                tertiaryColor: '#1f2937'
            }
        });
    </script>
</head>
<body>
<?php require_once 'includes/header.php'; ?>

<section style="padding: 50px 0 30px 0; background: linear-gradient(180deg, rgba(17,24,39,0.6) 0%, var(--bg-dark) 100%); border-bottom: 1px solid var(--border-color);">
    <div class="container">
        <h1 style="font-size: 36px;"><i class="fa-solid fa-diagram-project" style="color: var(--primary); margin-right: 12px;"></i>Database ER Diagram</h1>
        <p style="color: var(--text-muted); margin-top: 8px; max-width: 720px;">Complete Entity-Relationship diagram for the EduStream platform database, showing all tables, column data types, primary/foreign key constraints, and table relationships.</p>

        <!-- Page Navigation Tabs -->
        <div style="display: flex; gap: 8px; margin-top: 28px;">
            <button onclick="showSection('erSection')" class="btn btn-primary btn-sm" id="tabER"><i class="fa-solid fa-database"></i> ER Diagram</button>
            <button onclick="showSection('wireframeSection')" class="btn btn-secondary btn-sm" id="tabWF"><i class="fa-solid fa-pencil-ruler"></i> Wireframes</button>
            <button onclick="showSection('schemaSection')" class="btn btn-secondary btn-sm" id="tabSC"><i class="fa-solid fa-code"></i> DB Schema</button>
        </div>
    </div>
</section>

<div class="container" style="padding: 40px 24px 80px 24px;">

    <!-- ================== ER DIAGRAM SECTION ================== -->
    <div id="erSection">
        <div class="admin-panel-card" style="padding: 32px;">
            <h2 style="margin-bottom: 6px;">Entity Relationship Diagram</h2>
            <p style="color: var(--text-muted); font-size: 14px; margin-bottom: 24px;">Six core tables with foreign key constraints ensuring data integrity across the EduStream platform.</p>
            
            <div class="mermaid" style="background: #0d1117; border-radius: 12px; padding: 24px; overflow-x: auto;">
erDiagram
    USERS {
        int id PK
        varchar name
        varchar email
        varchar password
        enum role
        tinyint is_verified
        timestamp created_at
    }
    
    OTPS {
        int id PK
        varchar email
        varchar otp
        datetime expires_at
        timestamp created_at
    }
    
    COURSES {
        int id PK
        varchar title
        text description
        varchar category
        varchar difficulty
        varchar image_url
        decimal price
        timestamp created_at
    }
    
    LESSONS {
        int id PK
        int course_id FK
        varchar title
        varchar video_url
        int duration
        int sort_order
        timestamp created_at
    }
    
    ENROLLMENTS {
        int id PK
        int user_id FK
        int course_id FK
        timestamp enrolled_at
        int progress
        datetime completed_at
    }
    
    LESSON_PROGRESS {
        int id PK
        int user_id FK
        int lesson_id FK
        timestamp completed_at
    }
    
    JOBS {
        int id PK
        varchar title
        varchar company
        varchar location
        enum type
        varchar category
        text description
        text requirements
        int salary_min
        int salary_max
        tinyint is_active
        timestamp created_at
    }
    
    JOB_APPLICATIONS {
        int id PK
        int job_id FK
        int user_id FK
        text cover_letter
        varchar portfolio_url
        enum status
        timestamp applied_at
    }
    
    USERS ||--o{ ENROLLMENTS : "enrolls in"
    USERS ||--o{ LESSON_PROGRESS : "marks complete"
    USERS ||--o{ JOB_APPLICATIONS : "applies to"
    COURSES ||--o{ LESSONS : "contains"
    COURSES ||--o{ ENROLLMENTS : "enrolled by"
    LESSONS ||--o{ LESSON_PROGRESS : "tracked via"
    JOBS ||--o{ JOB_APPLICATIONS : "receives"
            </div>
        </div>
        
        <!-- Table Relationship Summary -->
        <div class="grid" style="grid-template-columns: repeat(3, 1fr); gap: 20px; margin-top: 28px;">
            <?php
            $tables = [
                ['users', 'fa-users', 'var(--secondary)', 'Core authentication table storing all registered students and instructor accounts with hashed passwords.'],
                ['courses', 'fa-book-open', 'var(--primary)', 'Catalogue of all available learning modules with pricing, category, and difficulty metadata.'],
                ['lessons', 'fa-play-circle', 'var(--success)', 'Individual lesson videos nested under each course, with ordering and MP4 stream URLs.'],
                ['enrollments', 'fa-graduation-cap', 'var(--warning)', 'Many-to-many junction table linking students to enrolled courses with live progress tracking.'],
                ['lesson_progress', 'fa-check-circle', 'var(--primary)', 'Granular lesson-level completion tracker used to compute overall course completion percentages.'],
                ['jobs', 'fa-briefcase', 'var(--secondary)', 'Job board listing table with company details, role type, salary bands, and requirements.'],
                ['job_applications', 'fa-file-text', 'var(--success)', 'Student job application records linking users and jobs with cover letters and status tracking.'],
                ['otps', 'fa-shield-halved', 'var(--danger)', 'Temporary OTP records with expiry timestamps for email-based account verification flow.'],
            ];
            foreach ($tables as $t): ?>
                <div style="background: var(--bg-card); border: 1px solid var(--border-color); border-radius: var(--radius-md); padding: 20px;">
                    <div style="display: flex; align-items: center; gap: 12px; margin-bottom: 10px;">
                        <div style="width: 36px; height: 36px; border-radius: 8px; background: rgba(99,102,241,0.1); display: flex; align-items: center; justify-content: center; color: <?php echo $t[2]; ?>; font-size: 16px;">
                            <i class="fa-solid <?php echo $t[1]; ?>"></i>
                        </div>
                        <code style="font-size: 15px; color: #fff; font-weight: 700;"><?php echo $t[0]; ?></code>
                    </div>
                    <p style="font-size: 13px; color: var(--text-muted); line-height: 1.5;"><?php echo $t[3]; ?></p>
                </div>
            <?php endforeach; ?>
        </div>
    </div>

    <!-- ================== WIREFRAMES SECTION ================== -->
    <div id="wireframeSection" style="display: none;">
        <h2 style="margin-bottom: 24px; font-size: 28px;">Page Wireframes</h2>
        <p style="color: var(--text-muted); margin-bottom: 36px;">Functional blueprint layout maps for the 5 primary pages of the EduStream application.</p>

        <div style="display: flex; flex-direction: column; gap: 40px;">
            <?php
            $wireframes = [
                [
                    'page' => 'Home (index.php)',
                    'icon' => 'fa-house',
                    'color' => 'var(--primary)',
                    'sections' => [
                        'NAVBAR — Logo | Home | Courses | Jobs | Login | Sign Up',
                        'HERO — Headline + Sub-text | CTA Buttons | Floating Feature Card',
                        'STATS BAR — 4 Key Metrics (Students, Mentors, Rating, Support)',
                        'FEATURES GRID — 3 columns: OTP Auth | AJAX Search | Analytics',
                        'COURSES SECTION — 3 Featured Course Cards with Enroll CTAs',
                        'FOOTER — Brand | Quick Links | Catalog Links | Copyright',
                    ]
                ],
                [
                    'page' => 'Login / Register (login.php, register.php)',
                    'icon' => 'fa-right-to-bracket',
                    'color' => 'var(--secondary)',
                    'sections' => [
                        'NAVBAR — Minimal with Home and Course links',
                        'GLASSMORPHIC CARD — Centered max-width 480px card',
                        'FORM HEADER — Title + Subtitle context text',
                        'FORM FIELDS — Name / Email / Password / Role select',
                        'CTA BUTTON — Full-width gradient submit button',
                        'AUTH SWITCH — Link to Login or Register alternate page',
                        'DEBUG INFO — Sandbox test account credentials hint block',
                    ]
                ],
                [
                    'page' => 'OTP Verification (verify-otp.php)',
                    'icon' => 'fa-shield-halved',
                    'color' => 'var(--success)',
                    'sections' => [
                        'GLASSMORPHIC CARD — Centered 480px verification card',
                        'SANDBOX ALERT — (Local dev only) Shows generated OTP code',
                        'OTP GRID — 6 single-digit auto-tabbing input boxes',
                        'VERIFY BUTTON — Full-width submit & sign in',
                        'RESEND LINK — Triggers new OTP record generation',
                    ]
                ],
                [
                    'page' => 'Dashboard (dashboard.php, admin.php)',
                    'icon' => 'fa-chart-line',
                    'color' => 'var(--warning)',
                    'sections' => [
                        'NAVBAR — Full navigation with user name badge and Logout',
                        'WELCOME HEADER — User name greeting + Browse Courses CTA',
                        'STATS ROW — 3 cards: Enrolled | Completed | Lessons Done',
                        'STUDENT: ENROLLED LIST — Course rows with progress bars and Resume buttons',
                        'ADMIN: METRICS ROW — Students | Courses | Enrollments count cards',
                        'ADMIN: CHARTS — Bar (Enrollments/Course) + Line (Registrations) + Pie (Categories)',
                        'ADMIN: TAB PANEL — Student Log | Add Course Form | Add Lesson Form',
                    ]
                ],
                [
                    'page' => 'Jobs Board (jobs.php)',
                    'icon' => 'fa-briefcase',
                    'color' => 'var(--primary)',
                    'sections' => [
                        'HERO SECTION — Page title and description',
                        'FILTER BAR — Text search | Category select | Type select | Submit',
                        'JOBS LIST — Vertical stack of job detail cards',
                        'JOB CARD — Title + Badges + Company + Location + Salary + Requirements',
                        'APPLY MODAL — Slide-up modal with cover letter textarea + portfolio URL',
                        'APPLICATION STATUS — Applied badge shown if already submitted',
                    ]
                ],
            ];
            foreach ($wireframes as $wf): ?>
                <div style="background: var(--bg-card); border: 1px solid var(--border-color); border-radius: var(--radius-md); overflow: hidden;">
                    <div style="padding: 20px 24px; border-bottom: 1px solid var(--border-color); display: flex; align-items: center; gap: 12px; background: rgba(255,255,255,0.02);">
                        <div style="width: 36px; height: 36px; border-radius: 8px; background: rgba(99,102,241,0.1); display: flex; align-items: center; justify-content: center; color: <?php echo $wf['color']; ?>; font-size: 16px;">
                            <i class="fa-solid <?php echo $wf['icon']; ?>"></i>
                        </div>
                        <h3 style="font-size: 18px; font-family: var(--font-outfit);"><?php echo $wf['page']; ?></h3>
                    </div>
                    <div style="padding: 24px; display: grid; grid-template-columns: repeat(2, 1fr); gap: 10px;">
                        <?php foreach ($wf['sections'] as $idx => $section): ?>
                            <div style="display: flex; align-items: flex-start; gap: 10px; padding: 12px; border-radius: 6px; background: rgba(255,255,255,0.02); border: 1px solid var(--border-color);">
                                <div style="width: 22px; height: 22px; border-radius: 50%; background: <?php echo $wf['color']; ?>; display: flex; align-items: center; justify-content: center; font-size: 10px; font-weight: 700; color: #fff; flex-shrink: 0; margin-top: 1px;"><?php echo $idx+1; ?></div>
                                <span style="font-size: 13px; color: var(--text-muted); line-height: 1.4;"><?php echo $section; ?></span>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>

    <!-- ================== DB SCHEMA SQL SECTION ================== -->
    <div id="schemaSection" style="display: none;">
        <h2 style="margin-bottom: 24px;">Database SQL Schema</h2>
        <div class="admin-panel-card" style="padding: 32px;">
            <pre style="background: #0d1117; padding: 24px; border-radius: 10px; overflow-x: auto; font-size: 13px; line-height: 1.6; color: #e6edf3; border: 1px solid var(--border-color);"><code>-- EduStream Database Schema
-- Engine: MariaDB 10.4 / MySQL 8+
-- Charset: utf8mb4

CREATE DATABASE IF NOT EXISTS `capstone_project`;
USE `capstone_project`;

CREATE TABLE `users` (
  `id`           INT AUTO_INCREMENT PRIMARY KEY,
  `name`         VARCHAR(100) NOT NULL,
  `email`        VARCHAR(100) NOT NULL UNIQUE,
  `password`     VARCHAR(255) NOT NULL,
  `role`         ENUM('student','admin') DEFAULT 'student',
  `is_verified`  TINYINT(1) DEFAULT 0,
  `created_at`   TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

CREATE TABLE `otps` (
  `id`         INT AUTO_INCREMENT PRIMARY KEY,
  `email`      VARCHAR(100) NOT NULL,
  `otp`        VARCHAR(6) NOT NULL,
  `expires_at` DATETIME NOT NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

CREATE TABLE `courses` (
  `id`          INT AUTO_INCREMENT PRIMARY KEY,
  `title`       VARCHAR(255) NOT NULL,
  `description` TEXT NOT NULL,
  `category`    VARCHAR(100) NOT NULL,
  `difficulty`  VARCHAR(50) NOT NULL,
  `image_url`   VARCHAR(255) NOT NULL,
  `price`       DECIMAL(10,2) NOT NULL,
  `created_at`  TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

CREATE TABLE `lessons` (
  `id`         INT AUTO_INCREMENT PRIMARY KEY,
  `course_id`  INT NOT NULL,
  `title`      VARCHAR(255) NOT NULL,
  `video_url`  VARCHAR(255) NOT NULL,
  `duration`   INT NOT NULL,
  `sort_order` INT NOT NULL DEFAULT 0,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (`course_id`) REFERENCES `courses`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE `enrollments` (
  `id`          INT AUTO_INCREMENT PRIMARY KEY,
  `user_id`     INT NOT NULL,
  `course_id`   INT NOT NULL,
  `enrolled_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `progress`    INT DEFAULT 0,
  `completed_at` DATETIME DEFAULT NULL,
  UNIQUE KEY `user_course` (`user_id`,`course_id`),
  FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE,
  FOREIGN KEY (`course_id`) REFERENCES `courses`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE `lesson_progress` (
  `id`           INT AUTO_INCREMENT PRIMARY KEY,
  `user_id`      INT NOT NULL,
  `lesson_id`    INT NOT NULL,
  `completed_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  UNIQUE KEY `user_lesson` (`user_id`,`lesson_id`),
  FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE,
  FOREIGN KEY (`lesson_id`) REFERENCES `lessons`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE `jobs` (
  `id`           INT AUTO_INCREMENT PRIMARY KEY,
  `title`        VARCHAR(255) NOT NULL,
  `company`      VARCHAR(150) NOT NULL,
  `location`     VARCHAR(150) NOT NULL,
  `type`         ENUM('Full-time','Part-time','Remote','Internship','Freelance') DEFAULT 'Full-time',
  `category`     VARCHAR(100) NOT NULL,
  `description`  TEXT NOT NULL,
  `requirements` TEXT NOT NULL,
  `salary_min`   INT DEFAULT 0,
  `salary_max`   INT DEFAULT 0,
  `is_active`    TINYINT(1) DEFAULT 1,
  `created_at`   TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

CREATE TABLE `job_applications` (
  `id`            INT AUTO_INCREMENT PRIMARY KEY,
  `job_id`        INT NOT NULL,
  `user_id`       INT NOT NULL,
  `cover_letter`  TEXT NOT NULL,
  `portfolio_url` VARCHAR(255) DEFAULT '',
  `status`        ENUM('pending','reviewed','shortlisted','rejected') DEFAULT 'pending',
  `applied_at`    TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  UNIQUE KEY `user_job` (`user_id`,`job_id`),
  FOREIGN KEY (`job_id`) REFERENCES `jobs`(`id`) ON DELETE CASCADE,
  FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB;</code></pre>
        </div>
    </div>
</div>

<script>
function showSection(id) {
    ['erSection','wireframeSection','schemaSection'].forEach(s => {
        document.getElementById(s).style.display = s === id ? 'block' : 'none';
    });
}
</script>

<?php require_once 'includes/footer.php'; ?>
