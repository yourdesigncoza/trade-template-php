<?php
/**
 * API endpoint to get strategy details with criteria and invalidations
 */

require_once '../includes/config.php';
require_once '../includes/db.php';
require_once '../includes/functions.php';

header('Content-Type: application/json');

// Check if strategy ID is provided
if (!isset($_GET['id']) || empty($_GET['id'])) {
    echo json_encode(['success' => false, 'error' => 'Strategy ID is required']);
    exit;
}

$strategy_id = intval($_GET['id']);

// Get strategy details
$strategy = get_strategy_details($strategy_id);

if (!$strategy) {
    echo json_encode(['success' => false, 'error' => 'Strategy not found']);
    exit;
}

// Return strategy data
echo json_encode([
    'success' => true,
    'strategy' => $strategy
]);