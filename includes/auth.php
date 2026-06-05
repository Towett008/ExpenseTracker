<?php
// ============================================================
// includes/auth.php
// Helper functions for login/logout/session checks
// ============================================================

// Start the session once so every page can use $_SESSION
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// -------------------------------------------------------
// isLoggedIn() – returns true if the user is logged in
// -------------------------------------------------------
function isLoggedIn() {
    return isset($_SESSION['user_id']);
}

// -------------------------------------------------------
// requireLogin() – redirect to login if not logged in
// Call this at the top of any protected page
// -------------------------------------------------------
function requireLogin() {
    if (!isLoggedIn()) {
        header("Location: ../index.php");
        exit;
    }
}

// -------------------------------------------------------
// sanitize() – clean user input to prevent XSS attacks
// Always use this before displaying user-submitted text
// -------------------------------------------------------
function sanitize($data) {
    return htmlspecialchars(trim($data), ENT_QUOTES, 'UTF-8');
}
?>
