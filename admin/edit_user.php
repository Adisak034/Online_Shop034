<?php
require '../config.php'; // TODO-1: เชื่อมต่อฐานข้อมูลด้วย PDO
require_once '../session_timeout.php';
require 'auth_admin.php'; // TODO-2: การ์ดสิทธิ์(Admin Guard)

// TODO-3: ตรวจว่ามีพารามิเตอร์ id มาจริงไหม (ผ่าน GET)
// แนวทาง: ถ้าหมายเลขสมาชิกไม่มี -> redirect ไป users.php
if (!isset($_GET['id'])) {
    header("Location: users.php");
    exit;
}
// TODO-4: ดึงค่ำ id และ "แคสต์เป็น int" เพื่อควำมปลอดภัย
$user_id = (int)$_GET['id'];
// ดงึข้อมูลสมาชิกที่จะถูกแก้ไข
/*
TODO-5: เตรียม/รัน SELECT (เฉพาะ role = 'member')
SQL แนะนำ:
SELECT * FROM users WHERE user_id = ? AND role = 'member'
- ใช้prepare + execute([$user_id])
- fetch(PDO::FETCH_ASSOC) แล้วเก็บใน $user
*/
$stmt = $conn->prepare("SELECT * FROM users WHERE user_id = ? AND role = 'member'");
$stmt->execute([$user_id]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);
// TODO-6: ถ้าไม่พบข้อมูล -> แสดงข้อความและ exit;
if (!$user) {
    echo "<h3>ไม่พบสมาชิก</h3>";
    exit;
}
// ========== เมื่อผู้ใช้กด Submit ฟอร์ม ==========
$error = null;
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // TODO-7: รับค่า POST + trim
    $username = trim($_POST['username']);
    $full_name = trim($_POST['full_name']);
    $email = trim($_POST['email']);
    $password = $_POST['password'];
    $confirm = $_POST['confirm_password'];

    // TODO-8: ตรวจความครบถ้วน และตรวจรูปแบบ email
    if ($username === '' || $email === '') {
        $error = "กรุณากรอกข้อมูลให้ครบถ้วน";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = "รูปแบบอีเมลไม่ถูกต้อง";
    }
    // TODO-9: ถ้ำ validate ผ่ำน ใหต้ รวจสอบซ ้ำ (username/email ชนกับคนอนื่ ทไี่ มใ่ ชต่ ัวเองหรือไม่)
    // SQL แนะนำ:
    // SELECT 1 FROM users WHERE (username = ? OR email = ?) AND user_id != ?
    if (!$error) {
        $chk = $conn->prepare("SELECT 1 FROM users WHERE (username = ? OR email = ?) AND user_id != ?");
        $chk->execute([$username, $email, $user_id]);
        if ($chk->fetch()) {
            $error = "ชื่อผู้ใช้หรืออีเมลนี้มีอยู่ในระบบ";
        }
    }
    // ตรวจรหัสผ่าน (กรณีต้องการเปลี่ยน)
    // เงื่อนไข: อนุญาตให้ปล่อยว่างได้ (คือไม่เปลี่ยนรหัสผ่าน)
    $updatePassword = false;
    $hashed = null;
    if (!$error && ($password !== '' || $confirm !== '')) {
        // TODO: นักศึกษาเติมเงื่อนไข เช่น ยำว >= 6 และรหัสผ่านตรงกัน
        if (strlen($password) < 6) {
            $error = "รหัสผ่านต้องยาวอย่างน้อย 6 อักขระ";
        } elseif ($password !== $confirm) {
            $error = "รหัสผ่านใหม่กับยืนยันรหัสผ่านไม่ตรงกัน";
        } else {
            // แฮชรหัสผ่าน
            $hashed = password_hash($password, PASSWORD_DEFAULT);
            $updatePassword = true;
        }
    }
    // สร้าง SQL UPDATE แบบยืดหยุ่น (ถ้าไม่เปลี่ยนรหัสผ่านจะไม่แตะ field password)
    if (!$error) {
        if ($updatePassword) {
            // อัปเดตรวมรหัสผ่าน
            $sql = "UPDATE users SET username = ?, full_name = ?, email = ?, password = ? WHERE user_id = ?";
            $args = [$username, $full_name, $email, $hashed, $user_id];
        } else {
            // อัปเดตเฉพาะข้อมูลทั่วไป
            $sql = "UPDATE users SET username = ?, full_name = ?, email = ? WHERE user_id = ?";
            $args = [$username, $full_name, $email, $user_id];
        }
        $upd = $conn->prepare($sql);
        $upd->execute($args);
        header("Location: users.php");
        exit;
        }
    // เขียน update แบบปกติ: ถ้าไม่ซ้ำ -> ทำ UPDATE
    // if (!$error) {
    // $upd = $pdo->prepare("UPDATE users SET username = ?, full_name = ?, email = ? WHERE user_id = ?");
    // $upd->execute([$username, $full_name, $email, $user_id]);
    // // TODO-11: redirect กลับหน้า users.php หลังอัปเดตสำเร็จ
    // header("Location: users.php");
    // exit;
    // }

    // OPTIONAL: อัปเดตค่า $user เพื่ สะท้อนค่าที่ช่องฟอร์ม (หากมีerror)
    $user['username'] = $username;
    $user['full_name'] = $full_name;
    $user['email'] = $email;
}
?>
<!DOCTYPE html>
<html lang="th">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>แก้ไขสมาชิก</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-LN+7fdVzj6u52u30Kp6M/trliBMCMKTyK833zpbD+pXdCLuTusPj697FH4R/5mcr" crossorigin="anonymous">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css" rel="stylesheet">
    <link rel="stylesheet" href="admin_main.css">
</head>

<body>
    <!-- Navigation Bar -->
    <?php require_once 'navbar_admin.php'; ?>

    <div class="container mt-5">
        <!-- Welcome Section -->
        <div class="row mb-4">
            <div class="col-12">
                <div class="card admin-card shadow-lg">
                    <div class="card-body text-center">
                        <h1 class="display-5 text-primary mb-3 fw-bold">
                            <i class="bi bi-person-gear"></i> แก้ไขข้อมูลสมาชิก
                        </h1>
                        <p class="lead text-muted">
                            คุณกำลังแก้ไขข้อมูลของ: <strong><?= htmlspecialchars($user['username']) ?></strong>
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="container mt-5">

        <!-- Back Button -->
        <div class="row mb-4">
            <div class="col-12">
                <a href="users.php" class="btn btn-secondary">
                    <i class="bi bi-arrow-left"></i> กลับหน้ารายชื่อสมาชิก
                </a>
            </div>
        </div>

        <!-- Alert Messages -->
        <?php if (isset($error)): ?>
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <i class="bi bi-exclamation-triangle"></i> <?= htmlspecialchars($error) ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>

        <!-- Edit User Form -->
        <div class="row">
            <div class="col-12">
                <div class="card admin-card shadow-lg">
                    <div class="card-header bg-primary text-white">
                        <h5 class="mb-0 text-dark">
                            <i class="bi bi-pencil-square"></i> ฟอร์มแก้ไขข้อมูล
                        </h5>
                    </div>
                    <div class="card-body p-4">
                        <form method="post" class="row g-4">
                            <div class="col-md-6">
                                <label for="username" class="form-label"><i class="bi bi-person"></i> ชื่อผู้ใช้</label>
                                <input type="text" name="username" id="username" class="form-control" required 
                                       value="<?= htmlspecialchars($user['username']) ?>">
                            </div>
                            <div class="col-md-6">
                                <label for="full_name" class="form-label"><i class="bi bi-person-vcard"></i> ชื่อ - นามสกุล</label>
                                <input type="text" name="full_name" id="full_name" class="form-control" 
                                       value="<?= htmlspecialchars($user['full_name']) ?>">
                            </div>
                            <div class="col-12">
                                <label for="email" class="form-label"><i class="bi bi-envelope"></i> อีเมล</label>
                                <input type="email" name="email" id="email" class="form-control" required 
                                       value="<?= htmlspecialchars($user['email']) ?>">
                            </div>
                            <div class="col-md-6">
                                <label for="password" class="form-label"><i class="bi bi-key"></i> รหัสผ่านใหม่</label>
                                <input type="password" name="password" id="password" class="form-control">
                                <div class="form-text">
                                    <i class="bi bi-info-circle"></i> ถ้าไม่ต้องการเปลี่ยนรหัสผ่าน ให้เว้นว่างไว้
                                </div>
                            </div>
                            <div class="col-md-6">
                                <label for="confirm_password" class="form-label"><i class="bi bi-key-fill"></i> ยืนยันรหัสผ่านใหม่</label>
                                <input type="password" name="confirm_password" id="confirm_password" class="form-control">
                            </div>
                            <div class="col-12 text-center mt-4">
                                <button type="submit" class="btn btn-primary btn-lg">
                                    <i class="bi bi-check-circle"></i> บันทึกการแก้ไข
                                </button>
                                <a href="users.php" class="btn btn-secondary btn-lg ms-2">
                                    <i class="bi bi-x-circle"></i> ยกเลิก
                                </a>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <?php require_once 'footer_admin.php'; ?>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/js/bootstrap.bundle.min.js" integrity="sha384-ndDqU0Gzau9qJ1lfW4pNLlhNTkCfHzAVBReH9diLvGRem5+R9g2FzA8ZGN954O5Q" crossorigin="anonymous"></script>
</body>

</html>