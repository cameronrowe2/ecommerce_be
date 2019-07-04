<?php
session_start();

header("Access-Control-Allow-Origin: http://localhost:3000");
header('Access-Control-Allow-Credentials: true');
header("Access-Control-Allow-Headers: Content-Type");

include_once '../resources/library/database.php';

$database = new Database();
$mysqli = $database->getConnection();

$rest_json = file_get_contents("php://input");
$_POST = json_decode($rest_json, true);

if ($_SESSION['admin_id']) {

    $id = $_POST['id'];

    $stmt = $mysqli->prepare("DELETE FROM admin_users WHERE id = ?");

    $stmt->bind_param("s", $id);

    if (!$stmt->execute()) {
        echo json_encode(["success" => false]);
        die();
    }

    $stmt->close();

    echo json_encode(["success" => true, "data" => [
        "admin_user_id" => $id
    ]]);
} else {
    echo json_encode(["success" => false]);
}
