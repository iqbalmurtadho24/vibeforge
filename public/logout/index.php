<?php
/**
 * Logout Handler (GET Request)
 *
 * Destroys session and redirects to landing page.
 */

// Error reporting is governed centrally by include/config.php (APP_DEBUG).

// Define APP_ENTRY first
define('APP_ENTRY', true);

// Load configuration - use correct path from public/logout/ to include/
require_once __DIR__ . '/../../include/config.php';
require_once __DIR__ . '/../../include/helper.php';

// Initialize session
initSession();

// Invalidate this device's remember-me token + cookie
clearRememberToken();

// Clear all session data
$_SESSION = [];

// Delete session cookie
if (ini_get('session.use_cookies')) {
    $params = session_get_cookie_params();
    @setcookie(
        session_name(),
        '',
        time() - 42000,
        $params['path'] ?? '/',
        $params['domain'] ?? '',
        $params['secure'] ?? false,
        $params['httponly'] ?? true
    );
}

// Destroy session
@session_destroy();

// Redirect to landing page
header('Location: /');
exit;
