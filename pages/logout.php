<?php
// ============================================================
// pages/logout.php – Destroys the session and redirects
// ============================================================
require_once '../includes/auth.php';

// Destroy all session data
session_destroy();

// Send back to the login page
header("Location: ../index.php");
exit;
?>
