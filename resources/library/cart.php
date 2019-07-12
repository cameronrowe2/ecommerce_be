<?php

class cart
{

    public static function get($mysqli, $cart_id)
    {
        // get all cart_items
        $stmt = $mysqli->prepare("
            SELECT 
                p.id, p.title, p.imageUrl, p.price, p.sku, ci.quantity, p.weight, p.height, p.width, p.length
            FROM 
                cart_items ci, products p 
            WHERE 
                ci.cart_id = ? AND p.id = ci.product_id");

        $stmt->bind_param("s", $cart_id);

        if (!$stmt->execute()) {
            echo json_encode(["success" => false]);
            die();
        }

        $stmt->bind_result($p_id, $p_title, $p_imageUrl, $p_price, $p_sku, $quantity, $p_weight, $p_height, $p_width, $p_length);

        $cart_items = [];
        $total_price = 0;
        while ($stmt->fetch()) {
            $cart_items[] = [
                "product_id" => $p_id,
                "product_title" => $p_title,
                "product_imageUrl" => $p_imageUrl,
                "product_price" => $p_price,
                "product_sku" => $p_sku,
                "quantity" => $quantity,
                "product_weight" => $p_weight,
                "product_height" => $p_height,
                "product_width" => $p_width,
                "product_length" => $p_length
            ];
            $total_price +=  $quantity * $p_price;
        }
        $stmt->close();

        return [
            "cart_items" => $cart_items,
            "total_price" => $total_price
        ];
    }

    public static function getCartId($mysqli, $user_id)
    {
        $stmt = $mysqli->prepare("SELECT id FROM carts WHERE user_id = ?");

        $stmt->bind_param("s", $user_id);

        if (!$stmt->execute()) {
            echo json_encode(["success" => false]);
            die();
        }

        $stmt->bind_result($cart_id);
        $stmt->fetch();
        $stmt->close();

        return $cart_id;
    }

    public static function getProductQuantity($mysqli, $user_id, $product_id)
    {
        $stmt = $mysqli->prepare("SELECT quantity FROM carts, cart_items WHERE carts.user_id = ? AND carts.id = cart_items.cart_id AND cart_items.product_id = ?");

        $stmt->bind_param("ss", $user_id, $product_id);

        if (!$stmt->execute()) {
            echo json_encode(["success" => false]);
            die();
        }

        $stmt->bind_result($quantity);
        $stmt->fetch();
        $stmt->close();

        return $quantity;
    }

    public static function removeProduct($mysqli, $cart_id, $product_id)
    {
        $stmt = $mysqli->prepare("DELETE FROM cart_items WHERE cart_id = ? AND product_id = ?");

        $stmt->bind_param("ss", $cart_id, $product_id);

        if (!$stmt->execute()) {
            echo json_encode(["success" => false]);
            die();
        }

        $stmt->close();
    }

    public static function decrementProduct($mysqli, $quantity, $product_id, $cart_id)
    {
        $stmt = $mysqli->prepare("UPDATE cart_items SET quantity=? WHERE product_id = ? AND cart_id = ?");

        $stmt->bind_param("iss", $quantity, $product_id, $cart_id);

        if (!$stmt->execute()) {
            echo json_encode(["success" => false]);
            die();
        }
        $stmt->close();
    }

    public static function incrementProduct($mysqli, $quantity, $product_id, $cart_id)
    {
        $stmt = $mysqli->prepare("UPDATE cart_items SET quantity=? WHERE product_id = ? AND cart_id = ?");

        $stmt->bind_param("iss", $quantity, $product_id, $cart_id);

        if (!$stmt->execute()) {
            echo json_encode(["success" => false]);
            die();
        }
        $stmt->close();
    }

    public static function addProduct($mysqli, $cart_id, $product_id, $quantity)
    {
        $stmt = $mysqli->prepare("INSERT INTO cart_items (cart_id, product_id, quantity)  VALUES (?, ?, ?)");

        $stmt->bind_param("ssi", $cart_id, $product_id, $quantity);

        if (!$stmt->execute()) {
            echo json_encode(["success" => false]);
            die();
        }

        $stmt->close();
    }

    public static function getFromCookie($mysqli, $cookie)
    {
        if (sizeof($cookie) != 0) {

            // get all product ids
            $product_ids = array_keys($cookie);
            $product_ids_str = join(",", $product_ids);

            // get all cart_items
            $stmt = $mysqli->prepare("
            SELECT 
                p.id, p.title, p.imageUrl, p.price, p.sku, p.weight, p.height, p.width, p.length
            FROM 
                products p 
            WHERE 
                p.id IN ($product_ids_str)");

            if (!$stmt->execute()) {
                echo json_encode(["success" => false]);
                die();
            }

            $stmt->bind_result($p_id, $p_title, $p_imageUrl, $p_price, $p_sku, $p_weight, $p_height, $p_width, $p_length);

            $cart_items = [];
            $total_price = 0;
            while ($stmt->fetch()) {
                $quantity = $cookie[$p_id]['quantity'];

                $cart_items[] = [
                    "product_id" => $p_id,
                    "product_title" => $p_title,
                    "product_imageUrl" => $p_imageUrl,
                    "product_price" => $p_price,
                    "product_sku" => $p_sku,
                    "quantity" => $quantity,
                    "product_weight" => $p_weight,
                    "product_height" => $p_height,
                    "product_width" => $p_width,
                    "product_length" => $p_length
                ];
                $total_price +=  $quantity * $p_price;
            }
            $stmt->close();

            return [
                "cart_items" => $cart_items,
                "total_price" => $total_price
            ];
        } else {
            return [
                "cart_items" => [],
                "total_price" => 0
            ];
        }
    }
}
