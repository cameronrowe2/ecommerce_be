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
    $email = $_POST['email'];

    // existing account check
    $stmt = $mysqli->prepare("SELECT id FROM admin_users where email = ?");

    $stmt->bind_param("s", $email);

    if (!$stmt->execute()) {
        echo json_encode(["success" => false]);
        die();
    }

    $stmt->bind_result($query_id);

    $count = 0;
    if ($stmt->fetch()) {
        $count++;
    }

    $stmt->close();

    if ($count > 0) {
        echo json_encode(["success" => false]);
        exit();
    }

    // update email
    $stmt = $mysqli->prepare("UPDATE admin_users SET email=? WHERE id = ?");

    $stmt->bind_param("ss", $email, $id);

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
