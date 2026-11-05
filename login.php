<?php
require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/includes/functions.php';

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    if (!empty($email) && !empty($password)) {
        $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ?");
        $stmt->execute([$email]);
        $user = $stmt->fetch();

        if ($user && password_verify($password, $user['password'])) {
            if ($user['is_active'] == 0) {
                $error = 'Your account has been deactivated by the administrator.';
            } else {
                session_regenerate_id(true);
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['user_name'] = $user['name'];
                $_SESSION['user_email'] = $user['email'];
                $_SESSION['role'] = $user['role'];
                $_SESSION['storage_quota'] = $user['storage_quota'];

                log_activity($user['id'], 'Login', 'user', $user['id'], 'User logged into the system.');
                
                if ($user['role'] === 'admin') {
                    header("Location: " . BASE_URL . "admin/index.php");
                } else {
                    header("Location: " . BASE_URL . "dashboard.php");
                }
                exit;
            }
        } else {
            $error = 'Invalid email or password.';
        }
    } else {
        $error = 'Please fill in all fields.';
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - <?php echo e(get_setting('app_name', 'CloudVault')); ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css" rel="stylesheet">
    <link href="<?php echo BASE_URL; ?>assets/css/style.css" rel="stylesheet">
</head>
<body class="bg-light d-flex align-items-center justify-content-center min-vh-100">
    <div class="card cv-card p-4 shadow-sm" style="width: 100%; max-width: 400px;">
        <div class="text-center mb-4">
            <i class="fa-solid fa-cloud text-primary fa-3x mb-2"></i>
            <h3 class="fw-bold"><?php echo e(get_setting('app_name', 'CloudVault')); ?></h3>
            <p class="text-muted small">Sign in to your secure cloud storage</p>
        </div>
        <?php if (!empty($error)): ?>
            <div class="alert alert-danger py-2 small"><?php echo e($error); ?></div>
        <?php endif; ?>
        <form method="POST" action="">
            <div class="mb-3">
                <label class="form-label small fw-semibold">Email Address</label>
                <input type="email" name="email" class="form-control" required autofocus>
            </div>
            <div class="mb-3">
                <label class="form-label small fw-semibold">Password</label>
                <input type="password" name="password" class="form-control" required>
            </div>
            <button type="submit" class="btn btn-primary w-100 fw-semibold mb-3">Sign In</button>
        </form>
        <div class="text-center border-top pt-3">
            <span class="text-muted small">Don't have an account?</span>
            <a href="register.php" class="small fw-semibold text-decoration-none">Sign Up</a>
        </div>
    </div>
</body>
</html>