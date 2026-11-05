<?php
require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/functions.php';

$user_id = $_SESSION['user_id'];

if (isset($_GET['action'])) {
    if ($_GET['action'] === 'restore' && isset($_GET['id'])) {
        $stmt = $pdo->prepare("UPDATE files SET is_deleted = 0, deleted_at = NULL WHERE id = ? AND user_id = ?");
        $stmt->execute([intval($_GET['id']), $user_id]);
        header("Location: trash.php");
        exit;
    } elseif ($_GET['action'] === 'perm_delete' && isset($_GET['id'])) {
        $stmt = $pdo->prepare("SELECT file_path FROM files WHERE id = ? AND user_id = ?");
        $stmt->execute([intval($_GET['id']), $user_id]);
        $f = $stmt->fetch();
        if ($f && file_exists($f['file_path'])) {
            unlink($f['file_path']);
        }
        $stmt = $pdo->prepare("DELETE FROM files WHERE id = ? AND user_id = ?");
        $stmt->execute([intval($_GET['id']), $user_id]);
        header("Location: trash.php");
        exit;
    }
}

$stmt = $pdo->prepare("SELECT * FROM files WHERE user_id = ? AND is_deleted = 1 ORDER BY deleted_at DESC");
$stmt->execute([$user_id]);
$files = $stmt->fetchAll();

include __DIR__ . '/includes/header.php';
include __DIR__ . '/includes/sidebar.php';
?>

<div class="container-fluid">
    <h2 class="fw-bold mb-1">Trash Bin</h2>
    <p class="text-muted mb-4">Manage deleted files. Files can be restored or permanently removed.</p>

    <?php if (empty($files)): ?>
        <div class="card cv-card text-center py-5">
            <div class="card-body">
                <i class="fa-solid fa-trash-can fa-3x text-muted mb-3"></i>
                <h5>Your trash is empty</h5>
                <p class="text-muted small">Deleted items will appear here.</p>
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
                            <th>Deleted Date</th>
                            <th class="text-end">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($files as $file): ?>
                        <tr>
                            <td><i class="fa-solid fa-file-lines text-secondary me-2"></i><?php echo e($file['original_name']); ?></td>
                            <td><?php echo format_bytes($file['file_size']); ?></td>
                            <td><?php echo date('M d, Y H:i', strtotime($file['deleted_at'])); ?></td>
                            <td class="text-end">
                                <a href="trash.php?action=restore&id=<?php echo $file['id']; ?>" class="btn btn-sm btn-outline-success" title="Restore"><i class="fa-solid fa-rotate-left"></i> Restore</a>
                                <a href="trash.php?action=perm_delete&id=<?php echo $file['id']; ?>" class="btn btn-sm btn-outline-danger" title="Delete Permanently"><i class="fa-solid fa-trash"></i></a>
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