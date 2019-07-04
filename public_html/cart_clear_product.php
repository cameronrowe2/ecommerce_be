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

    // remove row
    $stmt = $mysqli->prepare("DELETE FROM cart_items WHERE cart_id = ? AND product_id = ?");

    $stmt->bind_param("ss", $cart_id, $product_id);

    if (!$stmt->execute()) {
        echo json_encode(["success" => false]);
        die();
    }

    $stmt->close();

    $data = cart::get($mysqli, $cart_id);

    echo json_encode(["success" => true, "data" => $data]);
} else {
    echo json_encode(["success" => false]);
}