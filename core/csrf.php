<?php

if (!defined('APP_ENTRY')) {
    http_response_code(403);
    exit('Direct access forbidden');
}

/**
 * CSRF token generation/verification, centralized (Section 8, CLAUDE.md).
 * Synchronizer token pattern: one token per session, reused across the
 * SPA shell's AJAX calls, verified with hash_equals() in core/router.php.
 */
if (!function_exists('generateCsrfToken')) {
    function generateCsrfToken(): string
    {
        if (empty($_SESSION['csrf_token'])) {
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        }

        return $_SESSION['csrf_token'];
    }
}

if (!function_exists('verifyCsrfToken')) {
    function verifyCsrfToken(?string $token): bool
    {
        if (empty($_SESSION['csrf_token']) || $token === null || $token === '') {
            return false;
        }

        return hash_equals($_SESSION['csrf_token'], $token);
    }
}
