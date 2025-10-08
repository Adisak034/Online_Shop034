<?php
session_start();
require_once 'session_timeout.php';
require 'config.php';
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}
$user_id = $_SESSION['user_id'];
$errors = [];
$success = "";
// ดึงข้อมูลสมาชิก
$stmt = $conn->prepare("SELECT * FROM users WHERE user_id = ?");
$stmt->execute([$user_id]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

// ดึงข้อมูล Wishlist
$wishlist_stmt = $conn->prepare("
    SELECT p.product_id, p.product_name, p.price, p.image 
    FROM wishlist w
    JOIN products p ON w.product_id = p.product_id
    WHERE w.user_id = ?
    ORDER BY w.created_at DESC
");
$wishlist_stmt->execute([$user_id]);
$wishlisted_items = $wishlist_stmt->fetchAll(PDO::FETCH_ASSOC);

// ดึงข้อมูลรีวิวของผู้ใช้
$reviews_stmt = $conn->prepare("
    SELECT r.review_id, r.rating, r.comment, r.created_at, p.product_id, p.product_name, p.image
    FROM reviews r
    JOIN products p ON r.product_id = p.product_id
    WHERE r.user_id = ?
    ORDER BY r.created_at DESC
");
$reviews_stmt->execute([$user_id]);
$user_reviews = $reviews_stmt->fetchAll(PDO::FETCH_ASSOC);

// เมื่อมีการส่งฟอร์มแก้ไขข้อมูล
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_profile'])) {
    $full_name = trim($_POST['full_name']);
    $email = trim($_POST['email']);
    $current_password = $_POST['current_password'] ?? '';
    $new_password = $_POST['new_password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';
    // ตรวจสอบชอื่ และอเีมลไมว่ ำ่ ง
    if (empty($full_name) || empty($email)) {
        $errors[] = "กรุณากรอกชื่อ-นามสกุลและอีเมล";
    }
    // ตรวจสอบอเีมลซ ้ำ
    $stmt = $conn->prepare("SELECT * FROM users WHERE email = ? AND user_id != ?");
    $stmt->execute([$email, $user_id]);
    if ($stmt->rowCount() > 0) {
        $errors[] = "อีเมลนี้ถูกใช้งานแล้ว";
    }
    // ตรวจสอบกำรเปลี่ยนรหัสผ่ำน (ถ ้ำมี)
    if (!empty($current_password) || !empty($new_password) || !empty($confirm_password)) {
        if (!password_verify($current_password, $user['password'])) {
            $errors[] = "รหัสผ่านเดิมไม่ถูกต้อง";
        } elseif (strlen($new_password) < 6) {
            $errors[] = "รหัสผ่านใหม่ต้องมีอย่างน้อย 6 ตัวอักษร";
        } elseif ($new_password !== $confirm_password) {
            $errors[] = "รหัสผ่านใหม่และการยืนยันไม่ตรงกัน";
        } else {
            $new_hashed = password_hash($new_password, PASSWORD_DEFAULT);
        }
    }
    // อัปเดตข ้อมูลหำกไม่มี error
    if (empty($errors)) {
        if (!empty($new_hashed)) {
            $stmt = $conn->prepare("UPDATE users SET full_name = ?, email = ?, password = ? WHERE user_id = ?");
            $stmt->execute([$full_name, $email, $new_hashed, $user_id]);
        } else {
            $stmt = $conn->prepare("UPDATE users SET full_name = ?, email = ? WHERE user_id = ?");
            $stmt->execute([$full_name, $email, $user_id]);
        }
        $success = "บันทึกข้อมูลเรียบร้อยแล้ว";
        // อัปเดต session หำกจ ำเป็น
        $_SESSION['username'] = $user['username'];
        $user['full_name'] = $full_name;
        $user['email'] = $email;
    }
}
?>
<!DOCTYPE html>
<html lang="th">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>โปรไฟล์ของฉัน - BoboIT Shop</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css" rel="stylesheet">
    <style>
        body {
            background-color: #f8f9fa;
        }

        .profile-card {
            border-radius: 15px;
        }

        .nav-pills .nav-link {
            border-radius: 10px;
        }

        .nav-pills .nav-link.active {
            background-color: #007bff;
        }

        .list-group-item img {
            width: 60px;
            height: 60px;
            object-fit: contain;
            background-color: #f8f9fa;
            border: 1px solid #dee2e6;
            border-radius: 8px;
        }

        .rating i {
            color: #ffc107;
        }
    </style>
</head>

<body>
    <!-- Navigation Bar -->
    <?php require_once 'navbar.php'; ?>
    <div class="container mt-4">
        <div class="card profile-card shadow-sm">
            <div class="card-body p-4">
                <h2 class="mb-4">โปรไฟล์ของฉัน</h2>

                <ul class="nav nav-pills mb-4" id="pills-tab" role="tablist">
                    <li class="nav-item" role="presentation">
                        <button class="nav-link active" id="pills-profile-tab" data-bs-toggle="pill" data-bs-target="#pills-profile" type="button" role="tab" aria-controls="pills-profile" aria-selected="true"><i class="bi bi-person-fill"></i> แก้ไขข้อมูลส่วนตัว</button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link" id="pills-wishlist-tab" data-bs-toggle="pill" data-bs-target="#pills-wishlist" type="button" role="tab" aria-controls="pills-wishlist" aria-selected="false"><i class="bi bi-heart-fill"></i> รายการโปรด</button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link" id="pills-reviews-tab" data-bs-toggle="pill" data-bs-target="#pills-reviews" type="button" role="tab" aria-controls="pills-reviews" aria-selected="false"><i class="bi bi-star-fill"></i> รีวิวของฉัน</button>
                    </li>
                </ul>

                <div class="tab-content" id="pills-tabContent">
                    <!-- Edit Profile Tab -->
                    <div class="tab-pane fade show active" id="pills-profile" role="tabpanel" aria-labelledby="pills-profile-tab">
                        <?php if (!empty($errors)) : ?>
                            <div class="alert alert-danger">
                                <?php foreach ($errors as $e) : ?>
                                    <div><?= htmlspecialchars($e) ?></div>
                                <?php endforeach; ?>
                            </div>
                        <?php elseif (!empty($success)) : ?>
                            <div class="alert alert-success"><?= $success ?></div>
                        <?php endif; ?>
                        <form method="post" class="row g-3">
                            <div class="col-md-6">
                                <label for="username" class="form-label">ชื่อผู้ใช้</label>
                                <input type="text" id="username" class="form-control" value="<?= htmlspecialchars($user['username']) ?>" disabled readonly>
                            </div>
                            <div class="col-md-6">
                                <label for="full_name" class="form-label">ชื่อ-นามสกุล</label>
                                <input type="text" name="full_name" id="full_name" class="form-control" required value="<?= htmlspecialchars($user['full_name']) ?>">
                            </div>
                            <div class="col-12">
                                <label for="email" class="form-label">อีเมล</label>
                                <input type="email" name="email" id="email" class="form-control" required value="<?= htmlspecialchars($user['email']) ?>">
                            </div>
                            <div class="col-12">
                                <hr>
                                <h5>เปลี่ยนรหัสผ่าน (หากไม่ต้องการเปลี่ยน ให้เว้นว่างไว้)</h5>
                            </div>
                            <div class="col-md-4">
                                <label for="current_password" class="form-label">รหัสผ่านปัจจุบัน</label>
                                <input type="password" name="current_password" id="current_password" class="form-control">
                            </div>
                            <div class="col-md-4">
                                <label for="new_password" class="form-label">รหัสผ่านใหม่</label>
                                <input type="password" name="new_password" id="new_password" class="form-control">
                            </div>
                            <div class="col-md-4">
                                <label for="confirm_password" class="form-label">ยืนยันรหัสผ่านใหม่</label>
                                <input type="password" name="confirm_password" id="confirm_password" class="form-control">
                            </div>
                            <div class="col-12">
                                <button type="submit" name="update_profile" class="btn btn-primary">บันทึกการเปลี่ยนแปลง</button>
                            </div>
                        </form>
                    </div>

                    <!-- Wishlist Tab -->
                    <div class="tab-pane fade" id="pills-wishlist" role="tabpanel" aria-labelledby="pills-wishlist-tab">
                        <?php if (empty($wishlisted_items)) : ?>
                            <div class="alert alert-info">คุณยังไม่มีสินค้าในรายการโปรด</div>
                        <?php else : ?>
                            <ul class="list-group">
                                <?php foreach ($wishlisted_items as $item) : ?>
                                    <li class="list-group-item d-flex justify-content-between align-items-center">
                                        <div class="d-flex align-items-center">
                                            <img src="<?= !empty($item['image']) ? 'product_images/' . rawurlencode($item['image']) : 'product_images/no-image.jpg' ?>" alt="<?= htmlspecialchars($item['product_name']) ?>" class="me-3">
                                            <div>
                                                <h6 class="mb-0"><?= htmlspecialchars($item['product_name']) ?></h6>
                                                <small class="text-muted">ราคา: <?= number_format($item['price'], 2) ?> บาท</small>
                                            </div>
                                        </div>
                                        <div>
                                            <a href="product_detail.php?id=<?= $item['product_id'] ?>" class="btn btn-sm btn-outline-primary" title="ดูรายละเอียด"><i class="bi bi-eye"></i></a>
                                            <a href="toggle_wishlist.php?product_id=<?= $item['product_id'] ?>&redirect=profile" class="btn btn-sm btn-outline-danger" title="ลบออกจากรายการโปรด"><i class="bi bi-trash"></i></a>
                                        </div>
                                    </li>
                                <?php endforeach; ?>
                            </ul>
                        <?php endif; ?>
                    </div>

                    <!-- Reviews Tab -->
                    <div class="tab-pane fade" id="pills-reviews" role="tabpanel" aria-labelledby="pills-reviews-tab">
                        <?php if (empty($user_reviews)) : ?>
                            <div class="alert alert-info">คุณยังไม่เคยรีวิวสินค้า</div>
                        <?php else : ?>
                            <ul class="list-group">
                                <?php foreach ($user_reviews as $review) : ?>
                                    <li class="list-group-item">
                                        <div class="d-flex justify-content-between align-items-center">
                                            <div class="d-flex align-items-center">
                                                <img src="<?= !empty($review['image']) ? 'product_images/' . rawurlencode($review['image']) : 'product_images/no-image.jpg' ?>" alt="<?= htmlspecialchars($review['product_name']) ?>" class="me-3">
                                                <div>
                                                    <h6 class="mb-1"><?= htmlspecialchars($review['product_name']) ?></h6>
                                                    <div class="rating">
                                                        <?php for ($i = 0; $i < $review['rating']; $i++) : ?><i class="bi bi-star-fill"></i><?php endfor; ?>
                                                        <?php for ($i = 0; $i < 5 - $review['rating']; $i++) : ?><i class="bi bi-star"></i><?php endfor; ?>
                                                    </div>
                                                </div>
                                            </div>
                                            <small class="text-muted"><?= date('d/m/Y', strtotime($review['created_at'])) ?></small>
                                        </div>
                                        <?php if (!empty($review['comment'])) : ?>
                                            <p class="mt-2 mb-0 fst-italic">"<?= nl2br(htmlspecialchars($review['comment'])) ?>"</p>
                                        <?php endif; ?>
                                        <div class="text-end mt-2">
                                            <a href="product_detail.php?id=<?= $review['product_id'] ?>&edit_review=<?= $review['review_id'] ?>#reviews-section" class="btn btn-sm btn-outline-secondary">แก้ไขรีวิว</a>
                                        </div>
                                    </li>
                                <?php endforeach; ?>
                            </ul>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <?php require_once 'footer.php'; ?>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>