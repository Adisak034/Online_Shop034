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
    <title>สมัครสมาชิก - BoboIT Shop</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-LN+7fdVzj6u52u30Kp6M/trliBMCMKTyK833zpbD+pXdCLuTusPj697FH4R/5mcr" crossorigin="anonymous">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css" rel="stylesheet">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Noto+Sans+Thai:wght@100..900&display=swap" rel="stylesheet">
    <style>
        html,
        body {
            height: 100%;
            font-family: "Noto Sans Thai", sans-serif;
        }

        body {
            display: flex;
            align-items: center;
            background-color: #f5f5f5;
        }

        .form-register {
            max-width: 500px;
            padding: 1rem;
        }

        .card {
            border-radius: 1rem;
            border: none;
        }

        .form-control {
            border-radius: 0.75rem;
            padding: 1rem;
        }

        .input-group-text {
            border-top-left-radius: 0.75rem;
            border-bottom-left-radius: 0.75rem;
        }
    </style>
</head>

<body>
    <main class="form-register w-100 m-auto">
        <div class="card shadow-lg">
            <div class="card-body p-4 p-md-5">
                <div class="text-center mb-4">
                    <h1 class="h3 mb-3 fw-bold text-warning"><i class="bi bi-shop"></i> BoboIT Shop</h1>
                    <h2 class="h4 mb-3 fw-normal">สร้างบัญชีใหม่</h2>
                </div>

                <?php if (!empty($error)) : ?>
                    <div class="alert alert-danger">
                        <?php foreach ($error as $e) : ?>
                            <div><i class="bi bi-exclamation-triangle-fill"></i> <?= htmlspecialchars($e) ?></div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>

                <form action="" method="post">
                    <div class="input-group mb-3">
                        <span class="input-group-text"><i class="bi bi-person"></i></span>
                        <input type="text" name="username" class="form-control" placeholder="ชื่อผู้ใช้" required value="<?= isset($_POST['username']) ? htmlspecialchars($_POST['username']) : '' ?>">
                    </div>

                    <div class="input-group mb-3">
                        <span class="input-group-text"><i class="bi bi-person-vcard"></i></span>
                        <input type="text" name="fullname" class="form-control" placeholder="ชื่อ-สกุล" required value="<?= isset($_POST['fullname']) ? htmlspecialchars($_POST['fullname']) : '' ?>">
                    </div>

                    <div class="input-group mb-3">
                        <span class="input-group-text"><i class="bi bi-envelope"></i></span>
                        <input type="email" name="email" class="form-control" placeholder="อีเมล" required value="<?= isset($_POST['email']) ? htmlspecialchars($_POST['email']) : '' ?>">
                    </div>

                    <div class="input-group mb-3">
                        <span class="input-group-text"><i class="bi bi-lock"></i></span>
                        <input type="password" name="password" class="form-control" placeholder="รหัสผ่าน" required>
                    </div>

                    <div class="input-group mb-4">
                        <span class="input-group-text"><i class="bi bi-lock-fill"></i></span>
                        <input type="password" name="confirm_password" class="form-control" placeholder="ยืนยันรหัสผ่าน" required>
                    </div>

                    <button class="w-100 btn btn-lg btn-warning" type="submit">สมัครสมาชิก</button>

                    <hr class="my-4">

                    <div class="text-center">
                        <small class="text-muted">มีบัญชีอยู่แล้ว? <a href="login.php" class="text-decoration-none">เข้าสู่ระบบที่นี่</a></small>
                    </div>
                </form>
            </div>
        </div>
    </main>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/js/bootstrap.bundle.min.js"
        integrity="sha384-ndDqU0Gzau9qJ1lfW4pNLlhNTkCfHzAVBReH9diLvGRem5+R9g2FzA8ZGN954O5Q" crossorigin="anonymous">
    </script>
</body>

</html>