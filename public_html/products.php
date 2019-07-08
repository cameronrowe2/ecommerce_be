<?php
session_start();

header("Access-Control-Allow-Origin: http://localhost:3000");
header('Access-Control-Allow-Credentials: true');

include_once '../resources/library/database.php';

$database = new Database();
$mysqli = $database->getConnection();

$stmt = $mysqli->prepare("SELECT id, title, description, price, imageUrl, sku, weight, height, width, length FROM products");

if (!$stmt->execute()) {
  echo json_encode(["success" => false]);
  die();
}

$stmt->bind_result($id, $title, $description, $price, $imageUrl, $sku, $weight, $height, $width, $length);

$arr = [];
while ($stmt->fetch()) {
  $arr[] = [
    "id" => $id,
    "title" => htmlspecialchars($title),
    "description" => htmlspecialchars($description),
    "price" => htmlspecialchars($price),
    "imageUrl" => htmlspecialchars($imageUrl),
    "sku" => htmlspecialchars($sku),
    "weight" => $weight,
    "height" => $height,
    "width" => $width,
    "length" => $length
  ];
}

$stmt->close();

echo json_encode(["success" => true, "data" => $arr]);
