<?php
/**
 * Database Helper Class
 * Handles all database operations for the Forex Trading Platform
 */

class Database {
    private $pdo;
    private $session_id;

    public function __construct($pdo, $session_id) {
        $this->pdo = $pdo;
        $this->session_id = $session_id;
    }

    // ==================== SESSION OPERATIONS ====================
    
    public function initSession($balance = 10000.00) {
        $stmt = $this->pdo->prepare("
            INSERT INTO sessions (session_id, balance, equity, free_margin) 
            VALUES (?, ?, ?, ?)
            ON DUPLICATE KEY UPDATE last_activity = CURRENT_TIMESTAMP
        ");
        $stmt->execute([$this->session_id, $balance, $balance, $balance]);
    }

    public function updateSessionStats($balance, $equity, $margin, $free_margin, $margin_level) {
        $stmt = $this->pdo->prepare("
            UPDATE sessions 
            SET balance = ?, equity = ?, margin = ?, free_margin = ?, margin_level = ?, 
                profit = equity - balance
            WHERE session_id = ?
        ");
        $stmt->execute([$balance, $equity, $margin, $free_margin, $margin_level, $this->session_id]);
    }

    public function getSessionData() {
        $stmt = $this->pdo->prepare("SELECT * FROM sessions WHERE session_id = ?");
        $stmt->execute([$this->session_id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    // ==================== POSITION OPERATIONS ====================
    
    public function savePosition($position) {
        $stmt = $this->pdo->prepare("
            INSERT INTO positions 
            (session_id, position_id, pair, type, lot_size, entry_price, current_price, 
             stop_loss, take_profit, pips, profit, amount, open_time)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
            ON DUPLICATE KEY UPDATE
                current_price = VALUES(current_price),
                stop_loss = VALUES(stop_loss),
                take_profit = VALUES(take_profit),
                pips = VALUES(pips),
                profit = VALUES(profit)
        ");
        
        $stmt->execute([
            $this->session_id,
            $position['id'],
            $position['pair'],
            $position['type'],
            $position['lot_size'],
            $position['entry'],
            $position['current'],
            $position['sl'] ?? null,
            $position['tp'] ?? null,
            $position['pips'] ?? 0,
            $position['profit'] ?? 0.0,
            $position['amount'] ?? null,
            $position['open_time'] ?? date('Y-m-d H:i:s')
        ]);
    }

    public function getPositions() {
        $stmt = $this->pdo->prepare("
            SELECT 
                position_id as id, pair, type, lot_size, 
                entry_price as entry, current_price as current,
                stop_loss as sl, take_profit as tp,
                pips, profit, amount, open_time,
                DATE_FORMAT(open_time, '%H:%i:%s') as timestamp
            FROM positions 
            WHERE session_id = ? AND status = 'open'
            ORDER BY open_time DESC
        ");
        $stmt->execute([$this->session_id]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getPosition($position_id) {
        $stmt = $this->pdo->prepare("
            SELECT 
                position_id as id, pair, type, lot_size, 
                entry_price as entry, current_price as current,
                stop_loss as sl, take_profit as tp,
                pips, profit, amount, open_time,
                DATE_FORMAT(open_time, '%H:%i:%s') as timestamp
            FROM positions 
            WHERE session_id = ? AND position_id = ? AND status = 'open'
        ");
        $stmt->execute([$this->session_id, $position_id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function deletePosition($position_id) {
        $stmt = $this->pdo->prepare("
            DELETE FROM positions 
            WHERE session_id = ? AND position_id = ?
        ");
        $stmt->execute([$this->session_id, $position_id]);
    }

    public function deleteAllPositions() {
        $stmt = $this->pdo->prepare("
            DELETE FROM positions WHERE session_id = ?
        ");
        $stmt->execute([$this->session_id]);
    }

    public function closePosition($position_id, $exit_price, $close_reason = 'manual') {
        // Get position details
        $position = $this->getPosition($position_id);
        if (!$position) return false;

        // Archive to trade_history
        $stmt = $this->pdo->prepare("
            INSERT INTO trade_history 
            (session_id, position_id, pair, type, lot_size, entry_price, exit_price,
             stop_loss, take_profit, pips, profit, amount, open_time, close_time, 
             duration_seconds, close_reason)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW(), 
                    TIMESTAMPDIFF(SECOND, ?, NOW()), ?)
        ");
        
        $stmt->execute([
            $this->session_id,
            $position_id,
            $position['pair'],
            $position['type'],
            $position['lot_size'],
            $position['entry'],
            $exit_price,
            $position['sl'],
            $position['tp'],
            $position['pips'],
            $position['profit'],
            $position['amount'],
            $position['open_time'],
            $position['open_time'],
            $close_reason
        ]);

        // Mark as closed
        $stmt = $this->pdo->prepare("
            UPDATE positions SET status = 'closed' WHERE position_id = ?
        ");
        $stmt->execute([$position_id]);

        // Update daily analytics
        $this->updateDailyAnalytics();

        return true;
    }

    // ==================== SETTINGS OPERATIONS ====================
    
    public function saveSettings($settings) {
        $pairs_json = isset($settings['pairs']) && is_array($settings['pairs']) 
            ? json_encode($settings['pairs']) 
            : null;

        $stmt = $this->pdo->prepare("
            INSERT INTO user_settings 
            (session_id, pairs, default_lot_size, risk_percentage, stop_loss_pips, 
             take_profit_pips, trailing_stop, positions_count, profit_bias, profit_scale,
             price_volatility, sim_speed, chart_timeframe, chart_tf_seconds, auto_trade)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
            ON DUPLICATE KEY UPDATE
                pairs = VALUES(pairs),
                default_lot_size = VALUES(default_lot_size),
                risk_percentage = VALUES(risk_percentage),
                stop_loss_pips = VALUES(stop_loss_pips),
                take_profit_pips = VALUES(take_profit_pips),
                trailing_stop = VALUES(trailing_stop),
                positions_count = VALUES(positions_count),
                profit_bias = VALUES(profit_bias),
                profit_scale = VALUES(profit_scale),
                price_volatility = VALUES(price_volatility),
                sim_speed = VALUES(sim_speed),
                chart_timeframe = VALUES(chart_timeframe),
                chart_tf_seconds = VALUES(chart_tf_seconds),
                auto_trade = VALUES(auto_trade)
        ");

        $stmt->execute([
            $this->session_id,
            $pairs_json,
            $settings['default_lot_size'] ?? 0.01,
            $settings['risk_percentage'] ?? 2.0,
            $settings['stop_loss_pips'] ?? 20,
            $settings['take_profit_pips'] ?? 40,
            $settings['trailing_stop'] ?? 0,
            $settings['positions_count'] ?? 5,
            $settings['profit_bias'] ?? 0.75,
            $settings['profit_scale'] ?? 1.0,
            $settings['price_volatility'] ?? 1.0,
            $settings['sim_speed'] ?? 'normal',
            $settings['chart_timeframe'] ?? '1m',
            $settings['chart_tf_seconds'] ?? 60,
            $settings['auto_trade'] ?? 0
        ]);
    }

    public function getSettings() {
        $stmt = $this->pdo->prepare("
            SELECT * FROM user_settings WHERE session_id = ?
        ");
        $stmt->execute([$this->session_id]);
        $settings = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($settings && $settings['pairs']) {
            $settings['pairs'] = json_decode($settings['pairs'], true);
        }
        
        return $settings;
    }

    // ==================== CHART STATE OPERATIONS ====================
    
    public function saveChartState($pair, $anchor, $base_price, $last_time, $candles = null) {
        $candles_json = $candles ? json_encode($candles) : null;
        
        $stmt = $this->pdo->prepare("
            INSERT INTO chart_state 
            (session_id, pair, anchor_price, base_price, last_time, candles_data)
            VALUES (?, ?, ?, ?, ?, ?)
            ON DUPLICATE KEY UPDATE
                anchor_price = VALUES(anchor_price),
                base_price = VALUES(base_price),
                last_time = VALUES(last_time),
                candles_data = VALUES(candles_data)
        ");
        
        $stmt->execute([
            $this->session_id,
            $pair,
            $anchor,
            $base_price,
            $last_time,
            $candles_json
        ]);
    }

    public function getChartState($pair) {
        $stmt = $this->pdo->prepare("
            SELECT * FROM chart_state 
            WHERE session_id = ? AND pair = ?
        ");
        $stmt->execute([$this->session_id, $pair]);
        $state = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($state && $state['candles_data']) {
            $state['candles_data'] = json_decode($state['candles_data'], true);
        }
        
        return $state;
    }

    // ==================== ANALYTICS OPERATIONS ====================
    
    public function updateDailyAnalytics() {
        $today = date('Y-m-d');
        
        // Get today's closed trades
        $stmt = $this->pdo->prepare("
            SELECT 
                COUNT(*) as total_trades,
                SUM(CASE WHEN profit > 0 THEN 1 ELSE 0 END) as winning_trades,
                SUM(CASE WHEN profit < 0 THEN 1 ELSE 0 END) as losing_trades,
                SUM(CASE WHEN profit > 0 THEN profit ELSE 0 END) as gross_profit,
                ABS(SUM(CASE WHEN profit < 0 THEN profit ELSE 0 END)) as gross_loss,
                SUM(profit) as net_profit,
                AVG(CASE WHEN profit > 0 THEN profit ELSE NULL END) as avg_win,
                AVG(CASE WHEN profit < 0 THEN profit ELSE NULL END) as avg_loss,
                MAX(profit) as largest_win,
                MIN(profit) as largest_loss,
                SUM(pips) as total_pips
            FROM trade_history
            WHERE session_id = ? AND DATE(close_time) = ?
        ");
        $stmt->execute([$this->session_id, $today]);
        $stats = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($stats['total_trades'] > 0) {
            $win_rate = ($stats['winning_trades'] / $stats['total_trades']) * 100;
            
            // Get session balance
            $session = $this->getSessionData();
            
            $stmt = $this->pdo->prepare("
                INSERT INTO analytics_daily 
                (session_id, date, total_trades, winning_trades, losing_trades, 
                 gross_profit, gross_loss, net_profit, win_rate, avg_win, avg_loss,
                 largest_win, largest_loss, total_pips, balance_end)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
                ON DUPLICATE KEY UPDATE
                    total_trades = VALUES(total_trades),
                    winning_trades = VALUES(winning_trades),
                    losing_trades = VALUES(losing_trades),
                    gross_profit = VALUES(gross_profit),
                    gross_loss = VALUES(gross_loss),
                    net_profit = VALUES(net_profit),
                    win_rate = VALUES(win_rate),
                    avg_win = VALUES(avg_win),
                    avg_loss = VALUES(avg_loss),
                    largest_win = VALUES(largest_win),
                    largest_loss = VALUES(largest_loss),
                    total_pips = VALUES(total_pips),
                    balance_end = VALUES(balance_end)
            ");
            
            $stmt->execute([
                $this->session_id,
                $today,
                $stats['total_trades'],
                $stats['winning_trades'],
                $stats['losing_trades'],
                $stats['gross_profit'],
                $stats['gross_loss'],
                $stats['net_profit'],
                $win_rate,
                $stats['avg_win'] ?? 0,
                abs($stats['avg_loss'] ?? 0),
                $stats['largest_win'],
                abs($stats['largest_loss']),
                $stats['total_pips'],
                $session['balance'] ?? 0
            ]);
        }
    }

    public function getAnalytics($days = 30) {
        $stmt = $this->pdo->prepare("
            SELECT * FROM analytics_daily 
            WHERE session_id = ? 
            ORDER BY date DESC 
            LIMIT ?
        ");
        $stmt->execute([$this->session_id, $days]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getTradeHistory($limit = 100) {
        $stmt = $this->pdo->prepare("
            SELECT * FROM trade_history 
            WHERE session_id = ? 
            ORDER BY close_time DESC 
            LIMIT ?
        ");
        $stmt->execute([$this->session_id, $limit]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
