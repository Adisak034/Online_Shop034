<?php
session_start(); // เริ่มต้น session เพื่อจัดการการเข้าสู่ระบบ
require_once 'config.php';

require_once 'session_timeout.php';
//ดึงข้อมูลสินค้าทั้งหมดจากฐานข้อมูล
$base_query = "
    SELECT 
        p.*, 
        c.category_name,
        AVG(r.rating) as average_rating,
        COUNT(r.review_id) as total_reviews
    FROM 
        products p
    LEFT JOIN 
        categories c ON p.category_id = c.category_id
    LEFT JOIN
        reviews r ON p.product_id = r.product_id
";

// ตรวจสอบว่าผู้ใช้เข้าสู่ระบบแล้วหรือไม่ป
$isLoggedIn = isset($_SESSION['user_id']);

// ตรวจสอบว่ามีการค้นหาหรือไม่
if (isset($_GET['search']) && !empty(trim($_GET['search']))) {
    $search_query = '%' . trim($_GET['search']) . '%';
    $final_query = $base_query . " WHERE p.product_name LIKE ? OR p.description LIKE ? GROUP BY p.product_id ORDER BY p.created_at DESC";
    $stmt = $conn->prepare($final_query);
    $stmt->execute([$search_query, $search_query]);
    $products = $stmt->fetchAll(PDO::FETCH_ASSOC);
} else {
    // ดึงข้อมูลสินค้าทั้งหมด
    $final_query = $base_query . " GROUP BY p.product_id ORDER BY p.created_at DESC";
    $stmt = $conn->query($final_query);
    $products = $stmt->fetchAll(PDO::FETCH_ASSOC);
}
// ดึง Wishlist ของผู้ใช้ (ถ้าล็อกอิน)
$wishlist_items = [];
if ($isLoggedIn) {
    $stmt = $conn->prepare("SELECT product_id FROM wishlist WHERE user_id = ?");
    $stmt->execute([$_SESSION['user_id']]);
    // ดึงข้อมูล product_id ทั้งหมดมาเก็บใน array
    $wishlist_items = $stmt->fetchAll(PDO::FETCH_COLUMN, 0);
}






?>
<!DOCTYPE html>
<html lang="th">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>BoboIT Shop</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-LN+7fdVzj6u52u30Kp6M/trliBMCMKTyK833zpbD+pXdCLuTusPj697FH4R/5mcr" crossorigin="anonymous">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css" rel="stylesheet">
    <style>
        body {
            background: #F8F8F8;
            min-height: 100vh;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }

        .navbar {
 background: #007bff !important;
            backdrop-filter: blur(10px);
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
        }

        .navbar-nav .nav-link {
            color: white !important;
            font-weight: 500;
            transition: all 0.3s ease;
        }

        .navbar-nav .nav-link:hover {
            color: #ffc107 !important;
            transform: translateY(-2px);
        }

        .dropdown-menu {
            border: none;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            border-radius: 8px;
            min-width: 200px;
            padding: 0.5rem 0;
            z-index: 9999;
            position: absolute;
        }

        .dropdown-item {
            padding: 0.5rem 1rem;
            transition: background-color 0.3s ease;
            white-space: nowrap;
        }

        .dropdown-item:hover {
            background-color: #f8f9fa;
        }

        .dropdown-item.text-danger:hover {
            background-color: #f8f9fa;
            color: #dc3545 !important;
        }

        .dropdown-item-text {
            padding: 0.5rem 1rem;
            color: #6c757d;
            font-weight: 500;
        }

        .dropdown-divider {
            margin: 0.5rem 0;
        }

        .navbar {
            z-index: 1030;
            position: relative;
        }

        .card {
            transition: all 0.3s ease;
            border: none;
            border-radius: 15px;
        }

        .card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
        }

        .display-4 {
            font-weight: 700;
 background: #007bff;
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }

        .lead {
            font-size: 1.1rem;
            line-height: 1.6;
        }

        .btn {
            border-radius: 10px;
            transition: all 0.3s ease;
        }

        .btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.2);
        }

        .badge {
            border-radius: 10px;
            font-weight: 500;
        }

        .navbar-brand {
            font-weight: bold;
            color: white !important;
        }

        .product-card {
            height: 100%;
            transition: all 0.3s ease;
            border-radius: 15px;
            overflow: hidden;
            background: white;
        }

        .product-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
        }

        .product-thumb-wrapper {
            aspect-ratio: 1 / 1; /* กำหนดอัตราส่วน 1:1 */
            overflow: hidden;
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            background-color: #f8f9fa; /* สีพื้นหลังเผื่อรูปโปร่งใส */
        }

        .product-thumb {
            width: 100%;
            height: 100%;
            object-fit: contain; /* ปรับรูปให้พอดีโดยไม่ตัด */
        }

        .product-meta {
            font-size: .8rem;
            letter-spacing: .05em;
            color: #007bff;
            text-transform: uppercase;
            font-weight: 600;
        }

        .product-title {
            font-size: 1.1rem;
            margin: .25rem 0 .5rem;
            font-weight: 700;
            color: #333;
        }

        .price {
            font-weight: 700;
            font-size: 1.2rem;
            color: #007bff;
        }

        .rating i {
            color: #ffc107;
        }

        /* ปุ่มหัวใจ */
        .wishlist {
            color: #b9bfc6;
            transition: all 0.3s ease;
            cursor: pointer;
        }

        .wishlist:hover {
            color: #ff5b5b;
        }

        .wishlist.active i {
            font-weight: 900; /* ทำให้เป็นตัวหนา */
            color: #ff5b5b; /* สีแดงเมื่อถูกใจ */
        }

        .badge-top-left {
            position: absolute;
            top: .8rem;
            left: .8rem;
            z-index: 2;
            border-radius: 10px;
            padding: 0.4rem 0.6rem;
            font-weight: 600;
        }

        .carousel-item img {
            height: 450px;
            object-fit: cover;
        }

        .carousel-caption h5,
        .carousel-caption p {
            text-shadow: 2px 2px 4px rgba(0, 0, 0, 0.7);
        }

        .carousel-caption {
            background-color: rgba(0, 0, 0, 0.4); /* สีพื้นหลังกึ่งโปร่งใส */
            backdrop-filter: blur(5px); /* เอฟเฟกต์เบลอ */
            -webkit-backdrop-filter: blur(5px); /* สำหรับ Safari */
            padding: 1rem;
            border-radius: 10px;
        }

        .carousel-control-prev-icon,
        .carousel-control-next-icon {
            background-color: rgba(0, 0, 0, 0.3);
            border-radius: 50%;
            padding: 1.5rem;
        }

        .btn {
            border-radius: 15px;
            font-weight: 600;
            transition: all 0.3s ease;
        }

        .btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.2);
        }

        @media (max-width: 768px) {
            .admin-card {
                margin-bottom: 1rem;
            }
            
            .display-4 {
                font-size: 2rem;
            }
        }
    </style>
</head>

<body>
    <!-- Navigation Bar -->
    <?php require_once 'navbar.php'; ?>

    <!-- Carousel Section -->
    <div id="heroCarousel" class="carousel slide" data-bs-ride="carousel">
        <div class="carousel-indicators">
            <button type="button" data-bs-target="#heroCarousel" data-bs-slide-to="0" class="active" aria-current="true" aria-label="Slide 1"></button>
            <button type="button" data-bs-target="#heroCarousel" data-bs-slide-to="1" aria-label="Slide 2"></button>
            <button type="button" data-bs-target="#heroCarousel" data-bs-slide-to="2" aria-label="Slide 3"></button>
        </div>
        <div class="carousel-inner">
            <div class="carousel-item active">
                <img src="carousel_images/carousel1.jpg" class="d-block w-100" alt="โปรโมชั่นพิเศษ">
                <div class="carousel-caption d-none d-md-block">
                    <h5>สินค้าไอทีลดราคาพิเศษ</h5>
                    <p>พบกับโปรโมชั่นสุดร้อนแรงได้แล้ววันนี้</p>
                </div>
            </div>
            <div class="carousel-item">
                <img src="carousel_images/carousel2.jpg" class="d-block w-100" alt="อุปกรณ์คอมพิวเตอร์">
                <div class="carousel-caption d-none d-md-block">
                    <h5>อัปเกรดคอมพิวเตอร์ของคุณ</h5>
                    <p>อุปกรณ์เสริมและส่วนประกอบคอมพิวเตอร์คุณภาพสูง</p>
                </div>
            </div>
            <div class="carousel-item">
                <img src="carousel_images/carousel3.jpg" class="d-block w-100" alt="แกดเจ็ตใหม่ล่าสุด">
                <div class="carousel-caption d-none d-md-block">
                    <h5>แกดเจ็ตใหม่ล่าสุด</h5>
                    <p>นำเทรนด์ก่อนใครด้วยสินค้าไอทีใหม่ล่าสุดจากเรา</p>
                </div>
            </div>
        </div>
        <button class="carousel-control-prev" type="button" data-bs-target="#heroCarousel" data-bs-slide="prev">
            <span class="carousel-control-prev-icon" aria-hidden="true"></span>
            <span class="visually-hidden">Previous</span>
        </button>
        <button class="carousel-control-next" type="button" data-bs-target="#heroCarousel" data-bs-slide="next">
            <span class="carousel-control-next-icon" aria-hidden="true"></span>
            <span class="visually-hidden">Next</span>
        </button>
    </div>
    
<div class="container mt-4">
            <form action="products.php" method="GET" class="d-flex justify-content-center mb-4">
                <div class="input-group w-75">
                    <input type="text" class="form-control form-control-lg" placeholder="ค้นหาสินค้า..."
                        aria-label="ค้นหาสินค้า" name="search">
                    <button class="btn btn-primary btn-lg" type="submit">
                        <i class="bi bi-search"></i> ค้นหา
                    </button>
                </div>
            </form>
        </div>
        <?php if (empty($products) && isset($_GET['search'])): ?>
            <div class="alert alert-warning text-center mt-4" role="alert">
                <i class="bi bi-exclamation-triangle-fill"></i> ไม่พบสินค้าที่ตรงกับคำค้นหา "<?= htmlspecialchars(trim($_GET['search'])) ?>"
            </div>
        <?php elseif (empty($products)): ?>
            <div class="alert alert-info text-center mt-4" role="alert">
                <i class="bi bi-info-circle-fill"></i> ยังไม่มีสินค้าในระบบ
            </div>
        <?php endif; ?>

<!-- Products Section -->
<!-- ===== ส่วนแสดงสินค้า ===== -->
<div class="container mt-5">
<div class="row g-4"> <!-- EDIT C -->
    <?php foreach ($products as $p): ?>
        <?php
                // เตรียมรูป
                $img = !empty($p['image'])
                    ? 'product_images/' . rawurlencode($p['image'])
                    : 'product_images/no-image.jpg';
                // ตกแต่ง badge: NEW ภายใน 7 วัน / HOT ถ้าสต็อกน้อยกว่า 5
                $isNew = isset($p['created_at']) && (time() - strtotime($p['created_at']) <= 7 * 24 * 3600);
                $isHot = (int)$p['stock'] > 0 && (int)$p['stock'] < 5;
                // ดำวรีวิว (ถ้าหมายถึง DB จะโชว์ 4.5 จำนวนนั้น; ถ้ามี $p['rating'] ให้แทน)
                $rating = (float)($p['average_rating'] ?? 0);
                $in_wishlist = in_array($p['product_id'], $wishlist_items);
                $full = floor($rating); // จำนวนดำวเต็ม (เต็ม 1 ดวง) , floor ปัดลง
                $half = ($rating - $full) >= 0.5 ? 1 : 0; // มีดำวครึ่งดวงหรือไม่
                ?>
                <div class="col-12 col-sm-6 col-lg-3">
                    <div class="card product-card h-100 position-relative">
                        <!-- TODO====check $isNew / $isHot ==== -->
                        <?php if ($isNew): ?>
                            <span class="badge bg-success badge-top-left">NEW</span>
                        <?php elseif ($isHot): ?>
                            <span class="badge bg-danger badge-top-left">HOT</span>
                        <?php endif; ?>
                        <!-- TODO====show Product images ==== -->
                        <a href="product_detail.php?id=<?= (int)$p['product_id'] ?>" class="d-block m-3 product-thumb-wrapper">
                            <img src="<?= htmlspecialchars($img) ?>"
                                alt="<?= htmlspecialchars($p['product_name']) ?>"
                                class="product-thumb">
                        </a>
                        <div class="px-3 pb-3 d-flex flex-column"> <!-- EDIT C -->
                            <!-- TODO====div for category, heart ==== -->
                            <div class="d-flex justify-content-between align-items-center mb-1">
                                <div class="product-meta">
                                    <?= htmlspecialchars($p['category_name'] ?? 'Category') ?>
                                </div>
                                <?php if ($isLoggedIn): ?>
                                    <button class="btn btn-link p-0 wishlist <?= $in_wishlist ? 'active' : '' ?>" title="เพิ่ม/ลบ รายการโปรด" type="button" data-product-id="<?= (int)$p['product_id'] ?>">
                                        <i class="bi bi-heart-fill"></i>
                                    </button>
                                <?php endif; ?>
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
                                <span class="text-muted ms-1" style="font-size: 0.8rem;">(<?= (int)$p['total_reviews'] ?>)</span>
                            </div>
                            <!-- TODO====div for price ==== -->
                            <div class="price mb-3">
                                <?= number_format((float)$p['price'], 2) ?> บาท
                            </div>
                            <!-- TODO====div for button check login ==== -->
                            <div class="mt-auto">
                                <?php if ($isLoggedIn): // ถ้าล็อกอินแล้ว ?>
                                    <?php if ((int)$p['stock'] > 0): // ตรวจสอบว่ามีสินค้าในสต็อกหรือไม่ ?>
                                        <div class="d-flex gap-2">
                                            <form action="cart.php" method="post" style="display: inline;">
                                                <input type="hidden" name="product_id" value="<?= (int)$p['product_id'] ?>">
                                                <input type="hidden" name="quantity" value="1">
                                                <button type="submit" class="btn btn-sm btn-primary">เพิ่มในตะกร้า</button>
                                            </form>
                                            <a href="product_detail.php?id=<?= (int)$p['product_id'] ?>" class="btn btn-sm btn-outline-primary">ดูรายละเอียด</a>
                                        </div>
                                    <?php else: // สินค้าหมด ?>
                                        <span class="badge bg-danger">สินค้าหมด</span>
                                        <a href="product_detail.php?id=<?= (int)$p['product_id'] ?>" class="btn btn-sm btn-outline-secondary ms-2">ดูรายละเอียด</a>
                                    <?php endif; ?>
                                <?php else: // ถ้ายังไม่ได้ล็อกอิน ?>
                                  <small class="text-muted d-block mb-2">กรุณาเข้าสู่ระบบเพื่อสั่งซื้อสินค้า</small>
                                    <div class="d-flex gap-2">
                                        <a href="product_detail.php?id=<?= (int)$p['product_id'] ?>" class="btn btn-sm btn-outline-primary">ดูรายละเอียด</a>
                                        <a href="login.php" class="btn btn-sm btn-success">เข้าสู่ระบบ</a>
                                    </div>
                                <?php endif; ?>

                            </div>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
    <!-- Footer Section -->
    <?php require_once 'footer.php'; ?>


    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/js/bootstrap.bundle.min.js"
        integrity="sha384-ndDqU0Gzau9qJ1lfW4pNLlhNTkCfHzAVBReH9diLvGRem5+R9g2FzA8ZGN954O5Q" crossorigin="anonymous">
    </script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            document.querySelectorAll('.wishlist').forEach(button => {
                button.addEventListener('click', function(event) {
                    event.preventDefault();

                    const productId = this.dataset.productId;
                    if (!productId) return;

                    const formData = new FormData();
                    formData.append('product_id', productId);

                    fetch('toggle_wishlist.php', {
                        method: 'POST',
                        body: formData
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.status === 'error') {
                            // หากเซิร์ฟเวอร์ตอบกลับว่า error (เช่น ยังไม่ล็อกอิน)
                            // อาจจะแสดง alert หรือ redirect ไปหน้า login
                            alert(data.message);
                            if (data.action === 'redirect') {
                                window.location.href = 'login.php';
                            }
                        } else if (data.status === 'added') {
                            this.classList.add('active');
                        } else if (data.status === 'removed') {
                            this.classList.remove('active');
                        }
                    })
                    .catch(error => console.error('Error:', error));
                });
            });
        });
    </script>
</body>

</html>