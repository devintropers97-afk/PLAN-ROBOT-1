<?php
/**
 * ZYN Trade Robot - Contoh Penggunaan
 *
 * File ini menunjukkan cara mengintegrasikan Robot dengan website PHP
 */

require_once 'ZynRobotClient.php';

// ========================================
// KONFIGURASI
// ========================================

// URL Robot API (sesuaikan dengan server robot)
$robotUrl = 'http://localhost:3001';

// API Key (set di .env robot: API_KEY=your-secret-key)
$apiKey = 'zyn-robot-secret-key';

// Buat instance client
$robot = new ZynRobotClient($robotUrl, $apiKey);

// ========================================
// CONTOH 1: Health Check
// ========================================

echo "=== Health Check ===\n";
try {
    $health = $robot->health();
    print_r($health);
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}

// ========================================
// CONTOH 2: Test Login
// ========================================

echo "\n=== Test Login ===\n";
try {
    $result = $robot->testLogin(
        'trader@email.com',
        'password123',
        true // isDemo
    );
    print_r($result);
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}

// ========================================
// CONTOH 3: Execute Trade
// ========================================

echo "\n=== Execute Trade ===\n";
try {
    // Submit trade ke queue
    $result = $robot->executeTrade([
        'email' => 'trader@email.com',
        'password' => 'password123',
        'direction' => 'CALL',
        'amount' => 10,
        'asset' => 'EUR/USD',
        'duration' => 1,
        'isDemo' => true
    ]);

    echo "Trade submitted!\n";
    echo "Job ID: " . $result['jobId'] . "\n";
    echo "Position in queue: " . $result['position'] . "\n";
    echo "Estimated wait: " . $result['estimatedWait'] . " seconds\n";

    // Wait for completion (optional)
    echo "\nWaiting for trade to complete...\n";
    $finalResult = $robot->waitForTrade($result['jobId'], 120);
    print_r($finalResult);

} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}

// ========================================
// CONTOH 4: Get Balance
// ========================================

echo "\n=== Get Balance ===\n";
try {
    $result = $robot->getBalance(
        'trader@email.com',
        'password123',
        true // isDemo
    );

    // Wait for result
    if (isset($result['jobId'])) {
        $balance = $robot->waitForTrade($result['jobId'], 60);
        print_r($balance);
    }
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}

// ========================================
// CONTOH 5: Queue Status
// ========================================

echo "\n=== Queue Status ===\n";
try {
    $status = $robot->getQueueStatus();
    print_r($status);
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}

// ========================================
// CONTOH 6: Integrasi dengan Database
// ========================================

echo "\n=== Database Integration Example ===\n";

/**
 * Contoh integrasi dengan database untuk multi-trader
 *
 * Struktur tabel traders:
 * CREATE TABLE traders (
 *     id INT AUTO_INCREMENT PRIMARY KEY,
 *     email VARCHAR(255) NOT NULL,
 *     password_encrypted TEXT NOT NULL,
 *     is_demo TINYINT(1) DEFAULT 1,
 *     balance DECIMAL(10,2) DEFAULT 0,
 *     auto_trade TINYINT(1) DEFAULT 0,
 *     created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
 * );
 *
 * CREATE TABLE trade_history (
 *     id INT AUTO_INCREMENT PRIMARY KEY,
 *     trader_id INT NOT NULL,
 *     job_id VARCHAR(50),
 *     direction ENUM('CALL', 'PUT'),
 *     amount DECIMAL(10,2),
 *     result ENUM('pending', 'win', 'lose'),
 *     profit DECIMAL(10,2),
 *     created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
 * );
 */

// Simulasi database (ganti dengan PDO/mysqli)
$traders = [
    [
        'id' => 1,
        'email' => 'trader1@email.com',
        'password' => encryptPassword('password1'),
        'is_demo' => true,
        'auto_trade' => true
    ],
    [
        'id' => 2,
        'email' => 'trader2@email.com',
        'password' => encryptPassword('password2'),
        'is_demo' => true,
        'auto_trade' => true
    ]
];

// Process auto-trade untuk semua trader
foreach ($traders as $trader) {
    if (!$trader['auto_trade']) continue;

    try {
        $password = decryptPassword($trader['password']);

        // Get signal dari strategy (contoh sederhana)
        $signal = getTradeSignal(); // Implement your strategy

        if ($signal['execute']) {
            $result = $robot->executeTrade([
                'email' => $trader['email'],
                'password' => $password,
                'direction' => $signal['direction'],
                'amount' => 10,
                'isDemo' => $trader['is_demo']
            ]);

            echo "Trade submitted for {$trader['email']}: {$result['jobId']}\n";

            // Save to database
            // INSERT INTO trade_history (trader_id, job_id, direction, amount, result)
            // VALUES ({$trader['id']}, '{$result['jobId']}', '{$signal['direction']}', 10, 'pending')
        }
    } catch (Exception $e) {
        echo "Error for {$trader['email']}: {$e->getMessage()}\n";
    }
}

/**
 * Contoh fungsi generate signal (implement sesuai strategy)
 */
function getTradeSignal()
{
    // Implement your trading strategy here
    // Contoh sederhana: random signal
    $directions = ['CALL', 'PUT'];
    return [
        'execute' => true,
        'direction' => $directions[array_rand($directions)],
        'confidence' => rand(60, 90)
    ];
}

echo "\n=== Done ===\n";
