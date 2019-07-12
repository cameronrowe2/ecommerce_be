<?php
session_start();

header("Access-Control-Allow-Origin: http://localhost:3000");
header('Access-Control-Allow-Credentials: true');

include_once '../resources/library/database.php';
include_once '../resources/library/category.php';

$database = new Database();
$mysqli = $database->getConnection();

$arr = category::getAll($mysqli);

$stmt = $mysqli->prepare("SELECT id, title FROM categories");

// if (!$stmt->execute()) {
//     echo json_encode(["success" => false]);
//     die();
// }

// $stmt->bind_result($id, $title);

// $arr = [];
// while ($stmt->fetch()) {
//     $arr[] = [
//         "id" => $id,
//         "title" => htmlspecialchars($title)
//     ];
// }

// $stmt->close();

echo json_encode(["success" => true, "data" => $arr]);
