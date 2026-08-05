<?php
defined('APP_EXEC') or die('Acesso direto não permitido.');

require_once dirname(__DIR__) . '/models/Usuario.php';

class AuthController {
    public function login() {
        Auth::requireGuest();
        
        $erro = '';
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            CSRF::validateToken($_POST['csrf_token'] ?? null);
            
            $email = trim($_POST['email'] ?? '');
            $senha = $_POST['senha'] ?? '';
            
            if (empty($email) || empty($senha) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
                $erro = 'E-mail ou senha inválidos.';
            } else {
                $usuario = Usuario::findByEmail($email);
                if ($usuario && password_verify($senha, $usuario['senha'])) {
                    session_regenerate_id(true);
                    $_SESSION['user_id'] = $usuario['id'];
                    $_SESSION['user_nome'] = $usuario['nome'];
                    Auth::redirect('/dashboard');
                } else {
                    $erro = 'E-mail ou senha inválidos.';
                    Logger::logSecurity("Tentativa de login falha para o email: {$email}");
                }
            }
        }
        
        require dirname(__DIR__) . '/views/auth/login.php';
    }

    public function logout() {
        session_destroy();
        Auth::redirect('/login');
    }

    public function cadastro() {
        Auth::requireGuest();
        
        $erro = '';
        $sucesso = '';
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            CSRF::validateToken($_POST['csrf_token'] ?? null);
            
            $nome = trim($_POST['nome'] ?? '');
            $email = trim($_POST['email'] ?? '');
            $senha = $_POST['senha'] ?? '';
            $confirmacao = $_POST['confirmacao_senha'] ?? '';
            
            if (mb_strlen($nome) < 3) {
                $erro = 'O nome deve ter no mínimo 3 caracteres.';
            } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                $erro = 'E-mail inválido.';
            } elseif (mb_strlen($senha) < 8 || !preg_match('/[a-zA-Z]/', $senha) || !preg_match('/[0-9]/', $senha)) {
                $erro = 'A senha deve ter no mínimo 8 caracteres, contendo letras e números.';
            } elseif ($senha !== $confirmacao) {
                $erro = 'As senhas não coincidem.';
            } else {
                if (Usuario::findByEmail($email)) {
                    $erro = 'E-mail já cadastrado.';
                } else {
                    try {
                        Usuario::create($nome, $email, $senha);
                        $sucesso = 'Cadastro realizado com sucesso! Faça login.';
                    } catch (Exception $e) {
                        Logger::logError("Erro ao cadastrar usuário", $e);
                        $erro = 'Ocorreu um erro interno. Tente novamente mais tarde.';
                    }
                }
            }
        }
        
        require dirname(__DIR__) . '/views/auth/cadastro.php';
    }

    public function recuperarSenha() {
        Auth::requireGuest();
        
        $erro = '';
        $sucesso = '';
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            CSRF::validateToken($_POST['csrf_token'] ?? null);
            
            $email = trim($_POST['email'] ?? '');
            if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                $erro = 'E-mail inválido.';
            } else {
                $usuario = Usuario::findByEmail($email);
                if ($usuario) {
                    $token = bin2hex(random_bytes(32));
                    if (Usuario::setRecoveryToken($email, $token)) {
                        // Simulação de envio de e-mail
                        $config = require dirname(dirname(__DIR__)) . '/config/config.php';
                        $link = $config['app_url'] . '/redefinir-senha?token=' . $token;
                        
                        $logMessage = sprintf("[%s] EMAIL SIMULATION | Para: %s | Link: %s\n", date('Y-m-d H:i:s'), $email, $link);
                        
                        $logDir = dirname(dirname(__DIR__)) . '/logs';
                        if (!is_dir($logDir)) {
                            @mkdir($logDir, 0755, true);
                        }
                        file_put_contents($logDir . '/email_simulation.log', $logMessage, FILE_APPEND | LOCK_EX);
                    }
                }
                // Sempre exibir a mesma mensagem para não vazar emails cadastrados
                $sucesso = 'Se o e-mail estiver cadastrado em nosso sistema, você receberá as instruções para redefinição de senha.';
            }
        }
        
        require dirname(__DIR__) . '/views/auth/recuperar-senha.php';
    }

    public function redefinirSenha() {
        Auth::requireGuest();
        
        $token = $_GET['token'] ?? '';
        if (empty($token)) {
            Auth::redirect('/login');
        }

        $usuario = Usuario::findByToken($token);
        
        $erro = '';
        $sucesso = '';
        if (!$usuario) {
            $erro = 'Link de redefinição de senha inválido ou expirado. Por favor, solicite uma nova recuperação.';
        } else {
            if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                CSRF::validateToken($_POST['csrf_token'] ?? null);
                
                $senha = $_POST['senha'] ?? '';
                $confirmacao = $_POST['confirmacao_senha'] ?? '';
                
                if (mb_strlen($senha) < 8 || !preg_match('/[a-zA-Z]/', $senha) || !preg_match('/[0-9]/', $senha)) {
                    $erro = 'A senha deve ter no mínimo 8 caracteres, contendo letras e números.';
                } elseif ($senha !== $confirmacao) {
                    $erro = 'As senhas não coincidem.';
                } else {
                    if (Usuario::updatePasswordWithToken($usuario['id'], $senha)) {
                        Logger::logSecurity("Senha redefinida via token", $usuario['id']);
                        $sucesso = 'Senha redefinida com sucesso! Faça login com sua nova senha.';
                    } else {
                        $erro = 'Ocorreu um erro interno. Tente novamente mais tarde.';
                    }
                }
            }
        }
        
        require dirname(__DIR__) . '/views/auth/redefinir-senha.php';
    }
}
