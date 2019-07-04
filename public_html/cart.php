<?php
session_start();

header("Access-Control-Allow-Origin: http://localhost:3000");
header('Access-Control-Allow-Credentials: true');

session_start();

include_once '../resources/library/database.php';

$database = new Database();
$mysqli = $database->getConnection();

if ($_SESSION['id']) {

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

    // get all cart_items
    $stmt = $mysqli->prepare("
        SELECT 
            p.id, p.title, p.imageUrl, p.price, p.sku, ci.quantity
        FROM 
            cart_items ci, products p 
        WHERE 
            ci.cart_id = ? AND p.id = ci.product_id");

    $stmt->bind_param("s", $cart_id);

    if (!$stmt->execute()) {
        echo json_encode(["success" => false]);
        die();
    }

    $stmt->bind_result($p_id, $p_title, $p_imageUrl, $p_price, $p_sku, $quantity);

    $cart_items = [];
    $total_price = 0;
    while ($stmt->fetch()) {
        $cart_items[] = [
            "product_id" => $p_id,
            "product_title" => $p_title,
            "product_imageUrl" => $p_imageUrl,
            "product_price" => $p_price,
            "product_sku" => $p_sku,
            "quantity" => $quantity
        ];
        $total_price +=  $quantity * $p_price;
    }
    $stmt->close();

    echo json_encode(["success" => true, "data" => [
        "cart_items" => $cart_items,
        "total_price" => $total_price
    ]]);
} else {
    echo json_encode(["success" => false]);
}
