<?php

session_start();

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