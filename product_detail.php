<?php
session_start();
require_once 'session_timeout.php';
require_once 'config.php';
// ตรวจสอบว่ามี id ส่งมาหรือไม่ ถ้าไม่มีให้กลับไปหน้าหลัก
if (!isset($_GET['id'])) {
    header("Location: index.php");
    exit();
}

// ดึงข้อมูลสินค้าจาก id ที่ส่งมา
$product_id = $_GET['id'];
$stmt = $conn->prepare("SELECT p.*, c.category_name
FROM products p
LEFT JOIN categories c ON p.category_id = c.category_id
WHERE p.product_id = ?");
$stmt->execute([$product_id]);
$product = $stmt->fetch(PDO::FETCH_ASSOC);

// ตรวจสอบสถานะการล็อกอิน
$isLoggedIn = isset($_SESSION['user_id']);
$user_id = $_SESSION['user_id'] ?? null;

// จัดการการส่งรีวิว
$review_error = '';
$review_success = '';
$edit_review_error = '';
if ($isLoggedIn && $_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submit_review'])) {
    $rating = (int)$_POST['rating'];
    $comment = trim($_POST['comment']);

    if ($rating < 1 || $rating > 5) {
        $review_error = "กรุณาให้คะแนน 1-5 ดาว";
    } else {
        // ตรวจสอบว่าเคยรีวิวสินค้านี้แล้วหรือยัง
        $stmt = $conn->prepare("SELECT 1 FROM reviews WHERE product_id = ? AND user_id = ?");
        $stmt->execute([$product_id, $user_id]);
        if ($stmt->fetch()) {
            $review_error = "คุณได้รีวิวสินค้านี้ไปแล้ว";
        } else {
            // เพิ่มรีวิวใหม่
            $stmt = $conn->prepare("INSERT INTO reviews (product_id, user_id, rating, comment) VALUES (?, ?, ?, ?)");
            $stmt->execute([$product_id, $user_id, $rating, $comment]);
            header("Location: product_detail.php?id=$product_id&review_success=1#reviews-section");
            exit();
        }
    }
}

// จัดการการแก้ไขรีวิว
if ($isLoggedIn && $_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_review'])) {
    $review_id_to_update = (int)$_POST['review_id'];
    $new_rating = (int)$_POST['rating'];
    $new_comment = trim($_POST['comment']);

    if ($new_rating < 1 || $new_rating > 5) {
        $edit_review_error = "กรุณาให้คะแนน 1-5 ดาว";
    } else {
        // ตรวจสอบว่าเป็นเจ้าของรีวิวจริงหรือไม่ก่อนอัปเดต
        $stmt = $conn->prepare("UPDATE reviews SET rating = ?, comment = ? WHERE review_id = ? AND user_id = ?");
        $stmt->execute([$new_rating, $new_comment, $review_id_to_update, $user_id]);
        header("Location: product_detail.php?id=$product_id&review_updated=1#reviews-section");
        exit();
    }
}

// จัดการการลบรีวิว
if ($isLoggedIn && isset($_GET['delete_review'])) {
    $review_id_to_delete = (int)$_GET['delete_review'];
    $stmt = $conn->prepare("DELETE FROM reviews WHERE review_id = ? AND user_id = ?");
    $stmt->execute([$review_id_to_delete, $user_id]);
    header("Location: product_detail.php?id=$product_id&review_deleted=1#reviews-section");
    exit();
}

// ดึงข้อมูลรีวิวทั้งหมดของสินค้าชิ้นนี้
$stmt = $conn->prepare("
    SELECT r.*, u.username 
    FROM reviews r 
    JOIN users u ON r.user_id = u.user_id 
    WHERE r.product_id = ? 
    ORDER BY r.created_at DESC
");
$stmt->execute([$product_id]);
$reviews = $stmt->fetchAll(PDO::FETCH_ASSOC);

// คำนวณคะแนนเฉลี่ยและจำนวนรีวิว
$total_reviews = count($reviews);
$average_rating = 0;
if ($total_reviews > 0) {
    $total_rating_sum = array_sum(array_column($reviews, 'rating'));
    $average_rating = $total_rating_sum / $total_reviews;
}

$user_has_reviewed = false;
if ($isLoggedIn) {
    $stmt = $conn->prepare("SELECT 1 FROM reviews WHERE product_id = ? AND user_id = ?");
    $stmt->execute([$product_id, $user_id]);
    $user_has_reviewed = (bool)$stmt->fetch();
}

?>
<!DOCTYPE html>
<html lang="th">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>รายละเอียดสินค้า - <?= isset($product['product_name']) ? htmlspecialchars($product['product_name']) : 'ไม่พบสินค้า' ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-LN+7fdVzj6u52u30Kp6M/trliBMCMKTyK833zpbD+pXdCLuTusPj697FH4R/5mcr" crossorigin="anonymous">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css" rel="stylesheet">
    <style>
        body {
            background: white;
            min-height: 100vh;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }

        .navbar {
            background: #007bff !important;
            backdrop-filter: blur(10px);
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
            z-index: 1030;
            position: relative;
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

        .display-6 {
            font-weight: 700;
            color: #343a40;
            /* background: linear-gradient(135deg, #007bff, #0056b3);
            -webkit-background-clip: text; */
            /* -webkit-text-fill-color: transparent; */
            /* background-clip: text; */
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

        .product-price {
            font-size: 2.5rem;
            font-weight: bold;
            color: #007bff;
        }

        .rating i {
            color: #ffc107;
            font-size: 1.1rem;
        }

        .rating-input-group .btn-star {
            color: #ccc;
            font-size: 1.8rem;
            transition: color 0.2s;
        }

        .rating-input-group .btn-star:hover,
        .rating-input-group .btn-star.selected {
            color: #ffc107;
        }

        .review-card {
            background-color: #f8f9fa;
            border-left: 4px solid #007bff;
        }

        .review-author {
            font-weight: 600;
        }

        .review-date {
            font-size: 0.85rem;
            color: #6c757d;
        }

        .review-form-card {
            background-color: #f8f9fa;
            border-radius: 15px;
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

        .product-image {
            border-radius: 15px;
            object-fit: contain;
            background-color: #f8f9fa;
            border: 1px solid #dee2e6;
        }

        @media (max-width: 768px) {
            .display-6 {
                font-size: 2rem;
            }
            .product-price { font-size: 2rem; }
        }
    </style>
</head>

<body>
    <!-- Navigation Bar -->
    <?php require_once 'navbar.php'; ?>

    <div class="container mt-4">
        <!-- Breadcrumb -->
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item">
                    <a href="index.php" class="text-decoration-none">
                        <i class="bi bi-house"></i> หน้าหลัก
                    </a>
                </li>
                <li class="breadcrumb-item">
                    <a href="products.php" class="text-decoration-none">
                        สินค้าทั้งหมด
                    </a>
                </li>
                <li class="breadcrumb-item active" aria-current="page">
                    รายละเอียดสินค้า
                </li>
            </ol>
        </nav>

        <?php if ($product): ?>
            <div class="card shadow-sm border-0">
                <div class="card-body p-4 p-lg-5">
                    <div class="row g-4 g-lg-5">
                        <!-- Image Column -->
                        <div class="col-lg-6">
                            <?php
                            $img = !empty($product['image'])
                                ? 'product_images/' . rawurlencode($product['image'])
                                : 'product_images/no-image.jpg';
                            ?>
                            <img src="<?= $img ?>" class="w-100 product-image" alt="<?= htmlspecialchars($product['product_name']) ?>">
                        </div>

                        <!-- Details Column -->
                        <div class="col-lg-6 d-flex flex-column">
                            <span class="badge bg-primary align-self-start mb-2"><?= htmlspecialchars($product['category_name'] ?? 'ไม่มีหมวดหมู่') ?></span>
                            <h1 class="display-6 mb-3"><?= htmlspecialchars($product['product_name']) ?></h1>

                            <!-- Rating -->
                            <div class="rating mb-3">
                                <?php
                                $full = floor($average_rating);
                                $half = ($average_rating - $full) >= 0.5 ? 1 : 0;
                                ?>
                                <?php for ($i = 0; $i < $full; $i++): ?><i class="bi bi-star-fill"></i><?php endfor; ?>
                                <?php if ($half): ?><i class="bi bi-star-half"></i><?php endif; ?>
                                <?php for ($i = 0; $i < 5 - $full - $half; $i++): ?><i class="bi bi-star"></i><?php endfor; ?>
                                <span class="text-muted ms-2">(<?= $total_reviews ?> รีวิว)</span>
                            </div>

                            <div class="d-flex justify-content-between align-items-center bg-light p-3 rounded mb-4">
                                <div>
                                    <div class="text-muted">ราคา</div>
                                    <div class="product-price"><?= number_format($product['price'], 2) ?></div>
                                </div>
                                <div class="text-end">
                                    <div class="text-muted">สถานะ</div>
                                    <?php if ($product['stock'] > 0): ?>
                                        <div class="badge bg-success fs-6">มีสินค้า (<?= $product['stock'] ?> ชิ้น)</div>
                                    <?php else: ?>
                                        <div class="badge bg-danger fs-6">สินค้าหมด</div>
                                    <?php endif; ?>
                                </div>
                            </div>

                            <!-- Add to Cart Section -->
                            <div class="mt-auto">
                                <?php if ($isLoggedIn): ?>
                                    <?php if ($product['stock'] > 0): ?>
                                        <form action="cart.php" method="post">
                                            <input type="hidden" name="product_id" value="<?= $product['product_id'] ?>">
                                            <div class="row g-2 align-items-center">
                                                <div class="col-4">
                                                    <label for="quantity" class="form-label visually-hidden">จำนวน</label>
                                                    <input type="number" name="quantity" id="quantity" class="form-control form-control-lg text-center quantity-input" value="1" min="1" max="<?= $product['stock'] ?>" required>
                                                </div>
                                                <div class="col-8">
                                                    <button type="submit" class="btn btn-primary btn-lg w-100">
                                                        <i class="bi bi-cart-plus-fill"></i> เพิ่มลงตะกร้า
                                                    </button>
                                                </div>
                                            </div>
                                        </form>
                                    <?php else: ?>
                                        <div class="alert alert-danger text-center mb-0">
                                            <i class="bi bi-x-circle-fill"></i> สินค้าหมดชั่วคราว
                                        </div>
                                    <?php endif; ?>
                                <?php else: ?>
                                    <div class="alert alert-warning text-center mb-0">
                                        <h5 class="alert-heading"><i class="bi bi-exclamation-triangle-fill"></i> โปรดเข้าสู่ระบบ</h5>
                                        <p>กรุณาเข้าสู่ระบบเพื่อทำการสั่งซื้อสินค้า</p>
                                        <a href="login.php" class="btn btn-primary">เข้าสู่ระบบ</a>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>

                    <!-- Description Section -->
                    <hr class="my-4 my-lg-5">
                    <div class="row">
                        <div class="col-12">
                            <h4 class="mb-3">รายละเอียดสินค้า</h4>
                            <p class="text-muted"><?= nl2br(htmlspecialchars($product['description'])) ?></p>
                        </div>
                    </div>

                    <!-- Reviews Section -->
                    <hr class="my-4 my-lg-5">
                    <div class="row" id="reviews-section">
                        <div class="col-12">
                            <h4 class="mb-4">รีวิวจากผู้ซื้อ (<?= $total_reviews ?>)</h4>

                            <!-- Review Submission Form -->
                            <?php if ($isLoggedIn && !$user_has_reviewed): ?>
                                <div class="card review-form-card mb-4 border-0">
                                    <div class="card-body p-4">                                        
                                        <h5 class="card-title mb-3">เขียนรีวิวของคุณ</h5>
                                        <?php if ($review_error): ?><div class="alert alert-danger"><?= $review_error ?></div><?php endif; ?>
                                        <?php if ($review_success): ?><div class="alert alert-success"><?= $review_success ?></div><?php endif; ?>
                                        
                                        <?php if (isset($_GET['review_updated'])): ?><div class="alert alert-success">อัปเดตรีวิวของคุณเรียบร้อยแล้ว</div><?php endif; ?>
                                        <?php if (isset($_GET['review_deleted'])): ?><div class="alert alert-success">ลบรีวิวของคุณเรียบร้อยแล้ว</div><?php endif; ?>
                                        <?php if ($edit_review_error): ?><div class="alert alert-danger"><?= $edit_review_error ?></div><?php endif; ?>
                                        <?php if (isset($_GET['review_success'])): ?><div class="alert alert-success">ขอบคุณสำหรับรีวิวของคุณ!</div><?php endif; ?>

                                        <form method="post">
                                            <div class="mb-3">
                                                <label class="form-label">ให้คะแนนสินค้า:</label>
                                                <div class="rating-input-group">
                                                    <input type="hidden" name="rating" id="rating-value" value="0" required>
                                                    <button type="button" class="btn btn-link p-0 btn-star" data-value="1"><i class="bi bi-star-fill"></i></button>
                                                    <button type="button" class="btn btn-link p-0 btn-star" data-value="2"><i class="bi bi-star-fill"></i></button>
                                                    <button type="button" class="btn btn-link p-0 btn-star" data-value="3"><i class="bi bi-star-fill"></i></button>
                                                    <button type="button" class="btn btn-link p-0 btn-star" data-value="4"><i class="bi bi-star-fill"></i></button>
                                                    <button type="button" class="btn btn-link p-0 btn-star" data-value="5"><i class="bi bi-star-fill"></i></button>
                                                </div>
                                            </div>
                                            <div class="mb-3">
                                                <label for="comment" class="form-label">ความคิดเห็น:</label>
                                                <textarea name="comment" id="comment" class="form-control" rows="3" placeholder="บอกเล่าประสบการณ์ของคุณเกี่ยวกับสินค้านี้..."></textarea>
                                            </div>
                                            <button type="submit" name="submit_review" class="btn btn-primary">ส่งรีวิว</button>
                                        </form>
                                    </div>
                                </div>
                            <?php elseif ($isLoggedIn && $user_has_reviewed): ?>
                                <div class="alert alert-info">คุณได้รีวิวสินค้านี้ไปแล้ว ขอบคุณสำหรับความคิดเห็นของคุณ!</div>
                            <?php endif; ?>

                            <!-- List of Reviews -->
                            <?php if (empty($reviews)): ?>
                                <p class="text-muted">ยังไม่มีรีวิวสำหรับสินค้านี้</p>
                            <?php else: ?>
                                <?php foreach ($reviews as $review): ?>
                                    <?php if (isset($_GET['edit_review']) && (int)$_GET['edit_review'] === $review['review_id'] && $review['user_id'] === $user_id): ?>
                                        <!-- Edit Form -->
                                        <div class="card review-form-card mb-4 border-0">
                                            <div class="card-body p-4">
                                                <h5 class="card-title mb-3">แก้ไขรีวิวของคุณ</h5>
                                                <form method="post">
                                                    <input type="hidden" name="review_id" value="<?= $review['review_id'] ?>">
                                                    <div class="mb-3">
                                                        <label class="form-label">ให้คะแนนสินค้า:</label>
                                                        <div class="rating-input-group">
                                                            <input type="hidden" name="rating" id="edit-rating-value-<?= $review['review_id'] ?>" value="<?= $review['rating'] ?>" required>
                                                            <?php for ($i = 1; $i <= 5; $i++): ?>
                                                                <button type="button" class="btn btn-link p-0 btn-star edit-star" data-value="<?= $i ?>" data-review-id="<?= $review['review_id'] ?>"><i class="bi bi-star-fill"></i></button>
                                                            <?php endfor; ?>
                                                        </div>
                                                    </div>
                                                    <div class="mb-3">
                                                        <label for="comment-<?= $review['review_id'] ?>" class="form-label">ความคิดเห็น:</label>
                                                        <textarea name="comment" id="comment-<?= $review['review_id'] ?>" class="form-control" rows="3"><?= htmlspecialchars($review['comment']) ?></textarea>
                                                    </div>
                                                    <button type="submit" name="update_review" class="btn btn-primary">บันทึกการแก้ไข</button>
                                                    <a href="product_detail.php?id=<?= $product_id ?>#reviews-section" class="btn btn-secondary">ยกเลิก</a>
                                                </form>
                                            </div>
                                        </div>
                                    <?php else: ?>
                                        <!-- Display Review -->
                                        <div class="card review-card border-0 mb-3">
                                            <div class="card-body">
                                                <div class="d-flex justify-content-between align-items-start">
                                                    <div>
                                                        <span class="review-author"><?= htmlspecialchars($review['username']) ?></span>
                                                        <span class="review-date ms-2"><?= date('d/m/Y H:i', strtotime($review['created_at'])) ?></span>
                                                    </div>
                                                    <div class="rating">
                                                        <?php for ($i = 0; $i < $review['rating']; $i++): ?><i class="bi bi-star-fill"></i><?php endfor; ?>
                                                        <?php for ($i = 0; $i < 5 - $review['rating']; $i++): ?><i class="bi bi-star"></i><?php endfor; ?>
                                                    </div>
                                                </div>
                                                <?php if (!empty($review['comment'])): ?>
                                                    <p class="card-text mt-2 mb-0"><?= nl2br(htmlspecialchars($review['comment'])) ?></p>
                                                <?php endif; ?>

                                                <?php if ($isLoggedIn && $review['user_id'] === $user_id): ?>
                                                    <div class="mt-2 text-end">
                                                        <a href="product_detail.php?id=<?= $product_id ?>&edit_review=<?= $review['review_id'] ?>#reviews-section" class="btn btn-sm btn-outline-primary">
                                                            <i class="bi bi-pencil-square"></i> แก้ไข
                                                        </a>
                                                        <a href="product_detail.php?id=<?= $product_id ?>&delete_review=<?= $review['review_id'] ?>#reviews-section" class="btn btn-sm btn-outline-danger" onclick="return confirm('คุณแน่ใจหรือไม่ว่าต้องการลบรีวิวนี้?')">
                                                            <i class="bi bi-trash"></i> ลบ
                                                        </a>
                                                    </div>
                                                <?php endif; ?>
                                            </div>
                                        </div>
                                    <?php endif; ?>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        <?php else: ?>
            <div class="text-center py-5">
                <i class="bi bi-search-heart display-1 text-muted"></i>
                <h2 class="mt-4">ไม่พบสินค้า</h2>
                <p class="lead text-muted">ขออภัย ไม่พบสินค้าที่คุณกำลังค้นหา</p>
                <a href="index.php" class="btn btn-primary mt-3">
                    <i class="bi bi-arrow-left"></i> กลับไปหน้าแรก
                </a>
            </div>
        <?php endif; ?>
    </div>

    <?php require_once 'footer.php'; ?>


    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/js/bootstrap.bundle.min.js" integrity="sha384-ndDqU0Gzau9qJ1lfW4pNLlhNTkCfHzAVBReH9diLvGRem5+R9g2FzA8ZGN954O5Q" crossorigin="anonymous"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const stars = document.querySelectorAll('.rating-input-group .btn-star');
            const ratingInput = document.getElementById('rating-value');

            stars.forEach(star => {
                star.addEventListener('click', function() {
                    const value = this.dataset.value;
                    ratingInput.value = value;

                    // Remove selected class from all stars
                    stars.forEach(s => s.classList.remove('selected'));

                    // Add selected class to stars up to the clicked one
                    for (let i = 0; i < value; i++) {
                        stars[i].classList.add('selected');
                    }
                });
            });

            // Script for edit form stars
            const editStarsContainers = document.querySelectorAll('.rating-input-group');
            editStarsContainers.forEach(container => {
                const stars = container.querySelectorAll('.edit-star');
                if (stars.length > 0) {
                    const reviewId = stars[0].dataset.reviewId;
                    const ratingInput = document.getElementById(`edit-rating-value-${reviewId}`);
                    
                    function setStars(value) {
                        for (let i = 0; i < 5; i++) {
                            if (i < value) {
                                stars[i].classList.add('selected');
                            } else {
                                stars[i].classList.remove('selected');
                            }
                        }
                    }

                    setStars(ratingInput.value); // Set initial stars

                    stars.forEach(star => {
                        star.addEventListener('click', function() {
                            ratingInput.value = this.dataset.value;
                            setStars(this.dataset.value);
                        });
                    });
                }
            });
        });
    </script>
</body>

</html>