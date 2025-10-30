# Database Enhancement - Implementation Guide

## 📋 Overview
Complete database persistence layer for the Forex Trading Platform with analytics and proper schema.

## 🗄️ New Database Schema

### Tables Created

1. **sessions** (enhanced)
   - Stores session data with balance, equity, margin tracking
   - Auto-updates last activity timestamp

2. **positions**
   - Active open positions with full details
   - Tracks entry, current price, SL, TP, profit, pips
   - Supports position status (open/closed)

3. **trade_history**
   - Archive of closed positions
   - Includes close reason, duration, exit price
   - Used for analytics calculations

4. **user_settings**
   - Per-session user preferences
   - Pairs selection, lot sizes, risk settings
   - Chart and simulation preferences
   - Auto-trade configuration

5. **chart_state**
   - Per-pair chart data persistence
   - Stores anchor prices, base prices, candles cache
   - Enables chart state recovery on reload

6. **analytics_daily**
   - Daily aggregated statistics
   - Win rate, profit/loss tracking
   - Average win/loss, largest trades
   - Balance tracking over time

7. **trades** (legacy)
   - Kept for backward compatibility
   - Minimal logging table

## 🚀 Migration Steps

### Step 1: Backup Current Data
```
Visit: http://localhost/new_forex/migrate.php
Click: "1. Create Backup First"
```
This creates a timestamped SQL backup in the `backups/` folder.

### Step 2: Run Migration
```
Click: "2. Run Migration"
```
This will:
- Drop existing tables
- Create new enhanced schema
- Set up foreign keys and indexes

### Step 3: Verify
Check that all 7 tables are created successfully.

## 📦 New Files Created

1. **migrations/001_enhanced_schema.sql**
   - Complete SQL migration script
   - Drop and recreate all tables
   - Proper indexes and foreign keys

2. **includes/database.php**
   - Database helper class
   - Methods for all CRUD operations
   - Analytics calculation
   - Settings management

3. **migrate.php**
   - Web-based migration tool
   - Backup functionality
   - Migration runner
   - Current database viewer

4. **Updated: includes/session.php**
   - Now initializes database helper
   - Loads settings from database
   - Falls back gracefully if DB not ready

## 🔧 Usage in Application

### Basic Usage
```php
// Already available in all files via session.php
global $db;

// Save a position
$db->savePosition($_SESSION['trades'][0]);

// Load all positions
$positions = $db->getPositions();

// Save settings
$db->saveSettings($_SESSION);

// Get analytics
$analytics = $db->getAnalytics(30); // Last 30 days
```

### Position Management
```php
// Get specific position
$position = $db->getPosition($position_id);

// Close a position
$db->closePosition($position_id, $exit_price, 'take_profit');

// Delete position
$db->deletePosition($position_id);

// Delete all
$db->deleteAllPositions();
```

### Chart State
```php
// Save chart state
$db->saveChartState($pair, $anchor, $base_price, $last_time, $candles);

// Load chart state
$state = $db->getChartState($pair);
```

### Analytics
```php
// Update daily stats
$db->updateDailyAnalytics();

// Get trade history
$history = $db->getTradeHistory(100);
```

## 🎯 Benefits After Migration

1. **Data Persistence**
   - Positions survive page reloads
   - Settings remembered across sessions
   - Chart state maintained

2. **Analytics**
   - Track performance over time
   - Win rate calculations
   - Profit/loss trends
   - Daily statistics

3. **Scalability**
   - Proper database design
   - Indexed for performance
   - Foreign key constraints
   - Support for multiple users

4. **Trade History**
   - Complete audit trail
   - Close reasons tracked
   - Duration calculations
   - Historical analysis

## ⚠️ Important Notes

1. **Backup First**: Always create a backup before migrating
2. **Session Data**: After migration, session data will persist to database
3. **Compatibility**: Old session-based code will continue to work
4. **Performance**: Indexed tables for fast queries
5. **Foreign Keys**: Cascade deletes for data integrity

## 🔄 Next Steps

After migration, you can:
1. Update trades.php to use $db->getPositions()
2. Update manage_positions.php to save to database
3. Create analytics dashboard
4. Add trade history view
5. Implement position recovery on page load

## 📞 Troubleshooting

**Migration fails:**
- Check database credentials in includes/db.php
- Ensure database user has CREATE/DROP privileges
- Check PHP error logs

**Data not persisting:**
- Verify migration completed successfully
- Check that $db is initialized in session.php
- Look for PHP errors in browser console

**Performance issues:**
- Run ANALYZE TABLE on all tables
- Check index usage with EXPLAIN
- Consider adding more indexes if needed

---

**Ready to migrate?** Visit http://localhost/new_forex/migrate.php to get started!
