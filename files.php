<?php
require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/functions.php';
require_once __DIR__ . '/includes/csrf.php';

$user_id = $_SESSION['user_id'];
$folder_id = isset($_GET['folder_id']) ? intval($_GET['folder_id']) : null;

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'create_folder') {
    verify_csrf_token($_POST['csrf_token'] ?? '');
    $folder_name = trim($_POST['folder_name'] ?? '');
    if (!empty($folder_name)) {
        $stmt = $pdo->prepare("INSERT INTO folders (user_id, parent_id, name) VALUES (?, ?, ?)");
        $stmt->execute([$user_id, $folder_id, $folder_name]);
        log_activity($user_id, 'Create Folder', 'folder', $pdo->lastInsertId(), "Created folder: $folder_name");
        header("Location: files.php" . ($folder_id ? "?folder_id=$folder_id" : ""));
        exit;
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['file'])) {
    verify_csrf_token($_POST['csrf_token'] ?? '');
    $file = $_FILES['file'];
    if ($file['error'] === UPLOAD_ERR_OK) {
        $file_size = $file['size'];
        $current_used = get_user_storage_used($user_id);
        $quota = $_SESSION['storage_quota'];

        if (($current_used + $file_size) > $quota) {
            $upload_error = "Storage quota exceeded.";
        } else {
            $max_size = (int)get_setting('max_upload_size', 52428800);
            if ($file_size > $max_size) {
                $upload_error = "File exceeds maximum allowable size.";
            } else {
                $original_name = basename($file['name']);
                $ext = strtolower(pathinfo($original_name, PATHINFO_EXTENSION));
                
                $blocked_exts = ['php', 'php3', 'php4', 'php5', 'phtml', 'phar', 'exe', 'bat', 'cmd', 'sh', 'cgi', 'pl', 'py', 'vbs', 'msi', 'dll'];
                if (in_array($ext, $blocked_exts)) {
                    $upload_error = "File type is forbidden for security reasons.";
                } else {
                    $stored_name = bin2hex(random_bytes(16)) . '_' . $original_name;
                    $dest_path = UPLOAD_DIR . $stored_name;

                    if (!is_dir(UPLOAD_DIR)) {
                        mkdir(UPLOAD_DIR, 0755, true);
                    }

                    if (move_uploaded_file($file['tmp_name'], $dest_path)) {
                        $stmt = $pdo->prepare("SELECT id, stored_name, file_path, file_size FROM files WHERE user_id = ? AND folder_id " . ($folder_id ? "= ?" : "IS NULL") . " AND original_name = ? AND is_deleted = 0");
                        if ($folder_id) {
                            $stmt->execute([$user_id, $folder_id, $original_name]);
                        } else {
                            $stmt->execute([$user_id, $original_name]);
                        }
                        $existing = $stmt->fetch();

                        if ($existing) {
                            $file_id = $existing['id'];
                            $stmt_v_count = $pdo->prepare("SELECT COUNT(*) as count FROM file_versions WHERE file_id = ?");
                            $stmt_v_count->execute([$file_id]);
                            $v_num = $stmt_v_count->fetch()['count'] + 1;

                            $stmt_ver = $pdo->prepare("INSERT INTO file_versions (file_id, version_number, stored_name, file_path, file_size) VALUES (?, ?, ?, ?, ?)");
                            $stmt_ver->execute([$file_id, $v_num, $existing['stored_name'], $existing['file_path'], $existing['file_size']]);

                            $stmt_up = $pdo->prepare("UPDATE files SET stored_name = ?, file_path = ?, file_size = ?, updated_at = NOW() WHERE id = ?");
                            $stmt_up->execute([$stored_name, $dest_path, $file_size, $file_id]);
                            log_activity($user_id, 'Upload Revision', 'file', $file_id, "Uploaded new revision for $original_name");
                        } else {
                            $stmt_in = $pdo->prepare("INSERT INTO files (user_id, folder_id, original_name, stored_name, file_path, mime_type, extension, file_size) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
                            $stmt_in->execute([$user_id, $folder_id, $original_name, $stored_name, $dest_path, $file['type'], $ext, $file_size]);
                            log_activity($user_id, 'Upload File', 'file', $pdo->lastInsertId(), "Uploaded file: $original_name");
                        }
                        header("Location: files.php" . ($folder_id ? "?folder_id=$folder_id" : ""));
                        exit;
                    } else {
                        $upload_error = "Failed to move uploaded file.";
                    }
                }
            }
        }
    }
}

$current_folder_name = "My Storage";
$parent_folder_id = null;
if ($folder_id) {
    $stmt = $pdo->prepare("SELECT * FROM folders WHERE id = ? AND user_id = ? AND is_deleted = 0");
    $stmt->execute([$folder_id, $user_id]);
    $folder = $stmt->fetch();
    if ($folder) {
        $current_folder_name = $folder['name'];
        $parent_folder_id = $folder['parent_id'];
    } else {
        header("Location: files.php");
        exit;
    }
}

$stmt = $pdo->prepare("SELECT * FROM folders WHERE user_id = ? AND parent_id " . ($folder_id ? "= ?" : "IS NULL") . " AND is_deleted = 0 ORDER BY name ASC");
if ($folder_id) {
    $stmt->execute([$user_id, $folder_id]);
} else {
    $stmt->execute([$user_id]);
}
$folders = $stmt->fetchAll();

$stmt = $pdo->prepare("SELECT * FROM files WHERE user_id = ? AND folder_id " . ($folder_id ? "= ?" : "IS NULL") . " AND is_deleted = 0 ORDER BY original_name ASC");
if ($folder_id) {
    $stmt->execute([$user_id, $folder_id]);
} else {
    $stmt->execute([$user_id]);
}
$files = $stmt->fetchAll();

include __DIR__ . '/includes/header.php';
include __DIR__ . '/includes/sidebar.php';
?>

<div class="container-fluid">
    <div class="row mb-4 align-items-center">
        <div class="col-md-6">
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-1">
                    <li class="breadcrumb-item"><a href="files.php">My Storage</a></li>
                    <?php if ($folder_id): ?>
                        <li class="breadcrumb-item active" aria-current="page"><?php echo e($current_folder_name); ?></li>
                    <?php endif; ?>
                </ol>
            </nav>
            <h2 class="fw-bold mb-0"><?php echo e($current_folder_name); ?></h2>
        </div>
        <div class="col-md-6 text-md-end mt-3 mt-md-0 d-flex justify-content-md-end gap-2">
            <button class="btn btn-outline-primary" data-bs-toggle="modal" data-bs-target="#newFolderModal"><i class="fa-solid fa-folder-plus me-2"></i> New Folder</button>
            <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#uploadModal"><i class="fa-solid fa-cloud-arrow-up me-2"></i> Upload File</button>
        </div>
    </div>

    <?php if (isset($upload_error)): ?>
        <div class="alert alert-danger"><?php echo e($upload_error); ?></div>
    <?php endif; ?>

    <?php if (!empty($folders)): ?>
        <h6 class="text-muted fw-bold mb-3">Folders</h6>
        <div class="row g-3 mb-4">
            <?php foreach ($folders as $fld): ?>
            <div class="col-md-3 col-sm-6">
                <div class="card cv-card p-3 d-flex flex-row align-items-center justify-content-between">
                    <a href="files.php?folder_id=<?php echo $fld['id']; ?>" class="text-decoration-none text-dark d-flex align-items-center gap-2 text-truncate">
                        <i class="fa-solid fa-folder text-warning fa-lg"></i>
                        <span class="fw-medium text-truncate"><?php echo e($fld['name']); ?></span>
                    </a>
                    <div class="dropdown">
                        <button class="btn btn-sm btn-light" data-bs-toggle="dropdown"><i class="fa-solid fa-ellipsis-vertical"></i></button>
                        <ul class="dropdown-menu dropdown-menu-end shadow-sm">
                            <li><a class="dropdown-item text-danger" href="folders.php?action=delete&id=<?php echo $fld['id']; ?>"><i class="fa-solid fa-trash me-2"></i> Delete</a></li>
                        </ul>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>

    <h6 class="text-muted fw-bold mb-3">Files</h6>
    <?php if (empty($files) && empty($folders)): ?>
        <div class="card cv-card text-center py-5">
            <div class="card-body">
                <i class="fa-solid fa-folder-open fa-3x text-muted mb-3"></i>
                <h5 class="fw-bold">This folder is empty</h5>
                <p class="text-muted small">Upload files or create folders to get started.</p>
                <button class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#uploadModal">Upload File</button>
            </div>
        </div>
    <?php elseif (!empty($files)): ?>
        <div class="card cv-card">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Name</th>
                            <th>Type</th>
                            <th>Size</th>
                            <th>Modified</th>
                            <th class="text-end">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($files as $file): ?>
                        <tr>
                            <td>
                                <i class="fa-solid fa-file-lines text-primary me-2"></i>
                                <a href="preview.php?id=<?php echo $file['id']; ?>" class="text-decoration-none text-dark fw-medium"><?php echo e($file['original_name']); ?></a>
                            </td>
                            <td><span class="badge bg-secondary bg-opacity-10 text-secondary"><?php echo strtoupper(e($file['extension'])); ?></span></td>
                            <td><?php echo format_bytes($file['file_size']); ?></td>
                            <td><?php echo date('M d, Y H:i', strtotime($file['updated_at'])); ?></td>
                            <td class="text-end">
                                <a href="download.php?id=<?php echo $file['id']; ?>" class="btn btn-sm btn-light" title="Download"><i class="fa-solid fa-download"></i></a>
                                <a href="preview.php?id=<?php echo $file['id']; ?>" class="btn btn-sm btn-light" title="Preview"><i class="fa-solid fa-eye"></i></a>
                                <div class="dropdown d-inline-block">
                                    <button class="btn btn-sm btn-light" data-bs-toggle="dropdown"><i class="fa-solid fa-ellipsis-vertical"></i></button>
                                    <ul class="dropdown-menu dropdown-menu-end shadow-sm">
                                        <li><a class="dropdown-item" href="versions.php?id=<?php echo $file['id']; ?>"><i class="fa-solid fa-code-commit me-2"></i> Versions</a></li>
                                        <li><a class="dropdown-item" href="share.php?id=<?php echo $file['id']; ?>&type=file"><i class="fa-solid fa-share-nodes me-2"></i> Share</a></li>
                                        <li><hr class="dropdown-divider"></li>
                                        <li><a class="dropdown-item text-danger" href="files.php?action=delete&id=<?php echo $file['id']; ?>"><i class="fa-solid fa-trash me-2"></i> Delete</a></li>
                                    </ul>
                                </div>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    <?php endif; ?>
</div>

<div class="modal fade" id="uploadModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="POST" enctype="multipart/form-data">
                <input type="hidden" name="csrf_token" value="<?php echo generate_csrf_token(); ?>">
                <div class="modal-header">
                    <h5 class="modal-title fw-bold">Upload File</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label small fw-semibold">Select File</label>
                        <input type="file" name="file" class="form-control" required>
                        <div class="form-text">Uploading to current folder. Existing filename will trigger version history.</div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Upload</button>
                </div>
            </form>
        </div>
    </div>
</div>

<div class="modal fade" id="newFolderModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="POST">
                <input type="hidden" name="csrf_token" value="<?php echo generate_csrf_token(); ?>">
                <input type="hidden" name="action" value="create_folder">
                <div class="modal-header">
                    <h5 class="modal-title fw-bold">Create New Folder</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label small fw-semibold">Folder Name</label>
                        <input type="text" name="folder_name" class="form-control" required autofocus>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Create</button>
                </div>
            </form>
        </div>
    </div>
</div>

<?php 
if (isset($_GET['action']) && $_GET['action'] === 'delete' && isset($_GET['id'])) {
    $del_id = intval($_GET['id']);
    $stmt = $pdo->prepare("UPDATE files SET is_deleted = 1, deleted_at = NOW() WHERE id = ? AND user_id = ?");
    $stmt->execute([$del_id, $user_id]);
    log_activity($user_id, 'Delete File', 'file', $del_id, 'Moved file to trash.');
    header("Location: files.php" . ($folder_id ? "?folder_id=$folder_id" : ""));
    exit;
}
include __DIR__ . '/includes/footer.php';
?>