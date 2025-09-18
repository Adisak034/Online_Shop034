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
        }

        .product-card {
            border-radius: 15px;
            border: none;
        }

        .price-badge {
            font-size: 1.5rem;
            font-weight: bold;
        }

        .quantity-input {
            max-width: 120px;
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
            <div class="navbar-nav ms-auto">
                <?php if ($isLoggedIn): ?>
                    <span class="navbar-text text-white">
                        <i class="bi bi-person-circle"></i> <?= htmlspecialchars($_SESSION['username']) ?>
                    </span>
                <?php endif; ?>
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
                        <?php if (!empty($product['image'])): ?>
                            <?php
                            $img = !empty($product['image'])
                                ? 'product_images/' . rawurlencode($product['image'])
                                : 'product_images/no-image.jpg';
                            ?>
                            <img src="<?= $img ?>" class="card-img-top" alt="<?= htmlspecialchars($product['product_name']) ?>" style="object-fit: cover; height: 400px; border-top-left-radius: 15px; border-top-right-radius: 15px;">
                        <?php else: ?>
                            <div class="bg-light d-flex align-items-center justify-content-center" style="height: 400px; border-top-left-radius: 15px; border-top-right-radius: 15px;">
                                <span class="text-muted fs-4">ไม่มีรูปภาพ</span>

                            </div>
                        <?php endif; ?>
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
            <p class="mb-0">&copy; Adisak Yongpanya 664230034 66/46</p>
        </div>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/js/bootstrap.bundle.min.js"
        integrity="sha384-ndDqU0Gzau9qJ1lfW4pNLlhNTkCfHzAVBReH9diLvGRem5+R9g2FzA8ZGN954O5Q" crossorigin="anonymous">
    </script>
</body>

</html>