<?php

session_start();
/* require_once 'includes/db.php'; */
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

echo json_encode($_SESSION['trades']);

/* $stmt = $pdo->prepare("INSERT INTO trades (session_id, pair, profit) VALUES (?, ?, ?)");
$stmt->execute([$session_id, $new_trade['pair'], $new_trade['profit']]); */