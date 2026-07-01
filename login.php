<?php
// login.php — Admin authentication
// NOTE: The user table stores passwords as plain text (as defined in the provided cycling.sql).
// The assignment brief states the database structure must not be modified, so the comparison
// below matches the password exactly as stored in the original schema.
session_start();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Secure Admin Login | Cit-E Cycling</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="style.css">
    <style>
        body { background: linear-gradient(135deg, #0a0e27 0%, #1a1f3a 50%, #0a0e27 100%); min-height: 100vh; display:flex; flex-direction:column; }
        .login-wrap { flex:1; display:flex; align-items:center; justify-content:center; padding:2rem 1rem; }
        .login-box { background:var(--white); border-radius:20px; box-shadow:0 20px 60px rgba(0,0,0,0.4); padding:3rem 2.5rem; width:100%; max-width:420px; text-align:center; }
        .login-box .icon { font-size:3.5rem; margin-bottom:1rem; }
        .login-box .title { font-size:1.5rem; font-weight:700; margin:1rem 0 0.5rem; color:var(--primary); }
        .login-box .message { color:var(--muted); margin-bottom:1.5rem; line-height:1.6; }
    </style>
</head>
<body>
<div class="login-wrap">
<div class="login-box">
<?php
include 'dbconnect.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo '<div class="icon">&#9888;</div>';
    echo '<div class="title">Invalid Request</div>';
    echo '<div class="message">Direct access is not permitted.</div>';
    echo '<a href="admin_login.html" class="btn btn-secondary" style="margin-top:1rem;">Back to Login</a>';
} else {
    $inputUser = trim($_POST['username'] ?? '');
    $inputPass = trim($_POST['password'] ?? '');

    if (empty($inputUser) || empty($inputPass)) {
        echo '<div class="icon">&#9888;</div>';
        echo '<div class="title">Missing Credentials</div>';
        echo '<div class="message">Both username and password are required.</div>';
        echo '<a href="admin_login.html" class="btn btn-primary" style="margin-top:1rem;">Try Again</a>';
    } else {
        try {
            $conn = new PDO("mysql:host=$servername;dbname=$database", $username, $password);
            $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

            // Prepared statement — safe from SQL injection.
            // Plain-text password comparison is required because the provided cycling.sql
            // stores passwords in plain text and the brief prohibits modifying the schema.
            $stmt = $conn->prepare("SELECT id, username FROM user WHERE username = :u AND password = :p LIMIT 1");
            $stmt->execute([':u' => $inputUser, ':p' => $inputPass]);
            $user = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($user) {
                session_regenerate_id(true);
                $_SESSION['admin_logged_in']  = true;
                $_SESSION['admin_username']   = $user['username'];
                $_SESSION['admin_id']         = $user['id'];

                header('Location: admin_menu.php');
                exit;
            } else {
                echo '<div class="icon">&#10060;</div>';
                echo '<div class="title">Login Failed</div>';
                echo '<div class="message">Incorrect username or password. Please try again.</div>';
                echo '<a href="admin_login.html" class="btn btn-primary" style="margin-top:1rem;">Try Again</a>';
            }
        } catch (PDOException $e) {
            echo '<div class="icon">&#128295;</div>';
            echo '<div class="title">Server Error</div>';
            echo '<div class="message">A database error occurred. Please try again later.</div>';
            echo '<a href="admin_login.html" class="btn btn-secondary" style="margin-top:1rem;">Back to Login</a>';
        }
    }
}
?>
</div>
</div>
<footer style="background:rgba(0,0,0,0.3); color:rgba(255,255,255,0.5); text-align:center; padding:1rem;">
    &copy; 2024 <span style="color:#e94560;">Cit-E Cycling</span> &mdash; All rights reserved.
</footer>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="interactions.js"></script>
</body>
</html>
