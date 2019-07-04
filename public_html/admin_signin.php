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

$email = $_POST['email'];
$password = $_POST['password'];

// $date = date('Y-m-d');
// $addr = $_SERVER['REMOTE_ADDR'];

// $stmt = $mysqli->prepare("SELECT ID FROM LoginAttempts where date = ? and addr = ?");
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

$stmt = $mysqli->prepare("SELECT id, password FROM admin_users where email = ?");

$stmt->bind_param("s", $email);

if (!$stmt->execute()) {
    echo json_encode(["success" => false]);
    die();
}

$stmt->bind_result($id, $hash_password);

if ($stmt->fetch()) {
    $stmt->close();
    // check password
    if (password_verify($password, $hash_password)) {
        $_SESSION["admin_id"] = $id;
        echo json_encode(["success" => true]);
    } else {
        echo json_encode(["success" => false]);

        // // store LoginAttempt in database
        // $stmt2 = $mysqli->prepare("INSERT INTO LoginAttempts (date, addr) VALUES (?, ?)");

        // if (!($stmt2)) {
        //     echo "Prepare failed: (" . $mysqli->errno . ") " . $mysqli->error;
        // }
        // $stmt2->bind_param("ss", $date, $addr);

        // if (!$stmt2->execute()) {
        //     echo json_encode(["success" => false]);
        //     die();
        // }
    }
} else {
    $stmt->close();
    echo json_encode(["success" => false]);
}
