<?php
// ✅ Only start session if it's not already started
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// ✅ Only declare the class if it doesn't already exist
if (!class_exists('DBConn')) {
    class DBConn {
        private $serverhost = "localhost";
        private $username = "root";
        private $password = "";
        private $database = "wmsfinal";
        public $conn;

        public function __construct()
        {
            $this->conn = new mysqli($this->serverhost, $this->username, $this->password, $this->database);

            if ($this->conn->error) {
                die("Connection error: " . $this->conn->error);
            }
        }
    }
}
?>