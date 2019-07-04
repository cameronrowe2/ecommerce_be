<?php
session_start();

header("Access-Control-Allow-Origin: http://localhost:3000");
header('Access-Control-Allow-Credentials: true');

unset($_SESSION['admin_id']);

echo json_encode(["success" => true]);
