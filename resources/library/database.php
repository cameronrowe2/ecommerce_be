<?php
class Database
{

    // specify your own database credentials
    private $host = "127.0.0.1";
    private $db_name = "ecommerce";
    private $username = "root";
    private $password = "test1234";
    public $conn;

    // get the database connection
    public function getConnection()
    {

        $this->conn = null;


        $mysqli = new mysqli($this->host, $this->username, $this->password, $this->db_name);
        if ($mysqli->connect_errno) {
            echo "Failed to connect to MySQL: (" . $mysqli->connect_errno . ") " . $mysqli->connect_error;
        }

        return $mysqli;
    }
}
