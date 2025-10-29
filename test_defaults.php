<?php
// test_defaults.php - Test if defaults are properly loaded
require_once 'includes/session.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Test Defaults</title>
    <style>
        body { font-family: Arial, sans-serif; padding: 20px; }
        table { border-collapse: collapse; width: 100%; max-width: 600px; }
        th, td { border: 1px solid #ddd; padding: 8px; text-align: left; }
        th { background-color: #4CAF50; color: white; }
        .success { color: green; }
        .error { color: red; }
    </style>
</head>
<body>
    <h1>Default Parameters Test</h1>
    <p>This page tests if all default parameters are properly loaded from <code>includes/defaults.php</code></p>
    
    <table>
        <thead>
            <tr>
                <th>Parameter</th>
                <th>Value</th>
                <th>Status</th>
            </tr>
        </thead>
        <tbody>
            <?php
            $expected = [
                'balance' => 10000.00,
                'equity' => 10000.00,
                'margin' => 0.00,
                'freemargin' => 10000.00,
                'marginlevel' => '0.00%',
                'leverage' => 100,
                'profit' => 0.00,
                'goal' => 15000.00,
                'default_lot_size' => 0.01,
                'max_positions' => 5,
                'risk_percentage' => 2.0,
                'stop_loss_pips' => 20,
                'take_profit_pips' => 40,
                'trailing_stop' => 0,
                'sim_speed' => 'normal',
                'price_volatility' => 1.0,
                'auto_trade' => 0
            ];
            
            foreach ($expected as $key => $expectedValue) {
                $actualValue = $_SESSION[$key] ?? 'NOT SET';
                $matches = ($actualValue == $expectedValue);
                $status = $matches ? '<span class="success">✓ OK</span>' : '<span class="error">✗ FAIL</span>';
                
                echo "<tr>";
                echo "<td><strong>{$key}</strong></td>";
                echo "<td>" . htmlspecialchars($actualValue) . "</td>";
                echo "<td>{$status}</td>";
                echo "</tr>";
            }
            ?>
        </tbody>
    </table>
    
    <p style="margin-top: 20px;">
        <a href="index.php">Go to Main Dashboard</a> | 
        <a href="set_params.php">Override Defaults in Settings</a>
    </p>
</body>
</html>
