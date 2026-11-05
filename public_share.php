<?php
require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/includes/functions.php';

$token = trim($_GET['token'] ?? '');
$stmt = $pdo->prepare("SELECT * FROM shares WHERE token = ? AND is_active = 1");
$stmt->execute([$token]);
$share = $stmt->fetch();

if (!$share || $share['resource_type'] !== 'file') {
    http_response_code(404);
    die("Share link is invalid or expired.");
}

$stmt = $pdo->prepare("SELECT * FROM files WHERE id = ? AND is_deleted = 0");
$stmt->execute([$share['resource_id']]);
$file = $stmt->fetch();

if (!$file) {
    http_response_code(404);
    die("Shared file no longer exists.");
}

if (isset($_GET['download']) && $_GET['download'] == 1) {
    $stmt = $pdo->prepare("UPDATE shares SET view_count = view_count + 1 WHERE id = ?");
    $stmt->execute([$share['id']]);

    header('Content-Description: File Transfer');
    header('Content-Type: ' . $file['mime_type']);
    header('Content-Disposition: attachment; filename="' . basename($file['original_name']) . '"');
    header('Expires: 0');
    header('Cache-Control: must-revalidate');
    header('Pragma: public');
    header('Content-Length: ' . filesize($file['file_path']));
    readfile($file['file_path']);
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Shared File - <?php echo e($file['original_name']); ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css" rel="stylesheet">
    <link href="<?php echo BASE_URL; ?>assets/css/style.css" rel="stylesheet">
</head>
<body class="bg-light d-flex align-items-center justify-content-center min-vh-100">
    <div class="card cv-card p-4 text-center shadow-sm" style="width: 100%; max-width: 450px;">
        <i class="fa-solid fa-file-arrow-down text-primary fa-3x mb-3"></i>
        <h4 class="fw-bold mb-1"><?php echo e($file['original_name']); ?></h4>
        <p class="text-muted small mb-4">Size: <?php echo format_bytes($file['file_size']); ?></p>
        <a href="public_share.php?token=<?php echo e($token); ?>&download=1" class="btn btn-primary w-100"><i class="fa-solid fa-download me-2"></i> Download File</a>
    </div>
</body>
</html>