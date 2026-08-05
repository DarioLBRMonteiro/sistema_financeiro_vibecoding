<?php
defined('APP_EXEC') or die('Acesso direto não permitido.');
$currentUri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$appUrlPath = parse_url($config['app_url'], PHP_URL_PATH);
if ($appUrlPath && strpos($currentUri, $appUrlPath) === 0) {
    $currentUri = substr($currentUri, strlen($appUrlPath));
}
if ($currentUri === '' || $currentUri === false) {
    $currentUri = '/';
}
?>
<div class="sidebar d-flex flex-column p-3 bg-white border-end h-100" style="width: 250px;">
    <a href="<?php echo Sanitizer::e($config['app_url']); ?>/dashboard" class="d-flex align-items-center mb-4 me-md-auto text-decoration-none" style="color: var(--gfs-primary);">
        <span class="fs-5 fw-bold headline-md d-none d-md-inline"><?php echo Sanitizer::e($config['app_name']); ?></span>
    </a>
    <ul class="nav nav-pills flex-column mb-auto">
        <li class="nav-item">
            <a href="<?php echo Sanitizer::e($config['app_url']); ?>/dashboard" class="nav-link <?php echo $currentUri === '/dashboard' ? 'active bg-light text-primary fw-semibold' : 'text-secondary'; ?>" style="border-radius: var(--gfs-border-radius);">
                Dashboard
            </a>
        </li>
        <li class="nav-item mt-1">
            <a href="<?php echo Sanitizer::e($config['app_url']); ?>/extrato" class="nav-link <?php echo $currentUri === '/extrato' ? 'active bg-light text-primary fw-semibold' : 'text-secondary'; ?>" style="border-radius: var(--gfs-border-radius);">
                Extrato
            </a>
        </li>
        <li class="nav-item mt-1">
            <a href="<?php echo Sanitizer::e($config['app_url']); ?>/categorias" class="nav-link <?php echo $currentUri === '/categorias' ? 'active bg-light text-primary fw-semibold' : 'text-secondary'; ?>" style="border-radius: var(--gfs-border-radius);">
                Categorias
            </a>
        </li>
    </ul>
</div>
