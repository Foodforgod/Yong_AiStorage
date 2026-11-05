<?php
require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/functions.php';

$user_id = $_SESSION['user_id'];
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $tag_name = trim($_POST['tag_name'] ?? '');
    if (!empty($tag_name)) {
        $stmt = $pdo->prepare("INSERT INTO tags (user_id, name) VALUES (?, ?)");
        $stmt->execute([$user_id, $tag_name]);
        header("Location: tags.php");
        exit;
    }
}

$stmt = $pdo->prepare("SELECT * FROM tags WHERE user_id = ? ORDER BY name ASC");
$stmt->execute([$user_id]);
$tags = $stmt->fetchAll();

include __DIR__ . '/includes/header.php';
include __DIR__ . '/includes/sidebar.php';
?>

<div class="container-fluid">
    <h2 class="fw-bold mb-1">Tags Management</h2>
    <p class="text-muted mb-4">Create and manage custom tags for your files.</p>

    <div class="row g-4">
        <div class="col-md-4">
            <div class="card cv-card p-4">
                <h5 class="fw-bold mb-3">Create New Tag</h5>
                <form method="POST">
                    <div class="mb-3">
                        <label class="form-label small fw-semibold">Tag Name</label>
                        <input type="text" name="tag_name" class="form-control" required>
                    </div>
                    <button type="submit" class="btn btn-primary w-100">Create Tag</button>
                </form>
            </div>
        </div>
        <div class="col-md-8">
            <div class="card cv-card p-4">
                <h5 class="fw-bold mb-3">Your Tags</h5>
                <?php if (empty($tags)): ?>
                    <p class="text-muted">No tags created yet.</p>
                <?php else: ?>
                    <div class="d-flex flex-wrap gap-2">
                        <?php foreach ($tags as $tag): ?>
                            <span class="badge bg-secondary p-2 fs-6"><i class="fa-solid fa-tag me-1"></i> <?php echo e($tag['name']); ?></span>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<?php include __DIR__ . '/includes/footer.php'; ?>