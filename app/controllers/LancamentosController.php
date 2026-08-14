<?php
defined('APP_EXEC') or die('Acesso direto não permitido.');

require_once dirname(__DIR__) . '/models/Lancamento.php';

class LancamentosController {
    public function extrato(): void {
        Auth::requireAuth();
        
        $pdo = Database::getConnection();
        $usuarioId = $_SESSION['user_id'];
        
        $mes = isset($_GET['mes']) ? (int)$_GET['mes'] : (int)date('m');
        $ano = isset($_GET['ano']) ? (int)$_GET['ano'] : (int)date('Y');
        
        if ($mes < 1 || $mes > 12) $mes = (int)date('m');
        if ($ano < 2000 || $ano > 2100) $ano = (int)date('Y');

        $busca = isset($_GET['busca']) ? trim($_GET['busca']) : '';
        $categoriaId = isset($_GET['categoria_id']) ? trim($_GET['categoria_id']) : '';

        $lancamentos = Lancamento::buscarExtrato($pdo, $usuarioId, $mes, $ano, $busca, $categoriaId);
        $categorias = Lancamento::getCategoriasDisponiveisFiltro($pdo, $usuarioId);
        
        $title = 'Extrato';
        require dirname(__DIR__) . '/views/extrato/index.php';
    }

    public function salvar(): void {
        Auth::requireAuth();
        
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            die('Método não permitido.');
        }

        CSRF::validateToken($_POST['csrf_token'] ?? '');

        $usuarioId = $_SESSION['user_id'];
        $pdo = Database::getConnection();

        // Validações
        $id = !empty($_POST['id']) ? (int)$_POST['id'] : null;
        $descricao = trim($_POST['descricao'] ?? '');
        $valor = isset($_POST['valor']) ? (float)$_POST['valor'] : 0.0;
        $dataMovimentacao = trim($_POST['data_movimentacao'] ?? '');
        $tipo = trim($_POST['tipo'] ?? '');
        $categoriaId = !empty($_POST['categoria_id']) ? (int)$_POST['categoria_id'] : null;
        $status = trim($_POST['status'] ?? '');
        $formaPagamento = trim($_POST['forma_pagamento'] ?? '');

        if (empty($descricao) || mb_strlen($descricao) > 255) {
            die('A descrição é obrigatória (máx. 255 caracteres).');
        }

        if (!is_numeric($valor) || $valor <= 0) {
            die('O valor deve ser um número positivo maior que zero.');
        }

        $d = DateTime::createFromFormat('Y-m-d', $dataMovimentacao);
        if (!$d || $d->format('Y-m-d') !== $dataMovimentacao) {
            die('Data inválida.');
        }

        if (!in_array($tipo, ['RECEITA', 'DESPESA'], true)) {
            die('Tipo de lançamento inválido.');
        }

        // Validar categoria_id
        if (!$categoriaId) {
            die('Categoria inválida.');
        }
        
        $stmtCat = $pdo->prepare("SELECT id FROM categorias WHERE id = :id AND (usuario_id IS NULL OR (usuario_id = :usuario_id AND deleted_at IS NULL))");
        $stmtCat->execute([':id' => $categoriaId, ':usuario_id' => $usuarioId]);
        if (!$stmtCat->fetch()) {
            die('Categoria inválida.');
        }

        if (!in_array($status, ['PAGO', 'PENDENTE'], true)) {
            die('Status inválido.');
        }

        if (!in_array($formaPagamento, ['DINHEIRO', 'PIX', 'DEBITO', 'CREDITO'], true)) {
            die('Forma de pagamento inválida.');
        }

        $dados = [
            'usuario_id' => $usuarioId,
            'categoria_id' => $categoriaId,
            'descricao' => $descricao,
            'valor' => $valor,
            'data_movimentacao' => $dataMovimentacao,
            'tipo' => $tipo,
            'status' => $status,
            'forma_pagamento' => $formaPagamento
        ];

        if ($id) {
            // Atualizar
            // Garantir que pertence ao usuário antes de salvar
            $lancamento = Lancamento::buscarPorId($pdo, $id, $usuarioId);
            if (!$lancamento) {
                die('Lançamento não encontrado ou não pertence a você.');
            }
            $dados['id'] = $id;
            Lancamento::atualizar($pdo, $dados);
        } else {
            // Inserir
            Lancamento::inserir($pdo, $dados);
        }

        // Voltar para extrato do mês selecionado
        $mes = (int)date('m', strtotime($dataMovimentacao));
        $ano = (int)date('Y', strtotime($dataMovimentacao));
        header('Location: ' . $GLOBALS['config']['app_url'] . '/extrato?mes=' . $mes . '&ano=' . $ano);
        exit;
    }

    public function excluir(): void {
        Auth::requireAuth();
        
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            die('Método não permitido.');
        }

        CSRF::validateToken($_POST['csrf_token'] ?? '');

        $usuarioId = $_SESSION['user_id'];
        $pdo = Database::getConnection();

        $id = !empty($_POST['id']) ? (int)$_POST['id'] : null;
        if (!$id) {
            die('ID inválido.');
        }

        // Tentar buscar primeiro (só para validar que pertence, e talvez pegar o mês para redirecionar)
        $lancamento = Lancamento::buscarPorId($pdo, $id, $usuarioId);
        if (!$lancamento) {
            die('Lançamento não encontrado ou não pertence a você.');
        }

        Lancamento::excluirLogicamente($pdo, $id, $usuarioId);

        $mes = (int)date('m', strtotime($lancamento['data_movimentacao']));
        $ano = (int)date('Y', strtotime($lancamento['data_movimentacao']));
        header('Location: ' . $GLOBALS['config']['app_url'] . '/extrato?mes=' . $mes . '&ano=' . $ano);
        exit;
    }
}
