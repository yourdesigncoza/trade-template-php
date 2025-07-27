<?php
/**
 * Form validation functions
 */

/**
 * Validate strategy form data
 * @param array $data
 * @return array Errors array
 */
function validate_strategy($data) {
    $errors = [];
    
    // Required fields
    if (empty(trim($data['name'] ?? ''))) {
        $errors['name'] = 'Strategy name is required';
    }
    
    if (empty(trim($data['instrument'] ?? ''))) {
        $errors['instrument'] = 'Instrument is required';
    }
    
    if (empty($data['timeframes'] ?? [])) {
        $errors['timeframes'] = 'At least one timeframe must be selected';
    }
    
    if (empty($data['sessions'] ?? [])) {
        $errors['sessions'] = 'At least one session must be selected';
    }
    
    return $errors;
}

/**
 * Validate trade form data
 * @param array $data
 * @return array Errors array
 */
function validate_trade($data) {
    $errors = [];
    
    // Required fields
    if (empty($data['strategy_id'])) {
        $errors['strategy_id'] = 'Strategy must be selected';
    }
    
    if (empty($data['direction'])) {
        $errors['direction'] = 'Direction must be selected';
    }
    
    if (empty($data['session'])) {
        $errors['session'] = 'Session must be selected';
    }
    
    if (empty($data['trade_timestamp'])) {
        $errors['trade_timestamp'] = 'Trade time is required';
    }
    
    // If trade was taken, validate price fields
    if (($data['taken'] ?? '1') === '1') {
        if (empty($data['entry_price']) || !is_numeric($data['entry_price'])) {
            $errors['entry_price'] = 'Valid entry price is required';
        }
        
        if (empty($data['stop_loss_price']) || !is_numeric($data['stop_loss_price'])) {
            $errors['stop_loss_price'] = 'Valid stop loss price is required';
        }
        
        if (empty($data['exit_price']) || !is_numeric($data['exit_price'])) {
            $errors['exit_price'] = 'Valid exit price is required';
        }
        
        if (empty($data['risk_percent']) || !is_numeric($data['risk_percent'])) {
            $errors['risk_percent'] = 'Risk percentage is required';
        }
        
        // Check all entry criteria are checked
        if (empty($data['entry_criteria']) || !is_array($data['entry_criteria'])) {
            $errors['entry_criteria'] = 'All entry criteria must be checked';
        } else {
            // Verify all criteria for the strategy are checked
            $strategy_id = $data['strategy_id'];
            $db = db();
            $stmt = $db->prepare("SELECT COUNT(*) FROM entry_criteria WHERE strategy_id = ?");
            $stmt->execute([$strategy_id]);
            $total_criteria = $stmt->fetchColumn();
            
            if (count($data['entry_criteria']) < $total_criteria) {
                $errors['entry_criteria'] = 'All entry criteria must be checked before logging a trade';
            }
        }
    } else {
        // Trade was missed
        if (empty(trim($data['missed_reason'] ?? ''))) {
            $errors['missed_reason'] = 'Reason for missing the trade is required';
        }
    }
    
    return $errors;
}

/**
 * Validate uploaded file
 * @param array $file $_FILES array element
 * @param array $allowed_types
 * @param int $max_size
 * @return string|null Error message or null if valid
 */
function validate_upload($file, $allowed_types = ['image/jpeg', 'image/png', 'image/gif'], $max_size = null) {
    if ($max_size === null) {
        $max_size = MAX_UPLOAD_SIZE;
    }
    
    if ($file['error'] !== UPLOAD_ERR_OK) {
        return 'Upload failed with error code: ' . $file['error'];
    }
    
    if ($file['size'] > $max_size) {
        return 'File size exceeds maximum allowed size of ' . ($max_size / 1024 / 1024) . 'MB';
    }
    
    $finfo = finfo_open(FILEINFO_MIME_TYPE);
    $mime_type = finfo_file($finfo, $file['tmp_name']);
    finfo_close($finfo);
    
    if (!in_array($mime_type, $allowed_types)) {
        return 'File type not allowed. Allowed types: ' . implode(', ', $allowed_types);
    }
    
    return null;
}

/**
 * Validate date/time format
 * @param string $datetime
 * @param string $format
 * @return bool
 */
function validate_datetime($datetime, $format = 'Y-m-d H:i') {
    $d = DateTime::createFromFormat($format, $datetime);
    return $d && $d->format($format) === $datetime;
}