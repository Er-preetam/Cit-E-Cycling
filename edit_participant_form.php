<?php
// edit_participant_form.php — Included by edit_participant.php for the GET (display) flow
// Restores the original scaffold filename while using the styled implementation
// $row must be set by edit_participant.php before including this file
require_once 'auth_check.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Participant | Cit-E Cycling</title>
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
            <li><a href="view_participants_edit_delete.php" class="active">Participants</a></li>
            <li><a href="logout.php" class="btn-logout">Logout</a></li>
        </ul>
    </nav>
    <div class="page-header">
        <h1>Edit Participant Scores</h1>
        <p>Update power output and distance travelled.</p>
    </div>
    <main class="main-container" style="max-width:600px;">
        <div class="card glow-box">
            <div class="card-title">Editing: <?= htmlspecialchars($row['firstname'] . ' ' . $row['surname']) ?></div>
            <div class="alert alert-info" style="margin-bottom:1rem; font-size:0.88rem;">
                Only <strong>Power Output</strong> and <strong>Distance Travelled</strong> can be updated.
            </div>
            <form action="edit_participant.php" method="POST" id="editForm" novalidate>
                <input type="hidden" name="id" value="<?= (int)$row['id'] ?>">
                <div class="form-group">
                    <label>Participant Firstname</label>
                    <input type="text" class="form-control" value="<?= htmlspecialchars($row['firstname']) ?>" disabled>
                </div>
                <div class="form-group">
                    <label>Participant Surname</label>
                    <input type="text" class="form-control" value="<?= htmlspecialchars($row['surname']) ?>" disabled>
                </div>
                <div class="form-group">
                    <label for="power_output">Power output in watts <span style="color:red;">*</span></label>
                    <input type="number"
                           id="power_output"
                           name="power_output"
                           class="form-control"
                           value="<?= htmlspecialchars($row['power_output']) ?>"
                           min="0" max="9999" step="0.1" required>
                    <div class="invalid-feedback">Please enter a valid power output (0-9999 watts).</div>
                </div>
                <div class="form-group">
                    <label for="distance_travelled">Distance in KM <span style="color:red;">*</span></label>
                    <input type="number"
                           id="distance_travelled"
                           name="distance"
                           class="form-control"
                           value="<?= htmlspecialchars($row['distance']) ?>"
                           min="0" max="9999" step="0.1" required>
                    <div class="invalid-feedback">Please enter a valid distance (0-9999 km).</div>
                </div>
                <div style="display:flex; gap:0.8rem; margin-top:1.5rem;">
                    <button type="submit" class="btn btn-primary">Update this rider</button>
                    <a href="view_participants_edit_delete.php" class="btn btn-secondary">Cancel</a>
                </div>
            </form>
        </div>
    </main>
    <footer>&copy; 2024 <span>Cit-E Cycling</span></footer>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="interactions.js"></script>
    <script>
    // ── Helper: validate a score field and toggle is-valid / is-invalid ──
    function validateScoreField(id, min, max) {
        const el = document.getElementById(id);
        const val = parseFloat(el.value);
        const ok  = !isNaN(val) && val >= min && val <= max;
        el.classList.toggle('is-invalid', !ok);
        el.classList.toggle('is-valid', ok);
        return ok;
    }

    // ── Real-time: validate on every keystroke and on blur ──
    document.getElementById('power_output').addEventListener('input', function() {
        validateScoreField('power_output', 0, 9999);
    });
    document.getElementById('power_output').addEventListener('blur', function() {
        validateScoreField('power_output', 0, 9999);
    });

    document.getElementById('distance_travelled').addEventListener('input', function() {
        validateScoreField('distance_travelled', 0, 9999);
    });
    document.getElementById('distance_travelled').addEventListener('blur', function() {
        validateScoreField('distance_travelled', 0, 9999);
    });

    // ── Submit handler ──
    document.getElementById('editForm').addEventListener('submit', function(e) {
        const pOk = validateScoreField('power_output', 0, 9999);
        const dOk = validateScoreField('distance_travelled', 0, 9999);
        if (!pOk || !dOk) {
            e.preventDefault();
            this.classList.add('was-validated');
        }
    });
    </script>
</body>
</html>
