<?php

// get all ids

include_once '../resources/library/database.php';

$database = new Database();
$mysqli = $database->getConnection();

$stmt = $mysqli->prepare("SELECT id FROM products");

if (!$stmt->execute()) {
    echo json_encode(["success" => false]);
    die();
}

$stmt->bind_result($id);

$arr = [];
while ($stmt->fetch()) {
    $arr[] = $id;
}

$stmt->close();

// echo json_encode($arr);

// set all rows randomly
foreach ($arr as $value) {

    $weight = rand(10, 200) / 100;
    $height = rand(10, 100);
    $width = rand(10, 100);
    $length = rand(10, 100);

    $stmt = $mysqli->prepare("UPDATE products SET weight=?, height=?, width=?, length=? WHERE id = ?");

    $stmt->bind_param("diiis", $weight, $height, $width, $length, $value);

    if (!$stmt->execute()) {
        echo json_encode(["success" => false]);
        die();
    }
    $stmt->close();
}

echo json_encode(["success" => true]);
