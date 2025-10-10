
<?php   
require '../config.php';
require_once '../session_timeout.php';
require 'auth_admin.php';

// ตรวจสอบสิทธิ์ admin
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../login.php");
    exit;
}

// --- จัดการการลบคำสั่งซื้อ ---
if (isset($_GET['delete']) && is_numeric($_GET['delete'])) {
    $order_id_to_delete = (int)$_GET['delete'];

    try {
        $conn->beginTransaction();

        // 1. ลบจาก order_items
        $stmt_items = $conn->prepare("DELETE FROM order_items WHERE order_id = ?");
        $stmt_items->execute([$order_id_to_delete]);

        // 2. ลบจาก shipping
        $stmt_shipping = $conn->prepare("DELETE FROM shipping WHERE order_id = ?");
        $stmt_shipping->execute([$order_id_to_delete]);

        // 3. ลบจาก orders
        $stmt_order = $conn->prepare("DELETE FROM orders WHERE order_id = ?");
        $stmt_order->execute([$order_id_to_delete]);

        $conn->commit();
    } catch (Exception $e) {
        $conn->rollBack();
        // สามารถเพิ่มการแจ้งเตือนข้อผิดพลาดได้ที่นี่
    }
    header("Location: orders.php");
    exit;
}
// ดึงคำสั่งซื้อทั้งหมด
$stmt = $conn->query("
    SELECT o.*, u.username
    FROM orders o
    LEFT JOIN users u ON o.user_id = u.user_id
    ORDER BY o.order_date DESC
");
$orders = $stmt->fetchAll(PDO::FETCH_ASSOC);


require '../function.php';   // ดึงฟังก์ชันที่เก็บไว้

// อัปเดตสถานะคำสั่งซื้อ
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['update_status'])) {
        $stmt = $conn->prepare("UPDATE orders SET status = ? WHERE order_id = ?");
        $stmt->execute([$_POST['status'], $_POST['order_id']]);
        header("Location: orders.php");
        exit;
    }
    if (isset($_POST['update_shipping'])) {
        $stmt = $conn->prepare("UPDATE shipping SET shipping_status = ? WHERE shipping_id = ?");
        $stmt->execute([$_POST['shipping_status'], $_POST['shipping_id']]);
        header("Location: orders.php");
        exit;
    }
}
?>

<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <title>จัดการคำสั่งซื้อ</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css" rel="stylesheet">
    <link rel="stylesheet" href="admin_main.css">
</head>
<body>
    <?php require_once 'navbar_admin.php'; ?>

<div class="container mt-5">
    <!-- Welcome Section -->
    <div class="row mb-5">
        <div class="col-12">
            <div class="card admin-card shadow-lg">
                <div class="card-body text-center">
                    <h1 class="display-5 text-primary mb-3 fw-bold">
                        <i class="bi bi-cart-check"></i> จัดการคำสั่งซื้อ
                    </h1>
                    <p class="lead text-muted">
                        ตรวจสอบและอัปเดตสถานะคำสั่งซื้อทั้งหมดในระบบ
                    </p>
                </div>
            </div>
        </div>
    </div>

<div class="container">
    <div class="card admin-card shadow-lg">
        <div class="card-body">
            <div class="accordion" id="ordersAccordion">

<?php foreach ($orders as $index => $order): ?>

    <?php $shipping = getShippingInfo($conn, $order['order_id']); ?>

    <div class="accordion-item">
        <h2 class="accordion-header" id="heading<?= $index ?>">
            <?php
                $status_color = 'secondary';
                switch ($order['status']) {
                    case 'processing':
                        $status_color = 'info';
                        break;
                    case 'shipped':
                        $status_color = 'primary';
                        break;
                    case 'completed':
                        $status_color = 'success';
                        break;
                    case 'cancelled':
                        $status_color = 'danger';
                        break;
                }
            ?>
            <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapse<?= $index ?>" aria-expanded="false" aria-controls="collapse<?= $index ?>"> 
                คำสั่งซื้อ #<?= $order['order_id'] ?> | <?= htmlspecialchars($order['username'] ?? 'ผู้ใช้ถูกลบ') ?> | วันที่: <?= date('d/m/Y', strtotime($order['order_date'])) ?> | สถานะ: <span class="badge bg-<?= $status_color ?>"><?= ucfirst($order['status']) ?></span>
            </button> 
        </h2>
        <div id="collapse<?= $index ?>" class="accordion-collapse collapse" aria-labelledby="heading<?= $index ?>" data-bs-parent="#ordersAccordion">
            <div class="accordion-body">
                <div class="row g-4">
                    <!-- Order Items Column -->
                    <div class="col-md-7">
                        <h5><i class="bi bi-list-ul"></i> รายการสินค้า</h5>
                        <ul class="list-group mb-3">
                            <?php foreach (getOrderItems($conn, $order['order_id']) as $item): ?>
                                <li class="list-group-item d-flex justify-content-between align-items-center">
                                    <div class="d-flex align-items-center">
                                        <img src="../product_images/<?= htmlspecialchars($item['image'] ?? 'no-image.jpg') ?>" alt="" style="width: 50px; height: 50px; object-fit: contain;" class="me-3 rounded">
                                        <div>
                                            <?= htmlspecialchars($item['product_name']) ?>
                                            <br>
                                            <small class="text-muted"><?= $item['quantity'] ?> x <?= number_format($item['price'], 2) ?></small>
                                        </div>
                                    </div>
                                    <span class="fw-bold"><?= number_format($item['quantity'] * $item['price'], 2) ?></span>
                                </li>
                            <?php endforeach; ?>
                            <li class="list-group-item d-flex justify-content-between align-items-center bg-light">
                                <strong class="fs-5">ยอดรวม</strong>
                                <strong class="fs-5 text-primary"><?= number_format($order['total_amount'], 2) ?> บาท</strong>
                            </li>
                        </ul>
                    </div>

                    <!-- Shipping and Status Column -->
                    <div class="col-md-5">
                        <!-- Shipping Info -->
                        <?php if ($shipping): ?>
                            <div class="card mb-3">
                                <div class="card-header"><i class="bi bi-truck"></i> ข้อมูลจัดส่ง</div>
                                <div class="card-body">
                                    <p class="mb-1"><i class="bi bi-geo-alt-fill"></i> <strong>ที่อยู่:</strong> <?= htmlspecialchars($shipping['address']) ?>, <?= htmlspecialchars($shipping['city']) ?> <?= $shipping['postal_code'] ?></p>
                                    <p class="mb-0"><i class="bi bi-telephone-fill"></i> <strong>เบอร์โทร:</strong> <?= htmlspecialchars($shipping['phone']) ?></p>
                                </div>
                            </div>
                        <?php endif; ?>

                        <!-- Status Update -->
                        <div class="card">
                            <div class="card-header"><i class="bi bi-pencil-square"></i> อัปเดตสถานะ</div>
                            <div class="card-body">
                                <!-- Order Status -->
                                <form method="post" class="row g-2 align-items-center mb-3">
                                    <label class="col-sm-4 col-form-label">คำสั่งซื้อ:</label>
                                    <input type="hidden" name="order_id" value="<?= $order['order_id'] ?>">
                                    <div class="col-sm-8 d-flex gap-2">
                                        <select name="status" class="form-select form-select-sm">
                                            <?php $statuses = ['pending', 'processing', 'shipped', 'completed', 'cancelled']; ?>
                                            <?php foreach ($statuses as $status): ?>
                                                <option value="<?= $status ?>" <?= ($order['status'] === $status) ? 'selected' : '' ?>><?= ucfirst($status) ?></option>
                                            <?php endforeach; ?>
                                        </select>
                                        <button type="submit" name="update_status" class="btn btn-sm btn-primary"><i class="bi bi-check-lg"></i></button>
                                    </div>
                                </form>
                                <!-- Shipping Status -->
                                <?php if ($shipping): ?>
                                <form method="post" class="row g-2 align-items-center">
                                    <label class="col-sm-4 col-form-label">การจัดส่ง:</label>
                                    <input type="hidden" name="shipping_id" value="<?= $shipping['shipping_id'] ?>">
                                    <div class="col-sm-8 d-flex gap-2">
                                        <select name="shipping_status" class="form-select form-select-sm">
                                            <?php $s_statuses = ['not_shipped', 'shipped', 'delivered']; ?>
                                            <?php foreach ($s_statuses as $s): ?>
                                                <option value="<?= $s ?>" <?= ($shipping['shipping_status'] === $s) ? 'selected' : '' ?>><?= ucfirst($s) ?></option>
                                            <?php endforeach; ?>
                                        </select>
                                        <button type="submit" name="update_shipping" class="btn btn-sm btn-success"><i class="bi bi-check-lg"></i></button>
                                    </div>
                                </form>
                                <?php endif; ?>
                                <hr>
                                <div class="text-end">
                                    <button type="button" class="btn btn-danger delete-order-button" data-order-id="<?= $order['order_id'] ?>">
                                        <i class="bi bi-trash"></i> ลบคำสั่งซื้อนี้
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
<?php endforeach; ?>
</div>
        </div>
    </div>
</div>
</div>

    <!-- Footer -->
    <?php require_once 'footer_admin.php'; ?>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/js/bootstrap.bundle.min.js"></script>
    <!-- SweetAlert2 -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const deleteButtons = document.querySelectorAll('.delete-order-button');
            deleteButtons.forEach(button => {
                button.addEventListener('click', function() {
                    const orderId = this.dataset.orderId;

                    Swal.fire({
                        title: 'คุณแน่ใจหรือไม่?',
                        text: `คุณต้องการลบคำสั่งซื้อที่ #${orderId} ใช่หรือไม่?`,
                        icon: 'warning',
                        showCancelButton: true,
                        confirmButtonColor: '#d33',
                        cancelButtonColor: '#6c757d',
                        confirmButtonText: 'ลบ',
                        cancelButtonText: 'ยกเลิก'
                    }).then((result) => {
                        if (result.isConfirmed) {
                            window.location.href = `orders.php?delete=${orderId}`;
                        }
                    });
                });
            });
        });
    </script>
</body>
</html>