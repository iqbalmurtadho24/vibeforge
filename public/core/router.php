<?php
/**
 * Router Proxy
 *
 * This file proxies requests to the actual router in the parent directory.
 * Created because Apache document root is set to public/.
 */

define('APP_ENTRY', true);

// Include the actual router (go up 2 levels: public/core -> public -> project root)
require_once dirname(__DIR__, 2) . '/core/router.php';
