<?php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../includes/admin_auth.php';
require_once __DIR__ . '/../includes/functions.php';

$id = isset($_GET['id']) ? intval($_GET['id']) : 0;
$stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
$stmt->execute([$id]);
$target = $stmt->fetch();

if (!$target) {
    die("User not found.");
}

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name'] ?? '');
    $role = $_POST['role'] ?? 'user';
    $quota = intval($_POST['quota'] ?? 10737418240);
    $is_active = isset($_POST['is_active']) ? 1 : 0;

    if (!empty($name)) {
        $stmt = $pdo->prepare("UPDATE users SET name = ?, role = ?, storage_quota = ?, is_active = ? WHERE id = ?");
        $stmt->execute([$name, $role, $quota, $is_active, $id]);
        header("Location: users.php");
        exit;
    } else {
        $error = 'Name cannot be empty.';
    }
}

include __DIR__ . '/../includes/header.php';
include __DIR__ . '/../includes/sidebar.php';
?>

<div class="container-fluid" style="max-width: 600px;">
    <h2 class="fw-bold mb-1">Edit User</h2>
    <p class="text-muted mb-4">Modify user permissions and storage quota.</p>

    <?php if ($error): ?><div class="alert alert-danger"><?php echo e($error); ?></div><?php endif; ?>

    <div class="card cv-card p-4">
        <form method="POST">
            <div class="mb-3">
                <label class="form-label small fw-semibold">Full Name</label>
                <input type="text" name="name" class="form-control" value="<?php echo e($target['name']); ?>" required>
            </div>
            <div class="mb-3">
                <label class="form-label small fw-semibold">Email Address</label>
                <input type="email" class="form-control" value="<?php echo e($target['email']); ?>" disabled>
            </div>
            <div class="mb-3">
                <label class="form-label small fw-semibold">Role</label>
                <select name="role" class="form-select">
                    <option value="user" <?php echo ($target['role'] === 'user') ? 'selected' : ''; ?>>User</option>
                    <option value="admin" <?php echo ($target['role'] === 'admin') ? 'selected' : ''; ?>>Administrator</option>
                </select>
            </div>
            <div class="mb-3">
                <label class="form-label small fw-semibold">Storage Quota (Bytes)</label>
                <input type="number" name="quota" class="form-control" value="<?php echo $target['storage_quota']; ?>" required>
            </div>
            <div class="mb-3 form-check">
                <input type="checkbox" name="is_active" class="form-check-input" id="isActiveCheck" <?php echo ($target['is_active'] ? 'checked' : ''); ?>>
                <label class="form-check-label" for="isActiveCheck">Account Active</label>
            </div>
            <button type="submit" class="btn btn-primary w-100">Save Changes</button>
        </form>
    </div>
</div>

<?php include __DIR__ . '/../includes/footer.php'; ?>