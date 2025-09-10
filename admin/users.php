<?php
session_start();
require_once '../config.php';
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
// ดึงข้อมูลสมาชิก
$stmt = $conn->prepare("SELECT * FROM users WHERE role = 'member' ORDER BY created_at DESC");
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
    <style>
        body {
            background: white;
            min-height: 100vh;
        }
        .card {
            transition: transform 0.3s, box-shadow 0.3s;
            border: none;
            border-radius: 15px;
        }
        .card:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(0,0,0,0.15);
        }
        .table {
            background-color: white;
            border-radius: 10px;
            overflow: hidden;
        }
        .btn {
            border-radius: 8px;
        }
        .navbar-brand {
            font-weight: bold;
        }
        .breadcrumb {
            background: rgba(255, 255, 255, 0.9);
            border-radius: 10px;
            padding: 0.75rem 1rem;
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

    <div class="container mt-4">
        <!-- Breadcrumb -->
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item">
                    <a href="index.php" class="text-decoration-none">
                        <i class="bi bi-speedometer2"></i> Dashboard
                    </a>
                </li>
                <li class="breadcrumb-item active" aria-current="page">
                    <i class="bi bi-people"></i> จัดการสมาชิก
                </li>
            </ol>
        </nav>

        <!-- Header Card -->
        <div class="row mb-4">
            <div class="col-12">
                <div class="card shadow-lg">
                    <div class="card-header bg-warning text-dark">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h2 class="mb-0">
                                    <i class="bi bi-people"></i> จัดการสมาชิก
                                </h2>
                                <small class="text-muted">จัดการข้อมูลสมาชิกในระบบ</small>
                            </div>
                            <div class="d-flex gap-2">
                                <a href="index.php" class="btn btn-secondary">
                                    <i class="bi bi-arrow-left"></i> กลับ Dashboard
                                </a>
                                <a href="../index.php" class="btn btn-info">
                                    <i class="bi bi-house"></i> หน้าหลัก
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Users Table -->
        <div class="row">
            <div class="col-12">
                <?php if (count($users) === 0): ?>
                    <div class="card shadow">
                        <div class="card-body text-center py-5">
                            <i class="bi bi-person-x display-1 text-muted"></i>
                            <h3 class="text-muted mt-3">ยังไม่มีสมาชิกในระบบ</h3>
                            <p class="text-muted">เมื่อมีผู้ใช้สมัครสมาชิก รายชื่อจะแสดงที่นี่</p>
                        </div>
                    </div>
                <?php else: ?>
                    <div class="card shadow-lg">
                        <div class="card-header bg-light">
                            <div class="d-flex justify-content-between align-items-center">
                                <h5 class="mb-0">
                                    <i class="bi bi-list-ul"></i> รายชื่อสมาชิก
                                </h5>
                                <div class="d-flex gap-3">
                                    <div class="badge bg-primary fs-6">
                                        <i class="bi bi-people"></i> ทั้งหมด: <?= count($users) ?> คน
                                    </div>
                                    <div class="badge bg-success fs-6">
                                        <i class="bi bi-person-check"></i> สมาชิกใหม่วันนี้: 
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
                                                    <div>
                                                        <strong><?= htmlspecialchars($user['username']) ?></strong>
                                                        <br>
                                                        <small class="text-muted">User ID: <?= $user['user_id'] ?></small>
                                                    </div>
                                                </td>
                                                <td><?= htmlspecialchars($user['full_name']) ?></td>
                                                <td>
                                                    <span class="badge bg-light text-dark">
                                                        <?= htmlspecialchars($user['email']) ?>
                                                    </span>
                                                </td>
                                                <td>
                                                    <small class="text-muted">
                                                        <?= date('d/m/Y H:i', strtotime($user['created_at'])) ?>
                                                    </small>
                                                </td>
                                                <td class="text-center">
                                                    <div class="btn-group" role="group">
                                                        <a href="edit_user.php?id=<?= $user['user_id'] ?>" 
                                                           class="btn btn-sm btn-outline-warning" 
                                                           title="แก้ไขข้อมูล">
                                                            <i class="bi bi-pencil"></i>
                                                        </a>
                                                        <a href="users.php?delete=<?= $user['user_id'] ?>" 
                                                           class="btn btn-sm btn-outline-danger"
                                                           title="ลบสมาชิก"
                                                           onclick="return confirm('คุณต้องการลบสมาชิก <?= htmlspecialchars($user['username']) ?> หรือไม่?\n\nการดำเนินการนี้ไม่สามารถย้อนกลับได้!')">
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
                           
                        </div>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Footer -->
    <footer class="bg-dark text-white py-4 mt-5">
        <div class="container text-center">
            <p class="mb-0">&copy; Adisak Yongpanya 664230034 66/46</p>
        </div>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/js/bootstrap.bundle.min.js"
        integrity="sha384-ndDqU0Gzau9qJ1lfW4pNLlhNTkCfHzAVBReH9diLvGRem5+R9g2FzA8ZGN954O5Q" crossorigin="anonymous">
    </script>
</body>

</html>