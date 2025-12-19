<?php
/**
 * ZYN Trade System - Database Backup System
 *
 * CARA PAKAI:
 * 1. Akses halaman ini sebagai admin: /admin/backup.php
 * 2. Klik tombol "Backup Now" untuk backup manual
 * 3. Setup cron job untuk backup otomatis (lihat instruksi di bawah)
 *
 * CARA SETUP CRON JOB (backup otomatis setiap hari jam 2 pagi):
 * 1. Buka terminal/SSH
 * 2. Ketik: crontab -e
 * 3. Tambahkan: 0 2 * * * php /path/to/admin/backup.php --cron
 * 4. Simpan dan keluar
 */

require_once '../includes/config.php';
require_once '../includes/functions.php';
require_once '../includes/language.php';
require_once '../includes/logger.php';

// Check if running from cron
$is_cron = (php_sapi_name() === 'cli' || isset($argv[1]) && $argv[1] === '--cron');

// Require admin login if not cron
if (!$is_cron) {
    if (!isLoggedIn() || !isAdmin()) {
        header('Location: /login.php');
        exit;
    }
}

class DatabaseBackup {
    private $db;
    private $backup_dir;
    private $max_backups = 30; // Keep last 30 backups

    public function __construct($db_config) {
        $this->backup_dir = dirname(__DIR__) . '/backups';

        // Create backup directory
        if (!is_dir($this->backup_dir)) {
            mkdir($this->backup_dir, 0755, true);
            file_put_contents($this->backup_dir . '/.htaccess', 'Deny from all');
        }

        // Connect to database
        try {
            $this->db = new PDO(
                "mysql:host={$db_config['host']};dbname={$db_config['name']};charset=utf8mb4",
                $db_config['user'],
                $db_config['pass'],
                [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
            );
        } catch (PDOException $e) {
            Logger::error('Database backup connection failed', ['error' => $e->getMessage()]);
            throw $e;
        }
    }

    /**
     * Create database backup
     */
    public function create() {
        $filename = 'backup_' . date('Y-m-d_His') . '.sql';
        $filepath = $this->backup_dir . '/' . $filename;

        try {
            // Get all tables
            $tables = $this->getTables();

            $sql = "-- ZYN Trade System Database Backup\n";
            $sql .= "-- Generated: " . date('Y-m-d H:i:s') . "\n";
            $sql .= "-- Server: " . ($_SERVER['SERVER_NAME'] ?? 'localhost') . "\n";
            $sql .= "-- Database: " . DB_NAME . "\n\n";
            $sql .= "SET FOREIGN_KEY_CHECKS=0;\n\n";

            foreach ($tables as $table) {
                // Get create table statement
                $sql .= $this->getCreateTable($table);

                // Get table data
                $sql .= $this->getTableData($table);
            }

            $sql .= "SET FOREIGN_KEY_CHECKS=1;\n";

            // Write to file
            file_put_contents($filepath, $sql);

            // Compress backup
            $gzFilepath = $filepath . '.gz';
            $gz = gzopen($gzFilepath, 'w9');
            gzwrite($gz, file_get_contents($filepath));
            gzclose($gz);

            // Remove uncompressed file
            unlink($filepath);

            // Cleanup old backups
            $this->cleanup();

            $filesize = filesize($gzFilepath);

            Logger::info('Database backup created', [
                'filename' => $filename . '.gz',
                'size' => $this->formatSize($filesize),
                'tables' => count($tables)
            ]);

            return [
                'success' => true,
                'filename' => $filename . '.gz',
                'filepath' => $gzFilepath,
                'size' => $filesize,
                'tables' => count($tables)
            ];

        } catch (Exception $e) {
            Logger::error('Database backup failed', ['error' => $e->getMessage()]);
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * Get all tables
     */
    private function getTables() {
        $stmt = $this->db->query("SHOW TABLES");
        return $stmt->fetchAll(PDO::FETCH_COLUMN);
    }

    /**
     * Get CREATE TABLE statement
     */
    private function getCreateTable($table) {
        $sql = "\n-- Table: {$table}\n";
        $sql .= "DROP TABLE IF EXISTS `{$table}`;\n";

        $stmt = $this->db->query("SHOW CREATE TABLE `{$table}`");
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        $sql .= $row['Create Table'] . ";\n\n";

        return $sql;
    }

    /**
     * Get table data as INSERT statements
     */
    private function getTableData($table) {
        $sql = "";

        $stmt = $this->db->query("SELECT * FROM `{$table}`");
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

        if (empty($rows)) {
            return $sql;
        }

        $columns = array_keys($rows[0]);
        $columnList = '`' . implode('`, `', $columns) . '`';

        $sql .= "INSERT INTO `{$table}` ({$columnList}) VALUES\n";

        $values = [];
        foreach ($rows as $row) {
            $rowValues = [];
            foreach ($row as $value) {
                if ($value === null) {
                    $rowValues[] = 'NULL';
                } else {
                    $rowValues[] = $this->db->quote($value);
                }
            }
            $values[] = '(' . implode(', ', $rowValues) . ')';
        }

        $sql .= implode(",\n", $values) . ";\n\n";

        return $sql;
    }

    /**
     * Cleanup old backups
     */
    private function cleanup() {
        $files = glob($this->backup_dir . '/backup_*.sql.gz');
        usort($files, function($a, $b) {
            return filemtime($b) - filemtime($a);
        });

        // Remove files beyond max_backups
        $to_delete = array_slice($files, $this->max_backups);
        foreach ($to_delete as $file) {
            unlink($file);
        }

        return count($to_delete);
    }

    /**
     * List all backups
     */
    public function listBackups() {
        $files = glob($this->backup_dir . '/backup_*.sql.gz');
        $backups = [];

        foreach ($files as $file) {
            $backups[] = [
                'filename' => basename($file),
                'size' => filesize($file),
                'size_formatted' => $this->formatSize(filesize($file)),
                'date' => date('Y-m-d H:i:s', filemtime($file)),
                'age' => $this->formatAge(filemtime($file))
            ];
        }

        // Sort by date descending
        usort($backups, function($a, $b) {
            return strtotime($b['date']) - strtotime($a['date']);
        });

        return $backups;
    }

    /**
     * Download backup file
     */
    public function download($filename) {
        $filepath = $this->backup_dir . '/' . basename($filename);

        if (!file_exists($filepath)) {
            return false;
        }

        header('Content-Type: application/gzip');
        header('Content-Disposition: attachment; filename="' . basename($filepath) . '"');
        header('Content-Length: ' . filesize($filepath));
        readfile($filepath);
        exit;
    }

    /**
     * Delete backup file
     */
    public function delete($filename) {
        $filepath = $this->backup_dir . '/' . basename($filename);

        if (file_exists($filepath)) {
            unlink($filepath);
            Logger::info('Backup deleted', ['filename' => $filename]);
            return true;
        }

        return false;
    }

    /**
     * Restore from backup
     */
    public function restore($filename) {
        $filepath = $this->backup_dir . '/' . basename($filename);

        if (!file_exists($filepath)) {
            return ['success' => false, 'error' => 'File not found'];
        }

        try {
            // Read compressed file
            $sql = gzfile($filepath);
            $sql = implode('', $sql);

            // Execute SQL
            $this->db->exec($sql);

            Logger::info('Database restored from backup', ['filename' => $filename]);

            return ['success' => true];

        } catch (Exception $e) {
            Logger::error('Database restore failed', ['error' => $e->getMessage()]);
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    private function formatSize($bytes) {
        $units = ['B', 'KB', 'MB', 'GB'];
        $i = 0;
        while ($bytes >= 1024 && $i < count($units) - 1) {
            $bytes /= 1024;
            $i++;
        }
        return round($bytes, 2) . ' ' . $units[$i];
    }

    private function formatAge($timestamp) {
        $diff = time() - $timestamp;

        if ($diff < 60) return __('time_just_now');
        if ($diff < 3600) return floor($diff / 60) . ' ' . __('time_minutes_ago');
        if ($diff < 86400) return floor($diff / 3600) . ' ' . __('time_hours_ago');
        if ($diff < 2592000) return floor($diff / 86400) . ' ' . __('time_days_ago');

        return date('d M Y', $timestamp);
    }
}

// Database config
$db_config = [
    'host' => DB_HOST ?? 'localhost',
    'name' => DB_NAME ?? 'zyn_trade',
    'user' => DB_USER ?? 'root',
    'pass' => DB_PASS ?? ''
];

// Initialize backup system
$backup = new DatabaseBackup($db_config);

// Handle actions
$message = '';
$message_type = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' || $is_cron) {
    // Verify CSRF token for web requests (not cron)
    if (!$is_cron && !verifyCSRFToken($_POST['csrf_token'] ?? '')) {
        $message = __('error_invalid_csrf');
        $message_type = 'error';
    } else {
        $action = $_POST['action'] ?? ($is_cron ? 'backup' : '');

    switch ($action) {
        case 'backup':
            $result = $backup->create();
            if ($result['success']) {
                $message = __('backup_success') . ": {$result['filename']} ({$backup->formatSize($result['size'])})";
                $message_type = 'success';
            } else {
                $message = __('backup_failed') . ": {$result['error']}";
                $message_type = 'error';
            }
            break;

        case 'download':
            $backup->download($_POST['filename']);
            break;

        case 'delete':
            if ($backup->delete($_POST['filename'])) {
                $message = __('backup_deleted');
                $message_type = 'success';
            } else {
                $message = __('backup_delete_failed');
                $message_type = 'error';
            }
            break;

        case 'restore':
            $result = $backup->restore($_POST['filename']);
            if ($result['success']) {
                $message = __('backup_restored');
                $message_type = 'success';
            } else {
                $message = __('backup_restore_failed') . ": {$result['error']}";
                $message_type = 'error';
            }
            break;
    }
    } // end CSRF check else
}

// If running from cron, exit here
if ($is_cron) {
    echo $message . "\n";
    exit;
}

// Get backup list
$backups = $backup->listBackups();

$page_title = __('admin_backup');
require_once 'includes/admin-header.php';
?>

<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-0"><?php _e('backup_title'); ?></h1>
            <p class="text-muted mb-0"><?php _e('backup_subtitle'); ?></p>
        </div>
        <form method="POST" class="d-inline">
            <input type="hidden" name="csrf_token" value="<?php echo generateCSRFToken(); ?>">
            <input type="hidden" name="action" value="backup">
            <button type="submit" class="btn btn-primary">
                <i class="fas fa-download me-2"></i><?php _e('backup_now'); ?>
            </button>
        </form>
    </div>

    <?php if ($message): ?>
    <div class="alert alert-<?php echo $message_type === 'success' ? 'success' : 'danger'; ?> alert-dismissible fade show">
        <?php echo htmlspecialchars($message); ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
    <?php endif; ?>

    <!-- Info Cards -->
    <div class="row g-4 mb-4">
        <div class="col-md-4">
            <div class="card bg-primary text-white">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-shrink-0">
                            <i class="fas fa-database fa-2x"></i>
                        </div>
                        <div class="flex-grow-1 ms-3">
                            <h5 class="mb-0"><?php echo count($backups); ?></h5>
                            <small><?php _e('backup_total'); ?></small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card bg-success text-white">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-shrink-0">
                            <i class="fas fa-clock fa-2x"></i>
                        </div>
                        <div class="flex-grow-1 ms-3">
                            <h5 class="mb-0"><?php echo !empty($backups) ? $backups[0]['age'] : __('backup_no_backup'); ?></h5>
                            <small><?php _e('backup_last'); ?></small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card bg-info text-white">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-shrink-0">
                            <i class="fas fa-hdd fa-2x"></i>
                        </div>
                        <div class="flex-grow-1 ms-3">
                            <?php
                            $total_size = array_sum(array_column($backups, 'size'));
                            ?>
                            <h5 class="mb-0"><?php echo $backup->formatSize($total_size); ?></h5>
                            <small><?php _e('backup_total_size'); ?></small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Backup List -->
    <div class="card">
        <div class="card-header">
            <h5 class="mb-0"><i class="fas fa-list me-2"></i><?php _e('backup_list'); ?></h5>
        </div>
        <div class="card-body">
            <?php if (empty($backups)): ?>
            <div class="text-center py-5">
                <i class="fas fa-database fa-3x text-muted mb-3"></i>
                <p class="text-muted"><?php _e('backup_empty'); ?></p>
            </div>
            <?php else: ?>
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th><?php _e('backup_filename'); ?></th>
                            <th><?php _e('backup_size'); ?></th>
                            <th><?php _e('backup_date'); ?></th>
                            <th><?php _e('backup_age'); ?></th>
                            <th><?php _e('backup_actions'); ?></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($backups as $b): ?>
                        <tr>
                            <td>
                                <i class="fas fa-file-archive text-warning me-2"></i>
                                <?php echo htmlspecialchars($b['filename']); ?>
                            </td>
                            <td><?php echo $b['size_formatted']; ?></td>
                            <td><?php echo $b['date']; ?></td>
                            <td><span class="badge bg-secondary"><?php echo $b['age']; ?></span></td>
                            <td>
                                <form method="POST" class="d-inline">
                                    <input type="hidden" name="csrf_token" value="<?php echo generateCSRFToken(); ?>">
                                    <input type="hidden" name="filename" value="<?php echo htmlspecialchars($b['filename']); ?>">
                                    <button type="submit" name="action" value="download" class="btn btn-sm btn-primary" title="Download">
                                        <i class="fas fa-download"></i>
                                    </button>
                                    <button type="submit" name="action" value="restore" class="btn btn-sm btn-warning" title="Restore" onclick="return confirm('<?php echo addslashes(__('backup_restore_confirm')); ?>')">
                                        <i class="fas fa-undo"></i>
                                    </button>
                                    <button type="submit" name="action" value="delete" class="btn btn-sm btn-danger" title="<?php _e('delete'); ?>" onclick="return confirm('<?php echo addslashes(__('backup_delete_confirm')); ?>')">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </form>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- Cron Setup Guide -->
    <div class="card mt-4">
        <div class="card-header">
            <h5 class="mb-0"><i class="fas fa-cog me-2"></i><?php _e('backup_cron_title'); ?></h5>
        </div>
        <div class="card-body">
            <p><?php _e('backup_cron_desc'); ?></p>
            <pre class="bg-dark text-light p-3 rounded"><code># Backup setiap hari jam 2 pagi
0 2 * * * php <?php echo __FILE__; ?> --cron

# Backup setiap 6 jam
0 */6 * * * php <?php echo __FILE__; ?> --cron

# Backup setiap minggu (Minggu jam 3 pagi)
0 3 * * 0 php <?php echo __FILE__; ?> --cron</code></pre>
            <p class="text-muted mb-0">
                <i class="fas fa-info-circle me-1"></i>
                <?php _e('backup_cron_help'); ?>
            </p>
        </div>
    </div>
</div>

<?php require_once 'includes/admin-footer.php'; ?>
