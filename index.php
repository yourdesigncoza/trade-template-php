<?php
/**
 * Trade Log Form - Main entry point
 */

require_once 'includes/config.php';
require_once 'includes/db.php';
require_once 'includes/csrf.php';
require_once 'includes/functions.php';
require_once 'includes/validation.php';

$page_title = 'Log Trade';
$errors = [];
$form_data = [];

// Get all strategies for dropdown
$strategies = get_all_strategies();

// Check for errors from save-trade.php
if (isset($_SESSION['trade_errors'])) {
    $errors = $_SESSION['trade_errors'];
    $form_data = $_SESSION['trade_form_data'] ?? [];
    unset($_SESSION['trade_errors'], $_SESSION['trade_form_data']);
}

// Include header
include 'templates/header.php';
?>

<div class="px-4 py-6">
    <div class="max-w-4xl mx-auto">
        <!-- Page Header -->
        <div class="bg-white shadow rounded-lg p-6 mb-6">
            <div class="flex items-center">
                <div class="flex-shrink-0 bg-blue-100 rounded-lg p-3">
                    <svg class="h-8 w-8 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                    </svg>
                </div>
                <div class="ml-4">
                    <h1 class="text-2xl font-bold text-gray-900">Trade Log</h1>
                    <p class="text-gray-600">Record your trade execution and track discipline</p>
                </div>
            </div>
        </div>
        
        <?php if (empty($strategies)): ?>
            <!-- No strategies message -->
            <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-6 text-center">
                <svg class="mx-auto h-12 w-12 text-yellow-400 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path>
                </svg>
                <h3 class="text-lg font-medium text-yellow-800 mb-2">No Strategies Found</h3>
                <p class="text-yellow-700 mb-4">You need to create a strategy before you can log trades.</p>
                <a href="strategies.php" class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md text-white bg-yellow-600 hover:bg-yellow-700">
                    Create Your First Strategy
                </a>
            </div>
        <?php else: ?>
            <!-- Trade Form -->
            <form id="tradeForm" method="POST" action="api/save-trade.php" enctype="multipart/form-data" class="space-y-6">
                <?php echo csrf_field(); ?>
                
                <!-- Strategy Selection -->
                <div class="bg-white shadow rounded-lg p-6">
                    <h2 class="text-lg font-medium text-gray-900 mb-4">Strategy</h2>
                    
                    <div>
                        <label for="strategy_id" class="block text-sm font-medium text-gray-700 mb-2">
                            Select Strategy <span class="text-red-500">*</span>
                        </label>
                        <select name="strategy_id" id="strategy_id" class="mt-1 block w-full pl-3 pr-10 py-2 text-base border-gray-300 focus:outline-none focus:ring-blue-500 focus:border-blue-500 rounded-md" required>
                            <option value="">-- Select a Strategy --</option>
                            <?php foreach ($strategies as $strategy): ?>
                                <option value="<?php echo $strategy['id']; ?>" <?php echo (isset($form_data['strategy_id']) && $form_data['strategy_id'] == $strategy['id']) ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($strategy['name']); ?> (<?php echo htmlspecialchars($strategy['instrument']); ?>)
                                </option>
                            <?php endforeach; ?>
                        </select>
                        <?php if (isset($errors['strategy_id'])): ?>
                            <p class="mt-1 text-sm text-red-600"><?php echo $errors['strategy_id']; ?></p>
                        <?php endif; ?>
                    </div>
                </div>
                
                <!-- Entry Criteria Checklist (will be loaded dynamically) -->
                <div id="entryCriteriaSection" class="bg-white shadow rounded-lg p-6 hidden">
                    <h2 class="text-lg font-medium text-gray-900 mb-4">Entry Criteria Checklist</h2>
                    <div id="entryCriteriaList" class="space-y-3">
                        <!-- Will be populated via JavaScript -->
                    </div>
                </div>
                
                <!-- Exit Criteria Checklist (will be loaded dynamically) -->
                <div id="exitCriteriaSection" class="bg-white shadow rounded-lg p-6 hidden">
                    <h2 class="text-lg font-medium text-gray-900 mb-4">Exit Criteria Checklist</h2>
                    <div id="exitCriteriaList" class="space-y-3">
                        <!-- Will be populated via JavaScript -->
                    </div>
                </div>
                
                <!-- Invalidations Section -->
                <div id="invalidationsSection" class="bg-white shadow rounded-lg p-6 hidden">
                    <h2 class="text-lg font-medium text-gray-900 mb-4">Invalidations</h2>
                    <p class="text-sm text-gray-600 mb-4">Check any conditions that invalidated this trade</p>
                    <div id="invalidationsList" class="space-y-3">
                        <!-- Will be populated via JavaScript -->
                    </div>
                </div>
                
                <!-- Trade Details -->
                <div class="bg-white shadow rounded-lg p-6">
                    <h2 class="text-lg font-medium text-gray-900 mb-4">Trade Details</h2>
                    
                    <!-- Trade Taken Toggle -->
                    <div class="mb-6">
                        <div class="flex items-center">
                            <input type="hidden" name="taken" value="0">
                            <label class="flex items-center cursor-pointer">
                                <input type="checkbox" name="taken" value="1" id="tradeTaken" class="mr-3" checked>
                                <span class="text-sm font-medium text-gray-700">Trade Taken</span>
                            </label>
                            <span class="ml-3 text-sm text-gray-500">(Uncheck if trade was missed)</span>
                        </div>
                    </div>
                    
                    <!-- Trade Info Grid -->
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <!-- Direction -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">
                                Direction <span class="text-red-500">*</span>
                            </label>
                            <div class="flex space-x-4">
                                <label class="flex items-center">
                                    <input type="radio" name="direction" value="Long" class="mr-2" <?php echo (isset($form_data['direction']) && $form_data['direction'] == 'Long') ? 'checked' : ''; ?> required>
                                    <span>Long</span>
                                </label>
                                <label class="flex items-center">
                                    <input type="radio" name="direction" value="Short" class="mr-2" <?php echo (isset($form_data['direction']) && $form_data['direction'] == 'Short') ? 'checked' : ''; ?>>
                                    <span>Short</span>
                                </label>
                            </div>
                        </div>
                        
                        <!-- Session -->
                        <div>
                            <label for="session" class="block text-sm font-medium text-gray-700 mb-2">
                                Session <span class="text-red-500">*</span>
                            </label>
                            <select name="session" id="session" class="block w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-blue-500 focus:border-blue-500" required>
                                <option value="">-- Select Session --</option>
                                <option value="Asia" <?php echo (isset($form_data['session']) && $form_data['session'] == 'Asia') ? 'selected' : ''; ?>>Asia</option>
                                <option value="London" <?php echo (isset($form_data['session']) && $form_data['session'] == 'London') ? 'selected' : ''; ?>>London</option>
                                <option value="New York" <?php echo (isset($form_data['session']) && $form_data['session'] == 'New York') ? 'selected' : ''; ?>>New York</option>
                                <option value="All" <?php echo (isset($form_data['session']) && $form_data['session'] == 'All') ? 'selected' : ''; ?>>All Sessions</option>
                            </select>
                        </div>
                        
                        <!-- Time -->
                        <div>
                            <label for="trade_timestamp" class="block text-sm font-medium text-gray-700 mb-2">
                                Time <span class="text-red-500">*</span>
                            </label>
                            <input type="datetime-local" name="trade_timestamp" id="trade_timestamp" class="block w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-blue-500 focus:border-blue-500" required value="<?php echo isset($form_data['trade_timestamp']) ? $form_data['trade_timestamp'] : date('Y-m-d\TH:i'); ?>">
                        </div>
                        
                        <!-- Bias -->
                        <div>
                            <label for="bias" class="block text-sm font-medium text-gray-700 mb-2">
                                Bias
                            </label>
                            <input type="text" name="bias" id="bias" placeholder="e.g., Breakout, Reversal" class="block w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-blue-500 focus:border-blue-500" value="<?php echo isset($form_data['bias']) ? htmlspecialchars($form_data['bias']) : ''; ?>">
                        </div>
                    </div>
                    
                    <!-- Price Fields (shown only when trade is taken) -->
                    <div id="priceFields" class="mt-6 grid grid-cols-1 md:grid-cols-2 gap-6">
                        <!-- Entry Price -->
                        <div>
                            <label for="entry_price" class="block text-sm font-medium text-gray-700 mb-2">
                                Entry Price <span class="text-red-500">*</span>
                            </label>
                            <input type="number" name="entry_price" id="entry_price" step="0.00001" class="block w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-blue-500 focus:border-blue-500" value="<?php echo isset($form_data['entry_price']) ? $form_data['entry_price'] : ''; ?>">
                        </div>
                        
                        <!-- Stop Loss -->
                        <div>
                            <label for="stop_loss_price" class="block text-sm font-medium text-gray-700 mb-2">
                                Stop Loss <span class="text-red-500">*</span>
                            </label>
                            <input type="number" name="stop_loss_price" id="stop_loss_price" step="0.00001" class="block w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-blue-500 focus:border-blue-500" value="<?php echo isset($form_data['stop_loss_price']) ? $form_data['stop_loss_price'] : ''; ?>">
                        </div>
                        
                        <!-- Exit Price -->
                        <div>
                            <label for="exit_price" class="block text-sm font-medium text-gray-700 mb-2">
                                Exit Price <span class="text-red-500">*</span>
                            </label>
                            <input type="number" name="exit_price" id="exit_price" step="0.00001" class="block w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-blue-500 focus:border-blue-500" value="<?php echo isset($form_data['exit_price']) ? $form_data['exit_price'] : ''; ?>">
                        </div>
                        
                        <!-- Risk % -->
                        <div>
                            <label for="risk_percent" class="block text-sm font-medium text-gray-700 mb-2">
                                Risk % <span class="text-red-500">*</span>
                            </label>
                            <select name="risk_percent" id="risk_percent" class="block w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-blue-500 focus:border-blue-500">
                                <option value="0.25" <?php echo (isset($form_data['risk_percent']) && $form_data['risk_percent'] == '0.25') ? 'selected' : ''; ?>>0.25%</option>
                                <option value="0.5" <?php echo (isset($form_data['risk_percent']) && $form_data['risk_percent'] == '0.5') ? 'selected' : ''; ?>>0.5%</option>
                                <option value="1" <?php echo (isset($form_data['risk_percent']) && $form_data['risk_percent'] == '1') ? 'selected' : ''; ?>>1%</option>
                                <option value="2" <?php echo (isset($form_data['risk_percent']) && $form_data['risk_percent'] == '2') ? 'selected' : ''; ?>>2%</option>
                            </select>
                        </div>
                        
                        <!-- R-Multiple (calculated) -->
                        <div class="md:col-span-2">
                            <label class="block text-sm font-medium text-gray-700 mb-2">
                                R-Multiple
                            </label>
                            <div class="flex items-center">
                                <span id="rMultipleDisplay" class="text-2xl font-bold text-gray-900">--</span>
                                <span class="ml-2 text-gray-500">R</span>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Missed Trade Reason (hidden by default) -->
                    <div id="missedReasonField" class="mt-6 hidden">
                        <label for="missed_reason" class="block text-sm font-medium text-gray-700 mb-2">
                            Reason for Missing Trade <span class="text-red-500">*</span>
                        </label>
                        <textarea name="missed_reason" id="missed_reason" rows="3" class="block w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-blue-500 focus:border-blue-500"><?php echo isset($form_data['missed_reason']) ? htmlspecialchars($form_data['missed_reason']) : ''; ?></textarea>
                    </div>
                    
                    <!-- Reason -->
                    <div class="mt-6">
                        <label for="reason" class="block text-sm font-medium text-gray-700 mb-2">
                            Reason (Short Description)
                        </label>
                        <input type="text" name="reason" id="reason" class="block w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-blue-500 focus:border-blue-500" value="<?php echo isset($form_data['reason']) ? htmlspecialchars($form_data['reason']) : ''; ?>">
                    </div>
                </div>
                
                <!-- Screenshots Section -->
                <div class="bg-white shadow rounded-lg p-6">
                    <h2 class="text-lg font-medium text-gray-900 mb-4">Screenshots</h2>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">
                            Upload Screenshots
                        </label>
                        <input type="file" name="screenshots[]" multiple accept="image/*" class="block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-sm file:font-semibold file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100">
                        <p class="mt-1 text-sm text-gray-500">You can select multiple images. Max 5MB per file.</p>
                    </div>
                </div>
                
                <!-- Notes/Emotions Section -->
                <div class="bg-white shadow rounded-lg p-6">
                    <h2 class="text-lg font-medium text-gray-900 mb-4">Notes / Emotions (Optional)</h2>
                    
                    <!-- Text Notes -->
                    <div>
                        <label for="emotional_notes" class="block text-sm font-medium text-gray-700 mb-2">
                            Emotional Notes
                        </label>
                        <textarea name="emotional_notes" id="emotional_notes" rows="4" class="block w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-blue-500 focus:border-blue-500" placeholder="How did you feel about this trade? Any emotional insights or thoughts..."><?php echo isset($form_data['emotional_notes']) ? htmlspecialchars($form_data['emotional_notes']) : ''; ?></textarea>
                    </div>
                </div>
                
                    </div>
                </div>
                
                <!-- Submit Button -->
                <div class="flex justify-end">
                    <button type="submit" id="submitBtn" disabled class="inline-flex items-center px-6 py-3 border border-transparent text-base font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700 disabled:opacity-50 disabled:cursor-not-allowed">
                        Submit Trade Log
                    </button>
                </div>
            </form>
        <?php endif; ?>
    </div>
</div>

<!-- JavaScript for form interactions -->
<script src="assets/js/trade-form.js"></script>

<?php
$extra_scripts = '<script>
// Initialize form on page load
document.addEventListener("DOMContentLoaded", function() {
    if (window.initializeTradeForm) {
        initializeTradeForm();
    }
});
</script>';

include 'templates/footer.php';
?>