<?php
defined('APP_EXEC') or die('Acesso direto não permitido.');

class CSRF {
    private static string $sessionKey = 'csrf_token';

    /**
     * Retorna o token CSRF atual da sessão, gerando um novo se necessário.
     *
     * @return string
     */
    public static function getToken(): string {
        if (session_status() === PHP_SESSION_NONE) {
            return '';
        }
        
        if (empty($_SESSION[self::$sessionKey])) {
            $_SESSION[self::$sessionKey] = bin2hex(random_bytes(32));
        }

        return $_SESSION[self::$sessionKey];
    }

    /**
     * Valida o token CSRF fornecido contra o token da sessão.
     * Se falhar, registra um evento de segurança, aborta com HTTP 403 e encerra.
     *
     * @param string|null $token Token a ser validado
     * @return bool
     */
    public static function validateToken(?string $token): bool {
        if (session_status() === PHP_SESSION_NONE) {
            self::abort("Sessão inativa no momento da validação CSRF.");
        }

        $sessionToken = $_SESSION[self::$sessionKey] ?? '';

        if (empty($sessionToken) || empty($token) || !hash_equals($sessionToken, $token)) {
            $userId = $_SESSION['user_id'] ?? null;
            $requestUri = $_SERVER['REQUEST_URI'] ?? 'Desconhecida';
            
            require_once __DIR__ . '/Logger.php';
            Logger::logSecurity("FALHA CSRF: Validação de token inválida ou ausente na rota [{$requestUri}]", $userId);
            
            self::abort("Token CSRF inválido ou ausente. Acesso negado.");
        }

        return true;
    }

    /**
     * Interrompe a execução exibindo HTTP 403 Forbidden.
     *
     * @param string $message
     * @return void
     */
    private static function abort(string $message): void {
        http_response_code(403);
        die("<h1>403 Forbidden</h1><p>" . htmlspecialchars($message, ENT_QUOTES, 'UTF-8') . "</p>");
    }
}
