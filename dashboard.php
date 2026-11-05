<?php
require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/functions.php';

$user_id = $_SESSION['user_id'];

$stmt = $pdo->prepare("SELECT COUNT(*) as count FROM files WHERE user_id = ? AND is_deleted = 0");
$stmt->execute([$user_id]);
$total_files = $stmt->fetch()['count'];

$stmt = $pdo->prepare("SELECT COUNT(*) as count FROM folders WHERE user_id = ? AND is_deleted = 0");
$stmt->execute([$user_id]);
$total_folders = $stmt->fetch()['count'];

$used_storage = get_user_storage_used($user_id);
$quota = $_SESSION['storage_quota'];

$stmt = $pdo->prepare("SELECT * FROM files WHERE user_id = ? AND is_deleted = 0 ORDER BY created_at DESC LIMIT 5");
$stmt->execute([$user_id]);
$recent_files = $stmt->fetchAll();

$stmt = $pdo->prepare("SELECT * FROM files WHERE user_id = ? AND is_starred = 1 AND is_deleted = 0 LIMIT 5");
$stmt->execute([$user_id]);
$starred_files = $stmt->fetchAll();

include __DIR__ . '/includes/header.php';
include __DIR__ . '/includes/sidebar.php';
?>

<div class="container-fluid">
    <div class="row mb-4">
        <div class="col-12 d-flex justify-content-between align-items-center">
            <div>
                <h2 class="fw-bold">Dashboard</h2>
                <p class="text-muted mb-0">Welcome back, <?php echo e($_SESSION['user_name']); ?>!</p>
            </div>
            <div class="d-flex gap-2">
                <a href="<?php echo BASE_URL; ?>files.php" class="btn btn-primary"><i class="fa-solid fa-cloud-arrow-up me-2"></i> Upload Files</a>
            </div>
        </div>
    </div>

    <div class="row g-3 mb-4">
        <div class="col-md-3">
            <div class="card cv-card p-3 d-flex flex-row align-items-center gap-3">
                <div class="bg-primary bg-opacity-10 text-primary rounded-circle p-3 d-flex align-items-center justify-content-center" style="width: 50px; height: 50px;">
                    <i class="fa-solid fa-file fa-lg"></i>
                </div>
                <div>
                    <div class="text-muted small">Total Files</div>
                    <div class="h4 fw-bold mb-0"><?php echo $total_files; ?></div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card cv-card p-3 d-flex flex-row align-items-center gap-3">
                <div class="bg-success bg-opacity-10 text-success rounded-circle p-3 d-flex align-items-center justify-content-center" style="width: 50px; height: 50px;">
                    <i class="fa-solid fa-folder fa-lg"></i>
                </div>
                <div>
                    <div class="text-muted small">Total Folders</div>
                    <div class="h4 fw-bold mb-0"><?php echo $total_folders; ?></div>
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="card cv-card p-3">
                <div class="d-flex justify-content-between small text-muted mb-1">
                    <span>Storage Used</span>
                    <span><?php echo format_bytes($used_storage); ?> of <?php echo format_bytes($quota); ?></span>
                </div>
                <?php $pct = $quota > 0 ? min(100, round(($used_storage / $quota) * 100)) : 0; ?>
                <div class="progress mb-2" style="height: 8px;">
                    <div class="progress-bar bg-primary" role="progressbar" style="width: <?php echo $pct; ?>%"></div>
                </div>
                <div class="d-flex justify-content-between text-muted" style="font-size: 0.75rem;">
                    <span><?php echo $pct; ?>% capacity used</span>
                    <span><?php echo format_bytes($quota - $used_storage); ?> remaining</span>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-4">
        <div class="col-lg-8">
            <div class="card cv-card p-4">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h5 class="fw-bold mb-0"><i class="fa-solid fa-clock-rotate-left me-2 text-primary"></i> Recently Uploaded Files</h5>
                    <a href="<?php echo BASE_URL; ?>files.php" class="small text-decoration-none">View All</a>
                </div>
                <?php if (empty($recent_files)): ?>
                    <div class="text-center py-4 text-muted">
                        <i class="fa-solid fa-folder-open fa-2x mb-2"></i>
                        <p class="mb-0">No files uploaded yet.</p>
                    </div>
                <?php else: ?>
                    <div class="table-responsive">
                        <table class="table table-hover align-middle">
                            <thead class="table-light">
                                <tr>
                                    <th>Name</th>
                                    <th>Size</th>
                                    <th>Date</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($recent_files as $f): ?>
                                <tr>
                                    <td>
                                        <i class="fa-solid fa-file-lines text-primary me-2"></i>
                                        <?php echo e($f['original_name']); ?>
                                    </td>
                                    <td><?php echo format_bytes($f['file_size']); ?></td>
                                    <td><?php echo date('M d, Y', strtotime($f['created_at'])); ?></td>
                                    <td>
                                        <a href="<?php echo BASE_URL; ?>download.php?id=<?php echo $f['id']; ?>" class="btn btn-sm btn-outline-primary" title="Download"><i class="fa-solid fa-download"></i></a>
                                        <a href="<?php echo BASE_URL; ?>preview.php?id=<?php echo $f['id']; ?>" class="btn btn-sm btn-outline-secondary" title="Preview"><i class="fa-solid fa-eye"></i></a>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <div class="col-lg-4">
            <div class="card cv-card p-4 mb-4">
                <h5 class="fw-bold mb-3"><i class="fa-solid fa-bolt me-2 text-primary"></i> Quick Actions</h5>
                <div class="d-grid gap-2">
                    <a href="<?php echo BASE_URL; ?>files.php" class="btn btn-outline-primary text-start"><i class="fa-solid fa-cloud-arrow-up me-2"></i> Upload New File</a>
                    <a href="<?php echo BASE_URL; ?>starred.php" class="btn btn-outline-secondary text-start"><i class="fa-solid fa-star me-2 text-warning"></i> View Starred Items</a>
                    <a href="<?php echo BASE_URL; ?>trash.php" class="btn btn-outline-danger text-start"><i class="fa-solid fa-trash-can me-2"></i> Manage Trash Bin</a>
                </div>
            </div>

            <div class="card cv-card p-4">
                <h5 class="fw-bold mb-3"><i class="fa-solid fa-star me-2 text-warning"></i> Starred Items</h5>
                <?php if (empty($starred_files)): ?>
                    <p class="text-muted small mb-0">No starred items found.</p>
                <?php else: ?>
                    <ul class="list-unstyled mb-0">
                        <?php foreach ($starred_files as $sf): ?>
                        <li class="d-flex justify-content-between align-items-center py-2 border-bottom">
                            <span class="text-truncate" style="max-width: 180px;"><i class="fa-solid fa-file me-2 text-primary"></i><?php echo e($sf['original_name']); ?></span>
                            <a href="<?php echo BASE_URL; ?>download.php?id=<?php echo $sf['id']; ?>" class="btn btn-sm btn-link"><i class="fa-solid fa-download"></i></a>
                        </li>
                        <?php endforeach; ?>
                    </ul>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<?php include __DIR__ . '/includes/footer.php'; ?>