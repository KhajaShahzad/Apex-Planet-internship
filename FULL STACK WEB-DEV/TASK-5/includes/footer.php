<?php
// includes/footer.php
?>
</main>

<footer>
    <div class="container footer-grid">
        <div class="footer-info">
            <a href="index.php" class="logo">
                <i class="fa-solid fa-graduation-cap gradient-brand"></i>
                <span>EduStream</span>
            </a>
            <p>Empowering learners worldwide with professional-grade video courses, interactive progress trackers, and real-time assessments.</p>
            <div class="flex" style="gap: 16px; margin-top: 20px; font-size: 18px; color: var(--text-muted);">
                <a href="#" style="color: var(--text-muted);"><i class="fa-brands fa-github"></i></a>
                <a href="#" style="color: var(--text-muted);"><i class="fa-brands fa-linkedin"></i></a>
                <a href="#" style="color: var(--text-muted);"><i class="fa-brands fa-twitter"></i></a>
            </div>
        </div>
        
        <div class="footer-links">
            <h4>Quick Links</h4>
            <ul>
                <li><a href="index.php">Home</a></li>
                <li><a href="courses.php">Explore Courses</a></li>
                <li><a href="jobs.php">Jobs Board</a></li>
                <li><a href="login.php">Log In</a></li>
                <li><a href="register.php">Sign Up</a></li>
            </ul>
        </div>
        
        <div class="footer-links">
            <h4>Explore Catalog</h4>
            <ul>
                <li><a href="courses.php?category=Web+Development">Web Development</a></li>
                <li><a href="courses.php?category=Data+Science">Data Science</a></li>
                <li><a href="courses.php?category=UI%2FUX+Design">UI/UX Design</a></li>
                <li><a href="courses.php?category=Mobile+Dev">Mobile Dev</a></li>
            </ul>
        </div>
    </div>
    
    <div class="container footer-bottom">
        <p>&copy; <?php echo date('Y'); ?> EduStream Inc. Developed as an Apex Planet Capstone Project. All Rights Reserved.</p>
    </div>
</footer>

</body>
</html>
