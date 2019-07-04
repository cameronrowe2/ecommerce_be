<?php
session_start();

header("Access-Control-Allow-Origin: http://localhost:3000");
header('Access-Control-Allow-Credentials: true');

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

    if (isset($cart_id)) {

        // create order
        $stmt = $mysqli->prepare("INSERT INTO orders (user_id)  VALUES (?)");

        $stmt->bind_param("s", $_SESSION['id']);

        if (!$stmt->execute()) {
            echo json_encode(["success" => false]);
            die();
        }
        $order_id = $stmt->insert_id;

        $stmt->close();

        // get all cart_items
        $stmt = $mysqli->prepare("SELECT product_id, quantity FROM cart_items WHERE cart_id = ?");

        $stmt->bind_param("s", $cart_id);

        if (!$stmt->execute()) {
            echo json_encode(["success" => false]);
            die();
        }

        $stmt->bind_result($product_id, $quantity);

        $cart_items = [];
        while ($stmt->fetch()) {
            $cart_items[] = [
                "product_id" => $product_id,
                "quantity" => $quantity
            ];
        }
        $stmt->close();

        // create order_items
        foreach ($cart_items as $cart_item) {
            $stmt = $mysqli->prepare("INSERT INTO order_items (order_id, product_id, quantity)  VALUES (?, ?, ?)");

            $stmt->bind_param("ssi", $order_id, $cart_item['product_id'], $cart_item['quantity']);

            if (!$stmt->execute()) {
                echo json_encode(["success" => false]);
                die();
            }
            $stmt->close();
        }

        // clear cart_items
        $stmt = $mysqli->prepare("DELETE FROM cart_items WHERE cart_id = ?");

        $stmt->bind_param("s", $cart_id);

        if (!$stmt->execute()) {
            echo json_encode(["success" => false]);
            die();
        }

        $stmt->close();

        // clear cart
        // $stmt = $mysqli->prepare("DELETE FROM carts WHERE id = ?");

        // $stmt->bind_param("s", $cart_id);

        // if (!$stmt->execute()) {
        //     echo json_encode(["success" => false]);
        //     die();
        // }

        // $stmt->close();

        echo json_encode(["success" => true]);
    } else {
        echo json_encode(["success" => false]);
    }
} else {
    echo json_encode(["success" => false]);
}
