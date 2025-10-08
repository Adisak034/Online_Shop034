<?php
require_once '../config.php';
require_once '../session_timeout.php';
require_once 'auth_admin.php';
//ตรวจสอบสิทธิ์admin
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../login.php");
    exit;
}
// ตรวจสอบว่ามีการส่ง id สินค้าหรือไม่
if (!isset($_GET['id'])) {
    header("Location: products.php");
    exit;
}

$product_id = $_GET['id'];
// ดึงข้อมูลสินค้า
$stmt = $conn->prepare("SELECT * FROM products WHERE product_id = ?");
$stmt->execute([$product_id]);
$product = $stmt->fetch(PDO::FETCH_ASSOC);
if (!$product) {
    echo "<h3>ไม่มีข้อมูลสินค้า</h3>";
    exit;
}
// ดึงหมวดหมู่ทั้งหมด
$categories = $conn->query("SELECT * FROM categories")->fetchAll(PDO::FETCH_ASSOC);
// เมื่อมีการส่งฟอร์ม
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['product_name']);
    $description = trim($_POST['description']);
    $price = (float)$_POST['price'];
    $stock = (int)$_POST['stock'];
    $category_id = (int)$_POST['category_id'];
    // ค่ำรูปเดิมจำกฟอร์ม
    $oldImage = $_POST['old_image'] ?? null;
    $removeImage = isset($_POST['remove_image']); // true/false
    if ($name && $price > 0) {
        // เตรียมตัวแปรรูปที่จะบันทึก
        $newImageName = $oldImage; // default: คงรูปเดิมไว้
        // 1) ถ ้ำมีติ๊ก "ลบรูปเดิม" → ตั้งให้เป็น null
        if ($removeImage) {
            $newImageName = null;
        }
        // 2) ถ ้ำมีอัปโหลดไฟล์ใหม่ → ตรวจแลว้เซฟไฟลแ์ ละตัง้ชอื่ ใหมท่ ับคำ่
        if (!empty($_FILES['product_image']['name'])) {
            $file = $_FILES['product_image'];
            // ตรวจชนิดไฟล์แบบง่ำย (แนะน ำ: ตรวจ MIME จริงด ้วย finfo)
            $allowed = ['image/jpeg', 'image/png'];
            if (in_array($file['type'], $allowed, true) && $file['error'] === UPLOAD_ERR_OK) {
                // สรำ้งชอื่ ไฟลใ์หม่
                $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
                $newImageName = 'product_' . time() . '.' . $ext;
                $uploadDir = realpath(__DIR__ . '/../product_images');
                $destPath = $uploadDir . DIRECTORY_SEPARATOR . $newImageName;
                // ย้ำยไฟล์อัปโหลด
                if (!move_uploaded_file($file['tmp_name'], $destPath)) {
                    // ถ ้ำย้ำยไม่ได ้ อำจตั้ง flash message แลว้คงใชรู้ปเดมิ ไว ้
                    $newImageName = $oldImage;
                }
            }
        }
        // อัปเดต DB
        $sql = "UPDATE products
SET product_name = ?, description = ?, price = ?, stock = ?, category_id = ?, image = ?
WHERE product_id = ?";
        $args = [$name, $description, $price, $stock, $category_id, $newImageName, $product_id];
        $stmt = $conn->prepare($sql);
        $stmt->execute($args);
        // ลบไฟล์เก่ำในดิสก์ ถ ้ำ:
        // - มีรูปเดิม ($oldImage) และ
        // - เกดิ กำรเปลยี่ นรปู (อัปโหลดใหมห่ รอื สั่งลบรปู เดมิ)
        if (!empty($oldImage) && $oldImage !== $newImageName) {
            $baseDir = realpath(__DIR__ . '/../product_images');
            $filePath = realpath($baseDir . DIRECTORY_SEPARATOR . $oldImage);
            if ($filePath && strpos($filePath, $baseDir) === 0 && is_file($filePath)) {
                @unlink($filePath);
            }
        }
        header("Location: products.php");
        exit;
    }
}

?>
<!DOCTYPE html>
<html lang="th">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>แก้ไขสินค้า - ระบบจัดการร้านค้า</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-LN+7fdVzj6u52u30Kp6M/trliBMCMKTyK833zpbD+pXdCLuTusPj697FH4R/5mcr" crossorigin="anonymous">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css" rel="stylesheet">
    <style>
        body {
            min-height: 100vh;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }

        .card {
            transition: transform 0.3s, box-shadow 0.3s;
        }

        .card:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 25px rgba(0, 0, 0, 0.15);
        }

        .admin-card {
            border: none;
            border-radius: 15px;
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
            box-shadow: 0 8px 32px rgba(31, 38, 135, 0.37);
        }

        .admin-card .card-body {
            padding: 2rem;
        }

        .admin-icon {
            font-size: 3rem;
            margin-bottom: 1rem;
        }

        .navbar-brand {
            font-weight: bold;
        }

        /* Dropdown Styles */
        .dropdown-menu {
            background: white;
            border: 1px solid rgba(0, 0, 0, 0.125);
            border-radius: 8px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
            z-index: 1050;
            min-width: 200px;
        }

        .dropdown-item {
            color: #495057;
            font-weight: 500;
            padding: 8px 16px;
            transition: all 0.3s ease;
        }

        .dropdown-item:hover {
            background-color: #f8f9fa;
            color: #495057;
        }

        .dropdown-item.text-danger:hover {
            background-color: #f5c6cb;
            color: #721c24;
        }

        .dropdown-item-text {
            color: #6c757d;
            font-size: 0.875rem;
        }

        .dropdown-divider {
            margin: 4px 0;
        }

        /* Form Styles */
        .form-label {
            color: #495057;
            font-weight: 600;
            margin-bottom: 0.5rem;
        }

        .form-control, .form-select {
            border: 2px solid #e9ecef;
            border-radius: 8px;
            padding: 0.75rem 1rem;
            transition: all 0.3s ease;
            font-size: 0.95rem;
            background: rgba(255, 255, 255, 0.9);
        }

        .form-control:focus, .form-select:focus {
            border-color: #007bff;
            box-shadow: 0 0 0 0.25rem rgba(0, 123, 255, 0.25);
            background: white;
        }

        /* Button Styles */
        .btn {
            border-radius: 8px;
            padding: 0.75rem 1.5rem;
            font-weight: 600;
            transition: all 0.3s ease;
        }

        .btn-primary {
            background: linear-gradient(135deg, #007bff 0%, #0056b3 100%);
            border: none;
        }

        .btn-primary:hover {
            background: linear-gradient(135deg, #0056b3 0%, #004085 100%);
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(0, 123, 255, 0.3);
        }

        .btn-secondary {
            background: linear-gradient(135deg, #6c757d 0%, #5a6268 100%);
            border: none;
        }

        .btn-secondary:hover {
            background: linear-gradient(135deg, #5a6268 0%, #495057 100%);
            transform: translateY(-2px);
        }

        /* Image Preview */
        .image-preview {
            border: 2px dashed #dee2e6;
            border-radius: 8px;
            padding: 1rem;
            text-align: center;
            background: rgba(248, 249, 250, 0.8);
            transition: all 0.3s ease;
        }

        .image-preview:hover {
            border-color: #007bff;
            background: rgba(227, 242, 253, 0.8);
        }

        .current-image {
            border-radius: 8px;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
        }

        /* Welcome Section Style */
        .welcome-card {
            background: white;
            border-radius: 15px;
            border: none;
        }

        .welcome-card .card-body {
            padding: 2rem;
            text-align: center;
        }

        .display-5 {
            color: #007bff;
        }
    </style>
</head>

<body>
    <!-- Navigation Bar -->
    <?php require_once 'navbar_admin.php'; ?>

    <div class="container mt-5">
        <!-- Welcome Section -->
        <div class="row mb-4">
            <div class="col-12">
                <div class="card admin-card shadow-lg">
                    <div class="card-body text-center">
                        <h1 class="display-5 text-primary mb-3">
                            <i class="bi bi-pencil-square"></i> แก้ไขสินค้า
                        </h1>
                        <p class="lead text-muted">
                            คุณกำลังแก้ไข: <strong><?= htmlspecialchars($product['product_name']) ?></strong>
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="container mt-5">
        <!-- Back Button -->
        <div class="row mb-4">
            <div class="col-12">
                <a href="products.php" class="btn btn-secondary">
                    <i class="bi bi-arrow-left"></i> กลับหน้ารายการสินค้า
                </a>
            </div>
        </div>

        <!-- Form Section -->
        <div class="row">
            <div class="col-12">
                <div class="card admin-card shadow-lg">
                    <div class="card-header bg-primary text-white">
                        <h5 class="mb-0">
                            <i class="bi bi-pencil-square"></i> ฟอร์มแก้ไขสินค้า
                        </h5>
                    </div>
                    <div class="card-body p-4">
                <form method="post" enctype="multipart/form-data" class="row g-3">
                    <div class="col-md-6">
                        <label for="product_name" class="form-label">ชื่อสินค้า</label>
                        <input type="text" name="product_name" id="product_name" class="form-control"
                            value="<?= htmlspecialchars($product['product_name']) ?>" required
                            placeholder="กรอกชื่อสินค้า">
                    </div>
                    <div class="col-md-3">
                        <label for="price" class="form-label">ราคา (บาท)</label>
                        <input type="number" step="0.01" name="price" id="price" class="form-control"
                            value="<?= htmlspecialchars($product['price']) ?>" required
                            placeholder="0.00">
                    </div>
                    <div class="col-md-3">
                        <label for="stock" class="form-label">จำนวนในคลัง</label>
                        <input type="number" name="stock" id="stock" class="form-control" 
                            value="<?= htmlspecialchars($product['stock']) ?>" required
                            placeholder="0">
                    </div>
                    <div class="col-md-6">
                        <label for="category_id" class="form-label">หมวดหมู่</label>
                        <select name="category_id" id="category_id" class="form-select" required>
                            <option value="">เลือกหมวดหมู่</option>
                            <?php foreach ($categories as $cat): ?>
                                <option value="<?= htmlspecialchars($cat['category_id']) ?>"
                                    <?= $cat['category_id'] == $product['category_id'] ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($cat['category_name']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">รูปปัจจุบัน</label>
                        <div class="image-preview">
                            <?php if (!empty($product['image'])): ?>
                                <img src="../product_images/<?= htmlspecialchars($product['image']) ?>"
                                    width="120" height="120" class="current-image mb-2 d-block mx-auto">
                                <small class="text-muted">รูปปัจจุบัน</small>
                            <?php else: ?>
                                <i class="bi bi-image text-muted" style="font-size: 3rem;"></i>
                                <p class="text-muted mb-0 mt-2">ไม่มีรูปภาพ</p>
                            <?php endif; ?>
                        </div>
                        <input type="hidden" name="old_image" value="<?= htmlspecialchars($product['image']) ?>">
                    </div>
                    <div class="col-12">
                        <label for="description" class="form-label">รายละเอียดสินค้า</label>
                        <textarea name="description" id="description" class="form-control" rows="4" 
                            placeholder="กรอกรายละเอียดสินค้า"><?= htmlspecialchars($product['description']) ?></textarea>
                    </div>
                    <div class="col-md-6">
                        <label for="product_image" class="form-label">อัปโหลดรูปใหม่</label>
                        <input type="file" name="product_image" id="product_image" class="form-control" accept="image/*">
                        <div class="form-text">
                            <i class="bi bi-info-circle"></i> รองรับไฟล์: JPG, PNG (ขนาดไม่เกิน 5MB)
                        </div>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">ตัวเลือกรูปภาพ</label>
                        <div class="form-check mt-3">
                            <input class="form-check-input" type="checkbox" name="remove_image" id="remove_image" value="1">
                            <label class="form-check-label" for="remove_image">
                                <i class="bi bi-x-circle text-danger"></i> ลบรูปเดิม
                            </label>
                        </div>
                        <div class="form-text text-muted">หากต้องการลบรูปเดิมทั้งหมด</div>
                    </div>
                    <div class="col-12 text-center mt-4">
                        <button type="submit" class="btn btn-primary btn-lg">
                            <i class="bi bi-check-circle"></i> บันทึกการแก้ไข
                        </button>
                        <a href="products.php" class="btn btn-secondary btn-lg ms-2">
                            <i class="bi bi-x-circle"></i> ยกเลิก
                        </a>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <?php require_once 'footer_admin.php'; ?>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/js/bootstrap.bundle.min.js" integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous"></script>
</body>

</html>