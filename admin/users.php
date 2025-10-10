<?php
require_once '../config.php';
require_once '../session_timeout.php';
require_once 'auth_admin.php';
// ตรวจสอบสิทธิ์admin
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../login.php");
    exit;
}
// ลบสมาชิก
if (isset($_GET['delete'])) {
    $user_id = $_GET['delete'];
    // ป้องกันลบตัวเอง
    if ($user_id != $_SESSION['user_id']) {
        $stmt = $conn->prepare("DELETE FROM users WHERE user_id = ? AND role = 'member'");
        $stmt->execute([$user_id]);
    }
    header("Location: users.php");
    exit;
}
// --- ส่วนจัดการการดึงข้อมูลสมาชิก ---
$search_term = trim($_GET['search'] ?? '');
// --- Pagination ---
$page = isset($_GET['page']) && is_numeric($_GET['page']) ? (int)$_GET['page'] : 1;
$limit = 10; // จำนวนสมาชิกต่อหน้า
$offset = ($page - 1) * $limit;

// --- SQL Query ---
$base_sql = "FROM users WHERE role = 'member'";
$conditions_sql = "";
$params = [];

if ($search_term !== '') {
    $conditions_sql = " AND (username LIKE ? OR email LIKE ?)";
    $params[] = '%' . $search_term . '%';
    $params[] = '%' . $search_term . '%';
}

// --- นับจำนวนสมาชิกทั้งหมดสำหรับ Pagination ---
$count_sql = "SELECT COUNT(*) " . $base_sql . $conditions_sql;
$count_stmt = $conn->prepare($count_sql);
$count_stmt->execute($params);
$total_users = $count_stmt->fetchColumn();
$total_pages = ceil($total_users / $limit);

// --- ดึงข้อมูลสมาชิกสำหรับหน้าปัจจุบัน ---
$sql = "SELECT * " . $base_sql . $conditions_sql . " ORDER BY created_at DESC LIMIT :limit OFFSET :offset";
$stmt = $conn->prepare($sql);
foreach ($params as $key => $value) {
    $stmt->bindValue($key + 1, $value);
}
$stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
$stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
$stmt->execute();
$users = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="th">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>จัดการสมาชิก</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-LN+7fdVzj6u52u30Kp6M/trliBMCMKTyK833zpbD+pXdCLuTusPj697FH4R/5mcr" crossorigin="anonymous">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css" rel="stylesheet">
    <link rel="stylesheet" href="admin_main.css">
</head>

<body>
    <!-- Navigation Bar -->
    <?php require_once 'navbar_admin.php'; ?>

    <div class="container mt-5">
        <!-- Welcome Section -->
        <div class="row mb-5">
            <div class="col-12">
                <div class="card admin-card shadow-lg">
                    <div class="card-body text-center">
                        <h1 class="display-5 text-primary mb-3 fw-bold">
                            <i class="bi bi-people"></i> จัดการสมาชิก
                        </h1>
                        <p class="lead text-muted">
                            จัดการข้อมูลผู้ใช้ในระบบ - <strong><?= htmlspecialchars($_SESSION['username']) ?></strong>
                        </p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Search Form -->
        <div class="row mb-4">
            <div class="col-12">
                <div class="card admin-card shadow-sm">
                    <div class="card-body">
                        <form method="get" class="row g-3 align-items-end">
                            <div class="col-md-9">
                                <label for="search" class="form-label"><i class="bi bi-search"></i> ค้นหาสมาชิก (ชื่อผู้ใช้ หรือ อีเมล)</label>
                                <input type="text" name="search" id="search" class="form-control" placeholder="พิมพ์เพื่อค้นหา..." value="<?= htmlspecialchars($search_term) ?>">
                            </div>
                            <div class="col-md-3 d-flex gap-2">
                                <button type="submit" class="btn btn-primary w-100"><i class="bi bi-funnel-fill"></i> ค้นหา</button>
                                <a href="users.php" class="btn btn-outline-secondary" title="รีเซ็ต"><i class="bi bi-x-lg"></i></a>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>

        <!-- Users Table Section -->
        <div class="row">
            <div class="col-12">
                <?php if (empty($users)): ?>
                    <div class="card admin-card shadow-lg">
                        <div class="card-body text-center py-5">
                            <div class="admin-icon text-muted">
                                <i class="bi bi-person-x"></i>
                            </div>
                            <h3 class="text-muted">ยังไม่มีสมาชิกในระบบ</h3>
                            <p class="text-muted">เมื่อมีผู้ใช้สมัครสมาชิก รายชื่อจะแสดงที่นี่</p>
                        </div>
                    </div>
                <?php else: ?>
                    <div class="card admin-card shadow-lg">
                        <div class="card-header bg-primary text-white">
                            <div class="d-flex justify-content-between align-items-center">
                                <h5 class="mb-0">
                                    <i class="bi bi-list-ul"></i> รายชื่อสมาชิก
                                </h5>
                                <div class="d-flex gap-3">
                                    <div class="badge bg-light text-dark fs-6">
                                        <i class="bi bi-people"></i> ทั้งหมด: <?= $total_users ?> คน
                                    </div>
                                    <div class="badge bg-success fs-6">
                                        <i class="bi bi-check-circle"></i> สมาชิกใหม่วันนี้: 
                                        <?php 
                                        $today = date('Y-m-d');
                                        $newToday = array_filter($users, function($user) use ($today) {
                                            return date('Y-m-d', strtotime($user['created_at'])) === $today;
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
                                                <i class="bi bi-hash"></i> ID
                                            </th>
                                            <th scope="col">
                                                <i class="bi bi-person"></i> ชื่อผู้ใช้
                                            </th>
                                            <th scope="col">
                                                <i class="bi bi-card-text"></i> ชื่อ - นามสกุล
                                            </th>
                                            <th scope="col">
                                                <i class="bi bi-envelope"></i> อีเมล
                                            </th>
                                            <th scope="col">
                                                <i class="bi bi-calendar"></i> วันที่สมัคร
                                            </th>
                                            <th scope="col" class="text-center">
                                                <i class="bi bi-gear"></i> จัดการ
                                            </th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($users as $user): ?>
                                            <tr>
                                                <td>
                                                    <span class="badge bg-secondary"><?= $user['user_id'] ?></span>
                                                </td>
                                                <td>
                                                    <strong><?= htmlspecialchars($user['username']) ?></strong>
                                                </td>
                                                <td>
                                                    <?php if ($user['full_name']): ?>
                                                        <?= htmlspecialchars($user['full_name']) ?>
                                                    <?php else: ?>
                                                        <span class="text-muted fst-italic">ไม่ระบุ</span>
                                                    <?php endif; ?>
                                                </td>
                                                <td>
                                                    <?= htmlspecialchars($user['email']) ?>
                                                </td>
                                                <td>
                                                    <?php 
                                                    $createdAt = new DateTime($user['created_at']);
                                                    $now = new DateTime();
                                                    $diff = $now->diff($createdAt);
                                                    
                                                    if ($diff->days == 0) {
                                                        echo '<span class="badge bg-success">วันนี้</span>';
                                                    } elseif ($diff->days <= 7) {
                                                        echo '<span class="badge bg-warning text-dark">' . $diff->days . ' วันที่แล้ว</span>';
                                                    } else {
                                                        echo '<small class="text-muted">' . date('d/m/Y', strtotime($user['created_at'])) . '</small>';
                                                    }
                                                    ?>
                                                </td>
                                                <td class="text-center"> 
                                                    <div class="d-inline-flex gap-1" role="group">
                                                        <a href="edit_user.php?id=<?= $user['user_id'] ?>"
                                                            class="btn btn-sm btn-outline-warning"
                                                            title="แก้ไขข้อมูล">
                                                            <i class="bi bi-pencil"></i>
                                                        </a> 
                                                        <button type="button" class="delete-button btn btn-sm btn-outline-danger" 
                                                                data-user-id="<?= $user['user_id'] ?>" title="ลบสมาชิก">
                                                            <i class="bi bi-trash"></i>
                                                        </button>
                                                    </div>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                        <!-- Pagination -->
                        <?php if ($total_pages > 1): ?>
                            <div class="card-footer bg-white">
                                <nav aria-label="Page navigation">
                                    <ul class="pagination justify-content-center mb-0">
                                        <?php
                                        $query_params = $_GET;
                                        unset($query_params['page']);
                                        $query_string = http_build_query($query_params);
                                        ?>
                                        <li class="page-item <?= ($page <= 1) ? 'disabled' : '' ?>">
                                            <a class="page-link" href="?page=<?= $page - 1 ?>&<?= $query_string ?>">ก่อนหน้า</a>
                                        </li>
                                        <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                                            <li class="page-item <?= ($page == $i) ? 'active' : '' ?>">
                                                <a class="page-link" href="?page=<?= $i ?>&<?= $query_string ?>"><?= $i ?></a>
                                            </li>
                                        <?php endfor; ?>
                                        <li class="page-item <?= ($page >= $total_pages) ? 'disabled' : '' ?>">
                                            <a class="page-link" href="?page=<?= $page + 1 ?>&<?= $query_string ?>">ถัดไป</a>
                                        </li>
                                    </ul>
                                </nav>
                            </div>
                        <?php endif; ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Footer -->
    <?php require_once 'footer_admin.php'; ?>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/js/bootstrap.bundle.min.js"
        integrity="sha384-ndDqU0Gzau9qJ1lfW4pNLlhNTkCfHzAVBReH9diLvGRem5+R9g2FzA8ZGN954O5Q" crossorigin="anonymous">
    </script>
    <!-- SweetAlert2 -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script> 
    

    <script>
        // ฟังกชันสำหรับแสดงกล่องยืนยัน SweetAlert2
        function showDeleteConfirmation(userId) {
            Swal.fire({
                title: 'คุณแน่ใจหรือไม่?',
                text: 'คุณจะไม่สามารถเรียกคืนข้อมูลกลับได้!',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonText: 'ลบ',
                cancelButtonText: 'ยกเลิก',
            }).then((result) => {
                if (result.isConfirmed) {
                    // หากผู้ใช้ยืนยัน ให้ส่งค่าฟอร์มไปยัง delete.php เพื่อลบข้อมูล
                    const form = document.createElement('form');
                    form.method = 'POST';
                    form.action = 'delUser_Sweet.php';
                    const input = document.createElement('input');
                    input.type = 'hidden';
                    input.name = 'u_id';
                    input.value = userId;
                    form.appendChild(input);
                    document.body.appendChild(form);
                    form.submit();
                }
            });
        }
        // แนบตัวตรวจจับเหตุการณ์คลิกกับองค์ปุ่มลบทั้งหมดที่มีคลาส delete-button
        const deleteButtons = document.querySelectorAll('.delete-button');
        deleteButtons.forEach((button) => {
            button.addEventListener('click', () => {
                const userId = button.getAttribute('data-user-id');
                showDeleteConfirmation(userId);
            });
        });
    </script>

</body>

</html>