<?php
/**
 * CSRF Protection utilities
 */

/**
 * Generate a CSRF token and store it in session
 * @return string The generated CSRF token
 */
function csrf_token() {
    if (!isset($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

/**
 * Verify a CSRF token
 * @param string $token The token to verify
 * @return bool True if valid, false otherwise
 */
function verify_csrf($token) {
    if (!isset($_SESSION['csrf_token'])) {
        return false;
    }
    return hash_equals($_SESSION['csrf_token'], $token);
}

/**
 * Generate a CSRF token field for forms
 * @return string HTML hidden input field
 */
function csrf_field() {
    return '<input type="hidden" name="csrf_token" value="' . htmlspecialchars(csrf_token()) . '">';
}

/**
 * Validate CSRF token from POST request
 * Dies with error if invalid
 */
function validate_csrf() {
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        if (!isset($_POST['csrf_token']) || !verify_csrf($_POST['csrf_token'])) {
            http_response_code(403);
            die('CSRF token validation failed');
        }
    }
}

/**
 * Regenerate CSRF token (useful after successful form submission)
 */
function regenerate_csrf() {
    unset($_SESSION['csrf_token']);
    return csrf_token();
}