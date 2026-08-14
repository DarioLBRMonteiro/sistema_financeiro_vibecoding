<?php
defined('APP_EXEC') or die('Acesso direto não permitido.');
require_once dirname(__DIR__) . '/templates/header.php';

// Variáveis: $mes, $ano, $busca, $categoriaId, $lancamentos, $categorias

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
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h1 class="headline-lg m-0" style="color: var(--gfs-primary);">Extrato</h1>
    <button type="button" class="btn fw-semibold text-white" style="background-color: var(--gfs-primary); border-radius: var(--gfs-border-radius);" data-bs-toggle="modal" data-bs-target="#novoLancamentoModal">
        + Novo Lançamento
    </button>
</div>

<!-- Filtros e Navegação -->
<div class="card shadow-sm border-0 mb-4" style="border-radius: var(--gfs-border-radius-lg);">
    <div class="card-body">
        <form method="GET" action="<?php echo Sanitizer::e($config['app_url']); ?>/extrato" class="row g-3 align-items-center">
            
            <!-- Navegação Temporal escondida nos inputs para manter ao submeter -->
            <input type="hidden" name="mes" value="<?php echo $mes; ?>">
            <input type="hidden" name="ano" value="<?php echo $ano; ?>">
            
            <div class="col-md-4 d-flex justify-content-center align-items-center">
                <a href="?mes=<?php echo $mesAnterior; ?>&ano=<?php echo $anoAnterior; ?>&busca=<?php echo urlencode($busca); ?>&categoria_id=<?php echo urlencode($categoriaId); ?>" class="btn btn-sm btn-light text-secondary me-2">&lt; Anterior</a>
                <strong class="mx-2" style="min-width: 130px; text-align: center;"><?php echo $nomeMesAtual; ?> / <?php echo $ano; ?></strong>
                <a href="?mes=<?php echo $mesProximo; ?>&ano=<?php echo $anoProximo; ?>&busca=<?php echo urlencode($busca); ?>&categoria_id=<?php echo urlencode($categoriaId); ?>" class="btn btn-sm btn-light text-secondary ms-2">Próximo &gt;</a>
            </div>

            <div class="col-md-4">
                <input type="text" name="busca" class="form-control" placeholder="Buscar por descrição..." value="<?php echo Sanitizer::e($busca); ?>" style="border-radius: var(--gfs-border-radius);">
            </div>
            
            <div class="col-md-3">
                <select name="categoria_id" class="form-select" style="border-radius: var(--gfs-border-radius);">
                    <option value="">Todas as Categorias</option>
                    <?php foreach ($categorias as $cat): ?>
                        <option value="<?php echo Sanitizer::e($cat['id']); ?>" <?php echo (string)$categoriaId === (string)$cat['id'] ? 'selected' : ''; ?>>
                            <?php echo Sanitizer::e($cat['nome']); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            
            <div class="col-md-1 d-grid">
                <button type="submit" class="btn btn-outline-secondary" style="border-radius: var(--gfs-border-radius);">Filtrar</button>
            </div>
        </form>
    </div>
</div>

<!-- Tabela de Lançamentos -->
<div class="card shadow-sm border-0" style="border-radius: var(--gfs-border-radius-lg);">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle m-0">
                <thead class="table-light">
                    <tr>
                        <th class="ps-4 label-md text-secondary border-bottom-0">Data</th>
                        <th class="label-md text-secondary border-bottom-0">Descrição</th>
                        <th class="label-md text-secondary border-bottom-0">Categoria</th>
                        <th class="label-md text-secondary border-bottom-0">Forma de Pgto</th>
                        <th class="label-md text-secondary border-bottom-0">Status</th>
                        <th class="text-end label-md text-secondary border-bottom-0">Valor</th>
                        <th class="text-end pe-4 label-md text-secondary border-bottom-0">Ações</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($lancamentos)): ?>
                        <tr>
                            <td colspan="7" class="text-center py-5 text-secondary">
                                Nenhum lançamento encontrado para o período ou filtros selecionados.<br>
                                <button type="button" class="btn btn-link p-0 m-0 align-baseline" data-bs-toggle="modal" data-bs-target="#novoLancamentoModal">Cadastre um novo lançamento</button>
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($lancamentos as $l): ?>
                            <tr>
                                <td class="ps-4 data-mono"><?php echo date('d/m/Y', strtotime($l['data_movimentacao'])); ?></td>
                                <td><?php echo Sanitizer::e($l['descricao']); ?></td>
                                <td><?php echo Sanitizer::e($l['categoria_nome'] ?? 'Sem categoria'); ?></td>
                                <td>
                                    <?php 
                                        $formas = [
                                            'DINHEIRO' => 'Dinheiro',
                                            'PIX' => 'Pix',
                                            'DEBITO' => 'Cartão de Débito',
                                            'CREDITO' => 'Cartão de Crédito'
                                        ];
                                        echo Sanitizer::e($formas[$l['forma_pagamento']] ?? $l['forma_pagamento']); 
                                    ?>
                                </td>
                                <td>
                                    <?php if ($l['status'] === 'PAGO'): ?>
                                        <span class="badge rounded-pill fw-normal px-3" style="background-color: #d1fae5; color: #065f46;">Pago</span>
                                    <?php else: ?>
                                        <span class="badge rounded-pill fw-normal px-3" style="background-color: #fef3c7; color: #92400e;">Pendente</span>
                                    <?php endif; ?>
                                </td>
                                <td class="text-end data-mono fw-semibold <?php echo $l['tipo'] === 'RECEITA' ? 'text-success' : 'text-danger'; ?>">
                                    <?php echo $l['tipo'] === 'RECEITA' ? '+' : '-'; ?> R$ <?php echo number_format($l['valor'], 2, ',', '.'); ?>
                                </td>
                                <td class="text-end pe-4">
                                    <button class="btn btn-sm btn-outline-secondary me-1" style="border-radius: var(--gfs-border-radius);" data-bs-toggle="modal" data-bs-target="#editarLancamentoModal">Editar</button>
                                    <button class="btn btn-sm btn-outline-danger" style="border-radius: var(--gfs-border-radius);" data-bs-toggle="modal" data-bs-target="#excluirLancamentoModal">Excluir</button>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Modais Dummy para Fase 5 -->
<div class="modal fade" id="novoLancamentoModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content" style="border-radius: var(--gfs-border-radius-lg);">
      <div class="modal-header border-bottom-0">
        <h5 class="modal-title headline-md" style="color: var(--gfs-primary);">Novo Lançamento</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body text-secondary body-md">O cadastro de lançamentos será implementado na Fase 5.</div>
      <div class="modal-footer border-top-0">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Fechar</button>
      </div>
    </div>
  </div>
</div>

<div class="modal fade" id="editarLancamentoModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content" style="border-radius: var(--gfs-border-radius-lg);">
      <div class="modal-header border-bottom-0">
        <h5 class="modal-title headline-md" style="color: var(--gfs-primary);">Editar Lançamento</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body text-secondary body-md">A edição de lançamentos será implementada na Fase 5.</div>
      <div class="modal-footer border-top-0">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Fechar</button>
      </div>
    </div>
  </div>
</div>

<div class="modal fade" id="excluirLancamentoModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content" style="border-radius: var(--gfs-border-radius-lg);">
      <div class="modal-header border-bottom-0">
        <h5 class="modal-title headline-md text-danger">Excluir Lançamento</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body text-secondary body-md">A exclusão lógica será implementada na Fase 5.</div>
      <div class="modal-footer border-top-0">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Fechar</button>
      </div>
    </div>
  </div>
</div>

<?php require_once dirname(__DIR__) . '/templates/footer.php'; ?>
