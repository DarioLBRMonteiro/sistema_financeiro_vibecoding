<?php
// Define constante de execução para controle de acesso direto
define('APP_EXEC', true);

// Inicializa a sessão com parâmetros de segurança reforçados conforme o FSD
session_start([
    'cookie_httponly' => true,
    'cookie_samesite' => 'Lax',
    'use_strict_mode' => true,
    'cookie_secure'    => isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on',
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

// Roteador
if ($requestUri === '/') {
    if (Auth::check()) {
        Auth::redirect('/dashboard');
    } else {
        Auth::redirect('/login');
    }
} elseif ($requestUri === '/login') {
    require_once dirname(__DIR__) . '/app/controllers/AuthController.php';
    (new AuthController())->login();
} elseif ($requestUri === '/logout') {
    require_once dirname(__DIR__) . '/app/controllers/AuthController.php';
    (new AuthController())->logout();
} elseif ($requestUri === '/cadastro') {
    require_once dirname(__DIR__) . '/app/controllers/AuthController.php';
    (new AuthController())->cadastro();
} elseif ($requestUri === '/recuperar-senha') {
    require_once dirname(__DIR__) . '/app/controllers/AuthController.php';
    (new AuthController())->recuperarSenha();
} elseif ($requestUri === '/redefinir-senha') {
    require_once dirname(__DIR__) . '/app/controllers/AuthController.php';
    (new AuthController())->redefinirSenha();
} elseif ($requestUri === '/dashboard') {
    require_once dirname(__DIR__) . '/app/controllers/DashboardController.php';
    (new DashboardController())->index();
} elseif ($requestUri === '/extrato') {
    require_once dirname(__DIR__) . '/app/controllers/LancamentosController.php';
    (new LancamentosController())->extrato();
} elseif ($requestUri === '/lancamento/salvar') {
    require_once dirname(__DIR__) . '/app/controllers/LancamentosController.php';
    (new LancamentosController())->salvar();
} elseif ($requestUri === '/lancamento/excluir') {
    require_once dirname(__DIR__) . '/app/controllers/LancamentosController.php';
    (new LancamentosController())->excluir();
} elseif ($requestUri === '/categorias') {
    require_once dirname(__DIR__) . '/app/controllers/CategoriasController.php';
    (new CategoriasController())->index();
} elseif ($requestUri === '/categoria/salvar') {
    require_once dirname(__DIR__) . '/app/controllers/CategoriasController.php';
    (new CategoriasController())->salvar();
} elseif ($requestUri === '/categoria/excluir') {
    require_once dirname(__DIR__) . '/app/controllers/CategoriasController.php';
    (new CategoriasController())->excluir();
} elseif ($requestUri === '/perfil/senha') {
    require_once dirname(__DIR__) . '/app/controllers/PerfilController.php';
    (new PerfilController())->senha();
} else {
    http_response_code(404);
    echo "<h1>404 Not Found</h1>";
    echo "<p>Caminho de recurso [" . Sanitizer::e($requestUri) . "] não encontrado.</p>";
}
