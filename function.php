<?php
include "db.php";
require_once "phpqrcode/qrlib.php"; // Ensure this path is correct

class DBFunc
{
    public $conn;

    public function __construct($db)
    {
        $this->conn = $db;
    }

    // ===== User Auth =====
    public function registerUser($username, $password, $role)
    {
        $pwd = password_hash($password, PASSWORD_DEFAULT);
        $stmt = $this->conn->prepare("INSERT INTO users (username, password, role) VALUES (?, ?, ?)");
        if ($stmt) {
            $stmt->bind_param('sss', $username, $pwd, $role);
            if ($stmt->execute()) {
                $stmt->close();
                header("Location: index.php");
                exit();
            }
            $stmt->close();
        } else {
            echo "Database error: " . $this->conn->error;
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
                header('Location: dashboard.php');
                exit();
            } else {
                echo "Invalid password.";
            }
        } else {
            echo "User not found.";
        }

        $stmt->close();
    }

    public function logoutUser()
    {
        session_start();
        session_unset();
        session_destroy();
        header("Location: login.php");
        exit();
    }

    // ===== Warehouse Functions =====
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

    public function viewWarehouse($id)
    {
        $stmt = $this->conn->prepare("SELECT * FROM warehouse WHERE id = ?");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $result = $stmt->get_result();
        $data = $result->fetch_assoc();
        $stmt->close();
        return $data;
    }

    public function deleteWarehouse($id)
    {
        $stmt = $this->conn->prepare("DELETE FROM warehouse WHERE id = ?");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $stmt->close();
    }

    public function getAllStock()
    {
        $sql = "SELECT id, sku, category, zone, rack, quantity FROM warehouse";
        $result = $this->conn->query($sql);
        $stocks = [];

        if ($result && $result->num_rows > 0) {
            while ($row = $result->fetch_assoc()) {
                $stocks[] = $row;
            }
        }

        return $stocks;
    }

    public function updateStock($id, $sku, $category, $zone, $rack, $quantity)
    {
        $stmt = $this->conn->prepare("UPDATE warehouse SET sku=?, category=?, zone=?, rack=?, quantity=? WHERE id=?");
        if ($stmt) {
            $stmt->bind_param("ssssii", $sku, $category, $zone, $rack, $quantity, $id);
            $stmt->execute();
            $stmt->close();
        }
    }

    public function deleteStock($id)
    {
        $stmt = $this->conn->prepare("DELETE FROM warehouse WHERE id = ?");
        if ($stmt) {
            $stmt->bind_param("i", $id);
            $stmt->execute();
            $stmt->close();
        }
    }

    public function getTop10BestSelling()
    {
        $stmt = $this->conn->prepare("
            SELECT w.id, w.sku, w.image, SUM(s.quantity_sold) AS total_sold
            FROM warehouse w
            JOIN sales s ON w.id = s.warehouse_id
            GROUP BY w.id, w.sku, w.image
            ORDER BY total_sold DESC
            LIMIT 10
        ");
        $stmt->execute();
        $result = $stmt->get_result();

        $top10 = [];
        while ($row = $result->fetch_assoc()) {
            $row['name'] = $row['sku']; // fallback name
            $top10[] = $row;
        }

        $stmt->close();
        return $top10;
    }

    public function getAllCategories()
    {
        $sql = "SELECT * FROM categories";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute();
        return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    }

    public function insertCategory($name, $description)
    {
        $sql = "INSERT INTO categories (name, description) VALUES (?, ?)";
        $stmt = $this->conn->prepare($sql);
        return $stmt->execute([$name, $description]);
    }

    public function updateCategory($id, $name, $description)
    {
        $sql = "UPDATE categories SET name=?, description=? WHERE id=?";
        $stmt = $this->conn->prepare($sql);
        return $stmt->execute([$name, $description, $id]);
    }

    public function deleteCategory($id)
    {
        $sql = "DELETE FROM categories WHERE id=?";
        $stmt = $this->conn->prepare($sql);
        return $stmt->execute([$id]);
    }

    public function getOrderHistory()
    {
        $logs = [];
        $sql = "SELECT timestamp, user, action, sku FROM activity_logs ORDER BY timestamp DESC";
        $result = $this->conn->query($sql);

        if ($result && $result->num_rows > 0) {
            while ($row = $result->fetch_assoc()) {
                $logs[] = $row;
            }
        }

        return $logs;
    }

    public function logActivity($user, $action, $sku)
    {
        $stmt = $this->conn->prepare("INSERT INTO activity_logs (user, action, sku) VALUES (?, ?, ?)");
        $stmt->bind_param("sss", $user, $action, $sku);
        $stmt->execute();
        $stmt->close();
    }

    public function filterStock($category, $zone)
    {
        $query = "SELECT * FROM warehouse WHERE 1=1";
        $types = "";
        $params = [];

        if (!empty($category)) {
            $query .= " AND category = ?";
            $types .= "s";
            $params[] = $category;
        }

        if (!empty($zone)) {
            $query .= " AND zone = ?";
            $types .= "s";
            $params[] = $zone;
        }

        $stmt = $this->conn->prepare($query);
        if (!empty($params)) {
            $stmt->bind_param($types, ...$params);
        }

        $stmt->execute();
        $result = $stmt->get_result();

        $stocks = [];
        while ($row = $result->fetch_assoc()) {
            $stocks[] = $row;
        }

        return $stocks;
    }

    public function insertStock($sku, $category, $zone, $rack, $quantity, $imagePath)
    {
        $orderTime = date('Y-m-d H:i:s');
        $this->generateQRForSKU($sku);

        $stmt = $this->conn->prepare("INSERT INTO warehouse (sku, category, zone, rack, quantity, image, order_time) VALUES (?, ?, ?, ?, ?, ?, ?)");
        if (!$stmt) return false;

        $stmt->bind_param("ssssiss", $sku, $category, $zone, $rack, $quantity, $imagePath, $orderTime);

        if ($stmt->execute()) {
            $this->logActivity($_SESSION['username'] ?? 'system', 'Inserted stock', $sku);
            return true;
        }
        return false;
    }

    private function generateQRForSKU($sku)
    {
        $qrDir = 'qrcodes/';
        if (!is_dir($qrDir)) {
            mkdir($qrDir, 0755, true);
        }

        $filename = $qrDir . $sku . '.png';
        if (!file_exists($filename)) {
            QRcode::png($sku, $filename, QR_ECLEVEL_L, 4, 2);
        }
    }

    public function searchStock($keyword, $zone)
    {
        $query = "SELECT sku, category, zone, rack, quantity FROM warehouse WHERE 1=1";
        $params = [];
        $types = '';

        if (!empty($keyword)) {
            $query .= " AND (sku LIKE ? OR category LIKE ?)";
            $kw = '%' . $keyword . '%';
            $params[] = $kw;
            $params[] = $kw;
            $types .= 'ss';
        }

        if (!empty($zone)) {
            $query .= " AND zone = ?";
            $params[] = $zone;
            $types .= 's';
        }

        $stmt = $this->conn->prepare($query);
        if ($types && $stmt) {
            $stmt->bind_param($types, ...$params);
        }

        $stmt->execute();
        $result = $stmt->get_result();
        return $result->fetch_all(MYSQLI_ASSOC);
    }

    public function stockOut($sku, $deductQty)
    {
        $stmt = $this->conn->prepare("SELECT id, quantity FROM warehouse WHERE sku = ?");
        $stmt->bind_param("s", $sku);
        $stmt->execute();
        $result = $stmt->get_result();
        $item = $result->fetch_assoc();
        $stmt->close();

        if ($item && $item['quantity'] >= $deductQty) {
            $newQty = $item['quantity'] - $deductQty;
            $orderTime = date('Y-m-d H:i:s');

            $updateStmt = $this->conn->prepare("UPDATE warehouse SET quantity = ?, order_time = ? WHERE id = ?");
            $updateStmt->bind_param("isi", $newQty, $orderTime, $item['id']);
            $success = $updateStmt->execute();
            $updateStmt->close();

            if ($success) {
                $this->logActivity($_SESSION['username'] ?? 'system', "Stock Out (-$deductQty)", $sku);
                return ["success" => true, "message" => "Successfully deducted $deductQty units from SKU: $sku."];
            }
            return ["success" => false, "message" => "Failed to update stock."];
        }
        return ["success" => false, "message" => "Not enough stock or invalid SKU."];
    }
}
?>