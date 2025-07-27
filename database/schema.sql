-- Trading Journal Database Schema
-- Database: trade_template

-- Drop existing tables if they exist (be careful in production!)
SET FOREIGN_KEY_CHECKS = 0;
DROP TABLE IF EXISTS trade_invalidation_logs;
DROP TABLE IF EXISTS trade_checklist_logs;
DROP TABLE IF EXISTS trade_screenshots;
DROP TABLE IF EXISTS trades;
DROP TABLE IF EXISTS invalidations;
DROP TABLE IF EXISTS exit_criteria;
DROP TABLE IF EXISTS entry_criteria;
DROP TABLE IF EXISTS strategies;
SET FOREIGN_KEY_CHECKS = 1;

-- Core strategy templates
CREATE TABLE strategies (
  id INT AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(255) NOT NULL,
  instrument VARCHAR(50) NOT NULL,
  timeframes JSON NOT NULL,
  sessions JSON NOT NULL,
  chart_image_url VARCHAR(255),
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  INDEX idx_name (name),
  INDEX idx_instrument (instrument)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Entry criteria for strategies
CREATE TABLE entry_criteria (
  id INT AUTO_INCREMENT PRIMARY KEY,
  strategy_id INT NOT NULL,
  label VARCHAR(255) NOT NULL,
  description TEXT,
  sort_order INT DEFAULT 0,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (strategy_id) REFERENCES strategies(id) ON DELETE CASCADE,
  INDEX idx_strategy_sort (strategy_id, sort_order)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Exit criteria for strategies
CREATE TABLE exit_criteria (
  id INT AUTO_INCREMENT PRIMARY KEY,
  strategy_id INT NOT NULL,
  label VARCHAR(255) NOT NULL,
  description TEXT,
  sort_order INT DEFAULT 0,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (strategy_id) REFERENCES strategies(id) ON DELETE CASCADE,
  INDEX idx_strategy_sort (strategy_id, sort_order)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Invalidation conditions
CREATE TABLE invalidations (
  id INT AUTO_INCREMENT PRIMARY KEY,
  strategy_id INT NOT NULL,
  label VARCHAR(255) NOT NULL,
  reason TEXT,
  code CHAR(1),
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (strategy_id) REFERENCES strategies(id) ON DELETE CASCADE,
  INDEX idx_strategy (strategy_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Trade logs
CREATE TABLE trades (
  id INT AUTO_INCREMENT PRIMARY KEY,
  strategy_id INT NOT NULL,
  taken BOOLEAN NOT NULL DEFAULT TRUE,
  missed_reason TEXT,
  direction ENUM('Long', 'Short') NOT NULL,
  session ENUM('Asia', 'London', 'New York', 'All') NOT NULL,
  bias VARCHAR(50),
  trade_timestamp DATETIME NOT NULL,
  entry_price DECIMAL(10,5),
  stop_loss_price DECIMAL(10,5),
  exit_price DECIMAL(10,5),
  risk_percent DECIMAL(5,2),
  r_multiple DECIMAL(5,2),
  reason TEXT,
  emotional_notes TEXT,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  FOREIGN KEY (strategy_id) REFERENCES strategies(id),
  INDEX idx_strategy_date (strategy_id, trade_timestamp),
  INDEX idx_taken (taken),
  INDEX idx_direction (direction)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Trade screenshots
CREATE TABLE trade_screenshots (
  id INT AUTO_INCREMENT PRIMARY KEY,
  trade_id INT NOT NULL,
  image_path VARCHAR(255) NOT NULL,
  uploaded_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (trade_id) REFERENCES trades(id) ON DELETE CASCADE,
  INDEX idx_trade (trade_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Track which criteria were checked for each trade
CREATE TABLE trade_checklist_logs (
  id INT AUTO_INCREMENT PRIMARY KEY,
  trade_id INT NOT NULL,
  checklist_type ENUM('entry', 'exit') NOT NULL,
  criteria_id INT NOT NULL,
  checked BOOLEAN DEFAULT FALSE,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (trade_id) REFERENCES trades(id) ON DELETE CASCADE,
  INDEX idx_trade_type (trade_id, checklist_type)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Track which invalidations were active
CREATE TABLE trade_invalidation_logs (
  id INT AUTO_INCREMENT PRIMARY KEY,
  trade_id INT NOT NULL,
  invalidation_id INT NOT NULL,
  active BOOLEAN DEFAULT FALSE,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (trade_id) REFERENCES trades(id) ON DELETE CASCADE,
  FOREIGN KEY (invalidation_id) REFERENCES invalidations(id),
  INDEX idx_trade (trade_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Insert sample strategy for testing
INSERT INTO strategies (name, instrument, timeframes, sessions) VALUES 
('London Breakout Strategy', 'EURUSD', '["M15", "H1", "H4"]', '["London", "New York"]');

-- Get the strategy ID
SET @strategy_id = LAST_INSERT_ID();

-- Insert sample entry criteria
INSERT INTO entry_criteria (strategy_id, label, description, sort_order) VALUES 
(@strategy_id, 'Asian Range Defined', 'Asian session range is clearly formed and narrow', 1),
(@strategy_id, 'Liquidity Sweep', 'Price swept Asian high/low before setup', 2),
(@strategy_id, 'Volume Confirmation', 'Strong volume on breakout candle', 3);

-- Insert sample exit criteria
INSERT INTO exit_criteria (strategy_id, label, description, sort_order) VALUES 
(@strategy_id, 'TP Hit / Structure Broken', 'Take profit based on structure break', 1),
(@strategy_id, 'Time-Based Exit', 'Exit if trade runs past session close', 2);

-- Insert sample invalidations
INSERT INTO invalidations (strategy_id, label, reason, code) VALUES 
(@strategy_id, 'High-Impact News', 'NFP or other high impact news within 30 mins', 'A'),
(@strategy_id, 'No Clean Range', 'Asian session had wide/choppy price action', 'B');

-- Grant privileges if needed (adjust username as necessary)
-- GRANT ALL PRIVILEGES ON trade_template.* TO 'root'@'localhost';
-- FLUSH PRIVILEGES;