<?php
session_start();

header("Access-Control-Allow-Origin: http://localhost:3000");
header('Access-Control-Allow-Credentials: true');
header("Access-Control-Allow-Headers: Content-Type");

include_once '../resources/library/database.php';

$database = new Database();
$mysqli = $database->getConnection();

$rest_json = file_get_contents("php://input");
$_POST = json_decode($rest_json, true);

if ($_SESSION['admin_id']) {

    // get all cart_items
    $stmt = $mysqli->prepare("
        SELECT 
            o.id, oi.product_id, oi.quantity, p.price, p.title, p.sku, o.user_id, o.stripe_id
        FROM 
            order_items oi, products p, orders o
        WHERE 
            oi.order_id = o.id AND p.id = oi.product_id");

    if (!$stmt->execute()) {
        echo json_encode(["success" => false]);
        die();
    }

    $stmt->bind_result($order_id, $product_id, $quantity, $product_price, $product_title, $product_sku, $user_id, $stripe_id);

    $orders = [];
    while ($stmt->fetch()) {
        if (isset($orders[$order_id])) {
            $orders[$order_id]['order_items'][] = [
                "product_id" => $product_id,
                "quantity" => $quantity,
                "product_title" => $product_title,
                "product_sku" => $product_sku,
                "product_price" => $product_price
            ];
            $orders["$order_id"]['total_price'] += $quantity * $product_price;
        } else {
            $orders[$order_id] = [];
            $orders[$order_id]['id'] = $order_id;
            $orders[$order_id]['user_id'] = $user_id;
            $orders[$order_id]['stripe_id'] = $stripe_id;
            $orders[$order_id]['order_items'] = [];
            $orders[$order_id]['order_items'][] = [
                "product_id" => $product_id,
                "quantity" => $quantity,
                "product_title" => $product_title,
                "product_sku" => $product_sku,
                "product_price" => $product_price
            ];
            $orders[$order_id]['total_price'] = $quantity * $product_price;
        }
    }
    $stmt->close();

    echo json_encode([
        "success" => true, "data" => $orders
    ]);
} else {
    echo json_encode(["success" => false]);
}
