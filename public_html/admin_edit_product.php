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
    $title = $_POST['title'];
    $description = $_POST['description'];
    $imageUrl = $_POST['imageUrl'];
    $price = $_POST['price'];
    $sku = $_POST['sku'];

    $stmt = $mysqli->prepare("UPDATE products SET title=?, description=?, imageUrl=?, price=?, sku=? WHERE id = ?");

    $stmt->bind_param("sssdss", $title, $description, $imageUrl, $price, $sku, $id);

    if (!$stmt->execute()) {
        echo json_encode(["success" => false]);
        die();
    }

    $stmt->close();

    echo json_encode(["success" => true, "data" => [
        "id" => $id,
        "title" => $title,
        "description" => $description,
        "price" => $price,
        "imageUrl" => $imageUrl,
        "sku" => $sku
    ]]);
} else {
    echo json_encode(["success" => false]);
}
