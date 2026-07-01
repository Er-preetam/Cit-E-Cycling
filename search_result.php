<?php
// search_result.php — Display participant or club search results
require 'auth_check.php';
include 'dbconnect.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Search Results | Cit-E Cycling</title>
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
        <h1>Search Results</h1>
        <p>Results matching your query.</p>
    </div>
    <main class="main-container">
    <?php

    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        echo '<div class="alert alert-warning">No search submitted. <a href="search_form.php">Go back to search</a></div>';
    } else {
        try {
            $conn = new PDO("mysql:host=$servername;dbname=$database", $username, $password);
            $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

            // Determine search type using the explicit hidden field
            $searchType = trim($_POST['search_type'] ?? '');
            $isClub = ($searchType === 'club');

            // ---- PARTICIPANT SEARCH ----
            if (!$isClub) {

                $query      = trim($_POST['firstname'] ?? '');
                $safe_query = htmlspecialchars($query);

                if (empty($query)) {
                    echo '<div class="alert alert-warning">Please enter a name to search. <a href="search_form.php">Try again</a></div>';
                } else {
                    $sql = "SELECT p.*, c.name AS club_name
                            FROM participant p
                            LEFT JOIN club c ON p.club_id = c.id
                            WHERE LOWER(p.firstname) LIKE LOWER(:q)
                               OR LOWER(p.surname)    LIKE LOWER(:q)
                            ORDER BY p.surname ASC";
                    $stmt = $conn->prepare($sql);
                    $stmt->execute([':q' => '%' . $query . '%']);
                    $results = $stmt->fetchAll(PDO::FETCH_ASSOC);

                    echo '<div class="card glow-box">';
                    echo '<div class="card-title">Participants matching &quot;' . $safe_query . '&quot;</div>';

                    if (empty($results)) {
                        echo '<div class="alert alert-info">No participants found matching &quot;' . $safe_query . '&quot;.</div>';
                    } else {
                        echo '<p style="margin-bottom:1rem; color:var(--muted); font-size:0.88rem;">Found <strong>' . count($results) . '</strong> result(s).</p>';
                        echo '<div class="table-wrapper"><table><thead><tr>';
                        echo '<th>#</th><th>First Name</th><th>Surname</th><th>Email</th><th>Club</th><th>Power (W)</th><th>Distance (km)</th>';
                        echo '</tr></thead><tbody>';
                        foreach ($results as $i => $r) {
                            $fn   = str_ireplace($safe_query, '<mark>' . $safe_query . '</mark>', htmlspecialchars($r['firstname']));
                            $sn   = str_ireplace($safe_query, '<mark>' . $safe_query . '</mark>', htmlspecialchars($r['surname']));
                            $club = htmlspecialchars($r['club_name'] ?? 'N/A');
                            echo '<tr>';
                            echo '<td>' . ($i + 1) . '</td>';
                            echo '<td>' . $fn . '</td>';
                            echo '<td>' . $sn . '</td>';
                            echo '<td style="font-size:0.82rem;">' . htmlspecialchars($r['email']) . '</td>';
                            echo '<td><span class="badge badge-club">' . $club . '</span></td>';
                            echo '<td>' . number_format((float)$r['power_output'], 1) . '</td>';
                            echo '<td>' . number_format((float)$r['distance'], 1) . '</td>';
                            echo '</tr>';
                        }
                        echo '</tbody></table></div>';
                    }
                    echo '</div>';
                }

            // ---- CLUB SEARCH ----
            } else {

                $query      = trim($_POST['club'] ?? '');
                $safe_query = htmlspecialchars($query);

                if (empty($query)) {
                    echo '<div class="alert alert-warning">Please enter a club name. <a href="search_form.php">Try again</a></div>';
                } else {
                    $clubStmt = $conn->prepare("SELECT * FROM club WHERE name LIKE :q ORDER BY name ASC");
                    $clubStmt->execute([':q' => '%' . $query . '%']);
                    $clubs = $clubStmt->fetchAll(PDO::FETCH_ASSOC);

                    if (empty($clubs)) {
                        echo '<div class="card"><div class="alert alert-info">No clubs found matching &quot;' . $safe_query . '&quot;.</div></div>';
                    } else {
                        foreach ($clubs as $club) {
                            $club_id   = (int) $club['id'];
                            $club_name = htmlspecialchars($club['name']);
                            $club_loc  = htmlspecialchars($club['location']);

                            // Get all members for this club
                            $pStmt = $conn->prepare("SELECT * FROM participant WHERE club_id = :cid ORDER BY surname ASC");
                            $pStmt->execute([':cid' => $club_id]);
                            $members = $pStmt->fetchAll(PDO::FETCH_ASSOC);

                            // Calculate totals and averages
                            $totalDist  = 0;
                            $totalPower = 0;
                            $count      = count($members);
                            foreach ($members as $m) {
                                $totalDist  += (float) $m['distance'];
                                $totalPower += (float) $m['power_output'];
                            }
                            $avgDist  = $count > 0 ? $totalDist  / $count : 0;
                            $avgPower = $count > 0 ? $totalPower / $count : 0;

                            echo '<div class="card glow-box" style="margin-bottom:1.5rem;">';
                            echo '<div class="card-title">' . $club_name . ' <small style="font-weight:400; color:var(--muted); font-size:0.82rem;">(' . $club_loc . ')</small></div>';

                            // Aggregate stats
                            echo '<div class="stats-grid" style="grid-template-columns:repeat(auto-fit,minmax(130px,1fr)); margin-bottom:1.5rem;">';
                            echo '<div class="stat-card blue"><div class="stat-value">' . $count . '</div><div class="stat-label">Members</div></div>';
                            echo '<div class="stat-card green"><div class="stat-value">' . number_format($totalDist, 1) . '</div><div class="stat-label">Total Distance (km)</div></div>';
                            echo '<div class="stat-card"><div class="stat-value">' . number_format($totalPower, 1) . '</div><div class="stat-label">Total Power (W)</div></div>';
                            echo '<div class="stat-card gold"><div class="stat-value">' . number_format($avgDist, 1) . '</div><div class="stat-label">Avg Distance (km)</div></div>';
                            echo '<div class="stat-card"><div class="stat-value">' . number_format($avgPower, 1) . '</div><div class="stat-label">Avg Power (W)</div></div>';
                            echo '</div>';

                            if (empty($members)) {
                                echo '<div class="alert alert-info">No participants registered for this club yet.</div>';
                            } else {
                                echo '<div class="table-wrapper"><table>';
                                echo '<thead><tr><th>#</th><th>First Name</th><th>Surname</th><th>Email</th><th>Power (W)</th><th>Distance (km)</th></tr></thead><tbody>';
                                foreach ($members as $i => $m) {
                                    echo '<tr>';
                                    echo '<td>' . ($i + 1) . '</td>';
                                    echo '<td>' . htmlspecialchars($m['firstname']) . '</td>';
                                    echo '<td>' . htmlspecialchars($m['surname']) . '</td>';
                                    echo '<td style="font-size:0.82rem;">' . htmlspecialchars($m['email']) . '</td>';
                                    echo '<td>' . number_format((float)$m['power_output'], 1) . '</td>';
                                    echo '<td>' . number_format((float)$m['distance'], 1) . '</td>';
                                    echo '</tr>';
                                }
                                echo '</tbody></table></div>';
                            }
                            echo '</div>';
                        }
                    }
                }
            }

        } catch (PDOException $e) {
            echo '<div class="alert alert-danger">Database error. Please try again later.</div>';
        }
    }
    ?>

    <div style="margin-top:1.5rem;">
        <a href="search_form.php" class="btn btn-secondary">New Search</a>
    </div>
    </main>
    <footer>&copy; 2024 <span>Cit-E Cycling</span> &mdash; Secure Admin</footer>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="interactions.js"></script>
</body>
</html>
