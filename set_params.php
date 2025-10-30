<?php

require_once 'includes/session.php';

if($_SERVER['REQUEST_METHOD'] === 'POST'){
    // Account Parameters - Override defaults
    $balance = floatval($_POST['balance']);
    $profit = floatval($_POST['profit']);
    $leverage = floatval($_POST['leverage']);
    $margin = floatval($_POST['margin']);
    $goal = floatval($_POST['goal']);
    $freemargin = floatval($_POST['freemargin']);
    $marginlevel = floatval($_POST['marginlevel']);
    $equity = floatval($_POST['equity']);

    // Trading Parameters
    $default_lot_size = floatval($_POST['default_lot_size'] ?? 0.01);
    $max_positions = intval($_POST['max_positions'] ?? 5);
    $risk_percentage = floatval($_POST['risk_percentage'] ?? 2);
    
    // Simulation Settings
    $sim_speed = $_POST['sim_speed'] ?? 'normal';
    $price_volatility = floatval($_POST['price_volatility'] ?? 1.0);
    $auto_trade = isset($_POST['auto_trade']) ? 1 : 0;

    // Chart Settings
    $chart_timeframe = $_POST['chart_timeframe'] ?? ($_SESSION['chart_timeframe'] ?? '1m');
    // Map timeframe to seconds
    $tf_map = [ '30s' => 30, '1m' => 60, '5m' => 300, '15m' => 900, '1h' => 3600 ];
    $chart_tf_seconds = $tf_map[$chart_timeframe] ?? 60;

    // Instruments/Pairs list (comma or newline separated)
    $pairs_text = $_POST['pairs'] ?? '';
    $pieces = preg_split('/[\r\n,]+/', $pairs_text);
    $pairs = [];
    foreach ($pieces as $s) {
        $s = trim($s);
        if ($s === '') continue;
        $s = strtoupper(str_replace(' ', '', $s));
        if (strpos($s, '/') === false && strlen($s) === 6) {
            $s = substr($s, 0, 3) . '/' . substr($s, 3);
        }
        if ($s !== '') $pairs[] = $s;
    }
    if (empty($pairs)) {
        $pairs = $_SESSION['pairs'] ?? ['EUR/USD','GBP/USD','USD/JPY','AUD/USD','USD/CAD','USD/CHF','NZD/USD','GBP/JPY'];
    }
    
    // Position Management
    $stop_loss_pips = floatval($_POST['stop_loss_pips'] ?? 50);
    $take_profit_pips = floatval($_POST['take_profit_pips'] ?? 100);
    $trailing_stop = isset($_POST['trailing_stop']) ? 1 : 0;

    // Positions Simulation Controls
    $positions_count = intval($_POST['positions_count'] ?? ($_SESSION['positions_count'] ?? ($_SESSION['max_positions'] ?? 5)));
    $profit_bias_input = $_POST['profit_bias'] ?? null;
    if ($profit_bias_input !== null) {
        $profit_bias = max(0.0, min(1.0, floatval($profit_bias_input) / 100.0));
    } else {
        $profit_bias = $_SESSION['profit_bias'] ?? 0.75;
    }
    $profit_scale = max(0.1, min(10.0, floatval($_POST['profit_scale'] ?? ($_SESSION['profit_scale'] ?? 1.0))));

    if($balance <= 0 || $goal <= 0){
        $error = "Please enter valid numbers for balance and goal.";
    } else {
        // Store account parameters
        $_SESSION["balance"] = $balance;
        $_SESSION["profit"] = $profit;
        $_SESSION["leverage"] = $leverage;
        $_SESSION["margin"] = $margin;
        $_SESSION["goal"] = $goal;
        $_SESSION["freemargin"] = $freemargin;
        $_SESSION["marginlevel"] = $marginlevel;
        $_SESSION["equity"] = $equity;
        
        // Store trading parameters
        $_SESSION["default_lot_size"] = $default_lot_size;
        $_SESSION["max_positions"] = $max_positions;
        $_SESSION["risk_percentage"] = $risk_percentage;
        
        // Store simulation settings
        $_SESSION["sim_speed"] = $sim_speed;
        $_SESSION["price_volatility"] = $price_volatility;
        $_SESSION["auto_trade"] = $auto_trade;
    $_SESSION["chart_timeframe"] = $chart_timeframe;
    $_SESSION["chart_tf_seconds"] = $chart_tf_seconds;
    $_SESSION["pairs"] = $pairs;
        
        // Store position management
        $_SESSION["stop_loss_pips"] = $stop_loss_pips;
        $_SESSION["take_profit_pips"] = $take_profit_pips;
        $_SESSION["trailing_stop"] = $trailing_stop;

    // Store positions simulation controls
    $_SESSION["positions_count"] = $positions_count;
    $_SESSION["profit_bias"] = $profit_bias;
    $_SESSION["profit_scale"] = $profit_scale;
        
        // Initialize empty positions and trades if not set
        if (!isset($_SESSION['positions'])) {
            $_SESSION['positions'] = [];
        }
        if (!isset($_SESSION['trades'])) {
            $_SESSION['trades'] = [];
        }

        // Store settings in cookie for JavaScript access
    setcookie('sim_speed', $sim_speed, time() + (86400 * 30), "/");
    setcookie('chart_timeframe', $chart_timeframe, time() + (86400 * 30), "/");
    // Persist pairs to cookie as comma-separated for frontend JS
    @setcookie('pairs', implode(',', $pairs), time() + (86400 * 30), "/");
        setcookie('price_volatility', $price_volatility, time() + (86400 * 30), "/");

        header("Location: index.php");
        exit();
    }
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Settings</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-white text-gray-800">

    <div class="max-w-lg mx-auto bg-white mt-20 p-8" style="margin-bottom: 80px;">
        <h1 class="text-2xl font-semibold text-center mb-6 text-blue-600">Settings</h1>

        <?php if (!empty($error)): ?>
            <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-2 rounded mb-4">
                <?= htmlspecialchars($error) ?>
            </div>
        <?php endif; ?>

        <form method="POST" action="">
            <!-- Account Information Section -->
            <div class="mb-6">
                <h2 class="text-lg font-semibold mb-3 text-gray-700 border-b pb-2">Account Information</h2>
                
                <div class="mb-4">
                    <label class="block mb-2 font-medium text-sm">Starting Balance ($)</label>
                    <input type="number" name="balance" step="0.01" value="<?= $_SESSION['balance'] ?? 10000 ?>" class="w-full p-2 border rounded-lg" required>
                </div>

                <div class="mb-4">
                    <label class="block mb-2 font-medium text-sm">Current Profit/Loss ($)</label>
                    <input type="number" name="profit" step="0.01" value="<?= $_SESSION['profit'] ?? 0 ?>" class="w-full p-2 border rounded-lg">
                    <small class="text-gray-500">Starting profit (can be negative)</small>
                </div>

                <div class="mb-4">
                    <label class="block mb-2 font-medium text-sm">Equity ($)</label>
                    <input type="number" name="equity" step="0.01" value="<?= $_SESSION['equity'] ?? 10000 ?>" class="w-full p-2 border rounded-lg">
                    <small class="text-gray-500">Balance + Floating P/L</small>
                </div>

                <div class="mb-4">
                    <label class="block mb-2 font-medium text-sm">Profit Goal ($)</label>
                    <input type="number" name="goal" step="0.01" value="<?= $_SESSION['goal'] ?? 20000 ?>" class="w-full p-2 border rounded-lg" required>
                </div>
            </div>

            <!-- Margin & Leverage Section -->
            <div class="mb-6">
                <h2 class="text-lg font-semibold mb-3 text-gray-700 border-b pb-2">Margin & Leverage</h2>
                
                <div class="mb-4">
                    <label class="block mb-2 font-medium text-sm">Leverage Ratio (e.g., 100)</label>
                    <input type="number" name="leverage" value="<?= $_SESSION['leverage'] ?? 100 ?>" class="w-full p-2 border rounded-lg">
                    <small class="text-gray-500">1:100 means $1 controls $100</small>
                </div>

                <div class="mb-4">
                    <label class="block mb-2 font-medium text-sm">Used Margin ($)</label>
                    <input type="number" name="margin" step="0.01" value="<?= $_SESSION['margin'] ?? 1000 ?>" class="w-full p-2 border rounded-lg">
                    <small class="text-gray-500">Margin locked in open positions</small>
                </div>

                <div class="mb-4">
                    <label class="block mb-2 font-medium text-sm">Free Margin ($)</label>
                    <input type="number" name="freemargin" step="0.01" value="<?= $_SESSION['freemargin'] ?? 9000 ?>" class="w-full p-2 border rounded-lg">
                    <small class="text-gray-500">Available margin for new trades</small>
                </div>

                <div class="mb-4">
                    <label class="block mb-2 font-medium text-sm">Margin Level (%)</label>
                    <input type="number" name="marginlevel" step="0.01" value="<?= $_SESSION['marginlevel'] ?? 1000 ?>" class="w-full p-2 border rounded-lg">
                    <small class="text-gray-500">(Equity / Margin) × 100</small>
                </div>
            </div>

            <!-- Trading Parameters Section -->
            <div class="mb-6">
                <h2 class="text-lg font-semibold mb-3 text-gray-700 border-b pb-2">Trading Parameters</h2>
                
                <div class="mb-4">
                    <label class="block mb-2 font-medium text-sm">Default Lot Size</label>
                    <input type="number" name="default_lot_size" step="0.01" value="<?= $_SESSION['default_lot_size'] ?? 0.01 ?>" class="w-full p-2 border rounded-lg">
                    <small class="text-gray-500">Standard position size (0.01 = micro lot)</small>
                </div>

                <div class="mb-4">
                    <label class="block mb-2 font-medium text-sm">Maximum Open Positions</label>
                    <input type="number" name="max_positions" value="<?= $_SESSION['max_positions'] ?? 5 ?>" class="w-full p-2 border rounded-lg">
                    <small class="text-gray-500">Max simultaneous trades allowed</small>
                </div>

                <div class="mb-4">
                    <label class="block mb-2 font-medium text-sm">Risk Per Trade (%)</label>
                    <input type="number" name="risk_percentage" step="0.1" value="<?= $_SESSION['risk_percentage'] ?? 2 ?>" class="w-full p-2 border rounded-lg">
                    <small class="text-gray-500">% of balance to risk per trade</small>
                </div>
            </div>

            <!-- Position Management Section -->
            <div class="mb-6">
                <h2 class="text-lg font-semibold mb-3 text-gray-700 border-b pb-2">Position Management</h2>
                
                <div class="mb-4">
                    <label class="block mb-2 font-medium text-sm">Default Stop Loss (Pips)</label>
                    <input type="number" name="stop_loss_pips" step="1" value="<?= $_SESSION['stop_loss_pips'] ?? 50 ?>" class="w-full p-2 border rounded-lg">
                    <small class="text-gray-500">Default SL distance in pips</small>
                </div>

                <div class="mb-4">
                    <label class="block mb-2 font-medium text-sm">Default Take Profit (Pips)</label>
                    <input type="number" name="take_profit_pips" step="1" value="<?= $_SESSION['take_profit_pips'] ?? 100 ?>" class="w-full p-2 border rounded-lg">
                    <small class="text-gray-500">Default TP distance in pips</small>
                </div>

                <div class="mb-4">
                    <label class="flex items-center">
                        <input type="checkbox" name="trailing_stop" value="1" <?= isset($_SESSION['trailing_stop']) && $_SESSION['trailing_stop'] ? 'checked' : '' ?> class="mr-2">
                        <span class="font-medium text-sm">Enable Trailing Stop</span>
                    </label>
                    <small class="text-gray-500 ml-6 block">Automatically move SL as price moves in profit</small>
                </div>
            </div>

            <!-- Positions Simulation Section -->
            <div class="mb-6">
                <h2 class="text-lg font-semibold mb-3 text-gray-700 border-b pb-2">Positions (Simulation)</h2>

                <div class="mb-4">
                    <label class="block mb-2 font-medium text-sm">Concurrent Positions</label>
                    <input type="number" name="positions_count" min="0" max="50" value="<?= $_SESSION['positions_count'] ?? ($_SESSION['max_positions'] ?? 5) ?>" class="w-full p-2 border rounded-lg">
                    <small class="text-gray-500">How many demo positions to maintain and update</small>
                </div>

                <div class="mb-4">
                    <label class="block mb-2 font-medium text-sm">Profit Bias (%)</label>
                    <input type="number" name="profit_bias" step="1" min="0" max="100" value="<?= isset($_SESSION['profit_bias']) ? round($_SESSION['profit_bias']*100) : 75 ?>" class="w-full p-2 border rounded-lg">
                    <small class="text-gray-500">Chance that updates push profits upward (e.g., 75 = mostly profitable)</small>
                </div>

                <div class="mb-4">
                    <label class="block mb-2 font-medium text-sm">Profit Scale (x)</label>
                    <input type="number" name="profit_scale" step="0.1" min="0.1" max="10" value="<?= $_SESSION['profit_scale'] ?? 1.0 ?>" class="w-full p-2 border rounded-lg">
                    <small class="text-gray-500">Scale profit magnitude per pip step</small>
                </div>
            </div>

            <!-- Simulation & Chart Settings Section -->
            <div class="mb-6">
                <h2 class="text-lg font-semibold mb-3 text-gray-700 border-b pb-2">Simulation & Chart Settings</h2>
                
                <div class="mb-4">
                    <label class="block mb-2 font-medium text-sm">Chart Update Speed</label>
                    <select name="sim_speed" class="w-full p-2 border rounded-lg">
                        <option value="slow" <?= ($_SESSION['sim_speed'] ?? 'normal') == 'slow' ? 'selected' : '' ?>>Slow (5s updates)</option>
                        <option value="normal" <?= ($_SESSION['sim_speed'] ?? 'normal') == 'normal' ? 'selected' : '' ?>>Normal (3s updates)</option>
                        <option value="fast" <?= ($_SESSION['sim_speed'] ?? 'normal') == 'fast' ? 'selected' : '' ?>>Fast (1s updates)</option>
                    </select>
                </div>

                <div class="mb-4">
                    <label class="block mb-2 font-medium text-sm">Chart Timeframe</label>
                    <select name="chart_timeframe" class="w-full p-2 border rounded-lg">
                        <?php $ctf = $_SESSION['chart_timeframe'] ?? '1m'; ?>
                        <option value="30s" <?= ($ctf=='30s')?'selected':'' ?>>30 seconds</option>
                        <option value="1m" <?= ($ctf=='1m')?'selected':'' ?>>1 minute</option>
                        <option value="5m" <?= ($ctf=='5m')?'selected':'' ?>>5 minutes</option>
                        <option value="15m" <?= ($ctf=='15m')?'selected':'' ?>>15 minutes</option>
                        <option value="1h" <?= ($ctf=='1h')?'selected':'' ?>>1 hour</option>
                    </select>
                </div>

                <div class="mb-4">
                    <label class="block mb-2 font-medium text-sm">Price Volatility</label>
                    <input type="number" name="price_volatility" step="0.1" min="0.1" max="5" value="<?= $_SESSION['price_volatility'] ?? 1.0 ?>" class="w-full p-2 border rounded-lg">
                    <small class="text-gray-500">1.0 = normal, 2.0 = double volatility</small>
                </div>

                <div class="mb-4">
                    <label class="flex items-center">
                        <input type="checkbox" name="auto_trade" value="1" <?= isset($_SESSION['auto_trade']) && $_SESSION['auto_trade'] ? 'checked' : '' ?> class="mr-2">
                        <span class="font-medium text-sm">Enable Auto-Trading (Demo Bot)</span>
                    </label>
                    <small class="text-gray-500 ml-6 block">Automatically generate simulated trades</small>
                </div>
            </div>

            <!-- Instruments / Pairs Section -->
            <div class="mb-6">
                <h2 class="text-lg font-semibold mb-3 text-gray-700 border-b pb-2">Instruments (Pairs)</h2>
                <div class="mb-2">
                    <label class="block mb-2 font-medium text-sm">Symbols</label>
                    <textarea name="pairs" rows="4" class="w-full p-2 border rounded-lg" placeholder="EUR/USD, GBP/USD, USD/JPY"><?php
                        echo htmlspecialchars(implode(", ", $_SESSION['pairs'] ?? []));
                    ?></textarea>
                </div>
                <small class="text-gray-500">Comma or newline separated. Used for positions and quotes.</small>
            </div>

            <button type="submit" class="w-full bg-blue-600 hover:bg-blue-700 text-white py-3 rounded-lg font-semibold text-lg">
                Save Configuration & Start
            </button>
        </form>

        <div class="text-center mt-4">
            <a href="index.php" class="text-blue-600 hover:underline">Back to Dashboard</a>
        </div>
    </div>

    <!-- Mobile Navigation -->
    <div class="mobile-nav">
        <a class="mobile-nav-item" href="index.php#quotes">
            <span class="nav-icon">⇅</span>
            <span>Quotes</span>
        </a>
        <a class="mobile-nav-item" href="index.php#chart">
            <span class="nav-icon">⌭</span>
            <span>Chart</span>
        </a>
        <a class="mobile-nav-item" href="trade.php">
            <span class="nav-icon">≡</span>
            <span>Trade</span>
        </a>
        <a class="mobile-nav-item" href="trades_view.php">
            <span class="nav-icon">⏱</span>
            <span>History</span>
        </a>
        <a class="mobile-nav-item active" href="set_params.php">
            <span class="nav-icon">⚙</span>
            <span>Settings</span>
        </a>
    </div>

</body>
</html>