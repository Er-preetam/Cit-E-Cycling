<?php
// delete.php — Delete a participant record
require 'auth_check.php';
include 'dbconnect.php';

$id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);

if (!$id || $id < 1) {
    header('Location: view_participants_edit_delete.php?msg=err');
    exit;
}

try {
    $conn = new PDO("mysql:host=$servername;dbname=$database", $username, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Verify participant exists before deleting
    $check = $conn->prepare("SELECT id FROM participant WHERE id = :id LIMIT 1");
    $check->execute([':id' => $id]);
    if (!$check->fetch()) {
        header('Location: view_participants_edit_delete.php?msg=err');
        exit;
    }

    $stmt = $conn->prepare("DELETE FROM participant WHERE id = :id");
    $stmt->execute([':id' => $id]);

    header('Location: view_participants_edit_delete.php?msg=deleted');
    exit;

} catch (PDOException $e) {
    header('Location: view_participants_edit_delete.php?msg=err');
    exit;
}
?>
