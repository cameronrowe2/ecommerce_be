<?php
session_start();

header("Access-Control-Allow-Origin: http://localhost:3000");
header('Access-Control-Allow-Credentials: true');

include_once '../resources/library/database.php';

$database = new Database();
$mysqli = $database->getConnection();

if ($_SESSION['id']) {

    // get all cart_items
    $stmt = $mysqli->prepare("
        SELECT 
            o.id, oi.product_id, oi.quantity, p.price, p.title, p.sku 
        FROM 
            order_items oi, products p, orders o
        WHERE 
            oi.order_id = o.id AND p.id = oi.product_id AND o.user_id = ?");

    $stmt->bind_param("s", $_SESSION['id']);

    if (!$stmt->execute()) {
        echo json_encode(["success" => false]);
        die();
    }

    $stmt->bind_result($order_id, $product_id, $quantity, $product_price, $product_title, $product_sku);

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
