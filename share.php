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

$stmt = $pdo->prepare("SELECT * FROM shares WHERE resource_type = 'file' AND resource_id = ? AND user_id = ?");
$stmt->execute([$file_id, $user_id]);
$share = $stmt->fetch();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!$share) {
        $token = bin2hex(random_bytes(16));
        $stmt = $pdo->prepare("INSERT INTO shares (user_id, resource_type, resource_id, token) VALUES (?, 'file', ?, ?)");
        $stmt->execute([$user_id, $file_id, $token]);
    }
    header("Location: share.php?id=$file_id");
    exit;
}

include __DIR__ . '/includes/header.php';
include __DIR__ . '/includes/sidebar.php';
?>

<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="fw-bold">Share File</h2>
            <p class="text-muted mb-0"><?php echo e($file['original_name']); ?></p>
        </div>
        <a href="files.php" class="btn btn-outline-secondary">Back to Files</a>
    </div>

    <div class="card cv-card p-4" style="max-width: 600px;">
        <?php if ($share): ?>
            <div class="mb-3">
                <label class="form-label fw-semibold small">Public Share Link</label>
                <div class="input-group">
                    <input type="text" class="form-control" value="<?php echo BASE_URL; ?>public_share.php?token=<?php echo $share['token']; ?>" readonly>
                    <a href="public_share.php?token=<?php echo $share['token']; ?>" target="_blank" class="btn btn-outline-primary"><i class="fa-solid fa-external-link"></i></a>
                </div>
            </div>
            <p class="text-muted small">Anyone with this link can view and download this file.</p>
        <?php else: ?>
            <p class="text-muted">This file is not currently shared.</p>
            <form method="POST">
                <button type="submit" class="btn btn-primary">Generate Share Link</button>
            </form>
        <?php endif; ?>
    </div>
</div>

<?php include __DIR__ . '/includes/footer.php'; ?>