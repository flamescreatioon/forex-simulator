<?php
require_once 'includes/session.php';
// Use existing session trades (defaults already loaded)
$trades = $_SESSION['trades'];
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Trades History</title>
  <link rel="stylesheet" href="assets/css/style.css" />
  <style>
    .container-small { max-width: 960px; margin: 24px auto; padding: 16px; margin-bottom: 80px; }
    .toolbar { display:flex; justify-content: space-between; align-items:center; margin-bottom:12px; }
    .btn { display:inline-block; padding:8px 12px; border-radius:6px; background:#2c7be5; color:#fff; text-decoration:none; }
    .btn.secondary { background:#6c757d; }
    table { width:100%; border-collapse: collapse; }
    th, td { padding: 8px 10px; border-bottom: 1px solid #e5e5e5; }
    th { text-align:left; color:#666; font-weight:600; }
    .empty { padding: 24px; text-align:center; color:#666; }
  </style>
</head>
<body>
  <div class="container-small">
    <div class="toolbar">
      <h2>Trades History</h2>
      <div>
        <a href="index.php" class="btn secondary">← Back</a>
        <a href="#" id="refreshTrades" class="btn">Refresh</a>
      </div>
    </div>

    <?php if (count($trades) === 0): ?>
      <div class="empty">No trades yet. Return to the dashboard and wait for new entries.</div>
    <?php else: ?>
      <table>
        <thead>
          <tr>
            <th>Pair</th>
            <th>Profit</th>
            <th>Time</th>
          </tr>
        </thead>
        <tbody id="tradesTable">
          <?php foreach ($trades as $trade): ?>
            <tr>
              <td><?= htmlspecialchars($trade['pair']) ?></td>
              <td><?= htmlspecialchars($trade['profit']) ?></td>
              <td><?= htmlspecialchars($trade['timestamp']) ?></td>
            </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    <?php endif; ?>
  </div>

  <!-- Mobile Navigation -->
  <div class="mobile-nav">
    <a class="mobile-nav-item" href="index.php#quotes">
      <span class="nav-icon">⇅</span>
      <span>Quotes</span>
    </a>
    <a class="mobile-nav-item" href="index.php#chart">
      <span class="nav-icon"><svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-align-horizontal-distribute-center-icon lucide-align-horizontal-distribute-center">
          <rect width="6" height="14" x="4" y="5" rx="2" fill="currentColor" stroke="none" />
          <rect width="6" height="10" x="14" y="7" rx="2" />
          <path d="M17 22v-5" />
          <path d="M17 7V2" />
          <path d="M7 22v-3" />
          <path d="M7 5V2" />
        </svg></span>
      <span>Chart</span>
    </a>
    <a class="mobile-nav-item" href="trade.php">
      <span class="nav-icon"><svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-chart-line-icon lucide-chart-line"><path d="M3 3v16a2 2 0 0 0 2 2h16"/><path d="m19 9-5 5-4-4-3 3"/></svg></span>
      <span>Trade</span>
    </a>
    <a class="mobile-nav-item active" href="trades_view.php">
      <span class="nav-icon"><svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-history-icon lucide-history"><path d="M3 12a9 9 0 1 0 9-9 9.75 9.75 0 0 0-6.74 2.74L3 8"/><path d="M3 3v5h5"/><path d="M12 7v5l4 2"/></svg></span>
      <span>History</span>
    </a>
    <a class="mobile-nav-item" href="set_params.php">
      <span class="nav-icon"><svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-settings-icon lucide-settings"><path d="M9.671 4.136a2.34 2.34 0 0 1 4.659 0 2.34 2.34 0 0 0 3.319 1.915 2.34 2.34 0 0 1 2.33 4.033 2.34 2.34 0 0 0 0 3.831 2.34 2.34 0 0 1-2.33 4.033 2.34 2.34 0 0 0-3.319 1.915 2.34 2.34 0 0 1-4.659 0 2.34 2.34 0 0 0-3.32-1.915 2.34 2.34 0 0 1-2.33-4.033 2.34 2.34 0 0 0 0-3.831A2.34 2.34 0 0 1 6.35 6.051a2.34 2.34 0 0 0 3.319-1.915"/><circle cx="12" cy="12" r="3"/></svg></span>
      <span>Settings</span>
    </a>
  </div>

  <script>
    // Optional: Refresh in-page without generating extra trade entries server-side
    document.getElementById('refreshTrades').addEventListener('click', async (e) => {
      e.preventDefault();
      try {
        // Fetch the current session trades by hitting a lightweight endpoint that doesn't create new ones.
        // Since we don't have such an endpoint, we'll fallback to reloading the page for now.
        window.location.reload();
      } catch (err) {
        console.error('Refresh failed:', err);
      }
    });
  </script>
  <script src="assets/js/mobile-nav.js"></script>
</body>
</html>
