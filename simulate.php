<?php
// Start the session to persist values between requests
require_once 'includes/session.php';
require_once 'includes/db.php';
header('Content-Type: application/json');
// Don't leak PHP warnings/notices into JSON responses
@ini_set('display_errors', 0);
@error_reporting(E_ERROR | E_PARSE);
$session_id = session_id();

// Use session values (defaults already loaded from includes/defaults.php)
$balance = $_SESSION["balance"];
$profit = $_SESSION["profit"];
$goal = $_SESSION["goal"];
$margin = $_SESSION["margin"];
$leverage = $_SESSION["leverage"];
$price_volatility = $_SESSION["price_volatility"] ?? 1.0; // volatility multiplier
$sim_speed = $_SESSION["sim_speed"] ?? 'normal'; // simulation speed

// Adjust increment based on volatility setting
$base_increment = 50;
$max_increment = 500;
$increment_range = ($max_increment - $base_increment) * $price_volatility;

// Only simulate account growth when demo bot (auto_trade) is enabled
$auto_trade = $_SESSION["auto_trade"] ?? 0;
// If balance is below the goal and auto trade is enabled, simulate a profit increment
if ($auto_trade && $balance < $goal) {
    $increment = rand($base_increment, $base_increment + $increment_range);
    $profit = $profit + $increment;
    $balance = $balance + $increment;
    // Persist updated values to session
    $_SESSION["profit"] = $profit;
    $_SESSION["balance"] = $balance;
}

// Calculate derived account values
$equity = $balance + $profit;                     // equity (balance + profit)
$free_margin = $equity - $margin;                 // free margin available
$margin_level = $margin > 0 ? ($equity / $margin) * 100 : 0; // margin level as percentage

// Persist derived values to session for later use
$_SESSION["freemargin"] = $free_margin;
$_SESSION["marginlevel"] = $margin_level;
$_SESSION["equity"] = $equity;

// Determine if goal has been reached
$goal_reached = $balance >= $goal;

// Persist to DB (if available); failures must not break JSON response
try {
    if (isset($pdo) && $pdo instanceof PDO) {
        $stmt = $pdo->prepare("SELECT id FROM sessions WHERE session_id=?");
        $stmt->execute([$session_id]);

        if($stmt->rowCount()==0){
            $insert = $pdo->prepare("INSERT INTO sessions (session_id, balance, equity, margin, free_margin, margin_level, profit, goal) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
            $insert->execute([$session_id, $balance, $equity, $margin, $free_margin, $margin_level, $profit, $goal]);
        } else {
            $update = $pdo->prepare("UPDATE sessions SET balance=?, equity=?, margin=?, free_margin=?, margin_level=?, profit=?, goal=? WHERE session_id=?");
            $update->execute([$balance, $equity, $margin, $free_margin, $margin_level, $profit, $goal, $session_id]);
        }
    }
} catch (Throwable $e) {
    // swallow DB errors; we still return JSON below
}

// Return a JSON payload with formatted numbers (after DB ops to avoid corrupting JSON with PHP notices)
echo json_encode([
    'balance' => number_format($balance, 2),
    'equity' => number_format($equity, 2),
    'margin' => number_format($margin, 2),
    'freemargin' => number_format($free_margin, 2),
    'marginlevel' => number_format($margin_level, 2) . '%',
    'profit' => number_format($profit, 2),
    'goal' => number_format($goal, 2),
    'goal_reached' => $goal_reached
]);