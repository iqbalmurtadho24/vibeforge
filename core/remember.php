<?php

if (!defined('APP_ENTRY')) {
    http_response_code(403);
    exit('Direct access forbidden');
}

if (!defined('ROOT_PATH')) {
    define('ROOT_PATH', dirname(__DIR__));
}
require_once ROOT_PATH . '/core/Repo.php';

/**
 * Remember-me tokens: selector+validator pattern, per-device (Section 8).
 * Data lives in the `remember_tokens` entity via Repo (JSON or SQL,
 * transparent to callers) - never plaintext validator at rest.
 */
if (!function_exists('issueRememberToken')) {
    function issueRememberToken($userId): void
    {
        $selector = bin2hex(random_bytes(9));
        $validator = bin2hex(random_bytes(32));
        $expiresAt = time() + (defined('REMEMBER_ME_LIFETIME') ? REMEMBER_ME_LIFETIME : 60 * 60 * 24 * 30);

        Repo::table('remember_tokens')->insert([
            'user_id' => $userId,
            'selector' => $selector,
            'validator_hash' => hash('sha256', $validator),
            'expires_at' => $expiresAt,
            'created_at' => date('c'),
        ]);

        setcookie('remember_token', $selector . ':' . $validator, [
            'expires' => $expiresAt,
            'path' => '/',
            'secure' => isProduction(),
            'httponly' => true,
            'samesite' => 'Lax',
        ]);
    }
}

if (!function_exists('clearRememberToken')) {
    function clearRememberToken(): void
    {
        $cookie = $_COOKIE['remember_token'] ?? '';
        if ($cookie !== '' && str_contains($cookie, ':')) {
            [$selector] = explode(':', $cookie, 2);
            foreach (Repo::table('remember_tokens')->where(['selector' => $selector]) as $row) {
                Repo::table('remember_tokens')->delete($row['id']);
            }
        }

        if (isset($_COOKIE['remember_token'])) {
            setcookie('remember_token', '', [
                'expires' => time() - 3600,
                'path' => '/',
                'secure' => isProduction(),
                'httponly' => true,
                'samesite' => 'Lax',
            ]);
        }
    }
}
