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
    die("File not found or unauthorized.");
}

log_activity($user_id, 'Download', 'file', $file_id, 'Downloaded file: ' . $file['original_name']);

header('Content-Description: File Transfer');
header('Content-Type: ' . $file['mime_type']);
header('Content-Disposition: attachment; filename="' . basename($file['original_name']) . '"');
header('Expires: 0');
header('Cache-Control: must-revalidate');
header('Pragma: public');
header('Content-Length: ' . filesize($file['file_path']));
readfile($file['file_path']);
exit;
