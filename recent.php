<?php
require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/functions.php';

$user_id = $_SESSION['user_id'];
$stmt = $pdo->prepare("SELECT * FROM files WHERE user_id = ? AND is_deleted = 0 ORDER BY created_at DESC LIMIT 25");
$stmt->execute([$user_id]);
$files = $stmt->fetchAll();

include __DIR__ . '/includes/header.php';
include __DIR__ . '/includes/sidebar.php';
?>

<div class="container-fluid">
    <h2 class="fw-bold mb-1">Recent Files</h2>
    <p class="text-muted mb-4">Files recently uploaded or modified in your storage.</p>

    <?php if (empty($files)): ?>
        <div class="card cv-card text-center py-5">
            <div class="card-body">
                <i class="fa-solid fa-clock-rotate-left fa-3x text-muted mb-3"></i>
                <h5>No recent files</h5>
                <p class="text-muted small">Upload files to see them here.</p>
            </div>
        </div>
    <?php else: ?>
        <div class="card cv-card">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Name</th>
                            <th>Size</th>
                            <th>Date</th>
                            <th class="text-end">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($files as $file): ?>
                        <tr>
                            <td><i class="fa-solid fa-file-lines text-primary me-2"></i><?php echo e($file['original_name']); ?></td>
                            <td><?php echo format_bytes($file['file_size']); ?></td>
                            <td><?php echo date('M d, Y H:i', strtotime($file['created_at'])); ?></td>
                            <td class="text-end">
                                <a href="download.php?id=<?php echo $file['id']; ?>" class="btn btn-sm btn-light"><i class="fa-solid fa-download"></i></a>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    <?php endif; ?>
</div>

<?php include __DIR__ . '/includes/footer.php'; ?>