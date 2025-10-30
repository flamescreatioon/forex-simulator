-- Enhanced Forex Trading Platform Database Schema
-- Migration 001: Complete schema with positions, settings, analytics

-- Drop existing tables if they exist (careful in production!)
DROP TABLE IF EXISTS trade_history;
DROP TABLE IF EXISTS positions;
DROP TABLE IF EXISTS analytics_daily;
DROP TABLE IF EXISTS user_settings;
DROP TABLE IF EXISTS chart_state;
DROP TABLE IF EXISTS trades;
DROP TABLE IF EXISTS sessions;

-- Sessions table (enhanced)
CREATE TABLE sessions (
  id INT AUTO_INCREMENT PRIMARY KEY,
  session_id VARCHAR(255) UNIQUE NOT NULL,
  balance DECIMAL(12,2) DEFAULT 10000.00,
  equity DECIMAL(12,2) DEFAULT 10000.00,
  margin DECIMAL(12,2) DEFAULT 0.00,
  free_margin DECIMAL(12,2) DEFAULT 10000.00,
  margin_level DECIMAL(8,2) DEFAULT 0.00,
  profit DECIMAL(12,2) DEFAULT 0.00,
  goal DECIMAL(12,2) DEFAULT 0.00,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  last_activity TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  INDEX idx_session_id (session_id),
  INDEX idx_last_activity (last_activity)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Positions table (active trades)
CREATE TABLE positions (
  id INT AUTO_INCREMENT PRIMARY KEY,
  session_id VARCHAR(255) NOT NULL,
  position_id VARCHAR(50) UNIQUE NOT NULL,
  pair VARCHAR(20) NOT NULL,
  type ENUM('buy', 'sell') NOT NULL,
  lot_size DECIMAL(10,2) NOT NULL,
  entry_price DECIMAL(12,5) NOT NULL,
  current_price DECIMAL(12,5) NOT NULL,
  stop_loss DECIMAL(12,5) DEFAULT NULL,
  take_profit DECIMAL(12,5) DEFAULT NULL,
  pips INT DEFAULT 0,
  profit DECIMAL(12,2) DEFAULT 0.00,
  amount VARCHAR(20) DEFAULT NULL,
  open_time DATETIME NOT NULL,
  status ENUM('open', 'closed') DEFAULT 'open',
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  INDEX idx_session (session_id),
  INDEX idx_position_id (position_id),
  INDEX idx_status (status),
  INDEX idx_pair (pair),
  FOREIGN KEY (session_id) REFERENCES sessions(session_id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Trade history (closed positions archive)
CREATE TABLE trade_history (
  id INT AUTO_INCREMENT PRIMARY KEY,
  session_id VARCHAR(255) NOT NULL,
  position_id VARCHAR(50) NOT NULL,
  pair VARCHAR(20) NOT NULL,
  type ENUM('buy', 'sell') NOT NULL,
  lot_size DECIMAL(10,2) NOT NULL,
  entry_price DECIMAL(12,5) NOT NULL,
  exit_price DECIMAL(12,5) NOT NULL,
  stop_loss DECIMAL(12,5) DEFAULT NULL,
  take_profit DECIMAL(12,5) DEFAULT NULL,
  pips INT DEFAULT 0,
  profit DECIMAL(12,2) NOT NULL,
  amount VARCHAR(20) DEFAULT NULL,
  open_time DATETIME NOT NULL,
  close_time DATETIME NOT NULL,
  duration_seconds INT DEFAULT 0,
  close_reason ENUM('manual', 'stop_loss', 'take_profit', 'trailing_stop') DEFAULT 'manual',
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  INDEX idx_session (session_id),
  INDEX idx_pair (pair),
  INDEX idx_close_time (close_time),
  INDEX idx_profit (profit),
  FOREIGN KEY (session_id) REFERENCES sessions(session_id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- User settings (per session preferences)
CREATE TABLE user_settings (
  id INT AUTO_INCREMENT PRIMARY KEY,
  session_id VARCHAR(255) UNIQUE NOT NULL,
  pairs TEXT DEFAULT NULL COMMENT 'JSON array of selected pairs',
  default_lot_size DECIMAL(10,2) DEFAULT 0.01,
  risk_percentage DECIMAL(5,2) DEFAULT 2.00,
  stop_loss_pips INT DEFAULT 20,
  take_profit_pips INT DEFAULT 40,
  trailing_stop TINYINT(1) DEFAULT 0,
  positions_count INT DEFAULT 5,
  profit_bias DECIMAL(3,2) DEFAULT 0.75,
  profit_scale DECIMAL(5,2) DEFAULT 1.00,
  price_volatility DECIMAL(5,2) DEFAULT 1.00,
  sim_speed VARCHAR(20) DEFAULT 'normal',
  chart_timeframe VARCHAR(10) DEFAULT '1m',
  chart_tf_seconds INT DEFAULT 60,
  auto_trade TINYINT(1) DEFAULT 0,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  FOREIGN KEY (session_id) REFERENCES sessions(session_id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Chart state (per-pair chart data cache)
CREATE TABLE chart_state (
  id INT AUTO_INCREMENT PRIMARY KEY,
  session_id VARCHAR(255) NOT NULL,
  pair VARCHAR(20) NOT NULL,
  anchor_price DECIMAL(12,5) DEFAULT NULL,
  base_price DECIMAL(12,5) DEFAULT NULL,
  last_time INT DEFAULT NULL,
  candles_data TEXT DEFAULT NULL COMMENT 'JSON array of recent candles',
  updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  UNIQUE KEY unique_session_pair (session_id, pair),
  INDEX idx_session (session_id),
  INDEX idx_pair (pair),
  FOREIGN KEY (session_id) REFERENCES sessions(session_id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Analytics daily (aggregated statistics per day)
CREATE TABLE analytics_daily (
  id INT AUTO_INCREMENT PRIMARY KEY,
  session_id VARCHAR(255) NOT NULL,
  date DATE NOT NULL,
  total_trades INT DEFAULT 0,
  winning_trades INT DEFAULT 0,
  losing_trades INT DEFAULT 0,
  gross_profit DECIMAL(12,2) DEFAULT 0.00,
  gross_loss DECIMAL(12,2) DEFAULT 0.00,
  net_profit DECIMAL(12,2) DEFAULT 0.00,
  win_rate DECIMAL(5,2) DEFAULT 0.00,
  avg_win DECIMAL(12,2) DEFAULT 0.00,
  avg_loss DECIMAL(12,2) DEFAULT 0.00,
  largest_win DECIMAL(12,2) DEFAULT 0.00,
  largest_loss DECIMAL(12,2) DEFAULT 0.00,
  total_pips INT DEFAULT 0,
  balance_start DECIMAL(12,2) DEFAULT 0.00,
  balance_end DECIMAL(12,2) DEFAULT 0.00,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  UNIQUE KEY unique_session_date (session_id, date),
  INDEX idx_session (session_id),
  INDEX idx_date (date),
  FOREIGN KEY (session_id) REFERENCES sessions(session_id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Legacy trades table (keep for compatibility, but minimal)
CREATE TABLE trades (
  id INT AUTO_INCREMENT PRIMARY KEY,
  session_id VARCHAR(255),
  pair VARCHAR(20),
  profit DECIMAL(8,2),
  timestamp DATETIME DEFAULT CURRENT_TIMESTAMP,
  INDEX idx_session (session_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
