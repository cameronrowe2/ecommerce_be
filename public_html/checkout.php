<?php
session_start();

header("Access-Control-Allow-Origin: http://localhost:3000");
header('Access-Control-Allow-Credentials: true');
header("Access-Control-Allow-Headers: Content-Type");

include_once '../resources/library/database.php';
include_once '../resources/library/cart.php';
include_once '../resources/library/stripe-php-6.40.0/init.php';

$database = new Database();
$mysqli = $database->getConnection();

$rest_json = file_get_contents("php://input");
$_POST = json_decode($rest_json, true);

if ($_SESSION['id']) {

    $token_id = $_POST['token_id'];
    $delivery_code = $_POST['delivery_code'];
    $delivery_name = $_POST['delivery_name'];
    $delivery_price = $_POST['delivery_price'];

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

        // get all products and total price
        $cart_data = cart::get($mysqli, $cart_id);

        // charge card

        // Set your secret key: remember to change this to your live secret key in production
        // See your keys here: https://dashboard.stripe.com/account/apikeys
        \Stripe\Stripe::setApiKey('sk_test_ZRDHNfDsMdElDPD5UsW6fkZi00W7hFhOnM');

        $charge_price = $cart_data['total_price'] * 100;

        $charge = \Stripe\Charge::create(['amount' => $charge_price, 'currency' => 'aud', 'source' => $token_id]);

        // echo json_encode($charge);
        // return;

        // create order
        $stmt = $mysqli->prepare("INSERT INTO orders (user_id, stripe_id)  VALUES (?, ?)");

        $stmt->bind_param("ss", $_SESSION['id'], $charge['id']);

        if (!$stmt->execute()) {
            echo json_encode(["success" => false]);
            throw new Exception($mysqli->error);
            die();
        }
        $order_id = $stmt->insert_id;

        $stmt->close();

        // create delivery
        $stmt = $mysqli->prepare("INSERT INTO delivery (order_id, code, name, price)  VALUES (?, ?, ?, ?)");

        $stmt->bind_param("sssd", $order_id, $delivery_code, $delivery_name, $delivery_price);

        if (!$stmt->execute()) {
            echo json_encode(["success" => false]);
            throw new Exception($mysqli->error);
            die();
        }

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

        echo json_encode(["success" => true]);
    } else {
        echo json_encode(["success" => false]);
    }
} else {
    echo json_encode(["success" => false]);
}
