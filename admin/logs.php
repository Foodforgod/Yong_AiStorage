<?php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../includes/admin_auth.php';
require_once __DIR__ . '/../includes/functions.php';

$stmt = $pdo->query("SELECT l.*, u.email FROM activity_logs l LEFT JOIN users u ON l.user_id = u.id ORDER BY l.created_at DESC LIMIT 100");
$logs = $stmt->fetchAll();

include __DIR__ . '/../includes/header.php';
include __DIR__ . '/../includes/sidebar.php';
?>

<div class="container-fluid">
    <h2 class="fw-bold mb-1">Activity Audit Logs</h2>
    <p class="text-muted mb-4">Monitor system actions and security events.</p>

    <div class="card cv-card">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th>User</th>
                        <th>Action</th>
                        <th>Description</th>
                        <th>IP Address</th>
                        <th>Timestamp</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($logs as $log): ?>
                    <tr>
                        <td><?php echo e($log['email'] ?? 'System'); ?></td>
                        <td><span class="badge bg-secondary"><?php echo e($log['action']); ?></span></td>
                        <td><?php echo e($log['description']); ?></td>
                        <td><?php echo e($log['ip_address']); ?></td>
                        <td><?php echo e($log['created_at']); ?></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php include __DIR__ . '/../includes/footer.php'; ?>