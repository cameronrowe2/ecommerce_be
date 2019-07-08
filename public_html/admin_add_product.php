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
    $description = $_POST['description'];
    $imageUrl = $_POST['imageUrl'];
    $price = $_POST['price'];
    $sku = $_POST['sku'];
    $weight = $_POST['weight'];
    $height = $_POST['height'];
    $width = $_POST['width'];
    $length = $_POST['length'];

    $stmt = $mysqli->prepare("INSERT INTO products (title, description, imageUrl, price, sku, weight, height, width, length)  VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");

    $stmt->bind_param("sssdsdiii", $title, $description, $imageUrl, $price, $sku, $weight, $height, $width, $length);

    if (!$stmt->execute()) {
        echo json_encode(["success" => false]);
        die();
    }

    $product_id = $stmt->insert_id;

    $stmt->close();

    $mysqli->close();

    echo json_encode(["success" => true, "data" => [
        "id" => $product_id,
        "title" => $title,
        "description" => $description,
        "price" => $price,
        "imageUrl" => $imageUrl,
        "sku" => $sku,
        "weight" => $weight,
        "height" => $height,
        "width" => $width,
        "length" => $length
    ]]);
} else {
    echo json_encode(["success" => false]);
}
