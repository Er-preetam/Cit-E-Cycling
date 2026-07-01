<?php
// ============================================================
// secure_admin_details.php — Reusable widget for admin session details
// ============================================================
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$adminUsername = $_SESSION['admin_username'] ?? '';
$adminId        = $_SESSION['admin_id'] ?? '';

?>

<div class="card" style="margin-bottom:1.2rem;">
    <div class="alert alert-info" style="margin:0;">
        🧾 <strong>Secure Admin Details</strong>
        <div style="margin-top:0.35rem; font-size:0.9rem;">
            <div>👤 Username: <strong><?= htmlspecialchars((string)$adminUsername) ?></strong></div>
            <div>🆔 Secure Admin ID: <strong><?= htmlspecialchars((string)$adminId) ?></strong></div>
        </div>
    </div>
</div>

