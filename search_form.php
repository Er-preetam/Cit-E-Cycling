<?php
// search_form.php — Search for participants or clubs
require 'auth_check.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Search | Cit-E Cycling</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="style.css">
</head>
<body class="admin-theme">
    <nav class="navbar">
        <a href="admin_menu.php" class="navbar-brand">
            <img src="img/logo.png" alt="Cit-E Cycling Logo" class="navbar-logo-img">
        </a>
        <button class="nav-toggle" id="navToggle" aria-label="Toggle menu" aria-expanded="false">
            <span></span><span></span><span></span>
        </button>
        <ul class="nav-links" id="navLinks">
            <li><a href="admin_menu.php">Dashboard</a></li>
            <li><a href="search_form.php" class="active">Search</a></li>
            <li><a href="view_participants_edit_delete.php">Participants</a></li>
            <li><a href="logout.php" class="btn-logout">Logout</a></li>
        </ul>
    </nav>
    <div class="page-header">
        <h1>Search &amp; Explore</h1>
        <p>Find participants by name, or search clubs to see their stats.</p>
    </div>
    <main class="main-container" style="max-width:700px;">

        <!-- Participant Search -->
        <div class="card glow-box" style="margin-bottom:1.5rem;">
            <div class="card-title">Search for a Participant</div>
            <form action="search_result.php" method="POST" id="participantForm" novalidate>
                <input type="hidden" name="participant" value="1">
                <div class="form-group">
                    <label for="p-search">First Name or Surname</label>
                    <input type="text"
                           id="p-search"
                           name="firstname"
                           class="form-control"
                           placeholder="e.g. Lemmy or Stavers"
                           maxlength="100"
                           required>
                    <div class="invalid-feedback">Please enter a name to search.</div>
                </div>
                <button type="submit" class="btn btn-primary">Search Participants</button>
            </form>
        </div>

        <!-- Club Search -->
        <div class="card glow-box">
            <div class="card-title">Search for a Club</div>
            <form action="search_result.php" method="POST" id="clubForm" novalidate>
                <input type="hidden" name="search_type" value="club">
                <div class="form-group">
                    <label for="c-search">Club Name</label>
                    <input type="text"
                           id="c-search"
                           name="club"
                           class="form-control"
                           placeholder="e.g. Roker Rollers"
                           maxlength="100"
                           required>
                    <div class="invalid-feedback">Please enter a club name to search.</div>
                </div>
                <button type="submit" class="btn btn-primary">Search Clubs</button>
            </form>
        </div>

        <div style="margin-top:1.5rem;">
            <a href="admin_menu.php" class="btn btn-secondary">Back to Dashboard</a>
        </div>
    </main>
    <footer>&copy; 2024 <span>Cit-E Cycling</span> &mdash; Secure Admin</footer>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="interactions.js"></script>
    <script>
    // ── Real-time: clear invalid state as soon as user starts typing ──
    document.getElementById('p-search').addEventListener('input', function() {
        if (this.value.trim()) {
            this.classList.remove('is-invalid');
            this.classList.add('is-valid');
        } else {
            this.classList.remove('is-valid');
        }
    });
    document.getElementById('p-search').addEventListener('blur', function() {
        if (!this.value.trim()) { this.classList.add('is-invalid'); }
    });

    document.getElementById('c-search').addEventListener('input', function() {
        if (this.value.trim()) {
            this.classList.remove('is-invalid');
            this.classList.add('is-valid');
        } else {
            this.classList.remove('is-valid');
        }
    });
    document.getElementById('c-search').addEventListener('blur', function() {
        if (!this.value.trim()) { this.classList.add('is-invalid'); }
    });

    // ── Submit handlers ──
    document.getElementById('participantForm').addEventListener('submit', function(e) {
        const val = document.getElementById('p-search').value.trim();
        if (!val) {
            e.preventDefault();
            document.getElementById('p-search').classList.add('is-invalid');
        }
    });
    document.getElementById('clubForm').addEventListener('submit', function(e) {
        const val = document.getElementById('c-search').value.trim();
        if (!val) {
            e.preventDefault();
            document.getElementById('c-search').classList.add('is-invalid');
        }
    });
    </script>
</body>
</html>
