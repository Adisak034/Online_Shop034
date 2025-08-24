<?php
session_start();
require_once 'config.php';

$error = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // รับค่าจากฟอร์ม
    $usernameOrEmail = trim($_POST['username_or_email']);
    $password = $_POST['password'];
    
    // เอาค่าที่ได้มาจากฟอร์ม ไปตรวจสอบว่าข้อมูลตรงกับใน db หรือไม่
    $sql = "SELECT * FROM users WHERE username = ? OR email = ?";
    $stmt = $conn->prepare($sql);
    $stmt->execute([$usernameOrEmail, $usernameOrEmail]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
    if ($user && password_verify($password, $user['password'])) {
        $_SESSION['user_id'] = $user['user_id'];
        $_SESSION['username'] = $user['username'];
        $_SESSION['role'] = $user['role'];

        if($user['role'] === 'admin') {
            header("Location: admin/index.php");
        } else {
            header("Location: index.php");
        }
        exit();
    } else {
        $error = "ชื่อผู้ใช้หรือรหัสผ่านไม่ถูกต้อง";
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-LN+7fdVzj6u52u30Kp6M/trliBMCMKTyK833zpbD+pXdCLuTusPj697FH4R/5mcr" crossorigin="anonymous">
    <style>
        body {
            background: #51b5e0;
            background: radial-gradient(circle, rgba(81, 181, 224, 1) 0%, rgba(87, 199, 133, 1) 50%, rgba(237, 221, 83, 1) 100%);
        }
    </style>
</head>

<body>
    <div class="container mt-5">
        <div class="row justify-content-center">
            <div class="col-md-8 col-lg-6">
                <div class="card shadow">
                    <div class="card-header bg-primary text-white text-center">
                        <h2 class="mb-2">เข้าสู่ระบบ</h2>
                        <!-- Success message for registration -->
                        <?php if (isset($_GET['register']) && $_GET['register'] === 'success'): ?>
                            <div class="alert alert-success mb-0">
                                <i class="bi bi-check-circle"></i> สมัครสมาชิกสำเร็จ กรุณาเข้าสู่ระบบ
                            </div>
                        <?php endif; ?>
                        <!-- Error message -->
                        <?php if (!empty($error)): ?>
                            <div class="alert alert-danger mb-0">
                                <i class="bi bi-exclamation-triangle"></i> <?= htmlspecialchars($error) ?>
                            </div>
                        <?php endif; ?>
                    </div>
                    <div class="card-body">
                        <form method="post">
                            <div class="row mb-3">
                                <div class="col-12">
                                    <label for="username_or_email" class="form-label">ชื่อผู้ใช้ หรือ อีเมล</label>
                                    <input type="text" name="username_or_email" id="username_or_email" class="form-control" 
                                           placeholder="ชื่อผู้ใช้ หรือ อีเมล" required 
                                           value="<?= isset($_POST['username_or_email']) ? htmlspecialchars($_POST['username_or_email']) : '' ?>">
                                </div>
                            </div>
                            <div class="row mb-3">
                                <div class="col-12">
                                    <label for="password" class="form-label">รหัสผ่าน</label>
                                    <input type="password" name="password" id="password" class="form-control" 
                                           placeholder="รหัสผ่าน" required>
                                </div>
                            </div>
                            <div class="d-grid gap-2">
                                <button type="submit" class="btn btn-success btn-lg">เข้าสู่ระบบ</button>
                            </div>
                        </form>
                    </div>
                    <div class="card-footer text-center">
                        <small class="text-muted">ยังไม่มีบัญชี? <a href="register.php" class="text-decoration-none">สมัครสมาชิก</a></small>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/js/bootstrap.bundle.min.js"
        integrity="sha384-ndDqU0Gzau9qJ1lfW4pNLlhNTkCfHzAVBReH9diLvGRem5+R9g2FzA8ZGN954O5Q" crossorigin="anonymous">
    </script>
</body>

</html>