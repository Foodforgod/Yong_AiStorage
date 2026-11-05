<?php
require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/includes/functions.php';

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';

    if (empty($name) || empty($email) || empty($password) || empty($confirm_password)) {
        $error = 'Please fill in all fields.';
    } elseif ($password !== $confirm_password) {
        $error = 'Passwords do not match.';
    } elseif (strlen($password) < 6) {
        $error = 'Password must be at least 6 characters long.';
    } else {
        $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ?");
        $stmt->execute([$email]);
        if ($stmt->fetch()) {
            $error = 'An account with this email already exists.';
        } else {
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);
            $stmt = $pdo->prepare("INSERT INTO users (name, email, password, role, storage_quota) VALUES (?, ?, ?, 'user', 10737418240)");
            if ($stmt->execute([$name, $email, $hashed_password])) {
                $success = 'Registration successful! You can now sign in.';
            } else {
                $error = 'Registration failed. Please try again.';
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sign Up - <?php echo e(get_setting('app_name', 'CloudVault')); ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css" rel="stylesheet">
    <link href="<?php echo BASE_URL; ?>assets/css/style.css" rel="stylesheet">
</head>
<body class="bg-light d-flex align-items-center justify-content-center min-vh-100">
    <div class="card cv-card p-4 shadow-sm" style="width: 100%; max-width: 400px;">
        <div class="text-center mb-4">
            <i class="fa-solid fa-cloud text-primary fa-3x mb-2"></i>
            <h3 class="fw-bold">Create Account</h3>
            <p class="text-muted small">Sign up for secure cloud storage</p>
        </div>
        <?php if (!empty($error)): ?>
            <div class="alert alert-danger py-2 small"><?php echo e($error); ?></div>
        <?php endif; ?>
        <?php if (!empty($success)): ?>
            <div class="alert alert-success py-2 small"><?php echo e($success); ?></div>
        <?php endif; ?>
        <form method="POST" action="">
            <div class="mb-3">
                <label class="form-label small fw-semibold">Full Name</label>
                <input type="text" name="name" class="form-control" required autofocus value="<?php echo e($_POST['name'] ?? ''); ?>">
            </div>
            <div class="mb-3">
                <label class="form-label small fw-semibold">Email Address</label>
                <input type="email" name="email" class="form-control" required value="<?php echo e($_POST['email'] ?? ''); ?>">
            </div>
            <div class="mb-3">
                <label class="form-label small fw-semibold">Password</label>
                <input type="password" name="password" class="form-control" required>
            </div>
            <div class="mb-3">
                <label class="form-label small fw-semibold">Confirm Password</label>
                <input type="password" name="confirm_password" class="form-control" required>
            </div>
            <button type="submit" class="btn btn-primary w-100 fw-semibold mb-3">Sign Up</button>
        </form>
        <div class="text-center border-top pt-3">
            <span class="text-muted small">Already have an account?</span>
            <a href="login.php" class="small fw-semibold text-decoration-none">Sign In</a>
        </div>
    </div>
</body>
</html>