<?php

if (!defined('APP_ENTRY')) {
    http_response_code(403);
    exit('Direct access forbidden');
}

if (!defined('LOCALES_PATH')) {
    define('LOCALES_PATH', dirname(__DIR__) . '/locales');
}

if (!function_exists('escape')) {
    function escape(?string $value): string
    {
        return htmlspecialchars($value ?? '', ENT_QUOTES, 'UTF-8');
    }
}

if (!function_exists('redirect')) {
    function redirect(string $url): void
    {
        header('Location: ' . $url);
        exit;
    }
}

if (!defined('LANGUAGES_MANIFEST')) {
    define('LANGUAGES_MANIFEST', LOCALES_PATH . '/languages.json');
}

/**
 * Build the language list for the selector UI from locales/languages.json.
 */
function getAvailableLanguages(): array
{
    static $languages = null;

    if ($languages === null) {
        $languages = [];
        $raw = file_exists(LANGUAGES_MANIFEST) ? file_get_contents(LANGUAGES_MANIFEST) : false;
        $manifest = $raw !== false ? (json_decode($raw, true) ?? []) : [];

        foreach ($manifest as $code => $meta) {
            if (!file_exists(LOCALES_PATH . "/{$code}.json")) {
                continue;
            }
            $languages[$code] = [
                'code' => $code,
                'name' => $meta['name'] ?? strtoupper($code),
                'flag' => $meta['flag'] ?? "/assets/flags/{$code}.svg",
                'rtl'  => (bool) ($meta['rtl'] ?? false),
            ];
        }
    }

    return $languages;
}

/**
 * Language codes that have both a manifest entry and a translation file.
 */
function getAvailableLocaleCodes(): array
{
    return array_keys(getAvailableLanguages());
}

function isRtlLanguage(): bool
{
    $lang = $_SESSION['language'] ?? detectLanguage();
    $languages = getAvailableLanguages();

    return $languages[$lang]['rtl'] ?? false;
}

function getClientIp(): string
{
    $keys = [
        'HTTP_CLIENT_IP',
        'HTTP_X_FORWARDED_FOR',
        'HTTP_X_FORWARDED',
        'HTTP_X_CLUSTER_CLIENT_IP',
        'HTTP_FORWARDED_FOR',
        'HTTP_FORWARDED',
        'REMOTE_ADDR'
    ];

    foreach ($keys as $key) {
        if (!empty($_SERVER[$key])) {
            foreach (explode(',', $_SERVER[$key]) as $ip) {
                $ip = trim($ip);
                if (filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE) !== false) {
                    return $ip;
                }
            }
        }
    }

    return $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1';
}

function getCountryCodeFromIP(string $ip): string
{
    // Localhost / Development IP
    if ($ip === '127.0.0.1' || $ip === '::1' || strpos($ip, '192.168.') === 0 || strpos($ip, '10.') === 0) {
        return 'ID'; // Default dev country
    }

    // Try HTTP headers if provided by Cloudflare / Reverse Proxy
    if (!empty($_SERVER['HTTP_CF_IPCOUNTRY'])) {
        return strtoupper($_SERVER['HTTP_CF_IPCOUNTRY']);
    }

    return 'ID';
}

/**
 * Language detection algorithm:
 * 1. URL parameter (?lang=xx) -> updates session & user preference in DB (users.json / MySQL)
 * 2. Active Session ($_SESSION['language'])
 * 3. Logged-in user's saved preference in database (language_preference)
 * 4. IP-based Country Detection:
 *    - Arab League countries -> 'ar'
 *    - Mapped countries in manifest (ID, JP, US, GB, etc.) -> matching language
 *    - Unmapped country: if Arab country -> 'ar', otherwise -> 'en' (default fallback)
 */
function detectLanguage(): string
{
    $available = getAvailableLocaleCodes();

    // 1. URL Parameter (highest priority, updates session & DB)
    if (!empty($_GET['lang']) && in_array($_GET['lang'], $available, true)) {
        $lang = $_GET['lang'];
        $_SESSION['language'] = $lang;

        // Save preference to database if user is logged in
        if (function_exists('isLoggedIn') && function_exists('getCurrentUser') && function_exists('Repo') && isLoggedIn()) {
            $user = getCurrentUser();
            if (!empty($user['id']) && ($user['language_preference'] ?? '') !== $lang) {
                Repo::table('users')->update($user['id'], ['language_preference' => $lang]);
            }
        }
        return $lang;
    }

    // 2. Active Session
    if (!empty($_SESSION['language']) && in_array($_SESSION['language'], $available, true)) {
        return $_SESSION['language'];
    }

    // 3. Logged-in user database preference
    if (function_exists('isLoggedIn') && function_exists('getCurrentUser') && isLoggedIn()) {
        $user = getCurrentUser();
        if (!empty($user['language_preference']) && in_array($user['language_preference'], $available, true)) {
            $_SESSION['language'] = $user['language_preference'];
            return $user['language_preference'];
        }
    }

    // 4. IP-based Country Detection & Mapping
    $ip = getClientIp();
    $countryCode = getCountryCodeFromIP($ip);

    $arabicCountries = [
        'SA', 'AE', 'EG', 'IQ', 'JO', 'MA', 'DZ', 'KW', 'QA', 'BH', 'OM', 'YE', 'SY', 'LB', 'SD', 'LY', 'TN', 'MR', 'PS', 'SO', 'DJ', 'KM'
    ];

    $countryToLang = [
        // ASEAN (Indonesian)
        'ID' => 'id', 'MY' => 'id', 'SG' => 'id', 'BN' => 'id', 'TL' => 'id',
        // Japanese
        'JP' => 'ja',
        // English speaking
        'US' => 'en', 'GB' => 'en', 'AU' => 'en', 'NZ' => 'en', 'CA' => 'en', 'IE' => 'en',
    ];
    foreach ($arabicCountries as $ac) {
        $countryToLang[$ac] = 'ar';
    }

    if (isset($countryToLang[$countryCode]) && in_array($countryToLang[$countryCode], $available, true)) {
        $_SESSION['language'] = $countryToLang[$countryCode];
        return $countryToLang[$countryCode];
    }

    // Unmapped country fallback: 'ar' for Arab countries, 'en' for all others
    if (in_array($countryCode, $arabicCountries, true) && in_array('ar', $available, true)) {
        $fallback = 'ar';
    } else {
        $fallback = in_array('en', $available, true) ? 'en' : ($available[0] ?? 'en');
    }

    if (defined('APP_DEBUG') && APP_DEBUG) {
        $logLine = sprintf(
            "[%s] [i18n] Country \"%s\" (IP: %s) fallback to \"%s\".\n",
            date('Y-m-d H:i:s'),
            $countryCode,
            $ip,
            $fallback
        );
        @file_put_contents(dirname(__DIR__) . '/cache/debug.log', $logLine, FILE_APPEND);
    }

    $_SESSION['language'] = $fallback;
    return $fallback;
}

function buildLangUrl(string $langCode): string
{
    $params = $_GET;
    $params['lang'] = $langCode;
    $path = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?? '/';
    return $path . '?' . http_build_query($params);
}

function t(string $key, string $fallback = ''): string
{
    static $translations = [];

    $lang = detectLanguage();

    if (!isset($translations[$lang])) {
        $file = LOCALES_PATH . "/{$lang}.json";
        $translations[$lang] = file_exists($file)
            ? (json_decode(file_get_contents($file), true) ?? [])
            : [];
    }

    return $translations[$lang][$key] ?? ($fallback ?: $key);
}
