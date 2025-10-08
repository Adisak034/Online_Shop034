<?php
// หาชื่อไฟล์ปัจจุบันเพื่อกำหนด active class
$current_page_admin = basename($_SERVER['PHP_SELF']);
?>
<!-- Navigation Bar -->
<nav class="navbar navbar-expand-lg navbar-dark bg-primary shadow">
    <div class="container">
        <a class="navbar-brand fw-bold" href="index.php">
            <i class="bi bi-shield-check"></i> ระบบผู้ดูแลระบบ
        </a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#adminNavbar" aria-controls="adminNavbar" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="adminNavbar">
            <ul class="navbar-nav me-auto mb-2 mb-lg-0">
                <li class="nav-item">
                    <a class="nav-link <?= ($current_page_admin === 'index.php') ? 'active' : '' ?>" href="index.php"><i class="bi bi-speedometer2"></i> Dashboard</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?= in_array($current_page_admin, ['products.php', 'edit_products.php']) ? 'active' : '' ?>" href="products.php"><i class="bi bi-box-seam"></i> จัดการสินค้า</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?= ($current_page_admin === 'orders.php') ? 'active' : '' ?>" href="orders.php"><i class="bi bi-cart-check"></i> จัดการคำสั่งซื้อ</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?= in_array($current_page_admin, ['users.php', 'edit_user.php']) ? 'active' : '' ?>" href="users.php"><i class="bi bi-people"></i> จัดการสมาชิก</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?= ($current_page_admin === 'category.php') ? 'active' : '' ?>" href="category.php"><i class="bi bi-tags"></i> จัดการหมวดหมู่</a>
                </li>
            </ul>
            <div class="navbar-nav ms-auto">
                <div class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown">
                        <i class="bi bi-person-circle"></i> <?= htmlspecialchars($_SESSION['username']) ?>
                    </a>
                    <ul class="dropdown-menu dropdown-menu-end">
                        <li><span class="dropdown-item-text">ผู้ดูแลระบบ</span></li>
                        <li>
                            <hr class="dropdown-divider">
                        </li>
                        <li><a class="dropdown-item" href="../index.php">
                                <i class="bi bi-house"></i> กลับหน้าหลัก
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
    </div>
</nav>