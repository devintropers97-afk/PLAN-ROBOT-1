<?php
/**
 * ZYN Trade System - Available Assets API
 * Returns list of tradeable assets/pairs
 */

header('Content-Type: application/json');

require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/functions.php';

// Validate Robot API Key
requireRobotApiKey();

// Available trading pairs
$assets = [
    [
        'id' => 'EUR/USD',
        'name' => 'EUR/USD',
        'type' => 'forex',
        'payout' => 82,
        'active' => true,
        'min_amount' => 1,
        'max_amount' => 10000
    ],
    [
        'id' => 'GBP/USD',
        'name' => 'GBP/USD',
        'type' => 'forex',
        'payout' => 82,
        'active' => true,
        'min_amount' => 1,
        'max_amount' => 10000
    ],
    [
        'id' => 'USD/JPY',
        'name' => 'USD/JPY',
        'type' => 'forex',
        'payout' => 80,
        'active' => true,
        'min_amount' => 1,
        'max_amount' => 10000
    ],
    [
        'id' => 'AUD/USD',
        'name' => 'AUD/USD',
        'type' => 'forex',
        'payout' => 80,
        'active' => true,
        'min_amount' => 1,
        'max_amount' => 10000
    ]
];

// Get allowed markets from settings
$allowedMarkets = defined('ALLOWED_MARKETS') ? explode(',', ALLOWED_MARKETS) : ['EUR/USD', 'GBP/USD'];

// Filter assets based on allowed markets
$filteredAssets = array_filter($assets, function($asset) use ($allowedMarkets) {
    return in_array($asset['id'], $allowedMarkets);
});

echo json_encode([
    'success' => true,
    'assets' => array_values($filteredAssets),
    'timestamp' => date('c')
]);
