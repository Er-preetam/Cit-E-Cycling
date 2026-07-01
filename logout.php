<?php
// ============================================================
// logout.php — Destroy admin session
// ============================================================
session_start();
session_unset();
session_destroy();

header('Location: index.html');
exit;
?>
