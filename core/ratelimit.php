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
 * Rate limiting baseline (Section 8): fixed window, keyed by caller
 * (typically "login:{ip}:{email}") so it's shared IP+username scoping,
 * not just IP. Backed by `login_attempts` entity via Repo.
 */
if (!function_exists('isRateLimited')) {
    function isRateLimited(string $key): bool
    {
        $window = defined('RATE_LIMIT_WINDOW') ? RATE_LIMIT_WINDOW : 300;
        $max = defined('RATE_LIMIT_MAX_ATTEMPTS') ? RATE_LIMIT_MAX_ATTEMPTS : 5;
        $now = time();

        $recent = array_filter(
            Repo::table('login_attempts')->where(['key' => $key]),
            fn(array $row) => ($now - (int) ($row['timestamp'] ?? 0)) < $window
        );

        return count($recent) >= $max;
    }
}

if (!function_exists('recordFailedAttempt')) {
    function recordFailedAttempt(string $key): void
    {
        Repo::table('login_attempts')->insert(['key' => $key, 'timestamp' => time()]);
    }
}

if (!function_exists('clearRateLimit')) {
    function clearRateLimit(string $key): void
    {
        foreach (Repo::table('login_attempts')->where(['key' => $key]) as $row) {
            Repo::table('login_attempts')->delete($row['id']);
        }
    }
}
