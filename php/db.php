<?php
session_start();

class DBConn {
    private $serverhost = "localhost";
    private $username = "root";
    private $password = "";
    private $database = "wmsfinal";
    public $conn;

    public function __construct()
    {
        $this->conn = new mysqli($this->serverhost, $this->username, $this->password, $this->database);

        if($this->conn->error) {
        die("Connection error." . $this->conn->error);
        }
    }
}
?>