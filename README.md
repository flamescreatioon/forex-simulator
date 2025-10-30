# Forex Trading Platform (Demo)

A lightweight, mobile-first demo forex trading platform with live simulated candlestick charts, positions management, and a clean UI. Now ships as a Progressive Web App (PWA) so you can install it on your phone like a native app.

## Highlights

- Live multi-pair candlestick charts with adjustable timeframe and update speed
- Minimalist mobile UI for Quotes, Chart, Trade (positions), and History
- Add, delete, and manage positions with configurable defaults
- Settings for pairs, simulation speed, timeframe, volatility, and profit bias
- PWA: installable on mobile, works offline for shell pages, graceful offline fallback
- Optional persistent backend with MySQL (positions, settings, analytics)

---

## 1) Requirements

- Windows with XAMPP (Apache + PHP)
- PHP 8.x recommended
- MySQL (local or remote)
- A browser that supports PWAs (Chrome/Edge on Android, Safari on iOS for Add to Home Screen)

Folder layout (root is your XAMPP htdocs):

```
C:\xampp\htdocs\new_forex\
  assets/            # CSS, JS, icons
  components/        # Shared header/sidebar
  controllers/       # Frontend controllers (charts, updates)
  data/              # Demo data
  helpers/           # Candle generation endpoints
  includes/          # DB/session helpers
  migrations/        # SQL migrations
  migrate.php        # Web migration tool (backup + apply schema)
  index.php          # Main dashboard (Quotes + Chart)
  trade.php          # Positions page
  position.php       # Position details
  set_params.php     # Settings
  trades.php         # Positions API (JSON)
  update_pairs.php   # Persist pair selections
  manifest.webmanifest
  service-worker.js
  offline.html
```

---

## 2) Quick Start (Local)

1) Place the project in XAMPP
- Copy the entire `new_forex` folder to `C:\xampp\htdocs\`.

2) Start Apache (and MySQL if you plan to use DB)
- Open XAMPP Control Panel and start Apache (and MySQL if needed).

3) Open the app in your browser
- Go to: http://localhost/new_forex/
- Important: Do not open files directly via `file://`; PHP endpoints require Apache.

---

## 3) Using the App (as a user)

- Quotes (left panel on desktop; accessible via mobile nav):
  - Tap the pen icon to choose your pairs. Use the search/filter and Select All/Clear actions.
- Chart:
  - Renders a chart per selected pair. Timeframe and speed are controlled in Settings.
  - Candles continuously update with a bounded mean-reverting model.
- Trade (positions):
  - Shows your current demo positions. Tap a card for details.
  - Use the + icon on the Trade page (or Manage Positions page) to add/delete positions.
- Settings:
  - Pairs, timeframe, chart update speed
  - Positions generation controls (count, profit bias/scale)
  - Simulation settings (volatility, trailing stop, etc.)

---

## 4) Progressive Web App (Install on Mobile)

The app is PWA-enabled with `manifest.webmanifest` and `service-worker.js`.

- Android (Chrome/Edge):
  1. Open http://localhost/new_forex/
  2. Chrome menu → Add to Home screen (or Install app)
  3. Follow prompts; an icon will appear on your home screen

- iOS (Safari):
  1. Open http://localhost/new_forex/
  2. Share → Add to Home Screen
  3. Launch from the icon for a standalone, app-like experience

- Desktop (Chrome/Edge):
  - You may see an install icon in the address bar; click to install.

Offline behavior:
- Static shell pages (index, trade, assets) are cached.
- Dynamic PHP endpoints (e.g., `helpers/candles.php`, `trades.php`) are network-first with a cached fallback.
- If fully offline, navigation falls back to `offline.html` with a Retry button.

Note: Some iOS PWA limitations apply (e.g., background refresh constraints).

---

## 5) Database (Optional, Recommended)

A full MySQL schema is provided for persistence (positions, settings, chart state, analytics).

- Configure DB connection in `includes/db.php` (default points to a remote DB; switch to local if desired):

```php
$servername = "127.0.0.1";
$username = "root";
$password = ""; // default for XAMPP
$dbname = "forex";
```

- Run the migration tool:
  1. Visit: http://localhost/new_forex/migrate.php
  2. Click "Create Backup First" (optional for a new DB)
  3. Click "Run Migration" to create tables:
     - `sessions`, `positions`, `trade_history`, `user_settings`,
       `chart_state`, `analytics_daily`, and legacy `trades`

- After migration:
  - `includes/session.php` auto-initializes DB helper and loads settings from DB
  - You can extend endpoints/pages to use `$db` (see `includes/database.php`)

---

## 6) Advanced: Customization

- Pairs list: Update defaults in `index.php` (modal) and server-side session defaults.
- Timeframe/Speeds: Controlled in `set_params.php` (persisted to cookies/DB).
- Candle model: `helpers/candles.php` and `helpers/next_candle.php` (pair-aware, timeframe-aware).
- Charts: `controllers/chartControllers.js` (renders per-pair; resizes; updates).
- UI: `assets/css/style.css` for global styles.

---

## 7) Troubleshooting

- Chart doesn’t render:
  - Ensure you’re serving via Apache (http://localhost/new_forex/)
  - Check the Console for errors; ensure Lightweight Charts script loads
  - Verify `helpers/candles.php` returns JSON

- PWA isn’t installable:
  - Access via http(s), not file://
  - Ensure `manifest.webmanifest` and icons are accessible (check Network tab)
  - Service worker must be served from the app scope `/new_forex/`

- DB errors:
  - Verify credentials in `includes/db.php`
  - Run `migrate.php` and ensure tables were created
  - Check PHP error logs for details

---

## 8) Development Notes

- Icons: Put `assets/icons/icon-192.png` and `assets/icons/icon-512.png` (square PNGs). You can generate them using:
  - https://realfavicongenerator.net
  - https://favicon.io

- Service Worker Strategy:
  - Cache name: `forex-pwa-v1`
  - Cache-first for shell; network-first for APIs; offline fallback page

- Cookies and Settings:
  - The app reads/writes cookies for speed, timeframe, pairs; settings can be saved to DB via `$db->saveSettings()`

---

## 9) Security & Production Tips

- Never commit real DB credentials to public repos
- Consider using environment variables or a config not in version control
- Enforce HTTPS in production for PWA installability and security
- Limit or disable public endpoints if deploying a demo publicly

---

## 10) Try It

- Local: http://localhost/new_forex/
- Install as PWA: follow the steps in Section 4
- Adjust Settings: http://localhost/new_forex/set_params.php

If you’d like, I can also wire the positions and settings fully to the database layer across all pages and add an Analytics dashboard next.
