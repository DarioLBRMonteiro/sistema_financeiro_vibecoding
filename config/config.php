<?php
defined('APP_EXEC') or die('Acesso direto não permitido.');

return [
    'app_name' => 'Gestão Financeira Simples',
    'app_url'  => 'http://localhost:8080/sistema_financeiro', // Ajustado para o caminho do workspace local no Apache com porta 8080
    'env'      => 'desenvolvimento', // desenvolvimento | producao
    
    'db' => [
        'host'    => '127.0.0.1',
        'port'    => '3306',
        'dbname'  => 'gestao_financeira',
        'user'    => 'root',
        'pass'    => '',
        'charset' => 'utf8mb4',
    ],
    
    'session' => [
        'name'     => 'GFS_SESSID',
        'lifetime' => 7200, // 2 horas
    ]
];
