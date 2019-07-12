<?php

class wishlist
{

    public static function get($mysqli, $wishlist_id)
    {
        // get all cart_items
        $stmt = $mysqli->prepare("
        SELECT 
            p.id, p.title, p.imageUrl, p.price, p.sku
        FROM 
            wishlist_items wi, products p 
        WHERE 
            wi.wishlist_id = ? AND p.id = wi.product_id");

        $stmt->bind_param("s", $wishlist_id);

        if (!$stmt->execute()) {
            echo json_encode(["success" => false]);
            die();
        }

        $stmt->bind_result($p_id, $p_title, $p_imageUrl, $p_price, $p_sku);

        $wishlist_items = [];
        while ($stmt->fetch()) {
            $wishlist_items[] = [
                "product_id" => $p_id,
                "product_title" => $p_title,
                "product_imageUrl" => $p_imageUrl,
                "product_price" => $p_price,
                "product_sku" => $p_sku
            ];
        }
        $stmt->close();

        return $wishlist_items;
    }

    public static function getWishlistId($mysqli, $user_id)
    {
        $stmt = $mysqli->prepare("SELECT id FROM wishlists WHERE user_id = ?");

        $stmt->bind_param("s", $user_id);

        if (!$stmt->execute()) {
            echo json_encode(["success" => false]);
            die();
        }

        $stmt->bind_result($wishlist_id);
        $stmt->fetch();
        $stmt->close();

        return $wishlist_id;
    }

    public static function removeProduct($mysqli, $wishlist_id, $product_id)
    {
        $stmt = $mysqli->prepare("DELETE FROM wishlist_items WHERE wishlist_id = ? AND product_id = ?");

        $stmt->bind_param("ss", $wishlist_id, $product_id);

        if (!$stmt->execute()) {
            echo json_encode(["success" => false]);
            die();
        }

        $stmt->close();
    }

    public static function addProduct($mysqli, $wishlist_id, $product_id)
    {
        $stmt = $mysqli->prepare("INSERT INTO wishlist_items (wishlist_id, product_id)  VALUES (?, ?)");

        $stmt->bind_param("ss", $wishlist_id, $product_id);

        if (!$stmt->execute()) {
            echo json_encode(["success" => false]);
            die();
        }

        $stmt->close();
    }

    public static function productExists($mysqli, $user_id, $product_id)
    {
        $stmt = $mysqli->prepare("SELECT wi.id FROM wishlists w, wishlist_items wi WHERE w.user_id = ? AND w.id = wi.wishlist_id AND wi.product_id = ?");

        $stmt->bind_param("ss", $user_id, $product_id);

        if (!$stmt->execute()) {
            echo json_encode(["success" => false]);
            die();
        }

        $stmt->bind_result($check);
        $stmt->fetch();
        $stmt->close();

        return $check;
    }
}
