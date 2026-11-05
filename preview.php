<?php
require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/functions.php';

$file_id = isset($_GET['id']) ? intval($_GET['id']) : 0;
$user_id = $_SESSION['user_id'];

$stmt = $pdo->prepare("SELECT * FROM files WHERE id = ? AND user_id = ? AND is_deleted = 0");
$stmt->execute([$file_id, $user_id]);
$file = $stmt->fetch();

if (!$file || !file_exists($file['file_path'])) {
    http_response_code(404);
    die("File not found.");
}

include __DIR__ . '/includes/header.php';
include __DIR__ . '/includes/sidebar.php';
?>

<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="fw-bold"><?php echo e($file['original_name']); ?></h2>
            <p class="text-muted small mb-0">Size: <?php echo format_bytes($file['file_size']); ?> | Type: <?php echo e($file['mime_type']); ?></p>
        </div>
        <div class="d-flex gap-2">
            <a href="download.php?id=<?php echo $file['id']; ?>" class="btn btn-primary"><i class="fa-solid fa-download me-2"></i> Download</a>
            <a href="files.php" class="btn btn-outline-secondary">Back</a>
        </div>
    </div>

    <div class="card cv-card p-4 text-center">
        <?php 
        $ext = strtolower($file['extension']);
        if (in_array($ext, ['jpg', 'jpeg', 'png', 'gif', 'webp'])):
        ?>
            <img src="data:<?php echo $file['mime_type']; ?>;base64,<?php echo base64_encode(file_get_contents($file['file_path'])); ?>" class="img-fluid rounded mx-auto" style="max-height: 600px;" alt="Preview">
        <?php elseif ($ext === 'pdf'): ?>
            <iframe src="data:application/pdf;base64,<?php echo base64_encode(file_get_contents($file['file_path'])); ?>" width="100%" height="600px" style="border: none;"></iframe>
        <?php elseif (in_array($ext, ['txt', 'md', 'html', 'css', 'js'])): ?>
            <pre class="bg-light p-3 text-start rounded" style="max-height: 500px; overflow-y: auto;"><?php echo e(file_get_contents($file['file_path'])); ?></pre>
        <?php else: ?>
            <div class="py-5">
                <i class="fa-solid fa-file-circle-exclamation fa-3x text-muted mb-3"></i>
                <h5>Preview unavailable for this file type.</h5>
                <p class="text-muted small">You can download this file to open it on your device.</p>
                <a href="download.php?id=<?php echo $file['id']; ?>" class="btn btn-primary mt-2">Download File</a>
            </div>
        <?php endif; ?>
    </div>
</div>

<?php include __DIR__ . '/includes/footer.php'; ?>