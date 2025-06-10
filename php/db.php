<?php
session_start();

class DBConn {
    private $serverhost = "localhost";
    private $username = "root";
    private $password = "";
    private $database = "wmsfinal";
    private $conn;

    private function __construct()
    {
        $this->conn = new mysqli($this->serverhost, $this->username, $this->password, $this->database);

        if($this->conn->error) {
        die("Connection error." . $this->conn->error);
        }
    }
}
?>