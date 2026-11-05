<?php
require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/functions.php';

$user_id = $_SESSION['user_id'];
$query = trim($_GET['q'] ?? '');

$files = [];
if (!empty($query)) {
    $stmt = $pdo->prepare("SELECT * FROM files WHERE user_id = ? AND is_deleted = 0 AND original_name LIKE ? ORDER BY created_at DESC");
    $stmt->execute([$user_id, "%$query%"]);
    $files = $stmt->fetchAll();
}

include __DIR__ . '/includes/header.php';
include __DIR__ . '/includes/sidebar.php';
?>

<div class="container-fluid">
    <h2 class="fw-bold mb-1">Search Results</h2>
    <p class="text-muted mb-4">Showing results for: "<strong><?php echo e($query); ?></strong>"</p>

    <?php if (empty($files)): ?>
        <div class="card cv-card text-center py-5">
            <div class="card-body">
                <i class="fa-solid fa-search fa-3x text-muted mb-3"></i>
                <h5>No files found</h5>
                <p class="text-muted small">Try searching with a different keyword.</p>
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
                            <td>
                                <i class="fa-solid fa-file-lines text-primary me-2"></i>
                                <a href="preview.php?id=<?php echo $file['id']; ?>" class="text-decoration-none text-dark fw-medium"><?php echo e($file['original_name']); ?></a>
                            </td>
                            <td><?php echo format_bytes($file['file_size']); ?></td>
                            <td><?php echo date('M d, Y', strtotime($file['created_at'])); ?></td>
                            <td class="text-end">
                                <a href="download.php?id=<?php echo $file['id']; ?>" class="btn btn-sm btn-light" title="Download"><i class="fa-solid fa-download"></i></a>
                                <a href="preview.php?id=<?php echo $file['id']; ?>" class="btn btn-sm btn-light" title="Preview"><i class="fa-solid fa-eye"></i></a>
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