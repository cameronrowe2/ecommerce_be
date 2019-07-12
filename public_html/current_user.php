<?php
session_start();

header("Access-Control-Allow-Origin: http://localhost:3000");
header('Access-Control-Allow-Credentials: true');

include_once '../resources/library/database.php';
include_once '../resources/library/user.php';


$database = new Database();
$mysqli = $database->getConnection();

if ($_SESSION['id']) {

    $arr = user::get($mysqli, $_SESSION['id']);

    echo json_encode(["success" => true, "data" => $arr]);
} else {
    echo json_encode(["success" => false]);
}
