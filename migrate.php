<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Database Migration</title>
    <style>
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            max-width: 800px;
            margin: 50px auto;
            padding: 20px;
            background: #f5f5f5;
        }
        .container {
            background: white;
            padding: 30px;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        h1 { color: #333; margin-top: 0; }
        .warning {
            background: #fff3cd;
            border: 1px solid #ffc107;
            padding: 15px;
            border-radius: 5px;
            margin: 20px 0;
            color: #856404;
        }
        .success {
            background: #d4edda;
            border: 1px solid #28a745;
            padding: 15px;
            border-radius: 5px;
            margin: 20px 0;
            color: #155724;
        }
        .error {
            background: #f8d7da;
            border: 1px solid #dc3545;
            padding: 15px;
            border-radius: 5px;
            margin: 20px 0;
            color: #721c24;
        }
        button {
            background: #3498db;
            color: white;
            border: none;
            padding: 12px 24px;
            border-radius: 5px;
            cursor: pointer;
            font-size: 16px;
            margin-right: 10px;
        }
        button:hover { background: #2980b9; }
        button.danger {
            background: #e74c3c;
        }
        button.danger:hover { background: #c0392b; }
        pre {
            background: #f4f4f4;
            padding: 15px;
            border-radius: 5px;
            overflow-x: auto;
        }
        .step { margin: 15px 0; padding-left: 20px; }
        .step::before { content: "✓ "; color: #28a745; font-weight: bold; }
    </style>
</head>
<body>
    <div class="container">
        <h1>🗄️ Database Migration Tool</h1>
        
        <?php
        require_once 'includes/db.php';

        $action = $_GET['action'] ?? '';
        
        if ($action === 'migrate'):
            try {
                // Read migration file
                $migration_file = __DIR__ . '/migrations/001_enhanced_schema.sql';
                if (!file_exists($migration_file)) {
                    throw new Exception("Migration file not found!");
                }
                
                $sql = file_get_contents($migration_file);
                
                // Split into individual statements
                $statements = array_filter(
                    array_map('trim', explode(';', $sql)),
                    function($stmt) {
                        return !empty($stmt) && !preg_match('/^--/', $stmt);
                    }
                );
                
                echo '<div class="success"><strong>Migration Started</strong></div>';
                
                $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
                
                foreach ($statements as $statement) {
                    if (trim($statement)) {
                        $pdo->exec($statement);
                        // Extract table name for display
                        if (preg_match('/CREATE TABLE\s+(\w+)/i', $statement, $matches)) {
                            echo '<div class="step">Created table: ' . htmlspecialchars($matches[1]) . '</div>';
                        } elseif (preg_match('/DROP TABLE.*?(\w+)/i', $statement, $matches)) {
                            echo '<div class="step">Dropped table: ' . htmlspecialchars($matches[1]) . '</div>';
                        }
                    }
                }
                
                echo '<div class="success"><strong>✅ Migration completed successfully!</strong></div>';
                echo '<p><a href="migrate.php"><button>Back to Migration Tool</button></a></p>';
                
            } catch (Exception $e) {
                echo '<div class="error"><strong>❌ Migration Error:</strong><br>' . htmlspecialchars($e->getMessage()) . '</div>';
                echo '<p><a href="migrate.php"><button>Back</button></a></p>';
            }
            
        elseif ($action === 'backup'):
            try {
                // Simple backup - export current data
                $backup_file = 'backups/backup_' . date('Y-m-d_His') . '.sql';
                
                if (!is_dir('backups')) {
                    mkdir('backups', 0755, true);
                }
                
                $tables = ['sessions', 'trades', 'positions', 'user_settings', 'chart_state', 'trade_history', 'analytics_daily'];
                $backup_content = "-- Backup created: " . date('Y-m-d H:i:s') . "\n\n";
                
                foreach ($tables as $table) {
                    try {
                        // Get table structure
                        $result = $pdo->query("SHOW CREATE TABLE `$table`");
                        if ($row = $result->fetch(PDO::FETCH_NUM)) {
                            $backup_content .= "\n\n-- Table: $table\n";
                            $backup_content .= "DROP TABLE IF EXISTS `$table`;\n";
                            $backup_content .= $row[1] . ";\n\n";
                            
                            // Get table data
                            $rows = $pdo->query("SELECT * FROM `$table`")->fetchAll(PDO::FETCH_ASSOC);
                            foreach ($rows as $row) {
                                $values = array_map(function($v) use ($pdo) {
                                    return $v === null ? 'NULL' : $pdo->quote($v);
                                }, array_values($row));
                                $backup_content .= "INSERT INTO `$table` VALUES (" . implode(', ', $values) . ");\n";
                            }
                        }
                    } catch (Exception $e) {
                        // Table might not exist yet, skip
                        continue;
                    }
                }
                
                file_put_contents($backup_file, $backup_content);
                echo '<div class="success"><strong>✅ Backup created:</strong> ' . htmlspecialchars($backup_file) . '</div>';
                echo '<p><a href="migrate.php"><button>Back</button></a></p>';
                
            } catch (Exception $e) {
                echo '<div class="error"><strong>❌ Backup Error:</strong><br>' . htmlspecialchars($e->getMessage()) . '</div>';
                echo '<p><a href="migrate.php"><button>Back</button></a></p>';
            }
            
        else:
            // Show migration options
        ?>
        
        <div class="warning">
            <strong>⚠️ Warning:</strong> This will modify your database schema. 
            All existing data in affected tables will be deleted. Make sure to backup first!
        </div>
        
        <h2>Migration Plan</h2>
        <p>This migration will:</p>
        <ul>
            <li>Drop existing tables (sessions, trades)</li>
            <li>Create enhanced schema with proper relations</li>
            <li>Add new tables: positions, trade_history, user_settings, chart_state, analytics_daily</li>
            <li>Set up foreign keys and indexes for performance</li>
        </ul>
        
        <h2>New Features After Migration</h2>
        <ul>
            <li>✅ Persistent positions across page reloads</li>
            <li>✅ Complete trade history with analytics</li>
            <li>✅ User settings stored in database</li>
            <li>✅ Chart state persistence</li>
            <li>✅ Daily performance analytics</li>
            <li>✅ Win/loss statistics tracking</li>
        </ul>
        
        <h2>Actions</h2>
        <p>
            <a href="?action=backup"><button>1. Create Backup First</button></a>
            <a href="?action=migrate"><button class="danger">2. Run Migration</button></a>
        </p>
        
        <hr style="margin: 30px 0;">
        
        <h2>Current Database Info</h2>
        <?php
        try {
            $result = $pdo->query("SHOW TABLES");
            $tables = $result->fetchAll(PDO::FETCH_COLUMN);
            echo '<p><strong>Existing tables:</strong></p>';
            echo '<pre>' . implode("\n", $tables) . '</pre>';
        } catch (Exception $e) {
            echo '<div class="error">Could not fetch table list: ' . htmlspecialchars($e->getMessage()) . '</div>';
        }
        ?>
        
        <?php endif; ?>
    </div>
</body>
</html>
