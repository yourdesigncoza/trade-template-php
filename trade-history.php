<?php
/**
 * Trade History Page
 */

require_once 'includes/config.php';
require_once 'includes/db.php';
require_once 'includes/functions.php';

$page_title = 'Trade History';

// Get filter parameters
$strategy_filter = $_GET['strategy'] ?? '';
$taken_filter = $_GET['taken'] ?? '';

// Build query
$query = "
    SELECT t.*, s.name as strategy_name, s.instrument
    FROM trades t
    JOIN strategies s ON t.strategy_id = s.id
    WHERE 1=1
";

$params = [];

if (!empty($strategy_filter)) {
    $query .= " AND t.strategy_id = ?";
    $params[] = $strategy_filter;
}

if ($taken_filter !== '') {
    $query .= " AND t.taken = ?";
    $params[] = $taken_filter;
}

$query .= " ORDER BY t.trade_timestamp DESC";

$db = db();
$stmt = $db->prepare($query);
$stmt->execute($params);
$trades = $stmt->fetchAll();

// Get strategies for filter dropdown
$strategies = get_all_strategies();

include 'templates/header.php';
?>

<div class="px-4 py-6">
    <div class="max-w-7xl mx-auto">
        <!-- Page Header -->
        <div class="bg-white shadow rounded-lg p-6 mb-6">
            <div class="flex items-center justify-between">
                <div class="flex items-center">
                    <div class="flex-shrink-0 bg-purple-100 rounded-lg p-3">
                        <svg class="h-8 w-8 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                    </div>
                    <div class="ml-4">
                        <h1 class="text-2xl font-bold text-gray-900">Trade History</h1>
                        <p class="text-gray-600">View and analyze your past trades</p>
                    </div>
                </div>
                
                <!-- Filters -->
                <form method="GET" class="flex items-center space-x-3">
                    <select name="strategy" class="px-3 py-2 border border-gray-300 rounded-md text-sm">
                        <option value="">All Strategies</option>
                        <?php foreach ($strategies as $strategy): ?>
                            <option value="<?php echo $strategy['id']; ?>" <?php echo $strategy_filter == $strategy['id'] ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($strategy['name']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                    
                    <select name="taken" class="px-3 py-2 border border-gray-300 rounded-md text-sm">
                        <option value="">All Trades</option>
                        <option value="1" <?php echo $taken_filter === '1' ? 'selected' : ''; ?>>Taken Only</option>
                        <option value="0" <?php echo $taken_filter === '0' ? 'selected' : ''; ?>>Missed Only</option>
                    </select>
                    
                    <button type="submit" class="px-4 py-2 bg-gray-100 hover:bg-gray-200 rounded-md text-sm">
                        Filter
                    </button>
                </form>
            </div>
        </div>
        
        <!-- Trades Table -->
        <div class="bg-white shadow rounded-lg overflow-hidden">
            <?php if (empty($trades)): ?>
                <div class="p-6 text-center text-gray-500">
                    <svg class="mx-auto h-12 w-12 text-gray-400 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path>
                    </svg>
                    <p>No trades found.</p>
                    <p class="mt-2 text-sm">Start logging trades to see them here.</p>
                </div>
            <?php else: ?>
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Date/Time</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Strategy</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Instrument</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Direction</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Session</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">R-Multiple</th>
                                <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            <?php foreach ($trades as $trade): ?>
                                <tr>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                        <?php echo format_datetime($trade['trade_timestamp']); ?>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                        <?php echo htmlspecialchars($trade['strategy_name']); ?>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                        <?php echo htmlspecialchars($trade['instrument']); ?>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full <?php echo $trade['direction'] == 'Long' ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800'; ?>">
                                            <?php echo $trade['direction']; ?>
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                        <?php echo htmlspecialchars($trade['session']); ?>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <?php if ($trade['taken']): ?>
                                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-blue-100 text-blue-800">
                                                Taken
                                            </span>
                                        <?php else: ?>
                                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-gray-100 text-gray-800">
                                                Missed
                                            </span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <?php if ($trade['taken'] && $trade['r_multiple'] !== null): ?>
                                            <span class="text-sm font-medium <?php echo $trade['r_multiple'] >= 0 ? 'text-green-600' : 'text-red-600'; ?>">
                                                <?php echo number_format($trade['r_multiple'], 2); ?>R
                                            </span>
                                        <?php else: ?>
                                            <span class="text-sm text-gray-400">--</span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                        <a href="trade-details.php?id=<?php echo $trade['id']; ?>" class="text-blue-600 hover:text-blue-900">View</a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
                
                <!-- Summary Stats -->
                <?php
                $total_trades = count($trades);
                $taken_trades = array_filter($trades, function($t) { return $t['taken']; });
                $winning_trades = array_filter($taken_trades, function($t) { return $t['r_multiple'] > 0; });
                $win_rate = count($taken_trades) > 0 ? (count($winning_trades) / count($taken_trades)) * 100 : 0;
                $avg_r = count($taken_trades) > 0 ? array_sum(array_column($taken_trades, 'r_multiple')) / count($taken_trades) : 0;
                ?>
                
                <div class="bg-gray-50 px-6 py-4">
                    <div class="flex justify-between text-sm">
                        <div>
                            <span class="text-gray-500">Total Trades:</span>
                            <span class="font-medium text-gray-900 ml-2"><?php echo $total_trades; ?></span>
                        </div>
                        <div>
                            <span class="text-gray-500">Taken:</span>
                            <span class="font-medium text-gray-900 ml-2"><?php echo count($taken_trades); ?></span>
                        </div>
                        <div>
                            <span class="text-gray-500">Win Rate:</span>
                            <span class="font-medium text-gray-900 ml-2"><?php echo number_format($win_rate, 1); ?>%</span>
                        </div>
                        <div>
                            <span class="text-gray-500">Avg R:</span>
                            <span class="font-medium <?php echo $avg_r >= 0 ? 'text-green-600' : 'text-red-600'; ?> ml-2">
                                <?php echo number_format($avg_r, 2); ?>R
                            </span>
                        </div>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php include 'templates/footer.php'; ?>