<?php
/**
 * config/db.php
 * Database connection — MySQLi
 * All queries use prepared statements (no raw interpolation).
 */

define('DB_HOST', 'localhost');
define('DB_USER', 'root');       // ← change to your MySQL user
define('DB_PASS', '');           // ← change to your MySQL password
define('DB_NAME', 'task3_usermgmt');
define('DB_PORT', 3306);

mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

try {
    $conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME, DB_PORT);
    $conn->set_charset('utf8mb4');
} catch (mysqli_sql_exception $e) {
    // Never expose DB errors to end users in production
    error_log('DB Connection Error: ' . $e->getMessage());
    http_response_code(500);
    die('<div style="font-family:sans-serif;padding:2rem;color:#e74c3c;">
         <h2>Service Unavailable</h2>
         <p>Could not connect to the database. Please try again later.</p>
         </div>');
}
