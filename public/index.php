<?php
// Define constante de execução para controle de acesso direto
define('APP_EXEC', true);

// Inicializa a sessão com parâmetros de segurança reforçados conforme o FSD
session_start([
    'cookie_httponly' => true,
    'cookie_samesite' => 'Lax',
    'use_strict_mode' => true,
]);

// Carrega as configurações globais
$config = require_once dirname(__DIR__) . '/config/config.php';

// Carrega os helpers basais de infraestrutura
require_once dirname(__DIR__) . '/app/helpers/Logger.php';
require_once dirname(__DIR__) . '/app/helpers/Sanitizer.php';
require_once dirname(__DIR__) . '/app/helpers/CSRF.php';
require_once dirname(__DIR__) . '/app/helpers/Auth.php';

// Carrega a classe de banco de dados
require_once dirname(__DIR__) . '/config/database.php';

// Obtém o caminho da requisição
$requestUri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);

// Remove a pasta raiz do projeto da URL caso estejamos em subpasta local
$appUrlPath = parse_url($config['app_url'], PHP_URL_PATH);
if ($appUrlPath && strpos($requestUri, $appUrlPath) === 0) {
    $requestUri = substr($requestUri, strlen($appUrlPath));
}

// Normaliza o path final
if ($requestUri === '' || $requestUri === false) {
    $requestUri = '/';
}
if ($requestUri !== '/' && $requestUri[0] !== '/') {
    $requestUri = '/' . $requestUri;
}

// Roteador inicial simples para homologação da infraestrutura
if ($requestUri === '/' || $requestUri === '/login') {
    header('Content-Type: text/html; charset=UTF-8');
    ?>
    <!DOCTYPE html>
    <html lang="pt-BR">
    <head>
        <meta charset="UTF-8">
        <title><?php echo Sanitizer::e($config['app_name']); ?> - Infraestrutura</title>
        <link rel="icon" href="<?php echo Sanitizer::e($config['app_url']); ?>/assets/images/cursoemvideo-logo.ico" type="image/x-icon">
        <style>
            body {
                font-family: Arial, sans-serif;
                background-color: #f7f9fb;
                color: #191c1e;
                margin: 40px;
                line-height: 1.6;
            }
            .container {
                max-width: 600px;
                background: #ffffff;
                padding: 30px;
                border-radius: 4px;
                border: 1px solid #e0e3e5;
                box-shadow: 0 4px 12px rgba(0, 0, 0, 0.05);
            }
            h1 {
                color: #0f2d7b;
                margin-top: 0;
            }
            .badge {
                display: inline-block;
                background-color: #10b981;
                color: #ffffff;
                padding: 4px 8px;
                border-radius: 100px;
                font-size: 12px;
                font-weight: bold;
            }
        </style>
    </head>
    <body>
        <div class="container">
            <h1><?php echo Sanitizer::e($config['app_name']); ?></h1>
            <p><strong>Ambiente:</strong> <span class="badge"><?php echo Sanitizer::e($config['env']); ?></span></p>
            <hr>
            <p>A infraestrutura de base e estrutura física de pastas foram criadas e carregadas com sucesso!</p>
            <p>O Front Controller está ativo e interceptando as requisições de forma segura.</p>
        </div>
    </body>
    </html>
    <?php
} else {
    http_response_code(404);
    echo "<h1>404 Not Found</h1>";
    echo "<p>Caminho de recurso [" . Sanitizer::e($requestUri) . "] não encontrado.</p>";
}
