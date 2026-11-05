<?php
require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/functions.php';
require_once __DIR__ . '/includes/csrf.php';

$success = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf_token($_POST['csrf_token'] ?? '');
    $action = $_POST['action'] ?? '';

    if ($action === 'update_profile') {
        $name = trim($_POST['name'] ?? '');
        if (!empty($name)) {
            $stmt = $pdo->prepare("UPDATE users SET name = ? WHERE id = ?");
            $stmt->execute([$name, $_SESSION['user_id']]);
            $_SESSION['user_name'] = $name;
            $success = 'Profile updated successfully.';
            log_activity($_SESSION['user_id'], 'Update Profile', 'user', $_SESSION['user_id'], 'Updated account name.');
        } else {
            $error = 'Name cannot be empty.';
        }
    } elseif ($action === 'change_password') {
        $current = $_POST['current_password'] ?? '';
        $new = $_POST['new_password'] ?? '';
        $confirm = $_POST['confirm_password'] ?? '';

        if (!empty($current) && !empty($new) && !empty($confirm)) {
            if ($new === $confirm) {
                $stmt = $pdo->prepare("SELECT password FROM users WHERE id = ?");
                $stmt->execute([$_SESSION['user_id']]);
                $user = $stmt->fetch();

                if ($user && password_verify($current, $user['password'])) {
                    $hash = password_hash($new, PASSWORD_DEFAULT);
                    $stmt = $pdo->prepare("UPDATE users SET password = ? WHERE id = ?");
                    $stmt->execute([$hash, $_SESSION['user_id']]);
                    $success = 'Password changed successfully.';
                    log_activity($_SESSION['user_id'], 'Change Password', 'user', $_SESSION['user_id'], 'Changed account password.');
                } else {
                    $error = 'Incorrect current password.';
                }
            } else {
                $error = 'New passwords do not match.';
            }
        } else {
            $error = 'Please fill in all password fields.';
        }
    }
}

include __DIR__ . '/includes/header.php';
include __DIR__ . '/includes/sidebar.php';
?>

<div class="container-fluid">
    <div class="row mb-4">
        <div class="col-12">
            <h2 class="fw-bold">Account Profile & Security</h2>
            <p class="text-muted">Manage your account information and security credentials.</p>
        </div>
    </div>

    <?php if ($success): ?><div class="alert alert-success"><?php echo e($success); ?></div><?php endif; ?>
    <?php if ($error): ?><div class="alert alert-danger"><?php echo e($error); ?></div><?php endif; ?>

    <div class="row g-4">
        <div class="col-md-6">
            <div class="card cv-card p-4">
                <h5 class="fw-bold mb-3"><i class="fa-solid fa-user me-2 text-primary"></i> Personal Information</h5>
                <form method="POST">
                    <input type="hidden" name="csrf_token" value="<?php echo generate_csrf_token(); ?>">
                    <input type="hidden" name="action" value="update_profile">
                    <div class="mb-3">
                        <label class="form-label small fw-semibold">Full Name</label>
                        <input type="text" name="name" class="form-control" value="<?php echo e($_SESSION['user_name']); ?>" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label small fw-semibold">Email Address</label>
                        <input type="email" class="form-control" value="<?php echo e($_SESSION['user_email']); ?>" disabled>
                        <div class="form-text">Email address cannot be changed directly.</div>
                    </div>
                    <button type="submit" class="btn btn-primary">Save Changes</button>
                </form>
            </div>
        </div>

        <div class="col-md-6">
            <div class="card cv-card p-4">
                <h5 class="fw-bold mb-3"><i class="fa-solid fa-lock me-2 text-primary"></i> Change Password</h5>
                <form method="POST">
                    <input type="hidden" name="csrf_token" value="<?php echo generate_csrf_token(); ?>">
                    <input type="hidden" name="action" value="change_password">
                    <div class="mb-3">
                        <label class="form-label small fw-semibold">Current Password</label>
                        <input type="password" name="current_password" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label small fw-semibold">New Password</label>
                        <input type="password" name="new_password" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label small fw-semibold">Confirm New Password</label>
                        <input type="password" name="confirm_password" class="form-control" required>
                    </div>
                    <button type="submit" class="btn btn-primary">Update Password</button>
                </form>
            </div>
        </div>
    </div>
</div>

<?php include __DIR__ . '/includes/footer.php'; ?>