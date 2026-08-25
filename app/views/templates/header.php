<?php
defined('APP_EXEC') or die('Acesso direto não permitido.');
$config = require dirname(__DIR__, 3) . '/config/config.php';
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo isset($title) ? Sanitizer::e($title) . ' - ' : ''; ?><?php echo Sanitizer::e($config['app_name']); ?></title>
    
    <link rel="stylesheet" href="<?php echo Sanitizer::e($config['app_url']); ?>/assets/css/bootstrap.min.css">
    <link rel="stylesheet" href="<?php echo Sanitizer::e($config['app_url']); ?>/assets/css/admin-logic.css">
</head>
<body class="d-flex flex-column min-vh-100">
    <?php if (Auth::check()): ?>
    <nav class="navbar navbar-expand-lg bg-white border-bottom shadow-sm py-2">
        <div class="container-fluid px-4">
            <button class="navbar-toggler me-2 d-md-none border-0 px-1" type="button" data-bs-toggle="offcanvas" data-bs-target="#sidebarOffcanvas" aria-controls="sidebarOffcanvas">
                <span class="navbar-toggler-icon"></span>
            </button>
            <a class="navbar-brand d-md-none fw-bold" style="color: var(--gfs-primary);" href="<?php echo Sanitizer::e($config['app_url']); ?>/dashboard">
                <?php echo Sanitizer::e($config['app_name']); ?>
            </a>
            <div class="ms-auto d-flex align-items-center">
                <span class="me-3 fw-medium text-secondary d-none d-sm-inline">
                    Olá, <?php echo Sanitizer::e($_SESSION['user_nome'] ?? 'Usuário'); ?>
                </span>
                <div class="dropdown">
                    <button class="btn btn-sm btn-outline-secondary dropdown-toggle" type="button" id="userMenu" data-bs-toggle="dropdown" aria-expanded="false" style="border-radius: var(--gfs-border-radius); border-color: var(--gfs-outline-variant);">
                        Opções
                    </button>
                    <ul class="dropdown-menu dropdown-menu-end shadow-sm border-0" aria-labelledby="userMenu" style="border-radius: var(--gfs-border-radius-lg);">
                        <li><a class="dropdown-item" href="<?php echo Sanitizer::e($config['app_url']); ?>/perfil/senha">Alterar Senha</a></li>
                        <li><hr class="dropdown-divider"></li>
                        <li><a class="dropdown-item text-danger fw-semibold" href="<?php echo Sanitizer::e($config['app_url']); ?>/logout">Sair</a></li>
                    </ul>
                </div>
            </div>
        </div>
    </nav>
    <?php endif; ?>

    <div class="d-flex flex-grow-1 overflow-hidden">
        <?php if (Auth::check()): ?>
            <div class="d-none d-md-block">
                <?php require_once __DIR__ . '/sidebar.php'; ?>
            </div>
            <div class="offcanvas offcanvas-start d-md-none" tabindex="-1" id="sidebarOffcanvas" aria-labelledby="sidebarOffcanvasLabel">
                <div class="offcanvas-header border-bottom">
                    <h5 class="offcanvas-title fw-bold" id="sidebarOffcanvasLabel" style="color: var(--gfs-primary);">
                        <?php echo Sanitizer::e($config['app_name']); ?>
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="offcanvas" aria-label="Close"></button>
                </div>
                <div class="offcanvas-body p-0">
                    <?php require __DIR__ . '/sidebar.php'; ?>
                </div>
            </div>
        <?php endif; ?>
        <main class="flex-grow-1 p-4 bg-surface overflow-auto">
