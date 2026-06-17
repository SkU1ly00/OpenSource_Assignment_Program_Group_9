<?php
/**
 * Utility Functions
 * Common functions used throughout the application
 */

/**
 * Sanitize input data
 */
function sanitizeInput($data) {
    if (is_array($data)) {
        return array_map('sanitizeInput', $data);
    }
    return htmlspecialchars(trim($data), ENT_QUOTES, 'UTF-8');
}

/**
 * Validate email
 */
function validateEmail($email) {
    return filter_var($email, FILTER_VALIDATE_EMAIL);
}

/**
 * Validate password strength
 */
function validatePassword($password) {
    if (strlen($password) < PASSWORD_MIN_LENGTH) {
        return false;
    }
    return true;
}

/**
 * Format date
 */
function formatDate($date, $format = DATE_FORMAT) {
    if (empty($date) || $date === '0000-00-00' || $date === '0000-00-00 00:00:00') {
        return 'N/A';
    }
    try {
        $datetime = new DateTime($date);
        return $datetime->format($format);
    } catch (Exception $e) {
        return $date;
    }
}

/**
 * Format datetime
 */
function formatDateTime($datetime, $format = DATETIME_FORMAT) {
    if (empty($datetime) || $datetime === '0000-00-00 00:00:00') {
        return 'N/A';
    }
    try {
        $dt = new DateTime($datetime);
        return $dt->format($format);
    } catch (Exception $e) {
        return $datetime;
    }
}

/**
 * Check if user is logged in
 */
function isUserLoggedIn() {
    return isset($_SESSION['user_id']) && isset($_SESSION['username']);
}

/**
 * Get current user
 */
function getCurrentUser() {
    if (isUserLoggedIn()) {
        return [
            'id' => $_SESSION['user_id'],
            'username' => $_SESSION['username'],
            'email' => $_SESSION['email'],
            'role' => $_SESSION['role']
        ];
    }
    return null;
}

/**
 * Check if user has role
 */
function userHasRole($role) {
    if (isUserLoggedIn()) {
        return $_SESSION['role'] === $role;
    }
    return false;
}

/**
 * Check if user is admin
 */
function isAdmin() {
    return userHasRole('Admin');
}

/**
 * Redirect to URL
 */
function redirect($url) {
    header('Location: ' . $url);
    exit();
}

/**
 * Set flash message
 */
function setFlashMessage($key, $message) {
    $_SESSION['flash_messages'][$key] = $message;
}

/**
 * Get flash message
 */
function getFlashMessage($key) {
    if (isset($_SESSION['flash_messages'][$key])) {
        $message = $_SESSION['flash_messages'][$key];
        unset($_SESSION['flash_messages'][$key]);
        return $message;
    }
    return null;
}

/**
 * Generate CSRF token
 */
function generateCSRFToken() {
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

/**
 * Verify CSRF token
 */
function verifyCSRFToken($token) {
    if (empty($_SESSION['csrf_token']) || $token !== $_SESSION['csrf_token']) {
        return false;
    }
    return true;
}

/**
 * Get file size in KB/MB
 */
function getFileSizeFormatted($bytes) {
    $units = ['B', 'KB', 'MB', 'GB'];
    $bytes = max($bytes, 0);
    $pow = floor(($bytes ? log($bytes) : 0) / log(1024));
    $pow = min($pow, count($units) - 1);
    $bytes /= (1 << (10 * $pow));
    return round($bytes, 2) . ' ' . $units[$pow];
}

/**
 * Export data to CSV
 */
function exportToCSV($data, $filename) {
    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment; filename="' . $filename . '"');
    
    $output = fopen('php://output', 'w');
    
    if (!empty($data)) {
        // Write headers
        fputcsv($output, array_keys($data[0]));
        
        // Write data
        foreach ($data as $row) {
            fputcsv($output, $row);
        }
    }
    
    fclose($output);
    exit();
}

/**
 * Get incident severity color
 */
function getSeverityColor($severity) {
    $colors = [
        'Critical' => '#FF0000',
        'High' => '#FF6600',
        'Medium' => '#FFCC00',
        'Low' => '#00CC00'
    ];
    return $colors[$severity] ?? '#000000';
}

/**
 * Get incident status badge class
 */
function getStatusBadgeClass($status) {
    $classes = [
        'Open' => 'badge-warning',
        'In Progress' => 'badge-info',
        'Resolved' => 'badge-success',
        'Closed' => 'badge-secondary'
    ];
    return $classes[$status] ?? 'badge-light';
}

/**
 * Truncate string
 */
function truncateString($string, $length = 100) {
    if (strlen($string) > $length) {
        return substr($string, 0, $length) . '...';
    }
    return $string;
}

/**
 * Log error
 */
function logError($message) {
    $log_file = LOG_DIR . 'error_' . date('Y-m-d') . '.log';
    $timestamp = date('Y-m-d H:i:s');
    $log_message = "[$timestamp] $message\n";
    error_log($log_message, 3, $log_file);
}

/**
 * Get incident type badge
 */
function getIncidentTypeBadge($type) {
    $badges = [
        'Malware Infection' => 'danger',
        'Phishing Attack' => 'warning',
        'Unauthorized Access' => 'danger',
        'Data Breach' => 'danger',
        'Denial of Service' => 'warning',
        'System Failure' => 'warning',
        'Network Intrusion' => 'danger',
        'Password Compromise' => 'danger',
        'Configuration Error' => 'warning',
        'Third-party Incident' => 'info',
        'Insider Threat' => 'danger',
        'Vulnerability Exploitation' => 'danger',
        'Other' => 'secondary'
    ];
    return $badges[$type] ?? 'secondary';
}
?>