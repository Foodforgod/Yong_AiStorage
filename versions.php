<?php
require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/functions.php';

$file_id = isset($_GET['id']) ? intval($_GET['id']) : 0;
$user_id = $_SESSION['user_id'];

$stmt = $pdo->prepare("SELECT * FROM files WHERE id = ? AND user_id = ?");
$stmt->execute([$file_id, $user_id]);
$file = $stmt->fetch();

if (!$file) {
    die("File not found.");
}

$stmt = $pdo->prepare("SELECT * FROM file_versions WHERE file_id = ? ORDER BY version_number DESC");
$stmt->execute([$file_id]);
$versions = $stmt->fetchAll();

include __DIR__ . '/includes/header.php';
include __DIR__ . '/includes/sidebar.php';
?>

<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="fw-bold">Version History</h2>
            <p class="text-muted mb-0">Revisions for: <?php echo e($file['original_name']); ?></p>
        </div>
        <a href="files.php" class="btn btn-outline-secondary">Back to Files</a>
    </div>

    <div class="card cv-card p-4">
        <h5 class="fw-bold mb-3">Current Version</h5>
        <div class="d-flex justify-content-between align-items-center p-3 bg-light rounded mb-4">
            <div>
                <strong>Latest Revision</strong>
                <div class="text-muted small">Size: <?php echo format_bytes($file['file_size']); ?> | Updated: <?php echo $file['updated_at']; ?></div>
            </div>
            <a href="download.php?id=<?php echo $file['id']; ?>" class="btn btn-sm btn-primary">Download Current</a>
        </div>

        <h5 class="fw-bold mb-3">Previous Revisions</h5>
        <?php if (empty($versions)): ?>
            <p class="text-muted">No older version revisions recorded.</p>
        <?php else: ?>
            <div class="table-responsive">
                <table class="table table-hover align-middle">
                    <thead class="table-light">
                        <tr>
                            <th>Version</th>
                            <th>Size</th>
                            <th>Date</th>
                            <th class="text-end">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($versions as $ver): ?>
                        <tr>
                            <td>Version <?php echo $ver['version_number']; ?></td>
                            <td><?php echo format_bytes($ver['file_size']); ?></td>
                            <td><?php echo $ver['created_at']; ?></td>
                            <td class="text-end">
                                <a href="download.php?version_id=<?php echo $ver['id']; ?>" class="btn btn-sm btn-outline-primary">Download</a>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>
</div>

<?php include __DIR__ . '/includes/footer.php'; ?>