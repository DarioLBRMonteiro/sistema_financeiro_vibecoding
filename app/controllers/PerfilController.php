<?php
defined('APP_EXEC') or die('Acesso direto não permitido.');

require_once dirname(__DIR__) . '/models/Usuario.php';

class PerfilController {
    public function senha() {
        Auth::requireAuth();

        $erro = null;
        $sucesso = null;

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            try {
                if (!CSRF::validateToken($_POST['csrf_token'] ?? '')) {
                    http_response_code(403);
                    Logger::logSecurity("Falha de validacao CSRF em Alteracao de Senha", Auth::getUserId());
                    die('Sessão expirada ou requisição inválida.');
                }

                $senhaAtual = $_POST['senha_atual'] ?? '';
                $novaSenha = $_POST['nova_senha'] ?? '';
                $confirmarNovaSenha = $_POST['confirmar_nova_senha'] ?? '';
                $userId = Auth::getUserId();

                $user = Usuario::findById($userId);

                if (!$user) {
                    throw new Exception("Usuário não encontrado.");
                }

                if (!password_verify($senhaAtual, $user['senha'])) {
                    $erro = "A senha atual está incorreta.";
                } elseif (mb_strlen($novaSenha) < 8 || !preg_match('/[A-Za-z]/', $novaSenha) || !preg_match('/[0-9]/', $novaSenha)) {
                    $erro = "A nova senha deve ter no mínimo 8 caracteres, contendo letras e números.";
                } elseif ($novaSenha === $senhaAtual) {
                    $erro = "A nova senha não pode ser igual à senha atual.";
                } elseif ($novaSenha !== $confirmarNovaSenha) {
                    $erro = "A nova senha e a confirmação não coincidem.";
                } else {
                    Usuario::updatePassword($userId, $novaSenha);
                    $sucesso = "Sua senha foi alterada com sucesso!";
                    Logger::logSecurity("Alteracao de senha bem-sucedida", $userId);
                }
            } catch (Exception $e) {
                Logger::logError("Erro ao alterar senha", $e);
                $erro = "Ocorreu um erro interno ao processar sua solicitação. Tente novamente mais tarde.";
            }
        }

        $title = "Alterar Senha";
        require dirname(__DIR__) . '/views/perfil/senha.php';
    }
}
