<?php
defined('APP_EXEC') or die('Acesso direto não permitido.');

class Auth {
    /**
     * Verifica se o usuário atual está autenticado na sessão.
     *
     * @return bool
     */
    public static function check(): bool {
        if (session_status() === PHP_SESSION_NONE) {
            return false;
        }
        return isset($_SESSION['user_id']);
    }

    /**
     * Retorna o ID do usuário logado na sessão ou null se não autenticado.
     *
     * @return int|null
     */
    public static function getUserId(): ?int {
        if (self::check()) {
            return (int)$_SESSION['user_id'];
        }
        return null;
    }

    /**
     * Restringe o acesso apenas a usuários autenticados.
     * Se não estiver logado, redireciona para a página de login.
     *
     * @return void
     */
    public static function requireAuth(): void {
        if (!self::check()) {
            self::redirect('/login');
        }
    }

    /**
     * Restringe o acesso apenas a visitantes não autenticados (guests).
     * Se já estiver logado, redireciona para o dashboard.
     *
     * @return void
     */
    public static function requireGuest(): void {
        if (self::check()) {
            self::redirect('/dashboard');
        }
    }

    /**
     * Realiza um redirecionamento seguro com base na URL base do config.
     *
     * @param string $path Caminho relativo (ex: '/login', '/dashboard')
     * @return void
     */
    public static function redirect(string $path): void {
        $configPath = dirname(dirname(__DIR__)) . '/config/config.php';
        $appUrl = 'http://localhost:8080/sistema_financeiro';
        if (file_exists($configPath)) {
            $config = require $configPath;
            $appUrl = $config['app_url'] ?? $appUrl;
        }

        if ($path !== '' && $path[0] !== '/') {
            $path = '/' . $path;
        }

        header("Location: " . $appUrl . $path);
        exit;
    }
}
