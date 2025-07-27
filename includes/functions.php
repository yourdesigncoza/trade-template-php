<?php
/**
 * Helper functions for the application
 */

/**
 * Sanitize input data
 * @param string $data
 * @return string
 */
function sanitize($data) {
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data);
    return $data;
}

/**
 * Validate required fields
 * @param array $fields Array of field names to check
 * @param array $data Data array (usually $_POST)
 * @return array Array of error messages
 */
function validate_required($fields, $data) {
    $errors = [];
    foreach ($fields as $field) {
        if (!isset($data[$field]) || empty(trim($data[$field]))) {
            $errors[$field] = ucfirst(str_replace('_', ' ', $field)) . ' is required';
        }
    }
    return $errors;
}

/**
 * Validate decimal/numeric fields
 * @param string $value
 * @param string $field_name
 * @return string|null Error message or null if valid
 */
function validate_decimal($value, $field_name) {
    if (!is_numeric($value)) {
        return "$field_name must be a valid number";
    }
    if ($value < 0) {
        return "$field_name cannot be negative";
    }
    return null;
}

/**
 * Calculate R-multiple
 * @param float $entry
 * @param float $stop
 * @param float $exit
 * @return float|null
 */
function calculate_r_multiple($entry, $stop, $exit) {
    if ($entry == $stop) {
        return null;
    }
    
    $risk = abs($entry - $stop);
    $reward = $exit - $entry;
    
    // For short trades, reverse the calculation
    if ($stop > $entry) {
        $reward = $entry - $exit;
    }
    
    return round($reward / $risk, 2);
}

/**
 * Format datetime for display
 * @param string $datetime
 * @param string $format
 * @return string
 */
function format_datetime($datetime, $format = 'Y-m-d H:i') {
    return date($format, strtotime($datetime));
}

/**
 * Get all strategies
 * @return array
 */
function get_all_strategies() {
    $db = db();
    $stmt = $db->query("SELECT * FROM strategies ORDER BY name");
    return $stmt->fetchAll();
}

/**
 * Get strategy with all related data
 * @param int $strategy_id
 * @return array|false
 */
function get_strategy_details($strategy_id) {
    $db = db();
    
    // Get strategy
    $stmt = $db->prepare("SELECT * FROM strategies WHERE id = ?");
    $stmt->execute([$strategy_id]);
    $strategy = $stmt->fetch();
    
    if (!$strategy) {
        return false;
    }
    
    // Decode JSON fields
    $strategy['timeframes'] = json_decode($strategy['timeframes'], true);
    $strategy['sessions'] = json_decode($strategy['sessions'], true);
    
    // Get entry criteria
    $stmt = $db->prepare("SELECT * FROM entry_criteria WHERE strategy_id = ? ORDER BY sort_order");
    $stmt->execute([$strategy_id]);
    $strategy['entry_criteria'] = $stmt->fetchAll();
    
    // Get exit criteria
    $stmt = $db->prepare("SELECT * FROM exit_criteria WHERE strategy_id = ? ORDER BY sort_order");
    $stmt->execute([$strategy_id]);
    $strategy['exit_criteria'] = $stmt->fetchAll();
    
    // Get invalidations
    $stmt = $db->prepare("SELECT * FROM invalidations WHERE strategy_id = ? ORDER BY code");
    $stmt->execute([$strategy_id]);
    $strategy['invalidations'] = $stmt->fetchAll();
    
    return $strategy;
}

/**
 * Create alert message HTML
 * @param string $message
 * @param string $type (success, error, warning, info)
 * @return string
 */
function create_alert($message, $type = 'info') {
    $classes = [
        'success' => 'bg-green-100 border-green-400 text-green-700',
        'error' => 'bg-red-100 border-red-400 text-red-700',
        'warning' => 'bg-yellow-100 border-yellow-400 text-yellow-700',
        'info' => 'bg-blue-100 border-blue-400 text-blue-700'
    ];
    
    $class = $classes[$type] ?? $classes['info'];
    
    return '<div class="border px-4 py-3 rounded relative mb-4 ' . $class . '" role="alert">
        <span class="block sm:inline">' . htmlspecialchars($message) . '</span>
    </div>';
}

/**
 * Redirect with message
 * @param string $url
 * @param string $message
 * @param string $type
 */
function redirect_with_message($url, $message, $type = 'info') {
    $_SESSION['flash_message'] = $message;
    $_SESSION['flash_type'] = $type;
    header("Location: $url");
    exit;
}

/**
 * Display flash message and clear it
 * @return string
 */
function display_flash_message() {
    if (isset($_SESSION['flash_message'])) {
        $message = $_SESSION['flash_message'];
        $type = $_SESSION['flash_type'] ?? 'info';
        unset($_SESSION['flash_message'], $_SESSION['flash_type']);
        return create_alert($message, $type);
    }
    return '';
}