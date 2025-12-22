<?php
/**
 * ZYN Trade System - Google Analytics Integration
 *
 * CARA SETUP:
 * 1. Buat akun Google Analytics di https://analytics.google.com
 * 2. Buat Property baru untuk website Anda
 * 3. Copy Measurement ID (G-XXXXXXXXXX)
 * 4. Ganti GA_MEASUREMENT_ID di bawah dengan ID Anda
 *
 * CARA PAKAI:
 * Include file ini di header.php:
 * <?php include 'includes/analytics.php'; ?>
 *
 * Track custom event:
 * Analytics::trackEvent('button_click', ['button_name' => 'signup']);
 */

class Analytics {
    // GANTI DENGAN MEASUREMENT ID ANDA
    const GA_MEASUREMENT_ID = 'G-XXXXXXXXXX';

    // Enable/disable analytics
    const ENABLED = true;

    /**
     * Render Google Analytics script
     * Taruh di dalam <head>
     */
    public static function renderScript() {
        if (!self::ENABLED || self::GA_MEASUREMENT_ID === 'G-XXXXXXXXXX') {
            return '<!-- Google Analytics: Not configured -->';
        }

        // Check cookie consent
        $consent_granted = isset($_COOKIE['zyn_cookie_consent']) ||
                          (isset($_SESSION['cookie_consent']) && $_SESSION['cookie_consent']);

        $consent_mode = $consent_granted ? 'granted' : 'denied';

        return <<<HTML
<!-- Google Analytics -->
<script async src="https://www.googletagmanager.com/gtag/js?id={self::GA_MEASUREMENT_ID}"></script>
<script>
    window.dataLayer = window.dataLayer || [];
    function gtag(){dataLayer.push(arguments);}
    gtag('js', new Date());

    // Default consent mode (untuk GDPR compliance)
    gtag('consent', 'default', {
        'analytics_storage': '{$consent_mode}',
        'ad_storage': 'denied'
    });

    gtag('config', '{self::GA_MEASUREMENT_ID}', {
        'anonymize_ip': true,
        'cookie_flags': 'SameSite=None;Secure'
    });
</script>
HTML;
    }

    /**
     * Track page view (untuk SPA atau custom tracking)
     */
    public static function renderPageView($page_path = null, $page_title = null) {
        if (!self::ENABLED) return '';

        $path = $page_path ?? ($_SERVER['REQUEST_URI'] ?? '/');
        $title = $page_title ?? ($GLOBALS['page_title'] ?? 'ZYN Trade');

        return <<<HTML
<script>
    gtag('event', 'page_view', {
        page_path: '{$path}',
        page_title: '{$title}'
    });
</script>
HTML;
    }

    /**
     * Track custom event (JavaScript)
     */
    public static function trackEventJS($event_name, $params = []) {
        $params_json = json_encode($params);

        return <<<HTML
<script>
    gtag('event', '{$event_name}', {$params_json});
</script>
HTML;
    }

    /**
     * Track user signup
     */
    public static function trackSignup($method = 'email') {
        return self::trackEventJS('sign_up', ['method' => $method]);
    }

    /**
     * Track login
     */
    public static function trackLogin($method = 'license_key') {
        return self::trackEventJS('login', ['method' => $method]);
    }

    /**
     * Track purchase/subscription
     */
    public static function trackPurchase($transaction_id, $value, $currency = 'USD', $items = []) {
        $params = [
            'transaction_id' => $transaction_id,
            'value' => $value,
            'currency' => $currency,
            'items' => $items
        ];

        return self::trackEventJS('purchase', $params);
    }

    /**
     * Track button click
     */
    public static function trackClick($button_name, $page = null) {
        $params = [
            'button_name' => $button_name,
            'page' => $page ?? ($_SERVER['REQUEST_URI'] ?? '/')
        ];

        return self::trackEventJS('click', $params);
    }

    /**
     * Set user ID (untuk cross-device tracking)
     */
    public static function setUserId($user_id) {
        if (!self::ENABLED || empty($user_id)) return '';

        return <<<HTML
<script>
    gtag('set', 'user_id', '{$user_id}');
</script>
HTML;
    }

    /**
     * Set user properties
     */
    public static function setUserProperties($properties = []) {
        if (!self::ENABLED || empty($properties)) return '';

        $props_json = json_encode($properties);

        return <<<HTML
<script>
    gtag('set', 'user_properties', {$props_json});
</script>
HTML;
    }
}

/**
 * Helper function untuk render analytics script
 */
function render_analytics() {
    echo Analytics::renderScript();
}

/**
 * Track event dari PHP (via Measurement Protocol)
 * Untuk tracking server-side events
 */
function track_server_event($event_name, $params = [], $user_id = null) {
    if (!Analytics::ENABLED || Analytics::GA_MEASUREMENT_ID === 'G-XXXXXXXXXX') {
        return false;
    }

    // Measurement Protocol endpoint
    $url = 'https://www.google-analytics.com/mp/collect';
    $api_secret = 'YOUR_API_SECRET'; // Get from GA4 Admin

    $payload = [
        'client_id' => $_SESSION['ga_client_id'] ?? uniqid('', true),
        'events' => [
            [
                'name' => $event_name,
                'params' => $params
            ]
        ]
    ];

    if ($user_id) {
        $payload['user_id'] = $user_id;
    }

    // Send async request
    $ch = curl_init($url . '?measurement_id=' . Analytics::GA_MEASUREMENT_ID . '&api_secret=' . $api_secret);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));
    curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 5);

    $result = curl_exec($ch);
    curl_close($ch);

    return $result !== false;
}
