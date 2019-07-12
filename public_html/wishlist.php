<?php
session_start();

header("Access-Control-Allow-Origin: http://localhost:3000");
header('Access-Control-Allow-Credentials: true');

session_start();

include_once '../resources/library/database.php';
include_once '../resources/library/wishlist.php';

$database = new Database();
$mysqli = $database->getConnection();

if ($_SESSION['id']) {

    // get wishlist id
    $wishlist_id = wishlist::getWishlistId($mysqli, $_SESSION['id']);

    // get wishlist
    $data = wishlist::get($mysqli, $wishlist_id);

    echo json_encode(["success" => true, "data" => $data]);
} else {
    echo json_encode(["success" => false]);
}
