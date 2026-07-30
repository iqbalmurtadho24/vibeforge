<?php
/**
 * Vibeforge - Installation Wizard Bootstrap
 * 4-Step Unified Flow: Install -> Referensi -> Branding -> PRD
 *
 * Auto-detects environment from PHP $_SERVER (no manual input).
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
];

// Check for existing logo
$logoPath = $projectRoot . '/docs/logo.png';
$logoBase64 = '';
if (file_exists($logoPath)) {
    $logoData = file_get_contents($logoPath);
    if ($logoData !== false) {
        $logoBase64 = base64_encode($logoData);
    }
}
$filesData['logoBase64'] = $logoBase64;

// =====================================================
// AUTO-DETECT from PHP environment (Tahap 1)
// =====================================================
$docRoot = $_SERVER['DOCUMENT_ROOT'] ?? '';
$projectPath = dirname($docRoot);
$appName = basename($projectPath);
$serverName = $_SERVER['SERVER_NAME'] ?? $appName . '.test';
$localDomain = $serverName;
$phpVersion = phpversion();
$phpOk = version_compare($phpVersion, '8.3.0', '>=');
$isLaragon = stripos($projectPath, 'laragon') !== false;
$isXampp = stripos($projectPath, 'xampp') !== false;
$serverType = $isLaragon ? 'laragon' : ($isXampp ? 'xampp' : 'unknown');
$serverLabel = $isLaragon ? 'Laragon' : ($isXampp ? 'XAMPP' : 'Unknown');

$writableData = is_writable($projectPath . '/data');
$writableDocs = is_writable($projectPath . '/docs');
$writableRefs = is_writable($projectPath . '/references');
$writableCache = is_writable($projectPath . '/cache');

$allChecksPass = $phpOk && $writableData && $writableDocs;

// Pass to JS as JSON-safe values
$jsAppName = json_encode($appName);
$jsProjectPath = json_encode(str_replace('\\', '/', $projectPath));
$jsLocalDomain = json_encode($localDomain);
$jsPhpVersion = json_encode($phpVersion);
$jsServerType = json_encode($serverType);
$jsServerLabel = json_encode($serverLabel);
$jsDocRoot = json_encode(str_replace('\\', '/', $docRoot));
?>
