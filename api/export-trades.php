<?php
/**
 * ZYN Trade System - Export Trades API
 * Exports trade history in CSV or PDF format
 */

require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/functions.php';

// Check if user is logged in
if (!isLoggedIn()) {
    header('Location: ../login.php');
    exit;
}

$userId = $_SESSION['user_id'];
$format = isset($_GET['format']) ? cleanInput($_GET['format']) : 'csv';
$period = isset($_GET['period']) ? (int)$_GET['period'] : 30;

// Limit period to max 365 days
$period = min($period, 365);

try {
    global $pdo;

    // Get user info
    $stmt = $pdo->prepare("SELECT fullname, email FROM users WHERE id = ?");
    $stmt->execute([$userId]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    // Get trades
    $dateLimit = date('Y-m-d', strtotime("-{$period} days"));
    $stmt = $pdo->prepare("
        SELECT strategy, asset, amount, direction, result, profit_loss, created_at
        FROM trades
        WHERE user_id = ? AND DATE(created_at) >= ?
        ORDER BY created_at DESC
    ");
    $stmt->execute([$userId, $dateLimit]);
    $trades = $stmt->fetchAll(PDO::FETCH_ASSOC);

    if ($format === 'csv') {
        // Export as CSV
        $filename = 'ZYN_Trades_' . date('Y-m-d') . '.csv';

        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename="' . $filename . '"');

        $output = fopen('php://output', 'w');

        // UTF-8 BOM for Excel
        fprintf($output, chr(0xEF).chr(0xBB).chr(0xBF));

        // Header row
        fputcsv($output, ['Date/Time', 'Strategy', 'Asset', 'Amount', 'Direction', 'Result', 'P&L']);

        // Data rows
        foreach ($trades as $trade) {
            fputcsv($output, [
                date('Y-m-d H:i:s', strtotime($trade['created_at'])),
                $trade['strategy'],
                $trade['asset'],
                '$' . number_format($trade['amount'], 2),
                strtoupper($trade['direction']),
                strtoupper($trade['result']),
                ($trade['profit_loss'] >= 0 ? '+' : '') . '$' . number_format($trade['profit_loss'], 2)
            ]);
        }

        // Summary
        fputcsv($output, []);
        fputcsv($output, ['Summary']);
        fputcsv($output, ['Total Trades', count($trades)]);

        $wins = array_filter($trades, fn($t) => $t['result'] === 'win');
        $totalPnl = array_sum(array_column($trades, 'profit_loss'));

        fputcsv($output, ['Win Rate', count($trades) > 0 ? round(count($wins) / count($trades) * 100, 1) . '%' : '0%']);
        fputcsv($output, ['Total P&L', ($totalPnl >= 0 ? '+' : '') . '$' . number_format($totalPnl, 2)]);
        fputcsv($output, ['Generated', date('Y-m-d H:i:s') . ' WIB']);

        fclose($output);

    } else {
        // For PDF, redirect to export page
        header('Location: ../export.php?type=pdf&period=' . $period);
    }

} catch (PDOException $e) {
    error_log("Export trades error: " . $e->getMessage());
    header('Content-Type: application/json');
    echo json_encode([
        'success' => false,
        'message' => 'Failed to export trades'
    ]);
}
