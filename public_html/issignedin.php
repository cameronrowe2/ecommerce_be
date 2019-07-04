<?php
session_start();

header("Access-Control-Allow-Origin: http://localhost:3000");
header('Access-Control-Allow-Credentials: true');

// var_dump($_SESSION);
// return;

if (isset($_SESSION['id'])) {
    echo json_encode(["success" => true]);
} else {
    echo json_encode(["success" => false]);
}
