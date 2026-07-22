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

$name = trim((string) ($_POST['name'] ?? ''));
$email = trim((string) ($_POST['email'] ?? ''));
$password = (string) ($_POST['password'] ?? '');

if ($name === '' || !filter_var($email, FILTER_VALIDATE_EMAIL) || strlen($password) < 8) {
    echo json_encode(['success' => false, 'error' => t('register.invalid', 'Data pendaftaran tidak valid.')]);
    exit;
}

if (Repo::table('users')->where(['email' => $email]) !== []) {
    echo json_encode(['success' => false, 'error' => t('register.email_taken', 'Email sudah terdaftar.')]);
    exit;
}

$userId = Repo::table('users')->insert([
    'name' => $name,
    'email' => $email,
    'password_hash' => password_hash($password, PASSWORD_ARGON2ID),
    'role' => 'client',
    'theme_preference' => 'dark',
    'created_at' => date('c'),
    'updated_at' => date('c'),
]);

Repo::table('audit_trail')->insert([
    'action' => 'register',
    'user_id' => $userId,
    'ip' => $_SERVER['REMOTE_ADDR'] ?? 'unknown',
    'created_at' => date('c'),
]);

echo json_encode(['success' => true]);
