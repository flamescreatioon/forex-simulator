<?php
session_start();
if (!isset($_SESSION['balance'])) {
    header("Location: set_params.php");
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Trade - Demo Forex Platform</title>
  <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 text-gray-800">
  <div class="max-w-3xl mx-auto mt-6 bg-white rounded-2xl shadow-lg p-6">
    
    <!-- Account Summary -->
    <div class="text-center border-b pb-4 mb-4">
      <h1 class="text-2xl font-semibold text-blue-700">
        <?= number_format($_SESSION['equity'] ?? 0, 2) ?> USD
      </h1>
      <div class="grid grid-cols-2 gap-2 text-sm mt-3">
        <div>Balance: <span class="font-semibold"><?= number_format($_SESSION['balance'], 2) ?></span></div>
        <div>Equity: <span class="font-semibold"><?= number_format($_SESSION['equity'], 2) ?></span></div>
        <div>Margin: <span class="font-semibold"><?= number_format($_SESSION['margin'], 2) ?></span></div>
        <div>Free Margin: <span class="font-semibold"><?= number_format($_SESSION['free_margin'], 2) ?></span></div>
        <div>Margin Level (%): <span class="font-semibold"><?= number_format($_SESSION['margin_level'], 2) ?></span></div>
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
