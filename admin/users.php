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
    <?php require_once 'navbar_admin.php'; ?>

    <div class="container mt-5">
        <!-- Welcome Section -->
        <div class="row mb-5">
            <div class="col-12">
                <div class="card admin-card shadow-lg">
                    <div class="card-body">
                        <h1 class="display-5 text-primary mb-3">
                            <i class="bi bi-people"></i> จัดการสมาชิก
                        </h1>
                        <p class="lead text-muted">
                            จัดการข้อมูลผู้ใช้ในระบบ - <strong><?= htmlspecialchars($_SESSION['username']) ?></strong>
                        </p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Users Table Section -->
        <div class="row">
            <div class="col-12">
                <?php if (count($users) === 0): ?>
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
                                        <i class="bi bi-people"></i> ทั้งหมด: <?= count($users) ?> คน
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
                                                <td>
                                                    <?php if ($user['full_name']): ?>
                                                        <?= htmlspecialchars($user['full_name']) ?>
                                                    <?php else: ?>
                                                        <span class="text-muted fst-italic">ไม่ระบุ</span>
                                                    <?php endif; ?>
                                                </td>
                                                <td>
                                                    <span class="badge bg-light text-dark">
                                                        <?= htmlspecialchars($user['email']) ?>
                                                    </span>
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
                                                    <div class="btn-group" role="group">
                                                        <a href="edit_user.php?id=<?= $user['user_id'] ?>"
                                                            class="btn btn-sm btn-outline-warning"
                                                            title="แก้ไขข้อมูล">
                                                            <i class="bi bi-pencil"></i>
                                                        </a>
                                                                <form action="delUser_Sweet.php" method="POST" style="display:inline;">
                                                                    <input type="hidden" name="u_id" value="<?= $user['user_id'] ?>">
                                                                    <button type="button" class="delete-button btn btn-outline-danger btn-sm" 
                                                                            data-user-id="<?= $user['user_id'] ?>" title="ลบสมาชิก">
                                                                        <i class="bi bi-trash"></i>
                                                                    </button>
                                                                </form>
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
                                สามารถแก้ไขหรือลบข้อมูลสมาชิกได้ตามต้องการ
                            </small>
                        </div>
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