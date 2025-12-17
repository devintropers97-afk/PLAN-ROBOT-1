<?php
/**
 * ZYN Trade System - PDF Export
 *
 * CARA PAKAI:
 * 1. Include file ini
 * 2. Panggil: PDFExport::generateTradeReport($user_id, $start_date, $end_date);
 *
 * Library yang digunakan: TCPDF (tidak perlu install tambahan)
 * Kita pakai HTML to PDF approach yang simple
 */

class PDFExport {
    private static $company_name = 'ZYN Trade System';
    private static $company_tagline = 'Robot Trading Otomatis';

    /**
     * Generate Trade Report PDF
     */
    public static function generateTradeReport($user_id, $start_date = null, $end_date = null, $download = true) {
        // Default date range (last 30 days)
        if (!$start_date) $start_date = date('Y-m-d', strtotime('-30 days'));
        if (!$end_date) $end_date = date('Y-m-d');

        // Get user data
        $user = self::getUserData($user_id);
        if (!$user) return false;

        // Get trade data
        $trades = self::getTradeData($user_id, $start_date, $end_date);
        $stats = self::calculateStats($trades);

        // Generate HTML content
        $html = self::generateReportHTML($user, $trades, $stats, $start_date, $end_date);

        // Convert to PDF
        return self::htmlToPDF($html, "trade_report_{$user_id}_{$start_date}_{$end_date}.pdf", $download);
    }

    /**
     * Generate Commission Report PDF
     */
    public static function generateCommissionReport($user_id, $download = true) {
        $user = self::getUserData($user_id);
        if (!$user) return false;

        $commissions = self::getCommissionData($user_id);
        $referrals = self::getReferralData($user_id);

        $html = self::generateCommissionHTML($user, $commissions, $referrals);

        return self::htmlToPDF($html, "commission_report_{$user_id}.pdf", $download);
    }

    /**
     * Get user data from database
     */
    private static function getUserData($user_id) {
        global $pdo;

        if (!isset($pdo)) {
            // Fallback to direct connection
            try {
                $pdo = new PDO(
                    "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8mb4",
                    DB_USER, DB_PASS
                );
            } catch (PDOException $e) {
                return null;
            }
        }

        $stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
        $stmt->execute([$user_id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    /**
     * Get trade data
     */
    private static function getTradeData($user_id, $start_date, $end_date) {
        global $pdo;

        $stmt = $pdo->prepare("
            SELECT * FROM trades
            WHERE user_id = ?
            AND DATE(created_at) BETWEEN ? AND ?
            ORDER BY created_at DESC
        ");
        $stmt->execute([$user_id, $start_date, $end_date]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Calculate trading statistics
     */
    private static function calculateStats($trades) {
        $stats = [
            'total_trades' => count($trades),
            'wins' => 0,
            'losses' => 0,
            'win_rate' => 0,
            'total_profit' => 0,
            'total_loss' => 0,
            'net_profit' => 0,
            'best_trade' => 0,
            'worst_trade' => 0,
            'avg_profit' => 0,
            'by_strategy' => [],
            'by_pair' => [],
            'by_day' => []
        ];

        foreach ($trades as $trade) {
            $profit = $trade['profit'] ?? 0;

            if ($trade['result'] === 'win') {
                $stats['wins']++;
                $stats['total_profit'] += $profit;
                if ($profit > $stats['best_trade']) $stats['best_trade'] = $profit;
            } else {
                $stats['losses']++;
                $stats['total_loss'] += abs($profit);
                if ($profit < $stats['worst_trade']) $stats['worst_trade'] = $profit;
            }

            // Group by strategy
            $strategy = $trade['strategy'] ?? 'Unknown';
            if (!isset($stats['by_strategy'][$strategy])) {
                $stats['by_strategy'][$strategy] = ['wins' => 0, 'losses' => 0, 'profit' => 0];
            }
            $stats['by_strategy'][$strategy]['profit'] += $profit;
            if ($trade['result'] === 'win') {
                $stats['by_strategy'][$strategy]['wins']++;
            } else {
                $stats['by_strategy'][$strategy]['losses']++;
            }

            // Group by pair
            $pair = $trade['pair'] ?? 'Unknown';
            if (!isset($stats['by_pair'][$pair])) {
                $stats['by_pair'][$pair] = ['wins' => 0, 'losses' => 0, 'profit' => 0];
            }
            $stats['by_pair'][$pair]['profit'] += $profit;

            // Group by day
            $day = date('Y-m-d', strtotime($trade['created_at']));
            if (!isset($stats['by_day'][$day])) {
                $stats['by_day'][$day] = ['trades' => 0, 'profit' => 0];
            }
            $stats['by_day'][$day]['trades']++;
            $stats['by_day'][$day]['profit'] += $profit;
        }

        $stats['net_profit'] = $stats['total_profit'] - $stats['total_loss'];
        $stats['win_rate'] = $stats['total_trades'] > 0
            ? round(($stats['wins'] / $stats['total_trades']) * 100, 1)
            : 0;
        $stats['avg_profit'] = $stats['wins'] > 0
            ? round($stats['total_profit'] / $stats['wins'], 2)
            : 0;

        return $stats;
    }

    /**
     * Generate Report HTML
     */
    private static function generateReportHTML($user, $trades, $stats, $start_date, $end_date) {
        $generated_at = date('d M Y H:i');
        $period = date('d M Y', strtotime($start_date)) . ' - ' . date('d M Y', strtotime($end_date));

        $profit_color = $stats['net_profit'] >= 0 ? '#10b981' : '#ef4444';
        $profit_sign = $stats['net_profit'] >= 0 ? '+' : '';

        $html = <<<HTML
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'Helvetica', 'Arial', sans-serif; font-size: 12px; color: #333; line-height: 1.5; }

        .header { background: linear-gradient(135deg, #0a0a0f 0%, #1a1a2e 100%); color: #fff; padding: 30px; }
        .header h1 { font-size: 28px; margin-bottom: 5px; color: #00d4ff; }
        .header p { color: #94a3b8; font-size: 14px; }

        .report-info { display: flex; justify-content: space-between; margin-top: 20px; padding-top: 20px; border-top: 1px solid rgba(255,255,255,0.1); }
        .report-info div { }
        .report-info label { display: block; color: #64748b; font-size: 10px; text-transform: uppercase; }
        .report-info span { font-size: 14px; font-weight: 600; }

        .content { padding: 30px; }

        .summary-cards { display: flex; gap: 15px; margin-bottom: 30px; }
        .summary-card { flex: 1; background: #f8fafc; border-radius: 12px; padding: 20px; text-align: center; border: 1px solid #e2e8f0; }
        .summary-card .value { font-size: 28px; font-weight: 700; margin-bottom: 5px; }
        .summary-card .label { font-size: 11px; color: #64748b; text-transform: uppercase; }
        .summary-card.profit .value { color: {$profit_color}; }
        .summary-card.wins .value { color: #10b981; }
        .summary-card.rate .value { color: #00d4ff; }

        .section { margin-bottom: 30px; }
        .section-title { font-size: 16px; font-weight: 700; margin-bottom: 15px; padding-bottom: 10px; border-bottom: 2px solid #00d4ff; color: #0a0a0f; }

        table { width: 100%; border-collapse: collapse; font-size: 11px; }
        th { background: #0a0a0f; color: #fff; padding: 12px 10px; text-align: left; font-weight: 600; }
        td { padding: 10px; border-bottom: 1px solid #e2e8f0; }
        tr:nth-child(even) { background: #f8fafc; }
        .win { color: #10b981; font-weight: 600; }
        .loss { color: #ef4444; font-weight: 600; }

        .strategy-grid { display: flex; flex-wrap: wrap; gap: 15px; }
        .strategy-item { flex: 1; min-width: 200px; background: #f8fafc; border-radius: 8px; padding: 15px; border-left: 4px solid #00d4ff; }
        .strategy-item h4 { font-size: 14px; margin-bottom: 10px; color: #0a0a0f; }
        .strategy-item .stat { display: flex; justify-content: space-between; font-size: 11px; margin-bottom: 5px; }
        .strategy-item .stat label { color: #64748b; }

        .footer { background: #f8fafc; padding: 20px 30px; text-align: center; font-size: 10px; color: #64748b; border-top: 1px solid #e2e8f0; }

        .chart-placeholder { background: #f1f5f9; border-radius: 8px; padding: 40px; text-align: center; color: #64748b; margin-bottom: 20px; }

        @media print {
            body { print-color-adjust: exact; -webkit-print-color-adjust: exact; }
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>ZYN Trade System</h1>
        <p>Laporan Trading Otomatis</p>

        <div class="report-info">
            <div>
                <label>Nama User</label>
                <span>{$user['name']}</span>
            </div>
            <div>
                <label>Paket</label>
                <span>{$user['package']}</span>
            </div>
            <div>
                <label>Periode</label>
                <span>{$period}</span>
            </div>
            <div>
                <label>Generated</label>
                <span>{$generated_at}</span>
            </div>
        </div>
    </div>

    <div class="content">
        <!-- Summary Cards -->
        <div class="summary-cards">
            <div class="summary-card profit">
                <div class="value">{$profit_sign}\${$stats['net_profit']}</div>
                <div class="label">Net Profit</div>
            </div>
            <div class="summary-card">
                <div class="value">{$stats['total_trades']}</div>
                <div class="label">Total Trades</div>
            </div>
            <div class="summary-card wins">
                <div class="value">{$stats['wins']}</div>
                <div class="label">Wins</div>
            </div>
            <div class="summary-card rate">
                <div class="value">{$stats['win_rate']}%</div>
                <div class="label">Win Rate</div>
            </div>
        </div>

        <!-- Performance by Strategy -->
        <div class="section">
            <h3 class="section-title">Performa per Strategi</h3>
            <div class="strategy-grid">
HTML;

        foreach ($stats['by_strategy'] as $strategy => $data) {
            $total = $data['wins'] + $data['losses'];
            $wr = $total > 0 ? round(($data['wins'] / $total) * 100, 1) : 0;
            $profitClass = $data['profit'] >= 0 ? 'win' : 'loss';
            $profitSign = $data['profit'] >= 0 ? '+' : '';

            $html .= <<<HTML
                <div class="strategy-item">
                    <h4>{$strategy}</h4>
                    <div class="stat"><label>Trades</label> <span>{$total}</span></div>
                    <div class="stat"><label>Win Rate</label> <span>{$wr}%</span></div>
                    <div class="stat"><label>Profit</label> <span class="{$profitClass}">{$profitSign}\${$data['profit']}</span></div>
                </div>
HTML;
        }

        $html .= <<<HTML
            </div>
        </div>

        <!-- Trade History -->
        <div class="section">
            <h3 class="section-title">Riwayat Trading (Last 50)</h3>
            <table>
                <thead>
                    <tr>
                        <th>Tanggal</th>
                        <th>Pair</th>
                        <th>Strategi</th>
                        <th>Direction</th>
                        <th>Amount</th>
                        <th>Result</th>
                        <th>Profit</th>
                    </tr>
                </thead>
                <tbody>
HTML;

        $count = 0;
        foreach ($trades as $trade) {
            if ($count >= 50) break;

            $date = date('d M Y H:i', strtotime($trade['created_at']));
            $resultClass = $trade['result'] === 'win' ? 'win' : 'loss';
            $resultText = $trade['result'] === 'win' ? 'WIN' : 'LOSS';
            $profitSign = $trade['profit'] >= 0 ? '+' : '';

            $html .= <<<HTML
                    <tr>
                        <td>{$date}</td>
                        <td>{$trade['pair']}</td>
                        <td>{$trade['strategy']}</td>
                        <td>{$trade['direction']}</td>
                        <td>\${$trade['amount']}</td>
                        <td class="{$resultClass}">{$resultText}</td>
                        <td class="{$resultClass}">{$profitSign}\${$trade['profit']}</td>
                    </tr>
HTML;
            $count++;
        }

        $html .= <<<HTML
                </tbody>
            </table>
        </div>

        <!-- Daily Summary -->
        <div class="section">
            <h3 class="section-title">Ringkasan Harian</h3>
            <table>
                <thead>
                    <tr>
                        <th>Tanggal</th>
                        <th>Jumlah Trade</th>
                        <th>Profit/Loss</th>
                    </tr>
                </thead>
                <tbody>
HTML;

        foreach ($stats['by_day'] as $day => $data) {
            $dayFormatted = date('d M Y', strtotime($day));
            $profitClass = $data['profit'] >= 0 ? 'win' : 'loss';
            $profitSign = $data['profit'] >= 0 ? '+' : '';

            $html .= <<<HTML
                    <tr>
                        <td>{$dayFormatted}</td>
                        <td>{$data['trades']}</td>
                        <td class="{$profitClass}">{$profitSign}\${$data['profit']}</td>
                    </tr>
HTML;
        }

        $html .= <<<HTML
                </tbody>
            </table>
        </div>
    </div>

    <div class="footer">
        <p>Laporan ini di-generate otomatis oleh ZYN Trade System</p>
        <p>© 2024 ZYN Trade System. All rights reserved.</p>
        <p style="margin-top: 10px; font-size: 9px;">
            DISCLAIMER: Trading mengandung risiko tinggi. Performa masa lalu tidak menjamin hasil di masa depan.
        </p>
    </div>
</body>
</html>
HTML;

        return $html;
    }

    /**
     * Convert HTML to PDF using browser print
     * Simple approach without external library
     */
    private static function htmlToPDF($html, $filename, $download = true) {
        if ($download) {
            // Set headers for download
            header('Content-Type: text/html; charset=utf-8');
            header('Content-Disposition: inline; filename="' . $filename . '"');

            // Add print script
            $html = str_replace('</body>', '<script>window.onload = function() { window.print(); }</script></body>', $html);
        }

        return $html;
    }

    /**
     * Get commission data
     */
    private static function getCommissionData($user_id) {
        global $pdo;

        $stmt = $pdo->prepare("
            SELECT c.*, u.name as referred_name
            FROM commissions c
            LEFT JOIN users u ON c.referred_id = u.id
            WHERE c.referrer_id = ?
            ORDER BY c.created_at DESC
        ");
        $stmt->execute([$user_id]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Get referral data
     */
    private static function getReferralData($user_id) {
        global $pdo;

        $stmt = $pdo->prepare("
            SELECT r.*, u.name as referred_name, u.email as referred_email
            FROM referrals r
            LEFT JOIN users u ON r.referred_id = u.id
            WHERE r.referrer_id = ?
            ORDER BY r.created_at DESC
        ");
        $stmt->execute([$user_id]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Generate Commission Report HTML
     */
    private static function generateCommissionHTML($user, $commissions, $referrals) {
        // Similar structure to trade report
        // ... implement as needed
        return '<html><body><h1>Commission Report</h1></body></html>';
    }
}

/**
 * Helper function untuk export
 */
function exportTradePDF($user_id, $start_date = null, $end_date = null) {
    return PDFExport::generateTradeReport($user_id, $start_date, $end_date);
}
