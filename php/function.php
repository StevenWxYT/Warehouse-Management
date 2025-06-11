<?php
include "db.php";

class DBFunc {
    private $conn;

    public function __construct($db) {
        $this->conn = $db;
    }

    public function registerUser($username, $password, $role) {
        $pwd = password_hash($password, PASSWORD_DEFAULT);
        
        $stmt = $this->conn->prepare("INSERT INTO users (username, password, role) VALUES (?, ?, ?)");
        if ($stmt) {
            $stmt->bind_param('sss', $username, $pwd, $role);
            if ($stmt->execute()) {
                $stmt->close();
                header("Location: login.php");
                exit();
            }
            $stmt->close();
        }
    }

    public function loginUser($username, $password) {
        $stmt = $this->conn->prepare("SELECT * FROM users WHERE username = ?");
        $stmt->bind_param('s', $username);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result && $result->num_rows == 1) {
            $user = $result->fetch_assoc();
            if (password_verify($password, $user['password'])) {
                session_start();
                $_SESSION['username'] = $user['username'];
                $_SESSION['role'] = $user['role'];

                if ($user['role'] === 'admin') {
                    header('Location: dashboard.php');
                } elseif ($user['role'] === 'sellsman') {
                    header('Location: update.php');
                } elseif ($user['role'] === 'buyer') {
                    header('Location: retail.php');
                } else {
                    echo "Unknown role.";
                }
                exit();
            } else {
                echo "Invalid password.";
            }
        } else {
            echo "User not found.";
        }

        $stmt->close();
    }

    public function logoutUser() {
        session_start();
        session_unset();
        session_destroy();
        header("Location: login.php");
        exit();
    }

    public function insertWarehouse($id, $image, $sku, $rack, $zone, $name, $dimensions, $color, $weight, $quantity, $description, $price) {
        $stmt = $this->conn->prepare("INSERT INTO warehouse (id, image, sku, rack, zone, name, dimensions, colour, weight, quantity, description, price) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("issssssssiss", $id, $image, $sku, $rack, $zone, $name, $dimensions, $color, $weight, $quantity, $description, $price);
        $stmt->execute();
    }

    public function viewWarehouse($id) {
        $stmt = $this->conn->prepare("SELECT * FROM warehouse WHERE id = ?");
        $stmt->bind_param("i", $id);
        $stmt->execute();
    }

    public function updateWarehouse($id, $image, $sku, $rack, $zone, $name, $dimensions, $color, $weight, $quantity, $description, $price) {
        $stmt = $this->conn->prepare("UPDATE warehouse SET image, sku, rack, zone, name, dimensions, colour, weight, quantity, description, price WHERE id = ?");
        $stmt->bind_param("issssssssiss", $id, $image, $sku, $rack, $zone, $name, $dimensions, $color, $weight, $quantity, $description, $price);
        $stmt->execute();
    }

    public function deleteWarehouse($id) {
        $stmt = $this->conn->prepare("DELETE FROM warehouse WHERE id = ?");
        $stmt->bind_param("i", $id);
        $stmt->execute();
    }

    public function getWarehouse($id) {}
}
?>