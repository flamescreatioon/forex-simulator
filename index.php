<?php
// index.php - Main Trading Platform Interface
session_start();

// Mock data for demonstration
$account = [
    'balance' => 100000.00,
    'equity' => 99999.89,
    'margin' => 13.03,
    'free_margin' => 99986.86,
    'level' => 767458.866
];

$currency_pairs = [
    ['symbol' => 'AUDCAD', 'bid' => 0.91162, 'ask' => 0.91175, 'change' => 0.41, 'direction' => 'up'],
    ['symbol' => 'AUDCHF', 'bid' => 0.51828, 'ask' => 0.51841, 'change' => 0.41, 'direction' => 'down'],
    ['symbol' => 'AUDDKK', 'bid' => 4.45852, 'ask' => 4.46016, 'change' => 0, 'direction' => 'neutral'],
    ['symbol' => 'AUDHKD', 'bid' => 5.25944, 'ask' => 5.25953, 'change' => 0, 'direction' => 'neutral'],
    ['symbol' => 'AUDHUF', 'bid' => 228.86992, 'ask' => 229.08708, 'change' => 0, 'direction' => 'neutral'],
    ['symbol' => 'AUDJPY', 'bid' => 99.435, 'ask' => 99.448, 'change' => 0.89, 'direction' => 'up'],
    ['symbol' => 'AUDNOK', 'bid' => 6.89340, 'ask' => 6.89630, 'change' => 0, 'direction' => 'neutral'],
    ['symbol' => 'AUDNZD', 'bid' => 1.13246, 'ask' => 1.13273, 'change' => 0.21, 'direction' => 'up'],
    ['symbol' => 'AUDPLN', 'bid' => 2.63122, 'ask' => 2.63225, 'change' => 0, 'direction' => 'neutral'],
    ['symbol' => 'AUDSEK', 'bid' => 6.11812, 'ask' => 6.12610, 'change' => 0.46, 'direction' => 'up'],
];

$positions = [
    ['symbol' => 'AUDCAD', 'type' => 'buy', 'volume' => 0.02, 'open_price' => 0.91182, 'current_price' => 0.91174, 'profit' => -0.11]
];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Forex Trading Platform</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <script src="https://unpkg.com/lightweight-charts/dist/lightweight-charts.standalone.production.js"></script>
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
                        AUDCAD, H1: Australian Dollar vs Canadian Dollar
                    </div>
                    <div class="chart-container">
                        <div class="chart-placeholder">
                            <p>Chart visualization would go here (requires charting library like TradingView or Chart.js)</p>
                        </div>
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

    <script>
      
    </script>
</body>
</html>