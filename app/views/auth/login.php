<?php
defined('APP_EXEC') or die('Acesso direto não permitido.');
$title = 'Login';
require dirname(__DIR__) . '/templates/header.php';
?>

<div class="row justify-content-center align-items-center flex-grow-1 h-100 mt-5">
    <div class="col-12 col-md-6 col-lg-4">
        <div class="card shadow-sm border-0" style="border-radius: var(--gfs-border-radius-lg);">
            <div class="card-body p-4 p-md-5">
                <div class="text-center mb-4">
                    <h2 class="fw-bold mb-1" style="color: var(--gfs-primary);">Bem-vindo</h2>
                    <p class="text-secondary small">Entre com suas credenciais para acessar o painel.</p>
                </div>

                <?php if (!empty($erro)): ?>
                    <div class="alert alert-danger" role="alert" style="border-radius: var(--gfs-border-radius);">
                        <?php echo Sanitizer::e($erro); ?>
                    </div>
                <?php endif; ?>

                <?php if (!empty($_GET['sucesso'])): ?>
                    <div class="alert alert-success" role="alert" style="border-radius: var(--gfs-border-radius);">
                        <?php echo Sanitizer::e($_GET['sucesso']); ?>
                    </div>
                <?php endif; ?>

                <form action="<?php echo Sanitizer::e($config['app_url']); ?>/login" method="POST">
                    <input type="hidden" name="csrf_token" value="<?php echo Sanitizer::e(CSRF::getToken()); ?>">
                    
                    <div class="mb-3">
                        <label for="email" class="form-label fw-semibold small">E-mail</label>
                        <input type="email" class="form-control" id="email" name="email" required 
                               style="border-radius: var(--gfs-border-radius); border-color: var(--gfs-outline-variant);"
                               value="<?php echo isset($_POST['email']) ? Sanitizer::e($_POST['email']) : ''; ?>">
                    </div>
                    
                    <div class="mb-4">
                        <div class="d-flex justify-content-between align-items-center">
                            <label for="senha" class="form-label fw-semibold small mb-0">Senha</label>
                            <a href="<?php echo Sanitizer::e($config['app_url']); ?>/recuperar-senha" class="small text-decoration-none" style="color: var(--gfs-primary);">Esqueceu sua senha?</a>
                        </div>
                        <input type="password" class="form-control mt-2" id="senha" name="senha" required 
                               style="border-radius: var(--gfs-border-radius); border-color: var(--gfs-outline-variant);">
                    </div>
                    
                    <div class="d-grid mb-3">
                        <button type="submit" class="btn btn-primary fw-semibold" style="border-radius: var(--gfs-border-radius); background-color: var(--gfs-primary); border-color: var(--gfs-primary);">
                            Entrar
                        </button>
                    </div>
                </form>
                
                <div class="text-center mt-4">
                    <p class="small text-secondary mb-0">Não possui conta? <a href="<?php echo Sanitizer::e($config['app_url']); ?>/cadastro" class="text-decoration-none fw-semibold" style="color: var(--gfs-primary);">Criar uma conta</a></p>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require dirname(__DIR__) . '/templates/footer.php'; ?>
