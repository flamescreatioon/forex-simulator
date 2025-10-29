<?php
@ini_set('display_errors', 0);
@error_reporting(E_ERROR | E_PARSE);

// Start session quietly
if (session_status() === PHP_SESSION_NONE) {
    @session_start();
}

header('Content-Type: application/json');

// Helper function to generate a single candle with 70% up-bias and mean reversion to prevent runaway
function generateCandle($base, $time) {
    // Volatility scale (from settings if available)
    $volatility = isset($_SESSION['price_volatility']) ? floatval($_SESSION['price_volatility']) : 1.0;
    if ($volatility <= 0) $volatility = 1.0;

    // Mean-reversion anchor and channel
    $anchor = isset($_SESSION['candle_anchor']) ? floatval($_SESSION['candle_anchor']) : $base;
    $channel_width = isset($_SESSION['channel_width']) ? floatval($_SESSION['channel_width']) : 0.015; // ~150 pips for EURUSD

    // Distance from anchor, normalized
    $ratio = 0.0;
    if ($channel_width > 0) {
        $ratio = ($base - $anchor) / $channel_width; // -1..1 within channel
        // clamp
        if ($ratio > 1.5) $ratio = 1.5;
        if ($ratio < -1.5) $ratio = -1.5;
    }

    // Upward probability with soft mean reversion (less up-prob when above anchor)
    $upProb = 0.7 - 0.4 * $ratio; // if ratio>0 reduce up prob
    if ($upProb < 0.3) $upProb = 0.3;
    if ($upProb > 0.9) $upProb = 0.9;

    $isUp = (mt_rand() / mt_getrandmax()) <= $upProb;

    // Base movement in pips (3-12) scaled by volatility and damped near channel edges
    $magnitudeScale = max(0.3, 1.0 - 0.6 * abs($ratio));
    $pips = mt_rand(3, 12) * $volatility * $magnitudeScale; // 3..12 pips typical
    $move = ($pips * 0.0001) * ($isUp ? 1 : -1);

    $open = $base;
    $close = $open + $move;

    // Wicks: small random above/below, ensure high>=max(open,close), low<=min
    $wickUp = (mt_rand(1, 8) * 0.0001) * $volatility * $magnitudeScale;
    $wickDown = (mt_rand(1, 8) * 0.0001) * $volatility * $magnitudeScale;
    $high = max($open, $close) + $wickUp;
    $low = min($open, $close) - $wickDown;

    // Slowly move anchor toward current price (EMA) to keep trend realistic
    $anchor = 0.99 * $anchor + 0.01 * $close;
    $_SESSION['candle_anchor'] = $anchor;

    return [
        'time' => $time,
        'open' => round($open, 5),
        'high' => round($high, 5),
        'low' => round($low, 5),
        'close' => round($close, 5),
    ];
}

// Initialize or retrieve persistent candle state
if (!isset($_SESSION['candle_data'])) {
    // First time - generate initial candles
    $base = 1.08500; // EUR/USD starting price
    $data = [];
    $time = time() - (100 * 60); // Start 100 minutes ago
    
    for ($i = 0; $i < 100; $i++) {
        $data[] = generateCandle($base, $time);
        $base = $data[$i]['close'];
        $time += 60; // 1 minute per candle
    }
    
    $_SESSION['candle_data'] = $data;
    $_SESSION['last_candle_time'] = $time;
    $_SESSION['candle_base_price'] = $base;
} else {
    // Retrieve existing candles
    $data = $_SESSION['candle_data'];
}

// Clear any output buffers that might have errors
while (ob_get_level()) {
    ob_end_clean();
}

// Send only JSON
echo json_encode($data);
exit;
?>