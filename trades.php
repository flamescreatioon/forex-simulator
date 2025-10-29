<?php

require_once 'includes/session.php';
require_once 'includes/db.php';
header('Content-Type: application/json');
// Don't leak PHP warnings/notices into JSON responses
@ini_set('display_errors', 0);
@error_reporting(E_ERROR | E_PARSE);

$session_id = session_id();

if(!isset($_SESSION['trades'])){
    $_SESSION['trades'] = [];
}

// Check if auto_trade is enabled (defaults already loaded)
$auto_trade = $_SESSION['auto_trade'];

// Only generate new trades if auto_trade is enabled
if ($auto_trade == 1) {
    $pairs = ['EUR/USD', 'GBP/USD', 'USD/JPY', 'AUD/USD', 'USD/CAD', 'USD/CHF', 'NZD/USD', 'GBP/JPY'];
    
    // Get settings (defaults already loaded)
    $lot_size = $_SESSION['default_lot_size'];
    $risk_percentage = $_SESSION['risk_percentage'];
    $balance = $_SESSION['balance'];
    $risk_amount = ($balance * $risk_percentage) / 100;
    
    // Calculate profit based on risk (can be positive or negative)
    $profit = rand(-$risk_amount, $risk_amount * 2);
    
    $new_trade = [
        'pair' => $pairs[array_rand($pairs)],
        'lot_size' => $lot_size,
        'profit' => round($profit, 2),
        'timestamp' => date('H:i:s')
    ];

    array_unshift($_SESSION['trades'], $new_trade);
    $_SESSION['trades'] = array_slice($_SESSION['trades'], 0, 10);

    // Persist to DB first; failures should not corrupt the JSON body
    try {
        if (isset($pdo)) {
            $stmt = $pdo->prepare("INSERT INTO trades (session_id, pair, profit) VALUES (?, ?, ?)");
            $stmt->execute([$session_id, $new_trade['pair'], $new_trade['profit']]);
        }
    } catch (Throwable $e) {
        // Swallow DB errors for this lightweight demo endpoint
    }
}

// Now output JSON response only
echo json_encode($_SESSION['trades']);