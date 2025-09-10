<?php
session_start(); // เริ่มต้น session เพื่อจัดการการเข้าสู่ระบบ
require_once 'config.php';
//ดึงข้อมูลสินค้าทั้งหมดจากฐานข้อมูล
$stmt = $conn->query("SELECT p.*, c.category_name
FROM products p
LEFT JOIN categories c ON p.category_id = c.category_id
ORDER BY p.created_at DESC");
$products = $stmt->fetchAll(PDO::FETCH_ASSOC);

// ตรวจสอบว่าผู้ใช้เข้าสู่ระบบแล้วหรือไม่ป
$isLoggedIn = isset($_SESSION['user_id']);



?>
<!DOCTYPE html>
<html lang="th">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>หน้าหลัก - ร้านค้าออนไลน์</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-LN+7fdVzj6u52u30Kp6M/trliBMCMKTyK833zpbD+pXdCLuTusPj697FH4R/5mcr" crossorigin="anonymous">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css" rel="stylesheet">
    <style>
        body {
            background: white;
            min-height: 100vh;
        }
        .card {
            transition: transform 0.3s, box-shadow 0.3s;
            border: none;
            border-radius: 15px;
        }
        .card:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 25px rgba(0,0,0,0.15);
        }
        .navbar-brand {
            font-weight: bold;
        }
        .hero-section {
            background: rgba(255, 255, 255, 0.95);
            border-radius: 15px;
            backdrop-filter: blur(10px);
        }
        .product-card {
            height: 100%;
        }
        .price-tag {
            font-size: 1.2rem;
            font-weight: bold;
        }
    </style>
</head>

<body>
    <!-- Navigation Bar -->
    <nav class="navbar navbar-expand-lg navbar-dark bg-primary shadow">
        <div class="container">
            <a class="navbar-brand" href="index.php">
                <i class="bi bi-shop"></i> ร้านค้าออนไลน์
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
                </ul>
                <ul class="navbar-nav">
                    <?php if ($isLoggedIn): ?>
                        <li class="nav-item dropdown">
                            <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown">
                                <i class="bi bi-person-circle"></i> <?= htmlspecialchars($_SESSION['username']) ?>
                            </a>
                            <ul class="dropdown-menu">
                                <li><span class="dropdown-item-text">บทบาท: <?= htmlspecialchars($_SESSION['role']) ?></span></li>
                                <li><hr class="dropdown-divider"></li>
                                <li><a class="dropdown-item" href="profile.php">
                                    <i class="bi bi-person"></i> ข้อมูลส่วนตัว
                                </a></li>
                                <li><a class="dropdown-item" href="cart.php">
                                    <i class="bi bi-cart"></i> ตะกร้าสินค้า
                                </a></li>
                                <?php if($_SESSION['role'] === 'admin'): ?>
                                <li><hr class="dropdown-divider"></li>
                                <li><a class="dropdown-item" href="admin/index.php">
                                    <i class="bi bi-gear"></i> จัดการระบบ
                                </a></li>
                                <?php endif; ?>
                                <li><hr class="dropdown-divider"></li>
                                <li><a class="dropdown-item text-danger" href="logout.php">
                                    <i class="bi bi-box-arrow-right"></i> ออกจากระบบ
                                </a></li>
                            </ul>
                        </li>
                    <?php else: ?>
                        <li class="nav-item">
                            <a href="login.php" class="btn btn-success me-2">เข้าสู่ระบบ</a>
                        </li>
                        <li class="nav-item">
                            <a href="register.php" class="btn btn-primary">สมัครสมาชิก</a>
                        </li>
                    <?php endif; ?>
                </ul>
            </div>
        </div>
    </nav>

    <div class="container mt-4">
        <!-- Hero Section -->
        <div class="row mb-4">
            <div class="col-12">
                <div class="card hero-section shadow">
                    <div class="card-body text-center py-4">
                        <h1 class="display-5 text-primary mb-3">
                            <i class="bi bi-shop-window"></i> ยินดีต้อนรับสู่ร้านค้าออนไลน์
                        </h1>
                        <?php if ($isLoggedIn): ?>
                            <p class="lead text-muted">
                                สวัสดี <strong><?= htmlspecialchars($_SESSION['username']) ?></strong>! 
                                เลือกสินค้าที่คุณต้องการได้เลย
                            </p>
                        <?php else: ?>
                            <p class="lead text-muted mb-4">
                                สินค้าคุณภาพดี ราคาถูก จัดส่งรวดเร็ว
                            </p>
                            <div class="d-flex gap-2 justify-content-center">
                                <a href="login.php" class="btn btn-success">
                                    <i class="bi bi-box-arrow-in-right"></i> เข้าสู่ระบบ
                                </a>
                                <a href="register.php" class="btn btn-primary">
                                    <i class="bi bi-person-plus"></i> สมัครสมาชิก
                                </a>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>

        <!-- Products Section -->
        <div class="row mb-4">
            <div class="col-12">
                <h2 class="text-white text-center mb-4">
                    <i class="bi bi-bag-fill"></i> รายการสินค้า
                </h2>
            </div>
        </div>

        <!-- Products Grid -->
        <?php if (empty($products)): ?>
            <div class="row">
                <div class="col-12">
                    <div class="card shadow">
                        <div class="card-body text-center py-5">
                            <i class="bi bi-inbox display-1 text-muted"></i>
                            <h3 class="text-muted mt-3">ยังไม่มีสินค้าในระบบ</h3>
                            <p class="text-muted">กรุณารอสินค้าใหม่ หรือติดต่อผู้ดูแลระบบ</p>
                        </div>
                    </div>
                </div>
            </div>
        <?php else: ?>
            <div class="row g-4">
                <?php foreach ($products as $p): ?>
                    <div class="col-lg-4 col-md-6">
                        <div class="card product-card shadow">
                            <div class="card-header bg-primary text-white">
                                <h5 class="card-title mb-0">
                                    <i class="bi bi-box-seam"></i> <?= htmlspecialchars($p['product_name']) ?>
                                </h5>
                            </div>
                            <div class="card-body">
                                <div class="mb-2">
                                    <span class="badge bg-secondary">
                                        <i class="bi bi-tag"></i> <?= htmlspecialchars($p['category_name'] ?? 'ไม่มีหมวดหมู่') ?>
                                    </span>
                                </div>
                                <p class="card-text text-muted">
                                    <?= nl2br(htmlspecialchars(substr($p['description'], 0, 100))) ?>
                                    <?= strlen($p['description']) > 100 ? '...' : '' ?>
                                </p>
                                <div class="d-flex justify-content-between align-items-center mb-3">
                                    <div class="price-tag text-success">
                                        <i class="bi bi-currency-dollar"></i> <?= number_format($p['price'], 2) ?> บาท
                                    </div>
                                    <small class="text-muted">
                                        <i class="bi bi-boxes"></i> คงเหลือ <?= $p['stock'] ?> ชิ้น
                                    </small>
                                </div>
                            </div>
                            <div class="card-footer bg-white">
                                <div class="d-flex gap-2">
                                    <?php if ($isLoggedIn): ?>
                                        <?php if ($p['stock'] > 0): ?>
                                            <form action="cart.php" method="post" class="flex-grow-1">
                                                <input type="hidden" name="product_id" value="<?= $p['product_id'] ?>">
                                                <input type="hidden" name="quantity" value="1">
                                                <button type="submit" class="btn btn-success w-100">
                                                    <i class="bi bi-cart-plus"></i> เพิ่มในตะกร้า
                                                </button>
                                            </form>
                                        <?php else: ?>
                                            <button class="btn btn-secondary w-100 flex-grow-1" disabled>
                                                <i class="bi bi-x-circle"></i> สินค้าหมด
                                            </button>
                                        <?php endif; ?>
                                    <?php else: ?>
                                        <div class="flex-grow-1">
                                            <small class="text-muted d-block text-center">เข้าสู่ระบบเพื่อสั่งซื้อ</small>
                                        </div>
                                    <?php endif; ?>
                                    <a href="product_detail.php?id=<?= $p['product_id'] ?>" class="btn btn-outline-primary">
                                        <i class="bi bi-eye"></i> ดูรายละเอียด
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
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