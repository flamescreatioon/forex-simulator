<?php
// index.php - Main Trading Platform Interface
session_start();

// Mock data for demonstration
require 'helpers/currency_helpers.php';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Forex Trading Platform</title>
    <link rel="stylesheet" href="assets/css/style.css">
    
</head>
<body>
    <div class="container">
        <!-- Sidebar -->
        <?php include 'components/sidebar.php'; ?>
        <!-- Main Content -->
        <div class="main-content">
            <!-- Header -->
            <?php include 'components/header.php'; ?>
            <!-- Trading Area -->
            <div class="trading-area">
                <!-- Chart Section -->
                <div class="chart-section">
                    <div class="chart-header">
                        <?php echo $currency_symbols[0].' '.$timeframes[4].' '. $currency_names[$currency_symbols[0]]; ?>
                    </div>
                    <div class="chart-container">
                        <!-- <div class="chart-placeholder">
                            <p>Chart visualization would go here (requires charting library like TradingView or Chart.js)</p>
                        </div> -->
                    </div>
                </div>

                <!-- Symbol List -->
                <div class="symbol-list">
                    <div class="symbol-search">
                        <input type="text" placeholder="Search symbol" id="symbolSearch">
                    </div>
                    <div class="symbol-list-items">
                        <?php foreach ($currency_pairs as $pair): ?>
                            <div class="symbol-item" onclick="selectSymbol('<?php echo $pair['symbol']; ?>')">
                                <div>
                                    <div class="symbol-name"><?php echo $pair['symbol']; ?></div>
                                    <div class="symbol-prices">
                                        <span class="price-bid"><?php echo number_format($pair['bid'], 5); ?></span>
                                        <span class="price-ask"><?php echo number_format($pair['ask'], 5); ?></span>
                                    </div>
                                </div>
                                <?php if ($pair['change'] != 0): ?>
                                    <span class="symbol-change <?php echo $pair['direction']; ?>">
                                        <?php echo $pair['change']; ?>%
                                    </span>
                                <?php endif; ?>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>

            <!-- Bottom Panel -->
            <div class="bottom-panel">
                <div class="panel-tabs">
                    <div class="panel-tab active">Positions</div>
                    <div class="panel-tab">Orders</div>
                    <div class="panel-tab">Deals</div>
                </div>
                <div class="panel-content">
                    <div class="account-info">
                        <div class="info-item">
                            <span class="info-label">Balance:</span>
                            <span class="info-value"><?php echo number_format($account['balance'], 2); ?></span>
                        </div>
                        <div class="info-item">
                            <span class="info-label">Equity:</span>
                            <span class="info-value"><?php echo number_format($account['equity'], 2); ?></span>
                        </div>
                        <div class="info-item">
                            <span class="info-label">Margin:</span>
                            <span class="info-value"><?php echo number_format($account['margin'], 2); ?></span>
                        </div>
                        <div class="info-item">
                            <span class="info-label">Free margin:</span>
                            <span class="info-value"><?php echo number_format($account['free_margin'], 2); ?></span>
                        </div>
                        <div class="info-item">
                            <span class="info-label">Level:</span>
                            <span class="info-value"><?php echo number_format($account['level'], 2); ?>%</span>
                        </div>
                    </div>

                    <?php if (count($positions) > 0): ?>
                        <table class="positions-table">
                            <thead>
                                <tr>
                                    <th>Symbol</th>
                                    <th>Type</th>
                                    <th>Volume</th>
                                    <th>Price</th>
                                    <th>S / L</th>
                                    <th>T / P</th>
                                    <th>Current</th>
                                    <th>Profit</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($positions as $position): ?>
                                    <tr>
                                        <td><?php echo $position['symbol']; ?></td>
                                        <td><?php echo ucfirst($position['type']); ?></td>
                                        <td><?php echo $position['volume']; ?></td>
                                        <td><?php echo $position['open_price']; ?></td>
                                        <td>-</td>
                                        <td>-</td>
                                        <td><?php echo $position['current_price']; ?></td>
                                        <td style="color: <?php echo $position['profit'] < 0 ? '#e74c3c' : '#27ae60'; ?>">
                                            <?php echo number_format($position['profit'], 2); ?> USD
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    <?php else: ?>
                        <div class="no-positions">
                            <p>You don't have any positions</p>
                            <br>
                            <a href="#" class="create-order-link" onclick="openOrderModal(); return false;">Create New Order</a>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <!-- Mobile Navigation -->
    <div class="mobile-nav">
        <div class="mobile-nav-item active">
            <span>📊</span>
            <span>Quotes</span>
        </div>
        <div class="mobile-nav-item">
            <span>📈</span>
            <span>Chart</span>
        </div>
        <div class="mobile-nav-item">
            <span>≡</span>
            <span>Trade</span>
        </div>
        <div class="mobile-nav-item">
            <span>🕐</span>
            <span>History</span>
        </div>
        <div class="mobile-nav-item">
            <span>⚙</span>
            <span>Settings</span>
        </div>
    </div>

    <!-- Order Modal -->
    <div class="modal" id="orderModal">
        <div class="modal-content">
            <div class="modal-header">
                <div class="modal-title">AUDCAD</div>
                <div class="modal-close" onclick="closeOrderModal()">×</div>
            </div>
            <div class="modal-body">
                <div class="form-group">
                    <label class="form-label">Market Execution</label>
                </div>
                <div class="form-group">
                    <label class="form-label">Volume</label>
                    <input type="number" class="form-input" value="0.01" step="0.01">
                    <small>1 000.00 AUD</small>
                </div>
                <div class="form-group">
                    <label class="form-label">Stop Loss</label>
                    <input type="text" class="form-input" placeholder="-">
                </div>
                <div class="form-group">
                    <label class="form-label">Take Profit</label>
                    <input type="text" class="form-input" placeholder="-">
                </div>
                <div class="form-group">
                    <label class="form-label">Comment</label>
                    <input type="text" class="form-input">
                </div>
                <div class="price-display">
                    <button class="price-btn sell-btn">
                        <div class="price-value">0.91173</div>
                        <div>Sell by Market</div>
                    </button>
                    <button class="price-btn buy-btn">
                        <div class="price-value">0.91185</div>
                        <div>Buy by Market</div>
                    </button>
                </div>
            </div>
        </div>
    </div>
     <script src="https://unpkg.com/lightweight-charts/dist/lightweight-charts.standalone.production.js"></script>
    <script src="assets/js/app.js"></script>
    <script src="controllers/chartControllers.js"></script>
</body>
</html>