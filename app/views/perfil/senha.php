<?php
defined('APP_EXEC') or die('Acesso direto não permitido.');
require_once dirname(__DIR__) . '/templates/header.php';
?>

<div class="row justify-content-center">
    <div class="col-md-6 col-lg-5">
        <div class="card shadow-sm border-0 mt-4" style="border-radius: var(--gfs-border-radius-lg);">
            <div class="card-body p-4 p-md-5">
                <h1 class="headline-md mb-4 text-center" style="color: var(--gfs-primary);">Alterar Senha</h1>
                
                <?php if (!empty($erro)): ?>
                    <div class="alert alert-danger" role="alert" style="border-radius: var(--gfs-border-radius);">
                        <?php echo Sanitizer::e($erro); ?>
                    </div>
                <?php endif; ?>

                <?php if (!empty($sucesso)): ?>
                    <div class="alert alert-success" role="alert" style="border-radius: var(--gfs-border-radius);">
                        <?php echo Sanitizer::e($sucesso); ?>
                    </div>
                <?php endif; ?>

                <form action="<?php echo Sanitizer::e($config['app_url']); ?>/perfil/senha" method="POST">
                    <input type="hidden" name="csrf_token" value="<?php echo Sanitizer::e(CSRF::getToken()); ?>">
                    
                    <div class="mb-3">
                        <label for="senha_atual" class="form-label label-md">Senha Atual</label>
                        <input type="password" class="form-control" id="senha_atual" name="senha_atual" required>
                    </div>

                    <div class="mb-3">
                        <label for="nova_senha" class="form-label label-md">Nova Senha</label>
                        <input type="password" class="form-control" id="nova_senha" name="nova_senha" minlength="8" required>
                        <div class="form-text body-sm">No mínimo 8 caracteres, contendo letras e números.</div>
                    </div>

                    <div class="mb-4">
                        <label for="confirmar_nova_senha" class="form-label label-md">Confirmar Nova Senha</label>
                        <input type="password" class="form-control" id="confirmar_nova_senha" name="confirmar_nova_senha" minlength="8" required>
                    </div>

                    <div class="d-grid gap-2">
                        <button type="submit" class="btn text-white fw-semibold" style="background-color: var(--gfs-primary); border-radius: var(--gfs-border-radius);">
                            Salvar Nova Senha
                        </button>
                        <a href="<?php echo Sanitizer::e($config['app_url']); ?>/dashboard" class="btn btn-outline-secondary fw-semibold" style="border-radius: var(--gfs-border-radius);">
                            Cancelar
                        </a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<?php require_once dirname(__DIR__) . '/templates/footer.php'; ?>
