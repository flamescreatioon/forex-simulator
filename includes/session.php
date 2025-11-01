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
        
        // Only load settings from database if session doesn't have them yet
        // This prevents database values from overwriting newly saved session values
        // Check if key session variables are missing - if so, load from DB
        $needs_db_load = !isset($_SESSION['balance']) || 
                         !isset($_SESSION['default_lot_size']) || 
                         !isset($_SESSION['sim_speed']);
        
        if ($needs_db_load) {
            // Load settings from database if available
            $saved_settings = $db->getSettings();
            if ($saved_settings) {
                // Merge database settings into session
                foreach ($saved_settings as $key => $value) {
                    if ($key !== 'id' && $key !== 'session_id' && $key !== 'created_at' && $key !== 'updated_at') {
                        // Only set if not already in session
                        if (!isset($_SESSION[$key])) {
                            $_SESSION[$key] = $value;
                        }
                    }
                }
            }
            
            // Also try to load session stats from database
            $session_data = $db->getSessionData();
            if ($session_data) {
                // Only load balance/equity if not already set
                if (!isset($_SESSION['balance'])) $_SESSION['balance'] = $session_data['balance'];
                if (!isset($_SESSION['equity'])) $_SESSION['equity'] = $session_data['equity'];
                if (!isset($_SESSION['margin'])) $_SESSION['margin'] = $session_data['margin'];
                if (!isset($_SESSION['freemargin'])) $_SESSION['free_margin'] = $session_data['free_margin'];
                if (!isset($_SESSION['marginlevel'])) $_SESSION['margin_level'] = $session_data['margin_level'];
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
