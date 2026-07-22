<?php

if (!defined('APP_ENTRY')) {
    http_response_code(403);
    exit('Direct access forbidden');
}

if (!defined('ROOT_PATH')) {
    define('ROOT_PATH', dirname(__DIR__));
}

if (!function_exists('loadEnv')) {
    function loadEnv(string $path): void
    {
        if (!file_exists($path)) {
            return;
        }

        foreach (file($path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) as $line) {
            $line = trim($line);
            if ($line === '' || str_starts_with($line, '#') || !str_contains($line, '=')) {
                continue;
            }

            [$key, $value] = explode('=', $line, 2);
            $key = trim($key);
            $value = trim(trim($value), "\"'");

            if (getenv($key) === false) {
                putenv("{$key}={$value}");
            }
            $_ENV[$key] = $value;
        }
    }
}

loadEnv(ROOT_PATH . '/.env');

if (!function_exists('env')) {
    function env(string $key, $default = null)
    {
        if (array_key_exists($key, $_ENV)) {
            return $_ENV[$key];
        }
        $value = getenv($key);
        return $value === false ? $default : $value;
    }
}

defined('APP_ENV') or define('APP_ENV', env('APP_ENV', 'development'));
defined('APP_DEBUG') or define('APP_DEBUG', filter_var(env('APP_DEBUG', 'true'), FILTER_VALIDATE_BOOLEAN));
defined('APP_DISPLAY_NAME') or define('APP_DISPLAY_NAME', env('APP_DISPLAY_NAME', 'App'));

// DB_MODE: auto (default, per-entity SQL/JSON detection) | json (force) | mysql (force)
defined('DB_MODE') or define('DB_MODE', env('DB_MODE', 'auto'));
defined('DB_HOST') or define('DB_HOST', env('DB_HOST', 'localhost'));
defined('DB_PORT') or define('DB_PORT', env('DB_PORT', '3306'));
defined('DB_NAME') or define('DB_NAME', env('DB_NAME', ''));
defined('DB_USER') or define('DB_USER', env('DB_USER', 'root'));
defined('DB_PASSWORD') or define('DB_PASSWORD', env('DB_PASSWORD', ''));

defined('DATA_PATH') or define('DATA_PATH', ROOT_PATH . '/data');
defined('CACHE_PATH') or define('CACHE_PATH', ROOT_PATH . '/cache');

if (!is_dir(DATA_PATH)) {
    mkdir(DATA_PATH, 0755, true);
}
if (!is_dir(CACHE_PATH)) {
    mkdir(CACHE_PATH, 0755, true);
}

defined('CSRF_KEY') or define('CSRF_KEY', env('CSRF_KEY', ''));
defined('SESSION_LIFETIME') or define('SESSION_LIFETIME', (int) env('SESSION_LIFETIME', 7200));
defined('REMEMBER_ME_SECRET') or define('REMEMBER_ME_SECRET', env('REMEMBER_ME_SECRET', ''));
defined('REMEMBER_ME_LIFETIME') or define('REMEMBER_ME_LIFETIME', (int) env('REMEMBER_ME_LIFETIME', 60 * 60 * 24 * 30));
defined('RATE_LIMIT_MAX_ATTEMPTS') or define('RATE_LIMIT_MAX_ATTEMPTS', (int) env('RATE_LIMIT_MAX_ATTEMPTS', 5));
defined('RATE_LIMIT_WINDOW') or define('RATE_LIMIT_WINDOW', (int) env('RATE_LIMIT_WINDOW', 300));

if (!function_exists('isProduction')) {
    function isProduction(): bool
    {
        return defined('APP_ENV') && APP_ENV === 'production';
    }
}
