<?php
/**
 * Strategy Management Page
 */

require_once 'includes/config.php';
require_once 'includes/db.php';
require_once 'includes/csrf.php';
require_once 'includes/functions.php';
require_once 'includes/validation.php';

$page_title = 'Strategies';
$errors = [];
$form_data = [];
$edit_mode = false;
$strategy_id = null;

// Check if editing
if (isset($_GET['edit']) && is_numeric($_GET['edit'])) {
    $edit_mode = true;
    $strategy_id = intval($_GET['edit']);
    $strategy = get_strategy_details($strategy_id);
    if ($strategy) {
        $form_data = $strategy;
    } else {
        redirect_with_message('strategies.php', 'Strategy not found', 'error');
    }
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    validate_csrf();
    
    $action = $_POST['action'] ?? 'create';
    
    if ($action === 'delete') {
        // Handle deletion
        $delete_id = intval($_POST['strategy_id']);
        try {
            $db = db();
            $stmt = $db->prepare("DELETE FROM strategies WHERE id = ?");
            $stmt->execute([$delete_id]);
            redirect_with_message('strategies.php', 'Strategy deleted successfully', 'success');
        } catch (PDOException $e) {
            redirect_with_message('strategies.php', 'Error deleting strategy', 'error');
        }
    } else {
        // Create or update strategy
        $form_data = $_POST;
        $errors = validate_strategy($form_data);
        
        if (empty($errors)) {
            try {
                $db = db();
                
                // Prepare data
                $name = sanitize($form_data['name']);
                $instrument = sanitize($form_data['instrument']);
                $timeframes = json_encode($form_data['timeframes'] ?? []);
                $sessions = json_encode($form_data['sessions'] ?? []);
                $chart_image_url = sanitize($form_data['chart_image_url'] ?? '');
                
                if ($edit_mode) {
                    // Update existing strategy
                    $stmt = $db->prepare("UPDATE strategies SET name = ?, instrument = ?, timeframes = ?, sessions = ?, chart_image_url = ? WHERE id = ?");
                    $stmt->execute([$name, $instrument, $timeframes, $sessions, $chart_image_url, $strategy_id]);
                    $message = 'Strategy updated successfully';
                } else {
                    // Create new strategy
                    $stmt = $db->prepare("INSERT INTO strategies (name, instrument, timeframes, sessions, chart_image_url) VALUES (?, ?, ?, ?, ?)");
                    $stmt->execute([$name, $instrument, $timeframes, $sessions, $chart_image_url]);
                    $strategy_id = $db->lastInsertId();
                    $message = 'Strategy created successfully';
                }
                
                // Handle entry criteria
                if (isset($form_data['entry_criteria']) && is_array($form_data['entry_criteria'])) {
                    // Delete existing criteria
                    $stmt = $db->prepare("DELETE FROM entry_criteria WHERE strategy_id = ?");
                    $stmt->execute([$strategy_id]);
                    
                    // Insert new criteria
                    $stmt = $db->prepare("INSERT INTO entry_criteria (strategy_id, label, description, sort_order) VALUES (?, ?, ?, ?)");
                    foreach ($form_data['entry_criteria'] as $index => $criteria) {
                        if (!empty(trim($criteria['label']))) {
                            $stmt->execute([
                                $strategy_id,
                                sanitize($criteria['label']),
                                sanitize($criteria['description'] ?? ''),
                                $index
                            ]);
                        }
                    }
                }
                
                // Handle exit criteria
                if (isset($form_data['exit_criteria']) && is_array($form_data['exit_criteria'])) {
                    // Delete existing criteria
                    $stmt = $db->prepare("DELETE FROM exit_criteria WHERE strategy_id = ?");
                    $stmt->execute([$strategy_id]);
                    
                    // Insert new criteria
                    $stmt = $db->prepare("INSERT INTO exit_criteria (strategy_id, label, description, sort_order) VALUES (?, ?, ?, ?)");
                    foreach ($form_data['exit_criteria'] as $index => $criteria) {
                        if (!empty(trim($criteria['label']))) {
                            $stmt->execute([
                                $strategy_id,
                                sanitize($criteria['label']),
                                sanitize($criteria['description'] ?? ''),
                                $index
                            ]);
                        }
                    }
                }
                
                // Handle invalidations
                if (isset($form_data['invalidations']) && is_array($form_data['invalidations'])) {
                    // Delete existing invalidations
                    $stmt = $db->prepare("DELETE FROM invalidations WHERE strategy_id = ?");
                    $stmt->execute([$strategy_id]);
                    
                    // Insert new invalidations
                    $stmt = $db->prepare("INSERT INTO invalidations (strategy_id, label, reason, code) VALUES (?, ?, ?, ?)");
                    foreach ($form_data['invalidations'] as $index => $invalidation) {
                        if (!empty(trim($invalidation['label']))) {
                            $stmt->execute([
                                $strategy_id,
                                sanitize($invalidation['label']),
                                sanitize($invalidation['reason'] ?? ''),
                                chr(65 + $index) // A, B, C, etc.
                            ]);
                        }
                    }
                }
                
                redirect_with_message('strategies.php', $message, 'success');
                
            } catch (PDOException $e) {
                $errors['database'] = 'Database error: ' . $e->getMessage();
            }
        }
    }
}

// Get all strategies for listing
$strategies = get_all_strategies();

// Available timeframes and sessions
$available_timeframes = ['M1', 'M5', 'M15', 'M30', 'H1', 'H4', 'D1', 'W1', 'MN'];
$available_sessions = ['Asia', 'London', 'New York'];

include 'templates/header.php';
?>

<div class="px-4 py-6">
    <div class="max-w-6xl mx-auto">
        <!-- Page Header -->
        <div class="bg-white shadow rounded-lg p-6 mb-6">
            <div class="flex items-center justify-between">
                <div class="flex items-center">
                    <div class="flex-shrink-0 bg-green-100 rounded-lg p-3">
                        <svg class="h-8 w-8 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path>
                        </svg>
                    </div>
                    <div class="ml-4">
                        <h1 class="text-2xl font-bold text-gray-900">Trading Strategies</h1>
                        <p class="text-gray-600">Define and manage your trading strategies</p>
                    </div>
                </div>
                <button type="button" onclick="toggleForm()" class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md text-white bg-green-600 hover:bg-green-700">
                    <svg class="h-5 w-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                    </svg>
                    New Strategy
                </button>
            </div>
        </div>
        
        <!-- Strategy Form (hidden by default) -->
        <div id="strategyForm" class="<?php echo ($edit_mode || !empty($errors)) ? '' : 'hidden'; ?> mb-6">
            <form method="POST" action="" class="bg-white shadow rounded-lg p-6">
                <?php echo csrf_field(); ?>
                <input type="hidden" name="action" value="<?php echo $edit_mode ? 'update' : 'create'; ?>">
                
                <h2 class="text-lg font-medium text-gray-900 mb-6"><?php echo $edit_mode ? 'Edit Strategy' : 'Create New Strategy'; ?></h2>
                
                <!-- Basic Info -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                    <div>
                        <label for="name" class="block text-sm font-medium text-gray-700 mb-2">
                            Strategy Name <span class="text-red-500">*</span>
                        </label>
                        <input type="text" name="name" id="name" required class="block w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-blue-500 focus:border-blue-500" value="<?php echo htmlspecialchars($form_data['name'] ?? ''); ?>">
                        <?php if (isset($errors['name'])): ?>
                            <p class="mt-1 text-sm text-red-600"><?php echo $errors['name']; ?></p>
                        <?php endif; ?>
                    </div>
                    
                    <div>
                        <label for="instrument" class="block text-sm font-medium text-gray-700 mb-2">
                            Instrument <span class="text-red-500">*</span>
                        </label>
                        <input type="text" name="instrument" id="instrument" required placeholder="e.g., EURUSD, GBPUSD" class="block w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-blue-500 focus:border-blue-500" value="<?php echo htmlspecialchars($form_data['instrument'] ?? ''); ?>">
                        <?php if (isset($errors['instrument'])): ?>
                            <p class="mt-1 text-sm text-red-600"><?php echo $errors['instrument']; ?></p>
                        <?php endif; ?>
                    </div>
                </div>
                
                <!-- Timeframes -->
                <div class="mb-6">
                    <label class="block text-sm font-medium text-gray-700 mb-2">
                        Timeframes <span class="text-red-500">*</span>
                    </label>
                    <div class="grid grid-cols-3 md:grid-cols-5 gap-3">
                        <?php foreach ($available_timeframes as $tf): ?>
                            <label class="flex items-center">
                                <input type="checkbox" name="timeframes[]" value="<?php echo $tf; ?>" class="mr-2" <?php echo (isset($form_data['timeframes']) && in_array($tf, $form_data['timeframes'])) ? 'checked' : ''; ?>>
                                <span><?php echo $tf; ?></span>
                            </label>
                        <?php endforeach; ?>
                    </div>
                    <?php if (isset($errors['timeframes'])): ?>
                        <p class="mt-1 text-sm text-red-600"><?php echo $errors['timeframes']; ?></p>
                    <?php endif; ?>
                </div>
                
                <!-- Sessions -->
                <div class="mb-6">
                    <label class="block text-sm font-medium text-gray-700 mb-2">
                        Trading Sessions <span class="text-red-500">*</span>
                    </label>
                    <div class="grid grid-cols-3 gap-3">
                        <?php foreach ($available_sessions as $session): ?>
                            <label class="flex items-center">
                                <input type="checkbox" name="sessions[]" value="<?php echo $session; ?>" class="mr-2" <?php echo (isset($form_data['sessions']) && in_array($session, $form_data['sessions'])) ? 'checked' : ''; ?>>
                                <span><?php echo $session; ?></span>
                            </label>
                        <?php endforeach; ?>
                    </div>
                    <?php if (isset($errors['sessions'])): ?>
                        <p class="mt-1 text-sm text-red-600"><?php echo $errors['sessions']; ?></p>
                    <?php endif; ?>
                </div>
                
                <!-- Chart Image URL -->
                <div class="mb-6">
                    <label for="chart_image_url" class="block text-sm font-medium text-gray-700 mb-2">
                        Chart Model Image URL
                    </label>
                    <input type="url" name="chart_image_url" id="chart_image_url" placeholder="https://example.com/chart.png" class="block w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-blue-500 focus:border-blue-500" value="<?php echo htmlspecialchars($form_data['chart_image_url'] ?? ''); ?>">
                </div>
                
                <!-- Entry Criteria -->
                <div class="mb-6">
                    <h3 class="text-md font-medium text-gray-900 mb-3">Entry Criteria</h3>
                    <div id="entryCriteriaContainer" class="space-y-3">
                        <?php 
                        $entry_criteria = $form_data['entry_criteria'] ?? [['label' => '', 'description' => '']];
                        foreach ($entry_criteria as $index => $criteria): 
                        ?>
                            <div class="entry-criteria-item border rounded-lg p-4">
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                    <input type="text" name="entry_criteria[<?php echo $index; ?>][label]" placeholder="Criteria label" class="block w-full px-3 py-2 border border-gray-300 rounded-md" value="<?php echo htmlspecialchars($criteria['label'] ?? ''); ?>">
                                    <input type="text" name="entry_criteria[<?php echo $index; ?>][description]" placeholder="Description (optional)" class="block w-full px-3 py-2 border border-gray-300 rounded-md" value="<?php echo htmlspecialchars($criteria['description'] ?? ''); ?>">
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                    <button type="button" onclick="addEntryCriteria()" class="mt-3 text-sm text-blue-600 hover:text-blue-800">+ Add Entry Criteria</button>
                </div>
                
                <!-- Exit Criteria -->
                <div class="mb-6">
                    <h3 class="text-md font-medium text-gray-900 mb-3">Exit Criteria</h3>
                    <div id="exitCriteriaContainer" class="space-y-3">
                        <?php 
                        $exit_criteria = $form_data['exit_criteria'] ?? [['label' => '', 'description' => '']];
                        foreach ($exit_criteria as $index => $criteria): 
                        ?>
                            <div class="exit-criteria-item border rounded-lg p-4">
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                    <input type="text" name="exit_criteria[<?php echo $index; ?>][label]" placeholder="Criteria label" class="block w-full px-3 py-2 border border-gray-300 rounded-md" value="<?php echo htmlspecialchars($criteria['label'] ?? ''); ?>">
                                    <input type="text" name="exit_criteria[<?php echo $index; ?>][description]" placeholder="Description (optional)" class="block w-full px-3 py-2 border border-gray-300 rounded-md" value="<?php echo htmlspecialchars($criteria['description'] ?? ''); ?>">
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                    <button type="button" onclick="addExitCriteria()" class="mt-3 text-sm text-blue-600 hover:text-blue-800">+ Add Exit Criteria</button>
                </div>
                
                <!-- Invalidations -->
                <div class="mb-6">
                    <h3 class="text-md font-medium text-gray-900 mb-3">Invalidation Conditions</h3>
                    <div id="invalidationsContainer" class="space-y-3">
                        <?php 
                        $invalidations = $form_data['invalidations'] ?? [['label' => '', 'reason' => '']];
                        foreach ($invalidations as $index => $invalidation): 
                        ?>
                            <div class="invalidation-item border rounded-lg p-4">
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                    <input type="text" name="invalidations[<?php echo $index; ?>][label]" placeholder="Condition label" class="block w-full px-3 py-2 border border-gray-300 rounded-md" value="<?php echo htmlspecialchars($invalidation['label'] ?? ''); ?>">
                                    <input type="text" name="invalidations[<?php echo $index; ?>][reason]" placeholder="Reason (optional)" class="block w-full px-3 py-2 border border-gray-300 rounded-md" value="<?php echo htmlspecialchars($invalidation['reason'] ?? ''); ?>">
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                    <button type="button" onclick="addInvalidation()" class="mt-3 text-sm text-blue-600 hover:text-blue-800">+ Add Invalidation</button>
                </div>
                
                <!-- Form Actions -->
                <div class="flex justify-end space-x-3">
                    <button type="button" onclick="cancelForm()" class="px-4 py-2 border border-gray-300 rounded-md text-gray-700 hover:bg-gray-50">
                        Cancel
                    </button>
                    <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700">
                        <?php echo $edit_mode ? 'Update Strategy' : 'Create Strategy'; ?>
                    </button>
                </div>
            </form>
        </div>
        
        <!-- Strategies List -->
        <div class="bg-white shadow rounded-lg">
            <div class="px-6 py-4 border-b border-gray-200">
                <h2 class="text-lg font-medium text-gray-900">Your Strategies</h2>
            </div>
            
            <?php if (empty($strategies)): ?>
                <div class="p-6 text-center text-gray-500">
                    <svg class="mx-auto h-12 w-12 text-gray-400 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                    </svg>
                    <p>No strategies created yet.</p>
                    <p class="mt-2 text-sm">Click "New Strategy" to create your first trading strategy.</p>
                </div>
            <?php else: ?>
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Name</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Instrument</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Timeframes</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Sessions</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Created</th>
                                <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            <?php foreach ($strategies as $strategy): ?>
                                <tr>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="text-sm font-medium text-gray-900"><?php echo htmlspecialchars($strategy['name']); ?></div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="text-sm text-gray-900"><?php echo htmlspecialchars($strategy['instrument']); ?></div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="text-sm text-gray-900">
                                            <?php 
                                            $timeframes = json_decode($strategy['timeframes'], true);
                                            echo htmlspecialchars(implode(', ', $timeframes));
                                            ?>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="text-sm text-gray-900">
                                            <?php 
                                            $sessions = json_decode($strategy['sessions'], true);
                                            echo htmlspecialchars(implode(', ', $sessions));
                                            ?>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                        <?php echo format_datetime($strategy['created_at'], 'M d, Y'); ?>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                        <a href="?edit=<?php echo $strategy['id']; ?>" class="text-blue-600 hover:text-blue-900 mr-3">Edit</a>
                                        <form method="POST" action="" class="inline" onsubmit="return confirm('Are you sure you want to delete this strategy?');">
                                            <?php echo csrf_field(); ?>
                                            <input type="hidden" name="action" value="delete">
                                            <input type="hidden" name="strategy_id" value="<?php echo $strategy['id']; ?>">
                                            <button type="submit" class="text-red-600 hover:text-red-900">Delete</button>
                                        </form>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<script>
function toggleForm() {
    const form = document.getElementById('strategyForm');
    form.classList.toggle('hidden');
}

function cancelForm() {
    window.location.href = 'strategies.php';
}

let entryCriteriaCount = <?php echo count($entry_criteria); ?>;
let exitCriteriaCount = <?php echo count($exit_criteria); ?>;
let invalidationCount = <?php echo count($invalidations); ?>;

function addEntryCriteria() {
    const container = document.getElementById('entryCriteriaContainer');
    const div = document.createElement('div');
    div.className = 'entry-criteria-item border rounded-lg p-4';
    div.innerHTML = `
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <input type="text" name="entry_criteria[${entryCriteriaCount}][label]" placeholder="Criteria label" class="block w-full px-3 py-2 border border-gray-300 rounded-md">
            <input type="text" name="entry_criteria[${entryCriteriaCount}][description]" placeholder="Description (optional)" class="block w-full px-3 py-2 border border-gray-300 rounded-md">
        </div>
    `;
    container.appendChild(div);
    entryCriteriaCount++;
}

function addExitCriteria() {
    const container = document.getElementById('exitCriteriaContainer');
    const div = document.createElement('div');
    div.className = 'exit-criteria-item border rounded-lg p-4';
    div.innerHTML = `
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <input type="text" name="exit_criteria[${exitCriteriaCount}][label]" placeholder="Criteria label" class="block w-full px-3 py-2 border border-gray-300 rounded-md">
            <input type="text" name="exit_criteria[${exitCriteriaCount}][description]" placeholder="Description (optional)" class="block w-full px-3 py-2 border border-gray-300 rounded-md">
        </div>
    `;
    container.appendChild(div);
    exitCriteriaCount++;
}

function addInvalidation() {
    const container = document.getElementById('invalidationsContainer');
    const div = document.createElement('div');
    div.className = 'invalidation-item border rounded-lg p-4';
    div.innerHTML = `
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <input type="text" name="invalidations[${invalidationCount}][label]" placeholder="Condition label" class="block w-full px-3 py-2 border border-gray-300 rounded-md">
            <input type="text" name="invalidations[${invalidationCount}][reason]" placeholder="Reason (optional)" class="block w-full px-3 py-2 border border-gray-300 rounded-md">
        </div>
    `;
    container.appendChild(div);
    invalidationCount++;
}
</script>

<?php include 'templates/footer.php'; ?>