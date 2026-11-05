<?php
$current_page = $current_page ?? basename($_SERVER['PHP_SELF']);
?>
<nav id="sidebar">
    <div class="brand">
        <i class="fa-solid fa-cloud text-indigo"></i>
        <span><?php echo e(get_setting('app_name', 'CloudVault')); ?></span>
    </div>
    <div class="py-3">
        <ul class="nav flex-column gap-1">
            <li class="nav-item">
                <a class="nav-link <?php echo ($current_page == 'dashboard.php') ? 'active' : ''; ?>" href="<?php echo BASE_URL; ?>dashboard.php">
                    <i class="fa-solid fa-chart-pie"></i> Dashboard
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link <?php echo ($current_page == 'files.php') ? 'active' : ''; ?>" href="<?php echo BASE_URL; ?>files.php">
                    <i class="fa-solid fa-folder-open"></i> My Files
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link <?php echo ($current_page == 'recent.php') ? 'active' : ''; ?>" href="<?php echo BASE_URL; ?>recent.php">
                    <i class="fa-solid fa-clock-rotate-left"></i> Recent
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link <?php echo ($current_page == 'starred.php') ? 'active' : ''; ?>" href="<?php echo BASE_URL; ?>starred.php">
                    <i class="fa-solid fa-star"></i> Starred
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link <?php echo ($current_page == 'tags.php') ? 'active' : ''; ?>" href="<?php echo BASE_URL; ?>tags.php">
                    <i class="fa-solid fa-tags"></i> Tags
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link <?php echo ($current_page == 'trash.php') ? 'active' : ''; ?>" href="<?php echo BASE_URL; ?>trash.php">
                    <i class="fa-solid fa-trash-can"></i> Trash
                </a>
            </li>
            <?php if (isset($_SESSION['role']) && $_SESSION['role'] === 'admin'): ?>
            <li class="nav-item mt-3 pt-3 border-top border-secondary">
                <a class="nav-link text-warning <?php echo (strpos($current_page, 'admin') !== false) ? 'active' : ''; ?>" href="<?php echo BASE_URL; ?>admin/index.php">
                    <i class="fa-solid fa-shield-halved"></i> Admin Panel
                </a>
            </li>
            <?php endif; ?>
        </ul>
    </div>

    <?php 
    $used = get_user_storage_used($_SESSION['user_id'] ?? 0);
    $quota = $_SESSION['storage_quota'] ?? 10737418240;
    $pct = $quota > 0 ? min(100, round(($used / $quota) * 100)) : 0;
    ?>
    <div class="storage-meter">
        <div class="d-flex justify-content-between text-white small mb-1">
            <span>Storage</span>
            <span><?php echo format_bytes($used); ?> / <?php echo format_bytes($quota); ?></span>
        </div>
        <div class="progress" style="height: 6px;">
            <div class="progress-bar bg-primary" role="progressbar" style="width: <?php echo $pct; ?>%"></div>
        </div>
        <div class="text-muted text-end mt-1" style="font-size: 0.75rem;"><?php echo $pct; ?>% Used</div>
    </div>
</nav>

<nav class="top-navbar d-flex justify-content-between align-items-center">
    <div class="d-flex align-items-center gap-3">
        <button class="btn btn-sm btn-outline-secondary d-lg-none" id="sidebarToggle"><i class="fa-solid fa-bars"></i></button>
        <form action="<?php echo BASE_URL; ?>search.php" method="GET" class="d-none d-md-flex align-items-center">
            <div class="input-group input-group-sm">
                <span class="input-group-text bg-white border-end-0"><i class="fa-solid fa-search text-muted"></i></span>
                <input type="text" name="q" class="form-control border-start-0" placeholder="Search files and folders..." required>
            </div>
        </form>
    </div>
    <div class="d-flex align-items-center gap-3">
        <div class="dropdown">
            <a href="#" class="d-flex align-items-center text-decoration-none dropdown-toggle text-dark" id="userMenu" data-bs-toggle="dropdown">
                <div class="bg-primary text-white rounded-circle d-flex align-items-center justify-content-center me-2" style="width: 35px; height: 35px; font-weight: 600;">
                    <?php echo strtoupper(substr($_SESSION['user_name'] ?? 'U', 0, 1)); ?>
                </div>
                <span class="fw-medium d-none d-sm-inline"><?php echo e($_SESSION['user_name'] ?? 'User'); ?></span>
            </a>
            <ul class="dropdown-menu dropdown-menu-end shadow-sm border-0">
                <li><a class="dropdown-item" href="<?php echo BASE_URL; ?>profile.php"><i class="fa-solid fa-user-gear me-2"></i> Profile & Password</a></li>
                <li><hr class="dropdown-divider"></li>
                <li><a class="dropdown-item text-danger" href="<?php echo BASE_URL; ?>logout.php"><i class="fa-solid fa-right-from-bracket me-2"></i> Logout</a></li>
            </ul>
        </div>
    </div>
</nav>
<div id="main-content">