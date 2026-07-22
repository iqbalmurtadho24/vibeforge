<?php

if (!defined('APP_ENTRY')) {
    http_response_code(403);
    exit('Direct access forbidden');
}

if (!defined('ROOT_PATH')) {
    define('ROOT_PATH', dirname(__DIR__, 2));
}
require_once ROOT_PATH . '/include/config.php';
require_once ROOT_PATH . '/include/helper.php';

$email = trim((string) ($_POST['email'] ?? ''));
$password = (string) ($_POST['password'] ?? '');
$remember = ($_POST['remember'] ?? '0') === '1';

if ($email === '' || $password === '') {
    echo json_encode(['success' => false, 'error' => t('login.failed', 'Email atau password salah')]);
    exit;
}

$ip = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
$rateLimitKey = "login:{$ip}:" . strtolower($email);

if (isRateLimited($rateLimitKey)) {
    http_response_code(429);
    echo json_encode(['success' => false, 'error' => t('login.rate_limited', 'Terlalu banyak percobaan. Coba lagi nanti.')]);
    exit;
}

$matches = Repo::table('users')->where(['email' => $email]);
$user = $matches[0] ?? null;

$valid = $user !== null && password_verify($password, $user['password_hash'] ?? '');

// Dev-only convenience bypass (Section 6): password123 accepted for any
// demo user while APP_ENV !== production. Never active in production.
if (!$valid && $user !== null && !isProduction() && $password === 'password123') {
    $valid = true;
}

if (!$valid) {
    recordFailedAttempt($rateLimitKey);
    echo json_encode(['success' => false, 'error' => t('login.failed', 'Email atau password salah')]);
    exit;
}

clearRateLimit($rateLimitKey);

session_regenerate_id(true);
$_SESSION['user_id'] = $user['id'];
$_SESSION['role'] = $user['role'];

if ($remember) {
    issueRememberToken($user['id']);
}

Repo::table('audit_trail')->insert([
    'action' => 'login',
    'user_id' => $user['id'],
    'ip' => $ip,
    'created_at' => date('c'),
]);

echo json_encode([
    'success' => true,
    'dashboard_url' => getDashboardUrl(),
]);
