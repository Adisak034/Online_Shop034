<?php
// --- ดึงข้อมูลหมวดหมู่ทั้งหมดสำหรับ Navbar ---
$category_stmt_navbar = $conn->query("SELECT * FROM categories ORDER BY category_name ASC");
$all_categories_navbar = $category_stmt_navbar->fetchAll(PDO::FETCH_ASSOC);

// ตรวจสอบสถานะการล็อกอิน
$isLoggedIn_navbar = isset($_SESSION['user_id']);

// หาชื่อไฟล์ปัจจุบันเพื่อกำหนด active class
$current_page = basename($_SERVER['PHP_SELF']);
?>

<!-- Navigation Bar -->
<nav class="navbar navbar-expand-lg navbar-dark bg-primary shadow">
    <div class="container">
        <a class="navbar-brand fw-bold" href="index.php">
            <i class="bi bi-shop"></i> BoboIT Shop
        </a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav me-auto">
                <li class="nav-item">
                    <a class="nav-link <?= ($current_page === 'index.php') ? 'active' : '' ?>" href="index.php">
                        <i class="bi bi-house"></i> หน้าหลัก
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?= ($current_page === 'products.php') ? 'active' : '' ?>" href="products.php">
                        <i class="bi bi-box-seam"></i> สินค้าทั้งหมด
                    </a>
                </li>
                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle <?= (isset($_GET['category_id'])) ? 'active' : '' ?>" href="#" role="button" data-bs-toggle="dropdown">
                        <i class="bi bi-tags"></i> หมวดหมู่
                    </a>
                    <ul class="dropdown-menu">
                        <?php foreach ($all_categories_navbar as $cat) : ?>
                            <li><a class="dropdown-item <?= (isset($_GET['category_id']) && (int)$_GET['category_id'] === $cat['category_id']) ? 'active' : '' ?>" href="products.php?category_id=<?= $cat['category_id'] ?>"><?= htmlspecialchars($cat['category_name']) ?></a></li>
                        <?php endforeach; ?>
                    </ul>
                </li>
            </ul>
            <ul class="navbar-nav">
                <?php if ($isLoggedIn_navbar) : ?>
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle <?= in_array($current_page, ['profile.php', 'cart.php', 'orders.php']) ? 'active' : '' ?>" href="#" role="button" data-bs-toggle="dropdown">
                            <i class="bi bi-person-circle"></i> <?= htmlspecialchars($_SESSION['username']) ?>
                        </a>
                        <ul class="dropdown-menu dropdown-menu-end">
                            <li><a class="dropdown-item <?= ($current_page === 'profile.php') ? 'active' : '' ?>" href="profile.php"><i class="bi bi-person"></i> ข้อมูลส่วนตัว</a></li>
                            <li><a class="dropdown-item <?= ($current_page === 'cart.php') ? 'active' : '' ?>" href="cart.php"><i class="bi bi-cart"></i> ตะกร้าสินค้า</a></li>
                            <li><a class="dropdown-item <?= ($current_page === 'orders.php') ? 'active' : '' ?>" href="orders.php"><i class="bi bi-bag"></i> ประวัติการสั่งซื้อ</a></li>
                            <?php if ($_SESSION['role'] === 'admin') : ?>
                                <li>
                                    <hr class="dropdown-divider">
                                </li>
                                <li><a class="dropdown-item" href="admin/index.php"><i class="bi bi-gear"></i> จัดการระบบ</a></li>
                            <?php endif; ?>
                            <li>
                                <hr class="dropdown-divider">
                            </li>
                            <li><a class="dropdown-item text-danger" href="logout.php"><i class="bi bi-box-arrow-right"></i> ออกจากระบบ</a></li>
                        </ul>
                    </li>
                <?php else : ?>
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