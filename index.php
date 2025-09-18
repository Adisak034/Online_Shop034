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
            box-shadow: 0 8px 25px rgba(0, 0, 0, 0.15);
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

        .product-card {
            border: 1;
            background: #fff;
        }

        .product-thumb {
            height: 180px;
            object-fit: cover;
            border-radius: .5rem;
        }

        .product-meta {
            font-size: .75rem;
            letter-spacing: .05em;
            color: #8a8f98;
            text-transform: uppercase;
        }

        .product-title {
            font-size: 1rem;
            margin: .25rem 0 .5rem;
            font-weight: 600;
            color: #222;
        }

        .price {
            font-weight: 700;
        }

        .rating i {
            color: #ffc107;
        }

        /* ดำวสที อง */
        .wishlist {
            color: #b9bfc6;
        }

        .wishlist:hover {
            color: #ff5b5b;
        }

        .badge-top-left {
            position: absolute;
            top: .5rem;
            left: .5rem;
            z-index: 2;
            border-radius: .375rem;
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
                                <li>
                                    <hr class="dropdown-divider">
                                </li>
                                <li><a class="dropdown-item" href="profile.php">
                                        <i class="bi bi-person"></i> ข้อมูลส่วนตัว
                                    </a></li>
                                <li><a class="dropdown-item" href="cart.php">
                                        <i class="bi bi-cart"></i> ตะกร้าสินค้า
                                    </a></li>
                                <?php if ($_SESSION['role'] === 'admin'): ?>
                                    <li>
                                        <hr class="dropdown-divider">
                                    </li>
                                    <li><a class="dropdown-item" href="admin/index.php">
                                            <i class="bi bi-gear"></i> จัดการระบบ
                                        </a></li>
                                <?php endif; ?>
                                <li>
                                    <hr class="dropdown-divider">
                                </li>
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
        <!-- ===== สว่ นแสดงสนิ คำ้ ===== -->
        <div class="row g-4"> <!-- EDIT C -->
            <?php foreach ($products as $p): ?>
                <!-- TODO==== เตรียมรูป / ตกแต่ง badge / ดำวรีวิว ==== -->
                <?php
                // เตรียมรูป
                $img = !empty($p['image'])
                    ? 'product_images/' . rawurlencode($p['image'])
                    : 'product_images/no-image.jpg';
                // ตกแต่ง badge: NEW ภำยใน 7 วัน / HOT ถ ้ำสต็อกน้อยกว่ำ 5
                $isNew = isset($p['created_at']) && (time() - strtotime($p['created_at']) <= 7 * 24 * 3600);
                $isHot = (int)$p['stock'] > 0 && (int)$p['stock'] < 5;
                // ดำวรีวิว (ถ ้ำไม่มีใน DB จะโชว์ 4.5 จ ำลอง; ถ ้ำมี $p['rating'] ให้แทน)
                $rating = isset($p['rating']) ? (float)$p['rating'] : 4.5;
                $full = floor($rating); // จ ำนวนดำวเต็ม (เต็ม 1 ดวง) , floor ปัดลง
                $half = ($rating - $full) >= 0.5 ? 1 : 0; // มีดำวครึ่งดวงหรือไม่
                ?>
                <div class="col-12 col-sm-6 col-lg-3"> <!-- EDIT C -->
                    <div class="card product-card h-100 position-relative"> <!-- EDIT C -->
                        <!-- TODO====check $isNew / $isHot ==== -->
                        <?php if ($isNew): ?>
                            <span class="badge bg-success badge-top-left">NEW</span>
                        <?php elseif ($isHot): ?>
                            <span class="badge bg-danger badge-top-left">HOT</span>
                        <?php endif; ?>
                        <!-- TODO====show Product images ==== -->
                        <a href="product_detail.php?id=<?= (int)$p['product_id'] ?>" class="p-3 d-block">
                            <img src="<?= htmlspecialchars($img) ?>"
                                alt="<?= htmlspecialchars($p['product_name']) ?>"
                                class="img-fluid w-100 product-thumb">
                        </a>
                        <div class="px-3 pb-3 d-flex flex-column"> <!-- EDIT C -->
                            <!-- TODO====div for category, heart ==== -->
                            <div class="d-flex justify-content-between align-items-center mb-1">
                                <div class="product-meta">
                                    <?= htmlspecialchars($p['category_name'] ?? 'Category') ?>
                                </div>
                                <button class="btn btn-link p-0 wishlist" title="Add to wishlist" type="button">
                                    <i class="bi bi-heart"></i>
                                </button>
                            </div>
                            <!-- TODO====link, div for product name ==== -->
                            <a class="text-decoration-none" href="product_detail.php?id=<?= (int)$p['product_id'] ?>">
                                <div class="product-title">
                                    <?= htmlspecialchars($p['product_name']) ?>
                                </div>
                            </a>
                            <!-- TODO====div for rating ==== -->
                            <!-- ดำวรีวิว -->
                            <div class="rating mb-2">
                                <?php for ($i = 0; $i < $full; $i++): ?><i class="bi bi-star-fill"></i><?php endfor; ?>
                                <?php if ($half): ?><i class="bi bi-star-half"></i><?php endif; ?>
                                <?php for ($i = 0; $i < 5 - $full - $half; $i++): ?><i class="bi bi-star"></i><?php endfor; ?>
                            </div>
                            <!-- TODO====div for price ==== -->
                            <div class="price mb-3">
                                <?= number_format((float)$p['price'], 2) ?> บำท
                            </div>
                            <!-- TODO====div for button check login ==== -->
                            <div class="mt-auto d-flex gap-2">
                                <?php if ($isLoggedIn): ?>
                                    <form action="cart.php" method="post" class="d-inline-flex gap-2">
                                        <input type="hidden" name="product_id" value="<?= (int)$p['product_id'] ?>">
                                        <input type="hidden" name="quantity" value="1">
                                        <button type="submit" class="btn btn-sm btn-success">เพิ่มในตะกร ้ำ</button>
                                    </form>
                                <?php else: ?>
                                    <small class="text-muted">เขำ้สรู่ ะบบเพอื่ สั่งซอื้ </small>
                                <?php endif; ?>
                                <a href="product_detail.php?id=<?= (int)$p['product_id'] ?>"
                                    class="btn btn-sm btn-outline-primary ms-auto">ดูรำยละเอียด</a>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
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