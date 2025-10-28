<?php

session_start();
/* require_once 'includes/db.php'; */
$session_id = session_id();

header('Content-Type: application/json');

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

/* 
$trades = [];

for ($i = 0; $i < 8; $i++) {
    $entry = 3077.61;
    $current = 3569.59;
    $profit = 9830 + rand(0, 20);

    $trades[] = [
        "pair" => "Volatility 75 (1s) Index",
        "lot" => 20,
        "entry" => number_format($entry, 2),
        "current" => number_format($current, 2),
        "profit" => number_format($profit, 2),
    ];
}

echo json_encode($trades);
 */
/* $stmt = $pdo->prepare("INSERT INTO trades (session_id, pair, profit) VALUES (?, ?, ?)");
$stmt->execute([$session_id, $new_trade['pair'], $new_trade['profit']]); */