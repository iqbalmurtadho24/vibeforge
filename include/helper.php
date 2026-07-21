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
    $lang = $_SESSION['language'] ?? 'id';
    $languages = getAvailableLanguages();

    return $languages[$lang]['rtl'] ?? false;
}

function detectLanguage(): string
{
    if (!empty($_SESSION['language'])) {
        return $_SESSION['language'];
    }

    $available = getAvailableLocaleCodes();

    if (!empty($_GET['lang']) && in_array($_GET['lang'], $available, true)) {
        $_SESSION['language'] = $_GET['lang'];
        return $_GET['lang'];
    }

    return in_array('id', $available, true) ? 'id' : ($available[0] ?? 'id');
}

function t(string $key, string $fallback = ''): string
{
    static $translations = [];

    $lang = $_SESSION['language'] ?? detectLanguage();

    if (!isset($translations[$lang])) {
        $file = LOCALES_PATH . "/{$lang}.json";
        $translations[$lang] = file_exists($file)
            ? (json_decode(file_get_contents($file), true) ?? [])
            : [];
    }

    return $translations[$lang][$key] ?? ($fallback ?: $key);
}
