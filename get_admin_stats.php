<?php
// ============================================================
// get_admin_stats.php — Fetch admin dashboard statistics (JSON)
// ============================================================

header('Content-Type: application/json');
include 'dbconnect.php';

try {
    $conn = new PDO(
        "mysql:host=$servername;dbname=$database",
        $username,
        $password
    );
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Fetch statistics
    $statSql = "SELECT 
                    COUNT(*) as total_participants,
                    SUM(distance) as total_distance,
                    SUM(power_output) as total_power,
                    (SELECT COUNT(*) FROM club) as active_groups
                FROM participant";
    
    $statStmt = $conn->prepare($statSql);
    $statStmt->execute();
    $stats = $statStmt->fetch(PDO::FETCH_ASSOC);

    echo json_encode([
        'success' => true,
        'total_participants' => (int)($stats['total_participants'] ?? 0),
        'total_distance' => (float)($stats['total_distance'] ?? 0),
        'total_power' => (float)($stats['total_power'] ?? 0),
        'active_groups' => (int)($stats['active_groups'] ?? 0)
    ]);

} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => 'Database error: ' . $e->getMessage()
    ]);
}
?>
