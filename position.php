<?php
require_once 'includes/session.php';
// Defaults loaded via session include

$id = $_GET['id'] ?? '';
if (!$id) {
  http_response_code(400);
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Position Details</title>
  <link rel="stylesheet" href="assets/css/style.css">
  <style>
    body { background:#fff; color:#333; margin:0; font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif; }
    .container { padding:16px; padding-bottom:80px; }
    .topbar { display:flex; align-items:center; justify-content:space-between; padding:12px 0; }
    .back { width:40px; height:40px; display:flex; align-items:center; justify-content:center; font-size:22px; color:#666; cursor:pointer; }
     .title { font-weight:600; font-size:16px; color:#4a4f55; }

    .card { background:#fff; border:none; border-radius:10px; padding:16px; }
    .header { display:flex; align-items:flex-start; justify-content:space-between; margin-bottom:6px; }
    .pair { font-size:17px; font-weight:600; color:#2f3437; }
     .type { margin-left:6px; font-size:12px; font-weight:500; padding:2px 8px; border-radius:999px; background:rgba(47,128,237,0.08); }
    .profit { font-size:20px; font-weight:600; }
    .muted { color:#8d949a; font-size:12px; }

    .grid { display:grid; grid-template-columns: 1fr 1fr; grid-row-gap:10px; grid-column-gap:16px; margin-top:8px; }
    .label { color:#8a9096; font-weight:400; }
    .value { text-align:right; color:#3a3f44; font-weight:500; }

    .section { margin:18px 0 10px; font-weight:600; color:#333; }
     .divider { height:1px; background:#f5f7fa; margin:10px 0; }
    .action { width:40px; height:40px; display:flex; align-items:center; justify-content:center; font-size:20px; color:#7a7f85; cursor:pointer; text-decoration:none; }
  </style>
</head>
<body>
  <div class="container">
    <div class="topbar">
      <div class="back" onclick="history.back()">←</div>
      <div class="title">Position</div>
      <a class="action" href="manage_positions.php">+</a>
    </div>

    <div id="content"></div>
  </div>

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
    const positionId = <?= json_encode($id) ?>;

    function getSimInterval() {
      const m = document.cookie.match(/(?:^|; )sim_speed=([^;]+)/);
      const speed = m ? decodeURIComponent(m[1]) : 'normal';
      if (speed === 'slow') return 5000;
      if (speed === 'fast') return 1000;
      return 3000;
    }

    async function fetchPosition() {
      try {
        const res = await fetch('trades.php');
        const data = await res.json();
        const t = Array.isArray(data) ? data.find(x => x.id === positionId) : null;
        if (!t) {
          document.getElementById('content').innerHTML = '<div class="card">Position not found (it may have been removed).</div>';
          return;
        }
        const profit = parseFloat(t.profit) || 0;
        const profitColor = profit >= 0 ? '#2d7fb3' : '#c95a5a';
  const type = (t.type || 'buy').toLowerCase();
  const typeColor = type === 'buy' ? '#2d7fb3' : '#c95a5a';
  const typeBg = type === 'buy' ? 'rgba(45,127,179,0.08)' : 'rgba(201,90,90,0.08)';
        const pips = typeof t.pips !== 'undefined' ? t.pips : '';

        document.getElementById('content').innerHTML = `
          <div class="card">
            <div class="header">
              <div>
                <span class="pair">${t.pair}</span>
                <span class="type" style="color:${typeColor}; background:${typeBg}">${type} ${t.lot_size || '0.5'}</span>
                <div class="muted">${t.entry || '0.0000'} → ${t.current || '0.0000'}</div>
              </div>
              <div class="profit" style="color:${profitColor}">${Math.abs(profit).toFixed(2)}</div>
            </div>
            <div class="divider"></div>
            <div class="section">Details</div>
            <div class="grid">
              <div class="label">Entry</div><div class="value">${t.entry ?? '-'}</div>
              <div class="label">Current</div><div class="value">${t.current ?? '-'}</div>
              <div class="label">Stop Loss</div><div class="value">${t.sl ?? '-'}</div>
              <div class="label">Take Profit</div><div class="value">${t.tp ?? '-'}</div>
              <div class="label">Pips</div><div class="value">${pips === '' ? '' : Math.abs(pips)}</div>
              <div class="label">Opened</div><div class="value">${t.open_time ?? '-'}</div>
              <div class="label">ID</div><div class="value" style="font-weight:400;color:#666">${t.id}</div>
            </div>
          </div>
        `;
      } catch (e) {
        document.getElementById('content').innerHTML = '<div class="card">Error loading position.</div>';
      }
    }

    fetchPosition();
    let intervalId = setInterval(fetchPosition, getSimInterval());
    window.addEventListener('focus', () => {
      clearInterval(intervalId);
      intervalId = setInterval(fetchPosition, getSimInterval());
    });
  </script>
</body>
</html>
