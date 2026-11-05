<?php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../includes/admin_auth.php';
require_once __DIR__ . '/../includes/functions.php';

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $role = $_POST['role'] ?? 'user';
    $quota = intval($_POST['quota'] ?? 10737418240);

    if (!empty($name) && !empty($email) && !empty($password)) {
        $hash = password_hash($password, PASSWORD_DEFAULT);
        try {
            $stmt = $pdo->prepare("INSERT INTO users (name, email, password, role, storage_quota) VALUES (?, ?, ?, ?, ?)");
            $stmt->execute([$name, $email, $hash, $role, $quota]);
            log_activity($_SESSION['user_id'], 'Create User', 'user', $pdo->lastInsertId(), "Created user account: $email");
            header("Location: users.php");
            exit;
        } catch (PDOException $e) {
            $error = 'Email address already exists.';
        }
    } else {
        $error = 'Please fill in all required fields.';
    }
}

include __DIR__ . '/../includes/header.php';
include __DIR__ . '/../includes/sidebar.php';
?>

<div class="container-fluid" style="max-width: 600px;">
    <h2 class="fw-bold mb-1">Create User</h2>
    <p class="text-muted mb-4">Add a new normal or administrator user.</p>

    <?php if ($error): ?><div class="alert alert-danger"><?php echo e($error); ?></div><?php endif; ?>

    <div class="card cv-card p-4">
        <form method="POST">
            <div class="mb-3">
                <label class="form-label small fw-semibold">Full Name</label>
                <input type="text" name="name" class="form-control" required>
            </div>
            <div class="mb-3">
                <label class="form-label small fw-semibold">Email Address</label>
                <input type="email" name="email" class="form-control" required>
            </div>
            <div class="mb-3">
                <label class="form-label small fw-semibold">Password</label>
                <input type="password" name="password" class="form-control" required>
            </div>
            <div class="mb-3">
                <label class="form-label small fw-semibold">Role</label>
                <select name="role" class="form-select">
                    <option value="user">User</option>
                    <option value="admin">Administrator</option>
                </select>
            </div>
            <div class="mb-3">
                <label class="form-label small fw-semibold">Storage Quota (Bytes)</label>
                <input type="number" name="quota" class="form-control" value="10737418240" required>
                <div class="form-text">Default: 10GB (10737418240 bytes)</div>
            </div>
            <button type="submit" class="btn btn-primary w-100">Create User</button>
        </form>
    </div>
</div>

<?php include __DIR__ . '/../includes/footer.php'; ?>