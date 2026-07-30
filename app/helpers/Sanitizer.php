<?php
defined('APP_EXEC') or die('Acesso direto não permitido.');

class Sanitizer {
    /**
     * Escapa strings dinâmicas para prevenir Cross-Site Scripting (XSS).
     *
     * @param string|null $value
     * @return string
     */
    public static function e(?string $value): string {
        if ($value === null) {
            return '';
        }
        return htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
    }

    /**
     * Sanitiza strings de entrada, removendo tags HTML indesejadas.
     *
     * @param string $value
     * @return string
     */
    public static function cleanString(string $value): string {
        return strip_tags(trim($value));
    }

    /**
     * Sanitiza e valida e-mail.
     *
     * @param string $email
     * @return string Retorna o e-mail validado ou uma string vazia
     */
    public static function cleanEmail(string $email): string {
        $email = filter_var(trim($email), FILTER_SANITIZE_EMAIL);
        return filter_var($email, FILTER_VALIDATE_EMAIL) ? $email : '';
    }
}
