<?php
defined('APP_EXEC') or die('Acesso direto não permitido.');
$title = 'Cadastro';
require dirname(__DIR__) . '/templates/header.php';
?>

<div class="row justify-content-center align-items-center flex-grow-1 h-100 mt-5 mb-5">
    <div class="col-12 col-md-8 col-lg-5">
        <div class="card shadow-sm border-0" style="border-radius: var(--gfs-border-radius-lg);">
            <div class="card-body p-4 p-md-5">
                <div class="text-center mb-4">
                    <h2 class="fw-bold mb-1" style="color: var(--gfs-primary);">Criar Conta</h2>
                    <p class="text-secondary small">Cadastre-se para começar a controlar suas finanças.</p>
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
                <?php else: ?>
                
                <form action="<?php echo Sanitizer::e($config['app_url']); ?>/cadastro" method="POST">
                    <input type="hidden" name="csrf_token" value="<?php echo Sanitizer::e(CSRF::getToken()); ?>">
                    
                    <div class="mb-3">
                        <label for="nome" class="form-label fw-semibold small">Nome Completo</label>
                        <input type="text" class="form-control" id="nome" name="nome" required minlength="3"
                               style="border-radius: var(--gfs-border-radius); border-color: var(--gfs-outline-variant);"
                               value="<?php echo isset($_POST['nome']) ? Sanitizer::e($_POST['nome']) : ''; ?>">
                    </div>
                    
                    <div class="mb-3">
                        <label for="email" class="form-label fw-semibold small">E-mail</label>
                        <input type="email" class="form-control" id="email" name="email" required 
                               style="border-radius: var(--gfs-border-radius); border-color: var(--gfs-outline-variant);"
                               value="<?php echo isset($_POST['email']) ? Sanitizer::e($_POST['email']) : ''; ?>">
                    </div>
                    
                    <div class="mb-3">
                        <label for="senha" class="form-label fw-semibold small">Senha</label>
                        <input type="password" class="form-control" id="senha" name="senha" required minlength="8"
                               style="border-radius: var(--gfs-border-radius); border-color: var(--gfs-outline-variant);">
                        <div class="form-text small">Mínimo de 8 caracteres, contendo letras e números.</div>
                    </div>
                    
                    <div class="mb-4">
                        <label for="confirmacao_senha" class="form-label fw-semibold small">Confirmar Senha</label>
                        <input type="password" class="form-control" id="confirmacao_senha" name="confirmacao_senha" required minlength="8"
                               style="border-radius: var(--gfs-border-radius); border-color: var(--gfs-outline-variant);">
                    </div>
                    
                    <div class="d-grid mb-3">
                        <button type="submit" class="btn btn-primary fw-semibold" style="border-radius: var(--gfs-border-radius); background-color: var(--gfs-primary); border-color: var(--gfs-primary);">
                            Cadastrar
                        </button>
                    </div>
                </form>
                
                <?php endif; ?>
                
                <div class="text-center mt-4">
                    <p class="small text-secondary mb-0">Já possui conta? <a href="<?php echo Sanitizer::e($config['app_url']); ?>/login" class="text-decoration-none fw-semibold" style="color: var(--gfs-primary);">Faça login</a></p>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require dirname(__DIR__) . '/templates/footer.php'; ?>
