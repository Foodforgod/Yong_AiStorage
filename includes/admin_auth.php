<?php
require_once __DIR__ . '/auth.php';

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    http_response_code(403);
    die("Access Denied: Administrator Privileges Required.");
}
