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
  <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 text-gray-800">
  <div class="max-w-3xl mx-auto mt-6 bg-white rounded-2xl shadow-lg p-6" style="margin-bottom: 80px;">
    
    <!-- Account Summary -->
    <div class="text-center border-b pb-4 mb-4">
      <h1 class="text-2xl font-semibold text-blue-700">
        <?= number_format($_SESSION['equity'] ?? 0, 2) ?> USD
      </h1>
      <div class="grid grid-cols-2 gap-2 text-sm mt-3">
        <div>Balance: <span class="font-semibold"><?= number_format($_SESSION['balance'], 2) ?></span></div>
        <div>Equity: <span class="font-semibold"><?= number_format($_SESSION['equity'], 2) ?></span></div>
        <div>Margin: <span class="font-semibold"><?= number_format($_SESSION['margin'], 2) ?></span></div>
        <div>Free Margin: <span class="font-semibold"><?= number_format($_SESSION['freemargin'], 2) ?></span></div>
        <div>Margin Level (%): <span class="font-semibold"><?= number_format($_SESSION['marginlevel'], 2) ?></span></div>
      </div>
    </div>

    <!-- Positions -->
    <div>
      <h2 class="text-md font-semibold text-gray-700 mb-2">Positions</h2>
      <div id="positions">
        <!-- Dynamic positions will be loaded here -->
      </div>
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
    <a class="mobile-nav-item" href="info.php">
      <span class="nav-icon">⚙</span>
      <span>Settings</span>
    </a>
  </div>

  <script>
    // Fetch trades every 2s
    async function fetchPositions() {
      try {
        const res = await fetch('trades.php');
        const data = await res.json();

        let html = '';
        data.forEach(trade => {
          html += `
            <div class="flex justify-between items-center border-b py-2">
              <div>
                <span class="font-semibold">${trade.pair}</span>
                <span class="text-blue-600 ml-1">buy ${trade.lot}</span><br>
                <small>${trade.entry} → ${trade.current}</small>
              </div>
              <div class="text-blue-700 font-bold">${parseFloat(trade.profit).toFixed(2)}</div>
            </div>
          `;
        });

        document.getElementById('positions').innerHTML = html;
      } catch (err) {
        console.error("Error fetching positions:", err);
      }
    }

    fetchPositions();
    setInterval(fetchPositions, 2000);
  </script>
</body>
</html>
