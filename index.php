<?php
// index.php - Main Trading Platform Interface
require 'includes/session.php';

// Mock data for demonstration
require 'helpers/currency_helpers.php';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="mobile-web-app-capable" content="yes">
    <meta name="theme-color" content="#3498db">
    <title>Forex Trading Platform</title>
    <link rel="manifest" href="manifest.webmanifest">
    <link rel="icon" sizes="192x192" href="assets/icons/icon-192.png">
    <link rel="apple-touch-icon" href="assets/icons/icon-192.png">
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
                <div class="chart-section" id="chart">
                    <div class="chart-container">
                        <div id="chart-fallback" style="position:absolute;inset:0;display:flex;align-items:center;justify-content:center;color:#666;font-size:14px;padding:12px;text-align:center;">
                            If the chart doesn’t appear: ensure this site is served over HTTP (not file://) and the PHP helper endpoints under /helpers are reachable from your browser.
                        </div>
                    </div>
                </div>

                <!-- Symbol List -->
                <div class="symbol-list" id="quotes">
                    <div class="symbol-search" style="display:flex; gap:8px; align-items:center;">
                        <input type="text" placeholder="Search symbol" id="symbolSearch" style="flex:1;">
                        <button id="openPairsModal" class="icon-btn" aria-label="Edit" title="Edit">
                            <span class="icon-glyph">✎</span>
                        </button>
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
                    <div class="panel-tab active" data-target=".positions-table">Positions</div>
                    <div class="panel-tab" data-target=".trades-history">Orders</div>
                    <div class="panel-tab" data-target=".deals-table">Deals</div>
                </div>
                <div class="panel-content">
                    <div class="account-info">
                        <div class="info-item">
                            <span class="info-label">Balance:</span>
                            <span class="info-value" id="balance"><?php echo number_format($_SESSION['balance'], 2); ?></span>
                        </div>
                        <div class="info-item">
                            <span class="info-label">Equity:</span>
                            <span class="info-value" id="equity"><?php echo number_format($_SESSION['equity'], 2); ?></span>
                        </div>
                        <div class="info-item">
                            <span class="info-label">Margin:</span>
                            <span class="info-value" id="margin"><?php echo number_format($_SESSION['margin'], 2); ?></span>
                        </div>
                        <div class="info-item">
                            <span class="info-label">Free margin:</span>
                            <span class="info-value" id="freemargin"><?php echo number_format($_SESSION['freemargin'], 2); ?></span>
                        </div>
                        <div class="info-item">
                            <span class="info-label">Level:</span>
                            <span class="info-value" id="marginlevel"><?php echo number_format($_SESSION['marginlevel'], 2); ?>%</span>
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

                    <table class="trades-history" >
                        <thead>
                            <tr>
                                <th>Pair</th>
                                <th>Profit</th>
                                <th>Time</th>
                            </tr>
                        </thead>
                        <tbody id="tradesTableBody">
                           <tr>
                            <td>No trades yet</td>
                           </tr>
                        </tbody>
                    </table>
                    <div style="margin-top:8px;">
                        <a href="trades_view.php" class="create-order-link">View all trades</a>
                    </div>

                    <table class="deals-table" style="display: none;">
                        <thead>
                            <tr>
                                <th>Deal ID</th>
                                <th>Time</th>
                                <th>Symbol</th>
                                <th>Type</th>
                                <th>Volume</th>
                                <th>Price</th>
                                <th>Profit</th>
                            </tr>
                        </thead>
                        <tbody>
                           <tr>
                            <td colspan="7">No deals yet</td>
                           </tr>
                        </tbody>
                    </table>
                </div>
            </div>
    </div>

    <!-- Mobile Navigation -->
    <div class="mobile-nav">
        <a class="mobile-nav-item active" href="#quotes">
            <span class="nav-icon">⇅</span>
            <span>Quotes</span>
        </a>
        <a class="mobile-nav-item" href="#chart">
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
                <a class="mobile-nav-item" href="set_params.php">
            <span class="nav-icon">⚙</span>
            <span>Settings</span>
        </a>
    </div>

        <!-- Pairs Selector Modal -->
        <?php
            $default_pairs = ['EUR/USD','GBP/USD','USD/JPY','AUD/USD','USD/CAD','USD/CHF','NZD/USD','GBP/JPY'];
            $configured_pairs = isset($_SESSION['pairs']) && is_array($_SESSION['pairs']) ? $_SESSION['pairs'] : $default_pairs;
            $all_pairs = array_values(array_unique(array_merge($default_pairs, $configured_pairs)));
        ?>
        <div id="pairsModal" class="modal" style="display:none;">
            <div class="modal-content" style="max-width:480px;">
                <div class="modal-header">
                    <div class="modal-title">Select Pairs</div>
                    <div class="modal-close" id="closePairsModal" style="cursor:pointer;">×</div>
                </div>
                <div class="modal-body">
                    <div class="pairs-actions">
                        <div class="actions-row">
                            <input id="pairsSearch" type="text" class="pairs-search-input" placeholder="Search pairs e.g. EUR/USD" />
                            <button id="selectAllPairs" type="button" class="pairs-action-btn">Select All</button>
                            <button id="clearPairs" type="button" class="pairs-action-btn">Clear</button>
                        </div>
                    </div>
                    <div id="pairsScroll" class="pairs-scroll">
                        <div id="pairsList">
                            <?php foreach ($all_pairs as $p): ?>
                                <label class="pair-row">
                                    <div class="pair-left">
                                        <input type="checkbox" class="pair-checkbox" value="<?= htmlspecialchars($p) ?>">
                                        <span class="pair-code"><?= htmlspecialchars($p) ?></span>
                                    </div>
                                </label>
                            <?php endforeach; ?>
                        </div>
                    </div>
                    <div style="display:flex; gap:10px; margin-top:12px;">
                        <button id="savePairs" class="pairs-action-btn primary" style="flex:1;">Save</button>
                        <button id="cancelPairs" class="pairs-action-btn" style="flex:1;">Cancel</button>
                    </div>
                </div>
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
                    <input type="number" id="volumeInput" class="form-input" value="<?php echo $_SESSION['default_lot_size'] ?? 0.01; ?>" step="0.01">
                    <small>1 000.00 AUD</small>
                </div>
                <div class="form-group">
                    <label class="form-label">Stop Loss (pips)</label>
                    <input type="number" id="stopLossInput" class="form-input" value="<?php echo $_SESSION['stop_loss_pips'] ?? 0; ?>" placeholder="Pips from entry">
                    <small id="slPrice" style="color: #888;"></small>
                </div>
                <div class="form-group">
                    <label class="form-label">Take Profit (pips)</label>
                    <input type="number" id="takeProfitInput" class="form-input" value="<?php echo $_SESSION['take_profit_pips'] ?? 0; ?>" placeholder="Pips from entry">
                    <small id="tpPrice" style="color: #888;"></small>
                </div>
                <?php if (($_SESSION['trailing_stop'] ?? 0) == 1): ?>
                <div class="form-group">
                    <label class="form-label">
                        <input type="checkbox" id="trailingStopCheck" checked> Trailing Stop Enabled
                    </label>
                    <small>Stop loss will follow price as it moves in your favor</small>
                </div>
                <?php endif; ?>
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
            // Ensure LightweightCharts is available before loading the app module.
            (function loadChartsAndApp(){
                function loadScript(src, cb){
                    var s=document.createElement('script');
                    s.src=src; s.async=true; s.onload=function(){cb(true)}; s.onerror=function(){cb(false)}; document.head.appendChild(s);
                }
                function loadApp(){
                    // Inject module script dynamically so it runs after the chart lib is present
                    var m=document.createElement('script');
                    m.type='module';
                    m.src='assets/js/app.js';
                    document.body.appendChild(m);
                }
                // Try local vendor copy first (if you add it), then CDN.
                var triedLocal=false;
                function tryCdn(){
                    loadScript('https://unpkg.com/lightweight-charts/dist/lightweight-charts.standalone.production.js', function(){
                        if (window.LightweightCharts) { loadApp(); }
                        else { console.error('LightweightCharts failed to load from CDN.'); }
                    });
                }
                loadScript('assets/vendor/lightweight-charts.standalone.production.js', function(ok){
                    if (window.LightweightCharts) { loadApp(); }
                    else { tryCdn(); }
                });
            })();
        </script>
        <script>
            // Register service worker
            if ('serviceWorker' in navigator) {
                window.addEventListener('load', () => {
                    // Auto-detect base path from current location
                    const path = window.location.pathname;
                    const basePath = path.substring(0, path.lastIndexOf('/'));
                    navigator.serviceWorker.register(basePath + '/service-worker.js').catch(console.error);
                });
            }

            // Handle PWA install prompt (optional UI hook)
            let deferredPrompt;
            window.addEventListener('beforeinstallprompt', (e) => {
                e.preventDefault();
                deferredPrompt = e;
                // You can show a custom install button and call prompt() on click
                // Example: document.getElementById('installBtn').style.display = 'block';
            });

            async function triggerInstall() {
                if (deferredPrompt) {
                    deferredPrompt.prompt();
                    const { outcome } = await deferredPrompt.userChoice;
                    deferredPrompt = null;
                }
            }
        </script>
    <script>
        // Transfer settings from cookies to sessionStorage for JavaScript access
        function getCookie(name) {
            const value = `; ${document.cookie}`;
            const parts = value.split(`; ${name}=`);
            if (parts.length === 2) return parts.pop().split(';').shift();
            return null;
        }
        
        const simSpeed = getCookie('sim_speed');
        const priceVolatility = getCookie('price_volatility');
        
        if (simSpeed) sessionStorage.setItem('sim_speed', simSpeed);
        if (priceVolatility) sessionStorage.setItem('price_volatility', priceVolatility);



        // Pairs modal handlers
    const pairsBtn = document.getElementById('openPairsModal');
    const pairsBtnMobile = document.getElementById('openPairsModalMobile');
        const pairsModal = document.getElementById('pairsModal');
    const closePairsModal = document.getElementById('closePairsModal');
        const cancelPairs = document.getElementById('cancelPairs');
        const savePairs = document.getElementById('savePairs');
    const pairsSearch = document.getElementById('pairsSearch');
    const selectAllPairs = document.getElementById('selectAllPairs');
    const clearPairs = document.getElementById('clearPairs');

        function showPairsModal() {
            // Precheck from cookie
            const cookie = getCookie('pairs');
            const selected = cookie ? decodeURIComponent(cookie).split(',').map(s => s.trim()) : [];
            document.querySelectorAll('#pairsList .pair-checkbox').forEach(cb => {
                cb.checked = selected.includes(cb.value);
            });
            pairsModal.style.display = 'block';
        }
        function hidePairsModal() { pairsModal.style.display = 'none'; }
    if (pairsBtn) pairsBtn.addEventListener('click', showPairsModal);
    if (pairsBtnMobile) pairsBtnMobile.addEventListener('click', showPairsModal);
        if (closePairsModal) closePairsModal.addEventListener('click', hidePairsModal);
        if (cancelPairs) cancelPairs.addEventListener('click', hidePairsModal);
        if (pairsModal) pairsModal.addEventListener('click', (e) => { if (e.target === pairsModal) hidePairsModal(); });

        if (savePairs) savePairs.addEventListener('click', async () => {
            const selected = Array.from(document.querySelectorAll('#pairsList .pair-checkbox:checked')).map(cb => cb.value);
            try {
                const res = await fetch('update_pairs.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ pairs: selected })
                });
                if (!res.ok) throw new Error('Failed to update pairs');
                const out = await res.json();
                if (out && out.success) {
                    // Set cookie for frontend JS consumers (charts)
                    document.cookie = `pairs=${encodeURIComponent(selected.join(','))}; path=/; max-age=${60*60*24*30}`;
                    hidePairsModal();
                    // Reload to allow PHP-rendered parts to adapt if needed
                    window.location.reload();
                }
            } catch (e) {
                alert('Could not save pairs');
            }
        });

        // Filtering and bulk actions
        if (pairsSearch) {
            pairsSearch.addEventListener('input', () => {
                const q = pairsSearch.value.toLowerCase();
                document.querySelectorAll('#pairsList .pair-row').forEach(row => {
                    const text = row.textContent.toLowerCase();
                    row.style.display = text.includes(q) ? '' : 'none';
                });
            });
        }

        if (selectAllPairs) {
            selectAllPairs.addEventListener('click', () => {
                document.querySelectorAll('#pairsList .pair-checkbox').forEach(cb => cb.checked = true);
            });
        }
        if (clearPairs) {
            clearPairs.addEventListener('click', () => {
                document.querySelectorAll('#pairsList .pair-checkbox').forEach(cb => cb.checked = false);
            });
        }
    </script>
    <!-- app.js is injected dynamically above after the chart library loads -->
    <script src="controllers/updateController.js?v=<?php echo time(); ?>"></script>
    <script src="assets/js/mobile-nav.js"></script>
</body>
</html>