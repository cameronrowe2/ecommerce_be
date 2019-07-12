<?php
session_start();

header("Access-Control-Allow-Origin: http://localhost:3000");
header('Access-Control-Allow-Credentials: true');

session_start();

include_once '../resources/library/database.php';
include_once '../resources/library/cart.php';

$database = new Database();
$mysqli = $database->getConnection();

if ($_SESSION['id']) {

    // get cart id
    $cart_id = cart::getCartId($mysqli, $_SESSION['id']);

    // get all cart items
    $data = cart::get($mysqli, $cart_id);

    echo json_encode(["success" => true, "data" => $data]);
} else {
    // echo json_encode(["success" => false]);
    $cookie = isset($_COOKIE['cart_items_cookie']) ? $_COOKIE['cart_items_cookie'] : "";
    $cookie = stripslashes($cookie);
    $saved_cart_items = json_decode($cookie, true);

    // if $saved_cart_items is null, prevent null error
    if (!$saved_cart_items) {
        $saved_cart_items = array();
    }

    $data = cart::getFromCookie($mysqli, $saved_cart_items);

    echo json_encode(["success" => true, "data" => $data]);
}
