<?php
session_start();
require_once 'session_timeout.php';
require 'config.php';

// ตั้งค่า header ให้เป็น JSON
header('Content-Type: application/json');

// ตรวจสอบว่าผู้ใช้ล็อกอินหรือยัง
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['status' => 'error', 'message' => 'กรุณาเข้าสู่ระบบเพื่อใช้งานรายการโปรด', 'action' => 'redirect']);
    exit;
}

// ตรวจสอบว่ามีการส่ง product_id มาหรือไม่
if (($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['product_id']))) {
    $product_id = (int)$_POST['product_id'];
} elseif (isset($_GET['product_id'])) {
    $product_id = (int)$_GET['product_id'];
} else {
    echo json_encode(['status' => 'error', 'message' => 'ข้อมูลไม่ถูกต้อง']);
    exit;
}

$user_id = $_SESSION['user_id'];

try {
    // ตรวจสอบว่ามีสินค้านี้ใน Wishlist แล้วหรือยัง
    $stmt = $conn->prepare("SELECT 1 FROM wishlist WHERE user_id = ? AND product_id = ?");
    $stmt->execute([$user_id, $product_id]);
    $exists = $stmt->fetchColumn();

    if ($exists) {
        // ถ้ามีอยู่แล้ว -> ลบออก
        $deleteStmt = $conn->prepare("DELETE FROM wishlist WHERE user_id = ? AND product_id = ?");
        $deleteStmt->execute([$user_id, $product_id]);
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') { // ถ้ามาจาก GET (หน้า profile)
            header("Location: profile.php");
            exit();
        }
        echo json_encode(['status' => 'removed']); // ถ้ามาจาก POST (AJAX)
    } else {
        // ถ้ายังไม่มี -> เพิ่มเข้าไป
        $insertStmt = $conn->prepare("INSERT INTO wishlist (user_id, product_id) VALUES (?, ?)");
        $insertStmt->execute([$user_id, $product_id]);
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header("Location: profile.php");
            exit();
        }
        echo json_encode(['status' => 'added']);
    }

} catch (PDOException $e) {
    // กรณีเกิดข้อผิดพลาดกับฐานข้อมูล
    echo json_encode(['status' => 'error', 'message' => 'เกิดข้อผิดพลาดกับฐานข้อมูล: ' . $e->getMessage()]);
}

exit;
?>