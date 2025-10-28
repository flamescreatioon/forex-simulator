<?php
// Start the session to persist values between requests
session_start();
/* require_once 'includes/db.php'; */
$session_id = session_id();

// Retrieve persisted values or set defaults
$balance = $_SESSION["balance"] ?? 10000;     // current account balance
$profit = $_SESSION["profit"] ?? 0;           // accumulated profit (separately tracked)
$goal = $_SESSION["goal"] ?? 20000;           // target balance goal
$margin = $_SESSION["margin"] ?? ($balance * 0.1); // used margin (default 10% of balance)
$leverage = $_SESSION["leverage"] ?? 0;       // unused in this snippet but persisted if set

// If balance is below the goal, simulate a profit increment and update balance & profit
if ($balance < $goal) {
    $increment = rand(50, 500);   // random simulated gain
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

// Return a JSON payload with formatted numbers
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

/* $stmt = $pdo->prepare("SELECT id FROM sessions WHERE session_id=?");
$stmt->execute([$session_id]);

if($stm->rowCount()==0){
    $insert = $pdo->prepare("INSERT INTO sessions (session_id, balance, equity, margin, free_margin, margin_level, profit, goal) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
    $insert->execute([$session_id, $balance, $equity, $margin, $free_margin, $margin_level, $profit, $goal]);
} else {
    $update = $pdo->prepare("UPDATE sessions SET balance=?, equity=?, margin=?, free_margin=?, margin_level=?, profit=?, goal=? WHERE session_id=?");
    $update->execute([$balance, $equity, $margin, $free_margin, $margin_level, $profit, $goal, $session_id]);
} */