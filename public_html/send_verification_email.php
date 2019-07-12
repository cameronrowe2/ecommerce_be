<?php
session_start();

header("Access-Control-Allow-Origin: http://localhost:3000");
header('Access-Control-Allow-Credentials: true');
header("Access-Control-Allow-Headers: Content-Type");

include_once '../resources/library/database.php';
include_once '../resources/library/email.php';

$database = new Database();
$mysqli = $database->getConnection();

$rest_json = file_get_contents("php://input");
$_POST = json_decode($rest_json, true);

if ($_SESSION['id']) {

    $stmt = $mysqli->prepare("SELECT email, email_hash FROM users WHERE id = ?");

    $stmt->bind_param("s", $_SESSION['id']);

    if (!$stmt->execute()) {
        echo json_encode(["success" => false]);
        die();
    }

    $stmt->bind_result($email, $email_hash);
    $stmt->fetch();
    $stmt->close();

    if (!email::sendVerificationEmail($email, $email_hash)) {
        echo json_encode(["success" => false]);
    }

    echo json_encode(["success" => true]);
} else {
    echo json_encode(["success" => false]);
}
