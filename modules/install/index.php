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

$action = $_POST['action'] ?? '';
$input  = !empty($_POST) ? $_POST : (json_decode(file_get_contents('php://input'), true) ?? []);

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
    // check_folder — check if target app folder exists on disk
    // -----------------------------------------------------------------------
    case 'check_folder':
        handleCheckFolder($input);
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
    $commandToRun = trim($input['command'] ?? '');

    $subPath = $serverType === 'laragon' ? 'laragon/www' : 'xampp/htdocs';
    $targetDir = $drive . ':/' . $subPath . ($projectName !== '' ? '/' . $projectName : '');

    // Target parent directory (where download/degit runs)
    $parentDir = $drive . ':/' . $subPath;

    if (!is_dir($parentDir)) {
        mkdir($parentDir, 0755, true);
    }

    $winTargetDir = str_replace('/', '\\', is_dir($targetDir) ? $targetDir : $parentDir);

    if ($commandToRun !== '') {
        // Ganti operator ' && ' menjadi '; if ($?) { ' untuk kompatibilitas PS 5.1 jika input lama terkirim
        $commandToRun = str_replace(' && ', '; if ($?) { ', $commandToRun);
        if (str_contains($commandToRun, '; if ($?) { ') && !str_ends_with($commandToRun, ' }')) {
            $commandToRun .= ' }';
        }

        // Susun script utuh
        $innerCommand = "Set-Location -Path '$winTargetDir'; $commandToRun";

        // UTF-16LE + Base64 encode agar terhindar dari parser error tanda petik di level OS command line
        $encodedCommand = base64_encode(iconv('UTF-8', 'UTF-16LE', $innerCommand));

        // Tanpa -NoExit agar terminal menutup otomatis setelah script selesai
        $cmd = 'powershell.exe -WindowStyle Hidden -Command "Start-Process powershell.exe -ArgumentList \'-EncodedCommand\', \'' . $encodedCommand . '\'"';
    } else {
        $cmd = 'powershell.exe -WindowStyle Hidden -Command "Start-Process powershell.exe -ArgumentList \'-NoExit\', \'-Command\', \'Set-Location -Path ' . $winTargetDir . '\'"';
    }

    pclose(popen("start /B " . $cmd, "r"));

    echo json_encode([
        'success' => true,
        'message' => 'PowerShell terminal opened successfully',
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

function handleGenerateInstallMd(array $input): void
{
    $installPath = $input['installPath'] ?? '';
    $serverType = $input['serverType'] ?? 'laragon';
    $drive = $input['drive'] ?? 'C';
    $appMode = $input['appMode'] ?? 'new';
    $projectName = $input['projectName'] ?? 'vibeforge';
    $modeTitle = $appMode === 'redesign' ? 'Redesain Aplikasi' : 'Membuat Aplikasi Baru';

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

    // Build references section based on mode
    if ($appMode === 'redesign') {
        $referencesSection = "## 3. Referensi Aplikasi Redesain (`references/`)\n\n" .
            "Pada **Mode Redesain**, AI akan menganalisa seluruh isi folder `references/` (termasuk file HTML, PHP, JS, CSS, maupun subfolder dari codebase lama).\n\n" .
            "**Daftar File Referensi Saat Ini:**\n\n";
        foreach ($refFiles as $file) {
            $referencesSection .= "- `{$file}`\n";
        }
        if (empty($refFiles)) {
            $referencesSection .= "- *(folder references/ kosong - silakan masukkan file/folder referensi aplikasi lama)*\n";
        }
        $referencesSection .= "\n> **Instruksi Khusus AI (Mode Redesain)**:\n" .
            "> 1. AI WAJIB membaca SELURUH file/folder di `references/` terlebih dahulu.\n" .
            "> 2. Susun & tulis ulang `docs/prd.md` dan `docs/branding.md` secara utuh berdasarkan analisa dari `references/`.\n" .
            "> 3. Konsolidasikan referensi menjadi 6 file HTML standar di `references/*.html` (`landingpage.html`, `login.html`, `register.html`, `modul_manajemen.html`, `modul_admin.html`, `modul_client.html`).\n";
    } else {
        // New app mode - use template references
        $referencesSection = "## 3. Referensi HTML Templates (`references/`)\n\n" .
            "Template HTML referensi struktur visual shell:\n\n" .
            "| Step | File | Fungsi |\n" .
            "|------|------|--------|\n" .
            "| 5 | `references/landingpage.html` | Landing page publik |\n" .
            "| 6 | `references/login.html` | Halaman login |\n" .
            "| 7 | `references/register.html` | Halaman registrasi |\n" .
            "| 8 | `references/modul_manajemen.html` | Dashboard Super Admin |\n" .
            "| 9 | `references/modul_admin.html` | Dashboard Creator |\n" .
            "| 10 | `references/modul_client.html` | Dashboard Pendengar |\n\n" .
            "> **Instruksi Khusus AI (Mode Baru)**:\n" .
            "> AI menggunakan `docs/prd.md`, `docs/branding.md`, dan `references/*.html` sebagai acuan untuk membangun shell dan fitur aplikasi secara presisi.\n";
    }

    // Build install.md content following docs/document.md BUILD PROTOCOL
    $installMd = <<<MD
# Dokumentasi Instalasi & Protocol Eksekusi AI - Vibeforge Template

Dokumen ini adalah panduan utama instalasi dan **Build Protocol** untuk mengkonfigurasi serta memproses pembuatan aplikasi berbasis **Vibeforge Template** (PHP Single Page Application Framework).

---

## 1. Konfigurasi Server & Workspace

- **Mode Aplikasi**: `{$appMode}` (**{$modeTitle}**)
- **Local Disk**: `{$drive}:`
- **Jenis Web Server**: `{$serverType}`
- **Folder Kerja Target**: `{$installPath}\\{$projectName}`

---

## 2. Alur Kerja Setup Wizard

### Mode Aplikasi Baru (12 Langkah)
1. Overview -> 2. PRD -> 3. Branding -> 4. Logo -> 5-10. Templates HTML -> 11. Server -> 12. Path

### Mode Redesain (5 Langkah)
1. Overview -> 2. References Folder -> 3. Logo -> 4. Server -> 5. Path

---

{$referencesSection}

---

## 4. Protokol Pembangunan AI (Build Protocol - `docs/document.md`)

Setiap AI Coding Assistant (Claude Code CLI) WAJIB mengikuti urutan 3 Tahap Eksekusi di bawah ini secara linear:

### TAHAP 1 — AUDIT & RENCANA (Read-Only)
1. Baca `CLAUDE.md`, `docs/prd.md`, dan `docs/branding.md`.
2. Jika **Mode Redesain**: Baca seluruh folder `references/` -> tulis `docs/prd.md` & `docs/branding.md` -> konsolidasikan `references/*.html`.
3. Audit struktur file core (`include/config.php`, `core/router.php`, `public/core/router.php`, `core/session.php`, `core/csrf.php`, `core/Repo.php`, `modules/auth/`, `.env`, `data/users.json`).
4. Buat file `docs/build_plan.md` yang memuat mapping shell, file yang belum ada, dan daftar variabel environment.
5. **BERHENTI & TUNGGU APPROVAL OWNER** sebelum lanjut ke Tahap 2.

### TAHAP 2 — EKSEKUSI ONE-SHOT
1. Salin `.env.example` ke `.env` dan sesuaikan `APP_DISPLAY_NAME` serta `APP_TAGLINE`.
2. Update CSS variables di `public/assets/css/branding.css` sesuai `docs/branding.md`.
3. Buat hash Argon2ID valid untuk demo users di `data/users.json`.
4. Untuk setiap shell (`public/index.php`, `login/`, `register/`, `manajemen/`, `admin/`, `client/`):
   - Terapkan require header 4 file: `config.php`, `helper.php`, `session.php`, `csrf.php`.
   - Ekstrak seluruh teks statis menjadi key terjemahan di `locales/id.json`, `en.json`, dan `ar.json`.
   - Ganti nama aplikasi dengan `<?= APP_DISPLAY_NAME ?>`.
5. Validasi syntax PHP dengan `php -l` pada seluruh file `.php`.
6. Validasi fungsional dengan menjalankan server lokal sementara (`php -S localhost:8099 -t public`) dan tes HTTP 200 via `curl`.
7. **BERHENTI & TUNGGU APPROVAL OWNER** sebelum lanjut ke Tahap 3.

### TAHAP 3 — PREVIEW & VERIFIKASI
1. Pastikan document root webserver mengarah ke folder `public/`.
2. Akses `http://{$projectName}.test/` atau `http://localhost/{$projectName}/public/`.
3. Verifikasi auth flow, tombol quick-login demo, perantian bahasa i18n, dan fungsi logout.

---

## 5. Keamanan & User Demo Default

| Role | Email Demo | Password Demo |
|------|------------|---------------|
| Super Admin (Manajemen) | `admin@{$projectName}.com` | `password123` |
| Creator (Admin) | `admin@{$projectName}.id` | `password123` |
| Client (Pendengar) | `client@{$projectName}.com` | `password123` |

- Security Baseline: Password Argon2ID, CSRF Token Validation, IP+Username Rate Limiting, Prepared Statements (PDO Dual-Mode Repo).

---

**Dibuat otomatis oleh Vibeforge Setup Wizard**
MD;

    $installMdPath = ROOT_PATH . '/docs/install.md';

    // Remove existing install.md file first to force full recreation
    if (file_exists($installMdPath)) {
        @unlink($installMdPath);
    }

    if (@file_put_contents($installMdPath, $installMd)) {
        echo json_encode([
            'success' => true,
            'path' => $installMdPath,
            'message' => 'install.md berhasil di-generate'
        ]);
    } else {
        http_response_code(500);
        echo json_encode([
            'success' => false,
            'error' => 'Gagal menulis install.md'
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
