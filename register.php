<?php
require_once 'config.php';
if($_SERVER['REQUEST_METHOD'] === 'POST') {
    // รับค่าจากฟอร์ม
    $username = trim($_POST['username']);
    $fullname = trim($_POST['fullname']);
    $email = trim($_POST['email']);
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];

//นำข้อมูลลงฐานข้อมูล
    $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
    $sql = "INSERT INTO users(username,full_name,email,password,role) VALUES (?, ?, ?, ?, 'admin')";
    $stmt = $conn->prepare($sql);
    $stmt->execute([$username, $fullname, $email, $hashedPassword]);
    }
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/css/bootstrap.min.css" rel="stylesheet"
        integrity="sha384-LN+7fdVzj6u52u30Kp6M/trliBMCMKTyK833zpbD+pXdCLuT\usPj697FH4R/5mcr" crossorigin="anonymous">
</head>
<body>
    <div class="container mt-5">
    <h2>สมัครสมาชิก</h2>
    <form action=""method="post">
    <div class="row">
        <div class="col-md-6">
            <label for="username" class="form-label">ชื่อผู้ใช้</label>
            <input type="text" id="username" name="username" required class="form-control" placeholder="ชื่อผู้ใช้">
        
        </div>
        <div class="col-md-6">
            <label for="fullname" class="form-label">ชื่อ-สกุล</label>
            <input type="text" id="fullname" name="fullname" required class="form-control" placeholder="ชื่อ-สกุล">
        </div>
    </div>
    <div class="row">
        <div class="col-md-6">
            <label for="email" class="form-label">Email</label>
            <input type="email" id="email" name="email" required class="form-control" placeholder="Email">
        </div>
        <div class="col-md-6">
            <label for="password" class="form-label">รหัสผ่าน</label>
            <input type="password" id="password" name="password" required class="form-control" placeholder="รหัสผ่าน">
        </div>
    </div>
    <div class="row">
        <div class="col-md-6">
            <label for="confirm_password" class="form-label">ยืนยันรหัสผ่าน</label>
            <input type="password" id="confirm_password" name="confirm_password" required class="form-control" placeholder="ยืนยันรหัสผ่าน">
        </div>
    </div>
    <div class="mt-3">
        <button type="submit" class="btn btn-primary">สมัครสมาชิก</button>
        <a href="login.php" class="btn btn-link">เข้าสู่ระบบ</a>
    </div>
    </form>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/js/bootstrap.bundle.min.js"
    integrity="sha384-ndDqU0Gzau9qJ1lfW4pNLlhNTkCfHzAVBReH9diLvGRem5+R9g2FzA8ZGN954O5Q"
    crossorigin="anonymous"></script>
</body>
</html>