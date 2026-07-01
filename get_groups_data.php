<?php
// get_groups_data.php — Fetch all clubs and their participant stats (JSON)
// Uses only the provided schema: club and participant tables.
header('Content-Type: application/json');
include 'dbconnect.php';

try {
    $conn = new PDO(
        "mysql:host=$servername;dbname=$database",
        $username,
        $password
    );
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Fetch all clubs with aggregated participant statistics
    $sql = "SELECT c.id, c.name AS club_name, c.location,
                   COUNT(p.id)                  AS member_count,
                   COALESCE(SUM(p.distance), 0)      AS total_distance,
                   COALESCE(SUM(p.power_output), 0)  AS total_power_output
            FROM club c
            LEFT JOIN participant p ON p.club_id = c.id
            GROUP BY c.id, c.name, c.location
            ORDER BY member_count DESC";
    $stmt = $conn->prepare($sql);
    $stmt->execute();
    $clubs = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Overall statistics
    $totalClubs   = count($clubs);
    $totalMembers = array_sum(array_column($clubs, 'member_count'));
    $largestClub  = $totalClubs > 0 ? $clubs[0]['club_name'] : 'N/A';
    $maxPower     = $totalClubs > 0 ? max(array_column($clubs, 'total_power_output')) : 0;

    // Format output
    $formatted = array_map(function($c) {
        return [
            'club_name'          => $c['club_name'],
            'location'           => $c['location'],
            'member_count'       => (int)$c['member_count'],
            'total_distance'     => (float)$c['total_distance'],
            'total_power_output' => (float)$c['total_power_output']
        ];
    }, $clubs);

    echo json_encode([
        'success' => true,
        'groups'  => $formatted,
        'statistics' => [
            'total_groups'  => $totalClubs,
            'total_members' => $totalMembers,
            'largest_group' => $largestClub,
            'top_performer' => $maxPower > 0 ? round($maxPower) . 'W' : 'N/A'
        ]
    ]);

} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => 'Database error.']);
}
?>
