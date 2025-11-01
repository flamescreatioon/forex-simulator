<?php

if(session_status() === PHP_SESSION_NONE){
    session_start();
}

// Load default parameters (can be overridden by set_params.php)
require_once __DIR__ . '/defaults.php';

// Try to load database connection and helper, but continue without it if it fails
$db = null;
$pdo = null;

try {
    // Suppress errors for database connection
    @require_once __DIR__ . '/db.php';
    @require_once __DIR__ . '/database.php';

    // Only initialize if PDO connection was successful
    if (isset($pdo) && $pdo instanceof PDO) {
        $db = new Database($pdo, session_id());
        
        // Initialize session in database if not exists
        $db->initSession($_SESSION['balance'] ?? 10000.00);
        
        // Load settings from database if available
        $saved_settings = $db->getSettings();
        if ($saved_settings) {
            // Merge database settings into session
            foreach ($saved_settings as $key => $value) {
                if ($key !== 'id' && $key !== 'session_id' && $key !== 'created_at' && $key !== 'updated_at') {
                    $_SESSION[$key] = $value;
                }
            }
        }
    }
} catch (Exception $e) {
    // Database connection failed - continue in session-only mode
    // This allows the app to work even when DB connection limit is exceeded
    $db = null;
    $pdo = null;
    // Optionally log the error (comment out in production if too noisy)
    // error_log("DB unavailable (session-only mode): " . $e->getMessage());
}
