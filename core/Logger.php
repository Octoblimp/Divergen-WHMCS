<?php
namespace OpenWHM\Core;

/**
 * Logging System
 */
class Logger
{
    private static $levels = ['debug', 'info', 'warning', 'error'];
    
    /**
     * Log debug message
     */
    public static function debug($message, $context = [])
    {
        self::log('debug', $message, $context);
    }
    
    /**
     * Log info message
     */
    public static function info($message, $context = [])
    {
        self::log('info', $message, $context);
    }
    
    /**
     * Log warning message
     */
    public static function warning($message, $context = [])
    {
        self::log('warning', $message, $context);
    }
    
    /**
     * Log error message
     */
    public static function error($message, $context = [])
    {
        self::log('error', $message, $context);
    }
    
    /**
     * Write log entry
     */
    private static function log($level, $message, $context = [])
    {
        // Check if this level should be logged
        $configLevel = array_search(LOG_LEVEL, self::$levels);
        $messageLevel = array_search($level, self::$levels);
        
        if ($messageLevel < $configLevel) {
            return;
        }
        
        // Create log directory if needed
        if (!is_dir(LOG_PATH)) {
            mkdir(LOG_PATH, 0755, true);
        }
        
        // Format message
        $timestamp = date('Y-m-d H:i:s');
        $levelUpper = strtoupper($level);
        
        $logMessage = "[{$timestamp}] [{$levelUpper}] {$message}";
        
        if (!empty($context)) {
            $logMessage .= " " . json_encode($context);
        }
        
        $logMessage .= PHP_EOL;
        
        // Write to log file
        $logFile = LOG_PATH . '/' . date('Y-m-d') . '.log';
        file_put_contents($logFile, $logMessage, FILE_APPEND | LOCK_EX);
    }
    
    /**
     * Log activity to database
     */
    public static function activity($type, $description, $clientId = null, $adminId = null)
    {
        try {
            $db = Database::getInstance();
            $db->insert('activity_log', [
                'type' => $type,
                'description' => $description,
                'client_id' => $clientId,
                'admin_id' => $adminId,
                'ip_address' => $_SERVER['REMOTE_ADDR'] ?? null,
                'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? null,
                'created_at' => date('Y-m-d H:i:s')
            ]);
        } catch (\Exception $e) {
            self::error("Failed to log activity: " . $e->getMessage());
        }
    }
}
