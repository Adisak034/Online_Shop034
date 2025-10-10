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
    <title>เข้าสู่ระบบ - BoboIT Shop</title>
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

        .form-signin {
            max-width: 420px;
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
    <main class="form-signin w-100 m-auto">
        <div class="card shadow-lg">
            <div class="card-body p-4 p-md-5">
                <div class="text-center mb-4"> 
                    <h1 class="h3 mb-3 fw-bold text-warning"><i class="bi bi-shop"></i> BoboIT Shop</h1>
                    <h2 class="h4 mb-3 fw-normal">เข้าสู่ระบบ</h2>
                </div>

                <!-- Success message for registration -->
                <?php if (isset($_GET['register']) && $_GET['register'] === 'success') : ?>
                    <div class="alert alert-success"><i class="bi bi-check-circle-fill"></i> สมัครสมาชิกสำเร็จ! กรุณาเข้าสู่ระบบ</div>
                <?php endif; ?>
                <!-- Timeout message -->
                <?php if (isset($_GET['timeout']) && $_GET['timeout'] === '1') : ?>
                    <div class="alert alert-warning"><i class="bi bi-clock-history"></i> เซสชั่นหมดอายุ กรุณาเข้าสู่ระบบอีกครั้ง</div>
                <?php endif; ?>
                <!-- Error message -->
                <?php if (!empty($error)) : ?>
                    <div class="alert alert-danger"><i class="bi bi-exclamation-triangle-fill"></i> <?= htmlspecialchars($error) ?></div>
                <?php endif; ?>

                <form method="post">
                    <div class="input-group mb-3">
                        <span class="input-group-text"><i class="bi bi-person"></i></span>
                        <input type="text" name="username_or_email" class="form-control" placeholder="ชื่อผู้ใช้ หรือ อีเมล" required value="<?= isset($_POST['username_or_email']) ? htmlspecialchars($_POST['username_or_email']) : '' ?>">
                    </div>

                    <div class="input-group mb-3">
                        <span class="input-group-text"><i class="bi bi-lock"></i></span>
                        <input type="password" name="password" class="form-control" placeholder="รหัสผ่าน" required>
                    </div>

                    <button class="w-100 btn btn-lg btn-warning" type="submit">เข้าสู่ระบบ</button>

                    <hr class="my-4">

                    <div class="text-center">
                        <small class="text-muted">ยังไม่มีบัญชี? <a href="register.php" class="text-decoration-none">สมัครสมาชิกที่นี่</a></small>
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