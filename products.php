<?php
session_start();
require_once 'session_timeout.php';
require_once 'config.php';

// --- ดึงข้อมูลหมวดหมู่ทั้งหมดสำหรับ Navbar ---
// --- ส่วนจัดการการดึงข้อมูลสินค้า ---
$page_title = "สินค้าทั้งหมด";
$category_name = null;

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

$conditions = [];
$params = [];

// กรองตามหมวดหมู่
if (isset($_GET['category_id']) && !empty($_GET['category_id'])) {
    $category_id = (int)$_GET['category_id'];
    $conditions[] = "p.category_id = ?";
    $params[] = $category_id;

    // ดึงชื่อหมวดหมู่สำหรับแสดงผล
    $cat_name_stmt = $conn->prepare("SELECT category_name FROM categories WHERE category_id = ?");
    $cat_name_stmt->execute([$category_id]);
    $category_name = $cat_name_stmt->fetchColumn();
    if ($category_name) {
        $page_title = "หมวดหมู่: " . htmlspecialchars($category_name);
    }
}

// กรองตามการค้นหา
if (isset($_GET['search']) && !empty(trim($_GET['search']))) {
    $search_query = '%' . trim($_GET['search']) . '%';
    $conditions[] = "(p.product_name LIKE ? OR p.description LIKE ?)";
    $params[] = $search_query;
    $params[] = $search_query;
    $page_title = "ผลการค้นหา: '" . htmlspecialchars(trim($_GET['search'])) . "'";
}

$final_query = $base_query;
if (!empty($conditions)) {
    $final_query .= " WHERE " . implode(' AND ', $conditions);
}

$final_query .= " GROUP BY p.product_id ORDER BY p.created_at DESC";

$stmt = $conn->prepare($final_query);
$stmt->execute($params);
$products = $stmt->fetchAll(PDO::FETCH_ASSOC);

// --- ส่วนจัดการข้อมูลทั่วไป ---
$isLoggedIn = isset($_SESSION['user_id']);

// ดึง Wishlist ของผู้ใช้ (ถ้าล็อกอิน)
$wishlist_items = [];
if ($isLoggedIn) {
    $stmt_wishlist = $conn->prepare("SELECT product_id FROM wishlist WHERE user_id = ?");
    $stmt_wishlist->execute([$_SESSION['user_id']]);
    $wishlist_items = $stmt_wishlist->fetchAll(PDO::FETCH_COLUMN, 0);
}
?>
<!DOCTYPE html>
<html lang="th">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $page_title ?> - BoboIT Shop</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/css/bootstrap.min.css" rel="stylesheet">
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
            aspect-ratio: 1 / 1;
            overflow: hidden;
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            background-color: #f8f9fa;
        }

        .product-thumb {
            width: 100%;
            height: 100%;
            object-fit: contain;
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

        .wishlist {
            color: #b9bfc6;
            transition: all 0.3s ease;
            cursor: pointer;
        }

        .wishlist:hover {
            color: #ff5b5b;
        }

        .wishlist.active i {
            font-weight: 900;
            color: #ff5b5b;
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

        .page-header {
            background: white;
            border-radius: 15px;
        }
    </style>
</head>

<body>
    <!-- Navigation Bar -->
    <?php require_once 'navbar.php'; ?>

    <div class="container mt-4">
        <!-- Page Header -->
        <div class="p-4 mb-4 page-header shadow-sm">
            <div class="d-flex justify-content-between align-items-center">
                <h1 class="display-6 mb-0"><?= $page_title ?></h1>
                <form action="products.php" method="GET" class="d-flex">
                    <input type="text" class="form-control me-2" placeholder="ค้นหาสินค้า..." name="search" value="<?= isset($_GET['search']) ? htmlspecialchars($_GET['search']) : '' ?>">
                    <button class="btn btn-primary" type="submit"><i class="bi bi-search"></i></button>
                </form>
            </div>
        </div>

        <?php if (empty($products)) : ?>
            <div class="alert alert-warning text-center mt-4" role="alert">
                <i class="bi bi-exclamation-triangle-fill"></i> ไม่พบสินค้าที่ตรงกับเงื่อนไข
            </div>
        <?php endif; ?>

        <!-- Products Section -->
        <div class="row g-4">
            <?php foreach ($products as $p) : ?>
                <?php
                $img = !empty($p['image'])
                    ? 'product_images/' . rawurlencode($p['image'])
                    : 'product_images/no-image.jpg';
                $isNew = isset($p['created_at']) && (time() - strtotime($p['created_at']) <= 7 * 24 * 3600);
                $isHot = (int)$p['stock'] > 0 && (int)$p['stock'] < 5;
                $rating = (float)($p['average_rating'] ?? 0);
                $in_wishlist = in_array($p['product_id'], $wishlist_items);
                $full = floor($rating);
                $half = ($rating - $full) >= 0.5 ? 1 : 0;
                ?>
                <div class="col-12 col-sm-6 col-lg-3">
                    <div class="card product-card h-100 position-relative">
                        <?php if ($isNew) : ?>
                            <span class="badge bg-success badge-top-left">NEW</span>
                        <?php elseif ($isHot) : ?>
                            <span class="badge bg-danger badge-top-left">HOT</span>
                        <?php endif; ?>
                        <a href="product_detail.php?id=<?= (int)$p['product_id'] ?>" class="d-block m-3 product-thumb-wrapper">
                            <img src="<?= htmlspecialchars($img) ?>" alt="<?= htmlspecialchars($p['product_name']) ?>" class="product-thumb">
                        </a>
                        <div class="px-3 pb-3 d-flex flex-column">
                            <div class="d-flex justify-content-between align-items-center mb-1">
                                <div class="product-meta">
                                    <?= htmlspecialchars($p['category_name'] ?? 'Category') ?>
                                </div>
                                <?php if ($isLoggedIn) : ?>
                                    <button class="btn btn-link p-0 wishlist <?= $in_wishlist ? 'active' : '' ?>" title="เพิ่ม/ลบ รายการโปรด" type="button" data-product-id="<?= (int)$p['product_id'] ?>">
                                        <i class="bi bi-heart-fill"></i>
                                    </button>
                                <?php endif; ?>
                            </div>
                            <a class="text-decoration-none" href="product_detail.php?id=<?= (int)$p['product_id'] ?>">
                                <div class="product-title">
                                    <?= htmlspecialchars($p['product_name']) ?>
                                </div>
                            </a>
                            <div class="rating mb-2">
                                <?php for ($i = 0; $i < $full; $i++) : ?><i class="bi bi-star-fill"></i><?php endfor; ?>
                                <?php if ($half) : ?><i class="bi bi-star-half"></i><?php endif; ?>
                                <?php for ($i = 0; $i < 5 - $full - $half; $i++) : ?><i class="bi bi-star"></i><?php endfor; ?>
                                <span class="text-muted ms-1" style="font-size: 0.8rem;">(<?= (int)$p['total_reviews'] ?>)</span>
                            </div>
                            <div class="price mb-3">
                                <?= number_format((float)$p['price'], 2) ?> บาท
                            </div>
                            <div class="mt-auto">
                                <?php if ($isLoggedIn) : ?>
                                    <?php if ((int)$p['stock'] > 0) : ?>
                                        <div class="d-flex gap-2">
                                            <form action="cart.php" method="post" style="display: inline;">
                                                <input type="hidden" name="product_id" value="<?= (int)$p['product_id'] ?>">
                                                <input type="hidden" name="quantity" value="1">
                                                <button type="submit" class="btn btn-sm btn-primary">เพิ่มในตะกร้า</button>
                                            </form>
                                            <a href="product_detail.php?id=<?= (int)$p['product_id'] ?>" class="btn btn-sm btn-outline-primary">ดูรายละเอียด</a>
                                        </div>
                                    <?php else : ?>
                                        <span class="badge bg-danger">สินค้าหมด</span>
                                        <a href="product_detail.php?id=<?= (int)$p['product_id'] ?>" class="btn btn-sm btn-outline-secondary ms-2">ดูรายละเอียด</a>
                                    <?php endif; ?>
                                <?php else : ?>
                                    <small class="text-muted d-block mb-2">กรุณาเข้าสู่ระบบเพื่อสั่งซื้อสินค้า</small>
                                    <div class="d-flex gap-2">
                                        <a href="product_detail.php?id=<?= (int)$p['product_id'] ?>" class="btn btn-sm btn-outline-secondary">ดูรายละเอียด</a>
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

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/js/bootstrap.bundle.min.js"></script>
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