<?php

if (!defined('APP_ENTRY')) {
    http_response_code(403);
    exit('Direct access forbidden');
}

if (!defined('ROOT_PATH')) {
    define('ROOT_PATH', dirname(__DIR__));
}

require_once ROOT_PATH . '/include/config.php';
require_once ROOT_PATH . '/include/helper.php';
require_once ROOT_PATH . '/core/session.php';
require_once ROOT_PATH . '/core/csrf.php';
require_once ROOT_PATH . '/core/remember.php';
require_once ROOT_PATH . '/core/ratelimit.php';

initSession();

header('Content-Type: application/json');

$module = $_POST['module'] ?? $_GET['module'] ?? '';
$action = $_POST['action'] ?? $_GET['action'] ?? '';

if (!preg_match('/^[a-z0-9_]+$/', $module) || !preg_match('/^[a-z0-9_]+$/', $action)) {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => 'Invalid module/action']);
    exit;
}

// CSRF verified centrally here, not per-module (Section 8).
$csrfToken = $_POST['csrf_token'] ?? $_GET['csrf_token'] ?? null;
if (!verifyCsrfToken($csrfToken)) {
    http_response_code(403);
    echo json_encode(['success' => false, 'error' => 'Invalid CSRF token']);
    exit;
}

$modulePath = ROOT_PATH . "/modules/{$module}/{$action}.php";

if (!file_exists($modulePath)) {
    http_response_code(404);
    echo json_encode(['success' => false, 'error' => 'Module not found']);
    exit;
}

require $modulePath;
