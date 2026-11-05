<?php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../includes/admin_auth.php';
require_once __DIR__ . '/../includes/functions.php';

$stmt = $pdo->query("SELECT f.*, u.email FROM files f JOIN users u ON f.user_id = u.id ORDER BY f.created_at DESC");
$files = $stmt->fetchAll();

include __DIR__ . '/../includes/header.php';
include __DIR__ . '/../includes/sidebar.php';
?>

<div class="container-fluid">
    <h2 class="fw-bold mb-1">All System Files</h2>
    <p class="text-muted mb-4">Overview of all files uploaded across all user accounts.</p>

    <div class="card cv-card">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th>Name</th>
                        <th>Owner</th>
                        <th>Size</th>
                        <th>Status</th>
                        <th>Created</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($files as $file): ?>
                    <tr>
                        <td><i class="fa-solid fa-file-lines text-primary me-2"></i><?php echo e($file['original_name']); ?></td>
                        <td><?php echo e($file['email']); ?></td>
                        <td><?php echo format_bytes($file['file_size']); ?></td>
                        <td><span class="badge bg-<?php echo ($file['is_deleted'] ? 'danger' : 'success'); ?>"><?php echo ($file['is_deleted'] ? 'Deleted' : 'Active'); ?></span></td>
                        <td><?php echo $file['created_at']; ?></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php include __DIR__ . '/../includes/footer.php'; ?>