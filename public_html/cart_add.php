<?php
session_start();

header("Access-Control-Allow-Origin: http://localhost:3000");
header('Access-Control-Allow-Credentials: true');
header("Access-Control-Allow-Headers: Content-Type");

include_once '../resources/library/database.php';
include_once '../resources/library/cart.php';
include_once '../resources/library/wishlist.php';

$database = new Database();
$mysqli = $database->getConnection();

$rest_json = file_get_contents("php://input");
$_POST = json_decode($rest_json, true);

$product_id = $_POST['product_id'];

if ($_SESSION['id']) {

    // get cart id
    $cart_id = cart::getCartId($mysqli, $_SESSION['id']);

    // existing product check
    $quantity = cart::getProductQuantity($mysqli, $_SESSION['id'], $product_id);

    if ($quantity == null) {
        $quantity = 1;

        // add row
        cart::addProduct($mysqli, $cart_id, $product_id, $quantity);
    } else {
        // add 1 more
        $quantity++;

        // increment product
        cart::incrementProduct($mysqli, $quantity, $product_id, $cart_id);
    }

    // get wishlist id
    $wishlist_id = wishlist::getWishlistId($mysqli, $_SESSION['id']);

    // delete from wishlist
    wishlist::removeProduct($mysqli, $wishlist_id, $product_id);

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

    if (isset($saved_cart_items[$product_id])) {
        $saved_cart_items[$product_id]['quantity']++;
    } else {
        $saved_cart_items[$product_id]['quantity'] = 1;
    }

    // put item to cookie
    $json = json_encode($saved_cart_items, true);
    setcookie("cart_items_cookie", $json, time() + (86400 * 30), '/'); // 86400 = 1 day
    $_COOKIE['cart_items_cookie'] = $json;


    $data = cart::getFromCookie($mysqli, $saved_cart_items);

    echo json_encode(["success" => true, "data" => $data]);
}
