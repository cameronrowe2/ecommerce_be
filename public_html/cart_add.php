<?php
session_start();

header("Access-Control-Allow-Origin: http://localhost:3000");
header('Access-Control-Allow-Credentials: true');
header("Access-Control-Allow-Headers: Content-Type");

include_once '../resources/library/database.php';
include_once '../resources/library/cart.php';

$database = new Database();
$mysqli = $database->getConnection();

$rest_json = file_get_contents("php://input");
$_POST = json_decode($rest_json, true);

if ($_SESSION['id']) {

    $product_id = $_POST['product_id'];

    // get cart id
    $stmt = $mysqli->prepare("SELECT id FROM carts WHERE user_id = ?");

    $stmt->bind_param("s", $_SESSION['id']);

    if (!$stmt->execute()) {
        echo json_encode(["success" => false]);
        die();
    }

    $stmt->bind_result($cart_id);
    $stmt->fetch();
    $stmt->close();

    // existing product check
    $stmt = $mysqli->prepare("SELECT quantity FROM carts, cart_items WHERE carts.user_id = ? AND carts.id = cart_items.cart_id AND cart_items.product_id = ?");

    $stmt->bind_param("ss", $_SESSION['id'], $product_id);

    if (!$stmt->execute()) {
        echo json_encode(["success" => false]);
        die();
    }

    $stmt->bind_result($quantity);
    $stmt->fetch();
    $stmt->close();

    if ($quantity == null) {
        $quantity = 1;

        // add row
        $stmt = $mysqli->prepare("INSERT INTO cart_items (cart_id, product_id, quantity)  VALUES (?, ?, ?)");

        $stmt->bind_param("ssi", $cart_id, $product_id, $quantity);

        if (!$stmt->execute()) {
            echo json_encode(["success" => false]);
            die();
        }

        $stmt->close();
    } else {
        // add 1 more
        $quantity++;

        $stmt = $mysqli->prepare("UPDATE cart_items SET quantity=? WHERE product_id = ? AND cart_id = ?");

        $stmt->bind_param("iss", $quantity, $product_id, $cart_id);

        if (!$stmt->execute()) {
            echo json_encode(["success" => false]);
            die();
        }
        $stmt->close();
    }

    $data = cart::get($mysqli, $cart_id);

    echo json_encode(["success" => true, "data" => $data]);
} else {
    echo json_encode(["success" => false]);
}
