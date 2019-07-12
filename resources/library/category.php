<?php

class category
{

    public static function getAll($mysqli)
    {
        $stmt = $mysqli->prepare("SELECT id, title FROM categories");

        if (!$stmt->execute()) {
            echo json_encode(["success" => false]);
            die();
        }

        $stmt->bind_result($id, $title);

        $arr = [];
        while ($stmt->fetch()) {
            $arr[] = [
                "id" => $id,
                "title" => htmlspecialchars($title)
            ];
        }

        $stmt->close();

        return $arr;
    }

    public static function get($mysqli, $category_id)
    {
        $stmt = $mysqli->prepare("SELECT id, title FROM categories WHERE id = ?");

        $stmt->bind_param("s", $category_id);

        if (!$stmt->execute()) {
            echo json_encode(["success" => false]);
            die();
        }

        $stmt->bind_result($id, $title);

        $arr = [];
        if ($stmt->fetch()) {
            $arr = [
                "id" => $id,
                "title" => htmlspecialchars($title)
            ];
        }

        $stmt->close();

        return $arr;
    }

    public static function edit($mysqli, $title, $id)
    {
        $stmt = $mysqli->prepare("UPDATE categories SET title=? WHERE id = ?");

        $stmt->bind_param("ss", $title, $id);

        if (!$stmt->execute()) {
            echo json_encode(["success" => false]);
            die();
        }

        $stmt->close();
    }
}
