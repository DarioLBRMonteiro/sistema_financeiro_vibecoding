<?php
defined('APP_EXEC') or die('Acesso direto não permitido.');
require_once dirname(__DIR__) . '/templates/header.php';

// Variáveis injetadas pelo controller: $mes, $ano, $resumo, $despesasPorCategoria, $fluxoCaixa

$mesAnterior = $mes - 1;
$anoAnterior = $ano;
if ($mesAnterior < 1) {
    $mesAnterior = 12;
    $anoAnterior--;
}

$mesProximo = $mes + 1;
$anoProximo = $ano;
if ($mesProximo > 12) {
    $mesProximo = 1;
    $anoProximo++;
}

$mesesNomes = ['', 'Janeiro', 'Fevereiro', 'Março', 'Abril', 'Maio', 'Junho', 'Julho', 'Agosto', 'Setembro', 'Outubro', 'Novembro', 'Dezembro'];
$nomeMesAtual = $mesesNomes[$mes];

$isSaldoNegativo = $resumo['saldo'] < 0;
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h1 class="headline-lg m-0" style="color: var(--gfs-primary);">Dashboard</h1>
    <div>
        <button type="button" class="btn fw-semibold text-white me-2" style="background-color: var(--gfs-primary); border-radius: var(--gfs-border-radius);" data-bs-toggle="modal" data-bs-target="#novoLancamentoModal">
            + Novo Lançamento
        </button>
        <a href="<?php echo Sanitizer::e($config['app_url']); ?>/extrato?mes=<?php echo $mes; ?>&ano=<?php echo $ano; ?>" class="btn btn-outline-secondary fw-semibold" style="border-radius: var(--gfs-border-radius); border-color: var(--gfs-primary); color: var(--gfs-primary);">
            Ver Extrato Completo
        </a>
    </div>
</div>

<!-- Barra de Navegação Temporal -->
<div class="card shadow-sm border-0 mb-4" style="border-radius: var(--gfs-border-radius-lg);">
    <div class="card-body py-2 d-flex justify-content-center align-items-center">
        <a href="?mes=<?php echo $mesAnterior; ?>&ano=<?php echo $anoAnterior; ?>" class="btn btn-sm btn-light text-secondary me-3">&lt; Anterior</a>
        <h2 class="headline-md m-0 text-center" style="min-width: 200px;"><?php echo $nomeMesAtual; ?> de <?php echo $ano; ?></h2>
        <a href="?mes=<?php echo $mesProximo; ?>&ano=<?php echo $anoProximo; ?>" class="btn btn-sm btn-light text-secondary ms-3">Próximo &gt;</a>
    </div>
</div>

<!-- Cartões KPI -->
<div class="row g-4 mb-4">
    <!-- Receitas -->
    <div class="col-md-4">
        <div class="card shadow-sm border-0 h-100" style="border-radius: var(--gfs-border-radius-lg); border-bottom: 4px solid var(--gfs-success) !important;">
            <div class="card-body">
                <p class="label-md text-secondary mb-1 text-uppercase">Total de Receitas</p>
                <h3 class="headline-lg m-0 text-success data-mono">
                    R$ <?php echo number_format($resumo['receitas'], 2, ',', '.'); ?>
                </h3>
            </div>
        </div>
    </div>
    
    <!-- Despesas -->
    <div class="col-md-4">
        <div class="card shadow-sm border-0 h-100" style="border-radius: var(--gfs-border-radius-lg); border-bottom: 4px solid var(--gfs-outline) !important;">
            <div class="card-body">
                <p class="label-md text-secondary mb-1 text-uppercase">Total de Despesas</p>
                <h3 class="headline-lg m-0 text-secondary data-mono">
                    R$ <?php echo number_format($resumo['despesas'], 2, ',', '.'); ?>
                </h3>
            </div>
        </div>
    </div>
    
    <!-- Saldo -->
    <div class="col-md-4">
        <div class="card shadow-sm border-0 h-100" style="border-radius: var(--gfs-border-radius-lg); border-bottom: 4px solid <?php echo $isSaldoNegativo ? 'var(--gfs-error)' : 'var(--gfs-primary)'; ?> !important;">
            <div class="card-body position-relative">
                <p class="label-md text-secondary mb-1 text-uppercase">Saldo do Mês</p>
                <h3 class="headline-lg m-0 data-mono" style="color: <?php echo $isSaldoNegativo ? 'var(--gfs-error)' : 'var(--gfs-primary)'; ?>;">
                    R$ <?php echo number_format($resumo['saldo'], 2, ',', '.'); ?>
                </h3>
                <?php if ($isSaldoNegativo): ?>
                    <span class="badge bg-danger position-absolute top-0 end-0 mt-3 me-3 rounded-pill" style="background-color: var(--gfs-error) !important;">Saldo Negativo</span>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<!-- Gráficos -->
<div class="row g-4 mb-4">
    <div class="col-lg-8">
        <div class="card shadow-sm border-0 h-100" style="border-radius: var(--gfs-border-radius-lg);">
            <div class="card-body">
                <h5 class="headline-sm mb-4">Fluxo de Caixa (Últimos 6 Meses)</h5>
                <div style="height: 300px;">
                    <canvas id="fluxoCaixaChart"></canvas>
                </div>
            </div>
        </div>
    </div>
    <div class="col-lg-4">
        <div class="card shadow-sm border-0 h-100" style="border-radius: var(--gfs-border-radius-lg);">
            <div class="card-body">
                <h5 class="headline-sm mb-4">Despesas por Categoria</h5>
                <?php if (empty($despesasPorCategoria)): ?>
                    <p class="text-secondary body-md mt-5 text-center">Nenhuma despesa registrada neste mês.</p>
                <?php else: ?>
                    <div style="height: 300px;">
                        <canvas id="despesasCategoriaChart"></canvas>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<!-- Modal Dummy para Fase 5 -->
<div class="modal fade" id="novoLancamentoModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content" style="border-radius: var(--gfs-border-radius-lg);">
      <div class="modal-header border-bottom-0">
        <h5 class="modal-title headline-md" style="color: var(--gfs-primary);">Novo Lançamento</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body text-secondary body-md">
        O cadastro de lançamentos será implementado na Fase 5.
      </div>
      <div class="modal-footer border-top-0">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Fechar</button>
      </div>
    </div>
  </div>
</div>

<script src="<?php echo Sanitizer::e($config['app_url']); ?>/assets/js/chart.umd.js"></script>
<script>
document.addEventListener("DOMContentLoaded", function() {
    const fluxoCaixaData = <?php echo json_encode($fluxoCaixa); ?>;
    const despesasCategoriaData = <?php echo json_encode($despesasPorCategoria); ?>;

    const brandPrimary = '#0f2d7b';
    const brandSuccess = '#10b981';
    const brandError = '#ba1a1a';
    const brandSecondary = '#97378d';
    const brandOutline = '#757682';

    // Gráfico de Fluxo de Caixa
    const ctxFluxo = document.getElementById('fluxoCaixaChart');
    if (ctxFluxo) {
        new Chart(ctxFluxo, {
            type: 'bar',
            data: {
                labels: fluxoCaixaData.map(item => item.label),
                datasets: [
                    {
                        label: 'Receitas',
                        data: fluxoCaixaData.map(item => item.receitas),
                        backgroundColor: brandSuccess,
                        borderRadius: 4
                    },
                    {
                        label: 'Despesas',
                        data: fluxoCaixaData.map(item => item.despesas),
                        backgroundColor: brandError,
                        borderRadius: 4
                    }
                ]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            callback: function(value) {
                                return 'R$ ' + value.toLocaleString('pt-BR');
                            }
                        }
                    }
                },
                plugins: {
                    legend: { position: 'top' },
                    tooltip: {
                        callbacks: {
                            label: function(context) {
                                let label = context.dataset.label || '';
                                if (label) label += ': ';
                                if (context.parsed.y !== null) {
                                    label += 'R$ ' + context.parsed.y.toLocaleString('pt-BR', {minimumFractionDigits: 2});
                                }
                                return label;
                            }
                        }
                    }
                }
            }
        });
    }

    // Gráfico de Despesas por Categoria
    const ctxCategoria = document.getElementById('despesasCategoriaChart');
    if (ctxCategoria && despesasCategoriaData.length > 0) {
        const bgColors = [brandPrimary, brandSecondary, brandError, brandSuccess, brandOutline, '#2c4593', '#fd8feb', '#f59e0b', '#7b1c74'];
        new Chart(ctxCategoria, {
            type: 'doughnut',
            data: {
                labels: despesasCategoriaData.map(item => item.nome || 'Sem Categoria'),
                datasets: [{
                    data: despesasCategoriaData.map(item => item.total),
                    backgroundColor: bgColors,
                    borderWidth: 0
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { position: 'bottom' },
                    tooltip: {
                        callbacks: {
                            label: function(context) {
                                let label = context.label || '';
                                if (label) label += ': ';
                                if (context.parsed !== null) {
                                    label += 'R$ ' + context.parsed.toLocaleString('pt-BR', {minimumFractionDigits: 2});
                                }
                                return label;
                            }
                        }
                    }
                }
            }
        });
    }
});
</script>

<?php require_once dirname(__DIR__) . '/templates/footer.php'; ?>
