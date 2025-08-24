<?php
session_start(); // เริ่มต้น session เพื่อจัดการการเข้าสู่ระบบ

// ตรวจสอบว่าผู้ใช้เข้าสู่ระบบแล้วหรือไม่
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>หน้าหลัก - Online Shop</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-LN+7fdVzj6u52u30Kp6M/trliBMCMKTyK833zpbD+pXdCLuTusPj697FH4R/5mcr" crossorigin="anonymous">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css" rel="stylesheet">
    <style>
        body {
            background: #51b5e0;
            background: radial-gradient(circle, rgba(81, 181, 224, 1) 0%, rgba(87, 199, 133, 1) 50%, rgba(237, 221, 83, 1) 100%);
            min-height: 100vh;
        }
        .hero-section {
            padding: 4rem 0;
        }
        .card {
            transition: transform 0.3s;
        }
        .card:hover {
            transform: translateY(-5px);
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
                <i class="bi bi-shop"></i> Online Shop
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav me-auto">
                    <li class="nav-item">
                        <a class="nav-link active" href="index.php">
                            <i class="bi bi-house"></i> หน้าหลัก
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="#">
                            <i class="bi bi-grid"></i> สินค้า
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="#">
                            <i class="bi bi-cart"></i> ตรวจสอบออร์เดอร์
                        </a>
                    </li>
                </ul>
                <ul class="navbar-nav">
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown">
                            <i class="bi bi-person-circle"></i> <?= htmlspecialchars($_SESSION['username']) ?>
                        </a>
                        <ul class="dropdown-menu">
                            <li><span class="dropdown-item-text">บทบาท: <?= htmlspecialchars($_SESSION['role']) ?></span></li>
                            <li><hr class="dropdown-divider"></li>
                            <?php if($_SESSION['role'] === 'admin'): ?>
                            <li><a class="dropdown-item" href="admin/index.php">
                                <i class="bi bi-gear"></i> จัดการระบบ
                            </a></li>
                            <li><hr class="dropdown-divider"></li>
                            <?php endif; ?>
                            <li><a class="dropdown-item text-danger" href="logout.php">
                                <i class="bi bi-box-arrow-right"></i> ออกจากระบบ
                            </a></li>
                        </ul>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <!-- Hero Section -->
    <section class="hero-section">
        <div class="container">
            <div class="row justify-content-center">
                <div class="col-md-8">
                    <div class="card shadow-lg">
                        <div class="card-body text-center p-5">
                            <h1 class="display-4 text-primary mb-3">
                                <i class="bi bi-shop-window"></i> ยินดีต้อนรับ
                            </h1>
                            <p class="lead text-muted mb-4">
                                สวัสดี <strong><?= htmlspecialchars($_SESSION['username']) ?></strong>! 
                                ที่เข้าใช้งานเว็บไซต์ของเรา
                            </p>
                            <div class="badge bg-primary fs-6 mb-3">
                                บทบาท: <?= htmlspecialchars($_SESSION['role']) ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Quick Actions -->
    <section class="pb-5">
        <div class="container">
            <div class="row">
                <div class="col-12">
                    <h2 class="text-center text-white mb-4">เมนูการใช้งาน</h2>
                </div>
            </div>
            <div class="row g-4">
                <div class="col-md-4">
                    <div class="card shadow h-100">
                        <div class="card-body text-center">
                            <div class="text-primary mb-3">
                                <i class="bi bi-grid display-4"></i>
                            </div>
                            <h5 class="card-title">สินค้าทั้งหมด</h5>
                            <p class="card-text">ดูสินค้าทั้งหมดในร้านค้า</p>
                            <a href="#" class="btn btn-primary">
                                <i class="bi bi-arrow-right"></i> เข้าดู
                            </a>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card shadow h-100">
                        <div class="card-body text-center">
                            <div class="text-success mb-3">
                                <i class="bi bi-cart-check display-4"></i>
                            </div>
                            <h5 class="card-title">ออร์เดอร์ของฉัน</h5>
                            <p class="card-text">ตรวจสอบสถานะออร์เดอร์</p>
                            <a href="#" class="btn btn-success">
                                <i class="bi bi-arrow-right"></i> ตรวจสอบ
                            </a>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card shadow h-100">
                        <div class="card-body text-center">
                            <div class="text-info mb-3">
                                <i class="bi bi-person-gear display-4"></i>
                            </div>
                            <h5 class="card-title">โปรไฟล์</h5>
                            <p class="card-text">จัดการข้อมูลส่วนตัว</p>
                            <a href="#" class="btn btn-info">
                                <i class="bi bi-arrow-right"></i> แก้ไข
                            </a>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Admin Section -->
            <?php if($_SESSION['role'] === 'admin'): ?>
            <div class="row mt-4">
                <div class="col-12">
                    <div class="card shadow border-warning">
                        <div class="card-header bg-warning text-dark">
                            <h5 class="mb-0">
                                <i class="bi bi-shield-check"></i> เมนูผู้ดูแลระบบ
                            </h5>
                        </div>
                        <div class="card-body">
                            <div class="row g-3">
                                <div class="col-md-3">
                                    <a href="admin/index.php" class="btn btn-outline-warning w-100">
                                        <i class="bi bi-speedometer2"></i><br>
                                        Dashboard
                                    </a>
                                </div>
                                <div class="col-md-3">
                                    <a href="#" class="btn btn-outline-warning w-100">
                                        <i class="bi bi-box-seam"></i><br>
                                        จัดการสินค้า
                                    </a>
                                </div>
                                <div class="col-md-3">
                                    <a href="#" class="btn btn-outline-warning w-100">
                                        <i class="bi bi-people"></i><br>
                                        จัดการผู้ใช้
                                    </a>
                                </div>
                                <div class="col-md-3">
                                    <a href="#" class="btn btn-outline-warning w-100">
                                        <i class="bi bi-graph-up"></i><br>
                                        รายงาน
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <?php endif; ?>
        </div>
    </section>

    <!-- Footer -->
    <footer class="bg-dark text-white py-4 mt-auto">
        <div class="container text-center">
            <p class="mb-0">&copy; 664230034 Adisak Yongpanya 66/46</p>
        </div>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/js/bootstrap.bundle.min.js"
        integrity="sha384-ndDqU0Gzau9qJ1lfW4pNLlhNTkCfHzAVBReH9diLvGRem5+R9g2FzA8ZGN954O5Q" crossorigin="anonymous">
    </script>
</body>
</html>
