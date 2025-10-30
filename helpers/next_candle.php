<?php
@ini_set('display_errors', 0);
@error_reporting(E_ERROR | E_PARSE);

// Start session quietly
if (session_status() === PHP_SESSION_NONE) {
    @session_start();
}

header('Content-Type: application/json');

// Resolve requested pair (default to first configured or EUR/USD)
$requestedPair = isset($_GET['pair']) ? strtoupper(str_replace(' ', '', $_GET['pair'])) : '';
if ($requestedPair && strpos($requestedPair, '/') === false && strlen($requestedPair) === 6) {
    $requestedPair = substr($requestedPair, 0, 3) . '/' . substr($requestedPair, 3);
}
$pairsList = isset($_SESSION['pairs']) && is_array($_SESSION['pairs']) ? $_SESSION['pairs'] : ['EUR/USD'];
if ($requestedPair === '' || !in_array($requestedPair, $pairsList, true)) {
    $requestedPair = $pairsList[0];
}
$pairKey = str_replace('/', '_', $requestedPair);
if (!isset($_SESSION['chart_state'])) { $_SESSION['chart_state'] = []; }

// Helper function to generate a single candle with 70% up-bias and mean reversion
function generateCandle($base, $time) {
    global $pairKey;
    $volatility = isset($_SESSION['price_volatility']) ? floatval($_SESSION['price_volatility']) : 1.0;
    if ($volatility <= 0) $volatility = 1.0;

    $anchor = isset($_SESSION['chart_state'][$pairKey]['anchor']) ? floatval($_SESSION['chart_state'][$pairKey]['anchor']) : $base;
    $channel_width = isset($_SESSION['channel_width']) ? floatval($_SESSION['channel_width']) : 0.015; // ~150 pips

    $ratio = 0.0;
    if ($channel_width > 0) {
        $ratio = ($base - $anchor) / $channel_width;
        if ($ratio > 1.5) $ratio = 1.5;
        if ($ratio < -1.5) $ratio = -1.5;
    }

    $upProb = 0.7 - 0.4 * $ratio; // soften when above anchor
    if ($upProb < 0.3) $upProb = 0.3;
    if ($upProb > 0.9) $upProb = 0.9;
    $isUp = (mt_rand() / mt_getrandmax()) <= $upProb;

    $magnitudeScale = max(0.3, 1.0 - 0.6 * abs($ratio));
    $pips = mt_rand(3, 12) * $volatility * $magnitudeScale;
    $move = ($pips * 0.0001) * ($isUp ? 1 : -1);

    $open = $base;
    $close = $open + $move;

    $wickUp = (mt_rand(1, 8) * 0.0001) * $volatility * $magnitudeScale;
    $wickDown = (mt_rand(1, 8) * 0.0001) * $volatility * $magnitudeScale;
    $high = max($open, $close) + $wickUp;
    $low = min($open, $close) - $wickDown;

    // update anchor (EMA)
    $anchor = 0.99 * $anchor + 0.01 * $close;
    $_SESSION['chart_state'][$pairKey]['anchor'] = $anchor;

    return [
        'time' => $time,
        'open' => round($open, 5),
        'high' => round($high, 5),
        'low' => round($low, 5),
        'close' => round($close, 5),
    ];
}

// Initialize candle data if not exists
if (!isset($_SESSION['chart_state'][$pairKey]['data']) || !isset($_SESSION['chart_state'][$pairKey]['base_price'])) {
    // Return empty - client should load initial candles first
    echo json_encode(['error' => 'No initial data']);
    exit;
}

// Get simulation speed to determine candle generation frequency
$sim_speed = $_SESSION['sim_speed'] ?? 'normal';
$speed_intervals = [
    'slow' => 10,    // Generate new candle every 10 seconds
    'normal' => 5,   // Generate new candle every 5 seconds  
    'fast' => 2      // Generate new candle every 2 seconds
];

// Check if enough time has passed to generate a new candle
$last_update = $_SESSION['last_candle_update'] ?? 0;
$current_time = time();
$required_interval = $speed_intervals[$sim_speed] ?? 5;

if (($current_time - $last_update) < $required_interval) {
    // Not time yet - return the most recent candle
    $data = $_SESSION['candle_data'];
    echo json_encode(end($data));
    exit;
}

// Time to generate a new candle
$tfSeconds = isset($_SESSION['chart_tf_seconds']) ? intval($_SESSION['chart_tf_seconds']) : 60;
if ($tfSeconds <= 0) { $tfSeconds = 60; }
$base = $_SESSION['chart_state'][$pairKey]['base_price'];
$last_time = $_SESSION['chart_state'][$pairKey]['last_time'] ?? time();
$new_time = $last_time + $tfSeconds; // Each candle advances by timeframe seconds

// Generate new candle
$newCandle = generateCandle($base, $new_time);

// Add to session data
$_SESSION['chart_state'][$pairKey]['data'][] = $newCandle;

// Keep only last 200 candles to prevent memory bloat
if (count($_SESSION['chart_state'][$pairKey]['data']) > 200) {
    $_SESSION['chart_state'][$pairKey]['data'] = array_slice($_SESSION['chart_state'][$pairKey]['data'], -200);
}

// Update state
$_SESSION['chart_state'][$pairKey]['base_price'] = $newCandle['close'];
$_SESSION['chart_state'][$pairKey]['last_time'] = $new_time;
$_SESSION['chart_state'][$pairKey]['last_update'] = $current_time;

// Clear any output buffers that might have errors
while (ob_get_level()) {
    ob_end_clean();
}

// Return the new candle
echo json_encode($newCandle);
exit;
?>
