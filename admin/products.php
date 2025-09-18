<?php
require '../config.php'; // TODO: เชื่อมต่อฐานข้อมูลด้วย pdo
require 'auth_admin.php'; // TODO: การ์ดสิทธิ์(Admin Guard)
// แนวทาง: ถ้า !isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin' -> redirect ไป ../login.php แล้ว exit;
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../login.php");
    exit;
}
// เพิ่มสินค้าใหม่
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_product'])) {
    $name = trim($_POST['product_name']);
    $description = trim($_POST['description']);
    $price = floatval($_POST['price']); // floatval() ใชแปลงเป็น ้ float
    $stock = intval($_POST['stock']); // intval() ใชแ้ปลงเป็น integer
    $category_id = intval($_POST['category_id']);
    // ค่าที่ได้จากฟอร์มเป็น string เสมอ
    if ($name && $price > 0) { // ตรวจสอบชื่อและราคา
        $imageName = null;
        if (!empty($_FILES['product_image']['name'])) {
            $file = $_FILES['product_image'];
            $allowed = ['image/jpeg', 'image/png'];
            if (in_array($file['type'], $allowed)) {
                $ext = pathinfo($file['name'], PATHINFO_EXTENSION);
                $imageName = 'product_' . time() . '.' . $ext;
                $path = __DIR__ . '/../product_images/' . $imageName;
                move_uploaded_file($file['tmp_name'], $path);
            }
        }
        // เพิ่มสินค้าใหม่
        $stmt = $conn->prepare("INSERT INTO products (product_name, description, price, stock, category_id, image)
        VALUES (?, ?, ?, ?, ?, ?)");
        $stmt->execute([$name, $description, $price, $stock, $category_id, $imageName]);
        header("Location: products.php");
        exit;
    }
}

// ลบสินค้า
// if (isset($_GET['delete'])) {
//     $product_id = $_GET['delete'];
//     $stmt = $conn->prepare("DELETE FROM products WHERE product_id = ?");
//     $stmt->execute([$product_id]);
//     header("Location: products.php");
//     exit;
// }
// ลบสินค้าคำ (ลบไฟล์รูปด้วย)
if (isset($_GET['delete'])) {
    $product_id = (int)$_GET['delete']; // แคสต์เป็น int
    // 1) ดงึชอื่ ไฟลร์ปู จำก DB ก่อน
    $stmt = $conn->prepare("SELECT image FROM products WHERE product_id = ?");
    $stmt->execute([$product_id]);
    $imageName = $stmt->fetchColumn(); // null ถ ้ำไม่มีรูป
    // 2) ลบใน DB ด้วย Transaction
    try {
        $conn->beginTransaction();
        $del = $conn->prepare("DELETE FROM products WHERE product_id = ?");
        $del->execute([$product_id]);
        $conn->commit();
    } catch (Exception $e) {
        $conn->rollBack();
        // ใส่ flash message หรือ log ได้ตามต้องการ
        header("Location: products.php");
        exit;
    }
    // 3) ลบไฟล์รูปหลัง DB ลบสำเร็จ
    if ($imageName) {
        $baseDir = realpath(__DIR__ . '/../product_images'); // โฟลเดอร์เก็บรูป
        $filePath = realpath($baseDir . '/' . $imageName);
        // กัน path traversal: ต้องอยู่ใต้ $baseDir จริง ๆ
        if ($filePath && strpos($filePath, $baseDir) === 0 && is_file($filePath)) {
            @unlink($filePath); // ใช ้@ กัน warning ถำ้ลบไมส่ ำเร็จ   
        }
    }
    header("Location: products.php");
    exit;
}

$stmt = $conn->query("SELECT p.*, c.category_name FROM products p LEFT JOIN categories c ON
p.category_id = c.category_id ORDER BY p.created_at DESC");
$products = $stmt->fetchAll(PDO::FETCH_ASSOC);
// ดึงหมวดหมู่
$categories = $conn->query("SELECT * FROM categories")->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="th">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>จัดการสินค้า</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/css/bootstrap.min.css" rel="stylesheet"
        integrity="sha384-LN+7fdVzj6u52u30Kp6M/trliBMCMKTyK833zpbD+pXdCLuTusPj697FH4R/5mcr" crossorigin="anonymous">
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
            box-shadow: 0 8px 25px rgba(0, 0, 0, 0.15);
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
            <a class="navbar-brand" href="index.php">
                <i class="bi bi-shield-check"></i> ระบบผู้ดูแลระบบ
            </a>
            <div class="navbar-nav ms-auto">
                <div class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                        <i class="bi bi-person-circle"></i> <?= htmlspecialchars($_SESSION['username']) ?>
                    </a>
                    <ul class="dropdown-menu">
                        <li><span class="dropdown-item-text">ผู้ดูแลระบบ</span></li>
                        <li>
                            <hr class="dropdown-divider">
                        </li>
                        <li><a class="dropdown-item" href="../index.php">
                                <i class="bi bi-house"></i> กลับหน้าหลัก
                            </a></li>
                        <li><a class="dropdown-item" href="index.php">
                                <i class="bi bi-speedometer2"></i> Dashboard
                            </a></li>
                        <li>
                            <hr class="dropdown-divider">
                        </li>
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
                            <i class="bi bi-box-seam"></i> จัดการสินค้า
                        </h1>
                        <p class="lead text-muted">
                            จัดการข้อมูลสินค้าในระบบ - <strong><?= htmlspecialchars($_SESSION['username']) ?></strong>
                        </p>
                        <div class="badge bg-success fs-6">
                            <i class="bi bi-shop"></i> Product Management
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Add Product Form -->
        <div class="row mb-5">
            <div class="col-12">
                <div class="card admin-card shadow-lg">
                    <div class="card-header bg-success text-white">
                        <h5 class="mb-0">
                            <i class="bi bi-plus-circle"></i> เพิ่มสินค้าใหม่
                        </h5>
                    </div>
                    <form method="post" enctype="multipart/form-data" class="row g-3 mb-4">

                        <div class="card-body">
                            <form method="post" class="row g-3">
                                <div class="col-md-6">
                                    <label for="product_name" class="form-label">ชื่อสินค้า</label>
                                    <input type="text" name="product_name" id="product_name" class="form-control"
                                        placeholder="ชื่อสินค้า" required>
                                </div>
                                <div class="col-md-3">
                                    <label for="price" class="form-label">ราคา</label>
                                    <input type="number" step="0.01" name="price" id="price" class="form-control"
                                        placeholder="0.00" required>
                                </div>
                                <div class="col-md-3">
                                    <label for="stock" class="form-label">จำนวน</label>
                                    <input type="number" name="stock" id="stock" class="form-control" placeholder="0"
                                        required>
                                </div>
                                <div class="col-12">
                                    <label for="category_id" class="form-label">หมวดหมู่</label>
                                    <select name="category_id" id="category_id" class="form-select" required>
                                        <option value="">เลือกหมวดหมู่</option>
                                        <?php foreach ($categories as $cat): ?>
                                            <option value="<?= $cat['category_id'] ?>">
                                                <?= htmlspecialchars($cat['category_name']) ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                <div class="col-12">
                                    <label for="description" class="form-label">รายละเอียดสินค้า</label>
                                    <textarea name="description" id="description" class="form-control" rows="3"
                                        placeholder="รายละเอียดสินค้า"></textarea>
                                </div>
                                <form method="post" enctype="multipart/form-data" class="row g-3 mb-4">
                                    <div class="col-md-6">
                                        <label class="form-label">รปู สนิ คำ้ (jpg, png)</label>
                                        <input type="file" name="product_image" class="form-control">
                                    </div>
                                    <div class="col-12">
                                        <button type="submit" name="add_product" class="btn btn-success btn-lg">
                                            <i class="bi bi-plus-circle"></i> เพิ่มสินค้า
                                        </button>
                                    </div>
                                </form>
                        </div>
                </div>
            </div>
        </div>
        <!-- Products Table Section -->
        <div class="row">
            <div class="col-12">
                <?php if (count($products) === 0): ?>
                    <div class="card admin-card shadow-lg">
                        <div class="card-body text-center py-5">
                            <div class="admin-icon text-muted">
                                <i class="bi bi-inbox"></i>
                            </div>
                            <h3 class="text-muted">ยังไม่มีสินค้าในระบบ</h3>
                            <p class="text-muted">เพิ่มสินค้าใหม่โดยใช้ฟอร์มด้านบน</p>
                        </div>
                    </div>
                <?php else: ?>
                    <div class="card admin-card shadow-lg">
                        <div class="card-header bg-primary text-white">
                            <div class="d-flex justify-content-between align-items-center">
                                <h5 class="mb-0">
                                    <i class="bi bi-list-ul"></i> รายการสินค้า
                                </h5>
                                <div class="d-flex gap-3">
                                    <div class="badge bg-light text-dark fs-6">
                                        <i class="bi bi-box-seam"></i> ทั้งหมด: <?= count($products) ?> รายการ
                                    </div>
                                    <div class="badge bg-success fs-6">
                                        <i class="bi bi-check-circle"></i> สินค้าใหม่วันนี้:
                                        <?php
                                        $today = date('Y-m-d');
                                        $newToday = array_filter($products, function ($product) use ($today) {
                                            return date('Y-m-d', strtotime($product['created_at'])) === $today;
                                        });
                                        echo count($newToday);
                                        ?>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="card-body p-0">
                            <div class="table-responsive">
                                <table class="table table-hover mb-0">
                                    <thead class="table-dark">
                                        <tr>
                                            <th scope="col">
                                                <i class="bi bi-box"></i> ชื่อสินค้า
                                            </th>
                                            <th scope="col">
                                                <i class="bi bi-tags"></i> หมวดหมู่
                                            </th>
                                            <th scope="col">
                                                <i class="bi bi-currency-dollar"></i> ราคา
                                            </th>
                                            <th scope="col">
                                                <i class="bi bi-file-earmark-text"></i> รูปสินค้า
                                            </th>
                                            <th scope="col">
                                                <i class="bi bi-boxes"></i> คงเหลือ
                                            </th>
                                            <th scope="col" class="text-center">
                                                <i class="bi bi-gear"></i> จัดการ
                                            </th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($products as $p): ?>
                                            <tr>
                                                <td>
                                                    <div>
                                                        <strong><?= htmlspecialchars($p['product_name']) ?></strong>
                                                        <br>
                                                        <small class="text-muted">ID: <?= $p['product_id'] ?></small>
                                                    </div>
                                                </td>
                                                <td>
                                                    <span class="badge bg-secondary">
                                                        <?= htmlspecialchars($p['category_name'] ?? 'ไม่มีหมวดหมู่') ?>
                                                    </span>
                                                </td>
                                                <td>
                                                    <span class="fw-bold text-success">
                                                        <?= number_format($p['price'], 2) ?> บาท
                                                    </span>
                                                </td>
                                                <th><img src="../product_images/<?= htmlspecialchars($p['image']) ?>" alt="<?= htmlspecialchars($p['product_name']) ?>" class="img-thumbnail" width="100"></th>
                                                <td>
                                                    <?php if ($p['stock'] > 10): ?>
                                                        <span class="badge bg-success"><?= $p['stock'] ?> ชิ้น</span>
                                                    <?php elseif ($p['stock'] > 0): ?>
                                                        <span class="badge bg-warning"><?= $p['stock'] ?> ชิ้น</span>
                                                    <?php else: ?>
                                                        <span class="badge bg-danger">หมด</span>
                                                    <?php endif; ?>
                                                </td>
                                                <td class="text-center">
                                                    <div class="btn-group" role="group">
                                                    </div> <a href="edit_products.php?id=<?= $p['product_id'] ?>"
                                                        class="btn btn-sm btn-outline-warning" title="แก้ไขสินค้า">
                                                        <i class="bi bi-pencil"></i>
                                                    </a>

                                                    <a href="products.php?delete=<?= $p['product_id'] ?>"
                                                        class="btn btn-sm btn-outline-danger" title="ลบสินค้า"
                                                        onclick="return confirm('ต้องการลบสินค้า <?= htmlspecialchars($p['product_name']) ?> ?\n\nการดำเนินการนี้ไม่สามารถย้อนกลับได้!')">
                                                        <i class="bi bi-trash"></i>
                                                    </a>
                            </div>
                            </td>
                            </tr>
                        <?php endforeach; ?>
                        </tbody>
                        </table>
                        </div>
                    </div>
                    <div class="card-footer bg-light text-muted text-center">
                        <small>
                            <i class="bi bi-info-circle"></i>
                            สามารถแก้ไขหรือลบข้อมูลสินค้าได้ตามต้องการ
                        </small>
                    </div>
            </div>
        <?php endif; ?>
        </div>
    </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/js/bootstrap.bundle.min.js"
        integrity="sha384-ndDqU0Gzau9qJ1lfW4pNLlhNTkCfHzAVBReH9diLvGRem5+R9g2FzA8ZGN954O5Q" crossorigin="anonymous">
    </script>
</body>

</html>