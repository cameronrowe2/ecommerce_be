<?php

class admin_user
{

    public static function get($mysqli, $admin_user_id)
    {
        $stmt = $mysqli->prepare("SELECT id, name, email FROM admin_users WHERE id = ?");

        $stmt->bind_param("i", $admin_user_id);

        if (!$stmt->execute()) {
            echo json_encode(["success" => false]);
            die();
        }

        $stmt->bind_result($id, $name, $email);

        $arr = [];
        while ($stmt->fetch()) {
            $arr = [
                "id" => $id,
                "name" => htmlspecialchars($name),
                "email" => htmlspecialchars($email)
            ];
        }

        $stmt->close();

        return $arr;
    }
}
