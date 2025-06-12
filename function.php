<?php
include "db.php";

class DBFunc
{
    public $conn;

    public function __construct($db)
    {
        $this->conn = $db;
    }


    // User login/registration
    public function registerUser($username, $password, $role)
    {
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

    public function loginUser($username, $password)
    {
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

    // public function loginUser($username, $password)
    // {
    //     $stmt = $this->conn->prepare("SELECT * FROM users WHERE username = ?");
    //     $stmt->bind_param('s', $username);
    //     $stmt->execute();
    //     $result = $stmt->get_result();

    //     if ($result && $result->num_rows == 1) {
    //         $user = $result->fetch_assoc();
    //         if (password_verify($password, $user['password'])) {
    //             session_start();
    //             $_SESSION['username'] = $user['username'];
    //             $_SESSION['role'] = $user['role'];

    //             // All users are redirected to dashboard.php
    //             header('Location: dashboard.php');
    //             exit();
    //         } else {
    //             echo "Invalid password.";
    //         }
    //     } else {
    //         echo "User not found.";
    //     }

    //     $stmt->close();
    // }

    public function logoutUser()
    {
        session_start();
        session_unset();
        session_destroy();
        header("Location: login.php");
        exit();
    }


    // Warehouse function

    public function insertWarehouse($id, $sku, $rack, $zone, $quantity)
    {
        $imagePath = $this->handleImageUpload();

        if ($imagePath) {
            $stmt = $this->conn->prepare("INSERT INTO warehouse (id, image, sku, rack, zone, quantity) VALUES (?, ?, ?, ?, ?, ?)");
            $stmt->bind_param("issssi", $id, $imagePath, $sku, $rack, $zone, $quantity);
            if ($stmt->execute()) {
                echo "Insert successful.";
            } else {
                echo "Insert failed: " . $stmt->error;
            }
            $stmt->close();
        } else {
            echo "Image upload failed.";
        }
    }

    public function updateWarehouse($id, $sku, $rack, $zone, $quantity)
    {
        $imagePath = $this->handleImageUpload();

        if ($imagePath) {
            $stmt = $this->conn->prepare("UPDATE warehouse SET image = ?, sku = ?, rack = ?, zone = ?, quantity = ? WHERE id = ?");
            $stmt->bind_param("sssssi", $imagePath, $sku, $rack, $zone, $quantity, $id);
            if ($stmt->execute()) {
                echo "Update successful.";
            } else {
                echo "Update failed: " . $stmt->error;
            }
            $stmt->close();
        } else {
            echo "Image upload failed.";
        }
    }

    private function handleImageUpload()
    {
        if (!isset($_FILES['image']) || $_FILES['image']['error'] !== 0) {
            return false;
        }

        $targetDir = "uploads/";
        if (!is_dir($targetDir)) {
            mkdir($targetDir, 0755, true);
        }

        $imageName = basename($_FILES['image']['name']);
        $targetPath = $targetDir . uniqid() . "_" . $imageName;

        if (move_uploaded_file($_FILES['image']['tmp_name'], $targetPath)) {
            return $targetPath;
        } else {
            return false;
        }
    }


    // public function insertWarehouse($id, $image, $sku, $rack, $zone, $name, $dimensions, $color, $weight, $quantity, $description, $price)
    // {
    //     $stmt = $this->conn->prepare("INSERT INTO warehouse (id, image, sku, rack, zone, name, dimensions, colour, weight, quantity, description, price) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
    //     $stmt->bind_param("issssssssisd", $id, $image, $sku, $rack, $zone, $name, $dimensions, $color, $weight, $quantity, $description, $price);
    //     $stmt->execute();
    //     $stmt->close();
    // }

    public function viewWarehouse($id)
    {
        $stmt = $this->conn->prepare("SELECT * FROM warehouse WHERE id = ?");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $result = $stmt->get_result();
        $data = $result->fetch_assoc(); // or fetch_all(MYSQLI_ASSOC) for multiple
        $stmt->close();
        return $data;
    }

    // public function updateWarehouse($id, $image, $sku, $rack, $zone, $name, $dimensions, $color, $weight, $quantity, $description, $price)
    // {
    //     $stmt = $this->conn->prepare("UPDATE warehouse SET image = ?, sku = ?, rack = ?, zone = ?, name = ?, dimensions = ?, colour = ?, weight = ?, quantity = ?, description = ?, price = ? WHERE id = ?");
    //     $stmt->bind_param("sssssssdisdi", $image, $sku, $rack, $zone, $name, $dimensions, $color, $weight, $quantity, $description, $price, $id);
    //     $stmt->execute();
    //     $stmt->close();
    // }

    public function deleteWarehouse($id)
    {
        $stmt = $this->conn->prepare("DELETE FROM warehouse WHERE id = ?");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $stmt->close();
    }

    public function getTop10BestSelling()
    {
        $stmt = $this->conn->prepare("
        SELECT w.id, w.name, w.sku, w.image, SUM(s.quantity_sold) AS total_sold
        FROM warehouse w
        JOIN sales s ON w.id = s.warehouse_id
        GROUP BY w.id
        ORDER BY total_sold DESC
        LIMIT 10
    ");
        $stmt->execute();
        $result = $stmt->get_result();
        $top10 = $result->fetch_all(MYSQLI_ASSOC);
        $stmt->close();
        return $top10;
    }

    // public function getLowStockItems($threshold = 10)
    // {
    //     $stmt = $this->conn->prepare("SELECT * FROM warehouse WHERE quantity <= ?");
    //     $stmt->bind_param("i", $threshold);
    //     $stmt->execute();
    //     $result = $stmt->get_result();
    //     $items = $result->fetch_all(MYSQLI_ASSOC);
    //     $stmt->close();
    //     return $items;
    // }

    // public function getItemsByZone($zone)
    // {
    //     $stmt = $this->conn->prepare("SELECT * FROM warehouse WHERE zone = ?");
    //     $stmt->bind_param("s", $zone);
    //     $stmt->execute();
    //     $result = $stmt->get_result();
    //     $items = $result->fetch_all(MYSQLI_ASSOC);
    //     $stmt->close();
    //     return $items;
    // }

    // public function getRecentSales($limit = 10)
    // {
    //     $stmt = $this->conn->prepare("
    //     SELECT w.id, w.name, s.sale_date, s.quantity_sold
    //     FROM sales s
    //     JOIN warehouse w ON s.warehouse_id = w.id
    //     ORDER BY s.sale_date DESC
    //     LIMIT ?
    // ");
    //     $stmt->bind_param("i", $limit);
    //     $stmt->execute();
    //     $result = $stmt->get_result();
    //     $recent = $result->fetch_all(MYSQLI_ASSOC);
    //     $stmt->close();
    //     return $recent;
    // }
}
