<?php
/**
 * ZYN Trade System - Dynamic Sitemap Generator
 *
 * CARA PAKAI:
 * 1. Akses langsung: yoursite.com/sitemap.php
 * 2. Atau setup .htaccess untuk redirect sitemap.xml ke sitemap.php
 *
 * CARA SETUP .htaccess:
 * RewriteRule ^sitemap\.xml$ sitemap.php [L]
 *
 * Sitemap ini otomatis generate berdasarkan halaman yang ada
 */

// Include config for SITE_URL constant
require_once __DIR__ . '/includes/config.php';

// Set header sebagai XML
header('Content-Type: application/xml; charset=utf-8');
header('X-Robots-Tag: noindex');

// Use SITE_URL from config, fallback to current host
$base_url = defined('SITE_URL') ? SITE_URL : '';

// If SITE_URL not defined, use current host
if (empty($base_url) && isset($_SERVER['HTTP_HOST'])) {
    $protocol = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on') ? 'https' : 'http';
    $base_url = $protocol . '://' . $_SERVER['HTTP_HOST'];
}

// Daftar halaman publik dengan prioritas dan frequency
$pages = [
    // Homepage - highest priority
    [
        'url' => '/',
        'priority' => '1.0',
        'changefreq' => 'daily',
        'lastmod' => date('Y-m-d')
    ],

    // Main pages - high priority
    [
        'url' => '/index.php',
        'priority' => '1.0',
        'changefreq' => 'daily',
        'lastmod' => date('Y-m-d')
    ],
    [
        'url' => '/pricing.php',
        'priority' => '0.9',
        'changefreq' => 'weekly',
        'lastmod' => date('Y-m-d')
    ],
    [
        'url' => '/strategies.php',
        'priority' => '0.9',
        'changefreq' => 'weekly',
        'lastmod' => date('Y-m-d')
    ],
    [
        'url' => '/calculator.php',
        'priority' => '0.8',
        'changefreq' => 'monthly',
        'lastmod' => date('Y-m-d')
    ],
    [
        'url' => '/faq.php',
        'priority' => '0.7',
        'changefreq' => 'monthly',
        'lastmod' => date('Y-m-d')
    ],

    // Auth pages - medium priority
    [
        'url' => '/register.php',
        'priority' => '0.8',
        'changefreq' => 'monthly',
        'lastmod' => date('Y-m-d')
    ],
    [
        'url' => '/login.php',
        'priority' => '0.6',
        'changefreq' => 'monthly',
        'lastmod' => date('Y-m-d')
    ],

    // Mobile & Download
    [
        'url' => '/mobile.php',
        'priority' => '0.7',
        'changefreq' => 'monthly',
        'lastmod' => date('Y-m-d')
    ],

    // Legal pages - low priority
    [
        'url' => '/terms.php',
        'priority' => '0.3',
        'changefreq' => 'yearly',
        'lastmod' => date('Y-m-d')
    ],
    [
        'url' => '/privacy.php',
        'priority' => '0.3',
        'changefreq' => 'yearly',
        'lastmod' => date('Y-m-d')
    ],
    [
        'url' => '/disclaimer.php',
        'priority' => '0.3',
        'changefreq' => 'yearly',
        'lastmod' => date('Y-m-d')
    ],
    [
        'url' => '/refund.php',
        'priority' => '0.3',
        'changefreq' => 'yearly',
        'lastmod' => date('Y-m-d')
    ],
];

// Generate XML
echo '<?xml version="1.0" encoding="UTF-8"?>' . PHP_EOL;
?>
<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9"
        xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
        xmlns:image="http://www.google.com/schemas/sitemap-image/1.1"
        xsi:schemaLocation="http://www.sitemaps.org/schemas/sitemap/0.9
        http://www.sitemaps.org/schemas/sitemap/0.9/sitemap.xsd">
<?php foreach ($pages as $page): ?>
    <url>
        <loc><?php echo htmlspecialchars($base_url . $page['url']); ?></loc>
        <lastmod><?php echo $page['lastmod']; ?></lastmod>
        <changefreq><?php echo $page['changefreq']; ?></changefreq>
        <priority><?php echo $page['priority']; ?></priority>
    </url>
<?php endforeach; ?>

    <!-- Multi-language URLs -->
<?php
$languages = ['id', 'en', 'ms', 'th', 'vi', 'zh', 'ja', 'ko'];
$main_pages = ['/', '/pricing.php', '/strategies.php', '/calculator.php', '/faq.php'];

foreach ($main_pages as $page):
    foreach ($languages as $lang):
?>
    <url>
        <loc><?php echo htmlspecialchars($base_url . $page . '?lang=' . $lang); ?></loc>
        <lastmod><?php echo date('Y-m-d'); ?></lastmod>
        <changefreq>weekly</changefreq>
        <priority>0.5</priority>
    </url>
<?php
    endforeach;
endforeach;
?>
</urlset>
<?php

// Optionally save to file
$sitemap_file = __DIR__ . '/sitemap.xml';
$sitemap_content = ob_get_contents();

// Uncomment line below to auto-save sitemap.xml file
// file_put_contents($sitemap_file, $sitemap_content);
