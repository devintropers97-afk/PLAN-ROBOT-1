<?php
/**
 * ZYN Trade System - Language Handler
 * Multi-language support with Indonesian as default
 */

// Available languages
define('AVAILABLE_LANGUAGES', [
    'id' => ['name' => 'Bahasa Indonesia', 'flag' => '🇮🇩', 'dir' => 'ltr'],
    'en' => ['name' => 'English', 'flag' => '🇬🇧', 'dir' => 'ltr'],
    'ms' => ['name' => 'Bahasa Melayu', 'flag' => '🇲🇾', 'dir' => 'ltr'],
    'th' => ['name' => 'ไทย', 'flag' => '🇹🇭', 'dir' => 'ltr'],
    'vi' => ['name' => 'Tiếng Việt', 'flag' => '🇻🇳', 'dir' => 'ltr'],
    'zh' => ['name' => '中文', 'flag' => '🇨🇳', 'dir' => 'ltr'],
    'hi' => ['name' => 'हिन्दी', 'flag' => '🇮🇳', 'dir' => 'ltr'],
    'ar' => ['name' => 'العربية', 'flag' => '🇸🇦', 'dir' => 'rtl'],
    'pt' => ['name' => 'Português', 'flag' => '🇧🇷', 'dir' => 'ltr'],
    'es' => ['name' => 'Español', 'flag' => '🇪🇸', 'dir' => 'ltr'],
    'ru' => ['name' => 'Русский', 'flag' => '🇷🇺', 'dir' => 'ltr'],
    'fr' => ['name' => 'Français', 'flag' => '🇫🇷', 'dir' => 'ltr'],
    'de' => ['name' => 'Deutsch', 'flag' => '🇩🇪', 'dir' => 'ltr'],
    'ja' => ['name' => '日本語', 'flag' => '🇯🇵', 'dir' => 'ltr'],
    'ko' => ['name' => '한국어', 'flag' => '🇰🇷', 'dir' => 'ltr'],
    'tr' => ['name' => 'Türkçe', 'flag' => '🇹🇷', 'dir' => 'ltr'],
]);

// Default language
define('DEFAULT_LANGUAGE', 'id');

// Global translations variable
$GLOBALS['translations'] = [];
$GLOBALS['current_lang'] = DEFAULT_LANGUAGE;

/**
 * Initialize language system
 */
function initLanguage() {
    // Check if language is set in session, cookie, or URL
    $lang = DEFAULT_LANGUAGE;

    // Priority: URL param > Session > Cookie > Browser > Default
    if (isset($_GET['lang']) && isValidLanguage($_GET['lang'])) {
        $lang = $_GET['lang'];
        $_SESSION['language'] = $lang;
        setcookie('language', $lang, time() + (365 * 24 * 60 * 60), '/');
    } elseif (isset($_SESSION['language']) && isValidLanguage($_SESSION['language'])) {
        $lang = $_SESSION['language'];
    } elseif (isset($_COOKIE['language']) && isValidLanguage($_COOKIE['language'])) {
        $lang = $_COOKIE['language'];
        $_SESSION['language'] = $lang;
    } else {
        // Detect from browser
        $browserLang = detectBrowserLanguage();
        if ($browserLang && isValidLanguage($browserLang)) {
            $lang = $browserLang;
        }
    }

    $GLOBALS['current_lang'] = $lang;
    loadTranslations($lang);

    return $lang;
}

/**
 * Check if language code is valid
 */
function isValidLanguage($lang) {
    return isset(AVAILABLE_LANGUAGES[$lang]);
}

/**
 * Detect browser language
 */
function detectBrowserLanguage() {
    if (!isset($_SERVER['HTTP_ACCEPT_LANGUAGE'])) {
        return null;
    }

    $browserLang = substr($_SERVER['HTTP_ACCEPT_LANGUAGE'], 0, 2);
    return strtolower($browserLang);
}

/**
 * Load translations for a language
 */
function loadTranslations($lang) {
    $langFile = __DIR__ . '/../lang/' . $lang . '.php';
    $defaultFile = __DIR__ . '/../lang/' . DEFAULT_LANGUAGE . '.php';

    // Load default language first as fallback
    if (file_exists($defaultFile)) {
        $GLOBALS['translations'] = require $defaultFile;
    }

    // Override with selected language if different
    if ($lang !== DEFAULT_LANGUAGE && file_exists($langFile)) {
        $langTranslations = require $langFile;
        $GLOBALS['translations'] = array_merge($GLOBALS['translations'], $langTranslations);
    }
}

/**
 * Get translation by key
 * @param string $key Translation key
 * @param array $params Optional parameters for replacement
 * @return string Translated text
 */
function __($key, $params = []) {
    $text = $GLOBALS['translations'][$key] ?? $key;

    // Replace parameters if provided
    if (!empty($params)) {
        foreach ($params as $param => $value) {
            $text = str_replace(':' . $param, $value, $text);
        }
    }

    return $text;
}

/**
 * Echo translation
 */
function _e($key, $params = []) {
    echo __($key, $params);
}

/**
 * Get current language code
 */
function getCurrentLanguage() {
    return $GLOBALS['current_lang'];
}

/**
 * Get current language info
 */
function getCurrentLanguageInfo() {
    $lang = getCurrentLanguage();
    return AVAILABLE_LANGUAGES[$lang] ?? AVAILABLE_LANGUAGES[DEFAULT_LANGUAGE];
}

/**
 * Get all available languages
 */
function getAvailableLanguages() {
    return AVAILABLE_LANGUAGES;
}

/**
 * Get language switcher URL
 */
function getLanguageSwitchUrl($lang) {
    $currentUrl = $_SERVER['REQUEST_URI'];
    $parsedUrl = parse_url($currentUrl);
    $path = $parsedUrl['path'] ?? '/';

    // Parse existing query string
    $query = [];
    if (isset($parsedUrl['query'])) {
        parse_str($parsedUrl['query'], $query);
    }

    // Set or update language parameter
    $query['lang'] = $lang;

    return $path . '?' . http_build_query($query);
}

/**
 * Get text direction (ltr or rtl)
 */
function getTextDirection() {
    $langInfo = getCurrentLanguageInfo();
    return $langInfo['dir'] ?? 'ltr';
}

/**
 * Render language selector dropdown
 */
function renderLanguageSelector($class = '') {
    $currentLang = getCurrentLanguage();
    $currentInfo = getCurrentLanguageInfo();
    $languages = getAvailableLanguages();

    $html = '<div class="dropdown language-selector ' . $class . '">';
    $html .= '<button class="btn btn-outline-secondary btn-sm dropdown-toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false">';
    $html .= '<span class="lang-flag">' . $currentInfo['flag'] . '</span> ';
    $html .= '<span class="lang-code d-none d-sm-inline">' . strtoupper($currentLang) . '</span>';
    $html .= '</button>';
    $html .= '<ul class="dropdown-menu dropdown-menu-end language-dropdown">';

    foreach ($languages as $code => $info) {
        $activeClass = ($code === $currentLang) ? 'active' : '';
        $html .= '<li>';
        $html .= '<a class="dropdown-item ' . $activeClass . '" href="' . getLanguageSwitchUrl($code) . '">';
        $html .= '<span class="lang-flag">' . $info['flag'] . '</span> ';
        $html .= '<span class="lang-name">' . $info['name'] . '</span>';
        if ($code === $currentLang) {
            $html .= ' <i class="fas fa-check text-success ms-2"></i>';
        }
        $html .= '</a>';
        $html .= '</li>';
    }

    $html .= '</ul>';
    $html .= '</div>';

    return $html;
}

/**
 * Get HTML lang attribute
 */
function getHtmlLang() {
    return getCurrentLanguage();
}

// Auto-initialize when included
// Only init if session is already active (config.php should start it first)
if (session_status() === PHP_SESSION_ACTIVE) {
    initLanguage();
} elseif (session_status() === PHP_SESSION_NONE) {
    // If session not started yet, start it first then init
    session_start();
    initLanguage();
}
