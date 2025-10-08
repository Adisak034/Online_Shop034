<?php
session_start();
require_once 'session_timeout.php';
require 'config.php';
require 'function.php';
// ตรวจสอบการล็อกอิน
if (!isset($_SESSION['user_id'])) { // TODO: ใส่ session ของ user
    header("Location: login.php"); // TODO: หน้า login
    exit;
}
$user_id = $_SESSION['user_id']; // TODO: กำหนด user_id

// -----------------------------
// ดึงคำสั่งซื้อ ของผู้ใช้
// -----------------------------
$stmt = $conn->prepare("SELECT * FROM orders WHERE user_id = ? ORDER BY order_date DESC");
$stmt->execute([$user_id]);
$orders = $stmt->fetchAll(PDO::FETCH_ASSOC);



?>
<html lang="th">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ประวัติการสั่งซื้อ - BoboIT Shop</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css" rel="stylesheet">
    <style>
        body {
            background-color: #f8f9fa;
        }
        .accordion-button:not(.collapsed) {
            color: #0c63e4;
            background-color: #e7f1ff;
        }
        .order-item-img {
            width: 60px;
            height: 60px;
            object-fit: contain;
            background-color: #f8f9fa;
            border: 1px solid #dee2e6;
            border-radius: 5px;
        }
    </style>
</head>

<body>
    <?php require_once 'navbar.php'; ?>

    <div class="container mt-4">
        <h2 class="mb-4"><i class="bi bi-receipt"></i> ประวัติการสั่งซื้อ</h2>

        <?php if (isset($_GET['success'])) : ?>
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <i class="bi bi-check-circle-fill"></i> ทำรายการสั่งซื้อของคุณเรียบร้อยแล้ว!
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        <?php endif; ?>

        <?php if (empty($orders)) : ?>
            <div class="card text-center shadow-sm">
                <div class="card-body p-5">
                    <i class="bi bi-bag-x display-1 text-muted"></i>
                    <h4 class="mt-3">คุณยังไม่มีคำสั่งซื้อ</h4>
                    <p class="text-muted">คำสั่งซื้อทั้งหมดของคุณจะแสดงที่นี่</p>
                    <a href="products.php" class="btn btn-primary mt-2">
                        <i class="bi bi-arrow-left"></i> เริ่มเลือกซื้อสินค้า
                    </a>
                </div>
            </div>
        <?php else : ?>
            <div class="accordion" id="ordersAccordion">
                <?php foreach ($orders as $index => $order) : ?>
                    <div class="accordion-item">
                        <h2 class="accordion-header" id="heading<?= $order['order_id'] ?>">
                            <button class="accordion-button <?= $index > 0 ? 'collapsed' : '' ?>" type="button" data-bs-toggle="collapse" data-bs-target="#collapse<?= $order['order_id'] ?>" aria-expanded="<?= $index === 0 ? 'true' : 'false' ?>" aria-controls="collapse<?= $order['order_id'] ?>">
                                <div class="d-flex w-100 justify-content-between">
                                    <span><strong>คำสั่งซื้อ #<?= $order['order_id'] ?></strong></span>
                                    <span class="me-3">วันที่: <?= date('d/m/Y', strtotime($order['order_date'])) ?></span>
                                    <span class="badge bg-primary"><?= ucfirst($order['status']) ?></span>
                                </div>
                            </button>
                        </h2>
                        <div id="collapse<?= $order['order_id'] ?>" class="accordion-collapse collapse <?= $index === 0 ? 'show' : '' ?>" aria-labelledby="heading<?= $order['order_id'] ?>" data-bs-parent="#ordersAccordion">
                            <div class="accordion-body">
                                <div class="row">
                                    <div class="col-md-8">
                                        <h5>รายการสินค้า</h5>
                                        <ul class="list-group list-group-flush">
                                            <?php foreach (getOrderItems($conn, $order['order_id']) as $item) : ?>
                                                <li class="list-group-item d-flex align-items-center">
                                                    <img src="<?= !empty($item['image']) ? 'product_images/' . rawurlencode($item['image']) : 'product_images/no-image.jpg' ?>" class="order-item-img me-3" alt="<?= htmlspecialchars($item['product_name']) ?>">
                                                    <div class="flex-grow-1">
                                                        <?= htmlspecialchars($item['product_name']) ?>
                                                        <div class="small text-muted">จำนวน: <?= $item['quantity'] ?> x <?= number_format($item['price'], 2) ?></div>
                                                    </div>
                                                    <span class="fw-bold"><?= number_format($item['price'] * $item['quantity'], 2) ?></span>
                                                </li>
                                            <?php endforeach; ?>
                                        </ul>
                                    </div>
                                    <div class="col-md-4 mt-4 mt-md-0">
                                        <h5>สรุปและที่อยู่จัดส่ง</h5>
                                        <p><strong>ยอดรวม:</strong> <span class="float-end"><?= number_format($order['total_amount'], 2) ?> บาท</span></p>
                                        <?php $shipping = getShippingInfo($conn, $order['order_id']); ?>
                                        <?php if ($shipping) : ?>
                                            <hr>
                                            <p class="mb-1"><strong>ที่อยู่:</strong> <?= htmlspecialchars($shipping['address']) ?>, <?= htmlspecialchars($shipping['city']) ?> <?= $shipping['postal_code'] ?></p>
                                            <p class="mb-1"><strong>โทร:</strong> <?= htmlspecialchars($shipping['phone']) ?></p>
                                            <p><strong>สถานะจัดส่ง:</strong> <span class="badge bg-info"><?= ucfirst($shipping['shipping_status']) ?></span></p>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>

    <?php require_once 'footer.php'; ?>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>
