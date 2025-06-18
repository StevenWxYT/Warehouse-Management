<?php
class DBConn {
    private $host = "localhost";
    private $user = "root";
    private $password = "";
    private $database = "wmsfinal";
    public $conn;

    public function __construct() {
        // 创建连接
        $this->conn = new mysqli($this->host, $this->user, $this->password, $this->database);

        // 检查连接是否成功
        if ($this->conn->connect_error) {
            die("Connection failed: " . $this->conn->connect_error);
        }

        // 设置字符集，避免中文乱码
        $this->conn->set_charset("utf8mb4");
    }

    // 可选的关闭连接函数
    public function close() {
        if ($this->conn) {
            $this->conn->close();
        }
    }
}
?>
