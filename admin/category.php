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
    <title>จัดการหมวดหมู่</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>

<body class="container mt-4">
    <h2>จัดการหมวดหมู่สินค้า</h2>
    <?php if (isset($_SESSION['error'])): ?>
        <div class="alert alert-danger"><?= $_SESSION['error'] ?></div>
        <?php unset($_SESSION['error']); ?>
    <?php endif; ?>
    <?php if (isset($_SESSION['success'])): ?>
        <div class="alert alert-success"><?= $_SESSION['success'] ?></div>
        <?php unset($_SESSION['success']); ?>
    <?php endif; ?>
    <a href="index.php" class="btn btn-secondary mb-3">← กลับหน้าผู้ดูแล</a>
    <form method="post" class="row g-3 mb-4">
        <div class="col-md-6">
            <input type="text" name="category_name" class="form-control" placeholder="ชื่อหมวดหมู่" required>
        </div>
        <div class="col-md-2">
            <button type="submit" name="add_category" class="btn btn-primary">เพิ่มหมวดหมู่</button>
        </div>
    </form>
    <h5>รายการหมวดหมู่</h5>
    <table class="table table-bordered">
        <thead>
            <tr>
                <th>ชื่อหมวดหมู่</th>
                <th>แก้ไขชื่อ</th>
                <th>จัดการ</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($categories as $cat): ?>
                <tr>
                    <td><?= htmlspecialchars($cat['category_name']) ?></td>
                    <td>
                        <form method="post" class="d-flex">
                            <input type="hidden" name="category_id" value="<?= $cat['category_id'] ?>">
                            <input type="text" name="new_name" class="form-control me-2" placeholder="ชื่อใหม่"
                                required>
                            <button type="submit" name="update_category" class="btn btn-sm btn-warning">แก้ไข
                            </button>
                        </form>
                    </td>
                    <td>
                        <a href="category.php?delete=<?= $cat['category_id'] ?>" class="btn btn-sm btn-danger"
                            onclick="return confirm('คุณต้องการลบหมวดหมู่นี้หรือไม่?')">ลบ</a>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</body>

</html>