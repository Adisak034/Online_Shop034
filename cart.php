<?php
session_start();
require_once 'session_timeout.php';
require 'config.php';
// ตรวจสอบการล็อกอิน
if (!isset($_SESSION['user_id'])) { // TODO: ใส่ session ของ user
    header("Location: login.php"); // TODO: หน้า login
    exit;
}
$user_id = $_SESSION['user_id']; // TODO: กำหนด user_id
// -----------------------------
// อัปเดตจำนวนสินค้าในตะกร้า
// -----------------------------
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_cart'])) {
    foreach ($_POST['quantities'] as $cart_id => $quantity) {
        $quantity = max(0, (int)$quantity);
        if ($quantity === 0) {
            // ลบสินค้าถ้าจำนวนเป็น 0
            $stmt = $conn->prepare("DELETE FROM cart WHERE cart_id = ? AND user_id = ?");
            $stmt->execute([(int)$cart_id, $user_id]);
        } else {
            // อัปเดตจำนวน
            $stmt = $conn->prepare("UPDATE cart SET quantity = ? WHERE cart_id = ? AND user_id = ?");
            $stmt->execute([$quantity, (int)$cart_id, $user_id]);
        }
    }
    header("Location: cart.php?updated=1");
    exit;
}

// -----------------------------
// ดึงรายการสินค้าในตะกร้า
// -----------------------------
$stmt = $conn->prepare("SELECT cart.cart_id, cart.quantity, products.product_id, products.product_name, products.price, products.image, products.stock
FROM cart
JOIN products ON cart.product_id = products.product_id
WHERE cart.user_id = ?");
$stmt->execute([$user_id]);
$items = $stmt->fetchAll(PDO::FETCH_ASSOC);
// -----------------------------
// เพิ่มสินค้าลงในตะกร้า
// -----------------------------
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['product_id'])) {
    $product_id = $_POST['product_id'];
    $quantity = max(1, intval($_POST['quantity'] ?? 1));

    // ตรวจสอบวำ่ สินค้าชิ้นนี้อยู่ในตะกร้าแล้วหรือ ยัง
    $stmt = $conn->prepare("SELECT * FROM cart WHERE user_id = ? AND product_id = ?");
    $stmt->execute([$user_id, $product_id]);
    $item = $stmt->fetch(PDO::FETCH_ASSOC);
    if ($item) {
        // ถ้ามีแล้ว ให้เพิ่มจำนวน
        $stmt = $conn->prepare("UPDATE cart SET quantity = quantity + ? WHERE cart_id = ?");
        // TODO: ชื่อ ตำราง, primary key ของตะกร ้ำ
        $stmt->execute([$quantity, $item['cart_id']]);
    } else {
        // ถ้ายังไม่มี ให้เพิ่มใหม่
        $stmt = $conn->prepare("INSERT INTO cart (user_id, product_id, quantity) VALUES (?, ?, ?)");
        $stmt->execute([$user_id, $product_id, $quantity]);
    }
    header("Location: cart.php");
    exit;
}
// -----------------------------
// คำนวณรำคำรวม
// -----------------------------
$total = 0;
foreach ($items as $item) {
$total += $item['quantity'] * $item['price']; // TODO: quantity * price
}
// -----------------------------
// ลบสินค้าออกจากตะกร้า
// -----------------------------
if (isset($_GET['remove'])) {
$cart_id = $_GET['remove'];
$stmt = $conn->prepare("DELETE FROM cart WHERE cart_id = ? AND user_id = ?");
$stmt->execute([$cart_id, $user_id]);
header("Location: cart.php");
exit;
}


?>
<!DOCTYPE html>
<html lang="th">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ตะกร้าสินค้า - BoboIT Shop</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css" rel="stylesheet">
    <link rel="stylesheet" href="main.css">
    <style>
        body {
            background-color: #f8f9fa;
        }
        .cart-item-img {
            width: 80px;
            height: 80px;
            object-fit: contain;
            background-color: #f8f9fa;
            border: 1px solid #dee2e6;
            border-radius: 8px;
        }
        .quantity-input {
            max-width: 90px;
        }

        .text-warning {
            color: #fd7e14 !important;
        }

    </style>
</head>

<body>
    <?php require_once 'navbar.php'; ?>

    <div class="container mt-4">
        <h2 class="mb-4"><i class="bi bi-cart3"></i> ตะกร้าสินค้าของคุณ</h2>

        <?php if (isset($_GET['updated'])) : ?>
            <div class="alert alert-success">อัปเดตตะกร้าสินค้าเรียบร้อยแล้ว</div>
        <?php endif; ?>

        <?php if (empty($items)) : ?>
            <div class="card text-center shadow-sm">
                <div class="card-body p-5">
                    <i class="bi bi-cart-x display-1 text-muted"></i>
                    <h4 class="mt-3">ตะกร้าของคุณว่างเปล่า</h4>
                    <p class="text-muted">เลือกซื้อสินค้าที่คุณสนใจได้เลย</p>
                    <a href="products.php" class="btn btn-warning mt-2">
                        <i class="bi bi-arrow-left"></i> กลับไปเลือกซื้อสินค้า 
                    </a>
                </div>
            </div>
        <?php else : ?>
            <form action="cart.php" method="post">
                <div class="row g-4">
                    <!-- Cart Items -->
                    <div class="col-lg-8">
                        <div class="card shadow-sm">
                            <div class="card-body">
                                <div class="table-responsive">
                                    <table class="table align-middle">
                                        <thead>
                                            <tr>
                                                <th scope="col">สินค้า</th>
                                                <th scope="col" class="text-center">จำนวน</th>
                                                <th scope="col" class="text-end">ราคาต่อหน่วย</th>
                                                <th scope="col" class="text-end">ราคารวม</th>
                                                <th scope="col" class="text-center"></th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach ($items as $item) : ?>
                                                <tr>
                                                    <td>
                                                        <div class="d-flex align-items-center">
                                                            <img src="<?= !empty($item['image']) ? 'product_images/' . rawurlencode($item['image']) : 'product_images/no-image.jpg' ?>" class="cart-item-img me-3" alt="<?= htmlspecialchars($item['product_name']) ?>">
                                                            <div>
                                                                <a href="product_detail.php?id=<?= $item['product_id'] ?>" class="text-dark text-decoration-none fw-bold"><?= htmlspecialchars($item['product_name']) ?></a>
                                                                <div class="text-muted small">สต็อก: <?= $item['stock'] ?></div>
                                                            </div>
                                                        </div>
                                                    </td>
                                                    <td class="text-center">
                                                        <input type="number" name="quantities[<?= $item['cart_id'] ?>]" class="form-control form-control-sm text-center quantity-input mx-auto" value="<?= $item['quantity'] ?>" min="0" max="<?= $item['stock'] ?>">
                                                    </td>
                                                    <td class="text-end"><?= number_format($item['price'], 2) ?></td>
                                                    <td class="text-end fw-bold"><?= number_format($item['price'] * $item['quantity'], 2) ?></td>
                                                    <td class="text-center">
                                                        <button type="button" class="btn btn-sm btn-outline-danger delete-cart-item-button"
                                                                data-cart-id="<?= $item['cart_id'] ?>"
                                                                data-product-name="<?= htmlspecialchars($item['product_name']) ?>"
                                                                title="ลบสินค้า">
                                                            <i class="bi bi-trash"></i>
                                                        </button>
                                                    </td>
                                                </tr>
                                            <?php endforeach; ?>
                                        </tbody>
                                    </table>
                                </div>
                                <div class="d-flex justify-content-between align-items-center mt-3">
                                    <a href="products.php" class="btn btn-outline-secondary"><i class="bi bi-arrow-left"></i> เลือกซื้อสินค้าต่อ</a>
                                    <button type="submit" name="update_cart" class="btn btn-warning"><i class="bi bi-arrow-clockwise"></i> อัปเดตตะกร้า</button>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Order Summary -->
                    <div class="col-lg-4">
                        <div class="card shadow-sm">
                            <div class="card-body">
                                <h5 class="card-title mb-3">สรุปรายการสั่งซื้อ</h5>
                                <ul class="list-group list-group-flush">
                                    <li class="list-group-item d-flex justify-content-between align-items-center">
                                        ยอดรวม
                                        <span><?= number_format($total, 2) ?> บาท</span>
                                    </li>
                                    <li class="list-group-item d-flex justify-content-between align-items-center fw-bold">
                                        ยอดสุทธิ
                                        <span class="fs-5 text-warning"><?= number_format($total, 2) ?> บาท</span>
                                    </li>
                                </ul>
                                <div class="d-grid mt-3">
                                    <a href="checkout.php" class="btn btn-success btn-lg">
                                        ไปที่หน้าชำระเงิน <i class="bi bi-arrow-right"></i>
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </form>
        <?php endif; ?>
    </div>

    <?php require_once 'footer.php'; ?>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/js/bootstrap.bundle.min.js"></script>
    <!-- SweetAlert2 -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const deleteButtons = document.querySelectorAll('.delete-cart-item-button');
            deleteButtons.forEach(button => {
                button.addEventListener('click', function() {
                    const cartId = this.dataset.cartId;
                    const productName = this.dataset.productName;

                    Swal.fire({
                        title: 'คุณแน่ใจหรือไม่?',
                        text: `คุณต้องการลบ "${productName}" ออกจากตะกร้าใช่หรือไม่?`,
                        icon: 'warning',
                        showCancelButton: true,
                        confirmButtonColor: '#d33',
                        cancelButtonColor: '#6c757d',
                        confirmButtonText: 'ลบ',
                        cancelButtonText: 'ยกเลิก'
                    }).then((result) => {
                        if (result.isConfirmed) {
                            window.location.href = `cart.php?remove=${cartId}`;
                        }
                    });
                });
            });
        });
    </script>
</body>

</html>