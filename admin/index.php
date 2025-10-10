<?php

require_once '../config.php';
require_once 'auth_admin.php';
require_once '../session_timeout.php';

// ดึงข้อมูลสถิติ
$total_products = $conn->query("SELECT COUNT(*) FROM products")->fetchColumn();
$total_orders = $conn->query("SELECT COUNT(*) FROM orders")->fetchColumn();
$total_users = $conn->query("SELECT COUNT(*) FROM users WHERE role = 'member'")->fetchColumn();
$total_sales = $conn->query("SELECT SUM(total_amount) FROM orders WHERE status = 'completed'")->fetchColumn();
$total_sales = $total_sales ?? 0; // กำหนดค่าเริ่มต้นเป็น 0 หากไม่มีข้อมูล

// ตั้งค่า locale เป็นไทยเพื่อแสดงผลสกุลเงิน
if (class_exists('NumberFormatter')) {
    $formatter = new NumberFormatter('th_TH', NumberFormatter::CURRENCY);
    $formatted_sales = $formatter->formatCurrency($total_sales ?? 0, 'THB');
} else {
    $formatted_sales = number_format($total_sales, 2) . ' บาท';
}
?>
<!DOCTYPE html>
<html lang="th">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>แผงควบคุมผู้ดูแลระบบ</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-LN+7fdVzj6u52u30Kp6M/trliBMCMKTyK833zpbD+pXdCLuTusPj697FH4R/5mcr" crossorigin="anonymous">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css" rel="stylesheet">
    <link rel="stylesheet" href="admin_main.css">
</head>

<body>
    <!-- Navigation Bar -->
    <?php require_once 'navbar_admin.php'; ?>

    <div class="container mt-5">
        <!-- Welcome Section -->
        <div class="row mb-5">
            <div class="col-12">
                <div class="card admin-card shadow-lg">
                    <div class="card-body text-center">
                        <h1 class="display-5 text-primary mb-3 fw-bold">
                            <i class="bi bi-speedometer2"></i> Dashboard 
                        </h1>
                        <p class="lead text-muted">
                            ยินดีต้อนรับเข้าสู่ระบบจัดการ, <strong><?= htmlspecialchars($_SESSION['username']) ?></strong>
                        </p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Admin Menu Cards -->
        <div class="row g-4">
            <div class="col-lg-3 col-md-6">
                <div class="card admin-card shadow-lg border-0">
                    <div class="card-body">
                        <div class="admin-icon text-primary">
                            <i class="bi bi-box-seam"></i>
                        </div>
                        <h5 class="card-title">จัดการสินค้า</h5>
                        <p class="card-text text-muted">เพิ่ม แก้ไข ลบสินค้า</p>
                        <a href="products.php" class="btn btn-primary btn-lg w-100">
                            <i class="bi bi-arrow-right-circle"></i> เข้าจัดการ
                        </a>
                    </div>
                </div>
            </div>

            <div class="col-lg-3 col-md-6">
                <div class="card admin-card shadow-lg border-0">
                    <div class="card-body">
                        <div class="admin-icon text-success">
                            <i class="bi bi-cart-check"></i>
                        </div>
                        <h5 class="card-title">จัดการคำสั่งซื้อ</h5>
                        <p class="card-text text-muted">ตรวจสอบและจัดการออร์เดอร์</p>
                        <a href="orders.php" class="btn btn-success btn-lg w-100">
                            <i class="bi bi-arrow-right-circle"></i> เข้าจัดการ
                        </a>
                    </div>
                </div>
            </div>

            <div class="col-lg-3 col-md-6">
                <div class="card admin-card shadow-lg border-0">
                    <div class="card-body">
                        <div class="admin-icon text-info">
                            <i class="bi bi-people"></i>
                        </div>
                        <h5 class="card-title">จัดการสมาชิก</h5>
                        <p class="card-text text-muted">จัดการข้อมูลผู้ใช้</p>
                        <a href="users.php" class="btn btn-info btn-lg w-100">
                            <i class="bi bi-arrow-right-circle"></i> เข้าจัดการ
                        </a>
                    </div>
                </div>
            </div>

            <div class="col-lg-3 col-md-6">
                <div class="card admin-card shadow-lg border-0">
                    <div class="card-body">
                        <div class="admin-icon text-dark">
                            <i class="bi bi-tags"></i>
                        </div>
                        <h5 class="card-title">จัดการหมวดหมู่</h5>
                        <p class="card-text text-muted">จัดการหมวดหมู่สินค้า</p>
                        <a href="category.php" class="btn btn-dark btn-lg w-100">
                            <i class="bi bi-arrow-right-circle"></i> เข้าจัดการ
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <!-- Quick Stats Section -->
        <div class="row mt-5">
            <div class="col-12">
                <div class="card admin-card shadow-lg">
                    <div class="card-header bg-info text-white">
                        <h5 class="mb-0">
                            <i class="bi bi-graph-up"></i> สถิติระบบ
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="row text-center">
                            <div class="col-md-3 col-6 mb-3">
                                <a href="products.php" class="text-decoration-none text-dark">
                                    <div class="border-end">
                                        <h3 class="text-primary">
                                            <i class="bi bi-box"></i> <?= $total_products ?>
                                        </h3>
                                        <h4>สินค้าทั้งหมด</h4>
                                        <p class="text-muted">รายการ</p>
                                    </div>
                                </a>
                            </div>
                            <div class="col-md-3 col-6 mb-3">
                                <a href="orders.php" class="text-decoration-none text-dark">
                                    <div class="border-end">
                                        <h3 class="text-success">
                                            <i class="bi bi-cart3"></i> <?= $total_orders ?>
                                        </h3>
                                        <h4>คำสั่งซื้อ</h4>
                                        <p class="text-muted">ออร์เดอร์</p>
                                    </div>
                                </a>
                            </div>
                            <div class="col-md-3 col-6 mb-3">
                                <a href="users.php" class="text-decoration-none text-dark">
                                    <div class="border-end">
                                        <h3 class="text-info">
                                            <i class="bi bi-person-check"></i> <?= $total_users ?>
                                        </h3>
                                        <h4>สมาชิก</h4>
                                        <p class="text-muted">คน</p>
                                    </div>
                                </a>
                            </div>
                            <div class="col-md-3 col-6 mb-3">
                                <a href="orders.php" class="text-decoration-none text-dark">
                                    <h3 class="text-dark">
                                        <i class="bi bi-cash-stack"></i>
                                    </h3>
                                    <h4>ยอดขายรวม</h4>
                                    <p class="text-muted"><?= $formatted_sales ?></p>
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Footer -->
    <?php require_once 'footer_admin.php'; ?>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/js/bootstrap.bundle.min.js"
        integrity="sha384-ndDqU0Gzau9qJ1lfW4pNLlhNTkCfHzAVBReH9diLvGRem5+R9g2FzA8ZGN954O5Q" crossorigin="anonymous">
    </script>
</body>

</html>