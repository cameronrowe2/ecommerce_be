<?php
session_start();

header("Access-Control-Allow-Origin: http://localhost:3000");
header('Access-Control-Allow-Credentials: true');

include_once '../resources/library/database.php';

$database = new Database();
$mysqli = $database->getConnection();

if ($_SESSION['admin_id']) {

    $id = $_POST['id'];

    $stmt = $mysqli->prepare("DELETE FROM products WHERE id = ?");

    $stmt->bind_param("s", $id);

    if (!$stmt->execute()) {
        echo json_encode(["success" => false]);
        die();
    }

    $stmt->close();

    echo json_encode(["success" => true]);
} else {
    echo json_encode(["success" => false]);
}
