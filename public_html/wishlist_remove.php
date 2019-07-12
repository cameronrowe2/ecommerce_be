<?php
session_start();

header("Access-Control-Allow-Origin: http://localhost:3000");
header('Access-Control-Allow-Credentials: true');
header("Access-Control-Allow-Headers: Content-Type");

include_once '../resources/library/database.php';
include_once '../resources/library/wishlist.php';

$database = new Database();
$mysqli = $database->getConnection();

$rest_json = file_get_contents("php://input");
$_POST = json_decode($rest_json, true);

if ($_SESSION['id']) {

    $product_id = $_POST['product_id'];

    // get wishlist id
    $wishlist_id = wishlist::getWishlistId($mysqli, $_SESSION['id']);

    // existing product check
    $check = wishlist::productExists($mysqli, $_SESSION['id'], $product_id);

    if ($check != null) {

        // remove row
        wishlist::removeProduct($mysqli, $wishlist_id, $product_id);
    }

    $data = wishlist::get($mysqli, $wishlist_id);

    echo json_encode(["success" => true, "data" => $data]);
} else {
    echo json_encode(["success" => false]);
}
