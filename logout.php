<?php
require_once __DIR__ . '/config/config.php';
if (isset($_SESSION['user_id'])) {
    require_once __DIR__ . '/includes/functions.php';
    log_activity($_SESSION['user_id'], 'Logout', 'user', $_SESSION['user_id'], 'User logged out.');
}
$_SESSION = [];
session_destroy();
header("Location: " . BASE_URL . "login.php");
exit;
