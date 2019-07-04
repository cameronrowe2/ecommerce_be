<?php
session_start();

header("Access-Control-Allow-Origin: http://localhost:3000");
header('Access-Control-Allow-Credentials: true');
header("Access-Control-Allow-Headers: Content-Type");

include_once '../resources/library/database.php';

$rest_json = file_get_contents("php://input");
$_POST = json_decode($rest_json, true);

$database = new Database();
$mysqli = $database->getConnection();

if ($_SESSION['id']) {

    $order_id = $_POST['order_id'];

    // get all cart_items
    $stmt = $mysqli->prepare("SELECT order_items.product_id, order_items.quantity, products.price, products.title, products.sku FROM order_items, products where order_items.order_id = ? AND products.id = order_items.product_id");

    $stmt->bind_param("s", $order_id);

    if (!$stmt->execute()) {
        echo json_encode(["success" => false]);
        die();
    }

    $stmt->bind_result($product_id, $quantity, $product_price, $product_title, $product_sku);

    $order_items = [];
    $total_price = 0;
    while ($stmt->fetch()) {
        $order_items[] = [
            "product_id" => $product_id,
            "quantity" => $quantity,
            "product_title" => $product_title,
            "product_sku" => $product_sku,
            'product_price' => $product_price
        ];
        $total_price = $total_price + $quantity * $product_price;
    }
    $stmt->close();

    echo json_encode(["success" => true, "data" => [
        'total_price' => $total_price,
        'order_items' => $order_items,
        'id' => $order_id
    ]]);
} else {
    echo json_encode(["success" => false]);
}
