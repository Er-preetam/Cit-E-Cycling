<?php
// ============================================================
// admin_menu.php — Enhanced Admin Dashboard
// ============================================================
require 'auth_check.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Admin Dashboard | Cit-E Cycling</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="style.css">
    <style>
        body { background: linear-gradient(135deg, #0a0e27 0%, #1a1f3a 50%, #0a0e27 100%); }
        body.admin-theme { --primary: #0a0e27; }
    </style>
</head>
<body class="admin-theme">

    <!-- NAVBAR -->
    <nav class="navbar">
        <a href="admin_menu.php" class="navbar-brand">
            <img src="img/logo.png" alt="Cit-E Cycling Logo" class="navbar-logo-img">
            <span class="badge bg-secondary ms-2" style="font-size:0.75rem;">Admin</span>
        </a>
        <button class="nav-toggle" id="navToggle" aria-label="Toggle menu" aria-expanded="false">
            <span></span><span></span><span></span>
        </button>
        <ul class="nav-links" id="navLinks">
            <li><a href="admin_menu.php" class="active">🏠 <span class="link-label">Dashboard</span></a></li>
            <li><a href="leaderboard.html">🏆 <span class="link-label">Results</span></a></li>
            <li><a href="manage_groups.html">👥 <span class="link-label">Groups</span></a></li>
            <li><a href="search_form.php">🔍 <span class="link-label">Search</span></a></li>
            <li><a href="view_participants_edit_delete.php">📋 <span class="link-label">Participants</span></a></li>
            <li><a href="logout.php" class="btn-logout">🚪 <span class="link-label">Logout</span></a></li>
        </ul>
    </nav>

    <!-- PAGE HEADER -->
    <div class="page-header">
        <div class="page-header-shape page-header-shape-1"></div>
        <div class="page-header-shape page-header-shape-2"></div>
        <h1>⚙️ Admin Dashboard</h1>
        <p>Welcome, <strong><?= htmlspecialchars($_SESSION['admin_username']) ?></strong> — manage participants, results, and more.</p>
    </div>

    <!-- MAIN -->
    <main class="main-container" style="max-width: 1200px;">

        <?php include 'secure_admin_details.php'; ?>

        <!-- INFO CARD -->
        <div class="card glow-box" style="margin-bottom: 1.5rem; border-top: 3px solid var(--neon-cyan);">
            <div class="alert alert-info" style="margin-bottom: 0; background: rgba(0, 245, 255, 0.1); border: 1px solid rgba(0, 245, 255, 0.3); color: var(--text-light);">
                ℹ️ Use the quick links below to manage participants, view results, and handle event data.
            </div>
        </div>

        <!-- QUICK LINKS -->
        <div class="quick-links">

            <a href="view_participants_edit_delete.php" class="quick-link-card">
                <div class="ql-icon">👥</div>
                <div class="ql-title">Manage Participants</div>
                <div class="ql-desc">View, edit, update scores, or delete participant records.</div>
            </a>

            <a href="search_form.php" class="quick-link-card">
                <div class="ql-icon">🔍</div>
                <div class="ql-title">Search Participants</div>
                <div class="ql-desc">Find participants by name or search clubs and group stats.</div>
            </a>

            <a href="leaderboard.html" class="quick-link-card">
                <div class="ql-icon">🏆</div>
                <div class="ql-title">View Leaderboard</div>
                <div class="ql-desc">See prize winners by age, gender, and performance metrics.</div>
            </a>

            <a href="manage_groups.html" class="quick-link-card">
                <div class="ql-icon">👥</div>
                <div class="ql-title">Manage Groups</div>
                <div class="ql-desc">View registered cycling groups and their performance data.</div>
            </a>

            <a href="logout.php" class="quick-link-card" style="border-bottom-color: var(--neon-pink);">
                <div class="ql-icon">🚪</div>
                <div class="ql-title">Logout</div>
                <div class="ql-desc">Securely end your admin session and return to homepage.</div>
            </a>

        </div>

        <!-- ADMIN STATS -->
        <div class="card" style="margin-top: 2rem;">
            <div class="card-title">📊 Quick Statistics</div>
            <div id="statsContainer" style="text-align: center; padding: 1rem;">
                <p style="color: var(--muted);">Loading statistics...</p>
            </div>
        </div>

    </main>

    <!-- FOOTER -->
    <footer>
        &copy; 2024 <span>Cit-E Cycling</span> &mdash; Secure Admin
    </footer>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="interactions.js"></script>
    
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            loadAdminStats();
        });

        function loadAdminStats() {
            fetch('get_admin_stats.php')
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        displayStats(data);
                    } else {
                        showStatsError(data.error || 'Unable to load admin statistics.');
                    }
                })
                .catch(error => showStatsError('Error loading stats: ' + error.message));
        }

        function displayStats(data) {
            const html = `
                <div class="stats-grid">
                    <div class="stat-card blue">
                        <div class="stat-value">${data.total_participants}</div>
                        <div class="stat-label">Total Participants</div>
                    </div>
                    <div class="stat-card green">
                        <div class="stat-value">${data.active_groups}</div>
                        <div class="stat-label">Active Groups</div>
                    </div>
                    <div class="stat-card gold">
                        <div class="stat-value">${data.total_distance.toFixed(0)}mi</div>
                        <div class="stat-label">Total Distance</div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-value">${data.total_power.toFixed(0)}W</div>
                        <div class="stat-label">Total Power</div>
                    </div>
                </div>
            `;
            document.getElementById('statsContainer').innerHTML = html;
        }

        function showStatsError(message) {
            document.getElementById('statsContainer').innerHTML = `
                <div class="alert alert-warning" style="margin: 1rem;">
                    <strong>Unable to load statistics:</strong> ${message}
                </div>
            `;
        }
    </script>

</body>
</html>
