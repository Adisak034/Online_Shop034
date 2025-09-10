<?php
session_start();
require_once '../config.php';
require_once 'auth_admin.php';
?>
<!DOCTYPE html>
<html lang="th">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>แผงควบคุมผู้ดูแลระบบ</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-LN+7fdVzj6u52u30Kp6M/trliBMCMKTyK833zpbD+pXdCLuTusPj697FH4R/5mcr" crossorigin="anonymous">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css" rel="stylesheet">
    <style>
        body {
            background: white;
            min-height: 100vh;
        }
        .card {
            transition: transform 0.3s, box-shadow 0.3s;
        }
        .card:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 25px rgba(0,0,0,0.15);
        }
        .admin-card {
            border: none;
            border-radius: 15px;
        }
        .admin-card .card-body {
            padding: 2rem;
            text-align: center;
        }
        .admin-icon {
            font-size: 3rem;
            margin-bottom: 1rem;
        }
        .navbar-brand {
            font-weight: bold;
        }
    </style>
</head>

<body>
    <!-- Navigation Bar -->
    <nav class="navbar navbar-expand-lg navbar-dark bg-primary shadow">
        <div class="container">
            <a class="navbar-brand" href="#">
                <i class="bi bi-shield-check"></i> ระบบผู้ดูแลระบบ
            </a>
            <div class="navbar-nav ms-auto">
                <div class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown">
                        <i class="bi bi-person-circle"></i> <?= htmlspecialchars($_SESSION['username']) ?>
                    </a>
                    <ul class="dropdown-menu">
                        <li><span class="dropdown-item-text">ผู้ดูแลระบบ</span></li>
                        <li><hr class="dropdown-divider"></li>
                        <li><a class="dropdown-item" href="../index.php">
                            <i class="bi bi-house"></i> กลับหน้าหลัก
                        </a></li>
                        <li><hr class="dropdown-divider"></li>
                        <li><a class="dropdown-item text-danger" href="../logout.php">
                            <i class="bi bi-box-arrow-right"></i> ออกจากระบบ
                        </a></li>
                    </ul>
                </div>
            </div>
        </div>
    </nav>

    <div class="container mt-5">
        <!-- Welcome Section -->
        <div class="row mb-5">
            <div class="col-12">
                <div class="card admin-card shadow-lg">
                    <div class="card-body">
                        <h1 class="display-5 text-primary mb-3">
                            <i class="bi bi-speedometer2"></i> Dashboard
                        </h1>
                        <p class="lead text-muted">
                            ยินดีต้อนรับเข้าสู่ระบบจัดการ, <strong><?= htmlspecialchars($_SESSION['username']) ?></strong>
                        </p>
                        <div class="badge bg-success fs-6">ผู้ดูแลระบบ</div>
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
                        <div class="admin-icon text-warning">
                            <i class="bi bi-people"></i>
                        </div>
                        <h5 class="card-title">จัดการสมาชิก</h5>
                        <p class="card-text text-muted">จัดการข้อมูลผู้ใช้</p>
                        <a href="users.php" class="btn btn-warning btn-lg w-100">
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
                        <a href="categories.php" class="btn btn-dark btn-lg w-100">
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
                            <div class="col-md-3">
                                <div class="border-end">
                                    <h3 class="text-primary">
                                        <i class="bi bi-box"></i>
                                    </h3>
                                    <h4>จำนวนสินค้า</h4>
                                    <p class="text-muted">ดูรายละเอียดเพิ่มเติม</p>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="border-end">
                                    <h3 class="text-success">
                                        <i class="bi bi-cart3"></i>
                                    </h3>
                                    <h4>คำสั่งซื้อ</h4>
                                    <p class="text-muted">ยอดขายทั้งหมด</p>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="border-end">
                                    <h3 class="text-warning">
                                        <i class="bi bi-person-check"></i>
                                    </h3>
                                    <h4>สมาชิก</h4>
                                    <p class="text-muted">ผู้ใช้ในระบบ</p>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <h3 class="text-info">
                                    <i class="bi bi-award"></i>
                                </h3>
                                <h4>รายงาน</h4>
                                <p class="text-muted">สรุปผลประกอบการ</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Footer -->
    <footer class="bg-dark text-white py-4 mt-5">
        <div class="container text-center">
            <p class="mb-0">&copy; Adisak Yongpanya 664230034 66/46</p>
        </div>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/js/bootstrap.bundle.min.js"
        integrity="sha384-ndDqU0Gzau9qJ1lfW4pNLlhNTkCfHzAVBReH9diLvGRem5+R9g2FzA8ZGN954O5Q" crossorigin="anonymous">
    </script>
</body>

</html>