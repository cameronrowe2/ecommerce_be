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

$category_id = $_POST['category_id'];
$search_term = $_POST['search_term'];
$page_num = $_POST['page_num'];
$num_products = $_POST['num_products'];

$offset = intval($page_num) * intval($num_products);

$total_products = null;

if ($category_id != null) {
  // get count on products
  $stmt = $mysqli->prepare("SELECT count(*) FROM products p, categories c WHERE p.category_id = c.id AND p.category_id = ? AND (p.title LIKE ? OR p.description LIKE ?)");

  if ($search_term != null) {
    $search_string = "%$search_term%";
    $stmt->bind_param("iss", $category_id, $search_string, $search_string);
  } else {
    $any_string = "%%";
    $stmt->bind_param("iss", $category_id, $any_string, $any_string);
  }

  if (!$stmt->execute()) {
    echo json_encode(["success" => false]);
    die();
  }

  $stmt->bind_result($total_products);
  $stmt->fetch();
  $stmt->close();

  // start getting products per category
  $stmt = $mysqli->prepare("SELECT p.id, p.title, p.description, p.price, p.price_deal, p.imageUrl, p.sku, p.weight, p.height, p.width, p.length, p.category_id, c.title as category_title FROM products p, categories c WHERE category_id = ? AND p.category_id = c.id AND (p.title LIKE ? OR p.description LIKE ?) LIMIT $offset,$num_products");

  if ($search_term != null) {
    $search_string = "%$search_term%";
    $stmt->bind_param("iss", $category_id, $search_string, $search_string);
  } else {
    $any_string = "%%";
    $stmt->bind_param("iss", $category_id, $any_string, $any_string);
  }
} else {

  // get count on products
  $stmt = $mysqli->prepare("SELECT count(*) FROM products p, categories c WHERE p.category_id = c.id AND (p.title LIKE ? OR p.description LIKE ?)");

  if ($search_term != null) {
    $search_string = "%$search_term%";
    $stmt->bind_param("ss", $search_string, $search_string);
  } else {
    $any_string = "%%";
    $stmt->bind_param("ss", $any_string, $any_string);
  }

  if (!$stmt->execute()) {
    echo json_encode(["success" => false]);
    die();
  }

  $stmt->bind_result($total_products);
  $stmt->fetch();
  $stmt->close();

  // get products
  $stmt = $mysqli->prepare("SELECT p.id, p.title, p.description, p.price, p.price_deal, p.imageUrl, p.sku, p.weight, p.height, p.width, p.length, p.category_id, c.title as category_title FROM products p, categories c WHERE p.category_id = c.id AND (p.title LIKE ? OR p.description LIKE ?) LIMIT $offset,$num_products");

  if ($search_term != null) {
    $search_string = "%$search_term%";
    $stmt->bind_param("ss", $search_string, $search_string);
  } else {
    $any_string = "%%";
    $stmt->bind_param("ss", $any_string, $any_string);
  }
}
if (!$stmt->execute()) {
  echo json_encode(["success" => false]);
  die();
}

$stmt->bind_result($id, $title, $description, $price, $price_deal, $imageUrl, $sku, $weight, $height, $width, $length, $category_id, $category_title);

$arr = [];
while ($stmt->fetch()) {
  $arr[] = [
    "id" => $id,
    "title" => htmlspecialchars($title),
    "description" => htmlspecialchars($description),
    "price" => htmlspecialchars($price),
    "price_deal" => $price_deal,
    "imageUrl" => htmlspecialchars($imageUrl),
    "sku" => htmlspecialchars($sku),
    "weight" => $weight,
    "height" => $height,
    "width" => $width,
    "length" => $length,
    "category_id" => $category_id,
    "category_title" => $category_title
  ];
}

$stmt->close();

$total_pages = ceil($total_products / $num_products);

echo json_encode(["success" => true, "data" => [
  "products" => $arr,
  "total_pages" => $total_pages
]]);
