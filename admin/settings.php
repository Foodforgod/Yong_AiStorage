<?php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../includes/admin_auth.php';
require_once __DIR__ . '/../includes/functions.php';

$success = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $app_name = trim($_POST['app_name'] ?? '');
    $max_size = intval($_POST['max_upload_size'] ?? 52428800);

    $stmt = $pdo->prepare("UPDATE settings SET setting_value = ? WHERE setting_key = 'app_name'");
    $stmt->execute([$app_name]);

    $stmt = $pdo->prepare("UPDATE settings SET setting_value = ? WHERE setting_key = 'max_upload_size'");
    $stmt->execute([$max_size]);

    $success = 'Settings updated successfully.';
}

$app_name = get_setting('app_name', 'CloudVault');
$max_size = get_setting('max_upload_size', '52428800');

include __DIR__ . '/../includes/header.php';
include __DIR__ . '/../includes/sidebar.php';
?>

<div class="container-fluid" style="max-width: 600px;">
    <h2 class="fw-bold mb-1">System Settings</h2>
    <p class="text-muted mb-4">Configure global application parameters.</p>

    <?php if ($success): ?><div class="alert alert-success"><?php echo e($success); ?></div><?php endif; ?>

    <div class="card cv-card p-4">
        <form method="POST">
            <div class="mb-3">
                <label class="form-label small fw-semibold">Application Name</label>
                <input type="text" name="app_name" class="form-control" value="<?php echo e($app_name); ?>" required>
            </div>
            <div class="mb-3">
                <label class="form-label small fw-semibold">Max Upload Size (Bytes)</label>
                <input type="number" name="max_upload_size" class="form-control" value="<?php echo e($max_size); ?>" required>
            </div>
            <button type="submit" class="btn btn-primary w-100">Save Settings</button>
        </form>
    </div>
</div>

<?php include __DIR__ . '/../includes/footer.php'; ?>