<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
define('BASE_URL', 'http://localhost/Yong_AiStorage/');
define('UPLOAD_DIR', __DIR__ . '/../uploads/');
