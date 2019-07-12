<?php

class product
{

    public static function add($mysqli, $title, $description, $imageUrl, $price, $price_deal, $sku, $weight, $height, $width, $length, $category_id)
    {
        $stmt = $mysqli->prepare("INSERT INTO products (title, description, imageUrl, price, price_deal, sku, weight, height, width, length, category_id)  VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");

        $stmt->bind_param("sssddsdiiii", $title, $description, $imageUrl, $price, $price_deal, $sku, $weight, $height, $width, $length, $category_id);

        if (!$stmt->execute()) {
            echo json_encode(["success" => false]);
            die();
        }

        $product_id = $stmt->insert_id;

        $stmt->close();

        return $product_id;
    }
}
