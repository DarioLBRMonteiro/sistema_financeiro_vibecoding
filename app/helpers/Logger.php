<?php
defined('APP_EXEC') or die('Acesso direto não permitido.');

class Logger {
    private static ?string $logDir = null;

    private static function getLogDir(): string {
        if (self::$logDir === null) {
            self::$logDir = dirname(dirname(__DIR__)) . '/logs';
        }
        return self::$logDir;
    }

    public static function logError(string $message, ?Throwable $exception = null): void {
        $timestamp = date('Y-m-d H:i:s');
        $file = $exception ? $exception->getFile() : 'N/A';
        $line = $exception ? $exception->getLine() : 'N/A';
        $detail = $exception ? $exception->getMessage() : $message;
        
        $logMessage = sprintf("[%s] [ERROR] %s | File: %s | Line: %s\n", $timestamp, $detail, $file, $line);
        self::writeLog('error.log', $logMessage);
    }

    public static function logSecurity(string $event, ?int $userId = null): void {
        $timestamp = date('Y-m-d H:i:s');
        $userIdStr = $userId !== null ? (string)$userId : 'Nao autenticado';
        $ip = $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1';
        
        $logMessage = sprintf("[%s] [SECURITY] %s | UserID: %s | IP: %s\n", $timestamp, $event, $userIdStr, $ip);
        self::writeLog('security.log', $logMessage);
    }

    private static function writeLog(string $filename, string $message): void {
        $dir = self::getLogDir();
        if (!is_dir($dir)) {
            @mkdir($dir, 0755, true);
        }

        $filepath = $dir . '/' . $filename;
        
        // Contingência secundária se falhar a escrita direta
        try {
            $result = @file_put_contents($filepath, $message, FILE_APPEND | LOCK_EX);
            if ($result === false) {
                error_log("Contingencia - GFS Log: " . trim($message));
            }
        } catch (Throwable $e) {
            error_log("Contingencia - GFS Log: " . trim($message) . " | Falha: " . $e->getMessage());
        }
    }
}
