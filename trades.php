<?php

session_start();
require_once 'includes/db.php';
header('Content-Type: application/json');
// Don't leak PHP warnings/notices into JSON responses
@ini_set('display_errors', 0);
@error_reporting(E_ERROR | E_PARSE);

$session_id = session_id();

if(!isset($_SESSION['trades'])){
    $_SESSION['trades'] = [];
}

$pairs = ['EUR/USD', 'GBP/USD', 'USD/JPY', 'AUD/USD', 'USD/CAD', 'USD/CHF', 'NZD/USD', 'GBP/JPY'];

$new_trade = [
    'pair' => $pairs[array_rand($pairs)],
    'profit' => rand(50, 500),
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

// Now output JSON response only
echo json_encode($_SESSION['trades']);