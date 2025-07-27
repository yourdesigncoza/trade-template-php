<?php
/**
 * API endpoint to save trade log
 */

require_once '../includes/config.php';
require_once '../includes/db.php';
require_once '../includes/csrf.php';
require_once '../includes/functions.php';
require_once '../includes/validation.php';

// This should be a POST request
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    die('Method not allowed');
}

// Validate CSRF
validate_csrf();

// Get form data
$form_data = $_POST;
$errors = validate_trade($form_data);

if (!empty($errors)) {
    $_SESSION['trade_errors'] = $errors;
    $_SESSION['trade_form_data'] = $form_data;
    redirect_with_message('../index.php', 'Please correct the errors and try again', 'error');
}

try {
    $db = db();
    
    // Prepare trade data
    $strategy_id = intval($form_data['strategy_id']);
    $taken = ($form_data['taken'] ?? '0') === '1';
    $missed_reason = $taken ? null : sanitize($form_data['missed_reason']);
    $direction = sanitize($form_data['direction']);
    $session = sanitize($form_data['session']);
    $bias = sanitize($form_data['bias'] ?? '');
    $trade_timestamp = $form_data['trade_timestamp'];
    $reason = sanitize($form_data['reason'] ?? '');
    $emotional_notes = sanitize($form_data['emotional_notes'] ?? '');
    
    // Price fields (only for taken trades)
    $entry_price = null;
    $stop_loss_price = null;
    $exit_price = null;
    $risk_percent = null;
    $r_multiple = null;
    
    if ($taken) {
        $entry_price = floatval($form_data['entry_price']);
        $stop_loss_price = floatval($form_data['stop_loss_price']);
        $exit_price = floatval($form_data['exit_price']);
        $risk_percent = floatval($form_data['risk_percent']);
        
        // Calculate R-multiple
        $r_multiple = calculate_r_multiple($entry_price, $stop_loss_price, $exit_price);
    }
    
    // Begin transaction
    $db->beginTransaction();
    
    // Insert trade
    $stmt = $db->prepare("
        INSERT INTO trades (
            strategy_id, taken, missed_reason, direction, session, bias,
            trade_timestamp, entry_price, stop_loss_price, exit_price,
            risk_percent, r_multiple, reason, emotional_notes
        ) VALUES (
            ?, ?, ?, ?, ?, ?,
            ?, ?, ?, ?,
            ?, ?, ?, ?
        )
    ");
    
    $stmt->execute([
        $strategy_id, $taken, $missed_reason, $direction, $session, $bias,
        $trade_timestamp, $entry_price, $stop_loss_price, $exit_price,
        $risk_percent, $r_multiple, $reason, $emotional_notes
    ]);
    
    $trade_id = $db->lastInsertId();
    
    // Save entry criteria checklist
    if (isset($form_data['entry_criteria']) && is_array($form_data['entry_criteria'])) {
        $stmt = $db->prepare("
            INSERT INTO trade_checklist_logs (trade_id, checklist_type, criteria_id, checked)
            VALUES (?, 'entry', ?, 1)
        ");
        
        foreach ($form_data['entry_criteria'] as $criteria_id) {
            $stmt->execute([$trade_id, intval($criteria_id)]);
        }
    }
    
    // Save exit criteria checklist
    if (isset($form_data['exit_criteria']) && is_array($form_data['exit_criteria'])) {
        $stmt = $db->prepare("
            INSERT INTO trade_checklist_logs (trade_id, checklist_type, criteria_id, checked)
            VALUES (?, 'exit', ?, 1)
        ");
        
        foreach ($form_data['exit_criteria'] as $criteria_id) {
            $stmt->execute([$trade_id, intval($criteria_id)]);
        }
    }
    
    // Save invalidations
    if (isset($form_data['invalidations']) && is_array($form_data['invalidations'])) {
        $stmt = $db->prepare("
            INSERT INTO trade_invalidation_logs (trade_id, invalidation_id, active)
            VALUES (?, ?, 1)
        ");
        
        foreach ($form_data['invalidations'] as $invalidation_id) {
            $stmt->execute([$trade_id, intval($invalidation_id)]);
        }
    }
    
    // Handle screenshot uploads
    if (isset($_FILES['screenshots']) && !empty($_FILES['screenshots']['name'][0])) {
        $upload_dir = UPLOAD_DIR;
        
        // Create upload directory if it doesn't exist
        if (!is_dir($upload_dir)) {
            mkdir($upload_dir, 0755, true);
        }
        
        $stmt = $db->prepare("INSERT INTO trade_screenshots (trade_id, image_path) VALUES (?, ?)");
        
        foreach ($_FILES['screenshots']['tmp_name'] as $key => $tmp_name) {
            if ($_FILES['screenshots']['error'][$key] === UPLOAD_ERR_OK) {
                $file = [
                    'name' => $_FILES['screenshots']['name'][$key],
                    'type' => $_FILES['screenshots']['type'][$key],
                    'tmp_name' => $tmp_name,
                    'error' => $_FILES['screenshots']['error'][$key],
                    'size' => $_FILES['screenshots']['size'][$key]
                ];
                
                $error = validate_upload($file);
                if ($error === null) {
                    // Generate unique filename
                    $extension = pathinfo($file['name'], PATHINFO_EXTENSION);
                    $filename = 'trade_' . $trade_id . '_' . uniqid() . '.' . $extension;
                    $filepath = $upload_dir . $filename;
                    
                    if (move_uploaded_file($tmp_name, $filepath)) {
                        $stmt->execute([$trade_id, $filename]);
                    }
                }
            }
        }
    }
    
    // Commit transaction
    $db->commit();
    
    // Clear form data from session
    unset($_SESSION['trade_errors'], $_SESSION['trade_form_data']);
    
    redirect_with_message('../trade-history.php', 'Trade logged successfully!', 'success');
    
} catch (Exception $e) {
    $db->rollBack();
    error_log('Trade save error: ' . $e->getMessage());
    redirect_with_message('../index.php', 'Error saving trade. Please try again.', 'error');
}