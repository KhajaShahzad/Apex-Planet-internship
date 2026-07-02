<?php
// includes/db.php

$host = '127.0.0.1';
$user = 'root';
$pass = '';
$dbname = 'capstone_project';

try {
    // 1. Connect to MySQL server without a database context
    $pdo = new PDO("mysql:host=$host", $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // Create database if it does not exist
    $pdo->exec("CREATE DATABASE IF NOT EXISTS `$dbname` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
    
    // 2. Connect to the actual capstone project database
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);

    // Create tables if they do not exist
    
    // 1. Users Table
    $pdo->exec("CREATE TABLE IF NOT EXISTS `users` (
        `id` INT AUTO_INCREMENT PRIMARY KEY,
        `name` VARCHAR(100) NOT NULL,
        `email` VARCHAR(100) NOT NULL UNIQUE,
        `password` VARCHAR(255) NOT NULL,
        `role` ENUM('student', 'admin') DEFAULT 'student',
        `is_verified` TINYINT(1) DEFAULT 0,
        `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    ) ENGINE=InnoDB;");

    // 2. OTP Verification Table
    $pdo->exec("CREATE TABLE IF NOT EXISTS `otps` (
        `id` INT AUTO_INCREMENT PRIMARY KEY,
        `email` VARCHAR(100) NOT NULL,
        `otp` VARCHAR(6) NOT NULL,
        `expires_at` DATETIME NOT NULL,
        `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    ) ENGINE=InnoDB;");

    // 3. Courses Table
    $pdo->exec("CREATE TABLE IF NOT EXISTS `courses` (
        `id` INT AUTO_INCREMENT PRIMARY KEY,
        `title` VARCHAR(255) NOT NULL,
        `description` TEXT NOT NULL,
        `category` VARCHAR(100) NOT NULL,
        `difficulty` VARCHAR(50) NOT NULL,
        `image_url` VARCHAR(255) NOT NULL,
        `price` DECIMAL(10,2) NOT NULL,
        `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    ) ENGINE=InnoDB;");

    // 4. Lessons Table
    $pdo->exec("CREATE TABLE IF NOT EXISTS `lessons` (
        `id` INT AUTO_INCREMENT PRIMARY KEY,
        `course_id` INT NOT NULL,
        `title` VARCHAR(255) NOT NULL,
        `video_url` VARCHAR(255) NOT NULL,
        `duration` INT NOT NULL,
        `sort_order` INT NOT NULL DEFAULT 0,
        `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (`course_id`) REFERENCES `courses`(`id`) ON DELETE CASCADE
    ) ENGINE=InnoDB;");

    // 5. Enrollments Table
    $pdo->exec("CREATE TABLE IF NOT EXISTS `enrollments` (
        `id` INT AUTO_INCREMENT PRIMARY KEY,
        `user_id` INT NOT NULL,
        `course_id` INT NOT NULL,
        `enrolled_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        `progress` INT DEFAULT 0,
        `completed_at` DATETIME DEFAULT NULL,
        UNIQUE KEY `user_course` (`user_id`, `course_id`),
        FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE,
        FOREIGN KEY (`course_id`) REFERENCES `courses`(`id`) ON DELETE CASCADE
    ) ENGINE=InnoDB;");

    // 6. Lesson Progress Table (Tracks completed lessons per student)
    $pdo->exec("CREATE TABLE IF NOT EXISTS `lesson_progress` (
        `id` INT AUTO_INCREMENT PRIMARY KEY,
        `user_id` INT NOT NULL,
        `lesson_id` INT NOT NULL,
        `completed_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        UNIQUE KEY `user_lesson` (`user_id`, `lesson_id`),
        FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE,
        FOREIGN KEY (`lesson_id`) REFERENCES `lessons`(`id`) ON DELETE CASCADE
    ) ENGINE=InnoDB;");

    // Seed default admin if not exists
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM `users` WHERE `email` = ?");
    $stmt->execute(['admin@edustream.com']);
    if ($stmt->fetchColumn() == 0) {
        $adminPassword = password_hash('admin123', PASSWORD_DEFAULT);
        $stmtInsert = $pdo->prepare("INSERT INTO `users` (`name`, `email`, `password`, `role`, `is_verified`) VALUES (?, ?, ?, 'admin', 1)");
        $stmtInsert->execute(['Admin Instructor', 'admin@edustream.com', $adminPassword]);
    }

    // Seed default student if not exists
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM `users` WHERE `email` = ?");
    $stmt->execute(['student@edustream.com']);
    if ($stmt->fetchColumn() == 0) {
        $studentPassword = password_hash('student123', PASSWORD_DEFAULT);
        $stmtInsert = $pdo->prepare("INSERT INTO `users` (`name`, `email`, `password`, `role`, `is_verified`) VALUES (?, ?, ?, 'student', 1)");
        $stmtInsert->execute(['John Doe', 'student@edustream.com', $studentPassword]);
    }

    // Seed courses & lessons if empty
    $stmt = $pdo->query("SELECT COUNT(*) FROM `courses`");
    if ($stmt->fetchColumn() == 0) {
        // Insert Course data
        $pdo->exec("INSERT INTO `courses` (`title`, `description`, `category`, `difficulty`, `image_url`, `price`) VALUES 
        ('Complete Web Development Bootcamp', 'Master HTML, CSS, JavaScript, PHP, and MySQL from scratch. Build full stack web apps and deploy them live.', 'Web Development', 'Beginner', 'course_webdev.jpg', 99.99),
        ('Data Science & Machine Learning', 'Learn Numpy, Pandas, Matplotlib, Scikit-Learn, and build robust predictive models with Python.', 'Data Science', 'Intermediate', 'course_datascience.jpg', 129.99),
        ('Modern UI/UX Design Fundamentals', 'Understand core user experience design principles, wireframing, prototyping, and layout systems in Figma.', 'UI/UX Design', 'Beginner', 'course_design.jpg', 79.99),
        ('Mobile App Development with Flutter', 'Build beautiful, natively compiled multi-platform applications for iOS and Android using Flutter & Dart.', 'Mobile Dev', 'Advanced', 'course_flutter.jpg', 149.99)");

        // Get Course IDs
        $courses = $pdo->query("SELECT `id`, `title` FROM `courses`")->fetchAll();
        foreach ($courses as $c) {
            if (strpos($c['title'], 'Web Development') !== false) {
                $stmtLesson = $pdo->prepare("INSERT INTO `lessons` (`course_id`, `title`, `video_url`, `duration`, `sort_order`) VALUES (?, ?, ?, ?, ?)");
                $stmtLesson->execute([$c['id'], 'Introduction to HTML5', 'https://www.w3schools.com/html/mov_bbb.mp4', 10, 1]);
                $stmtLesson->execute([$c['id'], 'Styling with CSS3 Grid & Flexbox', 'https://www.w3schools.com/html/movie.mp4', 15, 2]);
                $stmtLesson->execute([$c['id'], 'JavaScript ES6 Basics & DOM', 'https://www.w3schools.com/html/mov_bbb.mp4', 20, 3]);
                $stmtLesson->execute([$c['id'], 'PHP Forms & Database Connections', 'https://www.w3schools.com/html/movie.mp4', 25, 4]);
                $stmtLesson->execute([$c['id'], 'Deploying Web Apps Live', 'https://www.w3schools.com/html/mov_bbb.mp4', 12, 5]);
            } else if (strpos($c['title'], 'Data Science') !== false) {
                $stmtLesson = $pdo->prepare("INSERT INTO `lessons` (`course_id`, `title`, `video_url`, `duration`, `sort_order`) VALUES (?, ?, ?, ?, ?)");
                $stmtLesson->execute([$c['id'], 'Introduction to Python & Jupyter', 'https://www.w3schools.com/html/mov_bbb.mp4', 12, 1]);
                $stmtLesson->execute([$c['id'], 'Pandas DataFrames & Manipulation', 'https://www.w3schools.com/html/movie.mp4', 18, 2]);
                $stmtLesson->execute([$c['id'], 'Data Visualization with Matplotlib', 'https://www.w3schools.com/html/mov_bbb.mp4', 15, 3]);
                $stmtLesson->execute([$c['id'], 'Machine Learning: Linear Regression', 'https://www.w3schools.com/html/movie.mp4', 30, 4]);
            } else if (strpos($c['title'], 'UI/UX Design') !== false) {
                $stmtLesson = $pdo->prepare("INSERT INTO `lessons` (`course_id`, `title`, `video_url`, `duration`, `sort_order`) VALUES (?, ?, ?, ?, ?)");
                $stmtLesson->execute([$c['id'], 'What is UX Design?', 'https://www.w3schools.com/html/mov_bbb.mp4', 8, 1]);
                $stmtLesson->execute([$c['id'], 'Color Theory & Typography', 'https://www.w3schools.com/html/movie.mp4', 14, 2]);
                $stmtLesson->execute([$c['id'], 'Creating Interactive Figma Prototypes', 'https://www.w3schools.com/html/mov_bbb.mp4', 20, 3]);
            } else if (strpos($c['title'], 'Flutter') !== false) {
                $stmtLesson = $pdo->prepare("INSERT INTO `lessons` (`course_id`, `title`, `video_url`, `duration`, `sort_order`) VALUES (?, ?, ?, ?, ?)");
                $stmtLesson->execute([$c['id'], 'Getting Started with Dart', 'https://www.w3schools.com/html/mov_bbb.mp4', 15, 1]);
                $stmtLesson->execute([$c['id'], 'Flutter Widgets & Layouts', 'https://www.w3schools.com/html/movie.mp4', 22, 2]);
                $stmtLesson->execute([$c['id'], 'State Management with Provider', 'https://www.w3schools.com/html/mov_bbb.mp4', 30, 3]);
            }
        }

        // Seed some demo enrollments and progress for the student
        $student = $pdo->query("SELECT `id` FROM `users` WHERE `email` = 'student@edustream.com'")->fetch();
        $coursesList = $pdo->query("SELECT `id` FROM `courses` LIMIT 2")->fetchAll();
        if ($student && count($coursesList) > 0) {
            $stmtEnroll = $pdo->prepare("INSERT INTO `enrollments` (`user_id`, `course_id`, `progress`) VALUES (?, ?, ?)");
            $stmtEnroll->execute([$student['id'], $coursesList[0]['id'], 40]);
            $stmtEnroll->execute([$student['id'], $coursesList[1]['id'], 0]);

            // Add completed progress on the first 2 lessons of the first course (Web Dev)
            $webdevLessons = $pdo->prepare("SELECT `id` FROM `lessons` WHERE `course_id` = ? ORDER BY `sort_order` ASC LIMIT 2");
            $webdevLessons->execute([$coursesList[0]['id']]);
            $lessonsToMark = $webdevLessons->fetchAll();
            
            $stmtProg = $pdo->prepare("INSERT INTO `lesson_progress` (`user_id`, `lesson_id`) VALUES (?, ?)");
            foreach ($lessonsToMark as $l) {
                $stmtProg->execute([$student['id'], $l['id']]);
            }
        }
    }
} catch (PDOException $e) {
    die("Database Connection or Setup Failed: " . $e->getMessage());
}
?>
