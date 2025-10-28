<?php
session_start();
// Use existing session trades without generating new ones
$trades = $_SESSION['trades'] ?? [];
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Trades History</title>
  <link rel="stylesheet" href="assets/css/style.css" />
  <style>
    .container-small { max-width: 960px; margin: 24px auto; padding: 16px; }
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
</body>
</html>
