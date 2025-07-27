<?php
/**
 * Analytics Dashboard
 */

require_once 'includes/config.php';
require_once 'includes/db.php';
require_once 'includes/functions.php';

$page_title = 'Analytics';

// Get all trades for analytics
$db = db();
$stmt = $db->query("
    SELECT t.*, s.name as strategy_name, s.instrument
    FROM trades t
    JOIN strategies s ON t.strategy_id = s.id
    ORDER BY t.trade_timestamp ASC
");
$all_trades = $stmt->fetchAll();

// Calculate metrics
$total_trades = count($all_trades);
$taken_trades = array_filter($all_trades, function($t) { return $t['taken']; });
$missed_trades = array_filter($all_trades, function($t) { return !$t['taken']; });
$winning_trades = array_filter($taken_trades, function($t) { return $t['r_multiple'] > 0; });
$losing_trades = array_filter($taken_trades, function($t) { return $t['r_multiple'] < 0; });

$win_rate = count($taken_trades) > 0 ? (count($winning_trades) / count($taken_trades)) * 100 : 0;
$total_r = array_sum(array_column($taken_trades, 'r_multiple'));
$avg_win = count($winning_trades) > 0 ? array_sum(array_column($winning_trades, 'r_multiple')) / count($winning_trades) : 0;
$avg_loss = count($losing_trades) > 0 ? array_sum(array_column($losing_trades, 'r_multiple')) / count($losing_trades) : 0;
$expectancy = count($taken_trades) > 0 ? $total_r / count($taken_trades) : 0;

// Calculate performance by strategy
$strategy_stats = [];
foreach ($all_trades as $trade) {
    $strategy_id = $trade['strategy_id'];
    if (!isset($strategy_stats[$strategy_id])) {
        $strategy_stats[$strategy_id] = [
            'name' => $trade['strategy_name'],
            'total' => 0,
            'taken' => 0,
            'wins' => 0,
            'total_r' => 0
        ];
    }
    
    $strategy_stats[$strategy_id]['total']++;
    if ($trade['taken']) {
        $strategy_stats[$strategy_id]['taken']++;
        $strategy_stats[$strategy_id]['total_r'] += $trade['r_multiple'];
        if ($trade['r_multiple'] > 0) {
            $strategy_stats[$strategy_id]['wins']++;
        }
    }
}

// Calculate cumulative R for equity curve
$equity_curve = [];
$cumulative_r = 0;
foreach ($taken_trades as $trade) {
    $cumulative_r += $trade['r_multiple'];
    $equity_curve[] = [
        'date' => $trade['trade_timestamp'],
        'cumulative_r' => $cumulative_r,
        'r_multiple' => $trade['r_multiple']
    ];
}

include 'templates/header.php';
?>

<div class="px-4 py-6">
    <div class="max-w-7xl mx-auto">
        <!-- Page Header -->
        <div class="bg-white shadow rounded-lg p-6 mb-6">
            <div class="flex items-center">
                <div class="flex-shrink-0 bg-indigo-100 rounded-lg p-3">
                    <svg class="h-8 w-8 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path>
                    </svg>
                </div>
                <div class="ml-4">
                    <h1 class="text-2xl font-bold text-gray-900">Trading Analytics</h1>
                    <p class="text-gray-600">Performance metrics and insights</p>
                </div>
            </div>
        </div>
        
        <!-- Key Metrics Grid -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-6">
            <!-- Total Trades -->
            <div class="bg-white shadow rounded-lg p-6">
                <div class="flex items-center">
                    <div class="flex-shrink-0 bg-blue-100 rounded-lg p-3">
                        <svg class="h-6 w-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path>
                        </svg>
                    </div>
                    <div class="ml-4">
                        <p class="text-sm font-medium text-gray-500">Total Trades</p>
                        <p class="text-2xl font-semibold text-gray-900"><?php echo $total_trades; ?></p>
                        <p class="text-sm text-gray-500">
                            <?php echo count($taken_trades); ?> taken / <?php echo count($missed_trades); ?> missed
                        </p>
                    </div>
                </div>
            </div>
            
            <!-- Win Rate -->
            <div class="bg-white shadow rounded-lg p-6">
                <div class="flex items-center">
                    <div class="flex-shrink-0 bg-green-100 rounded-lg p-3">
                        <svg class="h-6 w-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                    </div>
                    <div class="ml-4">
                        <p class="text-sm font-medium text-gray-500">Win Rate</p>
                        <p class="text-2xl font-semibold text-gray-900"><?php echo number_format($win_rate, 1); ?>%</p>
                        <p class="text-sm text-gray-500">
                            <?php echo count($winning_trades); ?>W / <?php echo count($losing_trades); ?>L
                        </p>
                    </div>
                </div>
            </div>
            
            <!-- Total R -->
            <div class="bg-white shadow rounded-lg p-6">
                <div class="flex items-center">
                    <div class="flex-shrink-0 <?php echo $total_r >= 0 ? 'bg-green-100' : 'bg-red-100'; ?> rounded-lg p-3">
                        <svg class="h-6 w-6 <?php echo $total_r >= 0 ? 'text-green-600' : 'text-red-600'; ?>" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                    </div>
                    <div class="ml-4">
                        <p class="text-sm font-medium text-gray-500">Total R</p>
                        <p class="text-2xl font-semibold <?php echo $total_r >= 0 ? 'text-green-600' : 'text-red-600'; ?>">
                            <?php echo number_format($total_r, 2); ?>R
                        </p>
                        <p class="text-sm text-gray-500">
                            Cumulative return
                        </p>
                    </div>
                </div>
            </div>
            
            <!-- Expectancy -->
            <div class="bg-white shadow rounded-lg p-6">
                <div class="flex items-center">
                    <div class="flex-shrink-0 bg-purple-100 rounded-lg p-3">
                        <svg class="h-6 w-6 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 12l3-3 3 3 4-4M8 21l4-4 4 4M3 4h18M4 4h16v12a1 1 0 01-1 1H5a1 1 0 01-1-1V4z"></path>
                        </svg>
                    </div>
                    <div class="ml-4">
                        <p class="text-sm font-medium text-gray-500">Expectancy</p>
                        <p class="text-2xl font-semibold <?php echo $expectancy >= 0 ? 'text-green-600' : 'text-red-600'; ?>">
                            <?php echo number_format($expectancy, 2); ?>R
                        </p>
                        <p class="text-sm text-gray-500">
                            Per trade average
                        </p>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Additional Stats -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">
            <!-- Win/Loss Analysis -->
            <div class="bg-white shadow rounded-lg p-6">
                <h2 class="text-lg font-medium text-gray-900 mb-4">Win/Loss Analysis</h2>
                <div class="space-y-3">
                    <div class="flex justify-between items-center">
                        <span class="text-sm text-gray-600">Average Win</span>
                        <span class="text-sm font-medium text-green-600">+<?php echo number_format($avg_win, 2); ?>R</span>
                    </div>
                    <div class="flex justify-between items-center">
                        <span class="text-sm text-gray-600">Average Loss</span>
                        <span class="text-sm font-medium text-red-600"><?php echo number_format($avg_loss, 2); ?>R</span>
                    </div>
                    <div class="flex justify-between items-center">
                        <span class="text-sm text-gray-600">Win/Loss Ratio</span>
                        <span class="text-sm font-medium text-gray-900">
                            <?php echo $avg_loss != 0 ? number_format(abs($avg_win / $avg_loss), 2) : 'N/A'; ?>
                        </span>
                    </div>
                    <div class="flex justify-between items-center">
                        <span class="text-sm text-gray-600">Largest Win</span>
                        <span class="text-sm font-medium text-green-600">
                            <?php 
                            $largest_win = count($winning_trades) > 0 ? max(array_column($winning_trades, 'r_multiple')) : 0;
                            echo '+' . number_format($largest_win, 2) . 'R';
                            ?>
                        </span>
                    </div>
                    <div class="flex justify-between items-center">
                        <span class="text-sm text-gray-600">Largest Loss</span>
                        <span class="text-sm font-medium text-red-600">
                            <?php 
                            $largest_loss = count($losing_trades) > 0 ? min(array_column($losing_trades, 'r_multiple')) : 0;
                            echo number_format($largest_loss, 2) . 'R';
                            ?>
                        </span>
                    </div>
                </div>
            </div>
            
            <!-- Performance by Strategy -->
            <div class="bg-white shadow rounded-lg p-6">
                <h2 class="text-lg font-medium text-gray-900 mb-4">Performance by Strategy</h2>
                <div class="space-y-3">
                    <?php foreach ($strategy_stats as $strategy_id => $stats): ?>
                        <?php 
                        $strategy_win_rate = $stats['taken'] > 0 ? ($stats['wins'] / $stats['taken']) * 100 : 0;
                        ?>
                        <div class="border-b pb-3 last:border-0">
                            <div class="flex justify-between items-start">
                                <div>
                                    <p class="text-sm font-medium text-gray-900"><?php echo htmlspecialchars($stats['name']); ?></p>
                                    <p class="text-xs text-gray-500">
                                        <?php echo $stats['taken']; ?> taken / <?php echo $stats['total']; ?> total
                                    </p>
                                </div>
                                <div class="text-right">
                                    <p class="text-sm font-medium <?php echo $stats['total_r'] >= 0 ? 'text-green-600' : 'text-red-600'; ?>">
                                        <?php echo number_format($stats['total_r'], 2); ?>R
                                    </p>
                                    <p class="text-xs text-gray-500">
                                        <?php echo number_format($strategy_win_rate, 0); ?>% win rate
                                    </p>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
        
        <!-- Equity Curve (if we have trades) -->
        <?php if (!empty($equity_curve)): ?>
            <div class="bg-white shadow rounded-lg p-6">
                <h2 class="text-lg font-medium text-gray-900 mb-4">Equity Curve</h2>
                <div class="overflow-x-auto">
                    <div class="min-w-full" style="height: 300px;">
                        <!-- Simple text representation - in a real app, you'd use a charting library -->
                        <div class="text-sm text-gray-500">
                            <p>Starting: 0R</p>
                            <p>Current: <?php echo number_format($cumulative_r, 2); ?>R</p>
                            <p class="mt-2">Trade progression:</p>
                            <div class="mt-2 space-y-1">
                                <?php foreach (array_slice($equity_curve, -10) as $point): ?>
                                    <div class="flex items-center text-xs">
                                        <span class="text-gray-600 w-32"><?php echo format_datetime($point['date'], 'M d, H:i'); ?></span>
                                        <span class="<?php echo $point['r_multiple'] >= 0 ? 'text-green-600' : 'text-red-600'; ?> w-20">
                                            <?php echo $point['r_multiple'] >= 0 ? '+' : ''; ?><?php echo number_format($point['r_multiple'], 2); ?>R
                                        </span>
                                        <span class="text-gray-900 font-medium">
                                            → <?php echo number_format($point['cumulative_r'], 2); ?>R
                                        </span>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        <?php endif; ?>
    </div>
</div>

<?php include 'templates/footer.php'; ?>