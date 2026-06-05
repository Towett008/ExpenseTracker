<?php
// ============================================================
// includes/config.php
// Database connection settings – edit these to match your setup
// ============================================================

define('DB_HOST', 'localhost');
define('DB_USER', 'root');       // your MySQL username (XAMPP default: root)
define('DB_PASS', 'Cynthia3457!');           // your MySQL password (XAMPP default: empty)
define('DB_NAME', 'expenses_tracker');

// Connect to the database
$conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);

// Stop everything if the connection fails
if ($conn->connect_error) {
    die("Database connection failed: " . $conn->connect_error);
}

// Tell MySQL to use UTF-8 so special characters work properly
$conn->set_charset("utf8mb4");
?>
