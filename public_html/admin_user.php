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

    $admin_user_id = $_POST['id'];

    $stmt = $mysqli->prepare("SELECT id, name, email FROM admin_users WHERE id = ?");

    $stmt->bind_param("i", $admin_user_id);

    if (!$stmt->execute()) {
        echo json_encode(["success" => false]);
        die();
    }

    $stmt->bind_result($id, $name, $email);

    $arr = [];
    while ($stmt->fetch()) {
        $arr = [
            "id" => $id,
            "name" => htmlspecialchars($name),
            "email" => htmlspecialchars($email)
        ];
    }

    $stmt->close();

    echo json_encode(["success" => true, "data" => $arr]);
} else {
    echo json_encode(["success" => false]);
}
