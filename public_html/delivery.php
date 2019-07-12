<?php
session_start();

header("Access-Control-Allow-Origin: http://localhost:3000");
header('Access-Control-Allow-Credentials: true');
header("Access-Control-Allow-Headers: Content-Type");

session_start();

include_once '../resources/library/database.php';
include_once '../resources/library/cart.php';


$database = new Database();
$mysqli = $database->getConnection();

$rest_json = file_get_contents("php://input");
$_POST = json_decode($rest_json, true);

// if ($_SESSION['id']) {

$address = $_POST['address'];
$suburb = $_POST['suburb'];
$postcode = $_POST['postcode'];
$country = $_POST['country'];
$email = $_POST['email'];


// get total weight (kg)
$total_weight = 0;

// get total height (cms): h + h + h ...
$total_height = 0;

// get total length (cms): max length
$total_length = 0;

// get total width (cms): max width
$total_width = 0;

$cart = null;

if ($_SESSION['id']) {
    $cart_id = cart::getCartId($mysqli, $_SESSION['id']);
    $cart = cart::get($mysqli, $cart_id);
} else {
    $cookie = isset($_COOKIE['cart_items_cookie']) ? $_COOKIE['cart_items_cookie'] : "";
    $cookie = stripslashes($cookie);
    $saved_cart_items = json_decode($cookie, true);

    // if $saved_cart_items is null, prevent null error
    if (!$saved_cart_items) {
        $saved_cart_items = array();
    }

    $cart = cart::getFromCookie($mysqli, $saved_cart_items);
}

foreach ($cart['cart_items'] as $cart_item) {
    // weight
    $total_weight = $total_weight + (floatval($cart_item['product_weight']) * intval($cart_item['quantity']));

    // height
    $total_height = $total_height + (intval($cart_item['product_height']) * intval($cart_item['quantity']));

    // length
    if (intval($cart_item['product_length']) > $total_length) {
        $total_length = intval($cart_item['product_length']);
    }

    // width
    if (intval($cart_item['product_width']) > $total_width) {
        $total_width = intval($cart_item['product_width']);
    }
}

////////////////////////
// TEMPORARY OVERRIDE
// get total weight (kg)
$total_weight = 1;

// get total height (cms): h + h + h ...
$total_height = 10;

// get total length (cms): max length
$total_length = 10;

// get total width (cms): max width
$total_width = 10;
////////////////////////

$ch = curl_init();
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt(
    $ch,
    CURLOPT_URL,
    'https://digitalapi.auspost.com.au/postage/parcel/domestic/service.php?from_postcode=4213&to_postcode=' . $postcode . '&length=' . $total_length . '&width=' . $total_width . '&height=' . $total_height . '&weight=' . $total_weight
);
curl_setopt($ch, CURLOPT_HTTPHEADER, array(
    'auth-key: 28744ed5982391881611cca6cf5c240'
));
$content = curl_exec($ch);
echo json_encode(["success" => true, "data" => [
    "auspost" => json_decode($content),
    "address" => [
        "address" => $address,
        "suburb" => $suburb,
        "postcode" => $postcode,
        "country" => $country,
        "email" => $email,
        "total_weight" => $total_weight,
        "total_height" => $total_height,
        "total_length" => $total_length,
        "total_width" => $total_width
    ]
]]);

    // // get cart id
    // $stmt = $mysqli->prepare("SELECT id FROM carts WHERE user_id = ?");

    // $stmt->bind_param("s", $_SESSION['id']);

    // if (!$stmt->execute()) {
    //     echo json_encode(["success" => false]);
    //     die();
    // }

    // $stmt->bind_result($cart_id);
    // $stmt->fetch();
    // $stmt->close();

    // // get all cart_items
    // $stmt = $mysqli->prepare("
    //     SELECT 
    //         p.id, p.title, p.imageUrl, p.price, p.sku, ci.quantity
    //     FROM 
    //         cart_items ci, products p 
    //     WHERE 
    //         ci.cart_id = ? AND p.id = ci.product_id");

    // $stmt->bind_param("s", $cart_id);

    // if (!$stmt->execute()) {
    //     echo json_encode(["success" => false]);
    //     die();
    // }

    // $stmt->bind_result($p_id, $p_title, $p_imageUrl, $p_price, $p_sku, $quantity);

    // $cart_items = [];
    // $total_price = 0;
    // while ($stmt->fetch()) {
    //     $cart_items[] = [
    //         "product_id" => $p_id,
    //         "product_title" => $p_title,
    //         "product_imageUrl" => $p_imageUrl,
    //         "product_price" => $p_price,
    //         "product_sku" => $p_sku,
    //         "quantity" => $quantity
    //     ];
    //     $total_price +=  $quantity * $p_price;
    // }
    // $stmt->close();

    // echo json_encode(["success" => true, "data" => [
    //     "cart_items" => $cart_items,
    //     "total_price" => $total_price
    // ]]);
// } else {
//     echo json_encode(["success" => false]);
// }
