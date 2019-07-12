<?php
session_start();

header("Access-Control-Allow-Origin: http://localhost:3000");
header('Access-Control-Allow-Credentials: true');
header("Access-Control-Allow-Headers: Content-Type");

include_once '../resources/library/database.php';
include_once '../resources/library/email.php';

$database = new Database();
$mysqli = $database->getConnection();

$rest_json = file_get_contents("php://input");
$_POST = json_decode($rest_json, true);

$email = $_POST['email'];
$name = $_POST['name'];
$password = $_POST['password'];
$password2 = $_POST['password2'];

// $date = date('Y-m-d');
// $addr = $_SERVER['REMOTE_ADDR'];

// $stmt = $mysqli->prepare("SELECT ID FROM CreateAccountAttempts where date = ? and addr = ?");
// $stmt->bind_param("ss", $date, $addr);

// if (!$stmt->execute()) {
//     echo json_encode(["success" => false]);
//     die();
// }

// $stmt->bind_result($ID_res);

// $count = 0;
// while ($stmt->fetch()) {
//     $count++;
// }

// if ($count > 2) {
//     echo json_encode(["success" => false]);
//     exit();
// }


// existing account check
$stmt = $mysqli->prepare("SELECT ID FROM users where email = ?");

$stmt->bind_param("s", $email);

if (!$stmt->execute()) {
    echo json_encode(["success" => false]);
    die();
}

$stmt->bind_result($ID);

$count = 0;
if ($stmt->fetch()) {
    $count++;
}

if ($count > 0) {
    echo json_encode(["success" => false]);
    exit();
}

// check if passwords are the same
if ($password != $password2) {
    echo json_encode(["success" => false]);
    exit();
}

// // store CreateAccountAttempt in database
// $stmt2 = $mysqli->prepare("INSERT INTO CreateAccountAttempts (date, addr) VALUES (?, ?)");

// if (!($stmt2)) {
//     echo "Prepare failed: (" . $mysqli->errno . ") " . $mysqli->error;
// }
// $stmt2->bind_param("ss", $date, $addr);

// if (!$stmt2->execute()) {
//     echo json_encode(["success" => false]);
//     die();
// }

$email_hash = md5(rand(0, 1000));

// hash password
$hash = password_hash($password, PASSWORD_DEFAULT);

$stmt = $mysqli->prepare("INSERT INTO users (email, password, name, email_hash)  VALUES (?, ?, ?, ?)");

$stmt->bind_param("ssss", $email, $hash, $name, $email_hash);

if (!$stmt->execute()) {
    echo json_encode(["success" => false]);
    die();
}
$user_id = $stmt->insert_id;

$stmt->close();

if (!email::sendVerificationEmail($email, $email_hash)) {
    echo json_encode(["success" => false]);
}

// create cart
$stmt = $mysqli->prepare("INSERT INTO carts (user_id)  VALUES (?)");

$stmt->bind_param("s", $user_id);

if (!$stmt->execute()) {
    echo json_encode(["success" => false]);
    die();
}

$cart_id = $stmt->insert_id;

$stmt->close();

// move cookie cart to db cart
$cookie = isset($_COOKIE['cart_items_cookie']) ? $_COOKIE['cart_items_cookie'] : "";
$cookie = stripslashes($cookie);
$saved_cart_items = json_decode($cookie, true);

// if $saved_cart_items is null, prevent null error
if (!$saved_cart_items) {
    $saved_cart_items = array();
}

foreach ($saved_cart_items as $product_id => $value) {

    // add row
    $stmt = $mysqli->prepare("INSERT INTO cart_items (cart_id, product_id, quantity)  VALUES (?, ?, ?)");

    $stmt->bind_param("ssi", $cart_id, $product_id, $value['quantity']);

    if (!$stmt->execute()) {
        echo json_encode(["success" => false]);
        die();
    }

    $stmt->close();
}

// clear cookie
$json = json_encode([], true);
setcookie("cart_items_cookie", $json, time() + (86400 * 30), '/'); // 86400 = 1 day
$_COOKIE['cart_items_cookie'] = $json;

// create wishlist
$stmt = $mysqli->prepare("INSERT INTO wishlists (user_id)  VALUES (?)");

$stmt->bind_param("s", $user_id);

if (!$stmt->execute()) {
    echo json_encode(["success" => false]);
    die();
}

$stmt->close();

echo json_encode(["success" => true]);
