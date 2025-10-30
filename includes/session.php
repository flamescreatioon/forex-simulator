<?php

if(session_status() === PHP_SESSION_NONE){
    session_start();
}

// Load default parameters (can be overridden by set_params.php)
require_once __DIR__ . '/defaults.php';

// Load database connection and helper
require_once __DIR__ . '/db.php';
require_once __DIR__ . '/database.php';

// Initialize database helper
$db = new Database($pdo, session_id());

// Initialize session in database if not exists
try {
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
} catch (Exception $e) {
    // Database not migrated yet or connection issue - continue with session-only mode
    error_log("Database initialization failed: " . $e->getMessage());
}
