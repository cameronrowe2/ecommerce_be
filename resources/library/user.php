<?php
class user
{
    // get the database connection
    public static function get($mysqli, $user_id)
    {

        $stmt = $mysqli->prepare("SELECT name, email, email_validated FROM users WHERE id = ?");

        $stmt->bind_param("s", $user_id);

        if (!$stmt->execute()) {
            echo json_encode(["success" => false]);
            die();
        }

        $stmt->bind_result($name, $email, $email_validated);

        $arr = [];
        if ($stmt->fetch()) {
            $arr = [
                "name" => $name,
                "email" => $email,
                "email_validated" => $email_validated
            ];
        }

        $stmt->close();

        return $arr;
    }
}
