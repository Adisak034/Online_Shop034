<?php
require '../config.php'; // TODO: เชื่อมต่อฐานข้อมูลด้วย PDO
require 'auth_admin.php'; // TODO: การ์ดสิทธิ์(Admin Guard)
// แนวทาง: ถ้า !isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin' -> redirect ไป ../login.php แล้ว exit;
// เพิ่มหมวดหมู่
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_category'])) {
    $category_name = trim($_POST['category_name']);
    if ($category_name) {
        $stmt = $conn->prepare("INSERT INTO categories  (category_name) VALUES (?)");
        $stmt->execute([$category_name]);
        $_SESSION['success'] = "เพิ่มหมวดหมู่เรียบร้อยแล้ว";
        header("Location: category.php");
        exit;
    }
}
// ลบหมวดหมู่ (แบบไม่มีการตรวจสอบว่ามีสินค้ในหมวดหมู่หรือไม่)
// ลบหมวดหมู่
// ตรวจสอบวำ่ หมวดหมนู่ ี้ยังถกู ใชอ้ยหู่ รอื ไม่
if (isset($_GET['delete'])) {
$category_id = $_GET['delete'];
// ตรวจสอบว่าหมวดหมู่ยังถูกใช้งานอยู่หรือไม่
$stmt = $conn->prepare("SELECT COUNT(*) FROM products WHERE category_id = ?");
$stmt->execute([$category_id]);
$productCount = $stmt->fetchColumn();
if ($productCount > 0) {
// ถ้าหมวดหมู่ยังถูกใช้งานอยู่  
$_SESSION['error'] = "ไม่สามารถลบหมวดหมู่ได้เนื่องจากยังมีสินค้าที่ใช้งานหมวดหมู่นี้อยู่";
} else {
// ถ้าหมวดหมู่ไม่มีสินค้าที่ใช้งานอยู่
$stmt = $conn->prepare("DELETE FROM categories WHERE category_id = ?");
$stmt->execute([$category_id]);
$_SESSION['success'] = "ลบหมวดหมู่เรียบร้อยแล้ว";
}
header("Location: category.php");
exit;
}

// แก้ไขหมวดหมู่
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_category'])) {
    $category_id = $_POST['category_id'];
    $category_name = trim($_POST['new_name']);
    if ($category_name) {
        $stmt = $conn->prepare("UPDATE categories SET category_name = ? WHERE category_id = ?");
        $stmt->execute([$category_name, $category_id]);
        $_SESSION['success'] = "แก้ไขหมวดหมู่เรียบร้อยแล้ว";
        header("Location: category.php");
        exit;
    }
}
// ดึงหมวดหมู่ทั้งหมด

$categories = $conn->query("SELECT * FROM categories ORDER BY category_id ASC")->fetchAll(PDO::FETCH_ASSOC);

// โค้ดนี้เขียนต่อกันยาวบรรทัดเดียวได้เพราะ ผลลัพธ์จากเมธอดหนึ่งสามารถส่งต่อ (chaining) ให้เมธอดถัดไปทันที โดยไม่ต้อง
// แยกตัวแปรเก็บไว้ก่อน
// $conn->query("...")->fetchAll(...);
// หากเขียนแยกเป็นหลายบรรทัดจะเป็นแบบนี้:
// $stmt = $conn->query("SELECT * FROM categories ORDER BY category_id ASC");
// $categories = $stmt->fetchAll(PDO::FETCH_ASSOC);
// ควรเขียนแยกบรรทัดเมื่อจะ ใช้$stmt ซ้ำหลายครั้ง (เช่น fetch ทีละ row, ตรวจจำนวนแถว)
// หรือเขียนแบบ prepare , execute
// $stmt = $conn->prepare("SELECT * FROM categories ORDER BY category_id ASC");
// $stmt->execute();
// $categories = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>จัดการหมวดหมู่</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-LN+7fdVzj6u52u30Kp6M/trliBMCMKTyK833zpbD+pXdCLuTusPj697FH4R/5mcr" crossorigin="anonymous">
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
            box-shadow: 0 8px 25px rgba(0,0,0,0.15);
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
                    <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown">
                        <i class="bi bi-person-circle"></i> <?= htmlspecialchars($_SESSION['username']) ?>
                    </a>
                    <ul class="dropdown-menu">
                        <li><span class="dropdown-item-text">ผู้ดูแลระบบ</span></li>
                        <li><hr class="dropdown-divider"></li>
                        <li><a class="dropdown-item" href="../index.php">
                            <i class="bi bi-house"></i> กลับหน้าหลัก
                        </a></li>
                        <li><a class="dropdown-item" href="index.php">
                            <i class="bi bi-speedometer2"></i> Dashboard
                        </a></li>
                        <li><hr class="dropdown-divider"></li>
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
                            <i class="bi bi-tags"></i> จัดการหมวดหมู่
                        </h1>
                        <p class="lead text-muted">
                            จัดการหมวดหมู่สินค้าในระบบ - <strong><?= htmlspecialchars($_SESSION['username']) ?></strong>
                        </p>
                        <div class="badge bg-info fs-6">
                            <i class="bi bi-collection"></i> Category Management
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Alert Messages -->
        <?php if (isset($_SESSION['error'])): ?>
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <i class="bi bi-exclamation-triangle"></i> <?= $_SESSION['error'] ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
            <?php unset($_SESSION['error']); ?>
        <?php endif; ?>
        <?php if (isset($_SESSION['success'])): ?>
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <i class="bi bi-check-circle"></i> <?= $_SESSION['success'] ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
            <?php unset($_SESSION['success']); ?>
        <?php endif; ?>

        <!-- Add Category Form -->
        <div class="row mb-5">
            <div class="col-12">
                <div class="card admin-card shadow-lg">
                    <div class="card-header bg-info text-white">
                        <h5 class="mb-0">
                            <i class="bi bi-plus-circle"></i> เพิ่มหมวดหมู่ใหม่
                        </h5>
                    </div>
                    <div class="card-body">
                        <form method="post" class="row g-3">
                            <div class="col-md-8">
                                <label for="category_name" class="form-label">ชื่อหมวดหมู่</label>
                                <input type="text" name="category_name" id="category_name" class="form-control" placeholder="ชื่อหมวดหมู่" required>
                            </div>
                            <div class="col-md-4 d-flex align-items-end">
                                <button type="submit" name="add_category" class="btn btn-info btn-md w-100">
                                    <i class="bi bi-plus-circle"></i> เพิ่มหมวดหมู่
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
        <!-- Categories Table -->
        <div class="row">
            <div class="col-12">
                <div class="card admin-card shadow-lg">
                    <div class="card-header bg-primary text-white">
                        <h5 class="mb-0">
                            <i class="bi bi-list-ul"></i> รายการหมวดหมู่ทั้งหมด
                        </h5>
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-hover mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th class="py-3">
                                            <i class="bi bi-hash"></i> ID
                                        </th>
                                        <th class="py-3">
                                            <i class="bi bi-tag"></i> ชื่อหมวดหมู่
                                        </th>
                                        <th class="py-3">
                                            <i class="bi bi-pencil"></i> แก้ไขชื่อ
                                        </th>
                                        <th class="py-3 text-center">
                                            <i class="bi bi-gear"></i> จัดการ
                                        </th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($categories as $cat): ?>
                                        <tr>
                                            <td class="py-3">
                                                <span class="badge bg-secondary fs-6"><?= $cat['category_id'] ?></span>
                                            </td>
                                            <td class="py-3">
                                                <div>
                                                    <h6 class="mb-0"><?= htmlspecialchars($cat['category_name']) ?></h6>
                                                    <small class="text-muted">หมวดหมู่ #<?= $cat['category_id'] ?></small>
                                                </div>
                                            </td>
                                            <td class="py-3 ">
                                                <form method="post" class="d-flex">
                                                    <input type="hidden" name="category_id" value="<?= $cat['category_id'] ?>">
                                                    <input type="text" name="new_name" class="form-control form-control-sm me-2" 
                                                           placeholder="ชื่อใหม่" required style="max-width: 400px;">
                                                    <button type="submit" name="update_category" class="btn btn-outline-warning btn-sm">
                                                        <i class="bi bi-check"></i> แก้ไข
                                                    </button>
                                                </form>
                                            </td>
                                            <td class="py-3 text-center">
                                                <a href="category.php?delete=<?= $cat['category_id'] ?>" 
                                                   class="btn btn-outline-danger btn-sm"
                                                   onclick="return confirm('คุณต้องการลบหมวดหมู่นี้หรือไม่?')">
                                                    <i class="bi bi-trash"></i> ลบ
                                                </a>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <br>
    <br>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>