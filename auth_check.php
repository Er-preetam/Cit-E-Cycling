<?php
// ============================================================
// auth_check.php — Included at the top of every admin page
// Redirects to login if the admin session is not active.
// ============================================================
session_start();

if (empty($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header('Location: admin_login.html');
    exit;
}
?>
