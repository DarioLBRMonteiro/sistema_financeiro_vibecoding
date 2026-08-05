<?php
defined('APP_EXEC') or die('Acesso direto não permitido.');
$title = 'Redefinir Senha';
require dirname(__DIR__) . '/templates/header.php';
?>

<div class="row justify-content-center align-items-center flex-grow-1 h-100 mt-5">
    <div class="col-12 col-md-6 col-lg-4">
        <div class="card shadow-sm border-0" style="border-radius: var(--gfs-border-radius-lg);">
            <div class="card-body p-4 p-md-5">
                <div class="text-center mb-4">
                    <h2 class="fw-bold mb-1" style="color: var(--gfs-primary);">Redefinir Senha</h2>
                    <p class="text-secondary small">Crie uma nova senha para sua conta.</p>
                </div>

                <?php if (!empty($erro)): ?>
                    <div class="alert alert-danger" role="alert" style="border-radius: var(--gfs-border-radius);">
                        <?php echo Sanitizer::e($erro); ?>
                    </div>
                    <?php if (strpos($erro, 'inválido ou expirado') !== false): ?>
                        <div class="text-center mt-4">
                            <a href="<?php echo Sanitizer::e($config['app_url']); ?>/recuperar-senha" class="btn btn-primary fw-semibold" style="border-radius: var(--gfs-border-radius);">Solicitar nova recuperação</a>
                        </div>
                    <?php endif; ?>
                <?php endif; ?>

                <?php if (!empty($sucesso)): ?>
                    <div class="alert alert-success" role="alert" style="border-radius: var(--gfs-border-radius);">
                        <?php echo Sanitizer::e($sucesso); ?>
                    </div>
                    <div class="text-center mt-4">
                        <a href="<?php echo Sanitizer::e($config['app_url']); ?>/login" class="btn btn-primary fw-semibold" style="border-radius: var(--gfs-border-radius);">Ir para o Login</a>
                    </div>
                <?php elseif (empty($erro) || strpos($erro, 'inválido ou expirado') === false): ?>
                
                <form action="<?php echo Sanitizer::e($config['app_url']); ?>/redefinir-senha?token=<?php echo urlencode($token); ?>" method="POST">
                    <input type="hidden" name="csrf_token" value="<?php echo Sanitizer::e(CSRF::getToken()); ?>">
                    
                    <div class="mb-3">
                        <label for="senha" class="form-label fw-semibold small">Nova Senha</label>
                        <input type="password" class="form-control" id="senha" name="senha" required minlength="8"
                               style="border-radius: var(--gfs-border-radius); border-color: var(--gfs-outline-variant);">
                        <div class="form-text small">Mínimo de 8 caracteres, contendo letras e números.</div>
                    </div>
                    
                    <div class="mb-4">
                        <label for="confirmacao_senha" class="form-label fw-semibold small">Confirmar Nova Senha</label>
                        <input type="password" class="form-control" id="confirmacao_senha" name="confirmacao_senha" required minlength="8"
                               style="border-radius: var(--gfs-border-radius); border-color: var(--gfs-outline-variant);">
                    </div>
                    
                    <div class="d-grid mb-3">
                        <button type="submit" class="btn btn-primary fw-semibold" style="border-radius: var(--gfs-border-radius); background-color: var(--gfs-primary); border-color: var(--gfs-primary);">
                            Salvar Nova Senha
                        </button>
                    </div>
                </form>
                
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<?php require dirname(__DIR__) . '/templates/footer.php'; ?>
