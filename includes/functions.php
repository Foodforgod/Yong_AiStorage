<?php
require_once __DIR__ . '/../config/database.php';

function e($string) {
    return htmlspecialchars($string ?? '', ENT_QUOTES, 'UTF-8');
}

function format_bytes($bytes, $precision = 2) {
    $units = ['B', 'KB', 'MB', 'GB', 'TB'];
    $bytes = max($bytes, 0);
    $pow = floor(($bytes ? log($bytes) : 0) / log(1024));
    $pow = min($pow, count($units) - 1);
    $bytes /= (1 << (10 * $pow));
    return round($bytes, $precision) . ' ' . $units[$pow];
}

function log_activity($user_id, $action, $resource_type = null, $resource_id = null, $description = null) {
    global $pdo;
    $ip = $_SERVER['REMOTE_ADDR'] ?? 'UNKNOWN';
    $stmt = $pdo->prepare("INSERT INTO activity_logs (user_id, action, resource_type, resource_id, description, ip_address) VALUES (?, ?, ?, ?, ?, ?)");
    $stmt->execute([$user_id, $action, $resource_type, $resource_id, $description, $ip]);
}

function get_user_storage_used($user_id) {
    global $pdo;
    $stmt = $pdo->prepare("SELECT SUM(file_size) as total FROM files WHERE user_id = ? AND is_deleted = 0");
    $stmt->execute([$user_id]);
    $res = $stmt->fetch();
    return $res['total'] ?? 0;
}

function get_setting($key, $default = '') {
    global $pdo;
    try {
        $stmt = $pdo->prepare("SELECT setting_value FROM settings WHERE setting_key = ?");
        $stmt->execute([$key]);
        $res = $stmt->fetch();
        return $res ? $res['setting_value'] : $default;
    } catch (Exception $e) {
        return $default;
    }
}
