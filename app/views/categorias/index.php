<?php defined('APP_EXEC') or die('Acesso direto não permitido.'); ?>
<?php require_once dirname(__DIR__) . '/templates/header.php'; ?>
<?php require_once dirname(__DIR__) . '/templates/sidebar.php'; ?>

<main class="col-md-9 ms-sm-auto col-lg-10 px-md-4 main-content pt-4 pb-5">
    <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center mb-4">
        <h1 class="h2 page-title">Categorias</h1>
    </div>

    <!-- Alertas -->
    <?php if (isset($_SESSION['success'])): ?>
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <?php echo Sanitizer::e($_SESSION['success']); unset($_SESSION['success']); ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    <?php endif; ?>

    <?php if (isset($_SESSION['error'])): ?>
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <?php echo Sanitizer::e($_SESSION['error']); unset($_SESSION['error']); ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    <?php endif; ?>

    <div class="row">
        <!-- Formulário para Nova Categoria Personalizada -->
        <div class="col-md-4 mb-4">
            <div class="card h-100">
                <div class="card-header bg-white">
                    <h5 class="card-title mb-0">Nova Categoria Personalizada</h5>
                </div>
                <div class="card-body">
                    <form action="<?php echo Sanitizer::e($config['app_url']); ?>/categoria/salvar" method="POST">
                        <input type="hidden" name="csrf_token" value="<?php echo Sanitizer::e(CSRF::getToken()); ?>">
                        
                        <div class="mb-3">
                            <label for="nome" class="form-label form-label-sm">Nome da Categoria</label>
                            <input type="text" class="form-control" id="nome" name="nome" maxlength="50" required>
                        </div>
                        
                        <div class="mb-4">
                            <label class="form-label form-label-sm">Tipo</label>
                            <div>
                                <div class="form-check form-check-inline">
                                    <input class="form-check-input" type="radio" name="tipo" id="tipo_receita" value="RECEITA" required>
                                    <label class="form-check-label text-success fw-bold" for="tipo_receita">Receita</label>
                                </div>
                                <div class="form-check form-check-inline">
                                    <input class="form-check-input" type="radio" name="tipo" id="tipo_despesa" value="DESPESA" required>
                                    <label class="form-check-label text-danger fw-bold" for="tipo_despesa">Despesa</label>
                                </div>
                            </div>
                        </div>
                        
                        <button type="submit" class="btn btn-primary w-100">Adicionar Categoria</button>
                    </form>
                </div>
            </div>
        </div>

        <!-- Listagem de Categorias -->
        <div class="col-md-8 mb-4">
            <div class="card card-table">
                <div class="card-header bg-white">
                    <h5 class="card-title mb-0">Suas Categorias</h5>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>Nome</th>
                                    <th>Tipo</th>
                                    <th>Origem</th>
                                    <th class="text-end">Ações</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($categoriasUsuario as $cat): ?>
                                    <tr>
                                        <td><?php echo Sanitizer::e($cat['nome']); ?></td>
                                        <td>
                                            <?php if ($cat['tipo'] === 'RECEITA'): ?>
                                                <span class="badge rounded-pill bg-success-subtle text-success border border-success-subtle px-3 py-2">Receita</span>
                                            <?php else: ?>
                                                <span class="badge rounded-pill bg-danger-subtle text-danger border border-danger-subtle px-3 py-2">Despesa</span>
                                            <?php endif; ?>
                                        </td>
                                        <td><span class="badge rounded-pill bg-secondary-subtle text-secondary px-3 py-2">Personalizada</span></td>
                                        <td class="text-end">
                                            <button type="button" class="btn btn-sm btn-outline-danger btn-excluir" 
                                                data-id="<?php echo Sanitizer::e($cat['id']); ?>" 
                                                data-nome="<?php echo Sanitizer::e($cat['nome']); ?>"
                                                data-bs-toggle="modal" data-bs-target="#modalExcluirCategoria">
                                                Excluir
                                            </button>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                                
                                <?php foreach ($categoriasSistema as $cat): ?>
                                    <tr>
                                        <td><?php echo Sanitizer::e($cat['nome']); ?></td>
                                        <td>
                                            <?php if ($cat['tipo'] === 'RECEITA'): ?>
                                                <span class="badge rounded-pill bg-success-subtle text-success border border-success-subtle px-3 py-2">Receita</span>
                                            <?php else: ?>
                                                <span class="badge rounded-pill bg-danger-subtle text-danger border border-danger-subtle px-3 py-2">Despesa</span>
                                            <?php endif; ?>
                                        </td>
                                        <td><span class="badge rounded-pill bg-light text-dark border px-3 py-2">Padrão do Sistema</span></td>
                                        <td class="text-end">
                                            <button type="button" class="btn btn-sm btn-outline-secondary" disabled title="Categorias do sistema não podem ser excluídas">
                                                Excluir
                                            </button>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>

                                <?php if (empty($categoriasUsuario) && empty($categoriasSistema)): ?>
                                    <tr>
                                        <td colspan="4" class="text-center py-4 text-muted">Nenhuma categoria encontrada.</td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</main>

<!-- Modal Confirmação de Exclusão -->
<div class="modal fade" id="modalExcluirCategoria" tabindex="-1" aria-labelledby="modalExcluirCategoriaLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title text-danger" id="modalExcluirCategoriaLabel">Confirmar Exclusão</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <p>Tem certeza de que deseja excluir a categoria '<strong id="excluirCategoriaNome"></strong>'?</p>
                <p class="text-muted small">Os lançamentos antigos vinculados a esta categoria continuarão visíveis no seu extrato histórico, mas ela não estará mais disponível para novos lançamentos.</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancelar</button>
                <form action="<?php echo Sanitizer::e($config['app_url']); ?>/categoria/excluir" method="POST" class="m-0 p-0">
                    <input type="hidden" name="csrf_token" value="<?php echo Sanitizer::e(CSRF::getToken()); ?>">
                    <input type="hidden" name="id" id="excluirCategoriaId" value="">
                    <button type="submit" class="btn btn-danger">Confirmar Exclusão</button>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const botoesExcluir = document.querySelectorAll('.btn-excluir');
    botoesExcluir.forEach(botao => {
        botao.addEventListener('click', function() {
            const id = this.getAttribute('data-id');
            const nome = this.getAttribute('data-nome');
            
            document.getElementById('excluirCategoriaId').value = id;
            document.getElementById('excluirCategoriaNome').textContent = nome;
        });
    });
});
</script>

<?php require_once dirname(__DIR__) . '/templates/footer.php'; ?>
