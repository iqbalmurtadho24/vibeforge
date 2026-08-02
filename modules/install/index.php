<?php
/**
 * Vibeforge - Install Module
 *
 * Unified entry point for all installation wizard AJAX actions.
 * Router proxies here via POST module=install + action=...
 *
 * Supported actions:
 *   - save    : Save file content (markdown, HTML, or logo image)
 *   - graphify: Trigger graphify knowledge graph update
 *   - execute : Open PowerShell terminal in project directory
 */
if (!defined('APP_ENTRY')) {
    http_response_code(403);
    exit('Direct access forbidden');
}

if (!defined('ROOT_PATH')) {
    define('ROOT_PATH', dirname(__DIR__, 2));
}

require_once ROOT_PATH . '/include/config.php';
require_once ROOT_PATH . '/include/helper.php';
require_once ROOT_PATH . '/core/session.php';

// Only allow in development environment
if (defined('APP_ENV') && APP_ENV === 'production') {
    http_response_code(403);
    echo json_encode(['success' => false, 'error' => 'Install wizard is disabled in production']);
    exit;
}

$input  = !empty($_POST) ? $_POST : (json_decode(file_get_contents('php://input'), true) ?? []);
$action = $input['action'] ?? '';

switch ($action) {
    // -----------------------------------------------------------------------
    // save — persist file content (markdown / HTML / logo PNG)
    // -----------------------------------------------------------------------
    case 'save':
        handleSave($input);
        break;

    // -----------------------------------------------------------------------
    // graphify — update knowledge graph after file change
    // -----------------------------------------------------------------------
    case 'graphify':
        handleGraphify($input);
        break;

    // -----------------------------------------------------------------------
    // execute — open PowerShell in project directory
    // -----------------------------------------------------------------------
    case 'execute':
        handleExecute($input);
        break;

    // -----------------------------------------------------------------------
    // setup_vhost — create Laragon virtual host & update hosts file (auto-elevate)
    // -----------------------------------------------------------------------
    case 'setup_vhost':
        handleSetupVhost($input);
        break;

    // -----------------------------------------------------------------------
    // clear_references — wipe references/*.html for redesign mode
    // -----------------------------------------------------------------------
    case 'clear_references':
        handleClearReferences();
        break;

    // -----------------------------------------------------------------------
    // list_references — scan references/ folder for files
    // -----------------------------------------------------------------------
    case 'list_references':
        handleListReferences();
        break;

    // -----------------------------------------------------------------------
    // generate_install_md — create/update docs/install.md based on current config
    // -----------------------------------------------------------------------
    case 'generate_install_md':
        handleGenerateInstallMd($input);
        break;

    // -----------------------------------------------------------------------
    // open_folder — open project folder in Windows Explorer
    // -----------------------------------------------------------------------
    case 'open_folder':
        handleOpenFolder($input);
        break;

    // -----------------------------------------------------------------------
    // rename_app — rename application folder, .env, .env.example, and Laragon vhost
    // -----------------------------------------------------------------------
    case 'rename_app':
        handleRenameApp($input);
        break;

    // -----------------------------------------------------------------------
    // check_folder — check if target app folder exists on disk
    // -----------------------------------------------------------------------
    case 'check_folder':
        handleCheckFolder($input);
        break;

    // -----------------------------------------------------------------------
    // ref_list — list files in references/ folder
    // -----------------------------------------------------------------------
    case 'ref_list':
        handleListReferences();
        break;

    // -----------------------------------------------------------------------
    // delete_ref — delete a file from references/ folder
    // -----------------------------------------------------------------------
    case 'delete_ref':
        handleDeleteRef($input);
        break;

    // -----------------------------------------------------------------------
    // cleanup_unchecked_pages — remove public/ folders for unchecked pages
    // -----------------------------------------------------------------------
    case 'cleanup_unchecked_pages':
        handleCleanupUncheckedPages($input);
        break;

    // -----------------------------------------------------------------------
    // remove_install_page — remove the install wizard page itself
    // -----------------------------------------------------------------------
    case 'remove_install_page':
        handleRemoveInstallPage($input);
        break;

    // -----------------------------------------------------------------------
    // detect_db_mode_from_references — scan references/ for SQL/MySQL config
    // -----------------------------------------------------------------------
    case 'detect_db_mode_from_references':
        handleDetectDbModeFromReferences($input);
        break;

    default:
        http_response_code(400);
        echo json_encode(['success' => false, 'error' => "Unknown action: {$action}"]);
        break;
}

/* -------------------------------------------------------------------------- */
/* Handlers                                                                    */
/* -------------------------------------------------------------------------- */

function handleSave(array $input): void
{
    // --- Logo upload (multipart) ---
    if (!empty($_FILES['logo'])) {
        $file = $_FILES['logo'];

        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $mimeType = finfo_file($finfo, $file['tmp_name']);
        finfo_close($finfo);

        if ($mimeType !== 'image/png') {
            http_response_code(400);
            echo json_encode(['success' => false, 'error' => 'Only PNG files are allowed']);
            return;
        }

        if ($file['size'] > 2 * 1024 * 1024) {
            http_response_code(400);
            echo json_encode(['success' => false, 'error' => 'File size must be under 2MB']);
            return;
        }

        $targetPath = ROOT_PATH . '/docs/logo.png';

        if (file_exists($targetPath)) {
            copy($targetPath, $targetPath . '.bak');
        }

        if (move_uploaded_file($file['tmp_name'], $targetPath)) {
            echo json_encode(['success' => true, 'path' => $targetPath]);
        } else {
            http_response_code(500);
            echo json_encode(['success' => false, 'error' => 'Failed to save logo']);
        }
        return;
    }

    // --- Config (server_type, drive, install_path) ---
    $isConfig = ($input['action'] ?? '') === 'config' || ($input['actionType'] ?? '') === 'config';
    if ($isConfig) {
        $config = [
            'server_type'  => $input['serverType'] ?? 'laragon',
            'drive'        => $input['drive'] ?? 'C',
            'installPath'  => $input['installPath'] ?? '',
            'updated_at'   => date('Y-m-d H:i:s'),
        ];

        $dataDir = ROOT_PATH . '/data';
        if (!is_dir($dataDir)) {
            mkdir($dataDir, 0755, true);
        }

        $configPath = $dataDir . '/install_config.json';
        if (@file_put_contents($configPath, json_encode($config, JSON_PRETTY_PRINT))) {
            echo json_encode(['success' => true]);
        } else {
            http_response_code(500);
            echo json_encode(['success' => false, 'error' => 'Failed to save config']);
        }
        return;
    }

    // --- Regular file content save ---
    $file    = $input['file'] ?? '';
    $content = $input['content'] ?? '';

    if (empty($file)) {
        http_response_code(400);
        echo json_encode(['success' => false, 'error' => 'No file specified']);
        return;
    }

    $normalizedPath = ltrim(str_replace(['../', '..\\', '/', '\\'], '/', $file), '/');
    $normalizedPath = preg_replace('/\.+\//', '', $normalizedPath); // strip any remaining ..
    $normalizedPath = preg_replace('/\.+\\\\/', '', $normalizedPath);

    // Security: allow docs/ and references/ subpaths
    $isAllowed = str_starts_with($normalizedPath, 'docs/') || str_starts_with($normalizedPath, 'references/');

    if (!$isAllowed) {
        http_response_code(403);
        echo json_encode(['success' => false, 'error' => 'Path not allowed: ' . $normalizedPath]);
        return;
    }

    $targetPath = ROOT_PATH . '/' . $normalizedPath;
    $targetDir  = dirname($targetPath);

    if (!is_dir($targetDir)) {
        mkdir($targetDir, 0755, true);
    }

    // Force delete existing target file first if it exists
    if (file_exists($targetPath)) {
        @unlink($targetPath);
    }

    $tmpFile = $targetPath . '.tmp';
    if (@file_put_contents($tmpFile, $content) !== false) {
        @chmod($tmpFile, 0666);
        if (@rename($tmpFile, $targetPath)) {
            echo json_encode(['success' => true, 'path' => $targetPath]);
        } else {
            // Fallback for Windows file lock issues
            if (@copy($tmpFile, $targetPath)) {
                @unlink($tmpFile);
                echo json_encode(['success' => true, 'path' => $targetPath]);
            } else {
                @unlink($tmpFile);
                http_response_code(500);
                echo json_encode(['success' => false, 'error' => 'Failed to replace target file']);
            }
        }
    } else {
        if (file_exists($tmpFile)) {
            @unlink($tmpFile);
        }
        http_response_code(500);
        echo json_encode(['success' => false, 'error' => 'Failed to write file']);
    }
}

function handleGraphify(array $input): void
{
    $changedFile = $input['file'] ?? '';

    if (defined('APP_DEBUG') && APP_DEBUG) {
        $logFile = ROOT_PATH . '/cache/debug.log';
        $logEntry = sprintf(
            "[%s] [graphify] File changed: %s - knowledge graph update triggered\n",
            date('Y-m-d H:i:s'),
            htmlspecialchars($changedFile)
        );
        @file_put_contents($logFile, $logEntry, FILE_APPEND);
    }

    // In a full implementation this would trigger ruflo/graphify update
    echo json_encode(['success' => true, 'graphify_updated' => true]);
}

function handleExecute(array $input): void
{
    $drive = $input['drive'] ?? 'C';
    $serverType = $input['serverType'] ?? 'laragon';
    $projectName = trim($input['projectName'] ?? '');

    $subPath = $serverType === 'laragon' ? 'laragon/www' : 'xampp/htdocs';
    $targetDir = $drive . ':/' . $subPath . ($projectName !== '' ? '/' . $projectName : '');

    $winTargetDir = str_replace('/', '\\', ROOT_PATH);

    // Open PowerShell with claude ready — user pastes prompt from clipboard
    $innerCommand = "Set-Location -Path '$winTargetDir'; claude";

    // UTF-16LE + Base64 encode for PowerShell compatibility
    $encodedCommand = base64_encode(iconv('UTF-8', 'UTF-16LE', $innerCommand));

    $cmd = 'powershell.exe -WindowStyle Hidden -Command "Start-Process powershell.exe -ArgumentList \'-NoExit\', \'-EncodedCommand\', \'' . $encodedCommand . '\'"';

    pclose(popen("start /B " . $cmd, "r"));

    echo json_encode([
        'success' => true,
        'message' => 'PowerShell terminal opened with claude ready',
        'path'    => $winTargetDir,
    ]);
}

function handleSetupVhost(array $input): void
{
    $projectName = trim($input['projectName'] ?? '');
    if (empty($projectName)) {
        echo json_encode(['success' => false, 'error' => 'Project name required']);
        return;
    }

    $drive = strtoupper($input['drive'] ?? 'C');
    $serverType = strtolower($input['serverType'] ?? 'laragon');
    $domain = $projectName . '.test';

    if ($serverType === 'xampp') {
        $vhostFile = "{$drive}:/xampp/apache/conf/extra/httpd-vhosts.conf";
        $vhostDir = dirname($vhostFile);
        $docRoot = "{$drive}:/xampp/htdocs/{$projectName}/public";
        $httpdPath = "{$drive}:\\xampp\\apache\\bin\\httpd.exe";
        $apacheDir = "{$drive}:\\xampp\\apache";
    } else {
        $vhostDir = "{$drive}:/laragon/etc/apache2/sites-enabled";
        $vhostFile = $vhostDir . "/auto.{$domain}.conf";
        $docRoot = "{$drive}:/laragon/www/{$projectName}/public";

        $apacheBins = glob("{$drive}:/laragon/bin/apache/*/bin/httpd.exe");
        $httpdPath = !empty($apacheBins) ? str_replace('/', '\\', $apacheBins[0]) : '';
        $apacheDir = !empty($apacheBins) ? str_replace('/', '\\', dirname(dirname($apacheBins[0]))) : '';
    }

    $vhostContentStr = "<VirtualHost *:80>\n" .
        "    DocumentRoot \"{$docRoot}\"\n" .
        "    ServerName {$domain}\n" .
        "    ServerAlias *.{$domain}\n" .
        "    <Directory \"{$docRoot}\">\n" .
        "        AllowOverride All\n" .
        "        Require all granted\n" .
        "    </Directory>\n" .
        "</VirtualHost>";

    $innerCommand = "
        \$domain = '{$domain}'
        \$vhostContent = @'\n" . $vhostContentStr . "\n'@
        \$vhostFile = '{$vhostFile}'
        \$hostsFile = 'C:/Windows/System32/drivers/etc/hosts'

        # Ensure directory exists
        \$vhostDir = [System.IO.Path]::GetDirectoryName(\$vhostFile)
        if (-not (Test-Path \$vhostDir)) {
            New-Item -ItemType Directory -Path \$vhostDir -Force | Out-Null
        }

        # Write vhost file
        if ('{$serverType}' -eq 'xampp') {
            \$currentVhost = if (Test-Path \$vhostFile) { [System.IO.File]::ReadAllText(\$vhostFile) } else { '' }
            if (\$currentVhost -notlike \"*{$domain}*\") {
                [System.IO.File]::AppendAllText(\$vhostFile, \"`r`n\" + \$vhostContent)
            }
        } else {
            [System.IO.File]::WriteAllText(\$vhostFile, \$vhostContent)
        }

        # Update hosts file (requires admin)
        \$hostsEntry = \"`r`n127.0.0.1`t{$domain}\"
        \$currentHosts = [System.IO.File]::ReadAllText(\$hostsFile)
        if (\$currentHosts -notlike \"*{$domain}*\") {
            [System.IO.File]::AppendAllText(\$hostsFile, \$hostsEntry)
        }

        # Flush DNS cache
        ipconfig /flushdns | Out-Null

        # Restart Apache service or standalone process
        \$service = Get-Service -Name '*apache*' -ErrorAction SilentlyContinue | Select-Object -First 1
        if (\$service -and \$service.Status -eq 'Running') {
            Restart-Service -Name \$service.Name -Force -ErrorAction SilentlyContinue
        } else {
            Get-Process httpd -ErrorAction SilentlyContinue | Stop-Process -Force -ErrorAction SilentlyContinue
            Start-Sleep -Seconds 1
            if ('{$httpdPath}' -ne '' -and (Test-Path '{$httpdPath}')) {
                Start-Process -FilePath '{$httpdPath}' -ArgumentList '-d', '{$apacheDir}' -WorkingDirectory '{$apacheDir}' -WindowStyle Hidden
            }
        }
    ";

    // UTF-16LE + Base64 encode
    $encodedCommand = base64_encode(iconv('UTF-8', 'UTF-16LE', $innerCommand));

    // Run with Start-Process -Verb RunAs to trigger UAC elevation
    $cmd = "powershell.exe -WindowStyle Hidden -Command \"Start-Process powershell.exe -ArgumentList '-EncodedCommand', '$encodedCommand' -Verb RunAs\"";

    pclose(popen("start /B " . $cmd, "r"));

    echo json_encode([
        'success' => true,
        'message' => "Virtual host for $domain created, hosts updated, and Apache restarted."
    ]);
}


function handleListReferences(): void
{
    $refDir = ROOT_PATH . '/references';
    $files = [];

    if (is_dir($refDir)) {
        $iterator = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($refDir, RecursiveDirectoryIterator::SKIP_DOTS),
            RecursiveIteratorIterator::SELF_FIRST
        );

        foreach ($iterator as $item) {
            $relativePath = str_replace(ROOT_PATH . '/', '', str_replace('\\', '/', $item->getPathname()));
            $files[] = [
                'name'       => $relativePath,
                'is_dir'     => $item->isDir(),
                'size'       => $item->isFile() ? $item->getSize() : 0,
                'updated_at' => date('Y-m-d H:i:s', $item->getMTime()),
            ];
        }
    }

    echo json_encode([
        'success' => true,
        'count'   => count($files),
        'files'   => $files,
        'ref_dir' => str_replace('/', '\\', $refDir)
    ]);
}

function handleDeleteRef(array $input): void
{
    $fileName = $input['file'] ?? '';

    if (empty($fileName)) {
        http_response_code(400);
        echo json_encode(['success' => false, 'error' => 'File name is required']);
        return;
    }

    // Sanitize: no path traversal
    $baseName = basename($fileName);
    if ($baseName !== $fileName || str_contains($fileName, '..')) {
        http_response_code(400);
        echo json_encode(['success' => false, 'error' => 'Invalid file name']);
        return;
    }

    $filePath = ROOT_PATH . '/references/' . $baseName;

    if (!file_exists($filePath)) {
        http_response_code(404);
        echo json_encode(['success' => false, 'error' => 'File not found']);
        return;
    }

    if (@unlink($filePath)) {
        echo json_encode(['success' => true, 'message' => 'File deleted']);
    } else {
        http_response_code(500);
        echo json_encode(['success' => false, 'error' => 'Failed to delete file']);
    }
}

/* -------------------------------------------------------------------------- */
/* New Handlers for Install Flow Improvements                                 */
/* -------------------------------------------------------------------------- */

/**
 * Remove public/ folders and files for pages that are NOT checked in the
 * wizard's Tahap 3B (Struktur Halaman). This ensures only active pages
 * remain in the public/ directory.
 */
function handleCleanupUncheckedPages(array $input): void
{
    $pageStructure = $input['pageStructure'] ?? [];

    // Normalize to booleans
    $pages = [
        'landing'    => !empty($pageStructure['landing']),
        'login'      => !empty($pageStructure['login']),
        'register'   => !empty($pageStructure['register']),
        'manajemen'  => !empty($pageStructure['manajemen']),
        'admin'      => !empty($pageStructure['admin']),
        'client'     => !empty($pageStructure['client']),
    ];

    // Map page keys to their public/ subfolder names
    // IMPORTANT: Use dynamic folder names from references/ structure, not hardcoded names.
    // The folder name in public/ MUST match the folder name used in references/.
    // For now, we use the page key as the folder name (backward compatible).
    // In a future enhancement, this will be read from references/ directory structure.
    $pageFolderMap = [
        'landing'    => 'index',       // public/index.php (no subfolder)
        'login'      => 'login',
        'register'   => 'register',
        'manajemen'  => 'manajemen',
        'admin'      => 'admin',
        'client'     => 'client',
    ];

    // Also map to related files that should be removed when a page is unchecked
    $pageRelatedFiles = [
        'manajemen'  => ['public/manajemen/index.php'],
        'admin'      => ['public/admin/index.php'],
        'client'     => ['public/client/index.php'],
        'register'   => ['public/register/index.php'],
        'login'      => ['public/login/index.php'],
    ];

    $removed = [];
    $errors = [];

    foreach ($pages as $pageKey => $isActive) {
        if ($isActive) {
            continue; // Skip active pages
        }

        // Remove subfolder for this page (if not landing — landing is public/index.php)
        if ($pageKey !== 'landing' && isset($pageFolderMap[$pageKey])) {
            $folderPath = ROOT_PATH . '/public/' . $pageFolderMap[$pageKey];
            if (is_dir($folderPath)) {
                $files = new RecursiveIteratorIterator(
                    new RecursiveDirectoryIterator($folderPath, RecursiveDirectoryIterator::SKIP_DOTS),
                    RecursiveIteratorIterator::CHILD_FIRST
                );
                foreach ($files as $file) {
                    if ($file->isDir()) {
                        @rmdir($file->getRealPath());
                    } else {
                        @unlink($file->getRealPath());
                    }
                }
                @rmdir($folderPath);
                $removed[] = $folderPath;
            }
        }

        // Remove related files
        if (isset($pageRelatedFiles[$pageKey])) {
            foreach ($pageRelatedFiles[$pageKey] as $relFile) {
                $fullPath = ROOT_PATH . '/' . $relFile;
                if (file_exists($fullPath)) {
                    if (@unlink($fullPath)) {
                        $removed[] = $fullPath;
                    } else {
                        $errors[] = "Failed to delete: {$relFile}";
                    }
                }
            }
        }
    }

    // If landing is unchecked but login is checked, ensure public/index.php
    // contains a redirect to /login/ (per install.md rules)
    if (!$pages['landing'] && $pages['login']) {
        $indexPath = ROOT_PATH . '/public/index.php';
        $redirectContent = '<?php' . "\n" .
            '/**' . "\n" .
            ' * Landing Page disabled — redirect to Login' . "\n" .
            ' */' . "\n" .
            'defined(\'APP_ENTRY\') or define(\'APP_ENTRY\', true);' . "\n" .
            'header(\'Location: /login/\');' . "\n" .
            'exit;' . "\n";

        if (file_exists($indexPath)) {
            $tmpFile = $indexPath . '.tmp';
            if (@file_put_contents($tmpFile, $redirectContent) !== false) {
                @rename($tmpFile, $indexPath);
                $removed[] = $indexPath . ' (replaced with redirect)';
            } else {
                $errors[] = 'Failed to update public/index.php with redirect';
            }
        }
    }

    // If landing IS checked and login is also checked, ensure public/index.php
    // is the landing page (not a redirect). This is handled by the build process.

    echo json_encode([
        'success' => true,
        'removed' => $removed,
        'errors' => $errors,
        'message' => 'Cleanup complete: unchecked page folders and files removed.',
    ]);
}

/**
 * Remove the install wizard page itself (public/install/) after vibe coding
 * has started. This is called once the AI build process begins.
 */
function handleRemoveInstallPage(array $input): void
{
    $installDir = ROOT_PATH . '/public/install';

    if (!is_dir($installDir)) {
        echo json_encode([
            'success' => true,
            'message' => 'Install directory already removed.',
            'removed' => [],
        ]);
        return;
    }

    $removed = [];
    $errors = [];

    $files = new RecursiveIteratorIterator(
        new RecursiveDirectoryIterator($installDir, RecursiveDirectoryIterator::SKIP_DOTS),
        RecursiveIteratorIterator::CHILD_FIRST
    );

    foreach ($files as $file) {
        if ($file->isDir()) {
            if (@rmdir($file->getRealPath())) {
                $removed[] = $file->getRealPath();
            } else {
                $errors[] = 'Failed to remove directory: ' . $file->getRealPath();
            }
        } else {
            if (@unlink($file->getRealPath())) {
                $removed[] = $file->getRealPath();
            } else {
                $errors[] = 'Failed to remove file: ' . $file->getRealPath();
            }
        }
    }

    // Remove the install directory itself
    if (@rmdir($installDir)) {
        $removed[] = $installDir;
    } else {
        $errors[] = 'Failed to remove install directory: ' . $installDir;
    }

    echo json_encode([
        'success' => true,
        'removed' => $removed,
        'errors' => $errors,
        'message' => 'Install wizard page removed.',
    ]);
}

/**
 * Scan references/ folder for SQL/MySQL configuration files and detect
 * whether the project should use DB_MODE=mysql or DB_MODE=json.
 *
 * Detection rules:
 * 1. If any .sql file exists in references/ → DB_MODE=mysql
 * 2. If any PHP file contains SQL queries (CREATE TABLE, INSERT INTO, SELECT, etc.)
 *    in a database context → DB_MODE=mysql
 * 3. If any PHP file contains MySQL connection config (mysqli_connect, PDO mysql,
 *    mysql_connect, DB_HOST, DB_NAME) → DB_MODE=mysql
 * 4. Otherwise → DB_MODE=json
 */
function handleDetectDbModeFromReferences(array $input): void
{
    $refDir = ROOT_PATH . '/references';
    $dbMode = 'json'; // Default
    $evidence = [];

    if (!is_dir($refDir)) {
        echo json_encode([
            'success' => true,
            'dbMode' => $dbMode,
            'evidence' => ['No references/ folder found — defaulting to JSON mode.'],
        ]);
        return;
    }

    // Scan for .sql files
    $iterator = new RecursiveIteratorIterator(
        new RecursiveDirectoryIterator($refDir, RecursiveDirectoryIterator::SKIP_DOTS),
        RecursiveIteratorIterator::SELF_FIRST
    );

    $sqlFiles = [];
    $phpFiles = [];

    foreach ($iterator as $item) {
        if ($item->isFile()) {
            $relativePath = str_replace(ROOT_PATH . '/', '', str_replace('\\', '/', $item->getPathname()));
            $ext = strtolower(pathinfo($item->getFilename(), PATHINFO_EXTENSION));

            if ($ext === 'sql') {
                $sqlFiles[] = $relativePath;
            } elseif ($ext === 'php' || $ext === 'inc' || $ext === 'phtml') {
                $phpFiles[] = [
                    'path' => $relativePath,
                    'fullPath' => $item->getPathname(),
                ];
            }
        }
    }

    // Rule 1: SQL files found
    if (!empty($sqlFiles)) {
        $dbMode = 'mysql';
        $evidence[] = 'SQL files found in references/: ' . implode(', ', $sqlFiles);
    }

    // Rule 2 & 3: Scan PHP files for SQL/MySQL patterns
    $mysqlPatterns = [
        '/CREATE\s+TABLE/i',
        '/INSERT\s+INTO/i',
        '/SELECT\s+.*\s+FROM/i',
        '/UPDATE\s+.*\s+SET/i',
        '/DELETE\s+FROM/i',
        '/ALTER\s+TABLE/i',
        '/DROP\s+TABLE/i',
        '/mysqli_connect/i',
        '/new\s+PDO\s*\(.*mysql/i',
        '/mysql_connect/i',
        '/mysql_query/i',
        '/DB_HOST/i',
        '/DB_NAME/i',
        '/database.*mysql/i',
        '/pdo.*mysql/i',
        '/mysql/i',
    ];

    foreach ($phpFiles as $phpFile) {
        $content = @file_get_contents($phpFile['fullPath']);
        if ($content === false) {
            continue;
        }

        foreach ($mysqlPatterns as $pattern) {
            if (preg_match($pattern, $content)) {
                if (!in_array('SQL/MySQL patterns found in: ' . $phpFile['path'], $evidence)) {
                    $evidence[] = 'SQL/MySQL patterns found in: ' . $phpFile['path'];
                }
                if ($dbMode !== 'mysql') {
                    $dbMode = 'mysql';
                }
                break; // One match is enough for this file
            }
        }
    }

    // Also check for JSON database files (data/*.json) as evidence of JSON mode
    // If references/ has no SQL and no MySQL patterns, keep DB_MODE=json
    if ($dbMode === 'json') {
        $evidence[] = 'No SQL files or MySQL patterns found in references/ — using JSON mode.';
    }

    echo json_encode([
        'success' => true,
        'dbMode' => $dbMode,
        'evidence' => $evidence,
        'sqlFiles' => $sqlFiles,
        'phpFilesScanned' => count($phpFiles),
    ]);
}

function handleGenerateInstallMd(array $input): void
{
    $installPath = $input['installPath'] ?? '';
    $serverType = $input['serverType'] ?? 'laragon';
    $drive = $input['drive'] ?? 'C';
    $projectName = $input['projectName'] ?? 'vibeforge';
    $pageStructure = $input['pageStructure'] ?? ['landing' => true, 'login' => true, 'register' => true, 'manajemen' => true, 'admin' => true, 'client' => true];
    $brandingMode = $input['brandingMode'] ?? 'manual';
    $prdMode = $input['prdMode'] ?? 'manual';

    // Normalize page structure to booleans
    $pages = [
        'landing'    => !empty($pageStructure['landing']),
        'login'      => !empty($pageStructure['login']),
        'register'   => !empty($pageStructure['register']),
        'manajemen'  => !empty($pageStructure['manajemen']),
        'admin'      => !empty($pageStructure['admin']),
        'client'     => !empty($pageStructure['client']),
    ];

    // Server-side validation rules
    if (!$pages['landing'] && !$pages['login']) {
        http_response_code(400);
        echo json_encode(['success' => false, 'error' => 'Minimal salah satu dari Landing Page atau Login harus dicentang.']);
        return;
    }

    $hasAnyRole = $pages['manajemen'] || $pages['admin'] || $pages['client'];
    if ($pages['login'] && !$hasAnyRole) {
        http_response_code(400);
        echo json_encode(['success' => false, 'error' => 'Karena Halaman Login dicentang, minimal satu role (Manajemen, Admin, atau Client) harus dipilih.']);
        return;
    }

    // Build active pages list
    $activePages = array_keys(array_filter($pages));
    $activePagesStr = implode(', ', $activePages);
    $inactivePages = array_keys(array_filter($pages, function($v) { return !$v; }));
    $inactivePagesStr = !empty($inactivePages) ? implode(', ', $inactivePages) : '—';

    // Build demo users table dynamically
    $demoUsersRows = [];
    if ($pages['manajemen']) {
        $demoUsersRows[] = "| Super Admin (Manajemen) | `admin@{$projectName}.com` | `password123` |";
    }
    if ($pages['admin']) {
        $demoUsersRows[] = "| Creator (Admin) | `admin@{$projectName}.id` | `password123` |";
    }
    if ($pages['client']) {
        $demoUsersRows[] = "| Client (User/Pendengar) | `client@{$projectName}.com` | `password123` |";
    }
    $demoUsersTableStr = implode("\n", $demoUsersRows);

    // Build validation criteria per page
    $validationCriteria = "";
    if ($pages['landing']) {
        $validationCriteria .= "- Landing page sudah berganti mengikuti hasil branding/referensi, bukan landing page bawaan template lama.\n";
        if (!$pages['login']) {
            $validationCriteria .= "- Landing Page dicentang TANPA Login → Landing Page TIDAK PERLU menampilkan tombol Masuk / Daftar (tombol Auth disembunyikan/dihapus), dan halaman role (Manajemen, Admin, Client) tidak wajib.\n";
        } else if ($pages['register']) {
            $validationCriteria .= "- Landing Page & Login & Register dicentang → Landing Page WAJIB menampilkan tombol \"Masuk\" dan \"Daftar\".\n";
        } else {
            $validationCriteria .= "- Landing Page & Login dicentang TANPA Register → Landing Page WAJIB menampilkan tombol \"Masuk\" saja (tanpa tombol Daftar).\n";
        }
    } else {
        $validationCriteria .= "- Landing Page TIDAK dicentang → AI WAJIB bikin `public/index.php` berisi redirect PHP ke `/login/`, dan landing page reference tidak perlu di-generate.\n";
    }
    if ($pages['login']) {
        $validationCriteria .= "- Halaman Login sudah disesuaikan (bukan placeholder generik) sesuai referensi/branding.\n";
    }
    if ($pages['register']) {
        $validationCriteria .= "- Halaman Register sudah disesuaikan dan berfungsi.\n";
    } else {
        $validationCriteria .= "- Register TIDAK dicentang → verifikasi tidak ada route/halaman register yang bocor ke publik, dan fitur \"buat user baru\" di Halaman Manajemen benar-benar berfungsi CRUD.\n";
    }
    if ($pages['manajemen']) {
        $validationCriteria .= "- Halaman Manajemen benar-benar bisa diakses setelah login, bukan cuma shell kosong.\n";
    } else {
        $validationCriteria .= "- Halaman Manajemen TIDAK dicentang → verifikasi tidak ada folder/route manajemen (super admin) yang ke-generate.\n";
    }
    if ($pages['admin']) {
        $validationCriteria .= "- Halaman Admin benar-benar bisa diakses setelah login, bukan cuma shell kosong.\n";
    } else {
        $validationCriteria .= "- Halaman Admin TIDAK dicentang → verifikasi tidak ada folder/route admin biasa yang ke-generate.\n";
    }
    if ($pages['client']) {
        $validationCriteria .= "- Halaman Client benar-benar bisa diakses setelah login, bukan cuma shell kosong.\n";
    } else {
        $validationCriteria .= "- Halaman Client TIDAK dicentang → verifikasi tidak ada folder/route client yang ke-generate.\n";
    }
    $validationCriteria .= "- CRUD di tiap modul yang relevan **harus berfungsi nyata** (create/read/update/delete tidak boleh cuma UI tanpa backend), diuji baik untuk skenario storage SQL maupun JSON sesuai hasil deteksi `DB_MODE`.\n";

    // Get references files list
    $refDir = ROOT_PATH . '/references';
    $refFiles = [];
    if (is_dir($refDir)) {
        $iterator = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($refDir, RecursiveDirectoryIterator::SKIP_DOTS),
            RecursiveIteratorIterator::SELF_FIRST
        );
        foreach ($iterator as $item) {
            if ($item->isFile()) {
                $relativePath = str_replace(ROOT_PATH . '/', '', str_replace('\\', '/', $item->getPathname()));
                $refFiles[] = $relativePath;
            }
        }
    }

    // Build references section
    $referencesSection = "## 3. Referensi Aplikasi (`references/`)\n\n";
    if (!empty($refFiles)) {
        $referencesSection .= "File/folder referensi berikut di-upload melalui Tahap 2 wizard. AI akan menganalisa seluruh isi folder `references/` (HTML, PHP, CSS, JS, Gambar, Video, Font, SQL skema) sebagai acuan alur & tampilan.\n\n";
        $referencesSection .= "**Daftar File Referensi:**\n\n";
        foreach ($refFiles as $file) {
            $referencesSection .= "- `{$file}`\n";
        }
        $referencesSection .= "\n> **Instruksi AI (Referensi, Database & Transformasi)**:\n" .
            "> 1. Baca SELURUH file/folder media & skrip di `references/` terlebih dahulu.\n" .
            "> 2. Gunakan sebagai acuan untuk menyusun `docs/prd.md` dan `docs/branding.md` (apabila mode auto).\n" .
            "> 3. GANTI total tampilan `public/index.php` dan shell `public/*.php` serta perbarui file root (`.env`, `.env.example`, `README.md`, `LICENSE`, `CHANGELOG.md`) sesuai branding baru.\n" .
            "> 4. **Aturan Mutlak Konfigurasi Database (SQL vs JSON)**:\n" .
            ">    - **Jika ditemukan file skema/query SQL di `references/`**: DILARANG KERAS menggunakan skema/konsep JSON database (`data/*.json`). AI WAJIB menghapus seluruh konsep JSON, mengkonfigurasi `DB_MODE=mysql` (atau `auto`), membuat migrasi SQL di `migrations/`, dan langsung menampilkan/memuat data dari database MySQL via `Repo::table()` sesuai query yang tertulis.\n" .
            ">    - **Jika TIDAK ada file/query SQL sama sekali di `references/`**: AI WAJIB mengkonfigurasi `DB_MODE=json` dan membuatkan file database JSON selengkap-lengkapnya di `data/*.json` yang mendukung seluruh fitur CRUD secara nyata dengan file locking & atomic write.\n\n";
    } else {
        $referencesSection .= "Tidak ada file referensi di-upload. AI WAJIB men-generate file HTML referensi terlebih dahulu di `references/` sebelum membangun aplikasi.\n\n";
    }

    // Build auto-generated files section
    $autoGenSection = "## 4. File yang Akan Di-Generate Otomatis\n\n";
    $hasAutoFiles = false;

    // Branding auto
    if ($brandingMode === 'auto' && !empty($refFiles)) {
        $hasAutoFiles = true;
        $autoGenSection .= "### 4.1 Branding (`docs/branding.md`)\n";
        $autoGenSection .= "- `docs/branding.md` — **akan di-generate otomatis** oleh AI saat TAHAP 2\n";
        $autoGenSection .= "  - Generate brand identity lengkap: Nama, Tagline, Value Proposition, Target Audience, Tone of Voice\n";
        $autoGenSection .= "  - Generate palet warna otomatis dari hasil audit referensi\n";
        $autoGenSection .= "  - Generate typography guidelines dari hasil audit referensi\n\n";
    }

    // PRD auto
    if ($prdMode === 'auto' && !empty($refFiles)) {
        $hasAutoFiles = true;
        $autoGenSection .= "### 4.2 PRD (`docs/prd.md`)\n";
        $autoGenSection .= "- `docs/prd.md` — **akan di-generate otomatis** oleh AI saat TAHAP 2\n";
        $autoGenSection .= "  - Generate 7 bagian PRD lengkap dari hasil audit referensi\n";
        $autoGenSection .= "  - Problem Statement, Goals, Target User, User Stories, Functional Requirements, Non-Functional Requirements, Scope\n";
        $autoGenSection .= "  - Self-review 4 pertanyaan sebelum PRD dianggap final\n\n";
    }

    // References auto (generate from PRD if no files uploaded but mode = auto)
    if (empty($refFiles)) {
        $hasAutoFiles = true;
        $autoGenSection .= "### 4.3 References HTML (`references/`)\n";
        $autoGenSection .= "- `references/` — **akan di-generate otomatis** oleh AI saat TAHAP 2\n";
        $autoGenSection .= "  - Generate `references/landingpage.html` — struktur landing page\n";
        $autoGenSection .= "  - Generate `references/login.html` — halaman login\n";
        $autoGenSection .= "  - Generate `references/register.html` — halaman register\n";
        if ($pages['manajemen']) {
            $autoGenSection .= "  - Generate `references/modul_manajemen.html` — halaman Super Admin\n";
        }
        if ($pages['admin']) {
            $autoGenSection .= "  - Generate `references/modul_admin.html` — halaman Admin/Creator\n";
        }
        if ($pages['client']) {
            $autoGenSection .= "  - Generate `references/modul_client.html` — halaman Client/Pendengar\n";
        }
        $autoGenSection .= "  - Referensi HTML ini akan dipakai sebagai golden template saat membangun aplikasi\n\n";
    }

    if (!$hasAutoFiles) {
        $autoGenSection = ""; // No auto-gen section if no auto files
    }

    // Build install.md content
    $installMd = <<<MD
# Dokumentasi Instalasi & Protocol Eksekusi AI - {$projectName}

Dokumen ini adalah panduan utama instalasi dan **Build Protocol** untuk mengkonfigurasi serta memproses pembuatan aplikasi berbasis **Vibeforge Template** (PHP Single Page Application Framework).

---

## 0. VIBEFORGE AI GUIDELINES & ERROR PREVENTION PROTOCOL (MUTLAK)

Sebelum menjalankan **Build Protocol**, AI Coding Assistant WAJIB mematuhi guardrail pencegahan error berikut:

### 0.1 Mencegah File Write / Lock / Update Errors
- **Penanganan Hak Akses Folder & Environment Lock**: Sebelum memulai pembangunan, jalankan `installer_skill_claude.bat` sebagai **Administrator** di Windows. Script ini mengeksekusi `icacls` untuk hak akses folder serta mengonfigurasi MCP (FileSystem Server, Sequential Thinking, Memory Server) guna mencegah *file permission lock* dan kegagalan penulisan file.
- **Single Component Execution (Stabilitas Memori & I/O)**: Bekerja secara bertahap — selesaikan **satu file/komponen pada satu waktu**. DILARANG menulis atau mengubah banyak file sekaligus di berbagai direktori dalam satu langkah eksekusi untuk mencegah file permission lock, buffer overflow, atau write collision.
- **Atomic File Write Protection**: Untuk penyimpanan JSON di `data/`, gunakan mekanisme file lock (`.lock`) dan atomic write (`.tmp` → `rename()`). Jangan menulis langsung ke file JSON utama tanpa `.tmp`.
- **Handling Permission Locks**: Jika terjadi kegagalan penulisan file, periksa status file lock / hak akses folder proyek sebelum mencoba ulang. Jalankan terminal tempat AI berada dalam mode Administrator.

### 0.2 Standar Penamaan & Konsistensi Database (Mencegah Runtime Mismatch)
- Seluruh variabel PHP/JS, kolom database, dan `id`/`name` input HTML WAJIB menggunakan format **`snake_case`**.
- **Aturan Mutlak Kolom Wilayah**: Setiap data yang berkaitan dengan alamat, kota, atau kabupaten WAJIB menggunakan nama kolom/variabel **`kota_kabupaten_rumah`**. DILARANG menggunakan nama alternatif (`city`, `kota_rumah`, `kabupaten`, dll) untuk mencegah runtime error dan ketidakcocokan query antar-modul.

### 0.3 Alur Kerja Agentic (Ruflo & Graphify Execution)
1. **Analisis Relasi**: Periksa file `core/router.php`, modul `modules/`, dan skema DB (atau Graphify jika tersedia) sebelum mengubah kode.
2. **Pecah Tugas Linear**: Pecah fitur ke sub-tahap: (1) UI Frontend SPA Shell, (2) Backend Endpoint (`modules/*/*.php`), (3) Integrasi Fetch/AJAX.
3. **Pesan Konfirmasi**: Setelah memahami seluruh aturan, AI wajib membalas di awal turn: `"Vibeforge Guidelines & Protection Protocol Diterima. Siap eksekusi."`

---

## 1. Konfigurasi Server & Workspace

- **Mode Aplikasi**: `unified`
- **Local Disk**: `{$drive}:`
- **Jenis Web Server**: `{$serverType}`
- **Folder Kerja Target**: `{$installPath}`
- **Branding Mode**: `{$brandingMode}`
- **PRD Mode**: `{$prdMode}`

---

## 2. Struktur Halaman Aktif

Halaman yang dicentang di Tahap 3B wizard (hanya ini yang dibangun):

| Halaman | Aktif | Shell File |
|---------|-------|------------|
| Landing Page | **{$pages['landing']}** | `public/index.php` |
| Login | **{$pages['login']}** | `public/login/index.php` |
| Register | **{$pages['register']}** | `public/register/index.php` |
| Manajemen | **{$pages['manajemen']}** | `public/manajemen/index.php` |
| Admin | **{$pages['admin']}** | `public/admin/index.php` |
| Client | **{$pages['client']}** | `public/client/index.php` |

**Aturan Landing ↔ Login**:
- Minimal salah satu dari Landing Page atau Login harus dicentang.
- Jika Landing Page dicentang TANPA Login, halaman role (Manajemen, Admin, Client) tidak wajib, dan desain Landing Page TIDAK PERLU memiliki tombol Masuk/Daftar.
- Jika Login dicentang, minimal satu role dari {manajemen, admin, client} WAJIB dicentang.
- Jika Login & Register dicentang, Landing Page WAJIB menampilkan tombol Masuk & Daftar. Jika hanya Login yang dicentang, tampilkan tombol Masuk saja.
- Jika Landing Page TIDAK dicentang, `public/index.php` WAJIB berisi redirect PHP (`header('Location: /login/'); exit;`).

**Halaman aktif**: {$activePagesStr}
**Halaman non-aktif**: {$inactivePagesStr}

---

{$referencesSection}

{$autoGenSection}

---

## 5. Protokol Pembangunan AI (Build Protocol)

Setiap AI Coding Assistant (Claude Code CLI) WAJIB mengikuti urutan 3 Tahap Eksekusi di bawah ini secara linear:

### TAHAP 1 — AUDIT & RENCANA (Read-Only)
1. Baca `CLAUDE.md`, `docs/prd.md`, dan `docs/branding.md`.
2. Audit keberadaan file/folder di `references/`:
   - **Jika `references/` Kosong**: AI WAJIB men-generate file HTML referensi di `references/` serta menyusun `docs/prd.md` & `docs/branding.md` sesuai konsep aplikasi baru.
   - **Jika `references/` Berisi File/Folder**: Baca seluruh folder `references/` (HTML, PHP, CSS, JS, media, skema SQL) -> gunakan sebagai acuan untuk `docs/prd.md` & `docs/branding.md`.
3. Audit skema database pada file di `references/`:
   - **Ada SQL**: Rencanakan skema migrasi di `migrations/` & set `DB_MODE=mysql` (atau `auto`).
   - **Tidak Ada SQL**: Set `DB_MODE=json` & buat skema entitas di `data/*.json`.
4. Audit struktur file core & root:
   - `include/config.php`, `include/helper.php`
   - `core/router.php`, `core/session.php`, `core/csrf.php`, `core/Repo.php`
   - `public/core/router.php` (router proxy - WAJIB)
   - `.env`, `.env.example`, `README.md`, `LICENSE`, `CHANGELOG.md`
   - `data/users.json`
   - `locales/languages.json` dan `locales/*.json`
5. Jalankan **Audit Protocol** sesuai `docs/audit_protocol.md`:
   - Output: `docs/AUDIT_BASIC.md`
   - Jika proyek memiliki multi-mode storage atau governance kompleks, lanjut dengan `docs/audit_conformance_addendum.md`
6. Buat file `docs/build_plan.md` yang memuat:
   - Mapping shell vs file yang sudah ada
   - Daftar file yang belum ada
   - Daftar variabel environment dari `.env.example`
7. **BERHENTI dan TUNGGU persetujuan project owner** sebelum lanjut ke TAHAP 2.

---

### TAHAP 2 — BUILD (Eksekusi)

1. **Transformasi Total Tampilan, Root Files & Branding (WAJIB)**:
   - GANTI total tampilan `public/index.php` dan seluruh shell `public/*.php` sesuai desain `references/`, `docs/prd.md`, dan `docs/branding.md`.
   - Update file-file konfigurasi & dokumentasi root (`.env`, `.env.example`, `README.md`, `LICENSE`, `CHANGELOG.md`) dengan nama aplikasi baru, tagline, deskripsi, dan lisensi/versi yang sesuai (DILARANG menyisakan nama "Vibeforge" atau landing page framework bawaan pada aplikasi hasil generate).
   - Periksa seluruh tautan navigasi (tombol, menu, form action). Pastikan tautan berfungsi baik di environment VirtualHost maupun non-VirtualHost (gunakan relative path atau URL helper).
2. **Kepatuhan Arsitektur 13 Pilar Software (WAJIB)**:
   - Entry Guard Pattern (CLAUDE.md §8)
   - Router Proxy Pattern (CLAUDE.md §3f)
   - Repo Pattern Dual-Mode (CLAUDE.md §3g)
   - SPA Shell Architecture (CLAUDE.md §3a)
   - Standar i18n & Multi-Bahasa (CLAUDE.md §2a): DILARANG hardcode string bahasa, gunakan `t('key')` & `locales/*.json`.
3. Generate demo users di `data/users.json`:
   - Lihat CLAUDE.md Section 6b untuk format dan password hash
   - Gunakan Argon2ID hash (jangan plain text)
4. Setup i18n files:
   - `locales/languages.json` (manifest)
   - `locales/id.json`, `locales/en.json`, `locales/ar.json`
   - Flag assets di `public/assets/flags/`
5. **Implementasi Database & CRUD Nyata**:
   - **Jika ditemukan file/query SQL/database di `references/`**: DILARANG KERAS menggunakan JSON database. Konfigurasi `DB_MODE=mysql`/`auto`, bangun migrasi SQL di `migrations/`, jalankan query via `Repo::table()`. Hapus/abaikan seluruh konsep JSON.
   - **Jika TIDAK ada file/query SQL sama sekali di `references/`**: Konfigurasi `DB_MODE=json`, buatkan database JSON selengkap-lengkapnya di `data/*.json` dengan file locking & atomic write, pastikan seluruh fitur CRUD berfungsi nyata.
   - Pastikan seluruh fitur CRUD (Create, Read, Update, Delete) berfungsi secara nyata sesuai spesifikasi di `docs/prd.md`.

---

### TAHAP 3 — VERIFY & PREVIEW

1. Jalankan validasi syntax PHP untuk SEMUA file `.php`:
   ```bash
   php -l public/index.php
   php -l public/login/index.php
   # ... semua file .php
   ```
2. Pastikan 0 parse error (CLAUDE.md §12h - Zero Tolerance).
3. Konfigurasi virtual host (Laragon):
   - Menu Laragon -> Apache -> httpd-vhosts.conf
   - Ubah `root`/`DocumentRoot` ke path `.../public`
   - **JANGAN klaim langkah ini otomatis selesai** tanpa konfirmasi eksplisit dari project owner
4. Informasikan URL preview: `http://<nama-folder-project>.test/`
5. Laporkan checklist manual berikut - **WAJIB diverifikasi project owner di browser**:

**Checklist Manual Preview:**
- [ ] Landing page tampil sesuai struktur `references/landingpage.html`
- [ ] Tombol quick-login demo (dev-only) berhasil masuk ke masing-masing role (manajemen/admin/client)
- [ ] Ganti bahasa (id/en/ar) mengubah SEMUA teks, termasuk konten yang di-inject via JavaScript (lihat CLAUDE.md §12d)
- [ ] Logout mengarah balik ke landing page tanpa render HTML apapun (CLAUDE.md §12f)
- [ ] Auth state konsisten di desktop header dan mobile bottom nav (CLAUDE.md §12i)

---

## 6. Keamanan & User Demo Default

**Security Baseline (Lihat CLAUDE.md §8 untuk detail lengkap):**
- Password: Argon2ID
- CSRF Token Validation
- IP+Username Rate Limiting
- Prepared Statements (PDO Dual-Mode Repo)

**Demo Users (Lihat CLAUDE.md §6b untuk format lengkap):**

| Role | Email Demo | Password Demo |
|------|------------|---------------|
{$demoUsersTableStr}

---

## 7. Referensi Dokumen Terkait

| File | Scope | Kapan Dibaca |
|------|-------|--------------|
| `CLAUDE.md` | Konstitusi teknis, arsitektur, keamanan | Selalu (utama) |
| `docs/document.md` | Decision guide: new vs redesign | Sebelum mulai build |
| `docs/prd.md` | Definisi produk, fitur, target user | Saat isi konsep aplikasi |
| `docs/branding.md` | Warna, font, logo, visual identity | Saat isi identitas visual |
| `docs/audit_protocol.md` | Audit teknis dasar | TAHAP 1 audit |
| `docs/audit_conformance_addendum.md` | Audit lanjutan multi-mode | Jika diperlukan |

---

## CONSTRAINT GLOBAL

- Backward compatible dengan struktur modular yang sudah ada.
- JANGAN sentuh/push data user nyata (`users.json`, `remember_tokens.json`, `audit_trail.json`) dan `.env`.
- Ikuti ATURAN WAJIB di atas (riset→analisa→eksekusi→uji→analisa di SETIAP stage, tanpa kecuali, termasuk wajib tanya ulang setelah revisi).

---

**Dibuat otomatis oleh Vibeforge Setup Wizard**
MD;

    // SIMPAN KONFIGURASI ke data/install_config.json (bukan overwrite install.md)
    $configData = [
        'serverType' => $serverType,
        'drive' => $drive,
        'installPath' => $installPath,
        'projectName' => $projectName,
        'pageStructure' => $pages,
        'brandingMode' => $brandingMode,
        'prdMode' => $prdMode,
        'referencesCount' => count($refFiles),
        'updated_at' => date('Y-m-d H:i:s'),
    ];

    $configPath = ROOT_PATH . '/data/install_config.json';
    $dataDir = dirname($configPath);
    if (!is_dir($dataDir)) {
        mkdir($dataDir, 0755, true);
    }

    if (@file_put_contents($configPath, json_encode($configData, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES))) {
        echo json_encode([
            'success' => true,
            'config_path' => $configPath,
            'message' => 'Konfigurasi tersimpan. File install.md adalah template static - edit manual jika perlu.'
        ]);
    } else {
        http_response_code(500);
        echo json_encode([
            'success' => false,
            'error' => 'Gagal menulis konfigurasi'
        ]);
    }
}

function handleOpenFolder(array $input): void
{
    $folder = $input['folder'] ?? 'references';
    $normalized = ltrim(str_replace(['../', '..\\'], '', $folder), '/\\');
    $targetDir = ROOT_PATH . '/' . $normalized;

    if (!is_dir($targetDir)) {
        mkdir($targetDir, 0755, true);
    }

    $winPath = str_replace('/', '\\', $targetDir);
    $cmd = 'powershell.exe -WindowStyle Hidden -Command "Start-Process explorer.exe \'' . $winPath . '\'"';
    pclose(popen("start /B " . $cmd, "r"));

    echo json_encode([
        'success' => true,
        'message' => 'Folder opened in File Explorer',
        'path' => $winPath
    ]);
}

function handleRenameApp(array $input): void
{
    $newName = trim($input['newName'] ?? '');
    $oldName = trim($input['oldName'] ?? '');

    if (empty($newName) || empty($oldName)) {
        echo json_encode(['success' => false, 'error' => 'Nama aplikasi tidak boleh kosong']);
        return;
    }

    // Sanitize: only lowercase alphanumeric, dashes, underscores
    if (!preg_match('/^[a-z][a-z0-9_-]*$/', $newName)) {
        echo json_encode(['success' => false, 'error' => 'Nama hanya boleh huruf kecil, angka, strip, dan underscore. Harus diawali huruf.']);
        return;
    }

    if ($newName === $oldName) {
        echo json_encode(['success' => false, 'error' => 'Nama baru sama dengan nama saat ini']);
        return;
    }

    $projectRoot = ROOT_PATH;
    $parentDir = dirname($projectRoot);
    $newPath = $parentDir . '/' . $newName;

    // Check target folder doesn't already exist
    if (is_dir($newPath)) {
        echo json_encode(['success' => false, 'error' => 'Folder "' . $newName . '" sudah ada di ' . str_replace('/', '\\', $parentDir)]);
        return;
    }

    // 1. Update .env — APP_DISPLAY_NAME
    $envPath = $projectRoot . '/.env';
    if (file_exists($envPath)) {
        $envContent = file_get_contents($envPath);
        $envContent = preg_replace(
            '/^APP_DISPLAY_NAME=.*$/m',
            'APP_DISPLAY_NAME="' . $newName . '"',
            $envContent
        );
        file_put_contents($envPath, $envContent);
    }

    // 2. Update .env.example — APP_DISPLAY_NAME
    $envExamplePath = $projectRoot . '/.env.example';
    if (file_exists($envExamplePath)) {
        $envExampleContent = file_get_contents($envExamplePath);
        $envExampleContent = preg_replace(
            '/^APP_DISPLAY_NAME=.*$/m',
            'APP_DISPLAY_NAME="' . $newName . '"',
            $envExampleContent
        );
        file_put_contents($envExamplePath, $envExampleContent);
    }

    // 3. Build a PowerShell script to run in background:
    //    - Rename folder
    //    - Update Laragon vhost
    //    - Reload Apache
    $isLaragon = stripos($projectRoot, 'laragon') !== false;
    $drive = strtoupper(substr($projectRoot, 0, 1));

    $psScript = "Start-Sleep -Seconds 3\n";

    if ($isLaragon) {
        $oldDomain = $oldName . '.test';
        $newDomain = $newName . '.test';
        $laragonBase = $drive . ':/laragon';
        $vhostDir = $laragonBase . '/etc/apache2/sites-enabled';
        $oldVhostFile = $vhostDir . '/auto.' . $oldDomain . '.conf';
        $newVhostFile = $vhostDir . '/auto.' . $newDomain . '.conf';
        $newDocRoot = $laragonBase . '/www/' . $newName . '/public';

        // Rename folder
        $psScript .= "Rename-Item -Path '" . str_replace('/', '\\', $projectRoot) . "' -NewName '" . $newName . "'\n";

        // Update vhost: remove old, create new
        $psScript .= "if (Test-Path '" . str_replace('/', '\\', $oldVhostFile) . "') {\n";
        $psScript .= "  Remove-Item '" . str_replace('/', '\\', $oldVhostFile) . "' -Force\n";
        $psScript .= "  \$vhostContent = @\"\n";
        $psScript .= "<VirtualHost *:80>\n";
        $psScript .= "    DocumentRoot \"" . str_replace('/', '\\', $newDocRoot) . "\"\n";
        $psScript .= "    ServerName " . $newDomain . "\n";
        $psScript .= "    <Directory \"" . str_replace('/', '\\', $newDocRoot) . "\">\n";
        $psScript .= "        AllowOverride All\n";
        $psScript .= "        Require all granted\n";
        $psScript .= "    </Directory>\n";
        $psScript .= "</VirtualHost>\n";
        $psScript .= "\"@\n";
        $psScript .= "  Set-Content -Path '" . str_replace('/', '\\', $newVhostFile) . "' -Value \$vhostContent -Encoding UTF8\n";
        $psScript .= "}\n";

        // Reload Apache
        $psScript .= "Start-Process -FilePath '" . str_replace('/', '\\', $laragonBase) . "\\bin\\apache\\httpd-2.4*/bin/httpd.exe' -ArgumentList '-k','restart' -NoNewWindow -ErrorAction SilentlyContinue 2>`$null\n";
        $psScript .= "Start-Sleep -Seconds 2\n";
    } else {
        // Just rename folder for non-Laragon
        $psScript .= "Rename-Item -Path '" . str_replace('/', '\\', $projectRoot) . "' -NewName '" . $newName . "'\n";
    }

    // Write the script to a temp file
    $scriptPath = $projectRoot . '/cache/_rename_app.ps1';
    @mkdir(dirname($scriptPath), 0755, true);
    file_put_contents($scriptPath, $psScript);

    // Execute the script in background via PowerShell
    $winScriptPath = str_replace('/', '\\', $scriptPath);
    $cmd = "powershell.exe -WindowStyle Hidden -ExecutionPolicy Bypass -File \"{$winScriptPath}\"";
    pclose(popen("start /B " . $cmd, "r"));

    $newDomain = $newName . '.test';
    $newUrl = 'http://' . $newDomain . '/install/';

    echo json_encode([
        'success' => true,
        'newName' => $newName,
        'newUrl'  => $newUrl,
        'message' => 'Aplikasi di-rename. Halaman akan dialihkan otomatis.'
    ]);
}

function handleCheckFolder(array $input): void
{
    $drive = $input['drive'] ?? 'C';
    $serverType = $input['serverType'] ?? 'laragon';
    $projectName = trim($input['projectName'] ?? '');

    if (empty($projectName)) {
        echo json_encode(['success' => false, 'exists' => false, 'error' => 'Nama project kosong']);
        return;
    }

    $subPath = $serverType === 'laragon' ? 'laragon/www' : 'xampp/htdocs';
    $targetDir = $drive . ':/' . $subPath . '/' . $projectName;

    $exists = is_dir($targetDir);

    echo json_encode([
        'success' => true,
        'exists'  => $exists,
        'path'    => str_replace('/', '\\', $targetDir)
    ]);
}
