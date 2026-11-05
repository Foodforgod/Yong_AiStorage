# Yong_AiStorage — Enterprise Digital Asset & Cloud File Management System

[![PHP Version](https://img.shields.io/badge/PHP-8.3%2B-777BB4?style=flat-square&logo=php&logoColor=white)](https://php.net)
[![Database](https://img.shields.io/badge/Database-MySQL%2FMariaDB-4479A1?style=flat-square&logo=mysql&logoColor=white)](https://mysql.com)
[![Frontend](https://img.shields.io/badge/UI Framework-Bootstrap_5.3-7952B3?style=flat-square&logo=bootstrap&logoColor=white)](https://getbootstrap.com)
[![Security Policy](https://img.shields.io/badge/Security-Prepared_Statements_%7C_CSRF_%7C_XSS-success?style=flat-square)](https://owasp.org)

**Yong_AiStorage** is a responsive, web-based digital file storage, management, and sharing platform built natively with PHP 8.3+, PDO MySQL, HTML5, CSS3, and Vanilla JavaScript. Engineered with modern SaaS user-experience patterns, it provides secure data isolation, custom directory architecture, file versioning, public link distribution, activity audit tracking, and administrator control.

---

## 🛠 Tech Stack & Architecture

### Backend Architecture
* **PHP 8.3+**: Built with native, framework-agnostic PHP using clean object/procedural includes without external vendor dependencies or heavyweight runtimes.
* **Database Driver**: Native `PDO` engine configured with explicit SQL parameter bindings (`PDO::ERRMODE_EXCEPTION`, `PDO::FETCH_ASSOC`, `PDO::ATTR_EMULATE_PREPARES = false`).
* **Relational Store**: Optimized MySQL / MariaDB schema enforcing foreign key cascades and composite indexing.

### Frontend Technology
* **Styling & Layout**: Bootstrap 5.3 (via CDN) integrated with custom visual themes (`assets/css/style.css`).
* **Typography & Icons**: Font Awesome 6 Pro iconography and Google Inter fonts.
* **Client Behavior**: Pure Vanilla JavaScript (ES6+) utilizing asynchronous Fetch API request handlers and dynamic Bootstrap Toast mechanisms.

---

## 🌟 Key Application Features

### 📁 Storage & Folder Hierarchy
<img width="1915" height="1027" alt="image" src="https://github.com/user-attachments/assets/67c4763f-23d7-46ee-9931-5408f9964d24" />

* **Nested Directory Structures**: Unlimited sub-directory nesting using parent-child relational mappings (`parent_id`).
* **Breadcrumb Navigation**: Path indicators across nested folder trees.
* **Grid & List Displays**: Dynamic visual switching for file and folder views.

### 🔐 Security & User Data Isolation
* **Strict Ownership Validation**: All backend CRUD operations filter explicitly by `user_id` to prevent insecure direct object reference (IDOR) exploits.
* **Sanitized File Persistence**: Uploaded files are obfuscated on disk using 128-bit cryptographically secure hexadecimal tokens (`bin2hex(random_bytes(16))`), hiding original filenames from direct URL traversal.
* **Executable Shielding**: Directory-level `.htaccess` protection blocking PHP and executable script instantiation inside `/uploads/`.
* **State Protection**: Cryptographic Anti-CSRF verification tokens enforced on all POST actions (`generate_csrf_token()`).
* **Output Escaping**: Protection against Cross-Site Scripting (XSS) using contextual UTF-8 HTML entity encoding (`e()`).

### 🔄 Advanced File Services
<img width="1912" height="1092" alt="image" src="https://github.com/user-attachments/assets/760e2ed9-5b47-4b19-aace-6fa6eba41a9b" />

* **Revision Control & Rollbacks**: Automatic version incrementing (`file_versions`) when uploading files with matching names.
* **Public Link Sharing**: Tokenized sharing links with standalone public view controllers (`public_share.php?token=...`).
* **Soft Deletion & Lifecycle Management**: Trash recovery bin (`is_deleted = 1`) with options to perform granular restorations or permanent physical storage purges.
* **Custom User Tagging**: Categorization system to search and aggregate files by custom meta tags.

### 🛡 Administrative Control
* **User & Quota Administration**: Configure per-user maximum byte storage limits, role modification (User vs. Admin), and instant account deactivation.
* **Audit Logging**: Comprehensive activity tracking capturing user ID, timestamp, target resource, action type, and IP address.
* **Global System Policies**: Configure maximum allowable upload file sizes and application branding variables.

---

## 📂 Project Directory Structure

```text
Yong_AiStorage/
├── admin/                  # Administrative Control Center
│   ├── files.php           # Global file inspector
│   ├── index.php           # Admin metrics and system analytics
│   ├── logs.php            # Security audit log visualizer
│   ├── settings.php        # Global environment settings
│   ├── user_create.php     # Manual user onboarding wizard
│   ├── user_edit.php       # Account privileges & quota updater
│   └── users.php           # Complete user list dashboard
├── assets/                 # Frontend Asset Pipeline
│   ├── css/
│   │   └── style.css       # Custom SaaS dashboard styles
│   └── js/
│       └── app.js          # Interactive UX & Toast handlers
├── config/                 # Environment Configuration
│   ├── config.php          # Session initialization & system constants
│   └── database.php        # PDO database wrapper & credentials
├── includes/               # Reusable Architecture Components
│   ├── admin_auth.php      # Administrator privilege validator
│   ├── auth.php            # Active session validator
│   ├── csrf.php            # Anti-CSRF token verification framework
│   ├── footer.php          # Base HTML footer and JS includes
│   ├── functions.php       # Helper library (Sanitization, Storage, Logs)
│   ├── header.php          # HTML head section & CSS includes
│   └── sidebar.php         # Responsive sidebar & storage quota meter
├── install/                # Setup & Deployment Wizard
│   └── install.php         # Automated installer and schema writer
├── uploads/                # Managed Storage Store
│   └── .htaccess           # Execution denial rules
├── dashboard.php           # Core user metrics dashboard
├── download.php            # Streaming file download gateway
├── files.php               # File Explorer & main directory view
├── folders.php             # Folder deletion & path routing
├── index.php               # Root entry redirection script
├── login.php               # Secure login controller
├── logout.php              # Session termination controller
├── preview.php             # Document and inline image viewer
├── profile.php             # User profile details and password manager
├── public_share.php        # Tokenized public download gateway
├── README.md               # Enterprise system documentation
├── recent.php              # Newly uploaded & modified assets list
├── register.php            # Account registration portal
├── search.php              # File & folder search index
├── share.php               # Share token generator interface
├── starred.php             # Starred files quick index
├── tags.php                # Custom tag manager
├── trash.php               # Soft-delete recycle bin
└── versions.php            # File revision control ledger
```

---

## 🚀 Step-by-Step Installation Guide

### Option 1: Local Development (XAMPP / WAMP)

#### Step 1: Environment Readiness
Ensure Apache and MySQL/MariaDB services are active inside your local environment control panel.

#### Step 2: Deployment Directory
Extract or clone the project folder into your web server's document root:
* **Windows (XAMPP):** `C:\xampp\htdocs\Yong_AiStorage\`
* **macOS (XAMPP):** `/Applications/XAMPP/htdocs/Yong_AiStorage/`
* **Linux (Apache):** `/var/www/html/Yong_AiStorage/`

#### Step 3: Run Setup Wizard
Open your web browser and navigate to:
```text
http://localhost/Yong_AiStorage/install/install.php
```

#### Step 4: Configure Database & Initial Administrator
1. Enter your database connection details:
   * **Host:** `localhost`
   * **Database User:** `root` (or custom MySQL user)
   * **Database Password:** *(leave empty for default XAMPP, or enter your database password)*
   * **Database Name:** `cloudvault_db` (or desired database name)
2. Create your primary Administrator credentials:
   * **Admin Name:** e.g., `Super Admin`
   * **Admin Email:** e.g., `admin@storage.local`
   * **Admin Password:** Choose a secure password.
3. Click **Install Application**. The installer automatically generates the database, initializes all required schemas, generates the write-protected upload environment, and builds `config/database.php`.

#### Step 5: Sign In
Upon completion, navigate to the portal login:
```text
http://localhost/Yong_AiStorage/login.php
```

---

### Option 2: Production Deployment (cPanel Shared Hosting)

1. **Upload Archive:** Compress the `Yong_AiStorage` folder into a ZIP file and upload it to `public_html` via cPanel File Manager.
2. **Extract Files:** Extract the ZIP contents into your web target directory.
3. **Provision Database:** Navigate to **cPanel > MySQL Database Wizard**, create a database and database user, and assign **ALL PRIVILEGES**.
4. **Execute Installer:** Visit `https://yourdomain.com/install/install.php` in your browser. Enter the database credentials created in cPanel.
5. **Post-Installation Hardening:** Once complete, delete or restrict access to the `install/` directory to prevent unauthorized re-initialization.

---

## 🔒 Security Policy Checklist

- [x] **PDO Parameterization**: Prepared SQL statements prevent SQL injection attacks.
- [x] **Bcrypt Password Storage**: User authentication relies exclusively on `password_hash()` using default secure cost factors.
- [x] **Session Hardening**: Anti-fixation checks execute `session_regenerate_id(true)` upon authentication state changes.
- [x] **Upload MIME & Extension Checks**: Strict array checks reject dangerous files (`.php`, `.exe`, `.sh`, `.phar`, etc.).
- [x] **CSRF Mitigation**: Hidden state tokens validate POST requests to protect sensitive endpoints.
- [x] **Directory Isolation**: Direct HTTP file execution inside `/uploads/` is disabled using `.htaccess` directive rules.

---

## ⚖️ License

Distributed under the **MIT License**. Free for academic, personal, and commercial implementation.
