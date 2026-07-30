<?php
/**
 * Vibeforge Setup Launcher
 *
 * Runs the vibeforge-setup.bat file server-side.
 * Since Laragon runs on the same machine as the user,
 * this opens PowerShell directly on the user's desktop.
 *
 * Only works in development/staging environment.
 */
defined('APP_ENTRY') or define('APP_ENTRY', true);

require_once __DIR__ . '/../include/config.php';

header('Content-Type: application/json; charset=utf-8');

// Block in production
if (defined('APP_ENV') && APP_ENV === 'production') {
    http_response_code(403);
    echo json_encode(['success' => false, 'error' => 'Forbidden in production']);
    exit;
}

// Only accept POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'error' => 'Method not allowed']);
    exit;
}

$batPath = realpath(__DIR__ . '/vibeforge-setup.bat');

if (!$batPath || !file_exists($batPath)) {
    http_response_code(404);
    echo json_encode(['success' => false, 'error' => 'Setup script not found']);
    exit;
}

// Run the bat file in a new window — does NOT block the PHP request
pclose(popen('start "" "' . $batPath . '"', 'r'));

echo json_encode(['success' => true]);
