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
    <button type="button" class="btn fw-semibold text-white btn-novo" style="background-color: var(--gfs-primary); border-radius: var(--gfs-border-radius);">
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
                <a href="?mes=<?php echo $mesAnterior; ?>&ano=<?php echo $anoAnterior; ?>&busca=<?php echo Sanitizer::e(urlencode($busca)); ?>&categoria_id=<?php echo Sanitizer::e(urlencode($categoriaId)); ?>" class="btn btn-sm btn-light text-secondary me-2">&lt; Anterior</a>
                <strong class="mx-2" style="min-width: 130px; text-align: center;"><?php echo Sanitizer::e($nomeMesAtual); ?> / <?php echo (int)$ano; ?></strong>
                <a href="?mes=<?php echo $mesProximo; ?>&ano=<?php echo $anoProximo; ?>&busca=<?php echo Sanitizer::e(urlencode($busca)); ?>&categoria_id=<?php echo Sanitizer::e(urlencode($categoriaId)); ?>" class="btn btn-sm btn-light text-secondary ms-2">Próximo &gt;</a>
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
                                <button type="button" class="btn btn-link p-0 m-0 align-baseline btn-novo">Cadastre um novo lançamento</button>
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
                                    <button class="btn btn-sm btn-outline-secondary me-1 btn-editar" style="border-radius: var(--gfs-border-radius);" 
                                        data-id="<?php echo $l['id']; ?>"
                                        data-descricao="<?php echo Sanitizer::e($l['descricao']); ?>"
                                        data-valor="<?php echo Sanitizer::e($l['valor']); ?>"
                                        data-data="<?php echo Sanitizer::e($l['data_movimentacao']); ?>"
                                        data-tipo="<?php echo Sanitizer::e($l['tipo']); ?>"
                                        data-categoria="<?php echo Sanitizer::e($l['categoria_id']); ?>"
                                        data-status="<?php echo Sanitizer::e($l['status']); ?>"
                                        data-forma="<?php echo Sanitizer::e($l['forma_pagamento']); ?>">Editar</button>
                                    <button class="btn btn-sm btn-outline-danger btn-excluir" style="border-radius: var(--gfs-border-radius);" 
                                        data-id="<?php echo $l['id']; ?>"
                                        data-descricao="<?php echo Sanitizer::e($l['descricao']); ?>"
                                        data-valor="<?php echo number_format($l['valor'], 2, ',', '.'); ?>">Excluir</button>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Modal Formulário Lançamento (Novo/Editar) -->
<div class="modal fade" id="lancamentoModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content" style="border-radius: var(--gfs-border-radius-lg);">
      <form action="<?php echo Sanitizer::e($config['app_url']); ?>/lancamento/salvar" method="POST">
        <input type="hidden" name="csrf_token" value="<?php echo CSRF::getToken(); ?>">
        <input type="hidden" name="id" id="lanc_id" value="">
        
        <div class="modal-header border-bottom-0 pb-0">
          <h5 class="modal-title headline-md" id="lancamentoModalTitle" style="color: var(--gfs-primary);">Novo Lançamento</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
        </div>
        <div class="modal-body pb-0">
            <div class="mb-3 d-flex gap-3">
                <div class="form-check">
                    <input class="form-check-input" type="radio" name="tipo" id="tipoDespesa" value="DESPESA" checked required>
                    <label class="form-check-label text-danger fw-semibold" for="tipoDespesa">Despesa</label>
                </div>
                <div class="form-check">
                    <input class="form-check-input" type="radio" name="tipo" id="tipoReceita" value="RECEITA" required>
                    <label class="form-check-label text-success fw-semibold" for="tipoReceita">Receita</label>
                </div>
            </div>

            <div class="mb-3">
                <label class="form-label label-md">Descrição</label>
                <input type="text" name="descricao" id="lanc_descricao" class="form-control" maxlength="255" required style="border-radius: var(--gfs-border-radius);">
            </div>

            <div class="row">
                <div class="col-md-6 mb-3">
                    <label class="form-label label-md">Valor (R$)</label>
                    <input type="number" step="0.01" min="0.01" name="valor" id="lanc_valor" class="form-control" required style="border-radius: var(--gfs-border-radius);">
                </div>
                <div class="col-md-6 mb-3">
                    <label class="form-label label-md">Data</label>
                    <input type="date" name="data_movimentacao" id="lanc_data" class="form-control" required style="border-radius: var(--gfs-border-radius);">
                </div>
            </div>

            <div class="mb-3">
                <label class="form-label label-md">Categoria</label>
                <select name="categoria_id" id="lanc_categoria" class="form-select" required style="border-radius: var(--gfs-border-radius);">
                    <option value="">Selecione...</option>
                    <?php foreach ($categorias as $cat): ?>
                        <option value="<?php echo Sanitizer::e($cat['id']); ?>"><?php echo Sanitizer::e($cat['nome']); ?></option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="row">
                <div class="col-md-6 mb-3">
                    <label class="form-label label-md">Forma de Pagamento</label>
                    <select name="forma_pagamento" id="lanc_forma" class="form-select" required style="border-radius: var(--gfs-border-radius);">
                        <option value="DINHEIRO">Dinheiro</option>
                        <option value="PIX">Pix</option>
                        <option value="DEBITO">Cartão de Débito</option>
                        <option value="CREDITO">Cartão de Crédito</option>
                    </select>
                </div>
                <div class="col-md-6 mb-3">
                    <label class="form-label label-md">Status</label>
                    <select name="status" id="lanc_status" class="form-select" required style="border-radius: var(--gfs-border-radius);">
                        <option value="PAGO">Pago</option>
                        <option value="PENDENTE">Pendente</option>
                    </select>
                </div>
            </div>

        </div>
        <div class="modal-footer border-top-0 pt-0">
          <button type="button" class="btn btn-light" data-bs-dismiss="modal" style="border-radius: var(--gfs-border-radius);">Cancelar</button>
          <button type="submit" class="btn text-white px-4" style="background-color: var(--gfs-primary); border-radius: var(--gfs-border-radius);">Salvar</button>
        </div>
      </form>
    </div>
  </div>
</div>

<!-- Modal Confirmação de Exclusão -->
<div class="modal fade" id="excluirLancamentoModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content" style="border-radius: var(--gfs-border-radius-lg);">
      <form action="<?php echo Sanitizer::e($config['app_url']); ?>/lancamento/excluir" method="POST">
        <input type="hidden" name="csrf_token" value="<?php echo CSRF::getToken(); ?>">
        <input type="hidden" name="id" id="excluir_id" value="">
        <div class="modal-header border-bottom-0">
          <h5 class="modal-title headline-md text-danger">Confirmar Exclusão</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
        </div>
        <div class="modal-body body-md text-secondary">
          Tem certeza de que deseja excluir o lançamento '<strong id="excluir_descricao"></strong>' no valor de R$ <strong id="excluir_valor"></strong>? 
          <br><br>Esta ação moverá o registro para o histórico excluído.
        </div>
        <div class="modal-footer border-top-0">
          <button type="button" class="btn btn-light" data-bs-dismiss="modal" style="border-radius: var(--gfs-border-radius);">Cancelar</button>
          <button type="submit" class="btn btn-danger" style="border-radius: var(--gfs-border-radius);">Confirmar Exclusão</button>
        </div>
      </form>
    </div>
  </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', () => {
    // Referências dos modais do Bootstrap
    let modalLancamento = null;
    let modalExcluir = null;
    
    if (typeof bootstrap !== 'undefined') {
        modalLancamento = new bootstrap.Modal(document.getElementById('lancamentoModal'));
        modalExcluir = new bootstrap.Modal(document.getElementById('excluirLancamentoModal'));
    } else {
        console.error('Bootstrap JS não está carregado.');
    }

    // Configura modal de lançamento (Novo vs Editar)
    const btnsNovo = document.querySelectorAll('.btn-novo');
    btnsNovo.forEach(btn => {
        btn.addEventListener('click', (e) => {
            e.preventDefault();
            try {
                document.getElementById('lancamentoModalTitle').innerText = 'Novo Lançamento';
                document.getElementById('lanc_id').value = '';
                document.getElementById('lanc_descricao').value = '';
                document.getElementById('lanc_valor').value = '';
                document.getElementById('lanc_data').value = '<?php echo date('Y-m-d'); ?>';
                document.getElementById('lanc_categoria').value = '';
                document.getElementById('tipoDespesa').checked = true;
                document.getElementById('lanc_status').value = 'PAGO';
                document.getElementById('lanc_forma').value = 'DINHEIRO';
                
                if(modalLancamento) modalLancamento.show();
            } catch(error) {
                console.error('Erro ao abrir Novo Lançamento:', error);
                alert('Erro na interface: ' + error.message);
            }
        });
    });

    const btnsEditar = document.querySelectorAll('.btn-editar');
    btnsEditar.forEach(btn => {
        btn.addEventListener('click', (e) => {
            e.preventDefault();
            try {
                document.getElementById('lancamentoModalTitle').innerText = 'Editar Lançamento';
                document.getElementById('lanc_id').value = btn.getAttribute('data-id');
                document.getElementById('lanc_descricao').value = btn.getAttribute('data-descricao');
                document.getElementById('lanc_valor').value = btn.getAttribute('data-valor');
                document.getElementById('lanc_data').value = btn.getAttribute('data-data');
                document.getElementById('lanc_categoria').value = btn.getAttribute('data-categoria');
                
                const tipo = btn.getAttribute('data-tipo');
                if(tipo === 'RECEITA') {
                    document.getElementById('tipoReceita').checked = true;
                } else {
                    document.getElementById('tipoDespesa').checked = true;
                }
                
                document.getElementById('lanc_status').value = btn.getAttribute('data-status');
                document.getElementById('lanc_forma').value = btn.getAttribute('data-forma');
                
                if(modalLancamento) modalLancamento.show();
            } catch (error) {
                console.error('Erro ao abrir Editar:', error);
                alert('Erro na interface: ' + error.message);
            }
        });
    });

    // Configura modal de exclusão
    const btnsExcluir = document.querySelectorAll('.btn-excluir');
    btnsExcluir.forEach(btn => {
        btn.addEventListener('click', (e) => {
            e.preventDefault();
            try {
                document.getElementById('excluir_id').value = btn.getAttribute('data-id');
                document.getElementById('excluir_descricao').innerText = btn.getAttribute('data-descricao');
                document.getElementById('excluir_valor').innerText = btn.getAttribute('data-valor');
                
                if(modalExcluir) modalExcluir.show();
            } catch(error) {
                console.error('Erro ao abrir Excluir:', error);
                alert('Erro na interface: ' + error.message);
            }
        });
    });
});
</script>

<?php require_once dirname(__DIR__) . '/templates/footer.php'; ?>
