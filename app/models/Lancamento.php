<?php
defined('APP_EXEC') or die('Acesso direto não permitido.');

class Lancamento {

    public static function getResumoMes(PDO $pdo, int $usuarioId, int $mes, int $ano): array {
        $stmt = $pdo->prepare("
            SELECT 
                COALESCE(SUM(CASE WHEN tipo = 'RECEITA' THEN valor ELSE 0 END), 0) AS total_receitas,
                COALESCE(SUM(CASE WHEN tipo = 'DESPESA' THEN valor ELSE 0 END), 0) AS total_despesas
            FROM lancamentos 
            WHERE usuario_id = :usuario_id 
              AND deleted_at IS NULL
              AND MONTH(data_movimentacao) = :mes 
              AND YEAR(data_movimentacao) = :ano
        ");
        $stmt->execute([
            ':usuario_id' => $usuarioId,
            ':mes' => $mes,
            ':ano' => $ano
        ]);
        $row = $stmt->fetch();
        
        $totalReceitas = (float) $row['total_receitas'];
        $totalDespesas = (float) $row['total_despesas'];
        
        return [
            'receitas' => $totalReceitas,
            'despesas' => $totalDespesas,
            'saldo' => $totalReceitas - $totalDespesas
        ];
    }

    public static function getDespesasPorCategoriaMes(PDO $pdo, int $usuarioId, int $mes, int $ano): array {
        $stmt = $pdo->prepare("
            SELECT c.nome, SUM(l.valor) AS total
            FROM lancamentos l
            LEFT JOIN categorias c ON l.categoria_id = c.id
            WHERE l.usuario_id = :usuario_id
              AND l.deleted_at IS NULL
              AND l.tipo = 'DESPESA'
              AND MONTH(l.data_movimentacao) = :mes 
              AND YEAR(l.data_movimentacao) = :ano
            GROUP BY c.id, c.nome
            ORDER BY total DESC
        ");
        $stmt->execute([
            ':usuario_id' => $usuarioId,
            ':mes' => $mes,
            ':ano' => $ano
        ]);
        return $stmt->fetchAll();
    }

    public static function getFluxoCaixaUltimosMeses(PDO $pdo, int $usuarioId, string $dataInicio): array {
        $stmt = $pdo->prepare("
            SELECT 
                YEAR(data_movimentacao) AS ano,
                MONTH(data_movimentacao) AS mes,
                COALESCE(SUM(CASE WHEN tipo = 'RECEITA' THEN valor ELSE 0 END), 0) AS receitas,
                COALESCE(SUM(CASE WHEN tipo = 'DESPESA' THEN valor ELSE 0 END), 0) AS despesas
            FROM lancamentos
            WHERE usuario_id = :usuario_id
              AND deleted_at IS NULL
              AND data_movimentacao >= :data_inicio
              AND data_movimentacao <= LAST_DAY(NOW())
            GROUP BY YEAR(data_movimentacao), MONTH(data_movimentacao)
            ORDER BY ano ASC, mes ASC
        ");
        $stmt->execute([
            ':usuario_id' => $usuarioId,
            ':data_inicio' => $dataInicio
        ]);
        return $stmt->fetchAll();
    }

    public static function buscarExtrato(PDO $pdo, int $usuarioId, int $mes, int $ano, string $busca = '', string $categoriaId = ''): array {
        $sql = "
            SELECT 
                l.id, l.descricao, l.valor, l.data_movimentacao, 
                l.tipo, l.status, l.forma_pagamento, l.categoria_id,
                c.nome AS categoria_nome
            FROM lancamentos l
            LEFT JOIN categorias c ON l.categoria_id = c.id
            WHERE l.usuario_id = :usuario_id
              AND l.deleted_at IS NULL
              AND MONTH(l.data_movimentacao) = :mes
              AND YEAR(l.data_movimentacao) = :ano
        ";
        $params = [
            ':usuario_id' => $usuarioId,
            ':mes' => $mes,
            ':ano' => $ano
        ];

        if ($busca !== '') {
            $buscaEscaped = addcslashes($busca, '%_\\');
            $sql .= " AND l.descricao LIKE :busca";
            $params[':busca'] = '%' . $buscaEscaped . '%';
        }

        if ($categoriaId !== '') {
            $sql .= " AND l.categoria_id = :categoria_id";
            $params[':categoria_id'] = $categoriaId;
        }

        $sql .= " ORDER BY l.data_movimentacao DESC, l.id DESC";

        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    public static function getCategoriasDisponiveisFiltro(PDO $pdo, int $usuarioId): array {
        $stmt = $pdo->prepare("
            SELECT id, nome, tipo 
            FROM categorias 
            WHERE (usuario_id IS NULL OR (usuario_id = :usuario_id AND deleted_at IS NULL))
            ORDER BY tipo ASC, nome ASC
        ");
        $stmt->execute([':usuario_id' => $usuarioId]);
        return $stmt->fetchAll();
    }

    public static function inserir(PDO $pdo, array $dados): bool {
        $stmt = $pdo->prepare("
            INSERT INTO lancamentos 
            (usuario_id, categoria_id, descricao, valor, data_movimentacao, tipo, status, forma_pagamento) 
            VALUES 
            (:usuario_id, :categoria_id, :descricao, :valor, :data_movimentacao, :tipo, :status, :forma_pagamento)
        ");
        return $stmt->execute([
            ':usuario_id' => $dados['usuario_id'],
            ':categoria_id' => $dados['categoria_id'],
            ':descricao' => $dados['descricao'],
            ':valor' => $dados['valor'],
            ':data_movimentacao' => $dados['data_movimentacao'],
            ':tipo' => $dados['tipo'],
            ':status' => $dados['status'],
            ':forma_pagamento' => $dados['forma_pagamento']
        ]);
    }

    public static function atualizar(PDO $pdo, array $dados): bool {
        $stmt = $pdo->prepare("
            UPDATE lancamentos 
            SET categoria_id = :categoria_id, 
                descricao = :descricao, 
                valor = :valor, 
                data_movimentacao = :data_movimentacao, 
                tipo = :tipo, 
                status = :status, 
                forma_pagamento = :forma_pagamento 
            WHERE id = :id 
              AND usuario_id = :usuario_id
        ");
        return $stmt->execute([
            ':categoria_id' => $dados['categoria_id'],
            ':descricao' => $dados['descricao'],
            ':valor' => $dados['valor'],
            ':data_movimentacao' => $dados['data_movimentacao'],
            ':tipo' => $dados['tipo'],
            ':status' => $dados['status'],
            ':forma_pagamento' => $dados['forma_pagamento'],
            ':id' => $dados['id'],
            ':usuario_id' => $dados['usuario_id']
        ]);
    }

    public static function excluirLogicamente(PDO $pdo, int $id, int $usuarioId): bool {
        $stmt = $pdo->prepare("
            UPDATE lancamentos 
            SET deleted_at = NOW() 
            WHERE id = :id 
              AND usuario_id = :usuario_id
        ");
        return $stmt->execute([
            ':id' => $id,
            ':usuario_id' => $usuarioId
        ]);
    }

    public static function buscarPorId(PDO $pdo, int $id, int $usuarioId): ?array {
        $stmt = $pdo->prepare("
            SELECT * FROM lancamentos 
            WHERE id = :id 
              AND usuario_id = :usuario_id 
              AND deleted_at IS NULL
        ");
        $stmt->execute([
            ':id' => $id,
            ':usuario_id' => $usuarioId
        ]);
        $result = $stmt->fetch();
        return $result ?: null;
    }
}
