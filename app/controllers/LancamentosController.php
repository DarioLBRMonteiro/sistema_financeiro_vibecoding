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
}
