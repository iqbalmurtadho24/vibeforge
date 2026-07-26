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
 * Adding a language = add an entry here + drop the matching locales/xx.json
 * translation file. No PHP code change, no hardcoded language list.
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

function detectLanguage(): string
{
    $available = getAvailableLocaleCodes();

    // 1. URL Parameter (takes priority and updates session)
    if (!empty($_GET['lang']) && in_array($_GET['lang'], $available, true)) {
        $_SESSION['language'] = $_GET['lang'];
        return $_GET['lang'];
    }

    // 2. Session
    if (!empty($_SESSION['language']) && in_array($_SESSION['language'], $available, true)) {
        return $_SESSION['language'];
    }

    // 3. IP-based Country Detection
    $ip = getClientIp();
    $countryCode = getCountryCodeFromIP($ip);

    $countryToLang = [
        // Indonesian / ASEAN
        'ID' => 'id', 'MY' => 'id', 'SG' => 'id', 'BN' => 'id', 'TL' => 'id',
        // Japanese
        'JP' => 'ja',
        // English speaking countries
        'US' => 'en', 'GB' => 'en', 'AU' => 'en', 'NZ' => 'en', 'CA' => 'en', 'IE' => 'en',
        // Arabic speaking countries
        'SA' => 'ar', 'AE' => 'ar', 'EG' => 'ar', 'IQ' => 'ar', 'JO' => 'ar',
        'MA' => 'ar', 'DZ' => 'ar', 'KW' => 'ar', 'QA' => 'ar', 'BH' => 'ar',
        'OM' => 'ar', 'YE' => 'ar', 'SY' => 'ar', 'LB' => 'ar', 'SD' => 'ar',
    ];

    if (isset($countryToLang[$countryCode]) && in_array($countryToLang[$countryCode], $available, true)) {
        $_SESSION['language'] = $countryToLang[$countryCode];
        return $countryToLang[$countryCode];
    }

    // Unmapped or missing country -> Log warning in APP_DEBUG mode and fallback to English ('en')
    if (defined('APP_DEBUG') && APP_DEBUG) {
        $logLine = sprintf(
            "[%s] [i18n] Negara \"%s\" (IP: %s) belum punya mapping bahasa di detectLanguage(). Fallback ke \"en\".\n",
            date('Y-m-d H:i:s'),
            $countryCode,
            $ip
        );
        @file_put_contents(dirname(__DIR__) . '/cache/debug.log', $logLine, FILE_APPEND);
    }

    $fallback = in_array('en', $available, true) ? 'en' : ($available[0] ?? 'en');
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
