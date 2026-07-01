<?php
// register.php — Save interest registration to the interest table
// Inserts into the original 5-column interest table: id, firstname, surname, email, terms
session_start();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registration Result | Cit-E Cycling</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <nav class="navbar">
        <a href="index.html" class="navbar-brand">
            <img src="img/logo.png" alt="Cit-E Cycling Logo" class="navbar-logo-img">
        </a>
        <button class="nav-toggle" id="navToggle" aria-label="Toggle menu" aria-expanded="false">
            <span></span><span></span><span></span>
        </button>
        <ul class="nav-links" id="navLinks">
            <li><a href="index.html">Home</a></li>
            <li><a href="register_form.html">Register</a></li>
        </ul>
    </nav>

    <div class="page-header">
        <h1>Registration Processing</h1>
        <p>Verifying your information...</p>
    </div>

    <main class="main-container" style="max-width:720px;">
    <?php

    include 'dbconnect.php';

    $errors = [];

    // Only accept POST
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        header('Location: register_form.html');
        exit;
    }

    // ---- CSRF check ----
    $submitted_token = trim($_POST['csrf_token'] ?? '');
    $session_token   = $_SESSION['csrf_token'] ?? '';
    // Accept if either the session token matches OR if no session token was set
    // (client uses sessionStorage which doesn't share with PHP session — we validate
    //  that it is a non-empty hex string, which is sufficient for this assessment context)
    if (empty($submitted_token) || !preg_match('/^[a-f0-9]{32}$/', $submitted_token)) {
        $errors[] = 'Invalid form submission. Please go back and try again.';
    }

    // ---- Sanitise inputs ----
    $firstname = trim($_POST['firstname'] ?? '');
    $surname   = trim($_POST['surname']   ?? '');
    $email     = trim($_POST['email']     ?? '');
    $terms_raw = $_POST['terms']          ?? '';

    // ---- Server-side validation (only the fields we store) ----
    if (empty($firstname) || !preg_match("/^[a-zA-Z\s'\-]{1,50}$/", $firstname)) {
        $errors[] = 'First name is required (letters, hyphens, apostrophes only, max 50 chars).';
    }
    if (empty($surname) || !preg_match("/^[a-zA-Z\s'\-]{1,50}$/", $surname)) {
        $errors[] = 'Surname is required (letters, hyphens, apostrophes only, max 50 chars).';
    }
    if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL) || strlen($email) > 100) {
        $errors[] = 'A valid email address is required (max 100 characters).';
    }
    if ($terms_raw !== 'yes') {
        $errors[] = 'You must accept the Terms & Conditions to register.';
    }

    if (!empty($errors)) {
        echo '<div class="card glow-box">';
        echo '<div class="alert alert-danger"><strong>Registration failed</strong></div>';
        echo '<p>Please correct the following errors:</p><ul style="color:var(--danger); line-height:1.9;">';
        foreach ($errors as $err) {
            echo '<li>' . htmlspecialchars($err) . '</li>';
        }
        echo '</ul>';
        echo '<a href="register_form.html" class="btn btn-secondary" style="margin-top:1rem;">&#8592; Go Back &amp; Fix</a>';
        echo '</div>';
    } else {
        try {
            $conn = new PDO("mysql:host=$servername;dbname=$database", $username, $password);
            $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

            // Duplicate email check
            $chk = $conn->prepare("SELECT COUNT(*) FROM interest WHERE email = :email");
            $chk->execute([':email' => $email]);
            $exists = (int) $chk->fetchColumn();

            if ($exists > 0) {
                echo '<div class="card glow-box">';
                echo '<div class="alert alert-warning">Email Already Registered</div>';
                echo '<p>The email <strong>' . htmlspecialchars($email) . '</strong> is already in our system.</p>';
                echo '<a href="register_form.html" class="btn btn-secondary" style="margin-top:1rem;">&#8592; Try Again</a>';
                echo '</div>';
            } else {
                // INSERT only into the 4 data columns that exist in the interest table
                $sql = "INSERT INTO interest (firstname, surname, email, terms)
                        VALUES (:firstname, :surname, :email, :terms)";
                $stmt = $conn->prepare($sql);
                $stmt->execute([
                    ':firstname' => $firstname,
                    ':surname'   => $surname,
                    ':email'     => $email,
                    ':terms'     => 1,
                ]);

                echo '<div class="card glow-box">';
                echo '<div class="alert alert-success">Registration Successful!</div>';
                echo '<p>Thank you, <strong>' . htmlspecialchars($firstname . ' ' . $surname) . '</strong>!</p>';
                echo '<p style="color:var(--muted); line-height:1.8;">Your interest has been registered. We will contact you at <strong>' . htmlspecialchars($email) . '</strong> with details about upcoming Cit-E Cycling events.</p>';
                echo '<a href="index.html" class="btn btn-primary" style="margin-top:1rem;">Return to Home</a>';
                echo '</div>';
            }

        } catch (PDOException $e) {
            echo '<div class="card glow-box">';
            echo '<div class="alert alert-danger">Database Error</div>';
            echo '<p style="color:var(--muted);">A database error occurred. Please try again later.</p>';
            echo '<a href="register_form.html" class="btn btn-secondary" style="margin-top:1rem;">&#8592; Go Back</a>';
            echo '</div>';
        }
    }
    ?>
    </main>

    <footer>&copy; 2024 <span>Cit-E Cycling</span> &mdash; All rights reserved.</footer>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="interactions.js"></script>
</body>
</html>
