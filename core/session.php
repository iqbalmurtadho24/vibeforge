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
 * Session bootstrap + auth-state helpers (Section 3b, CLAUDE.md).
 * Session is the single source of truth for login/role state; shells
 * re-validate it server-side on every load rather than trusting JS.
 */
if (!function_exists('initSession')) {
    function initSession(): void
    {
        if (session_status() === PHP_SESSION_ACTIVE) {
            return;
        }

        session_set_cookie_params([
            'lifetime' => defined('SESSION_LIFETIME') ? SESSION_LIFETIME : 7200,
            'path' => '/',
            'domain' => '',
            'secure' => isProduction(),
            'httponly' => true,
            'samesite' => 'Lax',
        ]);

        session_start();
    }
}

if (!function_exists('isLoggedIn')) {
    function isLoggedIn(): bool
    {
        return !empty($_SESSION['user_id']);
    }
}

if (!function_exists('getCurrentUser')) {
    function getCurrentUser(): ?array
    {
        static $cached = null;
        static $resolved = false;

        if ($resolved) {
            return $cached;
        }
        $resolved = true;

        if (empty($_SESSION['user_id'])) {
            return $cached = null;
        }

        return $cached = Repo::table('users')->find($_SESSION['user_id']);
    }
}

if (!function_exists('getDashboardUrl')) {
    function getDashboardUrl(): string
    {
        $roleToShell = [
            'manajemen' => '/manajemen/',
            'admin' => '/admin/',
            'client' => '/client/',
        ];

        $role = $_SESSION['role'] ?? null;

        return $roleToShell[$role] ?? '/';
    }
}

if (!function_exists('requireRole')) {
    /** @param string|array $roles */
    function requireRole($roles): void
    {
        $allowed = is_array($roles) ? $roles : [$roles];

        if (!isLoggedIn() || !in_array($_SESSION['role'] ?? null, $allowed, true)) {
            http_response_code(403);
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'error' => 'Forbidden']);
            exit;
        }
    }
}
