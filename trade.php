<?php
require_once 'includes/session.php';
// Defaults are now always loaded, no need to redirect
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Trade - Demo Forex Platform</title>
  <link rel="stylesheet" href="assets/css/style.css">
  <style>
    body {
      background: #fff;
      color: #333;
      margin: 0;
      padding: 0;
      font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
    }
    .trade-container {
      max-width: 100%;
      margin: 0;
      padding: 16px;
      padding-bottom: 80px;
    }
    
    /* Header with Equity */
    .equity-header {
      display: flex;
      justify-content: space-between;
      align-items: center;
      padding: 12px 0;
      margin-bottom: 16px;
    }
    .equity-amount {
      font-size: 28px;
      font-weight: 700;
      color: #3498db;
    }
    .menu-icon {
      width: 40px;
      height: 40px;
      display: flex;
      align-items: center;
      justify-content: center;
      font-size: 24px;
      cursor: pointer;
      color: #666;
    }
    
    /* Account Info Section - Compact List Style */
    .account-info-list {
      background: #fff;
      padding: 0;
      margin-bottom: 20px;
    }
    .info-row {
      display: flex;
      justify-content: space-between;
      align-items: center;
      padding: 12px 0;
      border-bottom: 1px solid #f0f0f0;
    }
    .info-row:last-child {
      border-bottom: none;
    }
    .info-label {
      font-size: 15px;
      color: #666;
      font-weight: 400;
    }
    .info-value {
      font-size: 15px;
      color: #333;
      font-weight: 600;
      text-align: right;
    }
    
    /* Positions Section */
    .positions-header {
      display: flex;
      justify-content: space-between;
      align-items: center;
      margin: 20px 0 12px;
    }
    .positions-title {
      font-size: 16px;
      font-weight: 600;
      color: #333;
    }
    .positions-menu {
      font-size: 20px;
      color: #999;
      cursor: pointer;
    }
    
    /* Position Card */
    .position-card {
      background: #fff;
      border: none;
      border-radius: 8px;
      padding: 14px;
      margin-bottom: 10px;
      cursor: pointer;
    }
    .position-header {
      display: flex;
      justify-content: space-between;
      align-items: center;
      margin-bottom: 6px;
    }
    .position-pair {
      font-size: 16px;
      font-weight: 700;
      color: #333;
    }
    .position-type {
      font-size: 14px;
      color: #3498db;
      font-weight: 500;
      margin-left: 6px;
    }
    .position-profit {
      font-size: 20px;
      font-weight: 700;
      color: #3498db;
    }
    .position-details {
      font-size: 13px;
      color: #999;
      margin-top: 4px;
    }
    .details-grid {
      display: grid;
      grid-template-columns: 1fr 1fr;
      grid-row-gap: 4px;
      grid-column-gap: 12px;
      margin-top: 6px;
    }
    .details-label {
      color: #777;
    }
    .details-value {
      text-align: right;
      color: #333;
      font-weight: 600;
    }
    
    .empty-state {
      text-align: center;
      padding: 40px 20px;
      color: #999;
    }
  </style>
</head>
<body>
  <div class="trade-container">
    
    <!-- Equity Header -->
    <div class="equity-header">
      <div class="menu-icon">☰</div>
      <div class="equity-amount"><?= number_format($_SESSION['equity'], 2) ?> USD</div>
      <a class="menu-icon" href="manage_positions.php" style="text-decoration:none;color:#666">+</a>
    </div>
    
    <!-- Account Information - Clean List Style -->
    <div class="account-info-list">
      <div class="info-row">
        <span class="info-label">Balance:</span>
        <span class="info-value"><?= number_format($_SESSION['balance'], 2) ?></span>
      </div>
      <div class="info-row">
        <span class="info-label">Equity:</span>
        <span class="info-value"><?= number_format($_SESSION['equity'], 2) ?></span>
      </div>
      <div class="info-row">
        <span class="info-label">Margin:</span>
        <span class="info-value"><?= number_format($_SESSION['margin'], 2) ?></span>
      </div>
      <div class="info-row">
        <span class="info-label">Free Margin:</span>
        <span class="info-value"><?= number_format($_SESSION['freemargin'], 2) ?></span>
      </div>
      <div class="info-row">
        <span class="info-label">Margin Level (%):</span>
        <span class="info-value"><?= $_SESSION['marginlevel'] ?></span>
      </div>
    </div>
    
    <!-- Positions Section -->
    <div class="positions-header">
      <h2 class="positions-title">Positions</h2>
      <span class="positions-menu">⋯</span>
    </div>
    
    <div id="positions">
      <!-- Dynamic positions will be loaded here -->
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
    <a class="mobile-nav-item active" href="trade.php">
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

  <script>
    // Fetch and display positions
    async function fetchPositions() {
      try {
        const res = await fetch('trades.php');
        const data = await res.json();

        if (!Array.isArray(data) || data.length === 0) {
          document.getElementById('positions').innerHTML = '<div class="empty-state">No positions yet</div>';
          return;
        }

        let html = '';
        data.forEach(trade => {
          const profit = parseFloat(trade.profit) || 0;
          const profitColor = profit >= 0 ? '#3498db' : '#e74c3c';
          const type = (trade.type || 'buy').toLowerCase();
          const typeColor = type === 'buy' ? '#3498db' : '#e74c3c';
          const pips = typeof trade.pips !== 'undefined' ? trade.pips : '';
          const amount = trade.amount || '0';
          
          html += `
            <div class=\"position-card\" onclick=\"location.href='position.php?id=${encodeURIComponent(trade.id)}'\">
              <div class="position-header">
                <div>
                  <span class="position-pair">${trade.pair}</span>
                  <span class="position-type" style="color:${typeColor}">${type} ${trade.lot_size || '0.5'}</span>
                </div>
                <div class="position-profit" style="color: ${profitColor}">
                  ${Math.abs(profit).toFixed(2)}
                </div>
              </div>
              <div class=\"position-details\">${trade.entry || '0.0000'} → ${trade.current || '0.0000'} • ${amount}</div>
            </div>
          `;
        });

        document.getElementById('positions').innerHTML = html;
      } catch (err) {
        console.error("Error fetching positions:", err);
        document.getElementById('positions').innerHTML = '<div class="empty-state">Error loading positions</div>';
      }
    }

    function getSimInterval() {
      const m = document.cookie.match(/(?:^|; )sim_speed=([^;]+)/);
      const speed = m ? decodeURIComponent(m[1]) : 'normal';
      if (speed === 'slow') return 5000;
      if (speed === 'fast') return 1000;
      return 3000;
    }

    fetchPositions();
    let intervalId = setInterval(fetchPositions, getSimInterval());
    // If speed cookie changes (user saved settings), adjust interval on next focus
    window.addEventListener('focus', () => {
      clearInterval(intervalId);
      intervalId = setInterval(fetchPositions, getSimInterval());
    });
  </script>
  <script src="assets/js/mobile-nav.js"></script>
</body>
</html>
