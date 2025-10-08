<?php
session_start();
require_once 'session_timeout.php';
require 'config.php';
// ตรวจสอบการล็อกอิน
if (!isset($_SESSION['user_id'])) { // TODO: ใส่ session ของ user
    header("Location: login.php"); // TODO: หน้า login
    exit;
}
$user_id = $_SESSION['user_id']; // TODO: กำหนด user_id

// ดึงรายการสินค้าในตะกร้า
$stmt = $conn->prepare("SELECT cart.cart_id, cart.quantity, cart.product_id, products.product_name, products.price, products.image
FROM cart
JOIN products ON cart.product_id = products.product_id
WHERE cart.user_id = ?");
$stmt->execute([$user_id]);
$items = $stmt->fetchAll(PDO::FETCH_ASSOC);

// -----------------------------
// คำนวณรำคำรวม
// -----------------------------
$total = 0;
foreach ($items as $item) {
    $total += $item['quantity'] * $item['price']; // TODO: quantity * price
}
// เมื่อลูกค้ากดยืนยันคำสั่งซื้อ (method POST)

$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $address = trim($_POST['address']); // TODO: ช่องกรอกที่อยู่
    $city = trim($_POST['city']); // TODO: ช่องกรอกจังหวัด
    $postal_code = trim($_POST['postal_code']); // TODO: ช่องกรอกรหัสไปรษณีย์
    $phone = trim($_POST['phone']); // TODO: ช่องกรอกเบอร์โทรศัพท์
    // ตรวจสอบการกรอกข้อมูล
    if (empty($address) || empty($city) || empty($postal_code) || empty($phone)) {
        $errors[] = "กรุณากรอกข้อมูลให้ครบถ้วน"; // TODO: ข้อความแจ้งเตือนกรอกไม่ครบ
    }
    if (empty($errors)) {
        // เริ่ม transaction
        $conn->beginTransaction();
        try {
            // บันทึกข้อมูลการสั่งซื้อ
            $stmt = $conn->prepare("INSERT INTO orders (user_id, total_amount, status) VALUES (?, ?, 'pending')");
            $stmt->execute([$user_id, $total]);
            $order_id = $conn->lastInsertId();
            // บันทึกข้อมูลรายการสินค้าใน order_items
            $stmtItem = $conn->prepare("INSERT INTO order_items (order_id, product_id, quantity, price) VALUES (?, ?, ?, ?)");
            foreach ($items as $item) {
                $stmtItem->execute([$order_id, $item['product_id'], $item['quantity'], $item['price']]);
            }
            // บันทึกข้อมูลการจัดส่ง
            $stmt = $conn->prepare("INSERT INTO shipping (order_id, address, city, postal_code, phone) VALUES (?, ?, ?, ?, ?)");
            $stmt->execute([$order_id, $address, $city, $postal_code, $phone]);
            // ลบตะกร้าสินค้า
            $stmt = $conn->prepare("DELETE FROM cart WHERE user_id = ?");
            $stmt->execute([$user_id]);
            // ยืนยันการบันทึก
            $conn->commit();
            header("Location: orders.php?success=1"); // TODO: หน้าสำหรับแสดงผลคำสั่งซื้อ
            exit;
        } catch (Exception $e) {
            $conn->rollBack();
            $errors[] = "เกิดข้อผิดพลาด: " . $e->getMessage();
        }
    }
}
?>
<!DOCTYPE html>
<html lang="th">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ชำระเงิน - BoboIT Shop</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css" rel="stylesheet">
    <style>
        body {
            background-color: #f8f9fa;
        }
        .order-summary-img {
            width: 50px;
            height: 50px;
            object-fit: contain;
            border-radius: 5px;
        }
    </style>
</head>

<body>
    <?php require_once 'navbar.php'; ?>

    <div class="container mt-4">
        <h2 class="mb-4"><i class="bi bi-wallet2"></i> ชำระเงิน</h2>

        <div class="row g-5">
            <!-- Shipping Form -->
            <div class="col-md-7 col-lg-8">
                <h4 class="mb-3">ข้อมูลการจัดส่ง</h4>
                <?php if (!empty($errors)) : ?>
                    <div class="alert alert-danger">
                        <?php foreach ($errors as $e) : ?>
                            <div><?= htmlspecialchars($e) ?></div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
                <form method="post" class="needs-validation" novalidate>
                    <div class="row g-3">
                        <div class="col-12">
                            <label for="address" class="form-label">ที่อยู่</label>
                            <input type="text" name="address" id="address" class="form-control" placeholder="บ้านเลขที่, ถนน, ตำบล/แขวง" required>
                            <div class="invalid-feedback">กรุณากรอกที่อยู่</div>
                        </div>

                        <div class="col-md-6">
                            <label for="city" class="form-label">จังหวัด</label>
                            <input type="text" name="city" id="city" class="form-control" required>
                            <div class="invalid-feedback">กรุณากรอกจังหวัด</div>
                        </div>

                        <div class="col-md-6">
                            <label for="postal_code" class="form-label">รหัสไปรษณีย์</label>
                            <input type="text" name="postal_code" id="postal_code" class="form-control" required>
                            <div class="invalid-feedback">กรุณากรอกรหัสไปรษณีย์</div>
                        </div>

                        <div class="col-12">
                            <label for="phone" class="form-label">เบอร์โทรศัพท์</label>
                            <input type="tel" name="phone" id="phone" class="form-control" placeholder="08xxxxxxxx" required>
                            <div class="invalid-feedback">กรุณากรอกเบอร์โทรศัพท์</div>
                        </div>
                    </div>

                    <hr class="my-4">

                    <div class="d-flex justify-content-between">
                        <a href="cart.php" class="btn btn-outline-secondary"><i class="bi bi-arrow-left"></i> กลับไปที่ตะกร้า</a>
                        <button class="btn btn-primary btn-lg" type="submit"><i class="bi bi-check-circle-fill"></i> ยืนยันการสั่งซื้อ</button>
                    </div>
                </form>
            </div>

            <!-- Order Summary -->
            <div class="col-md-5 col-lg-4 order-md-last">
                <h4 class="d-flex justify-content-between align-items-center mb-3">
                    <span class="text-primary">สรุปรายการ</span>
                    <span class="badge bg-primary rounded-pill"><?= count($items) ?></span>
                </h4>
                <ul class="list-group mb-3">
                    <?php foreach ($items as $item) : ?>
                        <li class="list-group-item d-flex justify-content-between lh-sm">
                            <div>
                                <h6 class="my-0"><?= htmlspecialchars($item['product_name']) ?></h6>
                                <small class="text-muted">จำนวน: <?= $item['quantity'] ?></small>
                            </div>
                            <span class="text-muted"><?= number_format($item['price'] * $item['quantity'], 2) ?></span>
                        </li>
                    <?php endforeach; ?>
                    <li class="list-group-item d-flex justify-content-between">
                        <span>ยอดรวม (บาท)</span>
                        <strong><?= number_format($total, 2) ?></strong>
                    </li>
                </ul>
            </div>
        </div>
    </div>

    <?php require_once 'footer.php'; ?>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>