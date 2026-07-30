<?php
defined('APP_EXEC') or die('Acesso direto não permitido.');

class Database {
    private static ?PDO $connection = null;

    public static function getConnection(): PDO {
        if (self::$connection === null) {
            $configPath = __DIR__ . '/config.php';
            if (!file_exists($configPath)) {
                self::handleError("Arquivo de configuração não encontrado.");
            }
            
            $config = require $configPath;
            $dbConfig = $config['db'];

            $dsn = sprintf(
                "mysql:host=%s;port=%s;dbname=%s;charset=%s",
                $dbConfig['host'],
                $dbConfig['port'],
                $dbConfig['dbname'],
                $dbConfig['charset']
            );

            try {
                self::$connection = new PDO($dsn, $dbConfig['user'], $dbConfig['pass'], [
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                    PDO::ATTR_EMULATE_PREPARES => false,
                ]);
            } catch (PDOException $e) {
                self::handleError("Erro na conexão com o banco de dados: " . $e->getMessage(), $e);
            }
        }

        return self::$connection;
    }

    private static function handleError(string $message, ?Exception $exception = null): void {
        $helperLogger = dirname(__DIR__) . '/app/helpers/Logger.php';
        if (file_exists($helperLogger)) {
            require_once $helperLogger;
            if (class_exists('Logger')) {
                Logger::logError($message, $exception);
            } else {
                error_log($message);
            }
        } else {
            error_log($message);
        }

        http_response_code(500);
        die("Ocorreu um erro interno ao processar sua solicitação. Tente novamente mais tarde.");
    }
}
