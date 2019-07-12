<?php
session_start();

header("Access-Control-Allow-Origin: http://localhost:3000");
header('Access-Control-Allow-Credentials: true');
header("Access-Control-Allow-Headers: Content-Type");

include_once '../resources/library/database.php';
include_once '../resources/library/category.php';

$database = new Database();
$mysqli = $database->getConnection();

$rest_json = file_get_contents("php://input");
$_POST = json_decode($rest_json, true);

if ($_SESSION['admin_id']) {

    $id = $_POST['id'];
    $title = $_POST['title'];

    category::edit($mysqli, $title, $id);

    echo json_encode(["success" => true, "data" => [
        "id" => $id,
        "title" => $title
    ]]);
} else {
    echo json_encode(["success" => false]);
}
