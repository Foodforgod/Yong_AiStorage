<?php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../includes/admin_auth.php';
require_once __DIR__ . '/../includes/functions.php';

$stmt = $pdo->query("SELECT COUNT(*) as count FROM users");
$total_users = $stmt->fetch()['count'];

$stmt = $pdo->query("SELECT COUNT(*) as count FROM files WHERE is_deleted = 0");
$total_files = $stmt->fetch()['count'];

$stmt = $pdo->query("SELECT SUM(file_size) as total FROM files WHERE is_deleted = 0");
$total_storage = $stmt->fetch()['total'] ?? 0;

include __DIR__ . '/../includes/header.php';
include __DIR__ . '/../includes/sidebar.php';
?>

<div class="container-fluid">
    <h2 class="fw-bold mb-1">Administrator Dashboard</h2>
    <p class="text-muted mb-4">System overview and management statistics.</p>

    <div class="row g-3 mb-4">
        <div class="col-md-4">
            <div class="card cv-card p-3 d-flex flex-row align-items-center gap-3">
                <div class="bg-primary bg-opacity-10 text-primary rounded-circle p-3 d-flex align-items-center justify-content-center" style="width: 50px; height: 50px;">
                    <i class="fa-solid fa-users fa-lg"></i>
                </div>
                <div>
                    <div class="text-muted small">Total Users</div>
                    <div class="h4 fw-bold mb-0"><?php echo $total_users; ?></div>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card cv-card p-3 d-flex flex-row align-items-center gap-3">
                <div class="bg-success bg-opacity-10 text-success rounded-circle p-3 d-flex align-items-center justify-content-center" style="width: 50px; height: 50px;">
                    <i class="fa-solid fa-file fa-lg"></i>
                </div>
                <div>
                    <div class="text-muted small">System Files</div>
                    <div class="h4 fw-bold mb-0"><?php echo $total_files; ?></div>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card cv-card p-3 d-flex flex-row align-items-center gap-3">
                <div class="bg-warning bg-opacity-10 text-warning rounded-circle p-3 d-flex align-items-center justify-content-center" style="width: 50px; height: 50px;">
                    <i class="fa-solid fa-hdd fa-lg"></i>
                </div>
                <div>
                    <div class="text-muted small">Total Storage Used</div>
                    <div class="h4 fw-bold mb-0"><?php echo format_bytes($total_storage); ?></div>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-3">
        <div class="col-md-3">
            <div class="card cv-card p-3">
                <a href="users.php" class="text-decoration-none fw-bold text-dark"><i class="fa-solid fa-users-gear me-2 text-primary"></i> Manage Users</a>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card cv-card p-3">
                <a href="logs.php" class="text-decoration-none fw-bold text-dark"><i class="fa-solid fa-list-check me-2 text-primary"></i> Activity Audit Logs</a>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card cv-card p-3">
                <a href="settings.php" class="text-decoration-none fw-bold text-dark"><i class="fa-solid fa-gear me-2 text-primary"></i> System Settings</a>
            </div>
        </div>
    </div>
</div>

<?php include __DIR__ . '/../includes/footer.php'; ?>