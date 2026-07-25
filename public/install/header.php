<?php
/**
 * Vibeforge - Installation Wizard Shell
 *
 * Flow: Welcome Overview (1) -> PRD (2) -> Branding (3) -> Logo (4) ->
 *        HTML Templates (5-10) -> Server Config (11) -> Install Path (12)
 */
defined('APP_ENTRY') or define('APP_ENTRY', true);

require_once __DIR__ . '/../../include/config.php';
require_once __DIR__ . '/../../include/helper.php';
require_once __DIR__ . '/../../core/session.php';
require_once __DIR__ . '/../../core/csrf.php';

initSession();
if (!empty($_GET['lang']) && in_array($_GET['lang'], getAvailableLocaleCodes(), true)) {
    $_SESSION['language'] = $_GET['lang'];
}
$currentLang = $_SESSION['language'] ?? detectLanguage();
$_SESSION['language'] = $currentLang;
$csrfToken = generateCsrfToken();
$isLoggedIn = isLoggedIn();
$dashboardUrl = getDashboardUrl();

$projectRoot = dirname(__DIR__, 2);

// Pre-load existing file contents for pre-population
$filesData = [
    'prd'            => file_exists($projectRoot . '/docs/prd.md')                   ? file_get_contents($projectRoot . '/docs/prd.md')                   : '',
    'branding'       => file_exists($projectRoot . '/docs/branding.md')              ? file_get_contents($projectRoot . '/docs/branding.md')              : '',
    'landingPage'    => file_exists($projectRoot . '/references/landingpage.html')   ? file_get_contents($projectRoot . '/references/landingpage.html')   : '',
    'loginPage'      => file_exists($projectRoot . '/references/login.html')         ? file_get_contents($projectRoot . '/references/login.html')         : '',
    'registerPage'   => file_exists($projectRoot . '/references/register.html')       ? file_get_contents($projectRoot . '/references/register.html')       : '',
    'manajemenPage'  => file_exists($projectRoot . '/references/modul_manajemen.html') ? file_get_contents($projectRoot . '/references/modul_manajemen.html') : '',
    'adminPage'      => file_exists($projectRoot . '/references/modul_admin.html')    ? file_get_contents($projectRoot . '/references/modul_admin.html')    : '',
    'clientPage'     => file_exists($projectRoot . '/references/modul_client.html')   ? file_get_contents($projectRoot . '/references/modul_client.html')   : '',
    'logoBase64'     => file_exists($projectRoot . '/docs/logo.png')                 ? base64_encode(file_get_contents($projectRoot . '/docs/logo.png'))   : '',
];

$installConfig = [];
$configPath = $projectRoot . '/data/install_config.json';
if (file_exists($configPath)) {
    $installConfig = json_decode(file_get_contents($configPath), true) ?? [];
}

// Auto-detect drive letter from project root
$detectedDrive = strtoupper(substr($projectRoot, 0, 1));
if (!preg_match('/^[A-Z]$/', $detectedDrive)) {
    $detectedDrive = 'C';
}

// Auto-detect server type from project path or common locations
$detectedServer = 'laragon';
$normalizedProjectRoot = str_replace('\\', '/', strtolower($projectRoot));

if (str_contains($normalizedProjectRoot, 'xampp')) {
    $detectedServer = 'xampp';
} elseif (str_contains($normalizedProjectRoot, 'laragon')) {
    $detectedServer = 'laragon';
} else {
    // Check common server root directories
    $drivePrefix = $detectedDrive . ':/';
    if (is_dir($drivePrefix . 'xampp/htdocs')) {
        $detectedServer = 'xampp';
    } elseif (is_dir($drivePrefix . 'laragon/www')) {
        $detectedServer = 'laragon';
    }
}

// Build the default install path
$defaultInstallPath = $detectedDrive . ':\\' . ($detectedServer === 'laragon' ? 'laragon\\www' : 'xampp\\htdocs');

// Use saved config OR detected values (detected takes precedence for fresh installs)
$effectiveDrive = $installConfig['drive'] ?? $detectedDrive;
$effectiveServer = $installConfig['server_type'] ?? $detectedServer;
$effectiveInstallPath = $installConfig['install_path'] ?? ($detectedDrive . ':\\' . ($detectedServer === 'laragon' ? 'laragon\\www' : 'xampp\\htdocs'));

// Build paths for UI preview
$laragonPreviewPath = $effectiveDrive . ':\\laragon\\www';
$xamppPreviewPath = $effectiveDrive . ':\\xampp\\htdocs';
$referencesPath = $projectRoot . '/references';

// Detect available Windows drives
$availableDrives = [];
if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') {
    foreach (range('A', 'Z') as $letter) {
        if (is_dir($letter . ':\\')) {
            $availableDrives[] = $letter;
        }
    }
}
if (empty($availableDrives)) {
    $availableDrives = ['C', 'D', 'E', 'F', 'G', 'H'];
}
if (!in_array($effectiveDrive, $availableDrives, true)) {
    $availableDrives[] = $effectiveDrive;
    sort($availableDrives);
}

// Pass to JS as JSON-safe values
$jsDrive = json_encode($effectiveDrive);
$jsServer = json_encode($effectiveServer);
$jsInstallPath = json_encode(str_replace('\\', '\\\\', $effectiveInstallPath));
$jsReferencesPath = json_encode(str_replace('\\', '\\\\', $referencesPath));
$jsAvailableDrives = json_encode($availableDrives);
?>
