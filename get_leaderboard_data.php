<?php
// get_leaderboard_data.php — Rankings using only the provided schema tables:
// participant, club, user, interest — NO cycling_group table used.
header('Content-Type: application/json');
include 'dbconnect.php';

try {
    $conn = new PDO(
        "mysql:host=$servername;dbname=$database",
        $username,
        $password
    );
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // ── TOP PERFORMERS BY POWER OUTPUT ──
    $powerSql = "SELECT firstname, surname, power_output, distance
                 FROM participant
                 WHERE power_output > 0
                 ORDER BY power_output DESC
                 LIMIT 20";
    $powerStmt = $conn->prepare($powerSql);
    $powerStmt->execute();
    $topPower = array_map(function($p) {
        return [
            'name'         => $p['firstname'] . ' ' . $p['surname'],
            'power_output' => (float)$p['power_output'],
            'distance'     => (float)$p['distance']
        ];
    }, $powerStmt->fetchAll(PDO::FETCH_ASSOC));

    // ── TOP PERFORMERS BY DISTANCE ──
    $distSql = "SELECT firstname, surname, power_output, distance
                FROM participant
                WHERE distance > 0
                ORDER BY distance DESC
                LIMIT 20";
    $distStmt = $conn->prepare($distSql);
    $distStmt->execute();
    $topDistance = array_map(function($p) {
        return [
            'name'         => $p['firstname'] . ' ' . $p['surname'],
            'power_output' => (float)$p['power_output'],
            'distance'     => (float)$p['distance']
        ];
    }, $distStmt->fetchAll(PDO::FETCH_ASSOC));

    // ── CLUB RANKINGS (uses the club table + participant table only) ──
    $clubSql = "SELECT c.id, c.name AS club_name, c.location,
                       COUNT(p.id)           AS member_count,
                       COALESCE(SUM(p.power_output), 0) AS total_power_output,
                       COALESCE(SUM(p.distance), 0)     AS total_distance
                FROM club c
                LEFT JOIN participant p ON p.club_id = c.id
                GROUP BY c.id, c.name, c.location
                ORDER BY total_power_output DESC
                LIMIT 20";
    $clubStmt = $conn->prepare($clubSql);
    $clubStmt->execute();
    $topGroups = array_map(function($g) {
        return [
            'club_name'          => $g['club_name'],
            'location'           => $g['location'],
            'member_count'       => (int)$g['member_count'],
            'total_power_output' => (float)$g['total_power_output'],
            'total_distance'     => (float)$g['total_distance']
        ];
    }, $clubStmt->fetchAll(PDO::FETCH_ASSOC));

    // ── OVERALL STATISTICS ──
    $statStmt = $conn->prepare(
        "SELECT COUNT(*) AS total_participants,
                COALESCE(SUM(distance), 0)     AS total_distance,
                COALESCE(SUM(power_output), 0) AS total_power
         FROM participant"
    );
    $statStmt->execute();
    $stats = $statStmt->fetch(PDO::FETCH_ASSOC);

    $clubCountStmt = $conn->prepare("SELECT COUNT(*) AS club_count FROM club");
    $clubCountStmt->execute();
    $clubCount = (int)$clubCountStmt->fetchColumn();

    echo json_encode([
        'success'     => true,
        'performance' => [
            'power_output' => $topPower,
            'distance'     => $topDistance
        ],
        'groups'      => $topGroups,
        'statistics'  => [
            'total_participants' => (int)$stats['total_participants'],
            'total_distance'     => (float)$stats['total_distance'],
            'total_power'        => (float)$stats['total_power'],
            'active_groups'      => $clubCount
        ]
    ]);

} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => 'Database error.']);
}
?>
