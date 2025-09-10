<?php
require_once 'config.php';

$error = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // รับค่าจากฟอร์ม
    $username = trim($_POST['username']);
    $fullname = trim($_POST['fullname']);
    $email = trim($_POST['email']);
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];

    // ตรวจสอบว่ากรอกข้ออมูลมาครบหรือไม่ (empty)
    if (empty($username) || empty($fullname) || empty($email) || empty($password) || empty($confirm_password)) {
        $error[] = "กรุณากรอกข้อมูลให้ครบถ้วน";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        // ตรวจสอบว่าอีเมลถูกต้องหรือไม่ (filter_var)
        $error[] = "กรุณากรอกอีเมลที่ถูกต้อง";
    } elseif ($password !== $confirm_password) {
        $error[] = "รหัสผ่านและยืนยันรหัสผ่านไม่ตรงกัน";
    } else {
        // ตรวจสอบชื่อผู้ใช้หรืออีเมลซ้ำ
        $sql = "SELECT * FROM users WHERE username = ? OR email = ?";
        $stmt = $conn->prepare($sql);
        $stmt->execute([$username, $email]);
        if ($stmt->rowCount() > 0) {
            $error[] = "ชื่อผู้ใช้หรืออีเมลนี้ถูกใช้งานแล้ว";
        }
    }
    if (empty($error)) {

        //นำข้อมูลลงฐานข้อมูล
        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
        $sql = "INSERT INTO users(username,full_name,email,password,role) VALUES (?, ?, ?, ?, 'member')";
        $stmt = $conn->prepare($sql);
        $stmt->execute([$username, $fullname, $email, $hashedPassword]);
        //ถ้าบันทึกสำเร็จให้รีไดเรกต์ไปยังหน้า login
        header("Location: login.php?register=success");
        exit();
    }
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register</title>
   <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-LN+7fdVzj6u52u30Kp6M/trliBMCMKTyK833zpbD+pXdCLuTusPj697FH4R/5mcr" crossorigin="anonymous">
    <style>
        body {
            background: white;
            min-height: 100vh;
        }
    </style>
</head>

<body>
    <div class="container mt-5">
        <div class="row justify-content-center">
            <div class="col-md-8 col-lg-6">
                <div class="card shadow">
                    <div class="card-header bg-primary text-white text-center">
                        <h2 class="mb-2 ">สมัครสมาชิก</h2>
                        <?php if (!empty($error)): // ถ้ามีข้อผิดพลาด ให้แสดงข้อความ
                        ?>
                            <div class="alert alert-danger">
                                <ul>
                                    <?php foreach ($error as $e): ?>
                                        <li style="list-style-type:none;"><?= htmlspecialchars($e) ?></li>
                                        <!-- ใช้ htmlspecialchars เพื่อป้องกัน XSS -->
                                        <!-- < ? = คือ short echo tag ?> -->
                                        <!-- ถ้ำเขียนเต็ม จะได้แบบด้านล่าง -->
                                        <?php // echo "<li>" . htmlspecialchars($e) . "</li>"; 
                                        ?>
                                    <?php endforeach; ?>
                                </ul>
                            </div>
                        <?php endif; ?>
                    </div>
                    <div class="card-body">
                        <form action="" method="post">
                            <div class="row mb-3">
                                <div class="col-md-6">
                                    <label for="username" class="form-label">ชื่อผู้ใช้</label>
                                    <input type="text" id="username" name="username" class="form-control"
                                        placeholder="ชื่อผู้ใช้" require value="<?= isset($_POST['username']) ? htmlspecialchars($_POST['username']) : '' ?>">
                                </div>
                                <div class="col-md-6">
                                    <label for="fullname" class="form-label">ชื่อ-สกุล</label>
                                    <input type="text" id="fullname" name="fullname" class="form-control"
                                        placeholder="ชื่อ-สกุล" require value="<?= isset($_POST['fullname']) ? htmlspecialchars($_POST['fullname']) : '' ?>">
                                </div>
                            </div>
                            <div class="row mb-3">
                                <div class="col-md-6">
                                    <label for="email" class="form-label">Email</label>
                                    <input type="email" id="email" name="email" class="form-control"
                                        placeholder="Email" require value="<?= isset($_POST['email']) ? htmlspecialchars($_POST['email']) : '' ?>">
                                </div>
                                <div class="col-md-6">
                                    <label for="password" class="form-label">รหัสผ่าน</label>
                                    <input type="password" id="password" name="password" class="form-control"
                                        placeholder="รหัสผ่าน" require>
                                </div>
                            </div>
                            <div class="row mb-3">
                                <div class="col-md-6">
                                    <label for="confirm_password" class="form-label">ยืนยันรหัสผ่าน</label>
                                    <input type="password" id="confirm_password" name="confirm_password"
                                        class="form-control" placeholder="ยืนยันรหัสผ่าน" require>
                                </div>
                            </div>
                            <div class="d-grid gap-2">
                                <button type="submit" class="btn btn-success btn-lg">สมัครสมาชิก</button>
                            </div>
                        </form>
                    </div>
                    <div class="card-footer text-center">
                        <small class="text-muted">มีบัญชีแล้ว? <a href="login.php"
                                class="text-decoration-none">เข้าสู่ระบบ</a></small>
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