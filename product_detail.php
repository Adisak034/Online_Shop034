<?php
session_start();
require_once 'config.php';
if (!isset($_GET['id'])) {
    header("Location: index.php");
    exit();
}

$product_id = $_GET['id'];
$stmt = $conn->prepare("SELECT p.*, c.category_name
FROM products p
LEFT JOIN categories c ON p.category_id = c.category_id
WHERE p.product_id = ?");
$stmt->execute([$product_id]);
$product = $stmt->fetch(PDO::FETCH_ASSOC);

$isLoggedIn = isset($_SESSION['user_id']);

?>
<!DOCTYPE html>
<html lang="th">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>รายละเอียดสินค้า - <?= htmlspecialchars($product['product_name']) ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-LN+7fdVzj6u52u30Kp6M/trliBMCMKTyK833zpbD+pXdCLuTusPj697FH4R/5mcr" crossorigin="anonymous">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css" rel="stylesheet">
    <style>
        body {
            background: white;
            min-height: 100vh;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }

        .navbar {
            background: linear-gradient(135deg, #007bff 0%, #0056b3 100%) !important;
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

        .admin-card {
            border: none;
            border-radius: 20px;
            overflow: hidden;
            background: linear-gradient(135deg, #ffffff 0%, #f8f9fa 100%);
            transition: all 0.3s ease;
        }

        .admin-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
        }

        .admin-icon {
            font-size: 3rem;
            margin: 1rem 0;
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

        .display-6 {
            font-weight: 700;
            background: linear-gradient(135deg, #007bff, #0056b3);
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

        .price-badge {
            font-size: 1.8rem;
            font-weight: bold;
            color: #007bff;
        }

        .quantity-input {
            max-width: 120px;
            border-radius: 10px;
        }

        .breadcrumb {
            background: rgba(255, 255, 255, 0.9);
            border-radius: 10px;
            padding: 0.75rem 1rem;
        }

        @media (max-width: 768px) {
            .admin-card {
                margin-bottom: 1rem;
            }
            
            .display-6 {
                font-size: 2rem;
            }
        }
    </style>
</head>

<body>
    <!-- Navigation Bar -->
    <nav class="navbar navbar-expand-lg navbar-dark bg-primary shadow">
        <div class="container">
            <a class="navbar-brand" href="index.php">
                <i class="bi bi-shop"></i> Bobo Eletronics
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav me-auto">
                    <li class="nav-item">
                        <a class="nav-link" href="index.php">
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
        <!-- Breadcrumb -->
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item">
                    <a href="index.php" class="text-decoration-none">
                        <i class="bi bi-house"></i> หน้าหลัก
                    </a>
                </li>
                <li class="breadcrumb-item active" aria-current="page">
                    รายละเอียดสินค้า
                </li>
            </ol>
        </nav>

        <!-- Back Button -->
        <div class="mb-3">
            <a href="index.php" class="btn btn-outline-secondary">
                <i class="bi bi-arrow-left"></i> กลับหน้ารายการสินค้า
            </a>
        </div>

        <!-- Product Detail Card -->
        <div class="row justify-content-center">
            <div class="col-lg-8">
                <div class="card product-card shadow-lg">
                    <div class="card-header bg-primary text-white">
                        <h3 class="card-title mb-0">
                            <i class="bi bi-box-seam"></i> รายละเอียดสินค้า
                        </h3>
                    </div>
                    <div>
                        <?php
                        $img = !empty($product['image'])
                            ? 'product_images/' . rawurlencode($product['image'])
                            : 'product_images/no-image.jpg';
                        ?>
                        <img src="<?= $img ?>" class="card-img-top p-3" alt="<?= htmlspecialchars($product['product_name']) ?>" style="object-fit: contain; height: 400px; border-top-left-radius: 15px; border-top-right-radius: 15px;">
                    </div>
                    <div class=" card-body p-4">
                        <!-- Product Name -->
                        <div class="text-center mb-4">
                            <h1 class="display-6 text-primary">
                                <?= htmlspecialchars($product['product_name']) ?>
                            </h1>
                            <div class="badge bg-secondary fs-6">
                                <i class="bi bi-tag"></i> <?= htmlspecialchars($product['category_name']) ?>
                            </div>
                        </div>

                        <!-- Product Description -->
                        <div class="row mb-4">
                            <div class="col-12">
                                <h5 class="text-muted">
                                    <i class="bi bi-card-text"></i> รายละเอียด
                                </h5>
                                <div class="card bg-light">
                                    <div class="card-body">
                                        <p class="card-text"><?= nl2br(htmlspecialchars($product['description'])) ?></p>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Product Info -->
                        <div class="row mb-4">
                            <div class="col-md-6">
                                <div class="card border-success">
                                    <div class="card-body text-center">
                                        <h5 class="text-success">
                                            <i class="bi bi-currency-dollar"></i> ราคา
                                        </h5>
                                        <div class="price-badge text-success">
                                            <?= number_format($product['price'], 2) ?> บาท
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="card border-info">
                                    <div class="card-body text-center">
                                        <h5 class="text-info">
                                            <i class="bi bi-boxes"></i> สต็อก
                                        </h5>
                                        <div class="price-badge text-info">
                                            <?= htmlspecialchars($product['stock']) ?> ชิ้น
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Add to Cart Section -->
                        <?php if ($isLoggedIn): ?>
                            <?php if ($product['stock'] > 0): ?>
                                <div class="card border-success">
                                    <div class="card-header bg-success text-white">
                                        <h5 class="mb-0">
                                            <i class="bi bi-cart-plus"></i> เพิ่มในตะกร้าสินค้า
                                        </h5>
                                    </div>
                                    <div class="card-body">
                                        <form action="cart.php" method="post">
                                            <input type="hidden" name="product_id" value="<?= $product['product_id'] ?>">
                                            <div class="row align-items-end">
                                                <div class="col-md-6">
                                                    <label for="quantity" class="form-label">
                                                        <i class="bi bi-123"></i> จำนวน:
                                                    </label>
                                                    <input type="number"
                                                        name="quantity"
                                                        id="quantity"
                                                        class="form-control quantity-input"
                                                        value="1"
                                                        min="1"
                                                        max="<?= $product['stock'] ?>"
                                                        required>
                                                    <div class="form-text">
                                                        สูงสุด <?= $product['stock'] ?> ชิ้น
                                                    </div>
                                                </div>
                                                <div class="col-md-6">
                                                    <button type="submit" class="btn btn-success btn-lg w-100">
                                                        <i class="bi bi-cart-check"></i> เพิ่มในตะกร้า
                                                    </button>
                                                </div>
                                            </div>
                                        </form>
                                    </div>
                                </div>
                            <?php else: ?>
                                <div class="alert alert-warning text-center">
                                    <i class="bi bi-exclamation-triangle"></i>
                                    <strong>สินค้าหมด!</strong> ขออภัยในขณะนี้สินค้าหมดชั่วคราว
                                </div>
                            <?php endif; ?>
                        <?php else: ?>
                            <div class="card border-warning">
                                <div class="card-body text-center">
                                    <i class="bi bi-person-lock display-4 text-warning"></i>
                                    <h5 class="text-warning mt-3">กรุณาเข้าสู่ระบบเพื่อสั่งซื้อสินค้า</h5>
                                    <div class="mt-3">
                                        <a href="login.php" class="btn btn-success me-2">
                                            <i class="bi bi-box-arrow-in-right"></i> เข้าสู่ระบบ
                                        </a>
                                        <a href="register.php" class="btn btn-primary">
                                            <i class="bi bi-person-plus"></i> สมัครสมาชิก
                                        </a>
                                    </div>
                                </div>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Footer -->
    <footer class="bg-dark text-white py-4 mt-5">
        <div class="container text-center">
            <p class="mb-0">&copy;2025 Bobo Eletronics.</p>
        </div>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/js/bootstrap.bundle.min.js"
        integrity="sha384-ndDqU0Gzau9qJ1lfW4pNLlhNTkCfHzAVBReH9diLvGRem5+R9g2FzA8ZGN954O5Q" crossorigin="anonymous">
    </script>
</body>

</html>