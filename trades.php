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

// --- Helper functions ---
function is_jpy_pair($pair) {
    return strpos($pair, 'JPY') !== false;
}

function gen_entry_price($pair) {
    if (is_jpy_pair($pair)) {
        return round(mt_rand(100000, 160000) / 1000, 3); // 100.000 - 160.000
    }
    return round(mt_rand(10000, 15000) / 10000, 4); // 1.0000 - 1.5000
}

function price_from_pips($pair, $price, $pips) {
    if (is_jpy_pair($pair)) {
        return round($price + ($pips * 0.001), 3);
    }
    return round($price + ($pips * 0.0001), 4);
}

function pips_between($pair, $a, $b) {
    if (is_jpy_pair($pair)) {
        return (int)round(($b - $a) / 0.001);
    }
    return (int)round(($b - $a) / 0.0001);
}

function pip_value_usd($pair) {
    return 10.0; // $10 per pip for 1.0 lot (simplified)
}

// --- Settings ---
$pairs = (isset($_SESSION['pairs']) && is_array($_SESSION['pairs']) && !empty($_SESSION['pairs']))
    ? $_SESSION['pairs']
    : ['EUR/USD', 'GBP/USD', 'USD/JPY', 'AUD/USD', 'USD/CAD', 'USD/CHF', 'NZD/USD', 'GBP/JPY'];
$lot_size = floatval($_SESSION['default_lot_size'] ?? 0.01);
$risk_percentage = floatval($_SESSION['risk_percentage'] ?? 2.0);
$balance = floatval($_SESSION['balance'] ?? 10000);
$risk_amount = max(1.0, ($balance * $risk_percentage) / 100);
$positions_count = intval($_SESSION['positions_count'] ?? ($_SESSION['max_positions'] ?? 5));
$profit_bias = floatval($_SESSION['profit_bias'] ?? 0.75); // 0..1
$profit_scale = floatval($_SESSION['profit_scale'] ?? 1.0);
$auto_trade = intval($_SESSION['auto_trade'] ?? 0);
$sl_pips_default = intval($_SESSION['stop_loss_pips'] ?? 20);
$tp_pips_default = intval($_SESSION['take_profit_pips'] ?? 40);
$trailing_stop = intval($_SESSION['trailing_stop'] ?? 0) === 1;
$volatility = max(0.1, floatval($_SESSION['price_volatility'] ?? 1.0));

// Enrich existing trades
foreach ($_SESSION['trades'] as &$t) {
    if (!isset($t['id'])) { $t['id'] = uniqid('pos_', true); }
    if (!isset($t['pair'])) { $t['pair'] = $pairs[array_rand($pairs)]; }
    if (!isset($t['type'])) { $t['type'] = (mt_rand(0, 100) < 60) ? 'buy' : 'sell'; }
    if (!isset($t['lot_size'])) { $t['lot_size'] = $lot_size; }
    if (!isset($t['entry'])) { $t['entry'] = gen_entry_price($t['pair']); }
    if (!isset($t['current'])) { $t['current'] = $t['entry']; }
    if (!isset($t['sl'])) { $t['sl'] = price_from_pips($t['pair'], $t['entry'], ($t['type']==='buy' ? -$sl_pips_default : $sl_pips_default)); }
    if (!isset($t['tp'])) { $t['tp'] = price_from_pips($t['pair'], $t['entry'], ($t['type']==='buy' ? $tp_pips_default : -$tp_pips_default)); }
    if (!isset($t['open_time'])) { $t['open_time'] = date('Y-m-d H:i:s'); }
    if (!isset($t['profit'])) { $t['profit'] = 0.0; }
    // Calculate amount (lot_size in standard lots × 100,000)
    $t['amount'] = number_format($t['lot_size'] * 100000, 0, '.', ',');
}
unset($t);

// Create positions up to desired count (generate only if auto_trade enabled or none exist yet)
if ($auto_trade == 1 || count($_SESSION['trades']) === 0) {
    while (count($_SESSION['trades']) < $positions_count) {
        $pair = $pairs[array_rand($pairs)];
        $type = (mt_rand(0, 100) < 60) ? 'buy' : 'sell';
        $entry = gen_entry_price($pair);
        $positive = (mt_rand() / mt_getrandmax()) < $profit_bias;
        $seed_pips = ($positive ? 1 : -1) * mt_rand(3, 25);
        $current = price_from_pips($pair, $entry, $seed_pips);
        $pips = pips_between($pair, $entry, $current) * ($type === 'buy' ? 1 : -1);
        $profit = $pips * pip_value_usd($pair) * $lot_size * $profit_scale;

        array_unshift($_SESSION['trades'], [
            'id' => uniqid('pos_', true),
            'pair' => $pair,
            'type' => $type,
            'lot_size' => $lot_size,
            'entry' => $entry,
            'current' => $current,
            'pips' => $pips,
            'sl' => price_from_pips($pair, $entry, ($type==='buy' ? -$sl_pips_default : $sl_pips_default)),
            'tp' => price_from_pips($pair, $entry, ($type==='buy' ? $tp_pips_default : -$tp_pips_default)),
            'open_time' => date('Y-m-d H:i:s'),
            'profit' => round($profit, 2),
            'timestamp' => date('H:i:s'),
            'amount' => number_format($lot_size * 100000, 0, '.', ',')
        ]);
    }
    $_SESSION['trades'] = array_slice($_SESSION['trades'], 0, $positions_count);
}

// Update each position with biased drift
foreach ($_SESSION['trades'] as &$t) {
    $pair = $t['pair'];
    $type = $t['type'];
    $entry = floatval($t['entry']);
    $current = floatval($t['current']);

    $positive = (mt_rand() / mt_getrandmax()) < $profit_bias; // push towards profit
    $dir = ($type === 'buy') ? ($positive ? 1 : -1) : ($positive ? -1 : 1);
    $step_pips = $dir * mt_rand(1, (int)max(2, 5 * $volatility));
    $new_current = price_from_pips($pair, $current, $step_pips);
    $t['current'] = $new_current;

    // Trailing stop adjustment
    $pips_from_entry_signed = pips_between($pair, $entry, $new_current) * ($type==='buy' ? 1 : -1);
    if ($trailing_stop && $pips_from_entry_signed > 0) {
        $trail = (int)floor($pips_from_entry_signed / 2);
        $trail = max(0, min($trail, $tp_pips_default - 1));
        $new_sl_pips = ($type==='buy' ? -$sl_pips_default + $trail : $sl_pips_default - $trail);
        $t['sl'] = price_from_pips($pair, $entry, $new_sl_pips);
    }

    $pips_signed_for_profit = pips_between($pair, $entry, $new_current) * ($type==='buy' ? 1 : -1);
    $profit = $pips_signed_for_profit * pip_value_usd($pair) * $t['lot_size'] * $profit_scale;
    $t['profit'] = round($profit, 2);
    $t['pips'] = $pips_signed_for_profit;
    $t['timestamp'] = date('H:i:s');
}
unset($t);

// Best-effort DB write of last change
try {
    if (isset($pdo) && !empty($_SESSION['trades'])) {
        $last = $_SESSION['trades'][0];
        $stmt = $pdo->prepare("INSERT INTO trades (session_id, pair, profit) VALUES (?, ?, ?)");
        $stmt->execute([$session_id, $last['pair'], $last['profit']]);
    }
} catch (Throwable $e) {
    // ignore for demo
}

echo json_encode($_SESSION['trades']);