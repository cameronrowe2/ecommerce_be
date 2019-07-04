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

    $name = $_POST['name'];
    $email = $_POST['email'];
    $password = $_POST['password'];

    // existing account check
    $stmt = $mysqli->prepare("SELECT id FROM admin_users where email = ?");

    $stmt->bind_param("s", $email);

    if (!$stmt->execute()) {
        echo json_encode(["success" => false]);
        die();
    }

    $stmt->bind_result($id);

    $count = 0;
    if ($stmt->fetch()) {
        $count++;
    }

    if ($count > 0) {
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

    // hash password
    $hash = password_hash($password, PASSWORD_DEFAULT);

    $stmt = $mysqli->prepare("INSERT INTO admin_users (email, password, name)  VALUES (?, ?, ?)");

    $stmt->bind_param("sss", $email, $hash, $name);

    if (!$stmt->execute()) {
        echo json_encode(["success" => false]);
        die();
    }
    $admin_user_id = $stmt->insert_id;

    $stmt->close();

    echo json_encode(["success" => true, "data" => [
        "id" => $admin_user_id,
        "name" => $name,
        "email" => $email
    ]]);
} else {
    echo json_encode(["success" => false]);
}
