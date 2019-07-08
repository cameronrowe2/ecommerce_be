<?php
session_start();

header("Access-Control-Allow-Origin: http://localhost:3000");
header('Access-Control-Allow-Credentials: true');
header("Access-Control-Allow-Headers: Content-Type");

include_once '../resources/library/database.php';
include_once '../resources/library/admin_user.php';

$database = new Database();
$mysqli = $database->getConnection();

$rest_json = file_get_contents("php://input");
$_POST = json_decode($rest_json, true);

if ($_SESSION['admin_id']) {

    $id = $_POST['id'];
    $name = $_POST['name'];

    $stmt = $mysqli->prepare("UPDATE admin_users SET name=? WHERE id = ?");

    $stmt->bind_param("ss", $name, $id);

    if (!$stmt->execute()) {
        echo json_encode(["success" => false]);
        die();
    }

    $stmt->close();

    $data = admin_user::get($mysqli, $id);

    echo json_encode(["success" => true, "data" => $data]);
} else {
    echo json_encode(["success" => false]);
}
