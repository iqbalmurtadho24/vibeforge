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

// Parse JSON request body if POST is empty (e.g. fetch JSON calls)
if (empty($_POST) && isset($_SERVER['REQUEST_METHOD']) && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $rawInput = file_get_contents('php://input');
    if (!empty($rawInput)) {
        $json = json_decode($rawInput, true);
        if (is_array($json)) {
            $_POST = $json;
        }
    }
}

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
$fallbackModulePath = ROOT_PATH . "/modules/{$module}/index.php";

if (!file_exists($modulePath) && !file_exists($fallbackModulePath)) {
    http_response_code(404);
    echo json_encode(['success' => false, 'error' => 'Module not found']);
    exit;
}

require file_exists($modulePath) ? $modulePath : $fallbackModulePath;
