<?php
// view_participants_edit_delete.php — Manage all participants
require 'auth_check.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Participants | Cit-E Cycling</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="style.css">
    <style>
        .actions-cell { white-space:nowrap; display:flex; gap:0.4rem; flex-wrap:wrap; }
        .search-inline { display:flex; gap:0.6rem; margin-bottom:1.2rem; align-items:center; flex-wrap:wrap; }
        .search-inline input { flex:1; min-width:180px; }
        .table-wrapper { overflow-x:auto; }
        /* Confirmation modal */
        .modal-overlay { display:none; position:fixed; inset:0; background:rgba(0,0,0,0.6); z-index:9999; align-items:center; justify-content:center; }
        .modal-overlay.active { display:flex; }
        .modal-box { background:#fff; border-radius:16px; padding:2rem 2.5rem; max-width:420px; width:90%; text-align:center; }
        .modal-icon { font-size:2.5rem; margin-bottom:1rem; }
        .modal-actions { display:flex; gap:1rem; justify-content:center; margin-top:1.5rem; }
    </style>
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
            <li><a href="search_form.php">Search</a></li>
            <li><a href="view_participants_edit_delete.php" class="active">Participants</a></li>
            <li><a href="logout.php" class="btn-logout">Logout</a></li>
        </ul>
    </nav>
    <div class="page-header">
        <h1>Manage Participants</h1>
        <p>View, edit scores, or delete participant records.</p>
    </div>
    <main class="main-container" style="max-width:1400px;">
    <?php
    include 'dbconnect.php';

    if (!empty($_GET['msg'])) {
        $msgMap = [
            'deleted' => ['success', 'Participant deleted successfully.'],
            'updated' => ['success', 'Participant scores updated successfully.'],
            'err'     => ['danger',  'An error occurred. Please try again.'],
        ];
        $key = $_GET['msg'];
        if (isset($msgMap[$key])) {
            [$type, $text] = $msgMap[$key];
            echo "<div class=\"alert alert-{$type}\">{$text}</div>";
        }
    }

    try {
        $conn = new PDO("mysql:host=$servername;dbname=$database", $username, $password);
        $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        $sql  = "SELECT p.id, p.firstname, p.surname, p.email, p.power_output, p.distance, c.name AS club_name
                 FROM participant p
                 LEFT JOIN club c ON p.club_id = c.id
                 ORDER BY p.surname ASC, p.firstname ASC";
        $stmt = $conn->prepare($sql);
        $stmt->execute();
        $participants = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $total = count($participants);
    ?>
    <div class="card glow-box">
        <div class="card-title">Participant List (<?= $total ?> total)</div>
        <div class="search-inline">
            <input type="text" class="form-control" id="filterInput" placeholder="Filter by name, email or club..." onkeyup="filterTable()">
            <small class="text-muted">Showing: <strong id="rowCount"><?= $total ?></strong></small>
        </div>
        <div class="table-wrapper">
            <table id="participantTable">
                <thead>
                    <tr>
                        <th>#</th><th>First Name</th><th>Surname</th><th>Email</th>
                        <th>Club</th><th>Power (W)</th><th>Distance (km)</th><th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                <?php if (empty($participants)): ?>
                    <tr><td colspan="8" style="text-align:center; padding:2rem; color:var(--muted);">No participants found.</td></tr>
                <?php else: ?>
                    <?php foreach ($participants as $i => $p): ?>
                    <tr>
                        <td><?= $i + 1 ?></td>
                        <td><?= htmlspecialchars($p['firstname']) ?></td>
                        <td><?= htmlspecialchars($p['surname']) ?></td>
                        <td style="font-size:0.82rem;"><?= htmlspecialchars($p['email']) ?></td>
                        <td><span class="badge badge-club"><?= htmlspecialchars($p['club_name'] ?? 'N/A') ?></span></td>
                        <td><?= number_format((float)$p['power_output'], 1) ?></td>
                        <td><?= number_format((float)$p['distance'], 1) ?></td>
                        <td>
                            <div class="actions-cell">
                                <a href="edit_participant.php?id=<?= (int)$p['id'] ?>" class="btn btn-warning btn-sm">Edit</a>
                                <button onclick="confirmDelete(<?= (int)$p['id'] ?>, '<?= htmlspecialchars($p['firstname'] . ' ' . $p['surname'], ENT_QUOTES) ?>')" class="btn btn-danger btn-sm">Delete</button>
                            </div>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
    <?php
    } catch (PDOException $e) {
        echo '<div class="alert alert-danger">Database error: ' . htmlspecialchars($e->getMessage()) . '</div>';
    }
    ?>
    </main>

    <!-- Delete Confirmation Modal -->
    <div class="modal-overlay" id="deleteModal" role="dialog" aria-modal="true" aria-labelledby="modalTitle">
        <div class="modal-box">
            <div class="modal-icon">&#9888;&#65039;</div>
            <h3 id="modalTitle">Confirm Deletion</h3>
            <p id="deleteModalMsg">Are you sure you want to delete this participant? This cannot be undone.</p>
            <div class="modal-actions">
                <a id="confirmDeleteBtn" href="#" class="btn btn-danger">Yes, Delete</a>
                <button onclick="closeModal()" class="btn btn-secondary">Cancel</button>
            </div>
        </div>
    </div>

    <footer>&copy; 2024 <span>Cit-E Cycling</span> &mdash; Secure Admin</footer>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="interactions.js"></script>
    <script>
    function confirmDelete(id, name) {
        document.getElementById('deleteModalMsg').textContent =
            'Are you sure you want to permanently delete ' + name + '? This action cannot be undone.';
        document.getElementById('confirmDeleteBtn').href = 'delete.php?id=' + id;
        document.getElementById('deleteModal').classList.add('active');
    }
    function closeModal() {
        document.getElementById('deleteModal').classList.remove('active');
    }
    document.getElementById('deleteModal').addEventListener('click', function(e) {
        if (e.target === this) closeModal();
    });
    function filterTable() {
        const q = document.getElementById('filterInput').value.toLowerCase();
        const rows = document.querySelectorAll('#participantTable tbody tr');
        let count = 0;
        rows.forEach(row => {
            const match = row.textContent.toLowerCase().includes(q);
            row.style.display = match ? '' : 'none';
            if (match) count++;
        });
        document.getElementById('rowCount').textContent = count;
    }
    </script>
</body>
</html>
