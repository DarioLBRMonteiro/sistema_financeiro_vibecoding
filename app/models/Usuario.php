<?php
defined('APP_EXEC') or die('Acesso direto não permitido.');

class Usuario {
    /**
     * Busca um usuário pelo e-mail
     */
    public static function findByEmail(string $email): ?array {
        $db = Database::getConnection();
        $stmt = $db->prepare("SELECT * FROM usuarios WHERE email = :email");
        $stmt->bindValue(':email', $email, PDO::PARAM_STR);
        $stmt->execute();
        $user = $stmt->fetch();
        return $user ?: null;
    }

    /**
     * Busca um usuário pelo token de recuperação
     */
    public static function findByToken(string $token): ?array {
        $db = Database::getConnection();
        $stmt = $db->prepare("SELECT * FROM usuarios WHERE token_recuperacao = :token AND token_expiracao > NOW()");
        $stmt->bindValue(':token', $token, PDO::PARAM_STR);
        $stmt->execute();
        $user = $stmt->fetch();
        return $user ?: null;
    }

    /**
     * Cria um novo usuário
     */
    public static function create(string $nome, string $email, string $senha): int {
        $db = Database::getConnection();
        $stmt = $db->prepare("INSERT INTO usuarios (nome, email, senha) VALUES (:nome, :email, :senha)");
        $stmt->bindValue(':nome', $nome, PDO::PARAM_STR);
        $stmt->bindValue(':email', $email, PDO::PARAM_STR);
        
        $hash = password_hash($senha, PASSWORD_BCRYPT);
        $stmt->bindValue(':senha', $hash, PDO::PARAM_STR);
        
        $stmt->execute();
        return (int) $db->lastInsertId();
    }

    /**
     * Salva o token de recuperação para o usuário
     */
    public static function setRecoveryToken(string $email, string $token): bool {
        $db = Database::getConnection();
        $stmt = $db->prepare("UPDATE usuarios SET token_recuperacao = :token, token_expiracao = DATE_ADD(NOW(), INTERVAL 1 HOUR) WHERE email = :email");
        $stmt->bindValue(':token', $token, PDO::PARAM_STR);
        $stmt->bindValue(':email', $email, PDO::PARAM_STR);
        $stmt->execute();
        return $stmt->rowCount() > 0;
    }

    /**
     * Atualiza a senha e limpa o token de recuperação
     */
    public static function updatePasswordWithToken(int $id, string $novaSenha): bool {
        $db = Database::getConnection();
        $stmt = $db->prepare("UPDATE usuarios SET senha = :senha, token_recuperacao = NULL, token_expiracao = NULL WHERE id = :id");
        $hash = password_hash($novaSenha, PASSWORD_BCRYPT);
        $stmt->bindValue(':senha', $hash, PDO::PARAM_STR);
        $stmt->bindValue(':id', $id, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->rowCount() > 0;
    }
}
