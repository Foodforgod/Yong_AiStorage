<?php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../includes/admin_auth.php';
require_once __DIR__ . '/../includes/functions.php';

$stmt = $pdo->query("SELECT * FROM users ORDER BY created_at DESC");
$users = $stmt->fetchAll();

include __DIR__ . '/../includes/header.php';
include __DIR__ . '/../includes/sidebar.php';
?>

<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="fw-bold">User Management</h2>
            <p class="text-muted mb-0">Create, edit, and manage system user accounts.</p>
        </div>
        <a href="user_create.php" class="btn btn-primary"><i class="fa-solid fa-user-plus me-2"></i> Create User</a>
    </div>

    <div class="card cv-card">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th>Name</th>
                        <th>Email</th>
                        <th>Role</th>
                        <th>Quota</th>
                        <th>Status</th>
                        <th class="text-end">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($users as $u): ?>
                    <tr>
                        <td class="fw-medium"><?php echo e($u['name']); ?></td>
                        <td><?php echo e($u['email']); ?></td>
                        <td><span class="badge bg-<?php echo ($u['role'] === 'admin') ? 'danger' : 'primary'; ?>"><?php echo strtoupper(e($u['role'])); ?></span></td>
                        <td><?php echo format_bytes($u['storage_quota']); ?></td>
                        <td><span class="badge bg-<?php echo ($u['is_active'] ? 'success' : 'secondary'); ?>"><?php echo ($u['is_active'] ? 'Active' : 'Inactive'); ?></span></td>
                        <td class="text-end">
                            <a href="user_edit.php?id=<?php echo $u['id']; ?>" class="btn btn-sm btn-light"><i class="fa-solid fa-pen"></i></a>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php include __DIR__ . '/../includes/footer.php'; ?>