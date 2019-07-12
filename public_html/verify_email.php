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

$email_hash = $_POST['email_hash'];

$stmt = $mysqli->prepare("UPDATE users SET email_validated = 1 WHERE email_hash = ?");

$stmt->bind_param("s", $email_hash);

if (!$stmt->execute()) {
    echo json_encode(["success" => false]);
    die();
}
$stmt->close();

echo json_encode(["success" => true]);
