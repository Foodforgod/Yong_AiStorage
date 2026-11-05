<?php
require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/functions.php';

$user_id = $_SESSION['user_id'];
if (isset($_GET['action']) && $_GET['action'] === 'delete' && isset($_GET['id'])) {
    $f_id = intval($_GET['id']);
    $stmt = $pdo->prepare("UPDATE folders SET is_deleted = 1, deleted_at = NOW() WHERE id = ? AND user_id = ?");
    $stmt->execute([$f_id, $user_id]);
    log_activity($user_id, 'Delete Folder', 'folder', $f_id, 'Moved folder to trash.');
}
header("Location: files.php");
exit;
