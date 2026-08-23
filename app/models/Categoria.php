<?php
defined('APP_EXEC') or die('Acesso direto não permitido.');

require_once dirname(__DIR__) . '/../config/database.php';

class Categoria
{
    private $pdo;

    public function __construct()
    {
        $this->pdo = Database::getConnection();
    }

    /**
     * Lista todas as categorias ativas (do sistema + personalizadas do usuário)
     * Ordenadas pelo nome para facilitar a visualização no dropdown.
     */
    public function listarAtivasPorUsuario($usuario_id)
    {
        $sql = "SELECT id, nome, tipo, usuario_id 
                FROM categorias 
                WHERE (usuario_id IS NULL OR usuario_id = :usuario_id)
                  AND deleted_at IS NULL
                ORDER BY tipo, nome ASC";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([':usuario_id' => $usuario_id]);
        return $stmt->fetchAll();
    }

    /**
     * Adiciona uma nova categoria personalizada
     */
    public function adicionar($usuario_id, $nome, $tipo)
    {
        $sql = "INSERT INTO categorias (usuario_id, nome, tipo) 
                VALUES (:usuario_id, :nome, :tipo)";
        $stmt = $this->pdo->prepare($sql);
        return $stmt->execute([
            ':usuario_id' => $usuario_id,
            ':nome'       => $nome,
            ':tipo'       => $tipo
        ]);
    }

    /**
     * Exclui logicamente (soft delete) uma categoria personalizada do usuário
     */
    public function excluirLogicamente($id, $usuario_id)
    {
        $sql = "UPDATE categorias 
                SET deleted_at = NOW() 
                WHERE id = :id 
                  AND usuario_id = :usuario_id 
                  AND deleted_at IS NULL";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([
            ':id'         => $id,
            ':usuario_id' => $usuario_id
        ]);
        return $stmt->rowCount() > 0;
    }

    /**
     * Verifica se já existe uma categoria com o mesmo nome e tipo (case insensitive)
     * para o sistema (usuario_id IS NULL) ou para o próprio usuário ativo.
     */
    public function verificarDuplicidade($usuario_id, $nome, $tipo)
    {
        // Usa LOWER() para comparar de forma case-insensitive
        $sql = "SELECT id FROM categorias 
                WHERE LOWER(nome) = LOWER(:nome) 
                  AND tipo = :tipo 
                  AND (usuario_id IS NULL OR usuario_id = :usuario_id) 
                  AND deleted_at IS NULL 
                LIMIT 1";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([
            ':nome'       => trim($nome),
            ':tipo'       => $tipo,
            ':usuario_id' => $usuario_id
        ]);
        
        return $stmt->fetch() !== false;
    }
}
