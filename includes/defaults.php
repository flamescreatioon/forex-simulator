<?php
// includes/defaults.php - Default application parameters
// 
// This file provides sensible defaults for all trading parameters.
// All defaults can be overridden by the user via set_params.php.
// 
// DEFAULT VALUES:
// - Balance: $10,000
// - Leverage: 1:100
// - Default Lot Size: 0.01 (micro lot)
// - Max Positions: 5
// - Risk Per Trade: 2%
// - Stop Loss: 20 pips
// - Take Profit: 40 pips
// - Simulation Speed: Normal (3 seconds)
// - Price Volatility: 1.0 (medium)
// - Auto Trade: Disabled
// - Trailing Stop: Disabled

// Initialize default account information if not already set
if (!isset($_SESSION['balance'])) {
    $_SESSION['balance'] = 10000.00;
}

if (!isset($_SESSION['equity'])) {
    $_SESSION['equity'] = 10000.00;
}

if (!isset($_SESSION['margin'])) {
    $_SESSION['margin'] = 0.00;
}

if (!isset($_SESSION['freemargin'])) {
    $_SESSION['freemargin'] = 10000.00;
}

if (!isset($_SESSION['marginlevel'])) {
    $_SESSION['marginlevel'] = '0.00%';
}

if (!isset($_SESSION['leverage'])) {
    $_SESSION['leverage'] = 100;
}

if (!isset($_SESSION['profit'])) {
    $_SESSION['profit'] = 0.00;
}

if (!isset($_SESSION['goal'])) {
    $_SESSION['goal'] = 15000.00;
}

// Trading parameters defaults
if (!isset($_SESSION['default_lot_size'])) {
    $_SESSION['default_lot_size'] = 0.01;
}

if (!isset($_SESSION['max_positions'])) {
    $_SESSION['max_positions'] = 5;
}

if (!isset($_SESSION['risk_percentage'])) {
    $_SESSION['risk_percentage'] = 2.0;
}

// Position management defaults
if (!isset($_SESSION['stop_loss_pips'])) {
    $_SESSION['stop_loss_pips'] = 20;
}

if (!isset($_SESSION['take_profit_pips'])) {
    $_SESSION['take_profit_pips'] = 40;
}

if (!isset($_SESSION['trailing_stop'])) {
    $_SESSION['trailing_stop'] = 0;
}

// Simulation settings defaults
if (!isset($_SESSION['sim_speed'])) {
    $_SESSION['sim_speed'] = 'normal';
}

if (!isset($_SESSION['price_volatility'])) {
    $_SESSION['price_volatility'] = 1.0;
}

if (!isset($_SESSION['auto_trade'])) {
    $_SESSION['auto_trade'] = 0;
}

// Initialize empty arrays if not set
if (!isset($_SESSION['positions'])) {
    $_SESSION['positions'] = [];
}

if (!isset($_SESSION['trades'])) {
    $_SESSION['trades'] = [];
}
