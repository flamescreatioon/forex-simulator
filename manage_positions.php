<?php
require_once 'includes/session.php';

// Helpers (duplicate minimal logic from trades.php for consistency)
function is_jpy_pair($pair) { return strpos($pair, 'JPY') !== false; }
function gen_entry_price($pair) {
    if (is_jpy_pair($pair)) { return round(mt_rand(100000, 160000) / 1000, 3); }
    return round(mt_rand(10000, 15000) / 10000, 4);
}
function price_from_pips($pair, $price, $pips) {
    if (is_jpy_pair($pair)) { return round($price + ($pips * 0.001), 3); }
    return round($price + ($pips * 0.0001), 4);
}

$pairs = [
  // Synthetic Volatility Indices (default choices)
  'Volatility 10 (1s) Index',
  'Volatility 25 (1s) Index',
  'Volatility 50 (1s) Index',
  'Volatility 75 (1s) Index',
  'Volatility 100 (1s) Index',
  'Volatility 10 Index',
  'Volatility 25 Index',
  'Volatility 50 Index',
  'Volatility 75 Index',
  'Volatility 100 Index',
  // Common FX pairs (kept as additional options)
  'EUR/USD','GBP/USD','USD/JPY','AUD/USD','USD/CAD','USD/CHF','NZD/USD','GBP/JPY'
];
$sl_pips_default = intval($_SESSION['stop_loss_pips'] ?? 20);
$tp_pips_default = intval($_SESSION['take_profit_pips'] ?? 40);
$lot_default = floatval($_SESSION['default_lot_size'] ?? 0.01);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    if ($action === 'add') {
        $pair = in_array($_POST['pair'] ?? '', $pairs, true) ? $_POST['pair'] : $pairs[0];
        $type = strtolower($_POST['type'] ?? 'buy');
        $type = ($type === 'sell') ? 'sell' : 'buy';
        $lot_size = max(0.01, floatval($_POST['lot_size'] ?? $lot_default));
        $entry = isset($_POST['entry']) && $_POST['entry'] !== '' ? floatval($_POST['entry']) : gen_entry_price($pair);
        $sl = isset($_POST['sl']) && $_POST['sl'] !== '' ? floatval($_POST['sl']) : price_from_pips($pair, $entry, ($type==='buy' ? -$sl_pips_default : $sl_pips_default));
        $tp = isset($_POST['tp']) && $_POST['tp'] !== '' ? floatval($_POST['tp']) : price_from_pips($pair, $entry, ($type==='buy' ? $tp_pips_default : -$tp_pips_default));

        $id = uniqid('pos_', true);
        $now = date('Y-m-d H:i:s');
        $trade = [
            'id' => $id,
            'pair' => $pair,
            'type' => $type,
            'lot_size' => $lot_size,
            'entry' => $entry,
            'current' => $entry,
            'sl' => $sl,
            'tp' => $tp,
            'open_time' => $now,
            'pips' => 0,
            'profit' => 0.0,
            'timestamp' => date('H:i:s'),
            'amount' => number_format($lot_size * 100000, 0, '.', ','),
        ];
        array_unshift($_SESSION['trades'], $trade);
        header('Location: manage_positions.php');
        exit;
    }

    if ($action === 'delete') {
        $id = $_POST['id'] ?? '';
        if ($id) {
            $_SESSION['trades'] = array_values(array_filter($_SESSION['trades'], function($t) use ($id) { return ($t['id'] ?? '') !== $id; }));
        }
        header('Location: manage_positions.php');
        exit;
    }

    if ($action === 'delete_all') {
        $_SESSION['trades'] = [];
        header('Location: manage_positions.php');
        exit;
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Manage Positions</title>
  <link rel="stylesheet" href="assets/css/style.css">
  <style>
    body { background:#fff; color:#2f3437; margin:0; font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif; }
    .container { padding:16px; padding-bottom:80px; }
    .topbar { display:flex; align-items:center; justify-content:space-between; padding:10px 0; }
    .navbtn { width:40px; height:40px; display:flex; align-items:center; justify-content:center; font-size:20px; color:#7a7f85; text-decoration:none; }
    .title { font-weight:600; font-size:16px; color:#4a4f55; }

    .card { background:#fff; border:none; border-radius:12px; padding:14px; margin-bottom:12px; }
    .row { display:flex; gap:10px; }
    .field { flex:1; }
    label { display:block; font-size:12px; color:#8a9096; margin-bottom:6px; }
    input, select { width:100%; padding:10px 12px; border:1px solid #eef0f3; border-radius:10px; font-size:14px; }
    .btn { display:inline-block; background:#2d7fb3; color:#fff; border:none; border-radius:10px; padding:10px 14px; font-weight:600; cursor:pointer; text-decoration:none; }
    .btn:hover { background:#246c97; }
    .btn-muted { background:#f2f5f8; color:#4a4f55; }

    .list-item { display:flex; align-items:center; justify-content:space-between; padding:12px 0; border-bottom:1px solid #f5f7fa; }
    .pair { font-weight:600; }
    .meta { color:#8d949a; font-size:12px; }
  </style>
</head>
<body>
  <div class="container">
    <div class="topbar">
      <a class="navbtn" href="javascript:history.back()">←</a>
      <div class="title">Manage Positions</div>
      <a class="navbtn" href="trade.php">✓</a>
    </div>

    <div class="card">
      <form method="post">
        <input type="hidden" name="action" value="add" />
        <div class="row">
          <div class="field">
            <label>Pair</label>
            <select name="pair">
              <?php foreach ($pairs as $p): ?>
                <option value="<?= htmlspecialchars($p) ?>"><?= htmlspecialchars($p) ?></option>
              <?php endforeach; ?>
            </select>
          </div>
          <div class="field">
            <label>Type</label>
            <select name="type">
              <option value="buy">Buy</option>
              <option value="sell">Sell</option>
            </select>
          </div>
        </div>
        <div class="row" style="margin-top:10px;">
          <div class="field">
            <label>Lot Size</label>
            <input type="number" name="lot_size" step="0.01" value="<?= htmlspecialchars($lot_default) ?>" />
          </div>
          <div class="field">
            <label>Amount</label>
            <input type="text" id="amount_display" readonly style="background:#f8f9fa;" value="<?= number_format($lot_default * 100000, 0, '.', ',') ?>" />
          </div>
        </div>
        <div class="row" style="margin-top:10px;">
          <div class="field">
            <label>Entry (auto if blank)</label>
            <input type="number" name="entry" step="0.0001" />
          </div>
          <div class="field">
            <label>Stop Loss (auto if blank)</label>
            <input type="number" name="sl" step="0.0001" />
          </div>
          <div class="field">
            <label>Take Profit (auto if blank)</label>
            <input type="number" name="tp" step="0.0001" />
          </div>
        </div>
        <div style="margin-top:12px; display:flex; gap:10px;">
          <button class="btn" type="submit">Add Position</button>
          <button class="btn btn-muted" type="reset">Clear</button>
        </div>
      </form>
    </div>

    <div class="card">
      <div style="display:flex; align-items:center; justify-content:space-between; margin-bottom:8px;">
        <div class="title" style="font-size:14px;">Existing Positions</div>
        <form method="post" onsubmit="return confirm('Delete all positions?');">
          <input type="hidden" name="action" value="delete_all" />
          <button class="btn btn-muted" type="submit">Delete All</button>
        </form>
      </div>
      <?php if (!empty($_SESSION['trades'])): ?>
        <?php foreach ($_SESSION['trades'] as $t): ?>
          <div class="list-item">
            <div>
              <div class="pair"><?= htmlspecialchars($t['pair'] ?? '-') ?> · <?= htmlspecialchars($t['type'] ?? '-') ?> · <?= htmlspecialchars((string)($t['lot_size'] ?? '')) ?></div>
              <div class="meta">Entry <?= htmlspecialchars((string)($t['entry'] ?? '-')) ?> · SL <?= htmlspecialchars((string)($t['sl'] ?? '-')) ?> · TP <?= htmlspecialchars((string)($t['tp'] ?? '-')) ?></div>
            </div>
            <form method="post" onsubmit="return confirm('Delete this position?');">
              <input type="hidden" name="action" value="delete" />
              <input type="hidden" name="id" value="<?= htmlspecialchars($t['id'] ?? '') ?>" />
              <button class="btn btn-muted" type="submit">Delete</button>
            </form>
          </div>
        <?php endforeach; ?>
      <?php else: ?>
        <div class="meta">No positions yet.</div>
      <?php endif; ?>
    </div>
  </div>

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
    <a class="mobile-nav-item active" href="trade.php">
      <span class="nav-icon"><svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-chart-line-icon lucide-chart-line"><path d="M3 3v16a2 2 0 0 0 2 2h16"/><path d="m19 9-5 5-4-4-3 3"/></svg></span>
      <span>Trade</span>
    </a>
    <a class="mobile-nav-item" href="trades_view.php">
      <span class="nav-icon"><svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-history-icon lucide-history"><path d="M3 12a9 9 0 1 0 9-9 9.75 9.75 0 0 0-6.74 2.74L3 8"/><path d="M3 3v5h5"/><path d="M12 7v5l4 2"/></svg></span>
      <span>History</span>
    </a>
    <a class="mobile-nav-item" href="set_params.php">
      <span class="nav-icon"><svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-settings-icon lucide-settings"><path d="M9.671 4.136a2.34 2.34 0 0 1 4.659 0 2.34 2.34 0 0 0 3.319 1.915 2.34 2.34 0 0 1 2.33 4.033 2.34 2.34 0 0 0 0 3.831 2.34 2.34 0 0 1-2.33 4.033 2.34 2.34 0 0 0-3.319 1.915 2.34 2.34 0 0 1-4.659 0 2.34 2.34 0 0 0-3.32-1.915 2.34 2.34 0 0 1-2.33-4.033 2.34 2.34 0 0 0 0-3.831A2.34 2.34 0 0 1 6.35 6.051a2.34 2.34 0 0 0 3.319-1.915"/><circle cx="12" cy="12" r="3"/></svg></span>
      <span>Settings</span>
    </a>
  </div>

  <script>
    // Update amount display when lot size changes
    const lotInput = document.querySelector('input[name="lot_size"]');
    const amountDisplay = document.getElementById('amount_display');
    
    if (lotInput && amountDisplay) {
      lotInput.addEventListener('input', () => {
        const lot = parseFloat(lotInput.value) || 0;
        const amount = lot * 100000;
        amountDisplay.value = amount.toLocaleString('en-US', {maximumFractionDigits: 0});
      });
    }
  </script>
</body>
</html>
