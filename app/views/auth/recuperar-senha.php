<?php
defined('APP_EXEC') or die('Acesso direto não permitido.');
$title = 'Recuperar Senha';
require dirname(__DIR__) . '/templates/header.php';
?>

<div class="row justify-content-center align-items-center flex-grow-1 h-100 mt-5">
    <div class="col-12 col-md-6 col-lg-4">
        <div class="card shadow-sm border-0" style="border-radius: var(--gfs-border-radius-lg);">
            <div class="card-body p-4 p-md-5">
                <div class="text-center mb-4">
                    <h2 class="fw-bold mb-1" style="color: var(--gfs-primary);">Recuperar Senha</h2>
                    <p class="text-secondary small">Informe seu e-mail para receber as instruções.</p>
                </div>

                <?php if (!empty($erro)): ?>
                    <div class="alert alert-danger" role="alert" style="border-radius: var(--gfs-border-radius);">
                        <?php echo Sanitizer::e($erro); ?>
                    </div>
                <?php endif; ?>

                <?php if (!empty($sucesso)): ?>
                    <div class="alert alert-success" role="alert" style="border-radius: var(--gfs-border-radius);">
                        <?php echo Sanitizer::e($sucesso); ?>
                    </div>
                    
                    <div class="text-center mt-4">
                        <a href="<?php echo Sanitizer::e($config['app_url']); ?>/login" class="btn btn-outline-secondary fw-semibold" style="border-radius: var(--gfs-border-radius);">Voltar ao Login</a>
                    </div>
                <?php else: ?>
                
                <form action="<?php echo Sanitizer::e($config['app_url']); ?>/recuperar-senha" method="POST">
                    <input type="hidden" name="csrf_token" value="<?php echo Sanitizer::e(CSRF::getToken()); ?>">
                    
                    <div class="mb-4">
                        <label for="email" class="form-label fw-semibold small">E-mail Cadastrado</label>
                        <input type="email" class="form-control" id="email" name="email" required 
                               style="border-radius: var(--gfs-border-radius); border-color: var(--gfs-outline-variant);"
                               value="<?php echo isset($_POST['email']) ? Sanitizer::e($_POST['email']) : ''; ?>">
                    </div>
                    
                    <div class="d-grid mb-3">
                        <button type="submit" class="btn btn-primary fw-semibold" style="border-radius: var(--gfs-border-radius); background-color: var(--gfs-primary); border-color: var(--gfs-primary);">
                            Enviar Link de Recuperação
                        </button>
                    </div>
                </form>
                
                <div class="text-center mt-4">
                    <p class="small text-secondary mb-0"><a href="<?php echo Sanitizer::e($config['app_url']); ?>/login" class="text-decoration-none fw-semibold" style="color: var(--gfs-primary);">Voltar ao Login</a></p>
                </div>
                
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<?php require dirname(__DIR__) . '/templates/footer.php'; ?>
