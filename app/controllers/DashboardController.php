<?php
defined('APP_EXEC') or die('Acesso direto não permitido.');

require_once dirname(__DIR__) . '/models/Lancamento.php';

class DashboardController {
    public function index(): void {
        Auth::requireAuth();
        
        $pdo = Database::getConnection();
        $usuarioId = $_SESSION['user_id'];
        
        $mes = isset($_GET['mes']) ? (int)$_GET['mes'] : (int)date('m');
        $ano = isset($_GET['ano']) ? (int)$_GET['ano'] : (int)date('Y');
        
        if ($mes < 1 || $mes > 12) $mes = (int)date('m');
        if ($ano < 2000 || $ano > 2100) $ano = (int)date('Y');

        $resumo = Lancamento::getResumoMes($pdo, $usuarioId, $mes, $ano);
        
        // Dados para gráfico de Despesas por Categoria
        $despesasPorCategoria = Lancamento::getDespesasPorCategoriaMes($pdo, $usuarioId, $mes, $ano);
        
        // Dados para gráfico de Fluxo de Caixa (últimos 6 meses)
        $dataInicioFluxo = date('Y-m-d', strtotime('-5 months', strtotime(date('Y-m-01'))));
        $fluxoCaixaRaw = Lancamento::getFluxoCaixaUltimosMeses($pdo, $usuarioId, $dataInicioFluxo);
        
        // Preencher meses vazios para o gráfico de fluxo
        $fluxoCaixa = [];
        $mesesNomes = ['', 'Jan', 'Fev', 'Mar', 'Abr', 'Mai', 'Jun', 'Jul', 'Ago', 'Set', 'Out', 'Nov', 'Dez'];
        $mesAtualLoop = strtotime($dataInicioFluxo);
        for ($i = 0; $i < 6; $i++) {
            $a = (int)date('Y', $mesAtualLoop);
            $m = (int)date('m', $mesAtualLoop);
            
            $receitas = 0;
            $despesas = 0;
            foreach ($fluxoCaixaRaw as $row) {
                if ((int)$row['ano'] === $a && (int)$row['mes'] === $m) {
                    $receitas = (float)$row['receitas'];
                    $despesas = (float)$row['despesas'];
                    break;
                }
            }
            
            $label = $mesesNomes[$m] . '/' . substr((string)$a, 2);
            
            $fluxoCaixa[] = [
                'label' => $label,
                'receitas' => $receitas,
                'despesas' => $despesas
            ];
            
            $mesAtualLoop = strtotime('+1 month', $mesAtualLoop);
        }

        $title = 'Dashboard';
        require dirname(__DIR__) . '/views/dashboard/index.php';
    }
}
