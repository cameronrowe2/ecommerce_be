<?php
session_start();

header("Access-Control-Allow-Origin: http://localhost:3000");
header('Access-Control-Allow-Credentials: true');
header("Access-Control-Allow-Headers: Content-Type");

include_once '../resources/library/database.php';
include_once '../resources/library/cart.php';
include_once '../resources/library/email.php';
include_once '../resources/library/stripe-php-6.40.0/init.php';

$database = new Database();
$mysqli = $database->getConnection();

$rest_json = file_get_contents("php://input");
$_POST = json_decode($rest_json, true);

$token_id = $_POST['token_id'];
$delivery_code = $_POST['delivery_code'];
$delivery_name = $_POST['delivery_name'];
$delivery_price = $_POST['delivery_price'];
$delivery_address = $_POST['delivery_address'];
$delivery_suburb = $_POST['delivery_suburb'];
$delivery_postcode = $_POST['delivery_postcode'];
$delivery_country = $_POST['delivery_country'];
$delivery_email = $_POST['delivery_email'];


if ($_SESSION['id']) {

    // get cart id
    $cart_id = cart::getCartId($mysqli, $_SESSION['id']);

    if (isset($cart_id)) {

        // get all products and total price
        $cart_data = cart::get($mysqli, $cart_id);

        // charge card

        // Set your secret key: remember to change this to your live secret key in production
        // See your keys here: https://dashboard.stripe.com/account/apikeys
        \Stripe\Stripe::setApiKey('sk_test_ZRDHNfDsMdElDPD5UsW6fkZi00W7hFhOnM');

        $total_price = $cart_data['total_price'] + floatval($delivery_price);
        $charge_price = $total_price * 100;

        $charge = \Stripe\Charge::create(['amount' => $charge_price, 'currency' => 'aud', 'source' => $token_id]);

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
        $stmt = $mysqli->prepare("INSERT INTO delivery (order_id, code, name, price, address, suburb, postcode, country)  VALUES (?, ?, ?, ?, ?, ?, ?, ?)");

        $stmt->bind_param("sssdssss", $order_id, $delivery_code, $delivery_name, $delivery_price, $delivery_address, $delivery_suburb, $delivery_postcode, $delivery_country);

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

        // send order email
        email::sendOrderEmail($delivery_email, $order_id, $cart_data, $delivery_price, $total_price);

        echo json_encode(["success" => true]);
    } else {
        echo json_encode(["success" => false]);
    }
} else {
    // echo json_encode(["success" => false]);
    // get cart
    $cookie = isset($_COOKIE['cart_items_cookie']) ? $_COOKIE['cart_items_cookie'] : "";
    $cookie = stripslashes($cookie);
    $saved_cart_items = json_decode($cookie, true);

    // if $saved_cart_items is null, prevent null error
    if (!$saved_cart_items) {
        $saved_cart_items = array();
    }

    $cart_data = cart::getFromCookie($mysqli, $saved_cart_items);

    // Set your secret key: remember to change this to your live secret key in production
    // See your keys here: https://dashboard.stripe.com/account/apikeys
    \Stripe\Stripe::setApiKey('sk_test_ZRDHNfDsMdElDPD5UsW6fkZi00W7hFhOnM');

    $total_price = $cart_data['total_price'] + floatval($delivery_price);
    $charge_price = $total_price * 100;

    $charge = \Stripe\Charge::create(['amount' => $charge_price, 'currency' => 'aud', 'source' => $token_id]);

    // echo json_encode($charge);
    // return;

    // create order
    $stmt = $mysqli->prepare("INSERT INTO orders (remote_addr, stripe_id, user_id)  VALUES (?, ?, ?)");

    $no_user_id = 0;

    $stmt->bind_param("sss", $_SERVER['REMOTE_ADDR'], $charge['id'], $no_user_id);

    if (!$stmt->execute()) {
        echo json_encode(["success" => false]);
        throw new Exception($mysqli->error);
        die();
    }
    $order_id = $stmt->insert_id;

    $stmt->close();

    // create delivery
    $stmt = $mysqli->prepare("INSERT INTO delivery (order_id, code, name, price, address, suburb, postcode, country)  VALUES (?, ?, ?, ?, ?, ?, ?, ?)");

    $stmt->bind_param("sssdssss", $order_id, $delivery_code, $delivery_name, $delivery_price, $delivery_address, $delivery_suburb, $delivery_postcode, $delivery_country);

    if (!$stmt->execute()) {
        echo json_encode(["success" => false]);
        throw new Exception($mysqli->error);
        die();
    }

    $stmt->close();

    // create order_items
    foreach ($saved_cart_items as $key => $cart_item) {
        $stmt = $mysqli->prepare("INSERT INTO order_items (order_id, product_id, quantity)  VALUES (?, ?, ?)");

        $stmt->bind_param("ssi", $order_id, $key, $cart_item['quantity']);

        if (!$stmt->execute()) {
            echo json_encode(["success" => false]);
            die();
        }
        $stmt->close();
    }

    // clear cart
    $saved_cart_items = array();

    // put item to cookie
    $json = json_encode($saved_cart_items, true);
    setcookie("cart_items_cookie", $json, time() + (86400 * 30), '/'); // 86400 = 1 day
    $_COOKIE['cart_items_cookie'] = $json;

    // send order email
    email::sendOrderEmail($delivery_email, $order_id, $cart_data, $delivery_price, $total_price);

    echo json_encode(["success" => true]);
}
