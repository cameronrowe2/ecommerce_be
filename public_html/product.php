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

$product_id = $_POST['product_id'];

$stmt = $mysqli->prepare("SELECT p.id, p.title, p.description, p.price, p.price_deal, p.imageUrl, p.sku, p.weight, p.height, p.width, p.length, p.category_id, c.title as category_title FROM products p, categories c WHERE p.id = ? AND p.category_id = c.id");

$stmt->bind_param("i", $product_id);

if (!$stmt->execute()) {
    echo json_encode(["success" => false]);
    die();
}

$stmt->bind_result($id, $title, $description, $price, $price_deal, $imageUrl, $sku, $weight, $height, $width, $length, $category_id, $category_title);

$arr = [];
while ($stmt->fetch()) {
    $arr = [
        "id" => $id,
        "title" => htmlspecialchars($title),
        "description" => htmlspecialchars($description),
        "price" => htmlspecialchars($price),
        "price_deal" => $price_deal,
        "imageUrl" => htmlspecialchars($imageUrl),
        "sku" => htmlspecialchars($sku),
        "weight" => $weight,
        "height" => $height,
        "width" => $width,
        "length" => $length,
        "category_id" => $category_id,
        "category_title" => $category_title
    ];
}

$stmt->close();

echo json_encode(["success" => true, "data" => $arr]);
