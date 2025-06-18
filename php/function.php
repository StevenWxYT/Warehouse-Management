<?php
include "db.php";

class DBFunc {
    private $conn;

    public function __construct($db) {
        $this->conn = $db;
    }

    public function userExists($username) {
        $stmt = $this->conn->prepare("SELECT id FROM users WHERE username = ?");
        $stmt->bind_param("s", $username);
        $stmt->execute();
        $stmt->store_result();
        $exists = $stmt->num_rows > 0;
        $stmt->close();
        return $exists;
    }

    public function registerUser($username, $password, $role) {
        $hashedPwd = password_hash($password, PASSWORD_DEFAULT);
        $stmt = $this->conn->prepare("INSERT INTO users (username, password, role) VALUES (?, ?, ?)");
        $stmt->bind_param("sss", $username, $hashedPwd, $role);
        $success = $stmt->execute();
        $stmt->close();
        return $success;
    }

    public function loginUser($username, $password) {
        $stmt = $this->conn->prepare("SELECT * FROM users WHERE username = ?");
        $stmt->bind_param("s", $username);
        $stmt->execute();
        $result = $stmt->get_result();
    
        if ($result && $result->num_rows === 1) {
            $user = $result->fetch_assoc();
    
            if (password_verify($password, $user['password'])) {
                // session_start() 应该在调用 loginUser 之前调用，避免重复启动session
                $_SESSION['username'] = $user['username'];
                $_SESSION['role'] = $user['role'];
                $stmt->close();
                return true;
            }
        }
        $stmt->close();
        return false;
    }
    

    public function logoutUser() {
        session_start();
        session_unset();
        session_destroy();
        header("Location: login.php");
        exit();
    }

    public function insertWarehouse($id, $image, $sku, $rack, $zone, $quantity) {
        $stmt = $this->conn->prepare("INSERT INTO warehouse (id, image, sku, rack, zone, quantity) VALUES (?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("issssi", $id, $image, $sku, $rack, $zone, $quantity);
        return $stmt->execute();
    }

    public function viewWarehouse($id) {
        $stmt = $this->conn->prepare("SELECT * FROM warehouse WHERE id = ?");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $result = $stmt->get_result();
        return $result->fetch_assoc();
    }

    public function updateWarehouse($id, $image, $sku, $rack, $zone, $quantity) {
        $stmt = $this->conn->prepare("UPDATE warehouse SET image=?, sku=?, rack=?, zone=?, quantity=? WHERE id=?");
        $stmt->bind_param("ssssii", $image, $sku, $rack, $zone, $quantity, $id);
        return $stmt->execute();
    }

    public function deleteWarehouse($id) {
        $stmt = $this->conn->prepare("DELETE FROM warehouse WHERE id = ?");
        $stmt->bind_param("i", $id);
        return $stmt->execute();
    }

    public function getAllWarehouse() {
        $sql = "SELECT * FROM warehouse ORDER BY id DESC";
        $result = $this->conn->query($sql);
        if ($result) {
            return $result->fetch_all(MYSQLI_ASSOC);
        }
        return [];
    }

    // ✅ 修改后的 Top10 Best Selling 产品查询（只返回现有字段）
    public function getTop10BestSelling() {
        $sql = "SELECT id, image, sku, rack, zone, quantity FROM warehouse ORDER BY quantity DESC LIMIT 10";
        $result = $this->conn->query($sql);
    
        $topSelling = [];
    
        if ($result && $result->num_rows > 0) {
            while ($row = $result->fetch_assoc()) {
                $topSelling[] = $row;
            }
        }
        return $topSelling;
    }    

    public function getOrderHistory() {
        $sql = "SELECT * FROM order_history ORDER BY ordered_date DESC";
        $result = $this->conn->query($sql);
    
        $orders = [];
        if ($result && $result->num_rows > 0) {
            while ($row = $result->fetch_assoc()) {
                $orders[] = $row;
            }
        }
        return $orders;
    }
       
}
?>
