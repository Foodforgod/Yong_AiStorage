<?php
session_start();
$step = isset($_GET['step']) ? intval($_GET['step']) : 1;
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $host = trim($_POST['db_host'] ?? 'localhost');
    $user = trim($_POST['db_user'] ?? 'root');
    $pass = $_POST['db_pass'] ?? '';
    $name = trim($_POST['db_name'] ?? 'cloudvault_db');

    $admin_name = trim($_POST['admin_name'] ?? '');
    $admin_email = trim($_POST['admin_email'] ?? '');
    $admin_pass = $_POST['admin_password'] ?? '';

    try {
        $pdo = new PDO("mysql:host=$host", $user, $pass, [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);
        
        $pdo->exec("CREATE DATABASE IF NOT EXISTS `$name` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;");
        $pdo->exec("USE `$name`;");

        $pdo->exec("
            CREATE TABLE IF NOT EXISTS settings (
                id INT AUTO_INCREMENT PRIMARY KEY,
                setting_key VARCHAR(100) NOT NULL UNIQUE,
                setting_value TEXT NOT NULL,
                updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
            ) ENGINE=InnoDB;
        ");
        
        $pdo->exec("
            CREATE TABLE IF NOT EXISTS users (
                id INT AUTO_INCREMENT PRIMARY KEY,
                name VARCHAR(100) NOT NULL,
                email VARCHAR(150) NOT NULL UNIQUE,
                password VARCHAR(255) NOT NULL,
                role ENUM('admin', 'user') NOT NULL DEFAULT 'user',
                storage_quota BIGINT NOT NULL DEFAULT 10737418240,
                is_active TINYINT(1) NOT NULL DEFAULT 1,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
            ) ENGINE=InnoDB;
        ");

        $pdo->exec("
            CREATE TABLE IF NOT EXISTS folders (
                id INT AUTO_INCREMENT PRIMARY KEY,
                user_id INT NOT NULL,
                parent_id INT DEFAULT NULL,
                name VARCHAR(255) NOT NULL,
                is_starred TINYINT(1) DEFAULT 0,
                is_deleted TINYINT(1) DEFAULT 0,
                deleted_at TIMESTAMP NULL DEFAULT NULL,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
            ) ENGINE=InnoDB;
        ");

        $pdo->exec("
            CREATE TABLE IF NOT EXISTS files (
                id INT AUTO_INCREMENT PRIMARY KEY,
                user_id INT NOT NULL,
                folder_id INT DEFAULT NULL,
                original_name VARCHAR(255) NOT NULL,
                stored_name VARCHAR(255) NOT NULL,
                file_path VARCHAR(500) NOT NULL,
                mime_type VARCHAR(100) NOT NULL,
                extension VARCHAR(50) NOT NULL,
                file_size BIGINT NOT NULL,
                is_starred TINYINT(1) DEFAULT 0,
                is_deleted TINYINT(1) DEFAULT 0,
                deleted_at TIMESTAMP NULL DEFAULT NULL,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
            ) ENGINE=InnoDB;
        ");

        $pdo->exec("
            CREATE TABLE IF NOT EXISTS file_versions (
                id INT AUTO_INCREMENT PRIMARY KEY,
                file_id INT NOT NULL,
                version_number INT NOT NULL,
                stored_name VARCHAR(255) NOT NULL,
                file_path VARCHAR(500) NOT NULL,
                file_size BIGINT NOT NULL,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
            ) ENGINE=InnoDB;
        ");

        $pdo->exec("
            CREATE TABLE IF NOT EXISTS tags (
                id INT AUTO_INCREMENT PRIMARY KEY,
                user_id INT NOT NULL,
                name VARCHAR(50) NOT NULL,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
            ) ENGINE=InnoDB;
        ");

        $pdo->exec("
            CREATE TABLE IF NOT EXISTS shares (
                id INT AUTO_INCREMENT PRIMARY KEY,
                user_id INT NOT NULL,
                resource_type ENUM('file', 'folder') NOT NULL,
                resource_id INT NOT NULL,
                token VARCHAR(64) NOT NULL UNIQUE,
                is_active TINYINT(1) DEFAULT 1,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
            ) ENGINE=InnoDB;
        ");

        $pdo->exec("
            CREATE TABLE IF NOT EXISTS activity_logs (
                id INT AUTO_INCREMENT PRIMARY KEY,
                user_id INT DEFAULT NULL,
                action VARCHAR(100) NOT NULL,
                resource_type VARCHAR(50) DEFAULT NULL,
                resource_id INT DEFAULT NULL,
                description TEXT,
                ip_address VARCHAR(45),
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
            ) ENGINE=InnoDB;
        ");

        $stmt = $pdo->prepare("INSERT IGNORE INTO settings (setting_key, setting_value) VALUES ('app_name', 'Yong_AiStorage'), ('max_upload_size', '52428800')");
        $stmt->execute();

        $hash = password_hash($admin_pass, PASSWORD_DEFAULT);
        $stmt = $pdo->prepare("INSERT INTO users (name, email, password, role) VALUES (?, ?, ?, 'admin') ON DUPLICATE KEY UPDATE password = ?");
        $stmt->execute([$admin_name, $admin_email, $hash, $hash]);

                        $config_content = "<?php\n" .
            "define('DB_HOST', '$host');\n" .
            "define('DB_USER', '$user');\n" .
            "define('DB_PASS', '$pass');\n" .
            "define('DB_NAME', '$name');\n\n" .
            "try {\n" .
            "    \$pdo = new PDO(\"mysql:host=\" . DB_HOST . \";dbname=\" . DB_NAME . \";charset=utf8mb4\", DB_USER, DB_PASS, [\n" .
            "        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,\n" .
            "        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,\n" .
            "        PDO::ATTR_EMULATE_PREPARES => false,\n" .
            "    ]);\n" .
            "} catch (PDOException \$e) {\n" .
            "    die(\"Database Connection Failure: \" . \$e->getMessage());\n" .
            "}\n";

        file_put_contents(__DIR__ . '/../config/database.php', $config_content);

        if (!is_dir(__DIR__ . '/../uploads')) {
            mkdir(__DIR__ . '/../uploads', 0755, true);
        }
        file_put_contents(__DIR__ . '/../uploads/.htaccess', "Options -Indexes
Deny from all
");

        header("Location: install.php?step=2");
        exit;
    } catch (Exception $e) {
        $error = $e->getMessage();
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Installation Wizard - Yong_AiStorage</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css" rel="stylesheet">
</head>
<body class="bg-light d-flex align-items-center justify-content-center min-vh-100">
    <div class="card shadow-sm p-4" style="width: 100%; max-width: 550px;">
        <div class="text-center mb-4">
            <i class="fa-solid fa-cloud text-primary fa-3x mb-2"></i>
            <h3 class="fw-bold">Yong_AiStorage Installation</h3>
        </div>

        <?php if ($step == 1): ?>
            <?php if ($error): ?><div class="alert alert-danger"><?php echo htmlspecialchars($error); ?></div><?php endif; ?>
            <form method="POST">
                <h5 class="fw-bold mb-3">Database Configuration</h5>
                <div class="mb-3">
                    <label class="form-label small fw-semibold">Database Host</label>
                    <input type="text" name="db_host" class="form-control" value="localhost" required>
                </div>
                <div class="mb-3">
                    <label class="form-label small fw-semibold">Database Username</label>
                    <input type="text" name="db_user" class="form-control" value="root" required>
                </div>
                <div class="mb-3">
                    <label class="form-label small fw-semibold">Database Password</label>
                    <input type="password" name="db_pass" class="form-control">
                </div>
                <div class="mb-3">
                    <label class="form-label small fw-semibold">Database Name</label>
                    <input type="text" name="db_name" class="form-control" value="cloudvault_db" required>
                </div>

                <h5 class="fw-bold mb-3 mt-4">Administrator Account</h5>
                <div class="mb-3">
                    <label class="form-label small fw-semibold">Admin Name</label>
                    <input type="text" name="admin_name" class="form-control" value="Administrator" required>
                </div>
                <div class="mb-3">
                    <label class="form-label small fw-semibold">Admin Email</label>
                    <input type="email" name="admin_email" class="form-control" value="admin@storage.local" required>
                </div>
                <div class="mb-3">
                    <label class="form-label small fw-semibold">Admin Password</label>
                    <input type="password" name="admin_password" class="form-control" required>
                </div>
                <button type="submit" class="btn btn-primary w-100 fw-semibold">Install Application</button>
            </form>
        <?php elseif ($step == 2): ?>
            <div class="text-center py-4">
                <i class="fa-solid fa-circle-check text-success fa-4x mb-3"></i>
                <h4 class="fw-bold">Installation Successful!</h4>
                <p class="text-muted small">Yong_AiStorage has been successfully installed and configured.</p>
                <a href="../login.php" class="btn btn-primary mt-3">Proceed to Login</a>
            </div>
        <?php endif; ?>
    </div>
</body>
</html>