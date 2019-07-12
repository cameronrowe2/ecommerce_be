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

    $title = $_POST['title'];

    $stmt = $mysqli->prepare("INSERT INTO categories (title) VALUES (?)");

    $stmt->bind_param("s", $title);

    if (!$stmt->execute()) {
        echo json_encode(["success" => false]);
        die();
    }

    $category_id = $stmt->insert_id;

    $stmt->close();

    $mysqli->close();

    echo json_encode(["success" => true, "data" => [
        "id" => $category_id,
        "title" => $title
    ]]);
} else {
    echo json_encode(["success" => false]);
}
