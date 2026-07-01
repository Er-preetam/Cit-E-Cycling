<?php
// edit_participant.php — Load form (GET via edit_participant_form.php) and save scores (POST)
require 'auth_check.php';
include 'dbconnect.php';

try {
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {

        $id           = filter_input(INPUT_POST, 'id', FILTER_VALIDATE_INT);
        $power_output = filter_input(INPUT_POST, 'power_output', FILTER_VALIDATE_FLOAT);
        $distance     = filter_input(INPUT_POST, 'distance', FILTER_VALIDATE_FLOAT);

        $errors = [];
        if (!$id || $id < 1) {
            $errors[] = 'Invalid participant ID.';
        }
        if ($power_output === false || $power_output === null || $power_output < 0 || $power_output > 9999) {
            $errors[] = 'Power output must be a number between 0 and 9999 watts.';
        }
        if ($distance === false || $distance === null || $distance < 0 || $distance > 9999) {
            $errors[] = 'Distance must be a number between 0 and 9999 km.';
        }

        if (!empty($errors)) {
            header('Location: view_participants_edit_delete.php?msg=err');
            exit;
        }

        $conn = new PDO("mysql:host=$servername;dbname=$database", $username, $password);
        $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        $stmt = $conn->prepare("UPDATE participant SET power_output = :power, distance = :distance WHERE id = :id");
        $stmt->execute([':power' => $power_output, ':distance' => $distance, ':id' => $id]);

        header('Location: view_participants_edit_delete.php?msg=updated');
        exit;

    } else {

        $id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);

        if (!$id || $id < 1) {
            $errorMsg = 'Invalid participant ID.';
        } else {
            $conn = new PDO("mysql:host=$servername;dbname=$database", $username, $password);
            $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            $stmt = $conn->prepare("SELECT * FROM participant WHERE id = :id LIMIT 1");
            $stmt->execute([':id' => $id]);
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            if (!$row) {
                $errorMsg = 'Participant not found.';
            }
        }

        if (isset($errorMsg)) {
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Edit Participant | Cit-E Cycling</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="style.css">
</head>
<body class="admin-theme">
    <main class="main-container">
        <div class="alert alert-danger" style="margin-top:2rem;">
            <?= htmlspecialchars($errorMsg) ?> <a href="view_participants_edit_delete.php">Back to list</a>
        </div>
    </main>
</body>
</html>
<?php
        } else {
            // Use the original scaffold pattern: include the form file
            include 'edit_participant_form.php';
        }
    }
} catch (PDOException $e) {
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Edit Participant | Cit-E Cycling</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <main class="main-container">
        <div class="alert alert-danger" style="margin-top:2rem;">Database error. <a href="view_participants_edit_delete.php">Back to list</a></div>
    </main>
</body>
</html>
<?php
}
?>
