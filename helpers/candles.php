<?php
header('Content-Type: application/json');

// Simulate live price feed
$base = 3077.00;
$data = [];
$time = time();

for ($i = 0; $i < 100; $i++) {
    $open = $base + rand(-30, 30) / 10;
    $close = $open + rand(-20, 20) / 10;
    $high = max($open, $close) + rand(0, 10) / 10;
    $low = min($open, $close) - rand(0, 10) / 10;

    $data[] = [
        "time" => $time - (100 - $i) * 60,
        "open" => round($open, 2),
        "high" => round($high, 2),
        "low" => round($low, 2),
        "close" => round($close, 2),
    ];

    $base = $close;
}

echo json_encode($data);
?>
